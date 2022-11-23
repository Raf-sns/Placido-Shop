<?php
/*
* © Copyright Castello Raphaël, 2019-2022
* Organisation: SNS - Web et Informatique
* Web site: https://sns.pm
* @link <contact@sns.pm>
*
* Script name:	install.php
*
* Class install::
*
* install::page_install();
* install::test_database( $context );
* install::record_config_api();
* install::insert_tables( $db_host, $db_name, $db_user, $db_passw );
* install::record_admin( $db_host, $db_name, $db_user, $db_passw );
* install::record_shop( $db_host, $db_name, $db_user, $db_passw );
* install::set_settings_api( array() );
* install::record_api_settings( $logo_shop );
* install::delete_install_folder();
* install::install_app();
* test :
* $config_file = 'test_config.php'
* $json_file = 'test_api.json';
*
*/


class install {


  /**
   * install::page_install();
   *
   * @return {html}  Home page installation
   */
  public static function page_install(){


				// require ADMIN/tools.php
				require ROOT.'/ADMIN/PHP/tools.php';

				// require API/tr.php
				require ROOT.'/API/tr.php';
				$API_LANGS = tr::get_translations($req="");

				// get html files
        $homepage = file_get_contents('install.html');

				$api_database_form = file_get_contents('api_database_form.html');

				$api_settings_form = file_get_contents('api_settings_form.html');

				$ARR = array(
					'db_enabled' => false,
					'api_settings' => API_SETTINGS, // const defined by api::init_settings();
					'api_locales_list' => tools::get_locales_settings(),
					'api_lang_FRONT' => $API_LANGS['api_lang_FRONT'],
					'api_lang_BACK' => $API_LANGS['api_lang_BACK'],
					'api_timezones_list' => tools::get_timezones()
				);

				// add partial API database form
				$partial = new Mustache_Engine();
				$ARR['api_database_form'] = $partial->render($api_database_form, $ARR);

				// add partial API setting form
				$partial = new Mustache_Engine();
				$ARR['api_settings_form'] = $partial->render($api_settings_form, $ARR);


				// render installation home page
				$m = new Mustache_Engine( array('entity_flags' => ENT_QUOTES));
				echo $m->render($homepage, $ARR);
        exit;

  }
  /**
   * install::page_install();
   */



