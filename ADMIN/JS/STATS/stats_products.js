/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: stats_products.js
 *
 * get_stats_products();
 *
 */



	/**
	 * get_stats_products( context );
	 *
	 * @return {json}  stats for poducts
	 */
	function get_stats_products( context ){

		// Period.value -> RETURN the period key 'y','m','w','d'

		// year, month , ..
		let Y,M,W,D;

		switch ( Period.value ) {

			case 'y':
				Y = Sort_date.get_year();
				M = '';
				W = '';
				D = '';
			break;

			case 'm':
				Y = Sort_date.get_year();
				M = Sort_date.get_month();
				W = '';
				D = '';
			break;

			case 'w':
				Y = Sort_date.get_year();
				M = '';
				W = Sort_date.get_week(); // int.
				D = '';
			break;

			case 'd':
				Y = Sort_date.get_year();
				M = Sort_date.get_month();
				W = '';
				D = Sort_date.get_day();
			break;

			default:
				// day by default
				Y = Sort_date.get_year();
				M = Sort_date.get_month();
				W = '';
				D = Sort_date.get_day();

		}
		// END SWTICH


		// disable buttons
		enable_buttons('disabled');

		// start loader
		loader('start');

		// create a FormData to send
		let Datas = new FormData();

		// add datas to FormData
		Datas.append('set', 'get_stats_by_interval' );
		Datas.append('token' , $.o.user.token );
		Datas.append('year', Y );
		Datas.append('month', M );
		Datas.append('week', W );
		Datas.append('day', D );
		Datas.append('period', Period.value );
		// append a context
		Datas.append('context', context );

		var MyHeaders = new Headers();
		var MyInit = {
			method: 'POST',
			mode: 'cors',
			headers: MyHeaders,
			cache: 'default',
			credentials: 'include',
			body: Datas
		};
		// send request to server - Test USE FETCH API
		fetch('index.php', MyInit).then(function(response){

					// first we wait for a response from the server ...
					if(response.ok) {

						// then treat the response as json
						return response.json();
					}
					else{
						// error
						alert( $.o.tr.error_server );
					}

			}).then(function(datas){

					// console.log( datas );

					// enable buttons
					enable_buttons('enabled');

					// end loader
					loader('end');

					// RENEW obj stats
					$.o.stats = {};
					$.o.stats = datas;

					// RENDER A TITLE - BOX - HTML -> this displays the period in local language
					document.getElementById('title_stats').innerHTML = $.o.stats.period.title;


					// no products / no cart for period
					if( $.o.stats.total_nb_products == 0
							|| $.o.stats.total_nb_in_cart == 0 ){

							// empty dates render
							document.getElementById('date_start').innerHTML = '';
							document.getElementById('date_end').innerHTML = '';

							// render NO VISITS in context
							if( typeof $.o.stats.context != 'undefined'
									&& $.o.stats.context == 'cart' ){

									// no cart translated
									document.getElementById('total_nb_visits').innerHTML =
									$.o.tr.empty_cart_visits;
							}
							else{

									// no visits translated
									document.getElementById('total_nb_visits').innerHTML =
									$.o.tr.empty_visits_products;
							}
					}
					else{

							// have products render number views products
							let total_nb_visits = document.getElementById('total_nb_visits');

							if( typeof $.o.stats.context != 'undefined'
									&& $.o.stats.context == 'cart' ){

									total_nb_visits.innerHTML =
									$.o.stats.nb_products_processed+` `+$.o.tr.articles_stats_processed;

							}
							else{
									total_nb_visits.innerHTML = $.o.stats.total_nb_products+` `+$.o.tr.stats_products;
							}

					}
					// end have products render number views

					// render date start / date end period
					if( $.o.stats.period.date_start.length != 0 ){

							// date start
							document.getElementById('date_start').innerHTML =
							$.o.stats.period.date_start;

							// date end
							if( $.o.stats.period.date_end.length != 0 ){

									// if have a end date string
									document.getElementById('date_end')
									.innerHTML = `<i class="fa-chevron-right fa-fw fas medium"></i>&nbsp;`
									+$.o.stats.period.date_end;
							}
					}
					// end render date start / date end period


					// this call clean_canvas(); and exit if no results
					chart_controller();

			}) // catch error
			.catch(function(error) {

					console.log(error.message);
			});
			// end fetch promise


	}
	/**
	 * get_stats_products();
	 */
