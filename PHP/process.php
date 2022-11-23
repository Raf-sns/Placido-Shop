<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	process.php
 *
 * process::get_one_product( $id );
 * process::return_pagination( $ARR, $ARR_products, $nb_wanted );
 * process::get_static_pages();
 * process::get_one_customer( $customer_id );
 * process::record_ip_rejected( $ip );
 * process::test_ip_rejected();
 *
 */

class process {


  /**
   * process::get_one_product( $id );
   *
   * @param  {int} $id  prod id
   * @return {array}    one product
   */
  public static function get_one_product($id){

      // GET ONE PRODUCT
      $ARR_pdo = array( 'id' => (int) $id, 'order_img' => 0 );
      $sql = 'SELECT * FROM products_imgs
      INNER JOIN products
      ON products_imgs.parent_id = products.id
      WHERE products_imgs.parent_id=:id AND products_imgs.order_img=:order_img';
      $response = 'one';
      $last_id = false; // RETURN last id

      return db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * process::get_one_product( $id );
   */



  /**
   *  process::return_pagination( $ARR, $ARR_products, $nb_wanted );
   *
   * @param  {array}  $ARR          -> GLOBAL ARRAY()
   * @param  {array}  $ARR_products -> array of products on line returned by server
   * @param  {int}    $nb_wanted    -> nb products by page (int)
   * @return {array}  $ARR[]  + [view] + [select_nb_opt]
   */
  public static function return_pagination( $ARR, $ARR_products, $nb_wanted ){


		// count nb products
    $count_products = count($ARR_products);

    // var_dump($count_products);

    // MANAGE  SELECT OPTION VIEW
    $ARR['select_nb_opt'] = array(); // FOR RENDER ARRAY TEMPLATE

    // DEFAULT  ARRAY TO <select> OPTIONS
    $seletable = [1, 2, 3, 4, 5, 10, 15, 20, 25, 50];

    // an new option is inserted ?? -> class page:: [$page == 'get_vendor'] case
    $option_inserted = false;

    // un-bug IF NB_WANTED IS TALLER THAN NB_FOR_PAGINA -> this win !!
    if( $nb_wanted > NB_FOR_PAGINA ){

        $nb_wanted = NB_FOR_PAGINA;
    }

    // if nb products is smaller THAN NB_FOR_PAGINA
    if( $count_products < NB_FOR_PAGINA ){

        $nb_wanted = $count_products;
    }

		$opt_added = false;

    // loop over select
    foreach( $seletable as $i => &$value ){

				// if same item was found -> pass it to selected
        if( $seletable[$i] == $nb_wanted ){

          	$ARR['select_nb_opt'][] =
						array( 'value' => $seletable[$i], 'opt' => 'selected' );

						$opt_added = true;
        }

				// if specific item and lowest of this ite -> insert before
				else if( $seletable[$i] > $nb_wanted && $opt_added == false ){

          // in case of shop have less product than NB_FOR_PAGINA DEFAULT
          $ARR['select_nb_opt'][$i-1] = array( 'value' => $nb_wanted, 'opt' => 'selected' );

          $opt_added = true;

        }
        else{
          // default <select> OPTION false
          $ARR['select_nb_opt'][] = array( 'value' => $seletable[$i], 'opt' => false  );
        }

    }
    // END FOR SELECT OPTION VIEW


    // IF NO PRODUCTS -> !! do it here we need prev. $ARR[datas]
    if( $count_products == 0 ){

        $ARR['view']['products'] = [];

        // DEFINE A NUMBER OF PRODS FOR VIEW BY DEFAULT
        $ARR['view']['def_nb_prods'] = NB_FOR_PAGINA;

        return $ARR;
        // stop here
    }


    // THEN CONTINUE ...


		// calcul pages need
    $pages_need = ceil( ($count_products / $nb_wanted) );

		// make a temp array result
		$ARR['view']['temp'] = $ARR_products;
    $ARR['view']['pages_need'] = $pages_need;
		$ARR['view']['page'] = 1;
    $ARR['view']['nb_wanted'] = $nb_wanted;
    $ARR['view']['products'] = array();
		// DEFINE A NUMBER OF PRODS FOR VIEW BY DEFAULT
		$ARR['view']['def_nb_prods'] = NB_FOR_PAGINA;

		// put first nb products first page
    for( $i=0; $i < $nb_wanted; $i++) {

        $ARR['view']['products'][] = $ARR_products[$i];

    }
    // end for


    return $ARR;

  }
  /**
   * process::return_pagination( $ARR, $ARR_products, $nb_wanted );
   */



  /**
   * process::get_static_pages();
   *
   * @return {array}  description
   */
  public static function get_static_pages(){


      // GET ONE PROD. QUANT.
      $ARR_pdo = false;
      $sql = 'SELECT * FROM static_pages';
      $response = 'all';
      $last_id = false;

      $PAGES_ENABLED = db::server($ARR_pdo, $sql, $response, $last_id);

      // empty case return an empty array
      if( empty($PAGES_ENABLED) ){

          return array();
      }

      $ARR_return = array();

      foreach( $PAGES_ENABLED as $k => $v ){

          $ARR_return +=
          array( $v['page_url'] =>
                    array( 'url' => $v['page_url'],
                           'page_title' => $v['page_title'] )
          );

      }
      // return ststic pages array
      return $ARR_return;

  }
  /**
   * process::get_static_pages();
   */



  /**
   * process::get_one_customer( $customer_id );
   *
   * @param  {type} $customer_id description
   * @return {type}              description
   */
  public static function get_one_customer( $customer_id ){

      $ARR_pdo = array('id' => (int) $customer_id);
      $sql = 'SELECT * FROM customers WHERE id=:id';
      $response = 'one';
      $last_id = false;
      //  ->  fetch
      $ONE_CUSTOMER = db::server($ARR_pdo, $sql, $response, $last_id);

      // parse tel ...
      // remove all spaces returns tabs, ...
      $tel = trim($ONE_CUSTOMER['tel']);
      $regex = '/(\r\n|\n|\t|\r|\s| ){1,}/';
      $replacement = ""; // !! ONLY "" ARE INTERPRETED
      $tel_parsed = preg_replace($regex, $replacement, $tel);
      // trasnform in array
      $ARR_tel_pre = str_split( $tel_parsed, 1 );
      // reverse array -> walk from the end, last char of tel
      $ARR_tel = array_reverse($ARR_tel_pre);
      // var_dump($ARR_tel);
      // index for get pairs of numbers and insert space
      $indx = 0;
      $pre_tel = ''; // string tel for retunr

      $count = count($ARR_tel); // count array for loop

      // concat tel number wll formated
      for( $i=0; $i < $count; $i++ ){

        $indx++;

        // serach for modulo
        $pre_tel = ( $indx%2 == 0 ) ?
        ' '.$ARR_tel[$i].$pre_tel : $ARR_tel[$i].$pre_tel;

        // var_dump( $ARR_tel[$i] );
      }
      // end for

      // var_dump( $pre_tel );

      $ONE_CUSTOMER['tel'] = $pre_tel;

      return $ONE_CUSTOMER;

  }
  /**
   * process::get_one_customer( $customer_id );
   */



  /**
   * process::record_ip_rejected( $ip );
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
   * process::record_ip_rejected( $ip );
   */



  /**
   * process::test_ip_rejected();
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
   * process::test_ip_rejected();
   */



}
// END CLASS process::



?>
