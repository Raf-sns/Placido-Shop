<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2021-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	settings.php
 *
 * settings::fetch_mail_admin( $mail );
 * settings::get_admins_list( $mail_current_admin );
 * settings::get_admin_by_id( $id );
 * settings::add_new_admin();
 * settings::delete_admin();
 * settings::update_access_admin();
 * settings::update_stripe_keys_user();
 * settings::update_shop_for_money_pays();
 * settings::set_settings_api( array() );
 * settings::record_api_settings();
 * settings::update_mailbox();
 * settings::init_production_mode();
 * settings::record_Token_Placido_User();
 *
 */

class settings {


  /**
   * settings::fetch_mail_admin($mail);
   *
   * @param  {string}  $mail
   * @return {mixed}   {bool} false if mail don't exist
   *                   || {string} mail if exist
   */
  public static function fetch_mail_admin($mail){


      $ARR_pdo = array( 'mail' => $mail );
      $sql = 'SELECT mail FROM admins WHERE mail=:mail';
      $response = 'one';
      $last_id = false;

      //  ->  fetch
      $FETCH_MAIL_ADMIN = db::server($ARR_pdo, $sql, $response, $last_id);

      // TEST RETURNED VALUE
      if( boolval($FETCH_MAIL_ADMIN) == true AND $FETCH_MAIL_ADMIN['mail'] == $mail ){

          return $FETCH_MAIL_ADMIN['mail'];
      }
      else{

          return false;
      }

  }
  /**
   * settings::fetch_mail_admin($mail);
   */



  /**
   * settings::get_admins_list( $mail_current_admin );
   *
   * @return {array}  get all administrators of the website
   */
  public static function get_admins_list( $mail_current_admin ){

      $ARR_pdo = array( 'mail_current_admin' => $mail_current_admin );
      $sql = 'SELECT id, name, mail FROM admins WHERE NOT mail=:mail_current_admin';
      $response = 'all';
      $last_id = false;
      $ADMINS_LIST = db::server($ARR_pdo, $sql, $response, $last_id);

      return $ADMINS_LIST;
  }
  /**
   * settings::get_admins_list( $mail_current_admin );
   */



  /**
   * settings::get_admin_by_id( $id );
   *
   * @return {mixed} {bool} false OR {int} one administrator id
   */
  public static function get_admin_by_id( $id ){


      $ARR_pdo = array( 'id' => $id );
      $sql = 'SELECT id, mail FROM admins WHERE id=:id';
      $response = 'one';
      $last_id = false;
      $ADMIN = db::server($ARR_pdo, $sql, $response, $last_id);

      // TEST RETURNED VALUE
      if( boolval($ADMIN) == false ){

          // error
          return false;
      }
      else if( boolval($ADMIN['id']) == false ){

          // admin with id=0 -> false ! Not update sup admin
          return false;
      }
      else{

          return array(
            'id' => (int) $ADMIN['id'],
            'mail' => (string) $ADMIN['mail']
          );
      }

  }
  /**
   * settings::get_admin_by_id( $id );
   */



  /**
   * settings::add_new_admin();
   *
   * @return {json}  add new admin
   */
  public static function add_new_admin(){


      // VERIFY token
      $user_id = token::verify_token();

      // action
      $action = (string) trim(htmlspecialchars($_POST['action']));

      // verify action
      if( !in_array($action, array('add','modify'), true) ){

          $tab = array('error' => tr::$TR['error_gen'] );
          echo json_encode($tab);
          exit;
      }

      // include verification fields for admins forms
      include 'verify_admin_fields.php';


      // include mail_current_admin for renew list without admin
      $mail_current_admin = (string) trim(htmlspecialchars($_POST['mail_current_admin']));

      // admin can't add same email admin
      if( ( $action == 'add'
            && settings::fetch_mail_admin($mail) == $mail )
          || ( $action == 'modify'
               && $mail_current_admin == $mail ) ){

          $tab = array('error' => tr::$TR['unable_renew_password'] );
          echo json_encode($tab);
          exit;
      }

      // Make a password hash for a new admin
      $new_pass = password_hash($mail.$passw, PASSWORD_DEFAULT);

      // RECORD NEW ADMIN
      $ARR_pdo = array(
        'id' => 0,
        'mail' => $mail,
        'passw' => $new_pass,
        'name' => $name,
      );

      // MODIFY ACTION
      if( $action == 'modify' ){

          // verify good id
          $id = (int) trim(htmlspecialchars($_POST['id']));

          // update PDO array
          $ARR_admin = settings::get_admin_by_id( $id );

          $ARR_pdo['id'] = $ARR_admin['id'];

          // error fetch admin ID or id == 0 -> sup admin can't be updated
          if( boolval($ARR_pdo['id']) == false ){

              $tab = array('error' => tr::$TR['error_gen'] );
              echo json_encode($tab);
              exit;
          }

          // watch for duplicates
          $mail_asked = $mail;

          // if the requested email is different from the original email
          // and an email is found in the database -> error
          if( $mail_asked != $ARR_admin['mail']
          && settings::fetch_mail_admin( $mail_asked ) == $mail_asked ){

              $tab = array('error' => tr::$TR['unable_renew_password'] );
              echo json_encode($tab);
              exit;
          }

          // UPDATE SQL COMMAND
          $sql = 'UPDATE admins SET
          mail=:mail, passw=:passw, name=:name
          WHERE id=:id';
      }
      else{

          // ADD NEW ADMIN COMMAND
          $sql = 'INSERT INTO admins (id, mail, passw, name)
          VALUES (:id, :mail, :passw, :name)';
      }

      $response = false;
      $last_id = false;

      $RECORD_NEW_ADMIN = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($RECORD_NEW_ADMIN) == false ){

          unset($_POST);

          $tab = array('error' => tr::$TR['unable_renew_password'] );
          echo json_encode($tab);
          exit;
      }

