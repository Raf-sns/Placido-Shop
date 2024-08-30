/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2022
 * Organisation: SNS - Web et informatique
 * Website / contact : https://sns.pm
 *
 * script name: stats.js
 *
 * Summary :
 *
 * Load scripts of API : 	loader.js
 * Chart.js config : 			Chart_config.js
 * Colors : 							stats_color.js
 * Date management : 			stats_date.js
 * Products : 						stats_products.js
 * Token API : 						stats_token.js
 *
 * Buttons return their element :
 *
 * Btn_localities.ID
 * Btn_days.ID
 * Btn_products.ID
 * Btn_doughnut.ID
 * Btn_line.ID
 * Btn_bar.ID
 *
 * stats_buttons_by_default();
 * reset_buttons();
 *
 * get_default();
 *
 * var Chart_context = 'localities'; // default
 *
 * select_context( event, context );
 *
 * var Chart_type = 'doughnut'; // default
 *
 * select_chart_type( event, chart_type );
 *
 * show_period_selector( event );
 * bind_select_period( period, event );
 * select_period();
 * navigate_BY_period( direction );
 *
 * enable_buttons( command );
 *
 * loader( command );
 *
 * get_stats_from_server( Y, M, W, D, period );
 *
 * clean_canvas();
 *
 * Period.value
 *
 * var Canvas_chart = {};
 *
 * chart_controller();
 *
 * default_legend_click();
 *
 * var Chart_options = {};
 *
 * set_chart_OPTIONS();
 *
 * set_max_scale_line_cart();
 * set_max_scale_bar_cart();
 *
 * set_height_canvas();
 *
 * var Placido_Charts = {};
 *
 * render_graphs();
 *
 */


	/**
	 * get_default();
	 *
	 * @return {object}  Sort_date.obj  - just re-init Sort_date.obj at today
	 */
	function get_default(){

			// by default
			Chart_context = 'localities';
			Chart_type = 'doughnut';

			// colors button by default
			stats_buttons_by_default();

			// re-init Sort_date.obj at today
			re_init_date();

			// render graph fetch DATAS  from server
			// ()=> Call -> chart_controller();
			get_stats_from_server();

	}
	/**
	 * get_default();
	 */



	/**
	 * get_today();
	 *
	 * @return {json}  get datas for today whatever Chart_type & Chart_context
	 */
	function get_today(){

			// re-init Sort_date.obj at today
			re_init_date();

			// simulate a change on select #sort_stats
			select_period();
	}
	/**
	 * get_today();
	 */



	// use Btn_localities.ID.classList.add('blue');
	// use [Btn_localities.ID]
	// like [Btn_localities] = document.getElementById('btn-localities');
	// WHY ? -> the script (stats.js) is charged before ids exists,
	// this provide a reference for the futures access to buttons

	// CHART CONTEXT

	// Btn_localities.ID
	var Btn_localities = {
		get ID() {
			return document.getElementById('btn-localities');
		}
	};

	// Btn_days.ID
	var Btn_days = {
		get ID() {
			return document.getElementById('btn-days');
		}
	};

	//  Btn_products.ID
	var Btn_products = {
		get ID() {
			return document.getElementById('btn-products');
		}
	};

	//  Btn_cart.ID
	var Btn_cart = {
		get ID() {
			return document.getElementById('btn-cart');
		}
	};
	// CHARTS TYPES

	// Btn_doughnut.ID
	var Btn_doughnut = {
		get ID() {
			return document.getElementById('btn-doughnut');
		}
	};

	// Btn_line.ID
	var Btn_line = {
		get ID() {
			return document.getElementById('btn-line');
		}
	};

	// Btn_bar.ID
	var Btn_bar = {
		get ID() {
			return document.getElementById('btn-bar');
		}
	};



	/**
	 * stats_buttons_by_default();
	 *
	 * @return {void} reset button colors
	 * to match the default settings
	 */
	function stats_buttons_by_default(){

		reset_buttons();

		// by default button id="btn-localities" is selected
		Btn_localities.ID.classList.add('gree');
		Btn_localities.ID.classList.remove('blue');

		// by default button id="btn-doughnut" is selected
		Btn_doughnut.ID.classList.add('gree')
		Btn_doughnut.ID.classList.remove('blue');

	}
	/**
	 * stats_buttons_by_default();
	 */



	/**
	 * reset_buttons();
	 *
	 * @return {void}  reset all button to un-selected color
	 */
	function reset_buttons(){

			// reset all chart Chart_context buttons
			let btns_ctxt = document.getElementsByClassName('chart_context');

			// loop over buttons same class
			Array.from( btns_ctxt ).forEach((item, i) => {
					// remove class selected
					item.classList.remove('gree');
					item.classList.add('blue');
			});

			// reset all chart Chart_type buttons
			let btns_types = document.getElementsByClassName('chart_type');

			// loop over buttons same class
			Array.from( btns_types ).forEach((item, i) => {
					// remove class selected
					item.classList.remove('gree');
					item.classList.add('blue');
			});
	}
	/**
	 * reset_buttons();
	 */



	// global Chart_context for DATAS : 'localities' / 'days'
	// by default -> nb_visits day is already rendered, no need 'days' by default
	var Chart_context = 'localities';

	/**
	 * select_context( event, context );
	 *
	 * @param  {event} event
	 * @param  {type} context  'localities' / 'days' / 'products' / 'cart'
	 * @return {type}           description
	 */
	function select_context( event, context ){


			// Manage view color button enabled
			let btns = document.getElementsByClassName('chart_context');

			// loop over buttons same class
			Array.from(btns).forEach((item, i) => {
					// remove class selected
					item.classList.remove('gree');
					item.classList.add('blue');
			});

			// attr class selected for item clicked
			event.currentTarget.classList.remove('blue');
			event.currentTarget.classList.add('gree');


			// watch previous context
			let previous_context = Chart_context;


			// pass global context for graph
			Chart_context = context;

			if( Chart_context == 'days' ){

					// force line ' Yes - Specify "DOUNGHNUT" '
					// -> this set type to 'line in controler
					// by default on click in context == days
					Chart_type = 'doughnut';
			}

			if( Chart_context == 'products' ){

					// no set Chart_type -> it's free here
					// fetch datas ()=> Call -> chart_controller();
					get_stats_products('products');

			}
			else if( Chart_context == 'cart' ){

					// fetch datas ()=> Call -> chart_controller();
					get_stats_products('cart');

			}
			// reload datas if we come from products
			else if( previous_context == 'products' || previous_context == 'cart' ){

					// render graph fetch DATAS  from server
					// ()=> Call -> chart_controller();
					get_stats_from_server();

			}
			// datas alredy cached, just modify graph
			else{

					// render graph NO fetch DATAS from server
					chart_controller();

			}

			// console.log( Chart_context );

			// manage icon title infos
			var icon = document.getElementById('icon_render_nb_visits');
			icon.classList.remove('fa-globe','fa-users','fa-th','fa-dolly');

			if( Chart_context == 'localities' ){
					icon.classList.add('fa-globe');
			}
			if( Chart_context == 'days' ){
					icon.classList.add('fa-users');
			}
			if( Chart_context == 'products' ){
					icon.classList.add('fa-th');
			}
			if( Chart_context == 'cart' ){
					icon.classList.add('fa-dolly');
			}

	}
	/**
	 * select_context( event, context );
	 */



	// global context for Chart_type :  'doughnut' / 'line' / 'bar'
	var Chart_type = 'doughnut'; // by default

	/**
	 * select_chart_type( event, chart_type );
	 *
	 * @param  {event} event
	 * @param  {type} chart_type 	'doughnut' / 'line' / 'bar'
	 * @return {type}            		description
	 */
	function select_chart_type( event, chart_type ){

			// Manage view color button enabled
			let btns = document.getElementsByClassName('chart_type');

			// loop over buttons same class
			Array.from(btns).forEach((item, i) => {
					// remove class selected
					item.classList.remove('gree');
					item.classList.add('blue');
			});

			// attr class selected for item clicked
			event.currentTarget.classList.remove('blue');
			event.currentTarget.classList.add('gree');

			// pass global context for Chart_type
			Chart_type = chart_type;

			// console.log( chart_type );

			// render graph NO fetch DATAS from server
			chart_controller();

	}
	/**
	 * select_chart_type( event, chart_type );
	 */



	/**
	 * show_period_selector( event );
	 *
	 * @return {void}  toggle period selector
	 */
	function show_period_selector(e){

			// important for not fire click on document on first click
			e.stopImmediatePropagation();

			// add listener on click body
			let period_selector = document.getElementById('period_selector');

			// show block period selector
			if( period_selector.className.indexOf('show') == -1 ){

					period_selector.className += ' show';

					// add a listener for click on document
					document.body.addEventListener('click',
					    show_period_selector,
					    false
					);

		  }
			else{

					// hide period selector
					period_selector.className = period_selector.className.replace(' show', '');

					// remove a listener for click on document
					document.body.removeEventListener('click',
					    show_period_selector,
					    false
					);
			}
	}
	/**
	 * show_period_selector( event );
	 */



	/**
	 * bind_select_period( period, event );
	 *
	 * @param  {string} period  'd','w','m','y'
	 * @return {json}   get datas when a period is asked
	 */
	function bind_select_period( period, e ){


				let sort_stats = document.getElementById('sort_stats');

				sort_stats.value = period;

				let text_value = e.currentTarget.innerText;

				// attr good text value in button period selector
				document.getElementById('label_period').innerText = text_value;

				// simulate onchange
				select_period();

	}
	/**
	 * bind_select_period( period, event );
	 */



	/**
	 * select_period();
	 *
	 * @return {canvas}  return Html canvas after selected a  period 'd','w','m','y'
	 */
	function select_period(){


			if( Chart_context == 'products' ){

					get_stats_products('products');

			}
			else if( Chart_context == 'cart' ){

					get_stats_products('cart');

			}
			else{

					// render graph fetch DATAS  from server
					// ()=> Call -> chart_controller();
					get_stats_from_server();
			}
	}
	/**
	 * select_period();
	 */



	/**
	 * navigate_BY_period( direction );
	 *
	 * @param  {type} direction 'before' / 'after'
	 * @return {canvas}          return Html canvas after clicked on 'before' / 'after'
	 */
	function navigate_BY_period( direction ){


			// on select period year, day, ...
			let input = document.getElementById('sort_stats');

			// get the period key 'y','m','w','d'
			let period = input.value;

			// set operation to do
			let Oper = ( direction == 'before' ) ? -1 : 1;

			let test_date = false;
			let week_nb;
			let year;

			// SWITCH period
			switch ( period ){

				// day
				case 'd':
					// remove or add one day
					Sort_date.obj.setDate( Sort_date.obj.getDate() + Oper );
					// !important set to 00:00:00
					Sort_date.obj.setHours(0, 0, 0);
					// test new Sort_date.obj - reset today if date overflow
					// return true / false
					test_date = date_overflow_test();
				break;
				default:

				// week
				case 'w':
					// get number of current week - ADD OR REMOVE OPERATOR
					week_nb = parseInt( Sort_date.get_week(), 10 ) + Oper;
					year = parseInt( Sort_date.get_year(), 10 ); // current year in int yyyy

					// Make an object date by week number && year - ATTR IT TO NEW obj.
					Sort_date.obj = getDateOfISOWeek( week_nb, year ); // set at 00:00:00
					// console.log( Sort_date.get_full_string() );
					// return true / false
					test_date = date_overflow_test();
				break;

				// month
				case 'm':
					// full string date obj
					let date_string = Sort_date.get_full_string();
					// SET MONTH
					Sort_date.obj = set_month( date_string, Oper );
					// !important set to 00:00:00
					Sort_date.obj.setHours(0, 0, 0);
					// console.log( Sort_date.get_full_string() );
					test_date = date_overflow_test();
				break;

				// year
				case 'y':
					// SET YEAR
					let year_string = Sort_date.get_year();
					Sort_date.obj = set_year( year_string, Oper ); // set_year() return object
					// !important set to 00:00:00
					Sort_date.obj.setHours(0, 0, 0);
					// console.log( Sort_date.get_full_string() );
					test_date = date_overflow_test();
				break;

			}
			// END SWITCH period

			// DATE OVERFLOW
			if( test_date == false ){

					// STOP HERE
					return;
			}

			// console.log( Sort_date.get_full_string() );

			// simulate a selection of period -> this fetch good datas
			select_period();

	}
	/**
	 * navigate_BY_period( direction );
	 */



	/**
	 * enable_buttons( command );
	 * @Param command 	'enabled' / 'disabled'
	 * @return {void}
	 */
	function enable_buttons( command ){


			let btns = document.getElementById('stats_nav')
			.getElementsByClassName('stats_navigators');

			if( command == 'disabled' ){

					Array.from(btns).forEach(function(item){
						item.setAttribute('disabled', 'disabled');
					});
			}
			else{

					// re-attr buttons
					Array.from(btns).forEach(function(item){
							item.removeAttribute('disabled');
					});
			}

	}
	/**
	 * end enable_buttons( command );
	 */



	/**
	 * loader( command );
	 * @Param command 	'start' / 'end'
	 * @return {void}
	 */
	function loader( command ){

			if( command == 'start' ){

					// insert loader html
					document.getElementById('date_start').innerHTML =
						`<span class="large stats_loader">
							<i class="fa-circle-notch fa-fw fa-spin far fas xlarge"></i>
							&nbsp; `+$.o.tr.loading_datas+` ...</span>`;

					document.getElementById('date_end').innerHTML = '';
			}
			else{
					// remove loader
					document.getElementById('date_start').innerHTML = '';
			}
	}
	/**
	 * end loader( command );
	 */



	/**
	 * get_stats_from_server();
	 *
	 * Call -> chart_controller();
	 *
	 * @param  {type} Y      description
	 * @param  {type} M      description
	 * @param  {type} W      description
	 * @param  {type} D      description
	 * @param  {type} period 	the period key 'y','m','w','d'
	 * @return {json}        return json { cities{ names[], nb_visits[] },
	 * 																			countries{...same...},
	 * 																			 regions{...same...},
	 * 																			  timezones{...same...}  }
	 */
	function get_stats_from_server(){


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

						// NOW we have the response in json
						// if have stats,  return :
						// 'today_nb_visits' => string
						// 'total_nb_visits' => int // search results nunmber
						// 'period'	=> array('date_start'=> string , 'date_end'=> string )
						// 'cities' => array('name'=> strings['tokyo', 'berlin',..],
						// 										'nb_visits'=> ints.[2,33,...] )
						// 'regions'	=> array
						// 'countries'	=> array
						// 'timezones'	=> array
						//
						// if no have stats, return :
						// 'today_nb_visits' => string
						// 'total_nb_visits' => int // search results nunmber
						// 'period'	=> 'period'	=> array('date_start'=> string , 'date_end'=> string )

						// enable buttons
						enable_buttons('enabled');

						// end loader
						loader('end');

						// RENEW obj stats
						$.o.stats = {};

						// api token
						$.o.stats.token_api = datas.token_api;

						// refresh today nb visits - in string translated
						$.o.stats.today_nb_visits = datas.today_nb_visits;

						// renew total number for period in int.
						$.o.stats.total_nb_visits = datas.total_nb_visits;

						$.o.stats.localities = {
							cities: '',
							countries: '',
							regions: '',
							timezones: '',
						};

						if( datas.total_nb_visits != 0 ){

								// fill object stats
								// localities
								$.o.stats.localities.cities = datas.cities; // names[], nb_visits[]
								$.o.stats.localities.countries = datas.countries; // same
								$.o.stats.localities.regions = datas.regions;
								$.o.stats.localities.timezones = datas.timezones;
								// days
								$.o.stats.days = datas.days; // dates[], days_nb[], 'title_grah'(str)
						}

	 					// console.log( $.o.stats );

						// RENDER A TITLE - BOX - HTML -> this displays the period in local language
						document.getElementById('title_stats').innerHTML = datas.period.title;


						// manage singular / plural for render string ex.(Visits/Visit)
						if( datas.total_nb_visits == 0 ){

								// NO VISITS - translated
								document.getElementById('total_nb_visits')
								.innerHTML = $.o.tr.empty_visits;
						}
						else if ( datas.total_nb_visits == 1 ) {

								// singular str
								document.getElementById('total_nb_visits')
								.innerHTML = datas.total_nb_visits+'&nbsp;'+$.o.tr.visit;
						}
						else if ( datas.total_nb_visits > 1 ) {

								// plural str
								document.getElementById('total_nb_visits')
								.innerHTML = datas.total_nb_visits+'&nbsp;'+$.o.tr.visits;
						}


						// render date start / date end period
						if( datas.period.date_end.length != 0 ){

								document.getElementById('date_start').innerHTML =
								datas.period.date_start;

								// if have a end date string
								document.getElementById('date_end')
								.innerHTML = `<i class="fa-chevron-right fa-fw fas medium"></i>&nbsp;`
								+datas.period.date_end;
						}
						else{
								// no have date end string
								document.getElementById('date_end').innerHTML = '';
						}


						// no datas for this period - return here
						if( datas.total_nb_visits == 0 ){

								clean_canvas();

								return;
						}


						// launch controller
						chart_controller();

			})
			.catch(function(error) {
			  console.log(error.message);
			});
			// end send request

	}
	/**
	 * get_stats_from_server();
	 */



	/**
	 * clean_canvas();
	 *
	 * @return {void}  Destroy all Chart instances
	 * Display 'none' all canvas if no resutls
	 */
	function clean_canvas(){


			// destroy charts before renew
			if( Object.entries(Placido_Charts).length != 0 ){

					// destroy each Charts instance
					for( const [key, for_item] of Object.entries(Placido_Charts) ){

							Placido_Charts[''+key+''].destroy();
					}
			}


			// in ALL CASES display none all containers of canvas
			let Containers = document.querySelectorAll('.localities')

			Containers.forEach((item) => {
			  item.style.display = 'none';
			});

	}
	/**
	 * clean_canvas();
	 */



	/**
	 * 	Period.value
	 */
	var Period = {
		get value() {
			return document.getElementById('sort_stats').value;
		}
	};

	// where to iterate array of datas
	var Canvas_chart;

	/**
	 * chart_controller();
	 *
	 * @param  {type} Chart_context  'localities' /	'days' / 'products' / 'cart'
	 * @param  {type} Chart_type     'doughnut' / 'line' / 'bar'
	 * @param  {type} period         Period.value
	 * @return {type}                description
	 */
	function chart_controller(){


			// for all cases clean_canvas();
			clean_canvas();

			// DAYS cases

			// Not render canvas if : context == days ANS Period == 'd'
			// Not render canvas if : $.o.stats.total_nb_visits == 0
			// DO NOT JOINT THAT WITH nb_visits == 0
			if( Chart_context == 'days' && Period.value == 'd' ){

					// do notihng
					return;
			}

			// unaviable chart 'pie' or 'doughnut' for DAYS
			if( Chart_context == 'days'
			&& (Period.value == 'w' || Period.value == 'm' || Period.value == 'y') ){

					// if wrong Chart_type - NEVER DOUNGHNUT for days
					if( Chart_type == 'doughnut' ){

							// set to line by default
							Chart_type = 'line';

							// reset all buttons by default color
							reset_buttons();

							Btn_days.ID.classList.add('gree');
							Btn_days.ID.classList.remove('blue');

							Btn_line.ID.classList.add('gree');
							Btn_line.ID.classList.remove('blue');

					}

			}
			// end unable chart 'pie' or 'doughnut' for DAYS

			// END DAYS

			//--------------------------

			// LOCALITIES

			// never 'line' for localities
			if( Chart_context == 'localities' && Chart_type == 'line' ){

					// force 'bar'
					Chart_type = 'bar';

					// reset all buttons by default color
					reset_buttons();

					// color selected for type 'bar'
					Btn_bar.ID.classList.add('gree');
					Btn_bar.ID.classList.remove('blue');

					// color selected for context 'localities'
					Btn_localities.ID.classList.add('gree');
					Btn_localities.ID.classList.remove('blue');

			}

			// enable doughnut if results <= 15
			if( Chart_context == 'localities'
			&& (Period.value == 'd' || Period.value == 'w' || Period.value == 'm') ){


					// enable doughnut only if results <= 15
					if( Chart_type == 'doughnut'
					&& typeof $.o.stats.localities.cities.names != 'undefined'
					&& $.o.stats.localities.cities.names.length > 15 ){

							Chart_type = 'bar';

							// reset all buttons by default color
							reset_buttons();

							// color selected for type 'bar'
							Btn_bar.ID.classList.add('gree');
							Btn_bar.ID.classList.remove('blue');

							// color selected for context 'localities'
							Btn_localities.ID.classList.add('gree');
							Btn_localities.ID.classList.remove('blue');
					}

			}
			// end enable doughnut

			// for year -> force bar
			if( Chart_context == 'localities' && Period.value == 'y' ){

					Chart_type = 'bar';

					// reset all buttons by default color
					reset_buttons();

					// color selected for type 'bar'
					Btn_bar.ID.classList.add('gree');
					Btn_bar.ID.classList.remove('blue');

					// color selected for context 'localities'
					Btn_localities.ID.classList.add('gree');
					Btn_localities.ID.classList.remove('blue');
			}
			// end LOCALITIES


			// CART
			// for cart + d + line -> force doughnut - Line have no sense
			if( Chart_context == 'cart' && Period.value == 'd' && Chart_type == 'line' ){


					// force DOUNGHNUT for cart + d
					Chart_type = 'doughnut';

					// reset all buttons by default color
					reset_buttons();

					// color selected for type 'cart'
					Btn_cart.ID.classList.add('gree');
					Btn_cart.ID.classList.remove('blue');

					// color button for chart type doughnut
					Btn_doughnut.ID.classList.add('gree');
					Btn_doughnut.ID.classList.remove('blue');

			}
			// end CART


			// MANAGE CANVAS from Array[]
			if( Chart_context == 'localities' ){

					// Canvas_chart array
					Canvas_chart = Locations; // see Locations in /Chart_config.js
			}

			if( Chart_context == 'days' ){

					// Canvas_chart array - One chart for render visits by period
					Canvas_chart = ['days'];
			}

			if( Chart_context == 'products' ){

					// Canvas_chart array - One chart for render products by period
					Canvas_chart = ['products'];
			}

			if( Chart_context == 'cart' ){

					// Canvas_chart array - One chart for render products by period
					Canvas_chart = ['cart'];
			}

			// empty things - do nothing - keep this
			if( $.o.stats.total_nb_visits == 0
					|| $.o.stats.total_nb_products == 0
					|| $.o.stats.total_nb_in_cart == 0 ){

					// do nothing
					return;
			}

			set_chart_OPTIONS();
			set_height_canvas();
	 		render_graphs();
	}
	/**
	 * chart_controller();
	 */



	/**
	 * default_legend_click();
	 *
	 * default behaviour of Chart.js
	 * @return {void}  hide or show charts on click in legends
	 */
	function default_legend_click(){

			return function(e, legendItem, legend) {

					const index = legendItem.datasetIndex;

					const ci = legend.chart;

					if( ci.isDatasetVisible(index) ){

						  ci.hide(index);
			        legendItem.hidden = true;
			    }
					else{

							ci.show(index);
			        legendItem.hidden = false;
			    }
			}
			// end function returned

	}
	/**
	 * default_legend_click();
	 */



	/**
	 * Chart_options - SET A GLOBAL OPTIONS OBJECT
	 * - this can be overridden depending on the context
	 */
	var Chart_options_default = {

			plugins : {
					legend : {
							labels : {},
							display : true,
							onClick : null, // disable click on legend tags
					},
					tooltip : {
							callbacks : {

							},
					},
					filler : {
	            propagate : true,
	        },
			},
			scales : {

					y : { // labels in horizontal bar
							min : 0,
							ticks : {
									autoSkip : true, // - false, show all labels
									stepSize : 1, // really show all labels
									padding : 5, // padding y axis outside canvas
							},
							grid : {
									display : false,
							},
							offset : false, // draw outside the padding
					},
					x : {
							min : 0,
							ticks : {
									autoSkip : true, // not show all labels
									stepSize : 1, // ignored if autoSkip == true ?
									padding : 5,
							},
							grid : {
									display : false,
							},
							offset : false, // draw outside the padding
							// stacked : false,
					}
			},
			// end scales
			datasets : {
					bar : {
							barThickness : 20,
							maxBarThickness : 30,
							minBarLength : 5,
					},
					line : {
							backgroundColor : Stats_color_line,
					},
					doughnut : {

					},
			},
			elements : {
					line : {

					},
					point: {

					},
					bar : {

					}
			},

	}
	/**
	 * end Chart_options_default {}
	 */


	// set an empty chart options -< it will be imbibed by set_chart_OPTIONS();
	// with the reference object : Chart_options_default { }
	var Chart_options = {};


	/**
	 * 	set_chart_OPTIONS();
	 *
	 *  General config : see /Chart_config.js
	 *
	 * @return {object}  Chart_options set options for chart in context
	 */
	function set_chart_OPTIONS(){

		// renew object chart options
		Chart_options = {};

		// copy base
		Chart_options = JSON.parse(JSON.stringify(Chart_options_default));

		// Chart type == bar
		if( Chart_type == 'bar' ){

				// horizontal bars for localities && products
				if( Chart_context == 'localities'
						|| Chart_context == 'products' ){


						// Chart_options.scales.y
						Chart_options.scales.y = {
								ticks : {
										source:'labels',
										autoSkip: false, // false -> show all labels
										stepSize : 1, // ignored if autoSkip == true ?
										padding: 10,
								},
								grid: {
										display: false
								},
								offset : true,
						};
						// end Chart_options.scales.y

						// Chart_options.scales.x
						Chart_options.scales.x = {
								position : 'top',
								ticks: {
										autoSkip : true, // true -> not show all labels
										stepSize : 1,
								},
								grid : {
										drawBorder : false,
										color : 'rgba(230, 230, 230, 0.3)',
										borderDash : [5,5],
										borderDashOffset : 0,
										borderWidth : 1,
								},
								offset : false,
						};
						// Chart_options.scales.x

						// horizontal bars for localities context
						Chart_options.indexAxis = 'y';

						// not display legend items
						Chart_options.plugins.legend.display = false;

						// width bars
						Chart_options.datasets.bar.barThickness = 18;
						Chart_options.datasets.bar.maxBarThickness = 30;
						Chart_options.datasets.bar.minBarLength = 20; // min height of bar

						// bar colors days
						Chart_options.datasets.bar.backgroundColor = ARR_color;

						// interaction horizontal bar
						Chart_options.interaction = {
					      intersect: false,
					      mode: 'index',
								axis: 'y',
				    };
				}
				// end  Chart_type == 'bar'
				// && Chart_context == 'localities' || 'products'


				// BARS + DAYS
				if( Chart_context == 'days' ){

						// Chart_options.scales.y
						Chart_options.scales.y = {
								ticks : {
										padding: 10
								},
								grid : {
										drawBorder : false,
										color : 'rgba(230, 230, 230, 0.3)',
										borderDash : [5,5],
										borderDashOffset : 0,
										borderWidth : 1,
								},
								offset : false,
						};
						// end Chart_options.scales.y

						// Chart_options.scales.x
						Chart_options.scales.x = {
								ticks: {
									autoSkip: false, // false - show all labels
									stepSize : 1, // ignored if autoSkip == true ?
									padding: 10
								},
								grid : {
										display: false
								},
								offset : true, // true - draw all bars same width
						};
						// Chart_options.scales.x

						Chart_options.indexAxis = 'x';

						Chart_options.plugins.legend.display = false;
						Chart_options.datasets.bar.barThickness = 20;
						Chart_options.datasets.bar.maxBarThickness = 30;
						Chart_options.datasets.bar.minBarLength = 20; // min height of bar

						// bar colors days
						// 7 colors , one for each day
						Chart_options.datasets.bar.backgroundColor = ARR_color.slice(0,7);
						// one color
						// Chart_options.backgroundColor = '#4ae2d1';

						// year case / override
						if( Period.value == 'y' ){

								Chart_options.datasets.bar.barThickness = 6;
								Chart_options.datasets.bar.borderSkipped = false;
								Chart_options.scales.x.ticks.display = false;
						}

						// for all bars + days
						Chart_options.interaction = {
							intersect: false,
							mode: 'index',
							axis: 'x',
						};

				}
				// end BARS + DAYS


				// BARS + CART
				if( Chart_context == 'cart' ){

						// Chart_options.scales.y
						Chart_options.scales.y = {
								ticks : {
										padding: 10
								},
								grid : {
										drawBorder : false,
										color : Fill_color,
										borderDash : [5,5],
										borderDashOffset : 0,
										borderWidth : 1,
								},
								offset : false,
								min : 0,
								max : set_max_scale_bar_cart(),
						};
						// end Chart_options.scales.y

						// Chart_options.scales.x
						Chart_options.scales.x = {
								ticks: {
									autoSkip: false, // false - show all labels
									stepSize : 1, // ignored if autoSkip == true ?
									padding: 5
								},
								grid : {
										display: false
								},
								offset : true, // true - draw all bars same width
						};
						// Chart_options.scales.x

						Chart_options.indexAxis = 'x';

						// // stacked
						// Chart_options.scales.x.stacked = true;
						// Chart_options.scales.y.stacked = true;

						// width bars
						Chart_options.datasets.bar.barThickness = 18;
						Chart_options.datasets.bar.maxBarThickness = 30;
						Chart_options.datasets.bar.minBarLength = 20; // min height of bar

						Chart_options.plugins.legend.onClick = default_legend_click();

						// force horizontal bar if tto much datas
						if( typeof $.o.stats.nb_products_processed != 'undefined'
								&& $.o.stats.nb_products_processed > 20 ){

									Chart_options.indexAxis = 'y'; // permute axes
									Chart_options.plugins.legend.position = 'top'; // legend on top
									Chart_options.datasets.bar.barThickness = 10;

									delete Chart_options.scales.x.ticks.autoSkip; // show all datas
									delete Chart_options.scales.x.ticks.stepSize;
									Chart_options.scales.x.position = 'top';
									Chart_options.scales.x.offset = false;
									Chart_options.scales.x.max = set_max_scale_bar_cart();
									Chart_options.scales.x.grid = {
											drawBorder : false,
											color : Fill_color,
											borderDash : [5,5],
											borderDashOffset : 0,
											borderWidth : 1,
									};

									Chart_options.scales.y.ticks = {
										autoSkip: false, // false - show all labels
										stepSize : 1, // ignored if autoSkip == true ?
										padding: 5
									};
									Chart_options.scales.y.offset = true; // true - draw all bars same width
									Chart_options.scales.y.beginAtZero = true;
									Chart_options.scales.y.grid.display = false;
									delete Chart_options.scales.y.max;
						}
						// end force horizontal bar

				}
				// end BARS + CART


		}
		// Chart type == bar



		// Chart type == line / i.-> no lines on countries
		if( Chart_type == 'line' ){

				// COMMONS FOR LINE
				// Chart_options.scales.y
				Chart_options.scales.y = {
						min : 0,
						ticks : {
								beginAtZero: true,
								autoSkip : true,
								padding: 10,
								stepSize : 1,
						},
						grid : {
								drawBorder : false,
								color : 'rgba(230, 230, 230, 0.3)',
								borderDash : [5,5],
								borderDashOffset : 10,
								borderWidth : 1,
						},
						offset : false,
				};
				// end Chart_options.scales.y

				// Chart_options.scales.x
				Chart_options.scales.x = {
						ticks: {
								source:'labels',
								autoSkip: false, // false - show all labels
								stepSize : 1, // ignored if autoSkip == true ?
								padding: 10,
								beginAtZero: true,
						},
						grid : {
								drawBorder : true,
								color : '#3a3e42',
						},
						offset : true,
				};
				// Chart_options.scales.x

				// datas in x AXE
				Chart_options.indexAxis = 'x';

				Chart_options.plugins.legend.display = false;

				// color of line : see /Chart_config.js
				Chart_options.datasets.line.backgroundColor = Stats_color_line;
				Chart_options.datasets.line.fill = false;

				if( Chart_context == 'products' ){

						Chart_options.scales.x.grid.color = 'rgb(58 202 154 / 50%)';
				}

				// type 'line' -> year case / override
				if( Period.value == 'y' ){

						// datas in y AXE
						Chart_options.indexAxis = 'y';
						Chart_options.scales.x.ticks.autoSkip = true; // not show all datas
						Chart_options.scales.x.ticks.padding = 0;

						Chart_options.scales.x.position = 'top';
						Chart_options.scales.x.offset = false;
						Chart_options.scales.x.grid = {
								drawBorder : false,
								color : Fill_color,
								borderDash : [5,5],
								borderDashOffset : 10,
								borderWidth : 1,
						};

						Chart_options.scales.y.reverse = true; // reverse datas ?
						Chart_options.scales.y.ticks.autoSkip = false;
						Chart_options.scales.y.ticks.padding = 10;
						Chart_options.scales.y.ticks.align = 'center'; // start / center / end
						Chart_options.scales.y.grid.color = 'rgb(58 202 154 / 50%)';
						Chart_options.scales.y.grid.borderDash = 0;
						Chart_options.scales.y.offset = true;

						Chart_options.datasets.line.fill = true;
						Chart_options.datasets.line.backgroundColor =
						'rgba(80, 255, 197, 0.38)'; // green transparent

				}
				// end year case / override


				// line -> horizontal && vertical
				Chart_options.interaction = {
						// true : interaction exclusive mouse + w. points
						// false : not interaction exclusive w. points
						intersect: true,
						mode: 'nearest',
						axis: 'y',
				};


				// line + cart
				if( Chart_context == 'cart' ){


						Chart_options.elements.line.backgroundColor =
							[ BackgroundColorStack_2, BackgroundColorStack_1 ];

						Chart_options.elements.line.borderColor =
							[ BackgroundColorStack_2, BackgroundColorStack_1 ];

						Chart_options.datasets.line.fill = false;

						Chart_options.plugins.legend.display = true;

						// attr default click on legend
						Chart_options.plugins.legend.onClick = default_legend_click();

						// manage Scale_index = 'y' | 'x' according direction of chart horiz./vertical
						let Scale_index = 'y';

						// Manage interaction
						let Interaction = {
								// true : interaction exclusive mouse + w. points
								// false : not interaction exclusive w. points
								intersect: true,
								mode: 'index',
								axis: 'x',
						};

						// line + cart : year case
						if( Period.value == 'y' ){

								// put legend on top for year
								Chart_options.plugins.legend.position = 'top';

								// scale to apply value min-max - this vary in direction context
								Scale_index = 'x';

								// override Interaction for year view
								Interaction = {
										mode: 'index',
										axis: 'y',
								};
						}

						// force max value in labels / same if skipped=true
						Chart_options.scales[Scale_index].min = 0;
						// Chart_options.scales[Scale_index].max = set_max_scale_line_cart();
						Chart_options.scales[Scale_index].ticks = {
							padding : 10,
						}; // this re-init tick un-bug

						// interaction on context
						Chart_options.interaction = Interaction;

				}
				// end line + cart

		}
		// end Chart type == line



		// Chart type ==  doughnut
		if( Chart_type == 'doughnut' ){

				// disable scales
				Chart_options.scales = false;

				// enable legend items
				Chart_options.plugins.legend.display = true;

				// interaction horizontal bar
				Chart_options.interaction = {
						intersect: false,
						mode: 'point',
						// axis: 'xy',
				};

				// doughnut + cart
				if( Chart_context == 'cart' ){

						// attr default click on legend
						delete Chart_options.plugins.legend.onClick;

						// detach protion of doughnut
						// Chart_options.datasets.doughnut.hoverOffset = 5;
				}

		}
		// end Chart type ==  doughnut



		// FOR ALL - tooltips
		Chart_options.plugins.tooltip.callbacks = {

				label: function( context ){

						// console.log( context );
						// context.datasetIndex : 0 -> visits
						// context.datasetIndex : 1 -> in_cart
						// context.datasetIndex : 2 -> purchased

						// get the label text // context.dataset.label ||
						let label = context.label || '';

						// get the value of data
						let value = context.dataset.data[context.dataIndex]; // get value

						// make a text for render nice informations
						let textValueTooltip = '';

						if( Chart_context == 'products' ){
								// render (str)-> "3 View(s)"
								textValueTooltip = value+' '+$.o.tr.product_wiews;
						}
						else if( Chart_context == 'cart' ){

								// render (str)-> "Articles in cart" / "Articles purchased" / "Visits"
								textValueTooltip = value;

								// Adjust text of tooltips on context
								// -> if have visits datas : visits in on 0 index
								if( Chart_type == 'line' && context.datasetIndex == 0 ){

										textValueTooltip += ' '+$.o.tr.visits;
								}

								if( Chart_type != 'line' && context.datasetIndex == 0
										|| Chart_type == 'line' && context.datasetIndex == 1 ){

										textValueTooltip += ' '+$.o.tr.products_in_cart;
								}

								if( Chart_type != 'line' && context.datasetIndex == 1
										|| Chart_type == 'line' && context.datasetIndex == 2 ){

										textValueTooltip += ' '+$.o.tr.products_purchased;
								}

								// tooltip for cart + doughnut
								if( Chart_type == 'doughnut' ){

										textValueTooltip = value+' '+$.o.tr.products_purchased;
								}

						}
						else{
								// render (str)-> "5 Visits | 1 Visit"
								textValueTooltip = ( value == 1 )
									? value+' '+$.o.tr.visit : value+' '+$.o.tr.visits;
						}

						return ( Chart_type == 'doughnut' )
							? [label, textValueTooltip] : textValueTooltip;

				},
				// end Chart_options.plugins.tooltip.callbacks : label : function
		};
		// end FOR ALL - TOOLTIPS

	}
	/**
	 * 	set_chart_OPTIONS();
	 */



	/**
	 * set_max_scale_line_cart();
	 *
	 * @return {type}  description
	 */
	function set_max_scale_line_cart(){

			let top = 0;

			let Obj_iterate = ( Chart_type == 'line' ) ?
			$.o.stats.cart_days.days_nb_visits
			: $.o.stats.cart_days.in_cart;

			Obj_iterate.forEach((item, i) => {

					top = ( top > item )
					? top : Obj_iterate[i];

			});

			return top;
	}
	/**
	 * set_max_scale_line_cart();
	 */



	/**
	 * set_max_scale_bar_cart();
	 *
	 * @return {type}  description
	 */
	function set_max_scale_bar_cart(){

			let top = 0;

			$.o.stats.cart.in_cart.forEach((item, i) => {

					top = ( top > item )
					? top : $.o.stats.cart.in_cart[i];

			});

			return top;
	}
	/**
	 * set_max_scale_bar_cart()
	 */



	/**
	 * set_height_canvas();
	 *
	 * @return {type}  description
	 */
	function set_height_canvas(){


			let width, height;

			// ADJUST OPTIONS FOR CHART TYPE
			switch ( Chart_type ) {

				// LINE
				case 'line' :

					// modif. canvas only in large screen
					if( window.innerWidth > 800 ){

							width = '600px'; // width calulated by chart.js
							height = '500px'; // height still fixed
					}
					else{
							// for mobile devices
							width = '500px';
							height = '500px';
					}

					// vertical line chart for days and year
					if( Period.value == 'y' && Chart_context == 'days'){

							// calcul ponderations for dynamic height of each canvas
							let nb_items = $.o.stats.days.dates.length;

							let calc = nb_items*35;
							height = calc+'px';
					}

					// vertical line chart for cart and year
					if( Period.value == 'y' && Chart_context == 'cart'){

							// ! Not same items ! calcul ponderations for dynamic height of each canvas
							let nb_items = $.o.stats.cart_days.days.length;

							let calc = nb_items*35;
							height = calc+'px';
					}


					// line - only one canvas
					let Canvas_Cities = document.getElementById('cities');
					Canvas_Cities.style.width = width;
					Canvas_Cities.style.height = height;

				break;

				// BAR
				case 'bar':

					// modif. canvas only in large screen
					if( window.innerWidth > 800 ){

							width = '600px'; // width calulated by chart.js
							height = '500px'; // height still fixed

					}
					else{
							// for mobile devices
							width = '500px'; // width still fixed
							height = '500px';

					}

					// apply style for BAR + 'days' || 'products' context - ONE chart context
					if( Chart_context == 'days'
							|| Chart_context == 'products'
							|| Chart_context == 'cart'  ){

							let Canvas_Cities = document.getElementById('cities');
							Canvas_Cities.style.width = width;
							Canvas_Cities.style.height = height;
					}

					// special case : BAR + Chart_context == 'localities'
					if( Chart_context == 'localities' ){

							// calcul height - attr styles to all canvas
							Locations.forEach(function(for_item, i){

								// take number of items and calcul height for each canvas
								let nb_items = $.o.stats.localities[for_item].names.length;
								let calc = nb_items*24;
								// calcul ponderations for dynamic height of each canvas
								let height = ( nb_items > 10 && nb_items <= 15 ) ? 480 : ( calc < 480 ) ? 480 : calc;
								height = ( nb_items > 5 && nb_items <= 10 ) ? 380 : height;
								height = ( nb_items <= 5 ) ? 300 : height;

								// apply style foreach canvas
								let Canva = document.getElementById(for_item);
								Canva.style.width = width;
								Canva.style.height = height+'px';
							});
							// end loop localities objects
					}
					// END special case : BAR + Chart_context == 'localities'

					// horizontal bar + cart if nb > 20
					if( Chart_context == 'cart'
							&& typeof $.o.stats.nb_products_processed != 'undefined'
							&& $.o.stats.nb_products_processed > 20 ){

								let nb_items = $.o.stats.cart_days.days.length;

								let calc = nb_items*70;
								height = calc+'px';

					}
					// end horizontal bar + cart

				break;
				// end BAR

				// DOUNGHNUT
				case 'doughnut':

					width = '400px';
					height = '400px';

					const Canvas_Html = document.getElementsByTagName('canvas');

					// in case of doughnut -> same size for all devices
					for(var i=0; i < Canvas_Html.length;i++){

							Canvas_Html[i].style.width = width;
							Canvas_Html[i].style.height = height;
					}

				break;
				// end DOUNGHNUT

				default:
					width = '600px';
					height = '500px';
			}
			// end switch
			// end ADJUST width / height for canvas

	}
	/**
	 * set_height_canvas();
	 */



	/**
	 * 	Placido_Charts
	 *  make an object for render all charts
	 */
	var Placido_Charts = {};

	/**
	 * render_graphs();
	 *
	 * @return {canvas} show canvas visits by cities, countries, regions, timezones
	 */
	function render_graphs(){


		// LOOP OVER Canvas_chart = Locations[] | ['days']
		Canvas_chart.forEach(function(for_item, k_arr){

				let Canva_item,
				Labels,
				Datas,
				Title_Chart;

				// render one canvas
				if( for_item == 'days' ){

						// datas sets
						Labels = $.o.stats.days.dates;
						Datas = [{ data : $.o.stats.days.days_nb }];

						// in one chart case get just the canvas 'cities'
						Canva_item = document.getElementById('cities');

						// set title of chart
						Title_Chart = $.o.stats.days.title_graph;

				}
				else if( for_item == 'products' ){
						// products case - one chart

						// datas sets
						Labels = $.o.stats.products.titles;
						Datas = [{ data : $.o.stats.products.nb_visits }];

						// in one char case get just the canvas 'cities'
						Canva_item = document.getElementById('cities');

						// set title of chart
						Title_Chart = $.o.stats.title_graph;
				}
				else if( for_item == 'cart' ){
						// cart case - one chart

						// datas sets
						let Datasets_Datas_1, Datasets_Datas_2, Fill;

						Bkg_color_1 = BackgroundColorStack_2;
						Bkg_color_2 = BackgroundColorStack_1;

						// render by date if CART + line
						if( Chart_type == 'line' ){

								Labels = $.o.stats.cart_days.days;
								Datasets_Datas_1 = $.o.stats.cart_days.in_cart;
								Datasets_Datas_2 = $.o.stats.cart_days.purchased;

								// always cart_days.days_nb_visits on CART + line
								Datasets_Datas_add =
								{
									label : $.o.tr.visits,
									data : $.o.stats.cart_days.days_nb_visits,
									backgroundColor : BackgroundColorStack_3,
									borderColor : BackgroundColorStack_3,
									pointBackgroundColor : BackgroundColorStack_3,
									pointBorderColor : BackgroundColorStack_3,
								};

								// fill between datasets for year
								if( Period.value == 'y' ){

										// set fill color between 2 lines
										// Bkg_color_1 = Fill_color;
										Fill = {
											target: 2,
											above: Fill_color, // set both above + below for fill between sale color
											below: Fill_color
										};
								}
								else{
										Fill = false;
								}

						} // end CART + line
						else{

								// render by titles products
								Labels = $.o.stats.cart.titles;
								Datasets_Datas_1 = $.o.stats.cart.in_cart;
								Datasets_Datas_2 = $.o.stats.cart.purchased;
								Fill = false;

						}

						if( Chart_type == 'doughnut' ){

								Bkg_color_1 = ARR_color;
								Bkg_color_2 = ARR_color;
						}

						// make datas array
						Datas = [
											{
												label : $.o.tr.products_in_cart,
												data : Datasets_Datas_1,
												backgroundColor : Bkg_color_1,
												borderColor : Bkg_color_1,
												pointBackgroundColor : BackgroundColorStack_2,
												pointBorderColor : BackgroundColorStack_2,
												fill: Fill
											},
											{
												label : $.o.tr.products_purchased,
												data : Datasets_Datas_2,
												backgroundColor : Bkg_color_2,
												borderColor : Bkg_color_2,
												pointBackgroundColor : BackgroundColorStack_1,
												pointBorderColor : BackgroundColorStack_1,
											},
						];
						// end Datas

						// CART + LINE -> push : days_nb_visits  stats
						if( Chart_type == 'line' ){

								// add some datasets on context
								// here insert visits at the 0 index
								Datas.splice(0, 0, Datasets_Datas_add);
								// Datas.push( Datasets_Datas_add );
						}

						// set title of chart
						Title_Chart = $.o.stats.title_graph;


						// doughnut -> remove products in cart
						if( Chart_type == 'doughnut' ){

								// remove datas in_cart -> keep only products purchased
								Datas.splice(0, 1);

								// overide title for render conversion rate by visits
								let average =
								(($.o.stats.total_nb_purchased / $.o.stats.cart_days.total_nb_visits) * 100).toFixed(2);

								// format average in locale get 0.00 or 0,00
								average = new Intl.NumberFormat().format(average);

								Title_Chart = $.o.stats.cart_days.total_nb_visits+` `+$.o.tr.visits
								+` / `+$.o.stats.total_nb_purchased+` `+$.o.tr.products_purchased
								+`<br>`+$.o.tr.conversion_rate_by_visits+` : `+average+` %`;
						}

						// in one chart case get just the canvas 'cities'
						Canva_item = document.getElementById('cities');

				}
				else{
					// render multiples canvas

					// datas sets
					Labels = $.o.stats.localities[for_item].names;
					Datas = [{ data : $.o.stats.localities[for_item].nb_visits }];

					// get canvas one by one : id="cities, id="regions", id="countries", id="timezones"
					Canva_item = document.getElementById(for_item);

					// title of chart  + nb foreach items
					let nb_for_item = $.o.stats.localities[for_item].names.length;
					Title_Chart = Locations_translate[k_arr]+`  :  `+nb_for_item;

				}
				// end  render multiples canvas


				// display block all containers of canvas
				let block_canv = document.getElementsByClassName('localities');
				block_canv[k_arr].style.display = 'block';

				// render titles
				let title = document.getElementsByClassName('title_chart');
				title[k_arr].innerHTML = Title_Chart;


				// register all canvas in Placido_Charts - render it
				Placido_Charts[''+for_item+''] = new Chart( Canva_item ,{

						type: Chart_type, // 'doughnut', 'bar', 'line'
						data: {
								labels: Labels, // labels
								datasets: Datas
						},
						options: Chart_options,

				});
				// end Placido_Charts

		});
		// END LOOP ARRAY_ITEMS

	}
	/**
	 * render_graphs();
	 */


// END stats.js
