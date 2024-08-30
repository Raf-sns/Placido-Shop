/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: trumbowyg_loader.js
 *
 * const Trumbowyg_route : string
 * const Trumbowyg_Arr_css : array
 * const Trumbowyg_Arr_scripts : array
 * Append to the DOM trumbowyg lib. JS/CSS + trumbowyg plugins
 * 
 */

	if( typeof Trumbowyg === 'undefined' ){


			const Trumbowyg_route = 'JS/apps/';

			const Trumbowyg_Arr_css = [
				'trumbowyg/dist/ui/trumbowyg.min.css',
				'trumbowyg/dist/plugins/colors/ui/trumbowyg.colors.min.css',
				'trumbowyg/dist/plugins/table/ui/trumbowyg.table.min.css',
				'trumbowyg/placido-style.css'
			];

			const Trumbowyg_Arr_scripts = [
				'jquery-resizable.min.js',
		    // trumbowyg NOT THE .min !!
		    'trumbowyg/dist/trumbowyg.js',
		    // plugin image base 64
		    'trumbowyg/dist/plugins/base64/trumbowyg.base64.min.js',
		    // plugin color NOT THE .min !!
		    'trumbowyg/dist/plugins/colors/trumbowyg.colors.js',
		    // plugin font size
		    'trumbowyg/dist/plugins/fontsize/trumbowyg.fontsize.min.js',
		    // plugin noembed
		    'trumbowyg/dist/plugins/noembed/trumbowyg.noembed.js',
		    // plugin resize image
		    'trumbowyg/dist/plugins/resizimg/trumbowyg.resizimg.min.js',
		    // plugin table NOT THE .min !!
		    'trumbowyg/dist/plugins/table/trumbowyg.table.js'
			];



			// append css
			Trumbowyg_Arr_css.forEach((item, i) => {

					$('head').append(`<link rel="stylesheet" href="`+Trumbowyg_route+item+`">`);
			});


			// append scripts
			Trumbowyg_Arr_scripts.forEach((item, i) => {

					$('body').append(`
						<script type="text/javascript" src="`+Trumbowyg_route+item+`"></script>
					`);
			});

	}
	// end if( !Trumbowyg )