  /**
   * install::test_database( $context );
   *
   * @param  {string} $context  'api' / ''
   * @return {json}             test if the database in well installed
   */
  public static function test_database( $context ){


			$error = '';

			// DB HOST
			$data_base_host = (string) trim(htmlspecialchars($_POST['data_base_host']));

			if( empty($data_base_host) ){

					$error = 'You must enter a host or "localhost" for the database host';
      }

      if( iconv_strlen($data_base_host) > 300 ){

					$error = 'You must enter a host name of less than 300 characters';
      }
			if( !empty($error) ){

					$tab = array( 'error' => $error );
					echo json_encode($tab);
					exit;
			}


      // DATABASE NAME
			$data_base_name = (string) trim(htmlspecialchars($_POST['data_base_name']));

      if( empty($data_base_name) ){

					$error = 'You must enter a name for the database';

      }
      if( iconv_strlen($data_base_name) > 300 ){

					$error = 'You must enter a database name of less than 300 characters';

      }

			if( !empty($error) ){

					$tab = array( 'error' => $error, 'el' => 'data_base_name' );
					echo json_encode($tab);
					exit;
			}


      // DB USER NAME
			$data_base_user = (string) trim($_POST['data_base_user']);

			if( empty($data_base_user) ){

					$error = 'For the database, you must specify a username';
      }
      if( iconv_strlen($data_base_user) > 300 ){

					$error = 'You must enter a database username of less than 300 characters';
      }

			if( !empty($error) ){

					$tab = array( 'error' => $error, 'el' => 'data_base_user' );
					echo json_encode($tab);
					exit;
			}


      // BD PASSWORD
			$data_base_passw = (string) trim($_POST['data_base_passw']);

			if( empty($data_base_passw) ){

					$error = 'For the database, you must specify a password';
      }
      if( iconv_strlen($data_base_passw) > 300 ){

					$error = 'You must enter a database password of less than 300 characters';
      }
			if( !empty($error) ){

					$tab = array( 'error' => $error, 'el' => 'data_base_passw' );
					echo json_encode($tab);
					exit;
			}


      // IS BD CREATED
      try {
          $bdd = new PDO( "mysql:host=$data_base_host", $data_base_user, $data_base_passw );
          $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $sql = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA
					WHERE SCHEMA_NAME=:dbname';
          $request = $bdd->prepare($sql);
          $request->execute(array('dbname' => $data_base_name));
          $CHECK_database_exist = $request->fetch(PDO::FETCH_ASSOC);

          // var_dump( boolval($CHECK_database_exist));

          if( boolval($CHECK_database_exist) != false ){

              if( !empty($context) && $context == 'api' ){

                  // prepa tab return - final registration context
									$TAB['data_base_host'] = $data_base_host;
                  $TAB['data_base_name'] = $data_base_name;
                  $TAB['data_base_user'] = $data_base_user;
                  $TAB['data_base_passw'] = $data_base_passw;
                  return $TAB;
              }
              else{

                echo json_encode( array('success' => 'The database is well recorded.') );
                exit;
              }
          }
          else{

              echo json_encode( array(  'error' =>
																				'Error, The database is not responding.') );
              exit;
          }

      }
      catch(PDOException $e){

          $tab = array('error' =>
					"Error, the database is not responding ...
					<br>
           - Check the data entered in the required fields
           <br>
          - Make sure you have created a database and assigned a user to it
          <br>
          - Add any prefixes related to your web hosting ex.: MyHost_... " );

          echo json_encode($tab);
          exit;

      }
      // end CATCH

  }
  /**
   * install::test_database( $context );
   */



	/**
	 * install::record_config_api();
	 *
	 * @return {type}  description
	 */
	public static function record_config_api(){


			// MODIFY FOR TEST / PRODUCTION
			$config_file = 'config.php'; // test_config.php

			// test database
			$DATA_BASE = install::test_database($context='api');

			$db_host = $DATA_BASE['data_base_host'];
			$db_name = $DATA_BASE['data_base_name'];
			$db_user = $DATA_BASE['data_base_user'];
			$db_passw = $DATA_BASE['data_base_passw'];


			// RECORD ACCESS DATABASE
			$get_config = file_get_contents( ROOT.'/API/'.$config_file );
			// var_dump( $get_config );

			$PATTERNS = array(
				'/const DB_HOST(.*);/',
				'/const DB_NAME(.*);/',
				'/const DB_USER(.*);/',
				'/const DB_PASSWORD(.*);/'
			);
			$REPLACEMENTS = array(
					'const DB_HOST = "'.preg_quote($db_host, '/').'";',
					'const DB_NAME = "'.preg_quote($db_name, '/').'";',
					'const DB_USER = "'.preg_quote($db_user, '/').'";',
					'const DB_PASSWORD = "'.preg_quote($db_passw, '/').'";'
			);

			$get_config = preg_replace($PATTERNS, $REPLACEMENTS, $get_config);
			// var_dump( '-------- RECORDED --------------' );
			// var_dump( $get_config );

			// END RECORD ACCESS DATABASE


			// RECORD MAILBOX ACCESS
			$mail_box_host = (string) trim($_POST['MAILBOX_HOST']);
			$mail_box_port = (int) trim($_POST['MAILBOX_PORT']);
			$mail_box_mail = (string) trim($_POST['MAILBOX_ACCOUNT']);
			$mail_box_passw = (string) trim($_POST['MAILBOX_PASSW']);

			// verify  MAILBOX_HOST
			if( empty($mail_box_host) || iconv_strlen($mail_box_host) > 150 ){

					$tab = array('error' =>
					"Please enter a mail server for your mailbox (under 150 characters max.)",
					'el' => 'MAILBOX_HOST' );
					echo json_encode($tab);
					exit;
			}
			// verify  MAILBOX_PORT
			if( empty($mail_box_port) || iconv_strlen( (string) $mail_box_port ) > 5 ){

					$tab = array('error' =>
					"Please enter a port for your mailbox",
					'el' => 'MAILBOX_PORT' );
					echo json_encode($tab);
					exit;
			}
			// verify  MAILBOX_ACCOUNT
			if( empty($mail_box_mail)
			|| iconv_strlen( $mail_box_mail ) > 150
		  || filter_var($mail_box_mail, FILTER_VALIDATE_EMAIL ) == false ){

					$tab = array('error' =>
					"Please enter a valid email address for your mailbox",
					'el' => 'MAILBOX_ACCOUNT' );
					echo json_encode($tab);
					exit;
			}
			// verify  MAILBOX_PASSW
			if( empty($mail_box_passw)
			|| iconv_strlen( $mail_box_passw ) > 150 ){

					$tab = array('error' =>
					"Please enter a valid email address for your mailbox",
					'el' => 'MAILBOX_PASSW' );
					echo json_encode($tab);
					exit;
			}

			$PATTERNS = array(
				'/const MAILBOX_HOST(.*);/',
				'/const MAILBOX_PORT(.*);/',
				'/const MAILBOX_ACCOUNT(.*);/',
				'/const MAILBOX_PASSW(.*);/'
			);
			$REPLACEMENTS = array(
					'const MAILBOX_HOST = "'.$mail_box_host.'";',
					'const MAILBOX_PORT = "'.$mail_box_port.'";',
					'const MAILBOX_ACCOUNT = "'.$mail_box_mail.'";',
					'const MAILBOX_PASSW = "'.$mail_box_passw.'";'
			);

			$get_config = preg_replace($PATTERNS, $REPLACEMENTS, $get_config);

			// END RECORD MAILBOX ACCESS


			// CALCUL SEC_API_KEY + SEC_API_IV
			function get_rand_string(){

					$ALPHA = array("m","e","u","B","v","j","Y","a","s","f","U","Z","R","q","n","L","K","W","g","w","t","d","M","k","i","Q","D","c","r","O","A","I","l","P","z","y",
					"b","x","o","p","X","h","S","N","V","F","J","G","T","E","H","C");

					// length of key
					$num = random_int(50, 100);

					$key = '';

					// loop for construct key
					for ($i=0; $i < $num; $i++) {

							// get a rand index
							$index = random_int(0, (count($ALPHA)-1) );
							// insert a random letter
							$key .= $ALPHA[$index];
					}

					return $key;
			}

			$SEC_API_KEY = get_rand_string();
			$SEC_API_IV = get_rand_string();

			$PATTERNS = array(
				'/const SEC_API_KEY(.*);/',
				'/const SEC_API_IV(.*);/'
			);
			$REPLACEMENTS = array(
					'const SEC_API_KEY = "'.$SEC_API_KEY.'";',
					'const SEC_API_IV = "'.$SEC_API_IV.'";'
			);

			$get_config = preg_replace($PATTERNS, $REPLACEMENTS, $get_config);
			// END CALCUL SEC_API_KEY + SEC_API_IV

			// RECORD FILE WITH USER SETTINGS
			file_put_contents( ROOT.'/API/'.$config_file, $get_config );

			// return data_base logins access
			return $DATA_BASE;

	}
	/**
	 * install::record_config_api();
	 */



  /**
   * install::insert_tables( $db_host, $db_name, $db_user, $db_passw );
   *
   * @param  {string} $db_host
   * @param  {string} $db_name
   * @param  {string} $db_user
   * @param  {string} $db_passw
   * @return {bool} return true if insert sucessfully all API tables
   */
  public static function insert_tables( $db_host, $db_name, $db_user, $db_passw ){


      // INSER TABLES IN DB
      try {

          $db = new PDO("mysql:host=$db_host;dbname=$db_name",$db_user, $db_passw);

          // setting the PDO error mode to exception
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // sql code to create table
          $sql = file_get_contents('placido-tables.sql');

          // using exec() because no results are returned
          $db->exec($sql);

          return true;

      }
			catch(PDOException $e){

            $tab = array('error' => "Error : ".$e->getMessage()."
						<br>
            The database tables could not be created
						<br>
						Check database user privileges");
						echo json_encode($tab);
						exit;

			}

  }
  /**
   * install::insert_tables( $db_host, $db_name, $db_user, $db_passw );
   */



	/**
	 * install::record_admin( $db_host, $db_name, $db_user, $db_passw );
	 *
	 * @return {type}  description
	 */
	public static function record_admin( $db_host, $db_name, $db_user, $db_passw ){



			// admin login mail
			$login_mail = trim(htmlspecialchars($_POST['login_mail']));

			// empty mail
			if( empty($login_mail) ){

					$tab = array('error' =>
					"Please, enter a private email for connecting to your management interface",
					'el' => 'login_mail' );
					echo json_encode($tab);
					exit;
			}

			// too long mail or bad format
			if( iconv_strlen($login_mail) > 300
			|| filter_var($login_mail, FILTER_VALIDATE_EMAIL ) == false ){

					$tab = array('error' =>
					"Your login email is not correct or longer than 300 characters",
					'el' => 'login_mail' );
					echo json_encode($tab);
					exit;
			}


			// admin login password
			$login_passw = trim($_POST['login_passw']);

			// empty password
			if( empty($login_passw) ){

					$tab = array('error' =>
					"Please, enter a password for connecting to your management interface",
					'el' => 'login_passw' );
					echo json_encode($tab);
					exit;
			}

			// too long password
			if( iconv_strlen($login_passw) > 300 ){

					$tab = array('error' =>
					"Your login password is not correct or longer than 300 characters",
					'el' => 'login_passw' );
					echo json_encode($tab);
					exit;
			}


			// admin name
			$admin_name = trim($_POST['admin_name']);

			if( empty($admin_name) ){

					$tab = array('error' =>
					"Please, enter a name or a nickname for your management interface",
					'el' => 'admin_name' );
					echo json_encode($tab);
					exit;
			}

			// too long password
			if( iconv_strlen($admin_name) > 300 ){

					$tab = array('error' =>
					"Your name or a nickname is longer than 300 characters",
					'el' => 'admin_name' );
					echo json_encode($tab);
					exit;
			}

			// END FIELDS VERIFICATION


			// hash password
			$admin_passw = password_hash($login_mail.$login_passw, PASSWORD_DEFAULT);


			// RECORD NEW PASS IN DB
			$ARR_pdo = array(
				'id' => 0,
				'mail' => $login_mail,
				'passw' => $admin_passw,
				'name' => $admin_name
			);

			$sql = 'INSERT INTO admins ( id, mail, passw, name )
			VALUES ( :id, :mail, :passw, :name )
			ON DUPLICATE KEY UPDATE mail=:mail, passw=:passw, name=:name';

			// INSERT DATAS ADMIN
      try {

          $db = new PDO("mysql:host=$db_host;dbname=$db_name",$db_user, $db_passw);

          // setting the PDO error mode to exception
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					// prepa. SQL
		      $request = $db->prepare($sql);

          // execute
          $request->execute($ARR_pdo);

          return true;

      }
			catch(PDOException $e){

            $tab = array('error' => "Error : ".$e->getMessage()."
						<br>
            Management account data has not been saved" );
						echo json_encode($tab);
						exit;

			}

	}
	/**
	 * install::record_admin( $db_host, $db_name, $db_user, $db_passw );
	 */



	/**
	 * install::record_shop( $db_host, $db_name, $db_user, $db_passw );
	 *
	 * @return {type}  description
	 */
	public static function record_shop( $db_host, $db_name, $db_user, $db_passw ){


      //  MAKE LOGO IF EXIST - if not img, it's ok
      if( empty($_FILES['LOGO']) || (int) $_FILES['LOGO']['size'] == 0 ){

				  $tab = array('error' =>
						"Please enter a logo for your website",
						'el' => 'LOGO' );
					echo json_encode($tab);
					exit;
			}

			// DELETE OLD IMG SHOP
      array_map('unlink', glob(ROOT.'/img/Logos/logo-shop*'));

			// make a fake $_FILES['img'] for re-use function tools::img_recorder( ... )
			$_FILES['img']['name'][0] = $_FILES['LOGO']['name'];
			$_FILES['img']['type'][0] = $_FILES['LOGO']['type'];
			$_FILES['img']['tmp_name'][0] = $_FILES['LOGO']['tmp_name'];
			$_FILES['img']['error'][0] = $_FILES['LOGO']['error'];
			$_FILES['img']['size'][0] = $_FILES['LOGO']['size'];

      $dir_path = ROOT.'/img/Logos';

      //  -> must add anothers this return 'logo-' + name img logo
      $ARR_sizes = array( 'logo-shop' => 600 );

			// require ADMIN/tools.php
			require ROOT.'/ADMIN/PHP/tools.php';

      // this return array of names imgs NOT prefixed
      $NEW_logo = tools::img_recorder( $dir_path, $ARR_sizes );

			$logo_shop = 'logo-shop-'.$NEW_logo[0];

      // var_dump( $logo_shop );


      // RECORD or UPDATE USER_SHOP
      $ARR_pdo = array( 'id' => 0,
                        'mode' => 1, // 1 mode sale // 0 mode catalog
												'by_money' => 1 // Allow cash purchases so you can test as soon as you install
                      );

			$sql = 'INSERT INTO user_shop
			( id,	mode, by_money ) VALUES ( :id, :mode, :by_money )
			ON DUPLICATE KEY UPDATE mode=:mode,	by_money=:by_money';

      // INSERT DATAS USER_SHOP
      try {

          $db = new PDO("mysql:host=$db_host;dbname=$db_name",$db_user,$db_passw);

          // setting the PDO error mode to exception
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					// prepa. SQL
		      $request = $db->prepare($sql);

          // execute
          $request->execute($ARR_pdo);

          // return logo-shop file name
					return $logo_shop;

      }
			catch(PDOException $e){

            $tab = array('error' => "Error : ".$e->getMessage()."
						<br>
            Record shop failed" );
						echo json_encode($tab);
						exit;
			}

	}
	/**
	 * install::record_shop(  $db_host, $db_name, $db_user, $db_passw );
	 */



	/**
	 * install::set_settings_api( array() );
	 *
	 * SET API JSON FOR ONE OR MORE KEYS
	 * ex. settings::set_settings_api( array('VERSION' => '1.0.2', 'HOST' => 'website') );
	 *
	 * @param  {array} $API array()
	 * @return {array}     api setting array
	 */
	public static function set_settings_api( $API ){


		$json_file = 'api.json';

		// fetch settings
		$get_json_settings = file_get_contents( ROOT.'/API/'.$json_file );

		$SETTINGS = json_decode($get_json_settings, true);

		// SORT ALPHABETICAL
		ksort( $SETTINGS , SORT_STRING );
		// echo '<pre>';
		// var_export( $SETTINGS );
		// echo '<pre>';

		// loop on datas recived $k -> string key as 'MY_KEY' => 'my value'
		foreach( $API as $k => $v ) {

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
		file_put_contents( ROOT.'/API/'.$json_file, $json_settings );

		return true;

	}
	/**
	 * install::set_settings_api( array() );
	 */



	/**
	 * install::record_api_settings( $logo_shop );
	 *
	 * @return {json}  success / error for record API settings
	 */
	public static function record_api_settings( $logo_shop ){



			// MAKE AN ARRAY TO PASS AT THE FUNCTION
			$ARR_API = array();

			// manage lang locale
			$LANGS = tools::get_locales_settings();

			// manage lang locale ex: 'af_NA'
			$lang_locale = (string) trim(htmlspecialchars($_POST['LANG_LOCALE']));

			// test if lang exist
			if( in_array( $lang_locale, array_column($LANGS, 'code') )
			&& iconv_strlen($lang_locale) < 50
			&& !empty($lang_locale) ){

					// get index
					$key_lang =  array_search($lang_locale, array_column($LANGS, 'code'));

					// define lang locale ex: 'af_NA'
					$ARR_API['LANG_LOCALE'] = $LANGS[$key_lang]['code'];

					// get CURRENCY ISO code ex.: 'NAD', 'EUR'
					$ARR_API['CURRENCY_ISO'] = $LANGS[$key_lang]['currency_iso'];
			}
			else {

					// error
          $tab = array( 'error' => "The local language chosen was not found" );
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
          $tab = array( 'error' => "Please enter a time zone from the list",
					'el' => 'TIMEZONE' );
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
          $tab = array( 'error' => "This time zone is not supported",
					'el' => 'TIMEZONE' );
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
          $tab = array( 'error' => "Your domain name is wrong",
					'el' => 'HOST' );
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

					// error
          $tab = array( 'error' =>
													"Please enter a title for your website ( max. 300 characters )",
													'el' => 'WEBSITE_TITLE' );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include WEBSITE_TITLE
			$ARR_API['WEBSITE_TITLE'] = $WEBSITE_TITLE;


			// META_DESCR
			$META_DESCR = (string) trim(htmlspecialchars($_POST['META_DESCR']));
			// bad META_DESCR
			if( empty($META_DESCR)
			|| iconv_strlen($META_DESCR) > 300 ){

					// error
          $tab = array( 'error' =>
						"Please enter a description for your website (max. 300 characters)",
						'el' => 'META_DESCR' );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// include META_DESCR
			$ARR_API['META_DESCR'] = $META_DESCR;



			// PUBLIC_NOTIFICATION_MAIL
			$PUBLIC_NOTIFICATION_MAIL =
			(string) trim(htmlspecialchars($_POST['PUBLIC_NOTIFICATION_MAIL']));

			// bad mail
			if( empty($PUBLIC_NOTIFICATION_MAIL) ){

					// error
          $tab = array( 'error' =>
						"Please enter a public email to exchange with your visitors",
						'el' => 'PUBLIC_NOTIFICATION_MAIL' );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}

			// too long mail or bad format
			if( iconv_strlen($PUBLIC_NOTIFICATION_MAIL) > 300
			|| filter_var($PUBLIC_NOTIFICATION_MAIL, FILTER_VALIDATE_EMAIL ) == false ){

					$tab = array('error' =>
					"Your public email is not correct or longer than 300 characters",
					'el' => 'PUBLIC_NOTIFICATION_MAIL' );
					echo json_encode($tab);
					exit;
			}
			// include PUBLIC_NOTIFICATION_MAIL
			$ARR_API['PUBLIC_NOTIFICATION_MAIL'] = $PUBLIC_NOTIFICATION_MAIL;



			// LANG_FRONT
			// require API/tr.php
			require_once ROOT.'/API/tr.php';
			// return :
			// $TR_LANGS['api_lang_FRONT'];
			// $TR_LANGS['api_lang_BACK'];
			$TR_LANGS = tr::get_translations($req="");

			$LANG_FRONT = (string) trim(htmlspecialchars($_POST['LANG_FRONT']));

			// test - permitted [3chars].txt
			if( iconv_strlen($LANG_FRONT) > 3 ){

					// error
          $tab = array( 'error' => "Application language code is wrong",
												'el' => 'LANG_FRONT' );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// translation not exist on server
			if( !in_array( $LANG_FRONT,
							array_column($TR_LANGS['api_lang_FRONT'], 'code') )  ){

					// error
          $tab = array( 'error' =>
						"This translation is not present in the application, download it from the site www.placido-shop.com or create your own",
						'el' => 'LANG_FRONT' );
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
          $tab = array( 'error' => "Application language code is wrong",
												'el' => 'LANG_BACK' );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// translation not exist on server
			if( !in_array( $LANG_BACK,
							array_column($TR_LANGS['api_lang_BACK'], 'code') )  ){

					// error
          $tab = array( 'error' =>
						"This translation is not present in the application, download it from the site www.placido-shop.com or create your own",
						'el' => 'LANG_BACK' );
					echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}
			// include LANG_BACK
			$ARR_API['LANG_BACK'] = $LANG_BACK;


			// ADMIN_FOLDER
			$ADMIN_FOLDER =
			(string) trim(htmlspecialchars($_POST['ADMIN_FOLDER']));

			// name admin folder too long: max = 300
			if( iconv_strlen($ADMIN_FOLDER) > 300 ){

					// error
					$tab = array( 'error' =>
						"The name of your application management folder is too long (max. 300 characters)",
						'el' => 'ADMIN_FOLDER' );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// scan directory and find admin folder for verify
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
					$tab = array( 'error' =>
						"Error, no management folder for your application was found",
						'el' => 'ADMIN_FOLDER' );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// do we rename admin folder ?
			// if != -> rename
			if( $find_folder_admin != $ADMIN_FOLDER ){

					// RENAME DIRECTORY ADMIN FOLDER
					if( !rename( ROOT.'/'.$find_folder_admin , ROOT.'/'.$ADMIN_FOLDER ) ){

							// error
							$tab = array( 'error' => "Error, unable to rename folder admin",
							'el' => 'ADMIN_FOLDER' );
							echo json_encode($tab, JSON_FORCE_OBJECT);
							exit;
					}
			}

			// include ADMIN_FOLDER
			$ARR_API['ADMIN_FOLDER'] = $ADMIN_FOLDER;


			// ADD LOGO shop
			$ARR_API['LOGO'] = $logo_shop;


			// SORT ALPHABETICAL
			ksort($ARR_API, SORT_STRING);

			// set api settings
			$SETTINGS = install::set_settings_api( $ARR_API );

			// success - return admin folder for redirect in success case
			return $ADMIN_FOLDER;

	}
	/**
	 * install::record_api_settings( $logo_shop );
	 */



	/**
	 * install::delete_install_folder();
	 *
	 * DELETE INSTALLATION FOLDER
	 * the folder INSTALL must be empty for delete it
	 *
	 */
	public static function delete_install_folder(){

			// list files
			$FILES = array_diff(scandir( ROOT.'/INSTALL'), array('.','..'));

			// loop and erase files
			foreach( $FILES as $file ){

					unlink( ROOT.'/INSTALL/'.$file );
			}

			// delete folder INSTALL
			rmdir(ROOT.'/INSTALL');
	}
	/**
	 * install::delete_install_folder();
	 */



	/**
	 * install::install_app();
	 *
	 * @return {type}  description
	 */
	public static function install_app(){


			// record settings in API/config.php file
			// return array if all ok
			// $INFO_DB['data_base_host']
			// $INFO_DB['data_base_name']
			// $INFO_DB['data_base_user']
			// $INFO_DB['data_base_passw']
			$INFO_DB = install::record_config_api();
			// var_dump($INFO_DB);
			// exit;


			$db_host = $INFO_DB['data_base_host'];
			$db_name = $INFO_DB['data_base_name'];
			$db_user = $INFO_DB['data_base_user'];
			$db_passw = $INFO_DB['data_base_passw'];

			// insert tables model "placido-tables.sql" in database
			install::insert_tables( $db_host,
															$db_name,
															$db_user,
															$db_passw );


			// record admin - return true if ok
			// record login email + hashed password + name of admin
			install::record_admin(  $db_host,
															$db_name,
															$db_user,
															$db_passw );

			// record shop - return file name of logo shop
			// record adresses + infos shop + infos bills
			$logo_shop = install::record_shop(  $db_host,
																					$db_name,
																					$db_user,
																					$db_passw );

			// record api.json
			// return $ADMIN_FOLDER
			$ADMIN_FOLDER = install::record_api_settings( $logo_shop );

			// delete folder /INSTALL
			install::delete_install_folder();

			// success + write a greeting message
			$tab = array(
				'success' => "Placido-Shop has been successfully installed
					<br>
					You will be redirected to the site administration",
				'admin_folder' => $ADMIN_FOLDER );

			// echo response before delete foler install
			echo json_encode($tab);
			exit;

  }
	/**
	 * install::install_app();
	 */



}
// END class install::




?>
