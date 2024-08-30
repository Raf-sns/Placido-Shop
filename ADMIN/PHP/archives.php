<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	 archives.php
 *
 *	const ARCHIVES_INCR
 *
 * archives::archive_vendor_sale();
 * archives::get_archives_shop( $limit );
 * archives::load_more_archives();
 * archives::get_ONE_archive( $sale_id );
 * archives::send_bill_from_archives();
 * archives::update_archive_as_payed();
 * archives::refound_vendor_sale();
 * archives::search_in_archives();
 * archives::search_by_date( $value );
 * archives::search_by_number( $value );
 * archives::search_by_customer( $value );
 *
 */

	// archive increment loader
	const ARCHIVES_INCR = 5;

class archives {


  /**
   * archives::archive_vendor_sale();
   *
   * @return {json}  get fresh archives + some template settings
	 *
	 * @return $tab['archives'] = array()
	 * @return $tab['new_sales'] = array()
	 * @return $tab['template']['load_more_archives'] = true /false
	 * @return $tab['template']['total_amount_shop'] = float
	 * @return $tab['template']['nb_new_sales'] = int.
	 * @return $tab['success'] = str.
	 *
   */
  public static function archive_vendor_sale(){


      // verify token
      token::verify_token();

      // sale_id
      $sale_id = (int) trim(htmlspecialchars($_POST['sale_id']));

      // GET NEW SALE TO TREAT
      $SALE = new_sales::get_ONE_sale( $sale_id );

      // echo json_encode($SALE);
      // exit;

      // - ABLE to store NOT PAYED SALE
      // // IF A SALE IS NOT PAYED DON'T SEND IT TO ARCHIVES
      // // payed -> 'set_payed' / 'not_payed'
      // if( isset($_POST['payed'])
      // && trim(htmlspecialchars($_POST['payed'])) == 'not_payed'
      // && boolval($SALE['payed']) == false ){
      //
      //     // error
      //     $tab = array('error' => 'Vous ne pouvez pas archiver une vente non-payée.');
      //     echo json_encode($tab);
      //     exit;
      // }

      // set payed if notified as payed / by default return value payed origin
      $payed =
			(  isset($_POST['payed'])
      	&& trim(htmlspecialchars($_POST['payed'])) == 'set_payed'
      	&& (int) $SALE['payed'] == 0  )  ?  1  :  (int) $SALE['payed'];

      // var_dump( $SALE );
      // exit;

      // FETCH LAST ARCHIVED SALE
      $ARR_pdo = false;
      $sql = 'SELECT sale_number FROM archived_sales ORDER BY sale_number DESC';
      $response = 'one';
      $last_id = false;

      $LAST_NUMBER_ARCHIVED = db::server($ARR_pdo, $sql, $response, $last_id);

      // $LAST_NUMBER_ARCHIVED - casted - on fresh install boolval == false
      $last_nb = ( boolval($LAST_NUMBER_ARCHIVED) == false )
			? 0 : (int) $LAST_NUMBER_ARCHIVED['sale_number'];

      // increment bill numbers in order
      $last_nb++;

      // SET CUSTOMER - in json
      $customer = json_encode($SALE['customer_settings']);

      // SET CUSTOMER SUP - in json
      if( !empty($SALE['customer_settings']['address_sup']) ){

        	$customer_sup = json_encode($SALE['customer_settings']);
      }
      else{
        	$customer_sup = '';
      }

      // DATE ARCHIVE + date mail
      $timezone = new DateTimeZone(TIMEZONE);
      $date_now_obj = new DateTime('now', $timezone);
			// format date for DataBase
      $date_now = $date_now_obj->format('Y-m-d');

      // INSERT ARCHIVE
      $ARR_pdo = array(
      'sale_number' => $last_nb,
      'sale_id' => (int) $SALE['sale_id'],
      'customer' => $customer,
      'customer_sup' => $customer_sup,
      'payed' => $payed,
      'processed' => 1,
      'date_sale' => $date_now,
      'id_payment' => $SALE['id_payment'],
      'id_card' => $SALE['id_card']
      );

      $sql = 'INSERT INTO archived_sales
      ( sale_number,
        sale_id,
        customer,
        customer_sup,
        payed,
        processed,
        date_sale,
        id_payment,
        id_card )
        VALUES ( :sale_number,
          :sale_id,
          :customer,
          :customer_sup,
          :payed,
          :processed,
          :date_sale,
          :id_payment,
          :id_card )';

      $response = false;
      $last_id = false;

      // INSERT ARCHIVE
      $INSERT_ARCHIVE = db::server($ARR_pdo, $sql, $response, $last_id);

      // ERROR
      if( boolval($INSERT_ARCHIVE) == false ){

          // error
          $tab = array('error' => tr::$TR['error_create_archive'] );
          echo json_encode($tab);
          exit;
      }


      // COMMENT FOR TEST -> not suppr new sale
      // SUPPRESS NEW SALE
      $ARR_pdo = array( 'sale_id' => $sale_id );

      $sql = 'DELETE FROM new_sales WHERE sale_id=:sale_id';
      $response = false;
      $last_id = false;
      $SUPPRESS_NEW_SALE = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($SUPPRESS_NEW_SALE) == false ){

          // error
          $tab = array('error' => tr::$TR['error_delete_command'] );
          echo json_encode($tab);
          exit;
      }


