<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2021-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	new_sales.php
 *
 * new_sales::get_fresh_sales();
 * new_sales::get_new_sales_user();
 * new_sales::get_sold_products( $sale_id );
 * new_sales::get_ONE_sale( $sale_id ); // used
 * new_sales::suppr_new_sale();
 *
 */

class new_sales {



  /**
   * new_sales::get_fresh_sales();
	 *
   * Get fresh sales on demand
	 *
	 * @return {json} :
	 *
   * @return {bool} 		success
	 * @return {array} 		products
	 * @return {array} 		new_sales
	 * @return {int} 			nb_new_sales
	 * @return {string} 	total_amount_shop
	 *
   */
  public static function get_fresh_sales(){


      $token = trim(htmlspecialchars($_POST['token']));
      // verify token
      program::verify_token($token);

      $tab['success'] = true;

      // renew products for decrem new sales
      $tab['products'] = products::get_products( cats::get_all_categories() );

      // return an array :
			// [ new_sales[] , 'total_amount_shop' , 'nb_new_sales' ]
      $GET_new_sales = new_sales::get_new_sales_user();

      // get fresh new sales
      $tab['new_sales'] = $GET_new_sales['new_sales']; // array

			// nb new sales
			$tab['nb_new_sales'] = $GET_new_sales['nb_new_sales'];

			// get new total amout in text
      $tab['total_amount_shop'] = $GET_new_sales['total_amount_shop'];

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

  }
  /**
   * new_sales::get_fresh_sales();
   */



  /**
   * new_sales::get_new_sales_user();
   *
   * @return {array}  'new_sales'
   * @return {str}    'total_amount_shop'
   * @return {int}    'nb_new_sales'
   */
  public static function get_new_sales_user(){


      // GET ALL NEW SALES OF AN USER
      $ARR_pdo = false;
      $sql = 'SELECT * FROM new_sales ORDER BY sale_id';
      $response = 'all';
      $last_id = false;
      //  ->  fetchAll
      $NEW_SALES = db::server($ARR_pdo, $sql, $response, $last_id);

      $TOTAL_AMOUNT_SHOP = 0;

      // empty new sales
      if( count($NEW_SALES) == 0 ){

          return array(
            'new_sales' => array(), // pass an empty array
            'total_amount_shop' => tools::intl_currency( 0 ),
            'nb_new_sales' => 0
          );
      }

			$TimeZone = new DateTimeZone(TIMEZONE);

			// LOOP
      foreach($NEW_SALES as $k => $v){

          // add an index -> cool for get object directly
          $NEW_SALES[$k]['index'] = $k;

          // well format date
					$Date_sale = new DateTime( $v['date_sale'], $TimeZone );
			    $stamp = $Date_sale->getTimestamp();
					$date_locale = tools::format_date_locale( $Date_sale, 'FULL' , 'SHORT', null );

          $NEW_SALES[$k]['date_sale'] = $stamp;
          $NEW_SALES[$k]['date_format'] = ucwords($date_locale);

          // FORMAT AMOUNT
          $amount_float = (float) ( (int) $v['amount'] / 100 ); // not in cent

					$NEW_SALES[$k]['amount_text'] = tools::intl_currency( $amount_float );

          // PAYED
          $NEW_SALES[$k]['payed'] = boolval($v['payed']);

          // processed
          $NEW_SALES[$k]['processed'] = boolval($v['processed']);

          // FETCH CUSTOMER
          $customer_id = (int) $v['customer_id'];
          $NEW_SALES[$k]['customer_settings'] = process::get_one_customer($customer_id);
          // adress sup true/false
          $NEW_SALES[$k]['sup'] =
          ( empty($NEW_SALES[$k]['customer_settings']['address_sup']) ) ? false : true;

          // FECTH SOLD PRODUCTS
          $sale_id = (int) $v['sale_id'];

          // GET SOLD PRODUCTS - by row products
          // i. new_sales::get_sold_products( sale_id );
          // * @return 'products_settings' -> array of products purchased
					// * @return 'refounded' -> bool
					// * @return 'refounded_amount' -> int.
					// * @return 'refounded_date' ->
					// * @return 'total_amount_sale' -> str. formated not in cent ex. "0,00"
					// * @return 'total_old_amount_sale' -> str. formated not in cent
					// * @return 'total_refounded_amount' -> str. formated not in cent
          // * @return 'total_tax_sale' -> str. formated not in cent
          $GET_SOLD_PRODUCTS = new_sales::get_sold_products( $sale_id );

					// echo json_encode( $GET_SOLD_PRODUCTS, JSON_NUMERIC_CHECK );
					// exit;

          $NEW_SALES[$k]['products_settings'] =
						$GET_SOLD_PRODUCTS['products_settings'];

          $NEW_SALES[$k]['total_tax_sale'] =
						$GET_SOLD_PRODUCTS['total_tax_sale'];

          $NEW_SALES[$k]['total_amount_sale'] =
						$GET_SOLD_PRODUCTS['total_amount_sale'];

          $TOTAL_AMOUNT_SHOP += (int) $v['amount'];

      }
      // END LOOP


      // FINAL RETURN
      return array(
        'new_sales' => $NEW_SALES,
        'total_amount_shop' => tools::intl_currency( ($TOTAL_AMOUNT_SHOP/100) ),
        'nb_new_sales' => count($NEW_SALES)
      );

  }
  /**
   * new_sales::get_new_sales_user();
   */



