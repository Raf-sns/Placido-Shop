<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	index.php
 *
 * ENTRY POINT FOR BACKEND APPLICATION
 *
 */


  // ERROR REPORTING :
  // E_ALL -> Report all PHP errors - 0 -> Turn off error reporting
  error_reporting(0);

	// include MUSTACHE
	require dirname(__DIR__).'/Mustache/Autoloader.php';
	Mustache_Autoloader::register();


	// CALL CLASSES by spl_autoload_register()
	spl_autoload_register(function ($class){

			// load PHP BACKEND application classes before
			$path_PHP_back = 'PHP/' . $class . '.php';

			// call path to API folder after
			$path_API = dirname(__DIR__).'/API/' . $class . '.php';

			// register all classes
	    if( file_exists($path_API) ) require $path_API;
			elseif( file_exists($path_PHP_back) ) require $path_PHP_back;

	});
	// end SPL auto registrer


	// init. constants ROOT, ARCH, API_SETTINGS, require API/constants.php
	api::init_settings();

	// SET GLOBAL ARRAY translation -> tr::$TR[]
	tr::init_tr( 'back' );

	// init modules after translation -> this override tr::$TR[...]
	api::init_modules( $context='back' );


	// START CONTROLLER
	control::start();

?>
