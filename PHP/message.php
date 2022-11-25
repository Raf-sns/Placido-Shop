<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2021
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	message.php
 *
 * CLASS message::
 *
 * message::record_message( $mail, $name, $message,	$date_mess );
 *
 */

class message {


  /**
   * message::record_message( $mail, $name, $message,	$date_mess );
   *
   * @param  {type} $mail      description
   * @param  {type} $name      description
   * @param  {type} $message   description
   * @param  {type} $date_mess description
   * @return {type}            description
   */
  public static function record_message( $mail, $name, $message,	$date_mess ){


      // RECORD MESSAGE
      $ARR_pdo = array(
        'mess_id' => 0,
        'mail' => $mail,
        'name' => $name,
        'message' => $message,
        'date_mess' => $date_mess,
        'readed' => 0
      );

      $sql = 'INSERT INTO messages
      ( mess_id, mail, name, message, date_mess, readed )
      VALUES ( :mess_id, :mail, :name, :message, :date_mess, :readed )';

      $response = false;
      $last_id = false;

      $RECORD_MESSAGE = db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * END message::record_message( $mail, $name, $message,	$date_mess );
   */


}
// END CLASS message::



?>