  /**
   * new_sales::get_sold_products();
	 *
	 * Get all products purchased for ONE sale
   *
   * @param  {int} $sale_id  new sale id
	 *
   * @return 'products_settings' -> array of products purchased
	 * @return 'refounded' -> bool
	 * @return 'refounded_amount' -> int.
	 * @return 'refounded_date' ->
	 * @return 'total_amount_sale' -> str. formated not in cent ex. "0,00"
	 * @return 'total_old_amount_sale' -> str. formated not in cent
	 * @return 'total_refounded_amount' -> str. formated not in cent
	 * @return 'total_tax_sale' -> str. formated not in cent
	 *
   */
  public static function get_sold_products( $sale_id ){


      $ARR_pdo = array( 'sale_id' => (int) $sale_id );
      $sql = 'SELECT * FROM sold_products WHERE sale_id=:sale_id';
      $response = 'all';
      $last_id = false;
      //  ->  fetchAll
      $SOLD_PRODS = db::server($ARR_pdo, $sql, $response, $last_id);

      $total_taxes = 0;
      $total_amount = 0;
      $is_refounded = false;
      $refounded_date = '1978-02-28';
      $refounded_amount = 0;
      $TimeZone = new DateTimeZone(TIMEZONE);
      // get total refounded amounts rows
      $total_refounded_amount = 0;
      // get the old amont sale
      $total_old_amount_sale = 0;

      // loop for format pu / pu_tt
      foreach( $SOLD_PRODS as $k2 => $v2 ){


          // default tax_text
          $SOLD_PRODS[$k2]['tax_text'] = '0.00';

          // no tax - rep. for template
          if( (float) $v2['tax'] == 0 ){

              $SOLD_PRODS[$k2]['tax'] = false;
          }

          $p_u = (int) $v2['price']; // in cent
          $price = $p_u; // in cent
          $quant = (int) $v2['quant'];
          $refounded = boolval( $v2['refounded'] );

          // the sale contain one product row refounded ?
          $is_refounded = ( $refounded == true ) ? true : $is_refounded;

          // refounded amount must be 0
          $refounded_amount = (int) $v2['refounded_amount']; // in cent must be 0

          // get the latest refounded row
          $refounded_date =
          ( $refounded == true
            && strtotime($v2['refounded_date']) > strtotime($refounded_date)
          ) ? $v2['refounded_date'] : $refounded_date;

          $old_pu_tt = 0;

          // calcul tax - set: 'tax_text' && 'total_tax_row'
          if( $SOLD_PRODS[$k2]['tax'] != false ){

              // format tax text in 0.00
              $SOLD_PRODS[$k2]['tax_text'] = tools::intl_number( $v2['tax'] );

              // override price with price + tax
              $tax = (float) ( round($v2['tax']/100 ,2) ); // get 0.2
							// pu + tax now $price = pu_tt in cent
              $price = $price + round( ( $price * $tax ), 0);

              // amount row products + tax + quant. in cent
              $amount = $price * $quant;

              // affect old price + tax + quant
              $old_pu_tt = $amount;

              // is refounded ?
              if( $refounded == true ){

                  $total_refounded_amount += $refounded_amount;

                  // set new amount
                  $amount = $amount - $refounded_amount;

                  // calc new pu
                  $calc = round( ($amount / $quant) , 2 );

                  $new_pu = round( (($calc) / ( 1+$tax )) , 2 );

                  $SOLD_PRODS[$k2]['refounded_pu_text'] =
										tools::intl_currency( ($new_pu/100) );

                  $calc_diff = $p_u - $new_pu;

                  $SOLD_PRODS[$k2]['refounded_pu_diff'] =
										tools::intl_currency( ($calc_diff/100) );

                  // calcul tax for all quantities
                  $calcul_tax = round( ($new_pu*$quant) * $tax, 0 ); // in cent
              }
              else{

                  // taxed but not refounded
                  // calcul tax for all quantities
                  $calcul_tax = round( ($p_u*$quant) * $tax, 0 ); // in cent
              }

              // add to total tax
              $total_taxes += $calcul_tax; // in cent

              // render tax in text NOT IN CENT
              $tax_val_text = (float) ( $calcul_tax / 100 );

              // total tax for one row product * quantity
              $SOLD_PRODS[$k2]['total_tax_row'] =
								tools::intl_currency( $tax_val_text );

              // in int.
              $SOLD_PRODS[$k2]['total_tax_int'] = $calcul_tax;
          }
          // end  calcul tax
          else{
              // no tax

              // amount product in cent
              $amount = $price * $quant;

              // affect old price + quant
              $old_pu_tt = $amount;

              // is refounded ?
              if( $refounded == true ){

                  $total_refounded_amount += $refounded_amount;

                  // set new amount
                  $amount = $amount - $refounded_amount;

                  // calc new pu
                  $new_pu = round( ($amount / $quant) , 2 );

                  $SOLD_PRODS[$k2]['refounded_pu_text'] =
										tools::intl_currency( ($new_pu/100) );

                  $calc_diff = $p_u - $new_pu;

                  $SOLD_PRODS[$k2]['refounded_pu_diff'] =
										tools::intl_currency( ($calc_diff/100) );

              }

          }
          // no tax


          // PU NOT IN CENT
          $SOLD_PRODS[$k2]['PU_text'] =
						tools::intl_currency( ($p_u/100) );

          $SOLD_PRODS[$k2]['PU_TT'] =
						tools::intl_currency( ($price/100) );

          // total_old_row_text NOT IN CENT
          $SOLD_PRODS[$k2]['total_old_row_text'] =
						tools::intl_currency( ($old_pu_tt/100) );

          // new total row NOT IN CENT
          $SOLD_PRODS[$k2]['total_new_row_text'] =
						tools::intl_currency( (($old_pu_tt-$refounded_amount)/100) );

          // refounded_text
          $SOLD_PRODS[$k2]['refounded_amount_text'] =
						tools::intl_currency( ($refounded_amount/100) );

          // total amount in cent
          $total_amount += $amount;

          // old total amount in cent
          $total_old_amount_sale += $old_pu_tt;

      }
      // end loop for format

      // make sure to not divide a 0 number
      $total_taxes_no_cent = ( $total_taxes != 0 )
      ? tools::intl_currency( ($total_taxes/100) )
      : false;

      $total_amount_no_cent = ( $total_amount != 0 )
      ? tools::intl_currency( ($total_amount/100) )
      : tools::intl_currency( $total_amount ); // get 0.00

      // last refounded date
      $Last_Date_Refound =
      DateTime::createFromFormat(
        'Y-m-d', $refounded_date, $TimeZone );

      $refounded_amount_text = ( $refounded_amount == 0 )
			? tools::intl_currency( 0 )
			: tools::intl_currency( ($refounded_amount/100) );

      return array(

			  'products_settings' => $SOLD_PRODS,
        // str not in cent
        'total_tax_sale' => $total_taxes_no_cent, // OR false
        // str not in cent
        'total_amount_sale' => $total_amount_no_cent,
        // str not in cent
        'total_old_amount_sale' => tools::intl_currency( ($total_old_amount_sale/100) ),
        'refounded' => $is_refounded, // bool
        'refounded_amount' => $total_refounded_amount, // cent
        // str not in cent
        'total_refounded_amount' => tools::intl_currency( ($total_refounded_amount/100) ),
        // last refounded date SHORT
        'refounded_date' =>
					tools::format_date_locale( $Last_Date_Refound, 'SHORT' , 'NONE', null )

      );

       // * @return 'products_settings' -> array of products
       // * @return 'total_tax_sale' -> str. formated not in cent
       // * @return 'total_amount_sale' -> str. formated not in cent
       // * @return 'total_old_amount_sale' -> str. formated not in cent
       // * @return 'refounded' -> bool
       // * @return 'refounded_amount' -> int.
       // * @return 'total_refounded_amount' -> str.
       // * @return 'refounded_date'

  }
  /**
   * new_sales::get_sold_products( $sale_id );
   */



