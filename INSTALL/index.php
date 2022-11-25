<?php
/**
 * PlACIDO-SHOP FRAMEWORK - INSTALL
 * Copyright © Raphaël Castello, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 index.php
 *
 * ENTRY POINT FOR AUTO INSTALLER
 *
 */


  // Report all PHP errors
  error_reporting(E_ALL);

	// Turn off all error reporting -> uncomment
  // error_reporting(0);

  // include MUSTACHE
  require dirname(__DIR__).'/Mustache/Autoloader.php';
  Mustache_Autoloader::register();

	require dirname(__DIR__).'/API/config.php';
	require dirname(__DIR__).'/API/api.php';
	api::init_settings();

	require 'install.php';

	// REQUESTS
	if( empty($_GET) && empty($_POST) ){

			install::page_install();
	}

	// IF $_POST
	if( isset($_POST) && !empty($_POST['set']) ){


			$set = trim(htmlspecialchars($_POST['set']));

			if( iconv_strlen($set) > 100 ){

					exit("Don't hack me !");
			}

			// SET
			switch ($set) {

					// test database
					case 'test_database':
						install::test_database($context='');
					break;

					// install_app
					case 'install_app':
						install::install_app();
					break;

					default:
						exit('Bad request ...');
					break;

			}
			// end switch

	}
	// END IF $_POST


?>
