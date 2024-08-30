<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	program.php
 *
 * program::get_back_office( $USER );
 * program::get_login_page();
 * program::login();
 * program::log_out();
 * program::get_by_money( $user_id );
 * private program::get_Token_Placido();
 * program::renew_password( $context );
 *
 */

class program {


  /**
   * program::get_back_office( $USER );
   *
   * @return {array}  array of datas for back-office
   */
  public static function get_back_office( $USER ){


      // INIT. ARR return
      $ARR = array();

			// HOST
			$ARR['host'] = HOST;

      // translating
      $ARR['tr'] = tr::$TR;

			// get infos modules
			$ARR['modules'] = api::$MODULES;

			// return  2 arrays -> 'api_lang_FRONT' => ['code' => $code, 'selected' => $selected]
			//                  -> 'api_lang_BACK' => ['code' => $code, 'selected' => $selected]
			$API_LANGS = tr::get_translations($req="");
			$ARR['api_lang_FRONT'] = $API_LANGS['api_lang_FRONT'];
			$ARR['api_lang_BACK'] = $API_LANGS['api_lang_BACK'];
			$ARR['api_locales_list'] = tools::get_locales_settings();
			$ARR['api_timezones_list'] = tools::get_timezones();

			// API SETTINGS
      $ARR['api_settings'] = API_SETTINGS;

			// PWA settings
			$WEB_APP = web_app::return_web_app_settings();
			$ARR['web_app'] = array(
				'public' => $WEB_APP['public'],
				'private' => $WEB_APP['private']
			);

      // CONSTRUCT USER
      $ARR['user'] =
      array(
				'name'=> $USER['name'],
        'mail' => $USER['mail'], // used in settings
        'token_max_time' => TOKEN_TIME,
        'token' =>
          token::set_token( $USER['id'], $USER['mail'], $USER['passw'] ),
				'token_Placido' => program::get_Token_Placido()
      );

      // GET LIST ADMINS
      $ARR['admins_list'] = settings::get_admins_list( $USER['mail'] );


      // GET SHOP
      $ARR['shop'] = shop::get_shop();
      // print_r( $ARR['shop'] );


      // GET CATEGORIES
      // return 2 arrays
      // -> 'cats' -> listed for object / 'cats_html' -> html representation
      $CATS = cats::get_cats();
      $ARR['cats_html'] = $CATS['cats_html'];
      $ARR['cats'] = $CATS['cats'];


      // GET PRODUCTs OF ONE USER - pass arr cats for not ask DB
      $ARR['products'] = products::get_products( $ARR['cats'] );

			// GET FEATURED PRODUCTS - IN API FOLDER !
			$ARR['featured_prods'] =
			featured_products::get_featured_products( $ARR['products'] );

      // GET NEW SALES
      // * @return {array}  'new_sales'
      // * @return {str}    'total_amount_shop'
      // * @return {int}    'nb_new_sales'
      $GET_new_sales = new_sales::get_new_sales_user();
      // var_dump( $GET_new_sales );

      $ARR['new_sales'] = $GET_new_sales['new_sales'];


      // TEMPLATE BY DEFALUT
      $ARR['template'] = array(
        'nb_products' => count($ARR['products']) ,
				'nb_featured_prods' => count($ARR['featured_prods']) ,
        'products' => products::get_pagina( $ARR['products'] ) ,
        'total_amount_shop' => $GET_new_sales['total_amount_shop'] ,
        'nb_new_sales' => $GET_new_sales['nb_new_sales'] ,
        'nb_for_pagina' => NB_FOR_PAGINA_BACKEND ,
        'nb_page_active' => 1 ,
        'lang' => LANG_BACK ,
				'count_not_read' => 0,
				'nb_messages' => 0,
				'display_mozaic' => ( DISPLAY_PRODUCTS == 'mozaic' ) ? true : false,
				'display_inline' => ( DISPLAY_PRODUCTS == 'inline' ) ? true : false
      );

      // model vue default
      $ARR['vue'] = array( 'home' => true,
                           'title' => tr::$TR['home'],
                           'icon' => 'fas fa-home'
                          );

      // GET ARCHIVES
      $ARR['archives'] = archives::get_archives_shop( $limit='0,'.ARCHIVES_INCR.'' );

			// for render button load more archive in view
			$ARR['template']['load_more_archives'] =
			( count($ARR['archives']) < ARCHIVES_INCR ) ? false : true;

			// add archives incrementor for call nexts results with ajax
			$ARR['template']['archives_incr'] = ARCHIVES_INCR;

      // GET USER MESSAGES - ! this return an array
      $GET_messages = mess::get_messages();
      $ARR['messages'] = $GET_messages['messages'];
      $ARR['template']['count_not_read'] = $GET_messages['count_not_read'];
      $ARR['template']['nb_messages'] = $GET_messages['nb_messages'];

			// GET ALL REJECTED IPs
			$ARR['ip_rejected'] = ip_rejected::get_all_rejected_IP();

			// GET STATIC PAGES
			$ARR['static_pages'] = static_pages::get_static_pages();

      // NB VISITS - return string (nb + string translated)
      $ARR['stats']['today_nb_visits'] = stats::get_stat_day_nb();

			// check if token IPinfo stats. was recorded
			$ARR['stats']['token_api'] = stats::check_token_api();


      // RETURN RESULT TAB ADMIN
      $tab = array('success'  => true, 'response' => $ARR );
			// var_dump( json_encode($tab, JSON_NUMERIC_CHECK) );
      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;


  }
  /**
   * program::get_back_office( $USER );
   */