  /**
   * new_sales::get_ONE_sale( $sale_id );
   *
   * @return {array}:
   * @return {int}    amount // in cent
   * @return {str}    amount_text
   * @return {int}    customer_id
   * @return {array}  customer_settings
   * @return {str}    date_sale
   * @return {str}    id_card
   * @return {str}    id_payment
   * @return {bool}   payed
   * @return {array}  products_settings
   * @return {int}    sale_id
   * @return {bool}   sup // address sup
   * @return {str}    total_tax_sale
   * @return {bool}   processed
   *
   */
  public static function get_ONE_sale( $sale_id ){

    // GET ONE SALE
    (int) $sale_id;

    $ARR_pdo = array( 'sale_id' => $sale_id );
    $sql = 'SELECT * FROM new_sales WHERE sale_id=:sale_id';
    $response = 'one';
    $last_id = false;
    //  ->  fetch
    $SALE = db::server($ARR_pdo, $sql, $response, $last_id);

    // empty new sales
    if( boolval($SALE) == false ){

        $tab = array('error' => tr::$TR['new_sale_not_found'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }


    // well format date
    $TimeZone = new DateTimeZone(TIMEZONE);
    $Date_sale = new DateTime( $SALE['date_sale'], $TimeZone );
    $stamp = $Date_sale->getTimestamp();
		$date_locale = tools::format_date_locale( $Date_sale, 'FULL' , 'SHORT', null );
    $SALE['date_sale'] = $stamp;
    $SALE['date_parsed'] = $date_locale;

    // FORMAT AMOUNT
    $SALE['amount_text'] = tools::intl_currency( ($SALE['amount']/100) );

    $SALE['amount'] = (int) $SALE['amount'];

    // PAYED
    $SALE['payed'] = boolval($SALE['payed']);

    // processed
    $SALE['processed'] = boolval($SALE['processed']);

    // FETCH CUSTOMER
    $customer_id = (int) $SALE['customer_id'];
    $SALE['customer_settings'] = process::get_one_customer($customer_id);
    // adress sup true/false
    $SALE['sup'] =
    ( empty($SALE['customer_settings']['address_sup']) ) ? false : true;

    // FECTH SOLD PRODUCTS
    // i. new_sales::get_sold_products( sale_id );
    // * @return 'products_settings' -> array of products
    // * @return 'total_tax_sale' -> str. formated not in cent
    // * @return 'total_amount_sale' -> str. formated not in cent
    // * @return 'total_old_amount_sale' -> str. formated not in cent
    // * @return 'refounded' -> bool
    // * @return 'refounded_amount' -> int.
    // * @return 'total_refounded_amount' -> str.
    // * @return 'refounded_date'
    $GET_SOLD_PRODUCTS = new_sales::get_sold_products($sale_id);

    $SALE['products_settings'] = $GET_SOLD_PRODUCTS['products_settings'];

    $SALE['total_tax_sale'] = $GET_SOLD_PRODUCTS['total_tax_sale'];


    // RETURN
    return $SALE;

  }
  /**
   * new_sales::get_ONE_sale();
   */



  /**
   * new_sales::suppr_new_sale();
   *
   * @return {array}  products[]
   * @return {array}  new_sales[]
	 * @return {bool}  	success
   * @return {str}    total_amount_shop -> in string formatted not in cent
   */
  public static function suppr_new_sale(){


      // verify token
      $token = trim(htmlspecialchars( $_POST['token'] ));
      program::verify_token( $token );

      $sale_id = (int) trim(htmlspecialchars( $_POST['sale_id'] ));

      // get sale to delete to recover customer data
      $ARR_pdo = array( 'sale_id' => $sale_id );
      $sql = 'SELECT * FROM new_sales WHERE sale_id=:sale_id';
      $response = 'one';
      $last_id = false;

      $NEW_SALE = db::server($ARR_pdo, $sql, $response, $last_id);

      // var_dump($NEW_SALE);

      // error fetch new sale
      if( empty($NEW_SALE) ){

          $tab = array('error' => tr::$TR['new_sale_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


      // DELETE CUSTOMER
      $ARR_pdo = array( 'id' => (int) $NEW_SALE['customer_id'] );
      $sql = 'DELETE FROM customers WHERE id=:id';
      $response = false;
      $last_id = false;

      $DELETE_CUSTOMER = db::server($ARR_pdo, $sql, $response, $last_id);

      // error delete customer
      if( boolval($DELETE_CUSTOMER) == false ){

          $tab = array('error' => tr::$TR['error_on_delete_customer'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


      // GET SOLD PRODUCTS FOR THIS SALE
      $ARR_pdo = array( 'sale_id' => $sale_id );
      $sql = 'SELECT * FROM sold_products WHERE sale_id=:sale_id';
      $response = 'all';
      $last_id = false;

			// GET SOLD PRODUCTS
      $ARR_sold_products = db::server($ARR_pdo, $sql, $response, $last_id);

			// empty sold products
			if( empty($ARR_sold_products) ){

					// error
          $tab['error'] = tr::$TR['products_new_sale_not_found'];
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

      // - RE-ADJUST QUANTITIES
      // loop over sold products
      foreach( $ARR_sold_products as $k => $v ){

          // re-affect quantities
          $ARR_pdo = array(
            'id' => (int) $v['prod_id'] ,
            'add_quant' => (int) $v['quant']
          );

          $sql = 'UPDATE products
					SET quant = quant + :add_quant WHERE id=:id';
          $response = false;
          $last_id = false;

					// re-affect quantities for each products previously sold
          $UPDATE_QUANT_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

					// error update quantities of sold products
					if( boolval($UPDATE_QUANT_PROD) != true ){

							// error
		          $tab['error'] = tr::$TR['udpate_quantities_failed'];
							echo json_encode($tab, JSON_FORCE_OBJECT);
							exit;
					}
      }
      // end loop


      // DELETE ALL SOLD PRODUCTS
      $ARR_pdo = array( 'sale_id' => $sale_id );
      $sql = 'DELETE FROM sold_products WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $DELETE_SOLD_PRODUCTS = db::server($ARR_pdo, $sql, $response, $last_id);

			// error delete sold products
			if( boolval($DELETE_SOLD_PRODUCTS) != true ){

					// error
					$tab['error'] = tr::$TR['error_delete_sold_products'];
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}


      // DELETE NEW SALE
      $ARR_pdo = array( 'sale_id' => $sale_id );
      $sql = 'DELETE FROM new_sales WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;

      $DELETE_NEW_SALE = db::server($ARR_pdo, $sql, $response, $last_id);

      // ERROR
      if( boolval($DELETE_NEW_SALE) != true ){

          $tab['error'] = tr::$TR['error_delete_new_sale'];
					echo json_encode($tab, JSON_NUMERIC_CHECK);
					exit;
      }

			// SUCCESS : return fresh sales
			// this return json + success => true i. not a string
			new_sales::get_fresh_sales();


  }
	/*
	 * new_sales::suppr_new_sale();
	 */



}
// END class new_sales::

?>
