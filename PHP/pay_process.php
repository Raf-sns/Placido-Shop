<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	pay_process.php
 *
 * Class : pay_process
 *
 * pay_process::data_process();
 * pay_process::confirm_payment();
 * pay_process::delete_new_sale();
 * pay_process::update_quant_prod( $id, $number, $to_do );
 * pay_process::update_as_payed( $sale_id );
 * pay_process::update_ref_payment( $sale_id, $hash_customer );
 * pay_process::get_sold_products( $CART ); //
 * pay_process::record_customer( $SALE );
 * pay_process::record_new_sale( $SALE, $new_customer_id );
 * pay_process::record_sold_products( $SALE, $new_sale_id );
 * pay_process::test_datas_user( $_sup='' );
 * pay_process::check_mode_payment_shop( $pay_with );
 * pay_process::secure_payment();
 *
 *
 */
class pay_process {


  /**
   * pay_process::data_process();
   *
   * @return {json}  verify fields and payment mode return stripe token
   */
  public static function data_process(){


    // SECURE PAYMENT
    // able to make 10 requests by day after -> rejected
    // + IN pay_process::delete_new_sale();
    // -> able to make ONLY 5 TRUE REQUEST BEFORE blocking
    pay_process::secure_payment();


    // TEST DATAS USER SENDED
    // RETURN ARRAY DATAS OF CUSTOMER - sanitized
    $CUSTOMER = pay_process::test_datas_user( $_sup='' );

    // if address sup
    if( !empty($_POST['lastname_sup'])
    || !empty($_POST['firstname_sup']) ){

        $CUSTOMER =
        array_merge( $CUSTOMER, pay_process::test_datas_user($_sup='_sup') );
    }

    // print_r( $CUSTOMER );
    // exit;

    // TEST MODE PAYMENT
    if( !isset($_POST['pay_with'])
    || empty($_POST['pay_with'])
    || trim(htmlspecialchars($_POST['pay_with'])) != 'CARD'
    && trim(htmlspecialchars($_POST['pay_with'])) != 'OTHER' ){

        // error
        $tab = array( 'error' => true,
                      'message' => tr::$TR['choose_payment_error'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // MODE PAYMENT $pay_with = 'CARD' / 'OTHER'
    $pay_with = trim(htmlspecialchars($_POST['pay_with']));

    // check mode shop -> if shop have card payment,
    // if it mode test card or mode production card
    // test if other mode payment was asked
    // $pay_with = 'CARD' / 'OTHER'
    // return $SHOP + key ['mode_payment']
    //                      =>  'card_test' / 'card_production' / 'other'
    $SHOP = pay_process::check_mode_payment_shop( $pay_with );

    // print_r( $SHOP );
    // exit;

    // valid sale_conditions
    if( empty(trim($_POST['sale_conditions']))
        || trim(htmlspecialchars($_POST['sale_conditions'])) != 'signed'  ){

        // error
        $tab = array( 'error' => true,
                      'message' => tr::$TR['accept_terms_and_conditions_error'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // get products and verify quantity aviable
    $CART = json_decode( trim($_POST['cart']), true );

    // empty cart items (products)
    if( !isset($CART['items']) || empty($CART['items']) ){

        // error
        $tab = array( 'error' => true,
                      'message' => tr::$TR['select_an_article'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // GET SOLD PRODUCTS AND CALCUL PRICES
    // return array[ 'PRODS', 'total_tax_sale', 'total_amount_sale' ]
    // - exit on error
    $SOLD_PRODS = pay_process::get_sold_products( $CART );
    $PRODS = $SOLD_PRODS['PRODS'];
    $total_tax_sale = $SOLD_PRODS['total_tax_sale'];
		// ! absolute value
    $total_amount_sale = abs( (int) $SOLD_PRODS['total_amount_sale'] );

    // total tax sale in string + currency sign
    $total_tax_sale_text = ( $total_tax_sale != 0 )
    ? tools::intl_currency( $total_tax_sale )
		: '';

    // total amount sale in string + currency sign
    $total_amount_sale_text =
			tools::intl_currency( ($total_amount_sale/100) );


    // make a general sale array
    $SALE = array(
      'total_tax_sale' => $total_tax_sale_text, // in string
      'total_amount_sale_text' => $total_amount_sale_text, // in string
      'total_amount_sale' => $total_amount_sale, // in cent
      'sold_products' => $PRODS,
      'shop' => $SHOP,
      'customer' => $CUSTOMER
    );

    // print_r( $SALE );
    // exit;

    // record customer - return (int) $new_customer_id;
    $new_customer_id = pay_process::record_customer( $SALE );

    // record new sale - return (int) $new_sale_id;
    $new_sale_id = pay_process::record_new_sale( $SALE, $new_customer_id );

    // record sold products - exit on error
    pay_process::record_sold_products( $SALE, $new_sale_id );

    // make a hash customer
    $hash_customer =
    api::api_crypt( $CUSTOMER['mail'].$new_sale_id , 'encr');

    // pass hash to id_payment
		// customer can retrieve the state of his command with hash url
    pay_process::update_ref_payment( $new_sale_id, $hash_customer );

    // ADD customer_id + sale_id TO SALE -> for verify payment refs. in Stripe
    $SALE['customer_id'] = $new_customer_id;
    $SALE['sale_id'] = $new_sale_id;


    // OTHER PAYMENT METHODS
    if( $pay_with == 'OTHER' && $SALE['shop']['mode_payment'] == 'other' ){

        // go directly for confirm_payment() -> this exit
        pay_process::confirm_payment( $SALE['sale_id'], $pay_with='OTHER' );
    }


    // get vendor STRIPE keys
    // i. $SALE['shop']['mode_payment']
		// => 'card_test' / 'card_production' / 'other'

    // test mode card payment
    if( $SALE['shop']['mode_payment'] == 'card_test' ){

        // test keys
        $secret_key =
        api::api_crypt( $SALE['shop']['test_priv_key'], 'decr' );

        $public_key =
        api::api_crypt( $SALE['shop']['test_pub_key'], 'decr' );
    }

    // production mode card payment
    if( $SALE['shop']['mode_payment'] == 'card_production' ){

        // production keys
        $secret_key =
        api::api_crypt( $SALE['shop']['prod_priv_key'], 'decr' );

        $public_key =
        api::api_crypt( $SALE['shop']['prod_pub_key'], 'decr' );
    }


    // CALL STRIPE PHP
    require_once('LIBS/Stripe/init.php');

    // CREATE STRIPE PAYMENT AND RETURN 'client_key' : $intent->client_secret
    try{

        $stripe = new \Stripe\StripeClient( $secret_key );

        $intent = $stripe->paymentIntents->create(
          [
            'amount' => $SALE['total_amount_sale'],
            'currency' => strtolower(CURRENCY_ISO), // lowercase - asked by Stripe ?
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' =>
            [
              'sale_id' => $new_sale_id,
              'customer_id' => $new_customer_id,
            ],
            'receipt_email' => $SALE['customer']['mail']
          ]
        );

        // echo json_encode($intent, JSON_FORCE_OBJECT);
        // exit;

    } // Stripe error
    catch(\Stripe\Exception\CardException $e) {

        // error
        $tab = array( 'error' => true,
                      'message' => $e->getError()->message );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }
    // end try/catch Stripe


    // return process_form :
    // true + secret_client + public_key vendor + total_amount_sale
    $tab = array( 'process_form' => true,
                  'client_key' => $intent->client_secret,
                  'public_key' => $public_key,
                  'total_amount_sale_text' => $SALE['total_amount_sale_text'],
                  'customer_id' => $new_customer_id,
                  'hash_customer' => $hash_customer,
                  'sale_id' => $new_sale_id,
                  'payment_id' => $intent->id,
                  'payment_mode_test' =>
                  ($SALE['shop']['mode_payment'] == 'card_test') ? true : false,
                );

    echo json_encode($tab, JSON_FORCE_OBJECT);
    exit;

    // print_r( $SALE );
    // exit;

  }
  /**
   * pay_process::data_process();
   */



  /**
   * pay_process::confirm_payment( $sale_id, $pay_with );
   *
   * RETRIEVE A PAYMENT SUCCEDED IN STRIPE
   * @return {void}  recive a payment succeeded from the client
   * -> decrement products and send mails :
   * order to customer / mail alert to admin
   * @param {int}   $sale_id -> $an_int_value / ''
   * @param {str}   $pay_with -> 'CARD' / 'OTHER'
   */
  public static function confirm_payment( $sale_id, $pay_with ){


      $sale_id = ( empty($sale_id) ) ?
      (int) trim(htmlspecialchars($_POST['sale_id'])) : (int) $sale_id;


      // GET ONE NEW SALE
      require_once dirname(__DIR__).'/'.ADMIN_FOLDER.'/PHP/new_sales.php';

      $SALE = new_sales::get_ONE_sale( $sale_id );
      // echo json_encode( $SALE, JSON_FORCE_OBJECT);
      // exit;

      $SHOP = pay_process::check_mode_payment_shop( $pay_with );
      // return :
      // $SALE['shop']['mode_payment']
      // echo json_encode( $SHOP, JSON_FORCE_OBJECT);
      // exit;


      // CARD test + production PAYMENT MODE
      if( $SHOP['mode_payment'] != 'other' ){


          // DATAS RECIVED FROM CLIENT -> confirmed by Stripe Client
          $payment_id = (string) trim(htmlspecialchars($_POST['payment_id']));

          // one or more empty datas - exit
          if( empty($payment_id) ){
            exit('empty values');
          }

          //  test length values
          if( iconv_strlen($payment_id) > 250  ){
            exit('Don\'t hack me !');
          }


          // get TEST or PRODUCTIONS KEYS IN CONTEXT
          $secret_key = ( $SHOP['mode_payment'] == 'card_test' ) ?
          api::api_crypt( $SHOP['test_priv_key'], 'decr' ) :
          api::api_crypt( $SHOP['prod_priv_key'], 'decr' );

          // create a var to store sale info returned by Stripe
          $SALE_INFOS;

          // CALL STRIPE PHP
          require_once('LIBS/Stripe/init.php');

          // try/catch init. Stripe
          try{

            $stripe = new \Stripe\StripeClient( $secret_key );

          } // error init. Stripe
          catch(\Stripe\Exception\CardException $e) {

            // error
            exit( $e->getError()->message );
          }
          // end try/catch Stripe

          // this return an object not an array
          $SALE_INFOS = $stripe->paymentIntents->retrieve( $payment_id, [] );
          // echo json_encode( $SALE_INFOS, JSON_FORCE_OBJECT );
          // exit;

          // empty $SALE_INFOS case ...
          if( empty($SALE_INFOS) ){
            exit('Sale not found in Stripe');
          }


          // test success payment
          if( $SALE_INFOS->status != 'succeeded'
          || (int) $SALE_INFOS->metadata->sale_id != (int) $SALE['sale_id']
          || (int) $SALE_INFOS->metadata->customer_id != (int) $SALE['customer_id'] ){
            exit('Datas send by Stripe not equals');
          }

          // UPDATE new SALE to 'payed' in BDD
          // $SALE_INFOS->id -> is a string -> $payment_id
          $UPDATE_AS_PAYED =
          	pay_process::update_as_payed( $SALE['sale_id'], $SALE_INFOS->id );

          // sale well updated
          if( boolval($UPDATE_AS_PAYED) == true ){

              // set payed to true
              $SALE['payed'] = true;
          }

      }
      // end CARD test + production PAYMENT MODE


      // DECREMENT QUANTITIES FOR SOLD PRODUCTS
      foreach( $SALE['products_settings'] as $k => $v ){

					// 'incr/decr' -> for each sold products
        	pay_process::update_quant_prod( $v['prod_id'], $v['quant'], 'decr' );

			}

			// record products on pruchased stats
			stats::record_stat_products_purchased( $SALE['products_settings'] );

			// put sale in session for retrieve user new sale during session
      // see program::page_api( );
      // start a session if not exist
      if( session_status() === PHP_SESSION_NONE ){

          session_start([
            'name' => 'PLACIDO-SHOP',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60, // 1 min. by default - no effect if server config
            'gc_maxlifetime' => 60,
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

			// need to mak a session timestamp
      $_SESSION['render_sale_stamp'] = time();

			// store render sale in a session
      $_SESSION['render_sale'] = $SALE;

      // make a hash customer
      $hash_customer =
      api::api_crypt( $SALE['customer_settings']['mail'].$SALE['sale_id'] , 'encr');

      // return direct_sale :
      $tab = array( 'direct_sale' => true,
                    'total_amount_sale_text' => $SALE['amount_text'],
                    'customer_id' => $SALE['customer_id'],
                    'hash_customer' => $hash_customer,
                    'sale_id' => $SALE['sale_id'] );


      echo json_encode($tab, JSON_FORCE_OBJECT);

      // make sale render url link
      $SALE['sale_render_url'] =
      'https://'.HOST.'/sale/'.$SALE['sale_id'].'/'.$hash_customer;

      // SEND ORDER TO CUSTOMER BY EMAIL
      mail::send_order_to_customer( $SALE );

      // ... need to sleep for send multiples e-mails ?
      sleep(2);

      //  SEND NEW SALE ALERT TO SELLER BY EMAIL
      mail::send_notif_new_sale( $SALE );


      unset($SALE);
      unset($_POST);
			// delete session token
			unset($_SESSION['token']);
      exit;
  }
  /**
   * pay_process::confirm_payment( $sale_id, $pay_with );
   */



  /**
   * pay_process::delete_new_sale();
   *
   * @return {void}  delete new sale created before
   * this is for card payement
   */
  public static function delete_new_sale(){


      // able to make 10 requests by day after -> rejected
      pay_process::secure_payment();


      // datas recived for delete new sale
      $sale_id = (int) trim(htmlspecialchars($_POST['sale_id']));
      $customer_id = (int) trim(htmlspecialchars($_POST['customer_id']));
      $hash_customer = (string) trim(htmlspecialchars($_POST['hash_customer']));
      $payment_id = (string) trim(htmlspecialchars($_POST['payment_id']));

      // test too high values
      if( abs($sale_id) > 4000000000
          || abs($customer_id) > 4000000000
          || iconv_strlen($hash_customer) > 600
          || iconv_strlen($payment_id) > 300 ){

          exit('Too long request ...');
      }

      // find new sale
      // get class new_sales::
      require_once dirname(__DIR__).'/'.ADMIN_FOLDER.'/PHP/new_sales.php';

      $SALE = new_sales::get_ONE_sale( $sale_id );

      // print_r( $SALE );
      // exit;

      // test same customer
      if( (int) $SALE['customer_id'] != $customer_id
          || (int) $SALE['sale_id'] != $sale_id
          || $SALE['id_payment'] != $hash_customer  ){

          exit('Bad datas ...');
      }

      // delete customer
      $ARR_pdo = array( 'id' => (int) $SALE['customer_id'] );
      $sql = 'DELETE FROM customers WHERE id=:id';
      $response = false;
      $last_id = false;

      $DELETE_CUSTOMER = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($DELETE_CUSTOMER) == false ){

          exit('New customer not deleted');
      }

      // delete new sale
      $ARR_pdo = array( 'sale_id' => (int) $SALE['sale_id'] );
      $sql = 'DELETE FROM new_sales WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $DELETE_NEW_SALE = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($DELETE_NEW_SALE) == false ){

          exit('New sale not deleted');
      }

      // delete sold products
      $ARR_pdo = array( 'sale_id' => (int) $SALE['sale_id'] );
      $sql = 'DELETE FROM sold_products WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $DELETE_SOLD_PRODUCTS = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($DELETE_SOLD_PRODUCTS) == false ){

          exit('Sold products not deleted');
      }


      // check mode shop -> if shop have card payment,
      // if it mode test card or mode production card
      // test if other mode payment was asked
      // @param  $pay_with = 'CARD' / 'OTHER'
      // return $SHOP[] + key ['mode_payment']
      //                  value  =>  'card_test' / 'card_production' / 'other'
      $SHOP = pay_process::check_mode_payment_shop( 'CARD' );

      // get TEST or PRODUCTIONS KEYS IN CONTEXT
      $secret_key = ( $SHOP['mode_payment'] == 'card_test' ) ?
      api::api_crypt( $SHOP['test_priv_key'], 'decr' ) :
      api::api_crypt( $SHOP['prod_priv_key'], 'decr' );


      // CALL STRIPE PHP
      require_once('LIBS/Stripe/init.php');


      // CREATE STRIPE CLIENT FOR DELETE INTENT
      try{

          $stripe = new \Stripe\StripeClient( $secret_key );

      } // Stripe error
      catch(\Stripe\Exception\CardException $e) {

          // error
          $tab = array( 'error' => true,
                        'message' => $e->getError()->message  );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }
      // end try/catch Stripe

      // this delete the Stripe Payment Intent an return an object
      // of the Intent deleted
      $DELETE_SALE_IN_STRIPE =
      $stripe->paymentIntents->cancel( $payment_id,
        ['cancellation_reason' => 'requested_by_customer']
      );

      // sale well canceled - if want to check
      if( $DELETE_SALE_IN_STRIPE->status == 'canceled' ){

          // $tab['deleted_in_stripe'] = true;

					// return success
					$tab['success'] = true;
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
      }


  }
  /**
   * pay_process::delete_new_sale();
   */



  /**
   * pay_process::update_quant_prod( $id, $number, $to_do );
   *
   * @param  {int} $id        a product id
   * @param  {int} $number    a number to increment or decrement for a value in DB
   * @param  {str} $to_do     'incr/decr'
   * @return {void}           increment or decrement but always return 0 if quant < 0
   */
  public static function update_quant_prod( $id, $number, $to_do ){


      $id = (int) $id;
      $number = (int) $number;

      if( $to_do != 'incr' && $to_do != 'decr' ){
        exit('update_quant_prod => Bad command ...');
      }

      // pass quant number to affect to negative for decrement
      $number = ( $to_do == 'decr' ) ? $number*-1 : $number;

      // UPDATE ONE PROD. QUANT. 'ope' -> calcul operation
      $ARR_pdo = array( 'id' => $id,
                        'ope' => $number );
      $sql = 'UPDATE products SET
      quant = IF(quant+:ope >= 0, quant+:ope, 0) WHERE id=:id';
      $response = false;
      $last_id = false;

      $UPDATE_QUANT_ONE_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($UPDATE_QUANT_ONE_PROD) == false ){

          exit('Unable to set new quantities');
      }

  }
  /**
   * pay_process::update_quant_prod( $id, $number, $to_do );
   */



  /**
   * pay_process::update_as_payed( $sale_id, $payment_id );
   *
   * @param  {int} $sale_id
   * @return {void}   Update a sale as payed -> card payment method
   */
  public static function update_as_payed( $sale_id, $payment_id ){


      $sale_id = (int) $sale_id;
      // this string will be steel in stripe refs like 'id'
      $payment_id = (string) $payment_id;

      // UPDATE ONE PROD. QUANT. 'ope' -> calcul operation
      $ARR_pdo = array( 'sale_id' => $sale_id,
                        'id_card' => $payment_id,
                        'payed' => 1 );
      $sql = 'UPDATE new_sales SET
      id_card=:id_card, payed=:payed WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $UPDATE_AS_PAYED = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($UPDATE_AS_PAYED) == false ){

          exit('Sale not passed as payed');
      }
      else{

          return true;
      }

      // var_dump('updated_as_payed');

  }
  /**
   * pay_process::update_as_payed( $sale_id );
   */



  /**
   * pay_process::update_ref_payment( $sale_id, $hash_customer );
   *
   * @param  {int} $sale_id
   * @param  {str} $hash_customer
   * @return {type}                description
   */
  public static function update_ref_payment( $sale_id, $hash_customer ){


      $sale_id = (int) $sale_id;

      $hash_customer = (string) trim(htmlspecialchars($hash_customer));

      // UPDATE ref payment this set id_payment
      $ARR_pdo = array( 'sale_id' => $sale_id,
                        'id_payment' => $hash_customer );
      $sql = 'UPDATE new_sales SET id_payment=:id_payment WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $UPDATE_REF_PAYMENT = db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   *  pay_process::update_ref_payment( $sale_id, $hash_customer );
   */



  /**
   * pay_process::get_sold_products( $CART );
   *
   * @param  {array}  $CART   user cart send by ajax
   * @return {array}  array( 'PRODS'
   *                         'total_tax_sale'
   *                         'total_amount_sale'  );
   */
  public static function get_sold_products( $CART ){


    $PRODS = array();

    $total_tax_sale = 0;
    $total_amount_sale = 0;

    // get products - verify quant aviable - calcul total cart
    foreach( $CART['items'] as $k => $v ){

        $get_one_prod = process::get_one_product( $v['id'] );

        // error - product not found
        if( boolval($get_one_prod) == false ){

            // error
            $tab = array( 'error' => true,
            'message' => $v['title'].'<br>'.tr::$TR['product_not_found'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }

        $PRODS[$k] = $get_one_prod;
        // append :
        // [id] - prod id
        // [parent_id] - prod id on prods_imgs
        // [name] - name img prez on prods_imgs
        // [order_img] - on prods_imgs
        // [cat_id]
        // [title]
        // [text]
        // [ref]
        // [quant]
        // [price] - in cent
        // [tax] - must be float
        // [date_prod]
        // [url]
        // [on_line]

        // verify quantity
        if( (int) $v['quant_wanted'] > (int) $PRODS[$k]['quant'] ){

            // error
            $tab = array( 'error' => true,
            'message' => $PRODS[$k]['title'].'<br>'.tr::$TR['quantity_unavailable'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }

        // calcul price product
        $p_u = (int) $PRODS[$k]['price']; // int in cent
        $price = $p_u; // keep p_u if no tax
        $quant_wanted = (int) $v['quant_wanted'];

        // is taxed ?
        if( (float) $PRODS[$k]['tax'] != 0 ){


						// same as backend
						$tax = (float) ( (float) $PRODS[$k]['tax'] / 100 );

						// price_tt in cent
						$price = round(($price + ( $price * $tax )), 0); // cent
						$tax_val = $price - $p_u; // cent

            // tax value text - string
            $PRODS[$k]['tax_text'] =
							tools::intl_number( (float) $PRODS[$k]['tax'] );

            // total tax product - float
            $PRODS[$k]['total_tax'] =
							tools::intl_currency( ($tax_val/100) );

            // add to total tax sale
            $total_tax_sale+= $tax_val; // float ? cent
        }

        // FROMAT PRICE - string
        $PRODS[$k]['price_text'] =
					tools::intl_currency( ($price/100) );

        // prod quant wanted
        $PRODS[$k]['quant_wanted'] = $quant_wanted;

        // total price - cent
        $PRODS[$k]['total_price'] = round( ($price * $quant_wanted), 0);

        // total price text
        $PRODS[$k]['total_price_text'] =
					tools::intl_currency( ($PRODS[$k]['total_price']/100) );

        // add to total amount sale - cent
        $total_amount_sale+= $PRODS[$k]['total_price'];

    }
    // loop in products asked

    // print_r( $PRODS );
    // exit;

    // return array
    return array( 'PRODS' => $PRODS,
                  'total_tax_sale' => $total_tax_sale,
                  'total_amount_sale' => $total_amount_sale
                );

  }
  /**
   * pay_process::get_sold_products( $CART );
   */



  /**
   * pay_process::record_customer( $SALE );
   *
   * @return {int}  return (int) $new_customer_id;
   */
  public static function record_customer( $SALE ){


      // INSERT CUSTOMER
      $ARR_pdo = $SALE['customer'];
      $sql = 'INSERT INTO customers (
               firstname,
               lastname,
               tel,
               mail,
               address,
               post_code,
               city,
							 country,
               firstname_sup,
               lastname_sup,
               address_sup,
               post_code_sup,
               city_sup,
						   country_sup )
      VALUES (:firstname,
              :lastname,
              :tel,
              :mail,
              :address,
              :post_code,
              :city,
							:country,
              :firstname_sup,
              :lastname_sup,
              :address_sup,
              :post_code_sup,
              :city_sup,
							:country_sup )';

      $response = false;
      $last_id = true; // RETURN last id

      $new_customer_id = db::server($ARR_pdo, $sql, $response, $last_id);
      // var_dump($new_customer_id);

      // error
      if( empty($new_customer_id) ){

          // error
          $tab = array( 'error' => true,
          'message' => tr::$TR['unable_record_customer'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // return id new customer
      return (int) $new_customer_id;

  }
  /**
   * pay_process::record_customer( $SALE );
   */



  /**
   * pay_process::record_new_sale( $SALE, $new_customer_id );
   *
   * @param  {type} $SALE description
   * @return {int}      return (int) $new_sale_id;
   */
  public static function record_new_sale( $SALE, $new_customer_id ){


      // RECORD NEW SALE
      // set new date
      $timezone = new DateTimeZone(TIMEZONE);
      $date_set = new DateTime('now', $timezone);
      $date_db = $date_set->format('Y-m-d H:i:s'); // FORMAT FOR DATA BASE

      // insert NEW SALE AND RETURN HIS ID
      $ARR_pdo = array( 'sale_id' => '',
                        'customer_id' => $new_customer_id,
                        'amount' => $SALE['total_amount_sale'],
                        'payed' => false,
                        'processed' => false,
                        'date_sale' => $date_db,
                        'id_payment' => 'no',
                        'id_card' => 'no'
                      );

      $sql = 'INSERT INTO new_sales
              ( sale_id,
                customer_id,
                amount,
                payed,
                processed,
                date_sale,
                id_payment,
                id_card )
        VALUES ( :sale_id,
                 :customer_id,
                 :amount,
                 :payed,
                 :processed,
                 :date_sale,
                 :id_payment,
                 :id_card )';

      $response = false;
      $last_id = true; // RETURN last id

      $new_sale_id = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($new_sale_id) == false ){

        // error
        $tab = array( 'error' => true,
        'message' => tr::$TR['unable_record_sale'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
      }

      // return new sale id
      return (int) $new_sale_id;

  }
  /**
   * pay_process::record_new_sale( $SALE, $new_customer_id );
   */



  /**
   * pay_process::record_sold_products( $SALE, $new_sale_id );
   *
   * @param  {array} 	$SALE        	sale array
   * @param  {int} 		$new_sale_id  id of new sale
   * @return {void/json}            record solds products or render json on error
   */
  public static function record_sold_products( $SALE, $new_sale_id ){


    // loop sold_products and add to db
    foreach( $SALE['sold_products'] as $k => $v ){

        // INSERT SOLD_PRODUCTS
        $ARR_pdo = array(
          'sale_id' => (int) $new_sale_id,
          'prod_id' => (int) $v['id'],
          'img_name' => $v['name'],
          'title' => $v['title'],
          'ref' => $v['ref'],
          'quant' => $v['quant_wanted'],
          'price' => $v['price'],
          'tax' => $v['tax'],
          'refounded' => 0,
          'refounded_date' => '',
          'refounded_amount' => 0
        );

        $sql = 'INSERT INTO sold_products
                ( sale_id,
                  prod_id,
                  img_name,
                  title,
                  ref,
                  quant,
                  price,
                  tax,
                  refounded,
                  refounded_date,
                  refounded_amount )
        VALUES ( :sale_id,
                  :prod_id,
                  :img_name,
                  :title,
                  :ref,
                  :quant,
                  :price,
                  :tax,
                  :refounded,
                  :refounded_date,
                  :refounded_amount  )';

        $response = false;
        $last_id = false; // no RETURN last id

        $INSERT_SOLD_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

        if( boolval($INSERT_SOLD_PROD) == false ){

          // error
          $tab = array( 'error' => true,
          'message' => tr::$TR['unable_record_products_sale'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
        }

    }
    // end loop sold_products and add to db


  }
  /**
   * pay_process::record_sold_products( $SALE, $new_sale_id );
   */



  /**
   * pay_process::test_datas_user( $_sup );
   *
   * @return {array}  $CUSTOMER[+_sup]
   */
  public static function test_datas_user( $_sup ){

    $_sup = ( $_sup == '_sup' ) ? '_sup' : '';

    // make an array customer
    $CUSTOMER = array();

    // pass sup by default empty values
    $CUSTOMER['lastname_sup'] = "";
    $CUSTOMER['firstname_sup'] = "";
    $CUSTOMER['address_sup'] = "";
    $CUSTOMER['post_code_sup'] = "";
    $CUSTOMER['city_sup'] = "";
		$CUSTOMER['country_sup'] = "";

    $regex_text_only = '/(`)|(\')|(<)|(>)|(&)|(#)|([0-9])|({)|(})|(\[)|(\])|(\$)/';
    $regex_text = '/(`)|(<)|(>)|({)|(})|(\[)|(\])|(\$)/';

    // ALL EMPTY
    if( empty($_POST) ){

        $tab = array(
        'error' => true,
        'message' => tr::$TR['complete_your_information'] );
        echo json_encode($tab);
        exit;
    }


    // LASTNAME
    $lastname = (string) trim(htmlspecialchars($_POST['lastname'.$_sup.'']));

    // EMPTY LASTNAME
    if( empty($lastname) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_mess_name'] );
        echo json_encode($tab);
        exit;
    }

    // too long request
    if( iconv_strlen($lastname) > 100 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_large_name'] );
        echo json_encode($tab);
        exit;
    }

    // test regex text only
    if( preg_match($regex_text_only, $lastname) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['bad_chars_lastname'] );
        echo json_encode($tab);
        exit;
    }

		// convert to utf-8
		$lastname = mb_convert_encoding( $lastname, "UTF-8",
				mb_detect_encoding($lastname, "auto", true) );

    $CUSTOMER['lastname'.$_sup.''] = ucwords($lastname);

    // END LASTNAME


    // FIRSTNAME
    $firstname = (string) trim(htmlspecialchars($_POST['firstname'.$_sup.'']));

    // EMPTY LASTNAME
    if( empty($firstname) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_firstname'] );
        echo json_encode($tab);
        exit;
    }


    // too long request
    if( iconv_strlen($firstname) > 100 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_large_firstname'] );
        echo json_encode($tab);
        exit;
    }

    // test regex text only
    if( preg_match($regex_text_only, $firstname) ){

      $tab = array( 'error' => true,
      'message' => tr::$TR['bad_chars_firstname'] );
      echo json_encode($tab);
      exit;
    }

		// convert to utf-8
		$firstname = mb_convert_encoding( $firstname, "UTF-8",
				mb_detect_encoding($firstname, "auto", true) );

    $CUSTOMER['firstname'.$_sup.''] = ucwords($firstname);
    // END FIRSTNAME


    // TEL
    if( empty($_sup) ){

      $tel = (string) trim(htmlspecialchars($_POST['tel']));

      // EMPTY TEL
      if( empty($tel) ){

          $tab = array( 'error' => true,
          'message' => tr::$TR['empty_phone'] );
          echo json_encode($tab);
          exit;
      }

      // too long request
      if( iconv_strlen($tel) > 20 ){

          $tab = array( 'error' => true,
          'message' => tr::$TR['too_large_phone'] );
          echo json_encode($tab);
          exit;
      }

      // REPLACE BAD CHARS TEL
      $tel = str_replace([' ', '.', '-', '(', ')'], '', $tel);

      // bad format
      if( preg_match('/[A-z]/', $tel) ){

          $tab = array( 'error' => true,
          'message' => tr::$TR['bad_phone_format'] );
          echo json_encode($tab);
          exit;
      }

      $CUSTOMER['tel'] = $tel;
    }
    // END TEL


    // VERIFY E-MAIL
    if( empty($_sup) ){

      $mail = (string) trim(htmlspecialchars($_POST['mail']));

      // EMPTY
      if( empty($mail) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_mail']);
        echo json_encode($tab);
        exit;

      }

      // too long request
      if( iconv_strlen($mail) > 100 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_large_mail'] );
        echo json_encode($tab);
        exit;
      }

      // IF BAD FORMAT
      if( filter_var($mail, FILTER_VALIDATE_EMAIL) == false ){

          $tab = array( 'error' => true,
          'message' => tr::$TR['invalid_mail'] );
          echo json_encode($tab);
          exit;
      }

      // pass in fliter
      $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);

      $CUSTOMER['mail'] = $mail;
    }
    // END E-MAIL


    // VERIFY ADDRESS
    $address =
		(string) trim(htmlspecialchars($_POST['address'.$_sup.''], ENT_NOQUOTES , 'UTF-8'));

    // EMPTY
    if( empty($address) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_address'] );
        echo json_encode($tab);
        exit;

    }

    // too long request
    if( iconv_strlen($address) > 250 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_large_address'] );
        echo json_encode($tab);
        exit;
    }

    // test regex text
    if( preg_match($regex_text, $address) ){

      $tab = array( 'error' => true,
      'message' => tr::$TR['bad_chars_address'] );
      echo json_encode($tab);
      exit;
    }

		// convert to utf-8
		$address = mb_convert_encoding( $address, "UTF-8",
				mb_detect_encoding($address, "auto", true) );

    $CUSTOMER['address'.$_sup.''] = $address;
    // END ADDRESS


    // POST CODE
    $post_code = (string) trim(htmlspecialchars($_POST['post_code'.$_sup.'']));

    // EMPTY POST CODE
    if( empty($post_code) ){

				// SOME CONTRIES DON'T HAVE POST CODE !
				$post_code = '';
    }

    // too long request
    if( !empty($post_code) && iconv_strlen($post_code) > 50 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_long_post_code'] );
        echo json_encode($tab);
        exit;
    }

    $CUSTOMER['post_code'.$_sup.''] = $post_code;
    // END POST CODE

    // CITY
    $city = (string) trim(htmlspecialchars($_POST['city'.$_sup.''], ENT_NOQUOTES , 'UTF-8'));

    // EMPTY CITY
    if( empty($city) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_city'] );
        echo json_encode($tab);
        exit;

    }

    // too long request
    if( iconv_strlen($city) > 100 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['too_long_city'] );
        echo json_encode($tab);
        exit;
    }
    // END CITY

    // test regex text
    if( preg_match($regex_text, $city) ){

      $tab = array( 'error' => true,
      'message' => tr::$TR['bad_city_format'] );
      echo json_encode($tab);
      exit;
    }

		// convert in utf-8
		$city = mb_convert_encoding( $city, "UTF-8",
				mb_detect_encoding($city, "auto", true) );

    $CUSTOMER['city'.$_sup.''] = $city;
		// END CITY


    // COUNTRY
    $country = (string) trim(htmlspecialchars($_POST['country'.$_sup.'']));

    // EMPTY COUNTRY
    if( empty($country) ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['empty_country'] );
        echo json_encode($tab);
        exit;

    }


    // too long request ( i. js max value.length = 58 )
    if( iconv_strlen($country) > 100 ){

        $tab = array( 'error' => true,
        'message' => tr::$TR['(too_long_country)'] );
        echo json_encode($tab);
        exit;
    }

    // test regex text
    if( preg_match($regex_text, $country) ){

      $tab = array( 'error' => true,
      'message' => tr::$TR['bad_country_format'] );
      echo json_encode($tab);
      exit;
    }

		// convert in utf-8
		$country = mb_convert_encoding( $country, "UTF-8",
				mb_detect_encoding($country, "auto", true) );

    $CUSTOMER['country'.$_sup.''] = $country;
    // END COUNTRY



    return $CUSTOMER;

  }
  /**
   * pay_process::test_datas_user( $_sup );
   */



  /**
   * pay_process::check_mode_payment_shop( $pay_with );
   *
   * @param  {string} $pay_with     'CARD' / 'OTHER'
   * @return {array}  return $SHOP + key ['mode_payment']
   *                            =>  'card_test' / 'card_production' / 'other'
   */
  public static function check_mode_payment_shop( $pay_with ){

      // get shop settings
      // fetch PUBLIC shop key
      $ARR_pdo = array('id' => 0);
      $sql = 'SELECT * FROM user_shop WHERE id=:id';
      $response = 'one';
      $last_id = false; // no RETURN last id
      $SHOP = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($SHOP) == false ){

          // error
          $tab = array( 'error' => true,
                        'message' => tr::$TR['error_retry'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // add a key mode payment
      $SHOP['mode_payment'] = '';

      // test card mode
      if( $pay_with == 'CARD' ){


          // test TEST MODE CARD - check if is in catalog mode
          if( !empty($SHOP['test_pub_key'])
              && !empty($SHOP['test_priv_key'])
              && boolval($SHOP['mode']) == true ){

              $SHOP['mode_payment'] = 'card_test';
          }

          // test TEST PRODUCTION CARD
          if( !empty($SHOP['prod_pub_key'])
              && !empty($SHOP['prod_priv_key'])
              && boolval($SHOP['mode']) == true ){

              $SHOP['mode_payment'] = 'card_production';
          }

          // no keys but card payment asked -> error
          if( empty($SHOP['mode_payment']) || boolval($SHOP['mode']) == false ){

              // error
              $tab = array( 'error' => true,
              'message' => tr::$TR['unable_init_payment'] );
              echo json_encode($tab, JSON_FORCE_OBJECT);
              exit;
          }


      }
      // END  test card mode

      // test OTHER PAYMENT MODE
      if( $pay_with == 'OTHER' ){


          if( boolval($SHOP['by_money']) == true
              && boolval($SHOP['mode']) == true ){

              $SHOP['mode_payment'] = 'other';
          }
          else{

              // error - something was wrong ...
              $tab = array( 'error' => true,
                            'message' => tr::$TR['error_retry'] );
              echo json_encode($tab, JSON_FORCE_OBJECT);
              exit;
          }

      }
      // test OTHER PAYMENT MODE


      // finally return SHOP
      return $SHOP;

  }
  /**
   * pay_process::check_mode_payment_shop( $pay_with );
   */



  /**
   * pay_process::secure_payment();
   *
   * SECURE PAYMENT PROCESS
   * able user to make only 10 card payment OR ABORT by day
   * store IP rejected in DB if user try > +10 payment
   * -> block totaly user IP for card payment when IP was recorded in DB
   *
   * @return {json}  error message
   */
  public static function secure_payment(){


      // 1 - Connection is not on https
      // ACCEPT ONLY HTTPS CONNECTIONS
      if( $_SERVER['HTTPS'] != 'on' ){

          $tab = array( 'error' => true,
                        'message' => 'Securized connection required' );

          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }


      // collect, validate and test ip of visitor
      // return false -> ban OR IP of user -> not ban
      $ip_user = process::test_ip_rejected();


      // 2 - IP user is not correct
      // if IP was evaluated at false
      if( boolval($ip_user) == false ){

          $tab = array( 'error' => true,
                        'message' => tr::$TR['unable_init_payment'].'<br>'
												.tr::$TR['please_contact_us'] );

          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // start a session if not exist
      if( session_status() === PHP_SESSION_NONE ){

          session_start([
              'name' => 'PLACIDO-SHOP',
              'use_strict_mode' => true,
              'cookie_samesite' => 'Strict',
              'cookie_lifetime' => 86400, // 1 day
              'gc_maxlifetime' => 86400,
              'cookie_secure' => true,
              'cookie_httponly' => true
          ]);
      }

      // if session ['CARD_RETRY'] no exist - create it
      if( !isset($_SESSION['CARD_RETRY']) ){

          $_SESSION['CARD_RETRY'] = 1;
      }
      else{
          // increment tries
          $_SESSION['CARD_RETRY'] += 1;
      }

      // 3 - IP try too much more payment
      // nb payment tries is over nb permitted
      if( $_SESSION['CARD_RETRY'] > 10 ){

          // re-init to 10 value -> it must become a very big int otherwise
          $_SESSION['CARD_RETRY'] = 10;

          // store IP rejected in DB
          process::record_ip_rejected( $ip_user );

          // return error
          $tab = array( 'error' => true,
                        'message' => tr::$TR['payment_quota_exceeded'].'<br>'
												.tr::$TR['please_contact_us'] );

          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }

  }
  /**
   * pay_process::secure_payment();
   */


}
// END CLASS pay_process::




?>
