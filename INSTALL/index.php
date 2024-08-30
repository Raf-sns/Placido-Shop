<?php
/**
 * PLACIDO-SHOP FRAMEWORK - INSTALL
 * Copyright © Raphaël Castello, 2020-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	 index.php
 *
 * ENTRY POINT FOR AUTO INSTALLER
 *
 */

  // Report all PHP errors
  error_reporting(E_ALL);

  // include MUSTACHE
  require dirname(__DIR__).'/Mustache/Autoloader.php';
  Mustache_Autoloader::register();

  // require config class ->
  // Even if the data is empty, it is necessary because it extends the "api" class
	require dirname(__DIR__).'/API/config.php';

  // require api class
	require dirname(__DIR__).'/API/api.php';

  // init all constants
	api::init_settings();

  // installation script
	require 'install.php';

	// NO REQUESTS - get the installation page
	if( empty($_GET) && empty($_POST) ){

			install::page_install();
	}

	// MANAGE POST REQUESTS
	if( isset($_POST) && !empty($_POST['set']) ){


			$set = (string) trim(htmlspecialchars($_POST['set']));

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
	// END MANAGE POST REQUESTS


?>