      // DELETE OLD CUSTOMER TABLE
      $ARR_pdo = array( 'id' => (int) $SALE['customer_id'] );
      $sql = 'DELETE FROM customers WHERE id=:id';
      $response = false;
      $last_id = false;

			$DELETE_CUSTOMER = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($DELETE_CUSTOMER) == false ){

          // error
          $tab = array('error' => tr::$TR['error_on_delete_customer'] );
          echo json_encode($tab);
          exit;
      }


			// RETURN A RESPONSE BEFORE SENDING MAIL
			$tab = array();

			// get new archives
			$tab['archives'] = archives::get_archives_shop( $limit='0,'.ARCHIVES_INCR.'' );
			$tab['template']['load_more_archives'] =
			( count($tab['archives']) < ARCHIVES_INCR ) ? false : true;

			// Pass info nb archives are loaded
			$tab['template']['load_more_archives_incr'] = ARCHIVES_INCR;

			// get fresh new sales
			// return an array :  [ new_sales[] , 'total_amount', 'total_amount_shop' ]
			$GET_new_sales = new_sales::get_new_sales_user();
			$tab['new_sales'] = $GET_new_sales['new_sales'];

			// set datas for template
			$tab['template']['total_amount_shop'] = $GET_new_sales['total_amount_shop'];
			$tab['template']['nb_new_sales'] = count($GET_new_sales['new_sales']);

			// success
			$tab['success'] = tr::$TR['order_well_archived'];

			echo json_encode( $tab, JSON_NUMERIC_CHECK );



      // IF ASK TO SEND MAILS -> get shop settings
      if( isset($_POST['send_mail_confirm_treatment'])
          || isset($_POST['send_bill_by_mail'])  ){

          // get shop for mail settings - NEED THIS !
          $SHOP = shop::get_shop();
      }

      // SEND BILL BY MAIL IF IS ASKED
      if( isset($_POST['send_bill_by_mail']) ){

          // date now for bill
					$date_now_format =
						tools::format_date_locale( $date_now_obj, 'SHORT' , 'NONE', null );

					// rep. for template
          $rep_tva = ( boolval($SALE['total_tax_sale']) == false ) ? false : true;

          // state for payed / un-payed SALE
          $state = ( $payed == 1 )
          ? tr::$TR['bill_payed'] : tr::$TR['waiting_for_payment'];

          $customer = json_decode( $customer ); // in std class

					$customer_bill =
            $customer->lastname.' '.$customer->firstname.'
            <br>
            '.$customer->address.'
            <br>
            '.$customer->post_code.' '.$customer->city.'
            <br>
            '.$customer->country;

					$shop_title =
					htmlspecialchars_decode( html_entity_decode(WEBSITE_TITLE), ENT_QUOTES );

					// ARRAY FOR MAIL TEMPLATE
					$ARR = array(
						'subject' => $shop_title.' - '.tr::$TR['your_bill'],
						'title' => tr::$TR['your_bill'],
						'host' => HOST,
						'year' => date('Y'),
						'logo' => LOGO,
						'shop_title' => $shop_title,
						'bill_id' => $last_nb,
						'customer' => $customer_bill,
						'amount_text' => $SALE['amount_text'],
						'total_tax_sale' => $SALE['total_tax_sale'],
						'rep_tva' => $rep_tva,
						'date_now' => $date_now_format,
						'products_selected' => $SALE['products_settings'],
						'legal_addr' => nl2br($SHOP['legal_addr']),
						'legal_mention' => nl2br($SHOP['legal_mention']),
						'state' => $state,
						'lang' => LANG_BACK, // here we use the lang of the backend of the company
						'tr' => tr::$TR,

					);

          // print_r( $ARR );

          // send_bill_by_mail
          mail::send_bill_by_mail( $customer->mail, $ARR );

          // need to sleep for send multiples e-mails
          sleep(2);
      }
      // END SEND BILL BY MAIL IF IS ASKED


      // SEND MAIL CONFRIM TREATMENT IS ASKED
      if( isset($_POST['send_mail_confirm_treatment']) ){

					$shop_title =
					htmlspecialchars_decode( html_entity_decode(WEBSITE_TITLE), ENT_QUOTES );

					$date_mail = ucfirst( tools::format_date_locale($date_now_obj, 'FULL', 'SHORT', null) );

          // array for mustache
          $ARR = array(
						'subject' => tr::$TR['order_processed'],
	          'message' => tr::$TR['order_processed_comm_txt'].$shop_title.'.',
	          'shop_title' => $shop_title,
	          'shop_img' => LOGO,
	          'shop_mail' => PUBLIC_NOTIFICATION_MAIL,
	          'date' => $date_mail,
	          'host' => HOST,
						'year' => date('Y'),
						'lang' => LANG_FRONT, // here we use the lang of the FRONT-end of the company
						'tr' => tr::$TR,
				 	);

          // send_mail_confirm_treatment
          mail::mail_confirm_treatment( $SALE['customer_settings']['mail'], $ARR);

      }
      // END SEND MAIL CONFRIM TREATMENT IS ASKED


      unset($_POST);
      exit;

  }
  /**
   * archives::archive_vendor_sale();
   */



  /**
   * archives::get_archives_shop( $limit );
   *
   * @return {array} :
   * @return {array}  archived_products
   * @return {array}  customer
   * @return {array}  customer_sup
   * @return {str}    date_sale
   * @return {str}    id_card
   * @return {str}    id_payment
   * @return {int}    payed
   * @return {bool}   refounded
   * @return {str}    refounded_date
   * @return {int}    sale_id
   * @return {int}    sale_number
   * @return {str}    total_amount_sale
   * @return {str}    total_old_amount_sale
   * @return {str}    total_refounded_amount
   * @return {str}    total_tax_sale
   * @return {int}    processed
   */
  public static function get_archives_shop( $limit ){


      // get archives with limit
      $ARR_pdo = false;

			// ugly hack ...
			if( isset($_POST['what']) ){

					$sql = $limit;
			}
			else{

					$sql = 'SELECT * FROM archived_sales
					ORDER BY sale_number DESC LIMIT '.$limit.'';
			}

      $response = 'all';
      $last_id = false;
      $ARCHIVES = db::server($ARR_pdo, $sql, $response, $last_id);

      $timeZone = new DateTimeZone(TIMEZONE);  // ! $timezone is used after

      // fetch sold products for archives
      foreach( $ARCHIVES as $k => $v ){

          // i. new_sales::get_sold_products( sale_id );
          // * @return 'products_settings' -> array of products
          // * @return 'total_tax_sale' -> str. formated not in cent
          // * @return 'total_amount_sale' -> str. formated not in cent
          // * @return 'total_old_amount_sale' -> str. formated not in cent
          // * @return 'refounded' -> bool
          // * @return 'refounded_amount' -> int.
          // * @return 'total_refounded_amount' -> str.
          // * @return 'refounded_date'
          $GET_archived_products = new_sales::get_sold_products($v['sale_id']);

          // products
          $ARCHIVES[$k]['archived_products'] =
            $GET_archived_products['products_settings'];

          // result. total taxes
          $ARCHIVES[$k]['total_tax_sale'] =
            $GET_archived_products['total_tax_sale'];

          // well format total_amount_sale
          $ARCHIVES[$k]['total_amount_sale'] =
            $GET_archived_products['total_amount_sale'];

          // old amount sale
          $ARCHIVES[$k]['total_old_amount_sale'] =
            $GET_archived_products['total_old_amount_sale'];

          // is refounded ?
          $ARCHIVES[$k]['refounded'] = $GET_archived_products['refounded'];

          $ARCHIVES[$k]['total_refounded_amount'] = ''; // by default ... for my brain


          if( $ARCHIVES[$k]['refounded'] == true ){

              // well format refount amount NOT IN CENT
              $ARCHIVES[$k]['total_refounded_amount'] =
                $GET_archived_products['total_refounded_amount'];

              // REFOUNDED DATE
              $ARCHIVES[$k]['refounded_date'] =
                $GET_archived_products['refounded_date'];

          }

          // date_sale well formatted
          $date_sale_db =
          DateTime::createFromFormat('Y-m-d', $ARCHIVES[$k]['date_sale'], $timeZone );

					// keep date sale db format
					$ARCHIVES[$k]['date_db'] = $ARCHIVES[$k]['date_sale'];

					// return 23/12/2022 in fr
					$ARCHIVES[$k]['date_sale'] =
						tools::format_date_locale( $date_sale_db, 'SHORT' , 'NONE', null );

          // decode customer - this decode too customer_sup
          $ARCHIVES[$k]['customer'] = json_decode( $v['customer'], true ); // true for array

      }
      // end loop $ARCHIVES

      return $ARCHIVES;

  }
  /**
   * archives::get_archives_shop( $limit );
   */



  /**
   * archives::load_more_archives();
   *
   * @return {json}  description
   */
  public static function load_more_archives(){


	    // VERIFY token
	    token::verify_token();

			$min = (int) abs( $_POST['min'] );
			$max = (int) abs( $_POST['max'] );

			$limit = ''.$min.','.$max.'';

			$ARCHIVES = archives::get_archives_shop( $limit );

			$load_more_archives =
			( count($ARCHIVES) < ARCHIVES_INCR ) ? false : true;

			$tab = array(
        'success' => true,
				'load_more_archives' => $load_more_archives,
        'archives' => $ARCHIVES
      );

      unset($_POST);
      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

	}
  /**
   * archives::load_more_archives();
   */



  /**
   * archives::get_ONE_archive( $sale_id );
   *
   * @return {type}  get one archive by sale_id
   */
  public static function get_ONE_archive( $sale_id ){


		// get ONE archive
    $ARR_pdo = array( 'sale_id' => $sale_id );
    $sql = 'SELECT * FROM archived_sales WHERE sale_id=:sale_id';
    $response = 'one';
    $last_id = false;
    $ONE_ARCHIVE = db::server($ARR_pdo, $sql, $response, $last_id);

		if( empty($ONE_ARCHIVE) ){

				// return false here
				return false;
		}

    $timeZone = new DateTimeZone(TIMEZONE);  // ! $timezone is used after

		$GET_archived_products = new_sales::get_sold_products( $sale_id );

    // products
    $ONE_ARCHIVE['archived_products'] =
      $GET_archived_products['products_settings'];

    // result. total taxes
    $ONE_ARCHIVE['total_tax_sale'] =
      $GET_archived_products['total_tax_sale'];

    // well format total_amount_sale
    $ONE_ARCHIVE['total_amount_sale'] =
      $GET_archived_products['total_amount_sale'];

    // old amount sale
    $ONE_ARCHIVE['total_old_amount_sale'] =
      $GET_archived_products['total_old_amount_sale'];

    // is refounded ?
    $ONE_ARCHIVE['refounded'] = $GET_archived_products['refounded'];

    $ONE_ARCHIVE['total_refounded_amount'] = ''; // by default ... for my brain


    if( $ONE_ARCHIVE['refounded'] == true ){

        // well format refount amount NOT IN CENT
        $ONE_ARCHIVE['total_refounded_amount'] =
          $GET_archived_products['total_refounded_amount'];

        // REFOUNDED DATE
        $ONE_ARCHIVE['refounded_date'] =
          $GET_archived_products['refounded_date'];

    }


	  // date_sale well formatted
		$date_sale_db =
			DateTime::createFromFormat('Y-m-d', $ONE_ARCHIVE['date_sale'], $timeZone );

		// return 23/12/2022 in fr
		$ONE_ARCHIVE['date_sale'] =
			tools::format_date_locale( $date_sale_db, 'SHORT' , 'NONE', null );

		// DATE DB return YYYY-MM-DD
		$ONE_ARCHIVE['date_db'] = $date_sale_db->format('Y-m-d');

    // decode customer - this decode too customer_sup
    $ONE_ARCHIVE['customer'] = json_decode( $ONE_ARCHIVE['customer'], true ); // true for array


		// return
		return $ONE_ARCHIVE;

	}
	/**
	 * archives::get_ONE_archive( $sale_id );
	 */



	/**
	 * archives::send_bill_from_archives();
	 *
	 * @return {type}  description
	 */
	public static function send_bill_from_archives(){


      // VERIFY token
	    token::verify_token();

			// sale id
			$sale_id = (int) abs($_POST['sale_id']);

			// get one arcive by sale_id -> sale id for get sold products list
			// this exit if archive was not found
      $ONE_ARCH = archives::get_ONE_archive( $sale_id );

// print_r( $ONE_ARCH );

			// make and object date ['date_db']
			$Date_sale_db =
			DateTime::createFromFormat('Y-m-d', $ONE_ARCH['date_db'], new DateTimeZone(TIMEZONE) );

			// date bill locally formated
			$Date_sale_format =
				tools::format_date_locale( $Date_sale_db, 'SHORT' , 'NONE', null );


			// rep. for template
			$rep_tva = ( boolval($ONE_ARCH['total_tax_sale']) == false ) ? false : true;

			// state for payed / un-payed SALE
			$state = ( (int) $ONE_ARCH['payed'] == 1 )
			? tr::$TR['bill_payed'] : tr::$TR['waiting_for_payment'];

			// get legal mentions of shop
			$SHOP = shop::get_shop();

			$customer_bill =
				$ONE_ARCH['customer']['lastname'].' '.$ONE_ARCH['customer']['firstname'].'
				<br>
				'.$ONE_ARCH['customer']['address'].'
				<br>
				'.$ONE_ARCH['customer']['post_code'].' '.$ONE_ARCH['customer']['city'].'
				<br>
				'.$ONE_ARCH['customer']['country'];

			$shop_title =
				htmlspecialchars_decode( html_entity_decode(WEBSITE_TITLE), ENT_QUOTES );

			// ARRAY FOR MAIL TEMPLATE
			$ARR = array(
				'subject' => $shop_title.' - '.tr::$TR['your_bill'],
				'title' => tr::$TR['your_bill'],
				'host' => HOST,
				'year' => date('Y'),
				'logo' => LOGO,
				'shop_title' => $shop_title,
				'bill_id' => $ONE_ARCH['sale_number'],
				'customer' => $customer_bill,
				'amount_text' => $ONE_ARCH['total_amount_sale'],
				'total_tax_sale' => $ONE_ARCH['total_tax_sale'],
				'rep_tva' => $rep_tva,
				'date_now' => $Date_sale_format,
				'products_selected' => $ONE_ARCH['archived_products'],
				'legal_addr' => nl2br($SHOP['legal_addr']),
				'legal_mention' => nl2br($SHOP['legal_mention']),
				'state' => $state,
				'lang' => LANG_BACK, // here we use the lang of the backend of the company
				'tr' => tr::$TR,

			);

			// is refounded ?
			if( (int) $ONE_ARCH['refounded'] == 1 ){

					$ARR['refounded'] = 1;
					$ARR['refounded_amount_text'] = $ONE_ARCH['total_refounded_amount'];
					$ARR['refounded_date'] = $ONE_ARCH['refounded_date'];
			}

			// print_r( $ARR );

			// send_bill_by_mail
			$send_mail = mail::send_bill_by_mail( $ONE_ARCH['customer']['mail'], $ARR );

			// success
			if( boolval($send_mail) == true ){

					unset($_POST);

					$TAB = array( 'success' => tr::$TR['success_send_bill_at_customer'] );
		      echo json_encode($TAB, JSON_FORCE_OBJECT);
		      exit;
			}
			// error
			else{

					unset($_POST);
					$TAB = array( 'error' => tr::$TR['error_mail_server'] );
					echo json_encode($TAB, JSON_FORCE_OBJECT);
					exit;
			}

	}
	/**
	 * archives::send_bill_from_archives( $sale_id );
	 */



	/**
	 * archives::update_archive_as_payed();
	 *
	 * @return {type}  description
	 */
	public static function update_archive_as_payed(){


      // VERIFY token
	    token::verify_token();

			// sale id
			$sale_id = (int) abs($_POST['sale_id']);

			// this exit if archive was not found
      $ONE_ARCH = archives::get_ONE_archive( $sale_id );


      // update achive to payed
      $ARR_pdo = array(
        'sale_number' => (int) $ONE_ARCH['sale_number'],
        'sale_id' => (int) $ONE_ARCH['sale_id'],
        'payed'=> 1
      );

			// sql. request
      $sql = 'UPDATE archived_sales SET
      payed=:payed WHERE sale_number=:sale_number AND sale_id=:sale_id';
			// settings request
      $response = false;
      $last_id = false;

			// udpate as payed
      $UPDATE_AS_PAYED = db::server($ARR_pdo, $sql, $response, $last_id);

			// error
      if( boolval($UPDATE_AS_PAYED) == false ){

          // error
          $tab = array('error' => tr::$TR['error_update_as_payed'] );
          echo json_encode($tab);
          exit;
      }


      $tab = array(
        'success' => tr::$TR['update_as_payed'],
        'one_archive' => archives::get_ONE_archive( $sale_id )
      );

      unset($_POST);
      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

	}
	/**
	 * archives::update_archive_as_payed();
	 */



  /**
   * archives::refound_vendor_sale();
   *
   * @return {type}  description
   */
  public static function refound_vendor_sale(){


      // VERIFY token
	    token::verify_token();

			// sale id
			$sale_id = (int) abs($_POST['sale_id']);

      $ONE_ARCH = archives::get_ONE_archive( $sale_id );
      // var_dump($ONE_ARCH);

      $sold_product_id = (int) trim(htmlspecialchars($_POST['sold_product_id']));

      // must be 0
      $val_refounded = (float) trim(htmlspecialchars($_POST['refounded_amount']));

      // value refounded in cent
      $refounded_amount = ( $val_refounded * 100 );

      $refounded = ( $refounded_amount == 0 ) ? 0 : 1;

      // refound date
      $TimeZone = new DateTimeZone(TIMEZONE);
      $date = new DateTime('now', $TimeZone);
      $refounded_date = $date->format('Y-m-d');

      // update sold product
      $ARR_pdo = array(
        'id' => $sold_product_id,
        'sale_id' => $sale_id,
        'refounded'=> $refounded,
        'refounded_date'=> $refounded_date,
        'refounded_amount' => $refounded_amount
      );

      $sql = 'UPDATE sold_products SET
      refounded=:refounded,
      refounded_date=:refounded_date,
      refounded_amount=:refounded_amount
      WHERE id=:id AND sale_id=:sale_id';

      $response = false;
      $last_id = false;

      $UPDATE = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($UPDATE) == false ){

          // error
          $tab = array('error' => tr::$TR['error_refound_sale'] );
          echo json_encode($tab);
          exit;
      }


      $tab = array(
        'success' => tr::$TR['success_refound_sale'],
        'one_archive' => archives::get_ONE_archive( $sale_id )
      );

      unset($_POST);
      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

  }
  /**
   * archives::refound_vendor_sale();
   */



	/**
	 * archives::search_in_archives();
	 *
	 * @return {json}  results or empty array
	 */
	public static function search_in_archives(){


	    // VERIFY token
	    token::verify_token();

			// 'date' / 'number' / 'customer'
			$what = trim(htmlspecialchars($_POST['what']));

			// value to search
			$value = trim(htmlspecialchars($_POST['value']));


			// search for date
			if( $what == 'date' ){

					// return array [ 'sql', 'search_value' ]
					$RES = archives::search_by_date( $value );
			}
			// end search for date : $what == 'date'

			// search by number of archive
			if( $what == 'number' ){

					// return array [ 'sql', 'search_value' ]
					$RES = archives::search_by_number( $value );
			}

			// search by customer in archive
			if( $what == 'customer' ){

					// -> this return json AND EXIT();
					// return array [ 'sql', 'search_value' ]
					$RES = archives::search_by_customer( $value );
			}


			// clean SQL string
			$sql = preg_replace('/(\n|\t)+/', ' ', $RES['sql']);

			$ARCHIVES = archives::get_archives_shop( $sql );

			$TAB = array(
				'success' => true,
				'what' => $what,
				'value' => $value,
				'sql' => $sql,
				'search_value' => $RES['search_value'],
				'search_archives' => $ARCHIVES
			);

			echo json_encode( $TAB, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * archives::search_in_archives();
	 */



	/**
	 * archives::search_by_date( $value );
	 *
	 * @param  {string} $value 	date value asked,
	 * 												accept '22' -> 2022, '223' -> 2023, '22 2' -> feb. 2022
	 * @return {array}        	return results of a research by date in archives
	 *   array(
	 *	   'sql' => $sql,
	 *	   'search_value' => $search_value ( string/date  translated )
	 *   );
	 */
	public static function search_by_date( $value ){


			$Date_to_search = new DateTime('now', new DateTimeZone(TIMEZONE));
			$Date_to_search->setTime(0,0,0);

			// remove all non digits - now date is 2022-07-19
			$value = preg_replace('/(\D)+/', "-", $value);


			// empty date / too long / not numeric
			if( empty($value) || iconv_strlen($value) > 10
					|| !preg_match('/\d{1,}/', $value) ){

					$TAB = array('error' => tr::$TR['error_date_archive'].'koko');
					echo json_encode( $TAB, JSON_FORCE_OBJECT );
					exit;
			}

			// explode date by "-"
			// we must have 3 arrays max.
			$VALUES = explode('-', $value);

			// too much arrays
			if( count($VALUES) > 3 ){

					$TAB = array('error' => tr::$TR['error_date_archive']);
					echo json_encode( $TAB, JSON_FORCE_OBJECT );
					exit;
			}


			// year case
			$YEAR = (int) $VALUES[0];

			// test numeric value
			if( !is_numeric($YEAR) || iconv_strlen($YEAR) > 4 ){

					$TAB = array('error' => tr::$TR['error_date_archive']);
					echo json_encode( $TAB, JSON_FORCE_OBJECT );
					exit;
			}

			// find year by 2 numbers ex. '23' | '223' for 2023
			if( iconv_strlen($YEAR) < 4 ){

					// get last 2 numbers of year
					$YEAR = (int) '20'.substr($YEAR, -2);

					// first day of year  / for the next 20th centuries ...
					$Date_to_search->setDate($YEAR, 1, 1);

			}

			// complete year 'YYYY'
			if( iconv_strlen($YEAR) == 4 ){

					// set date start to first day of year
					$Date_to_search->setDate($YEAR, 1, 1);
			}

			// return a readable locale string of the value researched
			$search_value = $YEAR;


			// month case
			if( isset($VALUES[1])
			&& is_numeric($VALUES[1])
			&& iconv_strlen($VALUES[1]) <= 2  ){

					// $MONTH may be !isset()
					$MONTH = abs( (int) $VALUES[1] );

					// set date start to year + month + first day of month
					$Date_to_search->setDate($YEAR, $MONTH, 1);

					// get name month in locale
					$month_str =
					tools::format_date_locale( $Date_to_search, 'NONE' , 'NONE', 'MMMM' );

					// add search value month if exist
					$search_value .= "&nbsp;-&nbsp;".ucfirst($month_str);

			}
			// end month


			// day case
			if( isset($VALUES[2])
			&& is_numeric($VALUES[2])
			&& iconv_strlen($VALUES[2]) <= 2  ){

					// $DAY may be !isset()
					$DAY = abs( (int) $VALUES[2] );

					// set date start to year + month + first day of month
					$Date_to_search->setDate($YEAR, $MONTH, $DAY);

					// render compete date on locale string
					$date_str =
					tools::format_date_locale( $Date_to_search, 'FULL' , 'NONE', null );

					// override search_value in locale string
					$search_value = ucwords($date_str);

			}
			// end day


			// SQL YEAR
			$sql = 'SELECT * FROM archived_sales
			WHERE YEAR(date_sale)='.$YEAR;

			// SQL MONTH
			$sql .= ( isset($MONTH) )
			? ' AND MONTH(date_sale)='.$MONTH.''
			: '';

			// SQL DAY
			$sql .= ( isset($DAY) )
			? ' AND DAY(date_sale)='.$DAY.''
			: '';

			$sql .= ' ORDER BY sale_number';


			// return array
			return array(
				'sql' => $sql,
				'search_value' => $search_value
			);

	}
	/**
	 * archives::search_by_date( $value );
	 */



	/**
	 * archives::search_by_number( $value );
	 *
	 * @param  {type} $value description
	 * @return {type}        description
	 */
	public static function search_by_number( $value ){


			if( empty($value) || !is_numeric($value) || iconv_strlen($value) > 100000000 ){

					$TAB = array('error' => tr::$TR['error_number_archive']);
					echo json_encode( $TAB, JSON_FORCE_OBJECT );
					exit;

			}

			$number = abs( (int) $value );

			$sql = 'SELECT * FROM archived_sales WHERE sale_number = '.$number.'';

			$search_value = tr::$TR['bill_number'].' '.tools::intl_number( $number );

			// return array
			return array(
				'sql' => $sql,
				'search_value' => $search_value
			);


	}
	/**
	 * archives::search_by_number( $value );
	 */



	/**
	 * archives::search_by_customer( $value );
	 *
	 * @param  {type} $value description
	 * @return {type}        description
	 */
	public static function search_by_customer( $value ){



			if( empty($value) || iconv_strlen($value) > 100000000 ){

					$TAB = array('error' => tr::$TR['error_number_archive']);
					echo json_encode( $TAB, JSON_FORCE_OBJECT );
					exit;

			}

			// remove all spaces replace by '-'
			$value = preg_replace('/(\s)+/', "-", $value);

			// explode by '-' -> if not return just str at [0] index
			$VALUES = explode('-', $value);


			$sql = 'SELECT * FROM archived_sales ORDER by sale_number';

			$ARCHIVES = archives::get_archives_shop( $sql );

			// prepa array to return
			$ARRAY_search = array();


			// loop in all archives
			foreach ( $ARCHIVES as $k => $v) {

					$level = 0;

					// regex for lastname
					$regex = '/('.trim($VALUES[0]).')/i';

					// regex for lastname + firstname if exist
					if( isset($VALUES[1]) ){

							$regex = '/('.trim($VALUES[2]).')|('.trim($VALUES[1]).')/i';
					}

					// search in lastname
					if( preg_match($regex, $v['customer']['lastname']) ){

							$level++;
					}

					// search in firstname
					if( preg_match($regex, $v['customer']['firstname']) ){

							$level++;
					}

					// continue on level == 0
					if( $level == 0 ){

							continue;
					}

					// else ...
					// append level to archives
					$ARCHIVES[$k]['level'] = $level;

					// Push to array search
					$ARRAY_search[] = $ARCHIVES[$k];

			}
			// end loop in all archives

			// if have results -> sort
			if( count($ARRAY_search) > 0 ){

					// sort by higest level
					usort($ARRAY_search, function($a, $b){

							return $b['level'] - $a['level'];
					});
			}

			$search_value = ( !isset($VALUES[1]) )
			? $VALUES[0]
			: $VALUES[0].' '.$VALUES[1];

			$TAB = array(
				'success' => true,
				'search_value' => $search_value,
				'search_archives' => $ARRAY_search
			);

			echo json_encode( $TAB, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * archives::search_by_customer( $value );
	 */





}
// END class archives::




?>
