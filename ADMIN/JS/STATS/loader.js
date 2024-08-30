/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2022-2024
 * Organisation: SNS - Web et informatique
 * Website / contact : https://sns.pm
 *
 * script name: loader.js
 *
 * Load all script of Placido-Stats API
 * - Launch api when all scripts are charged
 *
 *  const Arr_stats_scripts[] // array of urls scripts
 *	var Proms_array = []; // Pomises array
 *  load_stats_scripts();
 *
 */


	/**
	 * load_stats_scripts();
	 *
	 * Load all scripts synchronously for stats API
	 * @return {api}  Placido-Stats API
	 */
	function load_stats_scripts() {


		if( typeof Chart != 'undefined' ){

				// already charged
				get_default();

				// stop here
				return;
		}

		// scripts to load
		const Arr_stats_scripts = [
			'JS/apps/Chart.min.js',
			'JS/STATS/stats_colors.js',
			'JS/STATS/Chart_config.js',
			'JS/STATS/stats_date.js',
			'JS/STATS/stats.js',
			'JS/STATS/stats_token.js',
			'JS/STATS/stats_products.js',
		];

		// prepare a Promises array
		var Proms_array = [];

		// Pass ajax Async -> false
		$.ajaxSetup({
		  async: false
		});

		Arr_stats_scripts.forEach((url, i) => {

				var Prom = new Promise(function(resolve, reject){

						$.getScript(url, function(data, textStatus, jqxhr){

								// console.log( jqxhr );
								if( jqxhr.readyState == 4 && jqxhr.statusText == "success" ){

										resolve(i);
								}

						});
						// end getScript()

				});
				// end new Promise

				// push new Promise in Promises array
				Proms_array.push(Prom);

		});

		// excecute all promises and fire when all finished
		// note : allSettled( [] ) return results[{ status: 'fulfilled', value: a_value }, {...}]
		Promise.allSettled(Proms_array)
		.then( (Results) => {

				Results.forEach((one_result) => {

						// test if each are fulfilled
						if( one_result.status == 'fulfilled' ){

								// on last result -> re_init ajaxSetup, get default chart(s)
								if( one_result.value == Arr_stats_scripts.length-1 ){

										// re-init ajaxSetup
										$.ajaxSetup({
											async: true
										});

										// get default Chart(s)
										get_default();

								}
						}
						else{

								// render error msg on console
								console.log('Error on load stats scripts ...');

								// re-init ajaxSetup
								$.ajaxSetup({
									async: true
								});

								// stop here
								return;
						}

				});
				// end Results.forEach

		});
		// end Promise.allSettled

	}
	/**
	 *  end load_stats_scripts();
	 */



	// launch function
	load_stats_scripts();
