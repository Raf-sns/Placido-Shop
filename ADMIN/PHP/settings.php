<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2021-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	admin.php
 *
 * settings::update_access_admin();
 * settings::update_stripe_keys_user();
 * settings::update_shop_for_money_pays();
 * settings::set_settings( array() );
 * settings::record_api_settings();
 * settings::update_mailbox();
 * settings::update_mailbox();
 * settings::init_production_mode();
 * settings::record_Token_Placido_User();
 *
 */

class settings {


  /**
   * settings::update_access_admin();
   *
   * @return {array}  new js user object
   */
  public static function update_access_admin(){


      // VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
      // NOW IS THE TRUE ID
      $user_id = program::verify_token($token);


      // EMPTY NAME
      if( empty($_POST['name']) ){

          $tab = array('error' => tr::$TR['empty_name'] );
          echo json_encode($tab);
          exit;
      }
      // VERIFY  NAME
      if( !empty($_POST['name']) ){

          $name = trim(htmlspecialchars($_POST['name']));

          // IF MAX LENGTH
          if( iconv_strlen($name) > 100 ){

              $tab = array('error' => tr::$TR['too_large_name'] );
              echo json_encode($tab);
              exit;
          }

      }
      // END NAME


      // EMPTY E-MAIL
      if( empty($_POST['mail']) ){

          $tab = array('error' => tr::$TR['empty_mail'] );
          echo json_encode($tab);
          exit;
      }
      // VERIFY  E-MAIL
      if( !empty($_POST['mail']) ){

          $mail = trim(htmlspecialchars($_POST['mail']));

          // IF MAX LENGTH
          if( iconv_strlen($mail) > 100 ){

              $tab = array('error' => tr::$TR['too_large_mail'] );
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


      // VERIFY MAIL !!! -> for not set an another user by his e-mail
      // fetch old mail user BY ID - already verified by token
      $ARR_pdo = array('id' => $user_id );
      $sql = 'SELECT mail FROM admins WHERE id=:id';
      $response = 'one';
      $last_id = false;
      $OLD_MAIL_USER = db::server($ARR_pdo, $sql, $response, $last_id);

      // VERIFY MAIL ASKED
      $mail_asked = tools::fetch_mail_admin($mail);

      // IF MAIL != OLD_MAIL AND if $mail_asked == true (still exist) -> error
      if( $mail != $OLD_MAIL_USER['mail'] && boolval($mail_asked) == true ){

            $tab = array('error' => tr::$TR['unable_renew_password'] );
            echo json_encode($tab);
            exit;
      }
      // END VERIFY MAIL !!!


      // VERIFY PASSWORD
      // EMPTY PASS
      if( trim($_POST['passw']) == "" || empty($_POST['passw']) ){

          $tab = array('error' => tr::$TR['password_required'] );
          echo json_encode($tab);
          exit;
      }
      else {

          $passw = trim(htmlspecialchars($_POST['passw']));

      }
      // END PASS

      $new_pass =  password_hash($mail.$passw, PASSWORD_DEFAULT);

      // RECORD NEW PASS IN DB
      $ARR_pdo = array(
        'id' => $user_id,
        'mail' => $mail,
        'passw' => $new_pass,
        'name' => $name,
      );

      $sql = 'UPDATE admins SET mail=:mail, passw=:passw, name=:name WHERE id=:id';

      $response = false;
      $last_id = false;

      $UPDATE = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($UPDATE) == false ){

          unset($_POST);

          $tab = array('error' => tr::$TR['unable_renew_password'] );
          echo json_encode($tab);
          exit;
      }


			// success

      // unset post
      unset($_POST);

      // RE-INIT COOCKIES
      /* expire in 150 days */
      setcookie(
				"PL-GEST-mail", // name
				api::api_crypt( $mail, 'encr' ), // value
				time()+60*60*24*150, // expires
				'/'.ADMIN_FOLDER.'/', // path auth
				'/', // domain ?
				true, // secure
				true // http only
		 );

      // Make an array of user datas
      $ARR_user =
      array(
        'name'=> $name,
        'mail' => $mail, // keep mail for rendering value in setings
        'token_max_time' => TOKEN_TIME,
        'token' =>
          program::set_token( $user_id, $mail, $passw )
      );

			// success response
      $tab = array( 'success' => tr::$TR['update_success'],
                    'user' => $ARR_user   );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

  }
  /**
   * settings::update_access_admin();
   */



  /**
   * settings::update_stripe_keys_user();
   *
   * @return {type}  description
   */
  public static function update_stripe_keys_user(){


      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      $user_id = program::verify_token($token);

			// $context : 'test' / 'prod'
      $context = (string) trim(htmlspecialchars($_POST['context']));

			// BAD CONTEXT
			if( $context != 'test' && $context != 'prod' ){

					// error
          $tab = array('error' => tr::$TR['bad_context'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// test keys or empty
			$Test_Pub_Key = ( empty($_POST['test_pub_key']) || !isset($_POST['test_pub_key']) )
			? '' : (string) trim(htmlspecialchars($_POST['test_pub_key']));

			$Test_Priv_Key = ( empty($_POST['test_priv_key']) || !isset($_POST['test_priv_key']) )
			? '' : (string) trim(htmlspecialchars($_POST['test_priv_key']));

			// production keys or empty
			$Prod_Pub_Key = ( empty($_POST['prod_pub_key']) || !isset($_POST['prod_pub_key']) )
			? '' : (string) trim(htmlspecialchars($_POST['prod_pub_key']));

			$Prod_Priv_Key = ( empty($_POST['prod_priv_key']) || !isset($_POST['prod_priv_key']) )
			? '' : (string) trim(htmlspecialchars($_POST['prod_priv_key']));



      // ONE EMPTY KEY TEST ERROR
      if( $context == 'test'
      && ( !empty($Test_Pub_Key) && empty($Test_Priv_Key)
					|| empty($Test_Pub_Key) && !empty($Test_Priv_Key) )  ){

          // error
          $tab = array('error' => tr::$TR['error_one_empty_key'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // ONE EMPTY KEY PROD ERROR
      if( $context == 'prod'
      && ( !empty($Prod_Pub_Key) && empty($Prod_Priv_Key)
					|| empty($Prod_Pub_Key) && !empty($Prod_Priv_Key) )  ){

          // error
          $tab = array('error' => tr::$TR['error_one_empty_key'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


      // TEST KEYS
      if( $context == 'test' ){

          if( !empty($Test_Pub_Key)
					&& !empty($Test_Priv_Key) ){

              // UPDATE_STRIPE_KEYS -> encrypt and store Stripe keys
              $ARR_pdo = array(
                'id' => 0,
                'test_pub_key' => api::api_crypt( $Test_Pub_Key, 'encr' ) ,
                'test_priv_key' => api::api_crypt( $Test_Priv_Key, 'encr')
              );

              $sql = 'UPDATE user_shop SET
                      test_pub_key=:test_pub_key,
                      test_priv_key=:test_priv_key WHERE id=:id';

              $tab['success'] = tr::$TR['success_record_test_keys'];

          }
          else{

              // UPDATE_STRIPE_KEYS -> EMPTY KEYS
              $ARR_pdo = array( 'id' => 0,
                                'test_pub_key' => '',
                                'test_priv_key' => ''   );

              $sql = 'UPDATE user_shop SET
                      test_pub_key=:test_pub_key,
                      test_priv_key=:test_priv_key WHERE id=:id';

              $tab['success'] = tr::$TR['success_delete_test_keys'];

          }

      }
      // END TEST KEYS

      //  PROD KEYS
      if( $context == 'prod' ){

          if( !empty($Prod_Pub_Key)
					&& !empty($Prod_Priv_Key) ){

              // UPDATE_STRIPE_KEYS -> encrypt and store Stripe keys
              $ARR_pdo = array(
                'id' => 0,
                'prod_pub_key' => api::api_crypt($Prod_Pub_Key, 'encr'),
                'prod_priv_key' => api::api_crypt($Prod_Priv_Key, 'encr')
              );

              $sql = 'UPDATE user_shop SET
                      prod_pub_key=:prod_pub_key,
                      prod_priv_key=:prod_priv_key WHERE id=:id';

              $tab['success'] = tr::$TR['success_record_production_keys'];

          }
          else{

              // UPDATE_STRIPE_KEYS -> EMPTY KEYS
              $ARR_pdo = array( 'id' => 0,
                                'prod_pub_key' => '',
                                'prod_priv_key' => ''   );

              $sql = 'UPDATE user_shop SET
                      prod_pub_key=:prod_pub_key,
                      prod_priv_key=:prod_priv_key WHERE id=:id';

              $tab['success'] = tr::$TR['success_delete_production_keys'];

          }

      }
      // END PROD KEYS


      $response = false;
      $last_id = false;

      //  ->  update
      $UPDATE_STRIPE_KEYS = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($UPDATE_STRIPE_KEYS) == true ){

            // IF RECORDED TEST KEYS
            if( $context == 'test' ){

                if( empty($Test_Pub_Key)
								&& empty($Test_Priv_Key) ){

	                  $tab['test_pub_key'] = false;
	                  $tab['test_priv_key'] = false;
                }
                else{

	                  $tab['test_pub_key'] = true;
	                  $tab['test_priv_key'] = true;
                }


            }
            // END  IF RECORDED TEST KEYS

            // IF RECORDED PROD KEYS
            if( $context == 'prod' ){

                if( empty($Prod_Pub_Key)
								&& empty($Prod_Priv_Key) ){

                    $tab['prod_pub_key'] = false;
                    $tab['prod_priv_key'] = false;

                }
                else{

                    $tab['prod_pub_key'] = true;
                    $tab['prod_priv_key'] = true;
                }

            }
            // END IF RECORDED PROD KEYS

            echo json_encode($tab, JSON_NUMERIC_CHECK);
            exit;


      } // END IF $UPDATE_STRIPE_KEYS
      else{

            // error
            $tab = array('error' => tr::$TR['error_update_stripe_keys'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;

      }

  }
  /**
   * settings::update_stripe_keys_user();
   */



  /**
   * settings::update_shop_for_money_pays();
   *
   * @return {type}  description
   */
  public static function update_shop_for_money_pays(){


      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);


      // CHOICE OF PAYMENT BY MONEY AND CARD
      $choice = trim(htmlspecialchars($_POST['by_money']));

      if( $choice == 'by_money' ){

          $choice = 1;
      }
      else{
          $choice = 0;
      }

      // UPDATE
      $ARR_pdo = array( 'by_money' => $choice );

      $sql = 'UPDATE user_shop SET by_money=:by_money';
      $response = false;
      $last_id = false;

      $UPDATE_BY_MONEY = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($UPDATE_BY_MONEY) == true ){

          $tab = array( 'success' => tr::$TR['pay_mode_success'],
                        'by_money' => boolval($choice) );

          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }
      else{

          // error
          $tab = array( 'error' => tr::$TR['pay_mode_error'] );

          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

  }
  /**
   * settings::update_shop_for_money_pays();
   */



	/**
	 * settings::set_settings_api( array() );
	 *
	 * SET API JSON FOR ONE OR MORE KEYS
	 * ex. settings::set_settings_api( array('VERSION' => '1.0.2', 'HOST' => 'website') );
	 *
	 * @param  {array} $API array()
	 * @return {array}     api setting array
	 */
	public static function set_settings_api( $API ){


		// fetch settings
		$get_json_settings = file_get_contents(ROOT.'/API/api.json');

		$SETTINGS = json_decode($get_json_settings, true);

		// SORT ALPHABETICAL
		ksort( $SETTINGS , SORT_STRING );
		// echo '<pre>';
		// var_export( $SETTINGS );
		// echo '<pre>';

		// loop on datas recived $k -> string key as 'MY_KEY' => 'my value'
		foreach( $API as $k => $v ) {

				// var_dump( $k ); // get the key
				// var_dump( $SETTINGS[$k] ); // get the value

				// verify if key exist, then change value
				if( array_key_exists( $k, $SETTINGS) ){

						// set new value to settings ITEM
						$SETTINGS[$k] = $v;
				}
		}
		// end loop

		// encode in json
		$json_settings = json_encode( $SETTINGS,
		JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION
		| JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		// rewrite settings
		file_put_contents(ROOT.'/API/api.json', $json_settings );

		return $SETTINGS;

	}
	/**
	 * settings::set_settings_api( array() );
	 */



	/**
	 * settings::record_api_settings();
	 *
	 * @return {json}  success / error for record API settings
	 */
	public static function record_api_settings(){


			//  VERIFY TOKEN
			$token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			// MAKE AN ARRAY TO PASS AT THE FUNCTION
			$ARR_API = array();

			// manage lang locale
			$LANGS = tools::get_locales_settings();

			// manage lang locale ex: 'af_NA'
			$lang_locale = (string) trim(htmlspecialchars($_POST['LANG_LOCALE']));

			// test length
			if( iconv_strlen($lang_locale) > 50
			|| empty($lang_locale) ){

					// error
          $tab = array( 'error' => tr::$TR['bad_context'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// test if lang exist
			if( in_array( $lang_locale, array_column($LANGS, 'code') ) ){

					// get index
					$key_lang =  array_search($lang_locale, array_column($LANGS, 'code'));

					// define lang locale ex: 'af_NA'
					$ARR_API['LANG_LOCALE'] = $LANGS[$key_lang]['code'];

					// get CURRENCY ISO code ex.: 'NAD', 'EUR'
					$ARR_API['CURRENCY_ISO'] = $LANGS[$key_lang]['currency_iso'];
			}
			else {

					// error
          $tab = array( 'error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// manage TIMEZONE
			$TIMEZONE_s = tools::get_timezones();

			// manage lang locale ex: 'af_NA'
			$TIMEZONE = (string) trim(htmlspecialchars($_POST['TIMEZONE']));

			// test length
			if( iconv_strlen($TIMEZONE) > 50
			|| empty($TIMEZONE) ){

					// error
          $tab = array( 'error' => tr::$TR['bad_context'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// test if TIMEZONE exist
			if( in_array( $TIMEZONE, array_column($TIMEZONE_s, 'timezone') ) ){

					// define TIMEZONE for PHP  ex.: 'Europe/Paris'
					$ARR_API['TIMEZONE'] = $TIMEZONE;
			}
			else {

					// error
          $tab = array( 'error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// HOST
			$HOST = (string) trim(htmlspecialchars($_POST['HOST']));

			// bad host
			if( empty($HOST)
			|| iconv_strlen($HOST) < 4
			|| iconv_strlen($HOST) > 100
			|| filter_var($HOST, FILTER_VALIDATE_DOMAIN ) == false ){

					// error
          $tab = array( 'error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include HOST
			$ARR_API['HOST'] = $HOST;


			// WEBSITE_TITLE
			$WEBSITE_TITLE = (string) trim(htmlspecialchars($_POST['WEBSITE_TITLE']));
			// bad WEBSITE_TITLE
			if( empty($WEBSITE_TITLE)
			|| iconv_strlen($WEBSITE_TITLE) > 300 ){

					$resp = ( empty($WEBSITE_TITLE) ) ? tr::$TR['empty_website_title']
					: tr::$TR['too_long_website_title'];
					// error
          $tab = array( 'error' => $resp );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include WEBSITE_TITLE
			$ARR_API['WEBSITE_TITLE'] = $WEBSITE_TITLE;


			// META_DESCR
			$META_DESCR = (string) trim(htmlspecialchars($_POST['META_DESCR']));
			// bad META_DESCR
			if( empty($META_DESCR)
			|| iconv_strlen($META_DESCR) > 600 ){

					$resp = ( empty($META_DESCR) ) ? tr::$TR['empty_website_descr']
					: tr::$TR['too_long_website_descr'];
					// error
          $tab = array( 'error' => $resp );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include META_DESCR
			$ARR_API['META_DESCR'] = $META_DESCR;



			// SIZE IMAGE SOCIALS NETWORKS
			$LOGO_SN_SIZE = (int) trim(htmlspecialchars($_POST['LOGO_SN_SIZE']));

			// test good value
			if( $LOGO_SN_SIZE < 600 || $LOGO_SN_SIZE > 1200 ){

					// error
          $tab = array( 'error' => tr::$TR['bad_value_img_sn_size'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include SIZE IMAGE
			$ARR_API['LOGO_SN_SIZE'] = $LOGO_SN_SIZE;



			// IMAGE SOCIALS NETWORKS
			if( empty($_FILES['img']['name']) ){

					// if no img for social network pass the logo as default img
					$ARR_API['LOGO_SN'] = LOGO;
			}
			else{

					$dir_path = ROOT.'/img/Logos';

					// delete old img SN
					if( file_exists( $dir_path.'/'.LOGO_SN ) ){

							unlink( $dir_path.'/'.LOGO_SN );
          }

					// 1200 px recommanded by FB
					// - here html ranger get value between 600 & 1200
					$ARR_sizes = array( 'logo-sn' => $LOGO_SN_SIZE );

					// return imgs names
					$ARR_names_imgs = tools::img_recorder( $dir_path, $ARR_sizes );

					$ARR_API['LOGO_SN'] = 'logo-sn-'.$ARR_names_imgs[0];
			}
			// END IMAGE SOCIALS NETWORKS


			// PUBLIC_NOTIFICATION_MAIL
			$PUBLIC_NOTIFICATION_MAIL =
			(string) trim(htmlspecialchars($_POST['PUBLIC_NOTIFICATION_MAIL']));

			// bad mail
			if( empty($PUBLIC_NOTIFICATION_MAIL)
			|| filter_var($PUBLIC_NOTIFICATION_MAIL, FILTER_VALIDATE_EMAIL ) == false ){

					$resp = ( empty($META_DESCR) ) ? tr::$TR['empty_public_mail']
					: tr::$TR['public_mail_no_valid'];

					// error
          $tab = array( 'error' => $resp ); // bad_context
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include PUBLIC_NOTIFICATION_MAIL
			$ARR_API['PUBLIC_NOTIFICATION_MAIL'] = $PUBLIC_NOTIFICATION_MAIL;


			// TOKEN_TIME abs() force positive value
			$TOKEN_TIME = (int) abs(trim(htmlspecialchars($_POST['TOKEN_TIME'])));
			// empty TOKEN_TIME
			if( empty($TOKEN_TIME) ){

					// error
          $tab = array( 'error' => tr::$TR['empty_token_time'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// bad token time
			if( $TOKEN_TIME < 300 || $TOKEN_TIME > 86400 ){

					// error
          $tab = array( 'error' => tr::$TR['bad_token_time'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include TOKEN_TIME
			$ARR_API['TOKEN_TIME'] = $TOKEN_TIME;


			// NB_FOR_PAGINA
			$NB_FOR_PAGINA =
			(int) abs(trim(htmlspecialchars($_POST['NB_FOR_PAGINA'])));

			$NB_FOR_PAGINA_BACKEND =
			(int) abs(trim(htmlspecialchars($_POST['NB_FOR_PAGINA_BACKEND'])));

			// empty NB_FOR_PAGINA
			if( empty($NB_FOR_PAGINA) || empty($NB_FOR_PAGINA_BACKEND) ){

					// error
          $tab = array( 'error' => tr::$TR['error_number_for_pagina'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// bad number required
			if( ($NB_FOR_PAGINA || $NB_FOR_PAGINA_BACKEND) < 1
			|| ($NB_FOR_PAGINA || $NB_FOR_PAGINA_BACKEND) > 100000 ){

					// error
          $tab = array( 'error' => tr::$TR['error_number_for_pagina'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include NB_FOR_PAGINA
			$ARR_API['NB_FOR_PAGINA'] = $NB_FOR_PAGINA;

			// include NB_FOR_PAGINA_BACKEND
			$ARR_API['NB_FOR_PAGINA_BACKEND'] = $NB_FOR_PAGINA_BACKEND;


			//  SHORT TEXT
			$SHORT_TEXT =
			(int) abs(trim(htmlspecialchars($_POST['SHORT_TEXT'])));

			// empty $SHORT_TEXT
			if( empty($SHORT_TEXT) ){

					// error
          $tab = array( 'error' => tr::$TR['error_short_text'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// bad number required
			if( $SHORT_TEXT < 1 || $SHORT_TEXT > 1000 ){

					// error
          $tab = array( 'error' => tr::$TR['error_short_text'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include SHORT_TEXT
			$ARR_API['SHORT_TEXT'] = $SHORT_TEXT;


			//  DISPLAY_PRODUCTS
			$DISPLAY_PRODUCTS =
				(string) trim(htmlspecialchars($_POST['DISPLAY_PRODUCTS']));

			// empty $SHORT_TEXT
			if( empty($DISPLAY_PRODUCTS)
			|| ($DISPLAY_PRODUCTS != 'mozaic' && $DISPLAY_PRODUCTS != 'inline')
			){

					// set mozaic by default
        	$DISPLAY_PRODUCTS != 'mozaic';
			}
			// include SHORT_TEXT
			$ARR_API['DISPLAY_PRODUCTS'] = $DISPLAY_PRODUCTS;


			// DEF_ARR_SIZES
			// ARRAY SIZES MIN AND MAX PICTURES PRODUCTS
			$DEF_ARR_SIZES_min =
			(int) abs(trim(htmlspecialchars($_POST['DEF_ARR_SIZES-min'])));
			$DEF_ARR_SIZES_max =
			(int) abs(trim(htmlspecialchars($_POST['DEF_ARR_SIZES-max'])));

			// empty DEF_ARR_SIZES
			if( empty($DEF_ARR_SIZES_min) || empty($DEF_ARR_SIZES_max) ){

					// error
          $tab = array( 'error' => tr::$TR['empty_values_def_arr_sizes'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// bad number required - min
			if( $DEF_ARR_SIZES_min < 300 || $DEF_ARR_SIZES_min > 3500 ){

					// error
          $tab = array( 'error' => tr::$TR['bad_values_def_arr_sizes'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// bad number required - max
			if( $DEF_ARR_SIZES_max < 300 || $DEF_ARR_SIZES_max > 3500 ){

					// error
          $tab = array( 'error' => tr::$TR['bad_values_def_arr_sizes'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include DEF_ARR_SIZES
			$ARR_API['DEF_ARR_SIZES'] =
			array( 'min' => $DEF_ARR_SIZES_min,
							'max' => $DEF_ARR_SIZES_max );


			// LANG_FRONT
			// manage lang front for translation API
			// ex: 'en' for /translate/en.txt
			$TR_LANGS = tr::get_translations($req="");
			// return :
			// $TR_LANGS['api_lang_FRONT'];
			// $TR_LANGS['api_lang_BACK'];

			$LANG_FRONT = (string) trim(htmlspecialchars($_POST['LANG_FRONT']));

			// test - permitted [3chars].txt
			if( iconv_strlen($LANG_FRONT) > 3 ){

					// error
          $tab = array( 'error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// translation not exist on server
			if( !in_array( $LANG_FRONT,
							array_column($TR_LANGS['api_lang_FRONT'], 'code') )  ){

					// error
          $tab = array( 'error' => tr::$TR['translation_api_front_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include LANG_FRONT
			$ARR_API['LANG_FRONT'] = $LANG_FRONT;

			// LANG_BACK
			$LANG_BACK = (string) trim(htmlspecialchars($_POST['LANG_BACK']));

			// test - to permit [3chars].txt
			if( iconv_strlen($LANG_BACK) > 3 ){

					// error
          $tab = array( 'error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// translation not exist on server
			if( !in_array( $LANG_BACK,
							array_column($TR_LANGS['api_lang_BACK'], 'code') )  ){

					// error
          $tab = array( 'error' => tr::$TR['translation_api_backend_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include LANG_BACK
			$ARR_API['LANG_BACK'] = $LANG_BACK;


			// NEW ADMIN_FOLDER asked
			$ADMIN_FOLDER =
			(string) trim(htmlspecialchars($_POST['ADMIN_FOLDER']));

			// name admin folder too long: max = 300
			if( iconv_strlen($ADMIN_FOLDER) > 300 ){

					// error
					$tab = array( 'error' => tr::$TR['too_long_name_folder_admin'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// NAME FOLDER NOT BE A DIRNAME API !
			if( in_array( $ADMIN_FOLDER, ARCH ) == true ){

					// error
					$tab = array( 'error' => tr::$TR['cannot_use_this_folder_name'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// scan directory return array
			$SCAN_dir = array_diff(scandir(ROOT), array('..', '.'));

			// make a watcher
			$find_folder_admin = false;

			// find admin folder
			foreach( $SCAN_dir as $k => $v ){

					// fin old admin folder
					if( is_dir(ROOT.'/'.$v) && $v == ADMIN_FOLDER ){

							// var_dump($v);
							// pass it old admin folder name
							$find_folder_admin = $v;
							break;
					}
			}
			// end foreach find admin folder

			// admin folder not found
			if( $find_folder_admin == false ){

					// error
					$tab = array( 'error' => tr::$TR['folder_admin_not_found'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// do we rename admin folder ?
			// if != -> rename
			if( $find_folder_admin != $ADMIN_FOLDER ){

					// RENAME DIRECTORY ADMIN FOLDER
					if( !rename( ROOT.'/'.$find_folder_admin , ROOT.'/'.$ADMIN_FOLDER ) ){

							// error
							$tab = array( 'error' => tr::$TR['unable_to_rename_folder_admin'] );
							echo json_encode($tab, JSON_FORCE_OBJECT);
							exit;
					}
			}

			// include ADMIN_FOLDER
			$ARR_API['ADMIN_FOLDER'] = $ADMIN_FOLDER;

			// SORT ALPHABETICAL
			ksort($ARR_API, SORT_STRING);

			// set api settings
			$SETTINGS = settings::set_settings_api( $ARR_API );

			// success
			$tab = array( 'success' => tr::$TR['update_success'],
										'api_settings' => $SETTINGS );
			echo json_encode($tab, JSON_FORCE_OBJECT);
			exit;


	}
	/**
	 * settings::record_api_settings();
	 */



	/**
	 * settings::update_mailbox();
	 *
	 * @return {json}  success / error for update API Mailbox
	 */
	public static function update_mailbox(){


			//  VERIFY TOKEN
			$token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			$MAILBOX_HOST = (string) trim(htmlspecialchars($_POST['MAILBOX_HOST']));
			$MAILBOX_PORT = (string) trim(htmlspecialchars($_POST['MAILBOX_PORT']));
			$MAILBOX_ACCOUNT = (string) trim(htmlspecialchars($_POST['MAILBOX_ACCOUNT']));
			$MAILBOX_PASSW = (string) trim(htmlspecialchars($_POST['MAILBOX_PASSW']));

			// something is empty
			if( empty($MAILBOX_HOST)
				  || empty($MAILBOX_PORT)
					|| empty($MAILBOX_ACCOUNT)
					|| empty($MAILBOX_PASSW) ){

					$tab = array( 'error '=> tr::$TR['empty_thing_mailbox'] );
					echo json_encode($tab);
					exit;
			}


			// TEST MAIL HOST
			if( iconv_strlen($MAILBOX_HOST) > 150 ){

					$tab = array('error' => tr::$TR['bad_ip'] );
					echo json_encode($tab);
					exit;
			}

			// TEST PORT
			if( !is_numeric($MAILBOX_PORT) || (int) $MAILBOX_PORT > 65535 ){

					$tab = array('error' => tr::$TR['bad_ip'] );
					echo json_encode($tab);
					exit;
			}

			// TEST MAIL ACCOUNT
			if( iconv_strlen($MAILBOX_ACCOUNT) > 100 ){

					$tab = array('error' => tr::$TR['too_large_mail'] );
					echo json_encode($tab);
					exit;
			}

			// IF BAD FORMAT MAIL ACCOUNT
			if( filter_var($MAILBOX_ACCOUNT, FILTER_VALIDATE_EMAIL ) == false ){

					$tab = array('error' => tr::$TR['bad_mail'] );
					echo json_encode($tab);
					exit;
			}

			// TEST TOO LONG PASSORD
			if( iconv_strlen($MAILBOX_PASSW) > 1200 ){

					$tab = array('error' => tr::$TR['bad_login'] );
					echo json_encode($tab);
					exit;
			}

			// GET API/config.php
			$get_old_config = file_get_contents( ROOT.'/API/config.php' );

			$PATTERNS = array(
				'/const MAILBOX_HOST(.*);/',
				'/const MAILBOX_PORT(.*);/',
				'/const MAILBOX_ACCOUNT(.*);/',
				'/const MAILBOX_PASSW(.*);/'
			);
			$REPLACEMENTS = array(
					'const MAILBOX_HOST = "'.$MAILBOX_HOST.'";',
					'const MAILBOX_PORT = "'.$MAILBOX_PORT.'";',
					'const MAILBOX_ACCOUNT = "'.$MAILBOX_ACCOUNT.'";',
					'const MAILBOX_PASSW = "'.$MAILBOX_PASSW.'";'
			);

			// replace datas of Mailbox
			$new_config = preg_replace($PATTERNS, $REPLACEMENTS, $get_old_config);


			// RECORD CONFIG FILE WITH NEW MAILBOX SETTINGS
			if( file_put_contents( ROOT.'/API/config.php', $new_config ) ){

					$tab = array('success' => tr::$TR['mailbox_well_recorded'] );
					echo json_encode($tab);
					exit;
			}
			else{

					// error
					$tab = array('error' => tr::$TR['mailbox_rec_error'] );
					echo json_encode($tab);
					exit;
			}

	}
	/**
	 * settings::update_mailbox();
	 */



	/**
	 * settings::init_production_mode();
	 *
	 * Switch to production mode :
	 * - Delete new_sales + customers + sold_products
	 * - Delete archived_sales
	 * - Delete all statistics
	 *
	 * @return {json}  success/error
	 */
	public static function init_production_mode(){


			//  VERIFY TOKEN
			$token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

		  // prepa. db::server()
			$ARR_pdo = false;
			$response = false;
			$last_id = false;

			// make an array of SQL requests
			$SQL = array(
				'DELETE FROM archived_sales',
				'DELETE FROM customers; ALTER TABLE customers AUTO_INCREMENT = 1',
				'DELETE FROM new_sales; ALTER TABLE new_sales AUTO_INCREMENT = 1',
				'DELETE FROM sold_products; ALTER TABLE sold_products AUTO_INCREMENT = 1',

				'DELETE FROM stats_cart',
				'DELETE FROM stats_loca',
				'DELETE FROM stats_prods',

			);

			// delete all on a loop
			foreach ( $SQL as $sql ) {

					try{

							// execute SQL requests along the loop
							db::server($ARR_pdo, $sql, $response, $last_id);
					}
					catch(Throwable $t){

							// error
							$ARR = array( 'error' => $t->getMessage() );
							echo json_encode( $ARR, JSON_FORCE_OBJECT);
							exit;
					}

			}
			// end loop delete all


			// success
			$ARR = array( 'success' => tr::$TR['update_shop_success'] );
			echo json_encode( $ARR, JSON_FORCE_OBJECT);
			exit;

	}
	/**
	 * settings::init_production_mode();
	 */



	/**
	 * settings::record_Token_Placido_User();
	 *
	 * Record or delete an encrypted Placdio-Shop user token
	 *
	 * @return {json}  success/error
	 */
	public static function record_Token_Placido_User(){


			//  VERIFY TOKEN
			$token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			$token_placido = (string) trim(htmlspecialchars($_POST['token_placido']));

			// empty token allowed
			$token_placido = ( empty($token_placido) )
			? ''
			: api::api_crypt($token_placido, 'encr');

			// prepa. db::server()
			$ARR_pdo = array( 'id' => 0, 'token' =>  $token_placido );
			$response = false;
			$last_id = false;

			// SQL request
			$sql = 'INSERT INTO token_Placido (token) VALUES (:token)
			ON DUPLICATE KEY UPDATE token=:token';

			try{

					// execute SQL requests along the loop
					db::server($ARR_pdo, $sql, $response, $last_id);
			}
			catch(Throwable $t){

					// error
					$ARR = array( 'error' => $t->getMessage() );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}


			// success
			$ARR = array( 'success' => tr::$TR['token_well_recored'] );
			echo json_encode( $ARR, JSON_FORCE_OBJECT);
			exit;

	}
	/**
	 * settings::record_Token_Placido_User();
	 */




}
// END class settings::
?>
