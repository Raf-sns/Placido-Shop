<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello - 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	ip_rejected.php
 *
 * ip_rejected::get_ip_rejected();
 * ip_rejected::get_all_rejected_IP();
 * ip_rejected::admin_unban_ip();
 * ip_rejected::test_ip_rejected();
 * ip_rejected::record_ip_rejected( $ip );
 *
 */

class ip_rejected {


  /**
   * ip_rejected::get_ip_rejected();
   *
   * @return {json}  array of ip rejected
   */
  public static function get_ip_rejected(){


			// VERIFY USER
			$token = trim(htmlspecialchars( $_POST['token'] ));
			// verify token
			program::verify_token($token);

			// GET ALL REJECTED
			// @return : last_check -> date locale  formatted
			// @return : rejected -> array of [rejected ip + date]
			// @return : nb_rejected -> int
			$REJECTED = ip_rejected::get_all_rejected_IP();


			$tab = array( 'success'  => true,
										'last_check' => $REJECTED['last_check'],
										'rejected' => $REJECTED['rejected'],
									 	'nb_rejected' => $REJECTED['nb_rejected']  );

			echo json_encode($tab, JSON_NUMERIC_CHECK);
			exit;

	}
  /**
   * ip_rejected::get_ip_rejected();
   */



  /**
   * ip_rejected::get_all_rejected_IP();
	 *
   * Get all ip rejected in DB
	 * @return {string} 	last_check -> date time of last verification
	 * @return {array}  	rejected -> array of ip rejected
	 * @return {int} 			nb_rejected
   */
  public static function get_all_rejected_IP(){


			// GET ALL REJECTED
			$ARR_pdo = false;
			$sql = 'SELECT * FROM ip_rejected ORDER BY stamp DESC';
			$response = 'all';
      $last_id = false;
			$ALL_REJECTED = db::server($ARR_pdo, $sql, $response, $last_id);

			// set a local date for last check date infos
			$date_now = time();

			$fmt = new IntlDateFormatter(
				LANG_BACK, // api const
				IntlDateFormatter::FULL, // manage date
				IntlDateFormatter::SHORT, // manage time
				TIMEZONE, // api const
				null // calendar gregorian by default
			);

			$date_check = ucwords( $fmt->format( $date_now ) );

			if( empty($ALL_REJECTED) ){

					return array( 'last_check' => $date_check,
				 								'rejected' => array(),
												'nb_rejected' => 0  		);

			}

			// loop all rejecteds -> format date
			$REJECTED = array();

			foreach ( $ALL_REJECTED as $k => $v) {

					$REJECTED[] = array(
						'ip' => $v['ip'],
						'date_reject' => ucwords($fmt->format($v['stamp']))
					);
			}
			// end loop

			// return all rejected + last check date locale format
			return array( 'last_check' => $date_check,
										'rejected' => $REJECTED,
										'nb_rejected' => count($REJECTED)  );

	}
  /**
   * ip_rejected::get_all_rejected_IP();
   */



  /**
   * ip_rejected::admin_unban_ip();
   * remove an ip from ip_rejected
   * @return {json}  array of ip rejected
   */
  public static function admin_unban_ip(){


			// VERIFY USER
			$token = trim(htmlspecialchars( $_POST['token'] ));
			// verify token
			program::verify_token($token);

			// ip - able to suppr empty IPs -> IP must be == ''
			$ip = ( empty($_POST['ip']) )
			? ''
			: (string) trim(htmlspecialchars($_POST['ip']));


			// validate ip
			if( $ip != '' && !filter_var( $ip, FILTER_VALIDATE_IP )
					|| iconv_strlen( $ip ) > 50 ){

					$tab = array('error' => tr::$TR['bad_ip'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// UNBAN IP
			$ARR_pdo = array('ip' => $ip);
			$sql = 'DELETE FROM ip_rejected WHERE ip=:ip';
			$response = false;
      $last_id = false;
			$UNBAN_IP = db::server($ARR_pdo, $sql, $response, $last_id);

			// error
			if( boolval($UNBAN_IP) == false ){

					$tab = array('error' => tr::$TR['error_server'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// GET ALL REJECTED
			// @return : last_check -> date locale  formatted
			// @return : rejected -> array of [rejected ip + date]
			// @return : nb_rejected -> int
			$REJECTED = ip_rejected::get_all_rejected_IP();


			$tab = array( 'success'  => true,
										'last_check' => $REJECTED['last_check'],
										'rejected' => $REJECTED['rejected'],
									 	'nb_rejected' => $REJECTED['nb_rejected']  );

			// return json ip_rejected
			echo json_encode($tab, JSON_NUMERIC_CHECK);
			exit;

	}
  /**
   * ip_rejected::admin_unban_ip();
   */



  /**
   * ip_rejected::test_ip_rejected();
   *
   * TEST IF AN IP IS REJECTED BY API
   *
   * @param  {globals}      $_SERVER['REMOTE_ADDR']
   * @return {bool/string}  return false -> ban OR IP of user -> not ban
   */
  public static function test_ip_rejected(){


      // collect ip of visitor
      $ip_user = false;

      $options = array(
          'flags' =>
          FILTER_FLAG_IPV4 |
          FILTER_FLAG_IPV6 |
          FILTER_FLAG_NO_PRIV_RANGE |
          FILTER_FLAG_NO_RES_RANGE |
          FILTER_NULL_ON_FAILURE
      );

      // if have REMOTE_ADDR
      if( isset($_SERVER['REMOTE_ADDR'])
          && filter_var( $_SERVER['REMOTE_ADDR'],
          FILTER_VALIDATE_IP, $options ) != false )
      {

          $ip_user = filter_var( $_SERVER['REMOTE_ADDR'],
          FILTER_VALIDATE_IP, $options );
      }

      // if IP was evaluated at false
      if( boolval($ip_user) == false ){

          return false;
      }

      // test if rejected in DB
      // RECORD IP REJECTED IN DB
      $ARR_pdo = array( 'ip' => $ip_user );

      $sql = 'SELECT ip FROM ip_rejected WHERE ip=:ip';

      $response = 'one'; // ask one row
      $last_id = false;

      // VERIFY REJECTED IP
      $REJECTED_IP = db::server($ARR_pdo, $sql, $response, $last_id);

      // if a rejected was found -> return false
      if( !empty($REJECTED_IP['ip'])
          && $REJECTED_IP['ip'] == $ip_user ){

          return false;
      }

      // all well -> return IP of user
      return $ip_user;

  }
  /**
   * ip_rejected::test_ip_rejected();
   */



  /**
   * ip_rejected::record_ip_rejected( $ip );
   *
   * @param  {str} $ip  IP of visitor -> filtred
   * @return {bool}     Record an IP who was rejected
   */
  public static function record_ip_rejected( $ip ){

      // RECORD IP REJECTED IN DB
      $ARR_pdo = array(
          'ip' => $ip,
          'stamp' => time() // timestamp
      );

      $sql = 'INSERT INTO ip_rejected
      (ip, stamp) VALUES (:ip, :stamp)
      ON DUPLICATE KEY UPDATE stamp=:stamp';

      $response = false;
      $last_id = false;

      // RECORD IP
      db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * ip_rejected::record_ip_rejected( $ip );
   */



}
// end class ip_rejected::

?>
