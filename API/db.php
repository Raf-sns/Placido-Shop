<?php
/**
 * PLACIDO-SHOP FRAMEWORK - API
 * Copyright © Raphaël Castello, 2018-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	db.php
 *
 * class db extends config
 * manage PDOstatement & requests to database
 *
 * db::connect();
 * db::server( $ARR_pdo, $sql, $response, $last_id );
 *
 */


/**
 * class db extended from class config
 * manage PDOstatement & requests to database
 */
class db extends config {


  /**
   * private db::connect();
   *
   * @return {PDO Statement}
   */
  private static function connect(){

      // LOGIN_DATABASE_INFOS
      $host = self::DB_HOST;
      $dbname = self::DB_NAME;
      $user = self::DB_USER;
      $password = self::DB_PASSWORD;


      $bdd = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $user, $password, array( PDO::ATTR_PERSISTENT => false));

      return $bdd;

      // notes:
      // ALTER TABLE tablename AUTO_INCREMENT = 1
      // $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  }
  /**
   * END db::connect();
   * @return {PDO Statement}
   */



  /**
   * db::server( $ARR_pdo, $sql, $response, $last_id );
   * Handles all database queries
   * @param  {array}        $ARR_pdo       array of vars. for PDO
   * @param  {string}       $sql           SQL command
   * @param  {string/bool}  $response      'one' || 'all' || false
   * 'all' -> one row response, 'all' -> multiples rows response
   * false -> for return nothing e.g.: INSERT / UDPATE / DELETE
   * @param  {bool}         $last_id       true || false -> true return last inserted id
   * @return {array/int}    array from data base
   */
  public static function server( $ARR_pdo, $sql, $response, $last_id ){


    // INIT PDO
    $DATA_BASE = db::connect();

    // un-comment try/catch block for render error
    // try {

      // prepa. SQL
      $request = $DATA_BASE->prepare($sql);


      // $ARR_pdo
      if( $ARR_pdo != false ){

          // exec. ARRAY()
          $request->execute($ARR_pdo);
      }
      else{

          // request without array - $ARR_pdo = false;
          $request->execute();
      }

      // test $response asked
      if( $response != false ){

          // get one row
          if( $response == 'one' ){

              return $request->fetch(PDO::FETCH_ASSOC); // fetch ONE response
          }

          // get multiples rows
          if( $response == 'all' ){

              return $request->fetchAll(PDO::FETCH_ASSOC); // fetch MULTIPLES responses
          }

      }
      // END test $response asked

      // last inserted id ASKED
      if( $last_id == true && $response == false ){

          // return last insert id
          return $DATA_BASE->lastInsertId();

      }

      // PDO closeCursor
      $request->closeCursor();

      // return true for test
      return true;


  // } catch (PDOException $e) {
  //   die(sprintf("%s in %s at the line %d : %s",
  //   get_class($e), $e->getFile(), $e->getLine(), $e->getMessage()));
  // }

  }
  /**
   * END db::server( $ARR_pdo, $sql, $response, $last_id );
   */


}
/**
 * END class db::
 */

?>