      // success response
      $tab = array(
        'success' => tr::$TR['update_success'],
        'admins_list' => settings::get_admins_list($mail_current_admin)
      );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;
  }
  /**
   * settings::add_new_admin();
   */



  /**
   * settings::delete_admin();
   *
   * @return {json}  admin delete an other admin
   */
  public static function delete_admin(){


      // VERIFY token
      token::verify_token();

      // mail with readonly
      $mail = (string) trim(htmlspecialchars($_POST['mail']));

      // include mail_current_admin for renew list without admin
      $mail_current_admin = (string) trim(htmlspecialchars($_POST['mail_current_admin']));

      // id of admin to delete
      $id = (int) trim(htmlspecialchars($_POST['id']));

      // verify good id if admin id = 0 -> false
      $admin_id = settings::get_admin_by_id( $id )['id'];

      if( boolval($admin_id) == false ){

          unset($_POST);

          $tab = array('error' => tr::$TR['error_gen'] );
          echo json_encode($tab);
          exit;
      }


      // prepa. ARR. PDO
      $ARR_pdo = array(
        'id' => $admin_id,
        'mail' => $mail
      );

      // DELETE FORM admins with id and mail
      $sql = 'DELETE FROM admins WHERE id=:id AND mail=:mail';

      $response = false;
      $last_id = false;

      $DELETE_ADMIN = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($DELETE_ADMIN) == false ){

          unset($_POST);

          $tab = array('error' => tr::$TR['error_gen'] );
          echo json_encode($tab);
          exit;
      }

      // success response
      $tab = array(
        'success' => tr::$TR['update_success'],
        'admins_list' => settings::get_admins_list($mail_current_admin)
      );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;
  }
  /**
   * settings::delete_admin();
   */



  /**
   * settings::update_access_admin();
   *
   * @return {array}  new js user object
   */
  public static function update_access_admin(){


      // VERIFY token
      $user_id = token::verify_token();

      // include verification fields for admins forms
      include 'verify_admin_fields.php';


      // VERIFY MAIL !!! -> for not set an another user by his e-mail
      // fetch old mail user BY ID - already verified by token
      $ARR_pdo = array('id' => $user_id );
      $sql = 'SELECT mail FROM admins WHERE id=:id';
      $response = 'one';
      $last_id = false;
      $OLD_MAIL_USER = db::server($ARR_pdo, $sql, $response, $last_id);

      // VERIFY MAIL ASKED
      $mail_asked = settings::fetch_mail_admin($mail);

      // IF MAIL != OLD_MAIL AND if $mail_asked == true (still exist) -> error
      if( $mail != $OLD_MAIL_USER['mail'] && boolval($mail_asked) == true ){

            $tab = array('error' => tr::$TR['unable_renew_password'] );
            echo json_encode($tab);
            exit;
      }
      // END VERIFY MAIL !!!


      // Make a new password hash
      $new_pass = password_hash($mail.$passw, PASSWORD_DEFAULT);

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

      // RE-INIT COOCKIE
      setcookie(
				"PL-GEST-mail", // name
				api::api_crypt( $mail, 'encr' ), // value
				time()+TOKEN_TIME, // expires + TOKEN_TIME
				'/'.ADMIN_FOLDER.'/', // allowed folder path
				HOST, // domain
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
          token::set_token( $user_id, $mail, $passw )
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


      // VERIFY token
      $user_id = token::verify_token();

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


      // VERIFY token
      token::verify_token();

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
	 * @param  {array} $ARR_API array()
	 * @return {array} api settings array c.f.: API/constants.php
	 */
	public static function set_settings_api( $ARR_API ){


      // path of the constants file
      $file_path = ROOT.'/API/constants.php';

      // fetch constants file
      $consts = file_get_contents( $file_path );

      // prepare patterns array
      $Patterns = array();

      // prepare the substitution array
      $Replacements = array();

      // loop over API DATA array
      foreach ( $ARR_API as $k => $v ) {

          // push pattern dynamically
          $Patterns[] = '/const '.$k.' = (.*);/';

          // special behaviour for DEF_ARR_SIZES
          if( $k == 'DEF_ARR_SIZES' ){

              $Replacements[] =
              'const '.$k.' = array("min" => '.(int)$v['min'].', "max" => '.(int)$v['max'].');';
              continue;
          }

          // special behaviour for SLIDER
          if( $k == 'SLIDER' ){

              $Replacements[] =
              'const '.$k.' = array("display" => '.(int)$v['display'].', "play" => '.(int)$v['play'].', "delay" => '.(int)$v['delay'].', "speed" => '.(int)$v['speed'].');';
              continue;
          }

          // push replacements dynamically - watch for integer or string
          $Replacements[] =
          ( is_string($v) )
          ? 'const '.$k.' = "'.$v.'";'
          : 'const '.$k.' = '.(int)$v.';';

      }
      // end loop over API DATA array

      // replace all constants with their correct values
      $consts = preg_replace( $Patterns, $Replacements, $consts );

      try{

          // record the constants file
          file_put_contents( $file_path, $consts, LOCK_EX );
      }
      catch( Exception $e ){

          // error case
          $tab = array( 'error' => "Error : ".$e->getMessage()."
                                    <br>
                                    Set settings api failed" );
          echo json_encode($tab);
          exit;
      }

      // in some cases need to return ARRAY API SETTINGS
      return $ARR_API;

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


			// VERIFY token
      token::verify_token();

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
			$META_DESCR = (string) trim(htmlspecialchars($_POST['META_DESCR'], ENT_NOQUOTES));
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
      // remove too much spaces and line breaks
			$ARR_API['META_DESCR'] = $META_DESCR;
      $ARR_API['META_DESCR'] = preg_replace('/\s\s+/', ' ', $ARR_API['META_DESCR']);


      // ALLOW_SEARCH_ENGINES
      $ALLOW_SEARCH_ENGINES = (int) trim(htmlspecialchars($_POST['ALLOW_SEARCH_ENGINES']));
      // bad datas
      if( $ALLOW_SEARCH_ENGINES != 1 && $ALLOW_SEARCH_ENGINES != 0 ){

          // error
          $tab = array(
            'error' => tr::$TR['allow_search_engines'].' : '.tr::$TR['bad_context']
          );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }
      // include ALLOW_SEARCH_ENGINES
      $ARR_API['ALLOW_SEARCH_ENGINES'] = $ALLOW_SEARCH_ENGINES;

      // record robots.txt in context
      $context = ( $ARR_API['ALLOW_SEARCH_ENGINES'] == 0 ) ? null : 'allow';

      // record robots.txt with the good host for Sitemap url
      tools::record_robots_txt( $ARR_API['HOST'], $context );


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

					// delete old img logo SN
					array_map('unlink', glob(ROOT.'/img/Logos/logo-sn*'));

					$dir_path = ROOT.'/img/Logos';

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
        	$DISPLAY_PRODUCTS = 'mozaic';
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
			settings::set_settings_api( $ARR_API );

			// success
			$tab = array( 'success' => tr::$TR['update_success'],
										'api_settings' => $ARR_API );
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


			// VERIFY token
      token::verify_token();

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
   * - empty the sitemap.xml file
	 *
	 * @return {json}  success/error
	 */
	public static function init_production_mode(){


			// VERIFY token
      token::verify_token();

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
				'DELETE FROM stats_prods'
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

      // empty the sitemap.xml file
      sitemap::empty_the_sitemap();

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


			// VERIFY token
      token::verify_token();

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
