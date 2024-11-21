<?php
/**
 * PLACIDO-SHOP FRAMEWORK - API
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	 constants.php
 *
 * global constants used by the application
 *
 */

	// name of administration folder
	const ADMIN_FOLDER = "ADMIN";
	// allow search engines to index the store -> 1 : allow || 0 : disallow
	const ALLOW_SEARCH_ENGINES = 1;
	// state of compression -> 1 : use compression || 0 : no use compression
	const COMPRESSED = 0;
	// date of last compression
	const COMPRESSED_DATE = "";
	// compression timestamp - USE INTEGERS
	const COMPRESSED_STAMP = 1720883291;
	// currency ISO code
	const CURRENCY_ISO = "USD";
	// sizes min / max for images - USE INTEGERS - KEEP THIS ARRAY ON ONE LINE !
	const DEF_ARR_SIZES = array("min" => 400, "max" => 800);
	// how to display products : "mozaic" / "inline"
	const DISPLAY_PRODUCTS = "mozaic";
	// website domain name - without www. or https://
	const HOST = "my-placido-shop.com";
	// translation lang for backend (administration interface)
	const LANG_BACK = "en";
	// translation lang for front (public interface)
	const LANG_FRONT = "en";
	// lang locale - for display prices / dates / number format
	const LANG_LOCALE = "en_US";
	// last update date
	const LAST_UPDATE = "";
	// website / shop logo
	const LOGO = "";
	// social networks image
	const LOGO_SN = "";
	// social networks image size - USE INTEGERS
	const LOGO_SN_SIZE = 1000;
	// meta description for the website by default
	const META_DESCR = "Placido-Shop Online Sale Software";
	// number of products displayed per page for the frontend - USE INTEGERS
	const NB_FOR_PAGINA = 4;
	// number of products displayed per page for the backend - USE INTEGERS
	const NB_FOR_PAGINA_BACKEND = 4;
	// public email attached to the messages
	const PUBLIC_NOTIFICATION_MAIL = "user@placido-shop.com";
	// number of characters for short description - USE INTEGERS
	const SHORT_TEXT = 300;
	// slider settings on home page "delay" and "speed" in milliseconds
	// - USE INTEGERS - KEEP THIS ARRAY ON ONE LINE !
	const SLIDER = array("display" => 1, "play" => 1, "delay" => 4000, "speed" => 2000);
	// timezone
	const TIMEZONE = "America/Los_Angeles";
	// duration of security token validity in seconds - USE INTEGERS
	const TOKEN_TIME = 3600;
	// current software version
	const VERSION = "3.1.0";
	// website title
	const WEBSITE_TITLE = "Placido-Shop";

?>
