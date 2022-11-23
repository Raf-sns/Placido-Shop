<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	control.php
 *
 * CONTROLLER OF API
 *
 * Manage requests to API
 *
 * control::router();
 * control::start();
 * control::test_router();
 *
 */

class control {


	/**
	 * control::router();
	 * manage GET requests to API
	 * the router handles GET requests otherwise
	 * it returns the request method which should only be POST
	 *
	 * @return {string/object}
	 * 					string : $method = 'POST'
	 * 						object : -> set api::$REQ[] datas for specific render
	 */
	public static function router(){


			// for test
			// control::test_router();

			// REQ METHOD
			$method = $_SERVER['REQUEST_METHOD'];

			// test length (in octets) of request -> too long -> exit
			// accept only POST OR GET requests -> other -> exit
			if( strlen($_SERVER['REQUEST_URI']) > 3000
					|| $method != 'GET' && $method != 'POST' ){

					// -> redirect to 404
					header('Location: https://'.HOST.'/404.php');
					exit;
			}

			// if POST request  -> just return method, no test urls values
			if(  $method == 'POST'  ){

					// GO TO POST CONTROLLER
					return $method;
			}

			// trim / escape url request
			$request = trim(htmlspecialchars($_SERVER['REQUEST_URI']));


			// capture  GET  url requests
			if(  $method == 'GET'  ){

					// default
					if( $request == '/' ){
							// ask program to give the home page
							program::get_home_page(); // this exit in program::
					}

					// default - FB REQUEST
		      if( !empty($_GET['fbclid']) ){

		          program::get_home_page();
		      }


					// EXPLODE URL by '/'
					// this return an array
					$TAB_url = explode('/', $_SERVER['REQUEST_URI'] );

					// limit 10 items in array
					$c = count($TAB_url);

					if( $c > 10 ){

							// -> redirect to 404
							header('Location: https://'.HOST.'/404.php');
							exit;
					}


					// if( product ?) ^(.*)/product/([0-9]+)$
					if( isset($TAB_url[$c-2]) && $TAB_url[$c-2] == 'product' ){

							api::$REQ = array(	'prod_id' => $TAB_url[$c-1],
																	'url_request' => 'single_product'  );

							program::get_home_page();
					}
					// end prod


					// if( category ?) ^(.*)/category/([0-9]+)$
					if( isset($TAB_url[$c-2]) && $TAB_url[$c-2] == 'category' ){

							api::$REQ = array( 'cat_id' => $TAB_url[$c-1],
																	'url_request' => 'cat'  );

							program::get_home_page();
					}
					// end category


					// if( cart ?) ^cart/(.*)$
					if( isset($TAB_url[$c-2]) && $TAB_url[$c-2] == 'cart' ){
							// page_api=cart
							api::$REQ = array( 'page_api' => 'cart'  );

							program::get_home_page();
					}
					// end cart


					// if( sale ?) ^sale/(.*)/(.*)$  $1: sale_id , $2: hash_customer
					if( isset($TAB_url[$c-3]) && $TAB_url[$c-3] == 'sale' ){
							// page_api=sale
							api::$REQ = array( 'page_api' => 'sale' ,
																	'sale_id' => $TAB_url[$c-2],
																	'hash_customer' => $TAB_url[$c-1],
																);

							program::get_home_page();
					}
					// end sale


					// if( static_page ?) ^(.*).html$
					if( isset($TAB_url[$c-1])
					&& boolval(preg_match( '/(\.html)/i', $TAB_url[$c-1] )) === true  ){

							// page=url without '.html'
							$page = substr($TAB_url[$c-1], 0, strpos($TAB_url[$c-1],'.html') );

							api::$REQ = array( 'page' => $page );

							program::get_home_page();
					}
					// end static page


					// default :
					// -> redirect to 404
					header('Location: https://'.HOST.'/404.php');
					exit;

			}
			// END capture  GET  url requests

	}
	/**
	 * control::router();
	 */



  /**
   * control::start();
   *
   * CONTROLLER of API - Manage $_GET && $_POST requests
   */
  public static function start(){


			// GET REQUESTS ------------------------------------
			// the router handles GET requests otherwise
			// it returns the request method
			// which should only be POST
			$method = control::router();


	    // POST REQUESTS -----------------------------------

	    // test POST from website
			// External POST requests are not possible
	    if( $_SERVER['HTTP_HOST'] != HOST
	      || $_SERVER['REQUEST_METHOD'] != 'POST'
	      || $_SERVER['QUERY_STRING'] != "" ){

	        // -> redirect to 404
					header('Location: https://'.HOST.'/404.php');
					exit;
	    }

			// 'set' is the parameter to focus on one entry function
	    if( $method != 'POST' || empty($_POST['set']) ){

					// -> redirect to 404
					header('Location: https://'.HOST.'/404.php');
					exit;
			}


			// get a SAFE SET request - ALWAYS A STRING
			$set = ( !empty($_POST['set']) )
			? (string) trim(htmlspecialchars($_POST['set'])) : '';


	    // switch 'set' asked -> call a method
	    switch( $set ){


		      // GET TEMPLATES
		      case 'get_templates' :
		          tools::get_templates();
		      break;

					// GET STATIC PAGE
		      case 'return_static_page' :
		          tools::get_static_page();
		      break;

		      // GET OBJECT
		      case 'get_obj' :
		          program::get_home_page();
		      break;

		      // valid_form_payment
		      case 'valid_form_payment' :
		          pay_process::data_process();
		      break;

		      // confirm_user_payment
		      case 'confirm_user_payment' :
		          pay_process::confirm_payment($sale_id='', $pay_with='CARD');
		      break;

		      // destroy_new_sale
		      case 'destroy_new_sale' :
		          pay_process::delete_new_sale();
		      break;

		      // get_sale_user
		      case 'get_sale_user' :
		          program::user_access_sale();
		      break;

		      // contact
		      case 'send_mail_to_admin':
		          mail::send_mail_contact();
		      break;

		      // stat
		      case 'stat' :
		          stats::record_location_stat();
		      break;

					// record_stat_for_one_product
					case 'record_stat_for_one_product' :
		          stats::record_stat_product();
		      break;

					// record_stat_from_cart
					case 'record_stat_from_cart' :
		          stats::record_stat_cart();
		      break;


		      default:
		          program::get_home_page();

	    }
	    // END switch

			// unset if not a known request
	    unset($_POST);
			exit;
  }
  /**
   * control::start();
   */



}
// END CLASS control::



?>
