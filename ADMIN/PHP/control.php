<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	control.php
 *
 * backend controller
 *
 * control::start();
 *
 */
class control {


	/**
	 * control::start();
	 *
	 * Controller of API
	 */
	public static function start(){


		// REQUEST_METHOD METHOD
		$method = $_SERVER['REQUEST_METHOD'];

		// bad method or empty set command
		if( $method != 'POST' || empty($_POST['set']) ){

				// ask program to give the home page
				program::get_login_page();
				exit;
		}


		// security 'set' request
		$set = (string) trim(htmlspecialchars($_POST['set']));

		// test 'set' length
		if( iconv_strlen($set) > 40 ){

				// error
				$tab = array( 'error' => true,
											'message' => 'The request is too long ...' );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;
		}

		// VERIFY POST METHOD
		if( ( $_SERVER['HTTP_HOST'] != HOST && $set != 'record_api_settings' )
				|| $method != 'POST'
				|| $_SERVER['QUERY_STRING'] != "" ){

				// error
				$tab = array( 'error' => true,
											'message' => 'External requests are not possible' );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;

		}
		// end test POST from website


    // POST REQUESTS - $set is a key who call a function to run
    switch( $set ){


	      // login
	      case 'login':
	        	program::login();
	      break;

				// log_out
	      case 'log_out':
	        	program::log_out();
	      break;

	      // forgot_password
	      case 'forgot_password':
	        	program::renew_password($context='');
	      break;


				// SETTINGS
	      // change_admin_pass
	      case 'change_admin_pass':
	        	settings::update_access_admin();
	      break;

				// add_new_admin
				case 'add_new_admin':
	        	settings::add_new_admin();
	      break;

				// delete_admin
				case 'delete_admin':
	        	settings::delete_admin();
	      break;

	      // STRIPE KEYS
	      // set_stripe_keys
	      case 'set_stripe_keys':
	        	settings::update_stripe_keys_user();
	      break;

	      // update_by_money
	      case 'update_by_money' :
	        	settings::update_shop_for_money_pays();
	      break;

				// record_api_settings
				case 'record_api_settings' :
	        	settings::record_api_settings();
	      break;

				// update_mailbox
				case 'update_mailbox' :
	        	settings::update_mailbox();
	      break;

				// switch_production_mode
				case 'switch_production_mode' :
	        	settings::init_production_mode();
	      break;

				// record_token_placido
				case 'record_token_placido' :
	        	settings::record_Token_Placido_User();
	      break;


	      // STATISTICS
				// get_stats_by_interval
	      case 'get_stats_by_interval' :
	        	stats::get_stats_by_interval();
	      break;

	      // record_stats_token
	      case 'record_stats_token' :
	          stats::record_stats_token();
	      break;


				// NEW SALES
	      // get_new_sales -> hot refresh new sales && messages
	      case 'get_new_sales':
	        	new_sales::get_fresh_sales();
	      break;

	      // suppr_sale
	      case 'suppr_sale':
	          new_sales::suppr_new_sale();
	      break;

				// PRODUCTS
	      // add_product
	      case 'add_product':
	          products::rec_prod();
	      break;

	      // suppr_product
	      case 'suppr_product':
	          products::suppr_prod();
	      break;

				// modify_state_product
				case 'modify_state_product':
	          products::modify_state_product();
	      break;

				// set_cat_product
				case 'set_cat_product':
	          products::set_category_of_product();
	      break;

				// FEATURED PRODUCTS
				// record_featured_products
				case 'record_featured_products':
	          products::record_featured_products();
	      break;

				// record_slider_settings
				case 'record_slider_settings':
	          products::record_slider_settings();
	      break;

	      // CATEGORIES
	      // move_cat
	      case 'move_cat':
	          cats::move_cat();
	      break;

	      // insert_new_cat
	      case 'insert_new_cat':
	          cats::insert_cat();
	      break;

	      // set_cat // update or suppr
	      case 'set_cat':
	          cats::update_cat();
	      break;


				// SHOP
	      // update_shop
	      case 'update_shop':
	          shop::update_shop();
	      break;

				// set_mode_shop
	      case 'set_mode_shop':
	          shop::set_mode_shop();
	      break;

	      // MESSAGES
				// get_fresh_messages
				case 'get_fresh_messages':
	          mess::get_fresh_messages();
	      break;

	      // pass_mess_to_readed
	      case 'pass_mess_to_readed':
	          mess::update_mess_readed();
	      break;

	      // erase_message
	      case 'erase_message':
	          mess::suppr_message();
	      break;


				// COMPRESSION
	      // compress_ressources
	      case 'compress_ressources':
	          compress::compress_js_css();
	      break;

				// use_compressed
	      case 'use_compressed':
	          compress::use_compressed();
	      break;


				// SITEMAP
				// rebuild_sitemap
				case 'rebuild_sitemap':
	          sitemap::build_sitemap();
	      break;


				// MAIL
	      // send_mail_to_customer
	      case 'send_mail_to_customer':
	          mail::vendor_send_mail();
	      break;


				// ACHIVES
	      // archive_sale
	      case 'archive_sale':
	          archives::archive_vendor_sale();
	      break;

	      // refound_sale
	      case 'refound_sale':
	          archives::refound_vendor_sale();
	      break;

				// load_more_archives
				case 'load_more_archives':
						archives::load_more_archives();
				break;

				// update_bill_as_payed
				case 'update_bill_as_payed':
						archives::update_archive_as_payed();
				break;

				// search_archives
				case 'search_archives':
						archives::search_in_archives();
				break;

				// send_bill_at_customer
				case 'send_bill_at_customer':
						archives::send_bill_from_archives();
				break;


				// IP REJECTED
				// get_ip_rejected
				case 'get_ip_rejected':
						ip_rejected::get_ip_rejected();
				break;

				// unban_ip
				case 'unban_ip':
						ip_rejected::admin_unban_ip();
				break;


				// STATIC PAGES
				// record_static_page
				case 'record_static_page':
						static_pages::admin_record_static_page();
				break;

				// suppr_static_page
				case 'suppr_static_page':
						static_pages::admin_suppr_static_page();
				break;

				// modify_static_page
				case 'modify_static_page':
						static_pages::admin_modify_static_page();
				break;

				// edit_static_page
				case 'edit_static_page':
						static_pages::get_html();
				break;

				// record_edit_static_page
				case 'record_edit_static_page':
						static_pages::set_html();
				break;

				// PROGRESSIVE WEB APP
				// record_web_app
				case 'record_web_app':
						web_app::record_web_app_settings();
				break;

				// DEFAULT
				// get login page by default
	      default:
	      		program::get_login_page();
	      break;

		}
    // END switch

    unset($_POST);
		exit('Bad request ...');
	}
	/**
	 * END control::start();
	 * Controller of API
	 */



}
// END CLASS control::



?>
