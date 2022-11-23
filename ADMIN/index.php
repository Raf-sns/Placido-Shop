<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 index.php
 *
 */


  // Report all PHP errors
  // error_reporting(E_ALL);

	// Turn off all error reporting -> uncomment
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


	// INIT OBJ. WITH WEBSITE_SETTINGS - set TIMEZONE
	api::init_settings();

	// SET GLOBAL ARRAY translation -> tr::$TR[]
	tr::init_tr( 'back' );

	// init modules after translation -> this override tr::$TR[...]
	api::init_modules( $context='back' );


	// START CONTROLLER
	control::start();

?>
