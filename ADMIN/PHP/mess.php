<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello - 2020, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 mess.php
 *
 * mess::get_messages();
 * mess::get_fresh_messages();
 * mess::update_mess_readed();
 * mess::suppr_message();
 *
 */

class mess {


  /**
   * mess::get_messages();
   *
   * @return {array}
   */
  public static function get_messages(){


    // GET ALL MESSAGES
    $ARR_pdo = false;

    $sql = 'SELECT * FROM messages ORDER BY mess_id DESC';
    $response = 'all';
    $last_id = false;

    $ALL_MESSAGES = db::server($ARR_pdo, $sql, $response, $last_id);

    // rep. $count_not_read
    $count_not_read = 0;

		// NO MESSAGES
    if( count($ALL_MESSAGES) == 0 ){

        // return array count news and message
        return array(
					'messages' => array(),
					'nb_messages' => 0,
					'count_not_read' => $count_not_read,
				);
    }
		// END NO MESSAGES

		$timeZone = new DateTimeZone( TIMEZONE );

    // loop
    foreach ($ALL_MESSAGES as $k => $v) {

        // is READ ?
        if( boolval($v['readed']) == false ){

            $ALL_MESSAGES[$k]['readed'] = false;
            $count_not_read++; // count only not readed
        }
        else{
            $ALL_MESSAGES[$k]['readed'] = true;
        }
        // END is READ ?

        // FORMAT DATE
				$dateTimeObj = new DateTime($v['date_mess'], $timeZone);

				$dateFromatted = tools::format_date_locale( $dateTimeObj, 'FULL' , 'SHORT', null );

				$ALL_MESSAGES[$k]['date_mess'] = ucwords($dateFromatted);

    }
    // end loop

    // return array count news and message
    return array(
			'messages' => $ALL_MESSAGES,
			'nb_messages' => count($ALL_MESSAGES),
			'count_not_read' => $count_not_read,
		);

  }
  /**
   * mess::get_messages();
   */



  /**
   * mess::get_fresh_messages();
   *
   * @return {json}  resturn:  messages[] -> new messages
	 * 													 count_not_read -> int.
   */
  public static function get_fresh_messages(){


			// id of last messge in view
			$last_id = (int) trim(htmlspecialchars($_POST['last_id']));

      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);

      // GET NEW MESSAGES
      $ARR_pdo = array( 'last_id' => $last_id );

      $sql = 'SELECT * FROM messages WHERE mess_id > :last_id';
      $response = 'all'; // fetch all
      $last_id = false;

      $GET_NEW_MESSAGES = db::server($ARR_pdo, $sql, $response, $last_id);

			// NO NEW MESSAGES
      if( empty($GET_NEW_MESSAGES) ){

          $tab = array( 'success' => true,
												'messages' => array(),
												'count_not_read' => 0,
											 );
          echo json_encode($tab, JSON_NUMERIC_CHECK);
          exit;

      }

			$timeZone = new DateTimeZone( TIMEZONE );

			// else loop over new messages
			foreach ( $GET_NEW_MESSAGES as $k => $v ) {

					// FORMAT DATE
					$dateTimeObj = new DateTime($v['date_mess'], $timeZone);

					$dateFromatted = tools::format_date_locale( $dateTimeObj, 'FULL' , 'SHORT', null );

					$GET_NEW_MESSAGES[$k]['date_mess'] = ucwords($dateFromatted);

			}
			// end loop

			// return new messages
			$tab = array( 'success' => true,
										'messages' => $GET_NEW_MESSAGES,
										'count_not_read' => count($GET_NEW_MESSAGES),
									 );

			echo json_encode($tab, JSON_NUMERIC_CHECK);

			unset( $_POST );
			exit;

	}
  /**
   * mess::get_fresh_messages();
   */



  /**
   * mess::update_mess_readed();
   *
   * @return {type}  description
   */
  public static function update_mess_readed(){


      $mess_id = (int) trim(htmlspecialchars($_POST['mess_id']));

      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);

      // UPDATE TO READED
      $ARR_pdo = array( 'readed' => 1, 'mess_id' => $mess_id );

      $sql = 'UPDATE messages SET readed=:readed WHERE mess_id=:mess_id';
      $response = false;
      $last_id = false;

      $UPDATE_MESS_READED = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($UPDATE_MESS_READED) == true ){

          $tab = array('success' => true);
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }
      else{

          $tab = array( 'error' => tr::$TR['error_pass_mess_readed'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

  }
  /**
   * mess::update_mess_readed();
   */



  /**
   * mess::suppr_message();
   *
   * @return {type}  description
   */
  public static function suppr_message(){


    $mess_id = (int) trim(htmlspecialchars($_POST['mess_id']));

    // verify token
    $token = trim(htmlspecialchars($_POST['token']));
    program::verify_token($token);

    // UPDATE TO READED
    $ARR_pdo = array( 'mess_id' => $mess_id );

    $sql = 'DELETE FROM messages WHERE mess_id=:mess_id';
    $response = false;
    $last_id = false;

    $SUPPR_MESS = db::server($ARR_pdo, $sql, $response, $last_id);

    if( boolval($SUPPR_MESS) == true ){

        $tab = array( 'success' => tr::$TR['success_delete_message'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }
    else{

        $tab = array( 'error' => tr::$TR['error_delete_message']);
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

  }
  /**
   * mess::suppr_message();
   */


}
// END CLASS mess::

?>
