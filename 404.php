<?php

/**
 *  KEEP IT !
 */

	// PRODUCTION : Turn off all error reporting -> comment
  error_reporting(0);

  // include MUSTACHE
  require 'Mustache/Autoloader.php';
  Mustache_Autoloader::register();

  // CALL CLASSES by spl_autoload_register()
  spl_autoload_register(function ($class){

			// load API classes
			$path_API = 'API/' . $class . '.php';
			// load PHP application classes
	    $path_PHP_front = 'PHP/' . $class . '.php';

		  if( file_exists($path_API) ) require $path_API;
			elseif( file_exists($path_PHP_front) ) require $path_PHP_front;

	});

	// INIT OBJ. WITH api.json
  api::init_settings();

  // GLOBAL ARRAY translation
  tr::init_tr( 'front' );

	// DEFINE TIMEZONE - returned by api::
  date_default_timezone_set( TIMEZONE );

	// page_api = 404
	api::$REQ = array( 'page_api' => '404'  );

	program::get_home_page();

?>
