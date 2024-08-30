<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	ip_rejected.php
 *
 * ip_rejected::record_ip_rejected( $ip );
 * ip_rejected::test_ip_rejected();
 *
 */

class ip_rejected {


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



  /**
   * ip_rejected::test_ip_rejected();
   *
   * TEST IF AN IP IS REJECTED BY API
   *
   * @param  {globals}      $_SERVER['REMOTE_ADDR']
   * @return {bool/string}  return false OR IP of user
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



}
// end class ip_rejected
?>
