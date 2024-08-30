<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	token.php
 *
 * token::set_token( $user_id, $mail, $password );
 * token::clean_tokens( $user_id );
 * token::verify_token(); i.-> this return admin id in integer
 *
 */

class token {


  /**
   * token::set_token( $user_id, $mail, $password );
   *
   * @param  {int} 		$user_id  User id
   * @param  {string} $mail    	Mail user
   * @param  {string} $mdp     	Word pass user
   * @return {string} encrypted token
   */
  public static function set_token( $user_id, $mail, $password ){


      // BEFORE CLEAN OLD TOKEN
      token::clean_tokens($user_id);

			// Get cryptographically secure random bytes
      $bytes = random_bytes(32);

			// make a hashed token
			$token = password_hash(bin2hex($bytes).$user_id.microtime(true).$mail.$password, PASSWORD_DEFAULT);

			// timestamp
			$stamp = time();

			// RECORD token
      $ARR_pdo = array(
				'user_id' => $user_id,
				'token' => $token,
				'stamp' => $stamp
			);

			$sql = 'INSERT INTO tokens (user_id, token, stamp)
			VALUES (:user_id, :token, :stamp)';
      $response = false;
      $last_id = false;

      $INSERT_TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($INSERT_TOKEN) == true ){

          return $token;
      }
      else{

          $Arr = array( 'error' => tr::$TR['error_create_token'] );
          echo json_encode($Arr);
          exit;
      }

  }
  /**
   * END token::set_token( $user_id, $mail, $mdp );
   */



  /**
   * token::clean_tokens( $user_id );
   *
   * @param  {int} $user_id
   */
  public static function clean_tokens( $user_id ){


			// timestamp
      $time = time();

			// compare time now - token time duration in sec.
      $stamp_limit = $time - TOKEN_TIME;

      // CLEAN old tokens OR token alerady created
      $ARR_pdo = array( 'stamp_limit' => $stamp_limit, 'user_id' => $user_id );
      $sql = 'DELETE FROM tokens WHERE stamp < :stamp_limit OR user_id=:user_id';
      $response = false;
      $last_id = false;

			// delete old token
      $CLEAN_TOKENS = db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * END token::clean_tokens( $user_id );
   */



  /**
   * token::verify_token();
   *
   * @return {int}  user id
   */
  public static function verify_token(){


			// empty token
			if( !isset($_POST['token']) || empty($_POST['token']) ){

					// return error
					$Arr = array( 'error' => tr::$TR['empty_token'] );
					echo json_encode($Arr);
					exit;
			}

			// securise token recived
      $token = (string) trim(htmlspecialchars($_POST['token']));

			// timestamp
      $time = time();

			// validity token time
      $stamp_limit = $time - TOKEN_TIME;

      // FETCH by token
      $ARR_pdo = array('token' => $token);
      $sql = 'SELECT * FROM tokens WHERE token=:token';
      $response = 'one';
      $last_id = false;

      $ONE_TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);
      // var_dump($ONE_TOKEN);

      // ERROR 1 TOKEN NOT FOUND
      if( boolval($ONE_TOKEN) == false ){

          $Arr = array('error' =>
          "Mmmm ! You shouldn't be here.
          <br>Please, don't hack me !
          <br>Security report : <contact@sns.pm>");
          echo json_encode($Arr);
          exit;
      }

      // ERROR 2 TOKEN TIME TOO OLD
      if( $ONE_TOKEN['stamp'] < $stamp_limit ){

					// delete token
					token::clean_tokens( (int) $ONE_TOKEN['user_id'] );

          $Arr = array( 'error' => tr::$TR['token_expired'] );
          echo json_encode($Arr);
          exit;
      }

      // IF IT'S OK -> RETURN user_id
      return (int) $ONE_TOKEN['user_id'];

  }
  /**
   * END token::verify_token( $token );
   */



}
// end class token::
?>
