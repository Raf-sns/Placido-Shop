<?php
/**
 * PLACIDO-SHOP FRAMEWORK - API
 * Copyright © Raphaël Castello, 2022-2023
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	files_to_compress.php
 *
 * -> contain 2 constants as ARRAYS :
 *
 * 1 - CSS_RESSOURCES_TO_COMPRESS[]
 *
 * 2 - JS_RESSOURCES_TO_COMPRESS[]
 * -> values of ARRAYS are paths to ressources to compress
 *
 */



	// css files paths to compress
	// note : ROOT.'/CSS/all.min.css',
	// removed, this may be conflictual if use fontawesome JS
	const CSS_RESSOURCES_TO_COMPRESS = [

			// APPS CSS TO COMPRESS
			ROOT.'/CSS/apps/keen-slider.min.css',
			ROOT.'/CSS/apps/toastr.min.css',
			// API CSS TO COMPRESS
			ROOT.'/CSS/custom.css',
			ROOT.'/CSS/api.css',
			ROOT.'/CSS/single_product.css',
			ROOT.'/CSS/payment.css',
			ROOT.'/CSS/shop.css',
	];

	// js files paths to compress
	const JS_RESSOURCES_TO_COMPRESS = [

			// APPS TO COMPRESS
			// - first put libraries in top of the list
			ROOT.'/JS/apps/jquery.min.js',
			ROOT.'/JS/apps/mustache.min.js',
			ROOT.'/JS/apps/jquery.mustache.js',
			ROOT.'/JS/apps/touchSwipe.min.js',
			ROOT.'/JS/apps/keen-slider.min.js',
			ROOT.'/JS/apps/toastr.min.js',

			// JS CURRENT TO COMPRESS
			// ! order of cart.js -> main.js -> api_loader.js
			// ! is important !
			ROOT.'/JS/cart.js',
			ROOT.'/JS/main.js',
			ROOT.'/JS/stats.js',
			ROOT.'/JS/api_loader.js',
			// next order is not important
			ROOT.'/JS/tools.js',
			ROOT.'/JS/pagination.js',
			ROOT.'/JS/pwa.js',
			ROOT.'/JS/slideshow.js',
			ROOT.'/JS/history.js',
	];



?>
