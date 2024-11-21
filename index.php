<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	 index.php
 *
 * ENTRY POINT FOR APPLICATION
 *
 */

  // ERROR REPORTING :
  // E_ALL -> Report all PHP errors | 0 -> Turn off error reporting
  error_reporting(0);


  // include MUSTACHE
  require 'Mustache/Autoloader.php';
  Mustache_Autoloader::register();

  // CALL CLASSES by spl_autoload_register()
  spl_autoload_register(function($class){

			// load API classes
			$path_API = 'API/' . $class . '.php';
			// load PHP application classes
	    $path_PHP_front = 'PHP/' . $class . '.php';

	    if( file_exists($path_API) ) require $path_API;
			if( file_exists($path_PHP_front) ) require $path_PHP_front;

	});

	// load constants for API from API/constants.php for all scripts include back-end
  api::init_settings();

  // public static ARRAY translation
	// access translate like this : tr::$TR['my_translation_key']
  tr::init_tr( 'front' );

  // DEFINE TIMEZONE - returned by api::
  date_default_timezone_set( TIMEZONE );

	// init modules after translation -> this override tr::$TR[...]
	api::init_modules( $context='front' );

  // START CONTROLLER
  control::start();

?>