  /**
   * program::get_login_page();
   *
   * @return {html}  html login page
   */
  public static function get_login_page(){


      // ARRAY FOR MUSTACHE FOR DISPLAY LOGIN PAGE
      $ARR = array(
        'title_page' => tr::$TR['your_management'],
        'year' => date('Y'),
				'base' => ADMIN_FOLDER,
        'tr' => tr::$TR
      );

      // TEST COOKIE - retrieve mail user
      if( isset($_COOKIE['PL-GEST-mail']) ){

					$ARR['mail'] =
						api::api_crypt( $_COOKIE['PL-GEST-mail'], 'decr' );
      }

      // MUSTACHE
      // use .html instead of .mustache for default template extension
      $options =  array('extension' => '.html');
      $template = 'base'; // without extension

			// template loader
      $m = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
      ));

      // RENDER TEMPLATE LOGIN
      echo $m->render($template, $ARR);

			exit;

  }
  /**
   * END program::get_login_page();
   */



  /**
   * program::login();
   *
   * @return {array}  all for back office
   */
  public static function login(){



			if( session_status() === PHP_SESSION_NONE ){

          session_start([
            'name' => 'PLACIDO-SHOP-BACKEND',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60*60*12, // 12 hours
						//- no effect if server config - depend on php_ini ?
            'gc_maxlifetime' => 60*60*12, // 12 hours
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

      // TEST ERROR LOGINS COUNT - REJECT if over
      // if count no exist - create it
      if( !isset($_SESSION['count']) ){

          $_SESSION['count'] = 5; // 5 tests before ban
          // var_dump($_SESSION['count']);
      }

      // if max count - exit !
      if( isset($_SESSION['count']) && $_SESSION['count'] <= 1
			||	ip_rejected::test_ip_rejected() == false ){


					// get ip user -> if false : already rejected
					$ip_user = ip_rejected::test_ip_rejected();


					if( $_SESSION['count'] == 1 ){

							// record IP
							ip_rejected::record_ip_rejected( $ip_user );

							// send mail alert to admin
							mail::send_mail_alert();
					}

					$_SESSION['count'] = 0;

					// array error
          $tab = array('error' => tr::$TR['error_private_admin_page'] );

          // return json arr -> js redirect to home public page
          echo json_encode($tab);
          exit;

      }
      // TEST ERROR LOGINS COUNT

      // manage fields

      // E-MAIL
      // EMPTY
      if( empty($_POST['mail']) ){

          $tab = array('error' => tr::$TR['empty_mail']);
          echo json_encode($tab);
          exit;
      }

      // VERIFY  E-MAIL
      if( !empty($_POST['mail']) ){

          $mail = (string) trim(htmlspecialchars($_POST['mail']));

          // IF MAX LENGTH
          if( iconv_strlen($mail) > 80 ){

              $tab = array('error' => tr::$TR['too_long_login_mail'] );
              echo json_encode($tab);
              exit;
          }

          // IF BAD FORMAT
          if( filter_var($mail, FILTER_VALIDATE_EMAIL ) == false ){

              $tab = array('error' => tr::$TR['bad_mail'] );
              echo json_encode($tab);
              exit;
          }

      }
      // END E-MAIL



      // PASSWORD
      // EMPTY PASSWORD
      if( trim($_POST['mdp']) == "" || empty($_POST['mdp']) ){

          $tab = array('error' => tr::$TR['password_required'] );
          echo json_encode($tab);
          exit;
      }
      else {

          $mdp = (string) trim(htmlspecialchars($_POST['mdp']));

      }
      // END PASSWORD


      // call $USER
      $ARR_pdo = array('mail' => $mail);
      $sql = 'SELECT * FROM admins WHERE mail=:mail';
      $response = 'one';
      $last_id = false;

      $USER = db::server($ARR_pdo, $sql, $response, $last_id);
      // var_dump($USER);


      // bad request
      if( boolval( $USER ) == false ){

          // decr. session count
          $_SESSION['count']--;

          // array error
          $tab = array('error' =>
					tr::$TR['bad_login'].' '.$_SESSION['count'].' '.tr::$TR['trials'] );

          // return json arr
          echo json_encode($tab);
          exit;

      }
      // END bad request


      // WRONG PASSWORD
      if( password_verify($mail.$mdp, $USER['passw']) == false ){

          // decr. session count
          $_SESSION['count']--;

          // array error
          $tab = array('error' =>
					tr::$TR['bad_login'].' '.$_SESSION['count'].' '.tr::$TR['trials'] );

          // return json arr
          echo json_encode($tab);
          exit;

      }
      // END WRONG PASSWORD

      // LOGIN OK - CONTINUE

      // destroy session
      unset($_SESSION['count']);

      // store mail in coockie
      setcookie(
				"PL-GEST-mail", // name
				api::api_crypt( $mail, 'encr' ), // value
				time()+TOKEN_TIME, // expires + TOKEN_TIME
				'/'.ADMIN_FOLDER.'/', // allowed folder path
				HOST, // domain
				true, // secure
				true // http only
		 );

      // get datas for back-office - this exit script
      program::get_back_office( $USER );

  }
  /**
   * END program::login();
   *
   * @return {array}  all for back office
   */



  /**
   * program::log_out();
   *
   * @return {array}  success / error
   */
  public static function log_out(){


			// GET USER ID
			$user_id = token::verify_token();

			// delete token
			$log_out = token::clean_tokens( $user_id );

			$tab = array( 'success' => true );
      echo json_encode($tab);
      exit;

	}
  /**
   * program::log_out();
   */



  /**
   * program::get_by_money( $user_id );
   *
   * user choose payment in money
   *
   * @param  {int} $user_id
   * @return {bool}
   */
  public static function get_by_money( $user_id ){

      $ARR_pdo = array('user_id' => $user_id);
      $sql = 'SELECT by_money FROM user_shop WHERE user_id=:user_id';
      $response = 'one';
      $last_id = false;

      $BY_MONEY = db::server($ARR_pdo, $sql, $response, $last_id);

      return boolval( $BY_MONEY['by_money'] );

  }
  /**
   * program::get_by_money( $user_id );
   */


	///////////// TOKEN PLACIDO ////////////////


	/**
	 * program::get_Token_Placido();
	 *
	 * @return {string}  return Placido Shop user token decrypted
	 */
	private static function get_Token_Placido(){


			// prepa. db::server()
			$ARR_pdo = array( 'id' => 0 );
			$response = 'one';
			$last_id = false;

			// SQL request
			$sql = 'SELECT token FROM token_Placido WHERE id=:id';

			// execute
			$TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);

			// empty token case
			if( empty($TOKEN) ){

					return false;
			}

			// return decrypted token Placido
			return api::api_crypt( $TOKEN['token'], 'decr' );

	}
	/**
	 * program::get_Token_Placido();
	 */


/////////////////////////////////////////////////////////
///////////////   PASSWORD   ////////////////////////////
/////////////////////////////////////////////////////////


  /**
   * program::renew_password( $context );
   *
   * @param  {str} $context 'change_admin_pass' -> if not automatic renew password
   *
   */
  public static function renew_password( $context ){

      // set session
			if( session_status() === PHP_SESSION_NONE ){

          session_start([
            'name' => 'PLACIDO-SHOP-BACKEND',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60*60*12, // 12 hours
						//- no effect if server config - depend on php_ini ?
            'gc_maxlifetime' => 60*60*12, // 12 hours
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

			// SECURITY ABLE TO SEND 5 MAILS RENEW PASSORD -> THEN IP REJECTED
			// if count no exist - create it
      if( !isset($_SESSION['count']) ){

          $_SESSION['count'] = 5; // 5 tests before ban
      }

			// if max count - exit !
      if( isset($_SESSION['count']) && $_SESSION['count'] < 1
			    || ip_rejected::test_ip_rejected() == false ){

					// get ip user -> if false : already rejected
					$ip_user = ip_rejected::test_ip_rejected();

					if( $_SESSION['count'] == 0 ){

							// record IP
							ip_rejected::record_ip_rejected( $ip_user );

							// send mail alert to admin
							mail::send_mail_alert();
					}

					$_SESSION['count'] = -1;

          // array error
          $tab = array('error' => tr::$TR['error_private_admin_page'] );

          // return json arr -> js redirect to home public page
          echo json_encode($tab);
          exit;
      }
      // TEST ERROR LOGINS COUNT


      // EMPTY
      if( empty($_POST['mail']) ){

          // decrement session
          $_SESSION['count']--;

          $tab = array('error' => tr::$TR['empty_mail'] );
          echo json_encode($tab);
          exit;
      }

      // VERIFY  E-MAIL
      if( !empty($_POST['mail']) ){

          $mail = (string) trim(htmlspecialchars($_POST['mail']));

          // IF MAX LENGTH
          if( iconv_strlen($mail) > 100 ){

              // decrement session
              $_SESSION['count']--;

              $tab = array('error' => tr::$TR['too_large_mail'] );
              echo json_encode($tab);
              exit;
          }

          // IF BAD FORMAT
          if( filter_var($mail, FILTER_VALIDATE_EMAIL ) == false ){

              // decrement session
              $_SESSION['count']--;

              $tab = array('error' => tr::$TR['bad_mail'] );
              echo json_encode($tab);
              exit;
          }

      }
      // END E-MAIL


      // FECTH USER - FOR AUTOMATIC RENEW ['mail'] IS THE ONLY INFORMATION PASSED ...
      // $USER
      $ARR_pdo = array( 'mail' => $mail );
      $sql = 'SELECT * FROM admins WHERE mail=:mail';
      $response = 'one';
      $last_id = false;

      $USER = db::server($ARR_pdo, $sql, $response, $last_id);

      // IF USER IS FOUND && MAIL==MAIL
      if( boolval($USER) == true
          && $USER['mail'] == $mail ){

          // RENEW PASSORD
          // set a hard wordpass
          $alpha = array('A','B','C','D','E','F','G',
          			   'H','I','J','K','L','M','N','O','P',
          			   'Q','R','S','T','U','V','W',
          			   'X','Y','Z',
          			   '0','1','2','3','4','5',
          			   '6','7','8','9');

          shuffle($alpha);

          $pass_randed = '';

          // get 8 pairs of letters/numbers
          for( $i=0; $i < 16; $i++ ){

            	$pass_randed .= $alpha[$i];

            	if( $i%2 != 0 && $i != 15 ){
            		$pass_randed .= '_';
            	}

          }

          // echo $pass_randed; // get str. like : PL_H9_A1_SX_CR_O7_KU_MV

          $new_pass = password_hash($mail.$pass_randed, PASSWORD_DEFAULT);

          // RECORD NEW PASS IN DB
          $ARR_pdo = array(
						'passw' => $new_pass,
						'mail' => $mail,
						'id' => $USER['id']
					);

          $sql = 'UPDATE admins SET passw=:passw WHERE mail=:mail AND id=:id';

          $response = false;
          $last_id = false;

          $REC = db::server($ARR_pdo, $sql, $response, $last_id);

          // IF ERROR
          if( boolval($REC) != true ){

              // DECREM SESSION COUNT
  						$_SESSION['count']--;

              $tab = array('error' => tr::$TR['unable_renew_password'] );
              echo json_encode($tab);
              exit;

          }

          // SEND NEW PASSWORD BY MAIL
          $subject = tr::$TR['your_password'];
          $message = tr::$TR['forgot_password_info'].'&nbsp; <b>'.$pass_randed.'</b>
          <br />';

					// date mail
		      $Date_Now = new DateTime('now', new DateTimeZone(TIMEZONE) );
		      $date_mail = tools::format_date_locale( $Date_Now, 'FULL' , 'SHORT', null );

		      // array for mustache
		      $ARR = array(
						'subject' => $subject,
			      'message' => $message,
			      'shop_title' => WEBSITE_TITLE,
			      'shop_img' => LOGO,
			      'shop_mail' => false,
			      'date' => ucfirst($date_mail),
			      'host' => HOST,
						'year' => date('Y'),
			      'lang' => LANG_FRONT, // here use lang front
			      'tr' => tr::$TR
					);

		      // MUSTACHE FOR TEMPLATE
		      $options =  array('extension' => '.html');

          // template loader
		      $m = new Mustache_Engine(array(
		          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
		      ));

		      // ask template -> mail_confirm_traitement.html
		      $templ = 'just_comm_mail';

		      // loads template from `templates/$templ.html` and renders it with the ARRAY.
		      $html_message = $m->render($templ, $ARR);

		      // SEND MAIL by PHP MAILER
          if( mail::send_mail_by_PHPMailer($mail, $subject, $html_message) == true ){

              $tab = array('success' => tr::$TR['forgot_password_success'] );
              echo json_encode($tab);
              exit;
          }
          else{

              // ERROR MAIL
              $tab = array('error' => tr::$TR['error_mail_server'] );
              echo json_encode($tab);
              exit;
          }

      }
      else{

          // USER NOT FOUND
					$_SESSION['count']--;

          $tab = array('error' => tr::$TR['error_private_admin_page'] );
          echo json_encode($tab);
          exit;
      }

  }
  /**
   * END program::renew_password( $context );
   */



}
// END class program::

?>
