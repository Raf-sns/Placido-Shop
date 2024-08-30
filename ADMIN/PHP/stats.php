<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	stats.php
 *
 * stats::check_token_api();
 * stats::record_stats_token();
 * stats::get_stat_day_nb();
 * stats::get_stat_days_nb_by_period( $date_start, $date_end );
 * stats::return_stats( $GET_STATS, $period, $date_start, $date_end );
 * stats::get_stats_days( $GET_STATS, $period, $date_start_str, $date_end_str );
 * stats::get_stats_by_interval();
 * stats::stats_products( $GET_STATS, $period, $date_start_str, $date_end_str );
 * stats::stats_cart( $GET_STATS, $period, $date_start, $date_end );
 *
 */

class stats {


	/**
	 * stats::check_token_api();
	 *
	 * @return {string}  return true or false if an IPinfo token is registred or not
	 */
	public static function check_token_api(){


			// fetch token in DB
			$ARR_pdo = array('id' => 0);
      $sql = 'SELECT token FROM stats_token WHERE id=:id';
			$response = 'one';
      $last_id = false;

      $GET_STATS_TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);

			// error fetch token
			if( empty($GET_STATS_TOKEN['token']) ){

					return false;
			}

			// else
			return true;

	}
	/**
	 * stats::check_token_api();
	 */



  /**
   * 	stats::record_stats_token();
   *
   * @return {json}  return success / error on record token API
   */
  public static function record_stats_token(){


			// VERIFY token
      token::verify_token();

			$token_api = (string) trim(htmlspecialchars($_POST['token_api']));

			// test length
			if( iconv_strlen($token_api) > 500 ){

					$tab = array('error' => tr::$TR['token_stats_too_long'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// crypt token
			$token_encrypted = ( empty($token_api) )
			? '' : api::api_crypt( $token_api, 'encr' );

			// insert token
			$ARR_pdo = array( 'id' => 0, 'token' => $token_encrypted );
      $sql = 'INSERT INTO stats_token (id, token)
      VALUES (:id, :token)
			ON DUPLICATE KEY UPDATE token=:token';
      $response = false;
      $last_id = false;

      $INSERT_TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);

			// error
			if( boolval($INSERT_TOKEN) == false ){

					$tab = array( 'error' => tr::$TR['error_create_token'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// check token - no need to ask database
			$check_token = ( empty($token_api) ) ? false : true;

			// success
			$tab = array( 'success' => tr::$TR['token_stats_well_recorded'],
										'token_api' => $check_token  );

			echo json_encode($tab, JSON_FORCE_OBJECT);
			exit;

	}
  /**
   * 	stats::record_stats_token();
   */



  /**
   * 	stats::get_stat_day_nb();
   *
   * @return {string}  number of visits for today formatted in string
   */
  public static function get_stat_day_nb(){


      // fetch nb visits for today
      $day = date( 'Y-m-d', time() );

      $ARR_pdo = array( 'day' => $day );
      $sql = 'SELECT day_nb FROM stats_loca WHERE day=:day';
      $response = 'one';
      $last_id = false;

      $FETCH_NB_DAY = db::server($ARR_pdo, $sql, $response, $last_id);

      // if no record for today
      if( empty($FETCH_NB_DAY) ){

          $FETCH_NB_DAY = 0;
      }
      else{
          $FETCH_NB_DAY = $FETCH_NB_DAY['day_nb']; // db return a string
      }

      // RETURN nb VISITS for today
      return ( $FETCH_NB_DAY == 1 ) ?
				$FETCH_NB_DAY.' '.tr::$TR['visit'] : $FETCH_NB_DAY.' '.tr::$TR['visits'];

  }
  /**
   * stats::get_stat_day_nb();
   */



  /**
   * stats::get_stat_days_nb_by_period( $date_start, $date_end );
   *
   * @param  {string} $date_start
   * @param  {string} $date_end
   * @return {array} all rows from stats_loca -> return [...][day(date)+day_nb(int)]
   */
  public static function get_stat_days_nb_by_period( $date_start, $date_end ){


			// prepa. array for PDO::
      $ARR_pdo = array(
				'date_start' => $date_start, // 'yyyy-mm-dd'
				'date_end' => $date_end
			);

      $sql = 'SELECT day, day_nb FROM stats_loca
			WHERE day>=:date_start AND day<=:date_end ORDER BY day ASC';

			$response = 'all'; // mutltiples rows
      $last_id = false;

      $FETCH_ALL_NB_DAYS = db::server($ARR_pdo, $sql, $response, $last_id);

      // if no record for period
      if( empty($FETCH_ALL_NB_DAYS) ){

          $FETCH_ALL_NB_DAYS = array(
						'day' => date('Y-m-d'),
						'day_nb' => 0
					);
      }

      // RETURN nb VISITS for today
      return $FETCH_ALL_NB_DAYS;

  }
  /**
   * stats::get_stat_days_nb_by_period( $date_start, $date_end );
   */



	/**
	 *  stats::set_period( $start, $end, $period );
	 *
	 * @param  {type} $start  	DateTime object
	 * @param  {type} $end    	DateTime object
	 * @param  {char} $period 	'y' / 'm' / 'w' / 'd'
	 * @return {array}         	array(
	 *														'title' => $title,
	 *														'date_start' => $date_start_txt,
	 *														'date_end' => $date_end_txt
	 *													)
	 */
	public static function set_period( $start, $end, $period ){


			// default values
			$title = '';
			$date_end_txt = '';
			$date_start_txt = '';

			// What is the context ? 'y' / 'm' / 'w' / 'd'
			// week case
			if( $period == 'w' ){

					// note : $start->format('W') => Return a string !
					$title = tr::$TR['week_number'].' '.$start->format('W');
			}

			// month case
			if( $period == 'm' ){

					// this render 'April 2022' in locale string
					$title = ucwords( tools::format_date_locale($start, 'FULL' , 'NONE', 'MMMM yyyy') );
			}

			// year case
			if( $period == 'y' ){

					$title = tr::$TR['year'].' : '.$start->format('Y');
			}

			// format date end date start
			$date_start_txt = ucwords( tools::format_date_locale($start, 'FULL' , 'NONE', null) );
			$date_end_txt = ucwords( tools::format_date_locale($end, 'FULL' , 'NONE', null) );

			// day case
			if( $period == 'd' ){

					// check if date is today
					// render last datas check - BEFORE SET TO 00:00:00
					$render_hour = ( $start->format('Y-m-d') == date('Y-m-d') )
					? 'SHORT' : 'NONE';

					// render date + hour is day asked is today
					$title = ucwords( tools::format_date_locale( $start, 'FULL' , $render_hour, null ) );

					// this is evaluated to empty in js front
					$date_end_txt = '';
					$date_start_txt = '';
			}

			// array to return
			return array(
				'title' => $title,
				'date_start' => $date_start_txt,
				'date_end' => $date_end_txt
			);

	}
	/**
	 *  stats::set_period( $start, $end, $period );
	 */



	/**
	 * stats::return_stats( $GET_STATS, $period, $date_start, $date_end );
	 *
	 * @param  {array} 	$GET_STATS  	stats[] render by DB for interval given
	 * @param  {char} 	$period 			'y','m','w','d'
	 * @param  {string} $date_start 	'yyyy-mm-dd'
	 * @param  {string} $date_end   	'yyyy-mm-dd'
	 * @return {json}
	 * array(
	 *   'nb_visits' => stats::get_stat_day_nb(), // get nb visits for today - refresh
	 *   'total_nb_visits' => $total_nb_visits,
	 *   'period'	=> array('date_start'=>$date_start_txt, 'date_end'=>$date_end_txt),
	 *   'cities' => $CITIES,
	 *   'regions'	=> $REGIONS,
	 *   'countries'	=> $COUNTRIES,
	 *   'timezones'	=> $TIMEZONES
	 * );
	 */
	public static function return_stats( $GET_STATS, $period, $date_start, $date_end ){


			// used in loop && in empty case
			$timeZone = new DateTimeZone(TIMEZONE);

			// create locale date for start of period
			$start =
			DateTime::createFromFormat( 'Y-m-d', $date_start, $timeZone );

			$Clone_start =
			DateTime::createFromFormat( 'Y-m-d', $date_start, $timeZone );

			// set start time at 0
			$start->setTime(0, 0);

			// create locale date for end of period
			$end = 	DateTime::createFromFormat( 'Y-m-d', $date_end, $timeZone );
			// set time at 0
			$end->setTime(0, 0);


			// set title period - SET TITLE BEFORE EXIT EMPTY DATAS
			// return array(
			// 	'title' => $title,
			// 	'date_start' => $date_start_txt,
			// 	'date_end' => $date_end_txt
			// )
			$PERIOD = stats::set_period( $Clone_start, $end, $period );


			// EXIT if NO have some stats
      if( empty($GET_STATS) ){

					$tab = array(
						'today_nb_visits' => stats::get_stat_day_nb(), // string get nb visits for today - refresh
						'total_nb_visits' => 0,
						'period'	=> $PERIOD,
						'token_api' => stats::check_token_api()
					);

					echo json_encode($tab, JSON_NUMERIC_CHECK);
					exit;
			}
			// END NO STATS


			// prepa arrays to return
			$CITIES = array( 'names' => array(), 'nb_visits' => array() );
			$REGIONS = array( 'names' => array(), 'nb_visits' => array() );
			$COUNTRIES = array( 'names' => array(), 'nb_visits' => array() );
			$TIMEZONES = array( 'names' => array(), 'nb_visits' => array() );
			$total_nb_visits = 0;

			// prepa array for treatment
			$DATAS = array(
				'city'=>array(),
				'region'=>array(),
				'country'=>array(),
				'timezone'=>array()
			);

			// loop $GET_STATS rendered by DB
			foreach( $GET_STATS as $key => $value ){

					// increm nb_visits
					$total_nb_visits += (int) $value['day_nb'];

					// decode json datas for one day
					$DATAS_DAY = json_decode( $value['json'], true ); // true -> as array


					// foreach datas day loop in $location : ( Locations are arrays )
					// 'city'=>[], 'region'=>[], 'country'=>[], 'timezone'=>[], 'loc'=>[]
					foreach( $DATAS_DAY as $location => $DATAS_loc ){

							// no trait location geographic coords - for the future
							if( $location == 'loc' ) continue;


							// foreach locations datas
							foreach( $DATAS_loc as $k => $v ){

									// var_dump( $DATAS_loc );

									// continue if empty -> otherwise it write an empty array ...
									if( trim($DATAS_loc[$k][''.$location.'_txt']) == '' ) continue;

									$location_txt = trim($DATAS_loc[$k][''.$location.'_txt']);
									$location_nb = (int) $DATAS_loc[$k][''.$location.'_nb'];

									// if location is not already in array -> add it
									if( !in_array( $location_txt,
												array_column( $DATAS[$location], 'name' ) ) ){

											$DATAS[$location][] =
												array( 'name' => $location_txt,
																'nb_visits' => $location_nb );

									}
									else{

											// location is already registred - get his key in array
											$key =
											array_search( $location_txt,
												array_column( $DATAS[$location], 'name' ) );

											// add visits - with the key
											$DATAS[$location][$key]['nb_visits'] += $location_nb;
									}

							}
							// end foreach locations datas

					}
					// end foreach datas day
			}
			// end loop $GET_STATS rendered by DB

			// now $DATAS is full of datas
			// i. DATAS[] Organisation :
			// ['city'] => ['name' => 'string of data', 'nb_visits' => number], [same...], [...]
			// ['region'] => ['name' => 'string of data', 'nb_visits' => number], [same...], [...]
			// ['country'] => [same...], [...], ...
			// ['timezone'] => [same...], [...], ...



			// SORT ALL LOCATIONS
			// - sort all locations from highest to lowest number of visits
			foreach ($DATAS as $location => $array ) {

					// sort arrays from highest to lowest
					usort( $DATAS[$location], function ($a, $b){
							return $b['nb_visits'] - $a['nb_visits'];
					} );

					if( $location == 'city' ){

						// set array to render splitted  datas between labels / number datas
						$CITIES = array(
							// array_column() -> get new array with same indexes
							'names' => array_column($DATAS[$location], 'name'),
							'nb_visits' => array_column($DATAS[$location], 'nb_visits')	);
					}

					if( $location == 'region' ){

						$REGIONS = array(
							'names' => array_column($DATAS[$location], 'name'),
							'nb_visits' => array_column($DATAS[$location], 'nb_visits')	);
					}

					if( $location == 'country' ){

						$COUNTRIES = array(
							'names' => array_column($DATAS[$location], 'name'),
							'nb_visits' => array_column($DATAS[$location], 'nb_visits')	);
					}

					if( $location == 'timezone' ){

						$TIMEZONES = array(
							'names' => array_column($DATAS[$location], 'name'),
							'nb_visits' => array_column($DATAS[$location], 'nb_visits')	);
					}

			}
			// end foreach SORT ALL LOCATIONS



			// return  array 'days'
			// => array( 'dates'=>[], 'days_nb'=>[] , 'title_graph'=> str )
			$DAYS = stats::get_stats_days( $GET_STATS, $period, $date_start, $date_end );

			// array to return
			$tab = array(
				'today_nb_visits' => stats::get_stat_day_nb(), // string get nb visits for today - refresh
				'total_nb_visits' => $total_nb_visits,
				'period'	=> $PERIOD,
				'cities' => $CITIES,
				'regions'	=> $REGIONS,
				'countries'	=> $COUNTRIES,
				'timezones'	=> $TIMEZONES,
				'days' => $DAYS,
				'token_api' => stats::check_token_api()
			);


			// set headers - resolve CORS error ?
			header('Access-Control-Allow-Origin: https://'.HOST.'');
			header('Content-Type: text/plain');

			echo json_encode($tab, JSON_NUMERIC_CHECK);
			exit;

  }
	/**
	 * stats::return_stats( $GET_STATS, $date_start, $date_end );
	 */



	/**
	 * stats::get_stats_days( $GET_STATS, $period, $date_start_str, $date_end_str );
	 *
	 * @param  {array}  $GET_STATS  			DATAS recived from DB by date interval
	 * @param  {string} $date_start_str 	string 'yyyy-mm-dd'
	 * @param  {string} $date_end_str   	stting 'yyyy-mm-dd'
	 * @return {array}           				  return  array 'days'
	 * 										=> array( 'dates'=>[], 'days_nb'=>[] , 'title_graph'=> str )
	 */
	public static function get_stats_days( $GET_STATS, $period, $date_start_str, $date_end_str ){


			// set a DateTime object | setTimezone setted after
			$timeZone = new DateTimeZone(TIMEZONE);

			// set objects date_start / date_end
			$Date_start =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );

			// set obj date at 00:00:00 !
			$Date_start->setTime(0, 0);

			// keep date start for render a title in context
			$Date_Start_Saved =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );
			$Date_Start_Saved->setTime(0, 0);

			$Date_end =
				DateTime::createFromFormat( 'Y-m-d', $date_end_str, $timeZone );

			// set obj date at 00:00:00 !
			$Date_end->setTime(0, 0);

			// LIMIT DATE END at today
			$Date_now = new DateTime('now', $timeZone);
			$Date_now->setTime(0, 0);
			// if date_end > date_now -> date end = date now
			$Date_end = ( $Date_end > $Date_now ) ? $Date_now : $Date_end;

			// calcul nb visits for period
			$nb_total_period = 0;

			// index for navigate into $GET_STATS -> may miss days
			$index_datas = 0;

			// max index of GET_STATS ( server not return all days by days )
			$max_index = count($GET_STATS) - 1;

			// nb days period - for calcul average
			$nb_days_period = 0;


			// array days to construct
			$ARR_days = array( 'dates' => array(),
													'days_nb' => array() );


			// empty ARR_days for each days same if have NO DATAS for this day
			// while $date_start != date_end
			while( $Date_start <= $Date_end ){


					// check date by equivalent strings
					$date_start_format = $Date_start->format('Y-m-d');

					// watch if same date in $GET_STATS by index
					//  note : ( $index_datas <= $max_index )
					// -> day end at data must be < Date_end of period
					if( $index_datas <= $max_index
							&& $date_start_format == $GET_STATS[$index_datas]['day'] ){

							// day nb visits in int.
							$day_nb = (int) $GET_STATS[$index_datas]['day_nb'];

							// insert datas
							$ARR_days['dates'][] =
								ucwords( tools::format_date_locale($Date_start, 'FULL' , 'NONE', null) );
							$ARR_days['days_nb'][] = $day_nb;

							// increm $nb_total_period
							$nb_total_period += $day_nb;

							// increm index - After using value !!!
							$index_datas++;

					}
					else{

							// NO DATAS
							// insert empty datas
							$ARR_days['dates'][] =
								ucwords( tools::format_date_locale($Date_start, 'FULL' , 'NONE', null) );
							$ARR_days['days_nb'][] = 0;

					}
					// end watch if have datas

					// add new day
					$Date_start->add(new DateInterval('P1D'));

					// count a new day
					$nb_days_period++;

			}
			// end  while $date_start != date_end

			// var_dump($nb_total_period);
			// var_dump($nb_days_period);

			// calcul average period
			$average_calc = $nb_total_period / $nb_days_period;
			$average = round($average_calc, 2);
			// var_dump( $average );
			$average = tools::intl_number( $average );

			// calcul min - max
			$max_visits = max( $ARR_days['days_nb'] );
			$min_visits = min( $ARR_days['days_nb'] );

			// write average on the title chart
			$title_graph = tr::$TR['average'].'&nbsp;:&nbsp;&nbsp;'.$average
			.' / '.$nb_days_period.' '.tr::$TR['stats_days'];

			$title_graph .= '<br>'.tr::$TR['stats_max'].'&nbsp;'.$max_visits.' / '.tr::$TR['day']
			.'&nbsp;&nbsp;-';
			$title_graph .= '&nbsp;&nbsp;'.tr::$TR['stats_min'].'&nbsp;'.$min_visits.' / '.tr::$TR['day'].'';

			$ARR_days['title_graph'] = $title_graph;

			// return  array 'days' => array( 'dates'=>[], 'days_nb'=>[] , 'title_graph'=> str )
			return  $ARR_days;

	}
	/**
	* stats::get_stats_days( $GET_STATS, $period, $date_start_str, $date_end_str );
	*/



  /**
   * 	stats::get_stats_by_interval();
   *
   * @return {string}  number of visits for today formatted in string
   */
  public static function get_stats_by_interval(){


			// VERIFY token
      token::verify_token();

			// datas recived
			$year = (string) trim(htmlspecialchars($_POST['year']));
			$month = (string) trim(htmlspecialchars($_POST['month']));
			$week = (int) trim(htmlspecialchars($_POST['week'])); // keep in int !
			$day = (string) trim(htmlspecialchars($_POST['day']));

			// manage stats products context
			// products stats
			if( isset($_POST['context'])
			&& (string) trim(htmlspecialchars($_POST['context'])) == 'products' ){

					$Table = 'stats_prods';
			}
			// cart stats
			if( isset($_POST['context'])
			&& (string) trim(htmlspecialchars($_POST['context'])) == 'cart' ){

					$Table = 'stats_cart';
			}
			// localizations stats
			if( !isset($_POST['context']) ){

					$Table = 'stats_loca';
			}


			// the period keys 'y','m','w','d'
			$period = (string) trim(htmlspecialchars($_POST['period']));

			// verify keys  we want only 'y','m','w','d'
			if( !preg_match('/[y]|[m]|[w]|[d]/i', $period) ){

					exit('Bad datas expected ...');
			}

			// set a DateTime object | setTimezone setted after
			$date = new DateTime('now', new DateTimeZone(TIMEZONE) );

			// if a year is asked
			if( $period == 'y' ){

					// prepare DB REQUEST
					$ARR_pdo = array(
						'year' => $year
					);

					// get all rows by year
					$sql = 'SELECT * FROM '.$Table.' WHERE YEAR(day)=:year ORDER BY day ASC';

					$response = 'all';
					$last_id = false;

					// set date to the first January
					$date->setDate($year, 1, 1);
					// format date start like DB format
					$date_start = $date->format('Y-m-d');

					// add 1 year
					$date->modify('+1 year');
					// remove 1 day for get last day of year ( not new one )
					$date->modify('-1 day');
					// format date end
					$date_end = $date->format('Y-m-d');
					// get : $date_start -> '2022-01-01' && $date_end -> '2022-12-31'

					// var_dump( $date_start );
					// var_dump( $date_end );

			}
			// end year asked


			// if month asked
			if( $period == 'm' ){

					// prepare DB REQUEST
					$ARR_pdo = array(
						'year' => $year,
						'month' => $month
					);

					// get all rows by MONTH and year
					$sql = 'SELECT * FROM '.$Table.'
					WHERE YEAR(day)=:year AND MONTH(day)=:month ORDER BY day ASC';

					$response = 'all';
					$last_id = false;

					// // test
					// $month = 2;

					// set date to the first day of the month
					$date->setDate($year, $month, 1);
					// format date start like DB format
					$date_start = $date->format('Y-m-d');

					// add 1 year
					$date->modify('+1 month');
					// remove 1 day for get last day of month
					$date->modify('-1 day');
					// format date end
					$date_end = $date->format('Y-m-d');
					// get : $date_start -> '2022-02-01' && $date_end -> '2022-02-28'

					// var_dump( $date_start );
					// var_dump( $date_end );

			}
			// end month asked


			// if a week is asked
			if( $period == 'w' ){

					// this get the first day of a week
					$date->setISODate($year,$week);
					$date_start = $date->format('Y-m-d');

					// this get the last day of a week
					$date->setISODate($year,$week, 7);
					$date_end = $date->format('Y-m-d');

					// get : $date_start -> '2022-05-23' && $date_end -> '2022-05-29'
					// var_dump( $date_start );
					// var_dump( $date_end );

					// prepare DB REQUEST
					$ARR_pdo = array(
						'date_start' => $date_start,
						'date_end' => $date_end
					);

					$sql = 'SELECT * FROM '.$Table.' WHERE
									day >= :date_start AND day <= :date_end ORDER BY day ASC';

					$response = 'all';
					$last_id = false;

			}
			// end week


			// if day asked
			if( $period == 'd' ){

					// prepare DB REQUEST
					$ARR_pdo = array(
						'day' => $year.'-'.$month.'-'.$day
					);

					// get all rows by MONTH and year
					$sql = 'SELECT * FROM '.$Table.' WHERE day=:day';

					$response = 'all';
					$last_id = false;

					// set date to the day asked
					$date->setDate($year, $month, $day);
					// format date start like DB format
					$date_start = $date->format('Y-m-d');
					// date end is the same as date start
					$date_end = $date_start;

					// get : $date_start -> '2022-05-27' && $date_end -> '2022-05-27'
					// var_dump( $date_start );
					// var_dump( $date_end );

			}
			// end day asked


			// global DB request
      $GET_STATS = db::server($ARR_pdo, $sql, $response, $last_id);

			// var_dump( $GET_STATS );
			// exit;

			// no specific context
			if( !isset($_POST['context']) ){

					// manage stats localities
					stats::return_stats( $GET_STATS, $period, $date_start, $date_end );
			}

			// products context
			if( isset($_POST['context'])
			&& (string) trim(htmlspecialchars($_POST['context'])) == 'products' ){

					// manage stats products
					stats::stats_products( $GET_STATS, $period, $date_start, $date_end );
			}

			// cart context
			if( isset($_POST['context'])
			&& (string) trim(htmlspecialchars($_POST['context'])) == 'cart' ){

					// manage stats cart
					stats::stats_cart( $GET_STATS, $period, $date_start, $date_end );
			}

  }
  /**
   * stats::get_stats_by_interval();
   */



	/**
	 * 	stats::stats_products( $GET_STATS, $period, $date_start_str, $date_end_str );
	 *  i. -> $GET_STATS return :
	 *    [0][ day: "2022-06-15", products: (json) [ {"title": "prod1", "prod_id": 6, "nb_visits": 3},
	 *    			{"title": "prod12", "prod_id": 10, "nb_visits": 2}, ... ]   ]
	 *    [1][ day: "2022-06-16", products: (json)[...] ]
	 * @return {json}  stats products by interval
	 */
	public static function stats_products( $GET_STATS, $period, $date_start_str, $date_end_str ){


			// set Timezone
			$timeZone = new DateTimeZone(TIMEZONE);

			// set objects date_start / date_end
			$Date_start =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );

			// set obj date at 00:00:00 !
			$Date_start->setTime(0, 0);

			// clone for send an object with CURRENT TIME to PERIOD
			$Clone_Date_start =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );

			// date end
			$Date_end =
				DateTime::createFromFormat( 'Y-m-d', $date_end_str, $timeZone );

			// set obj date at 00:00:00 !
			$Date_end->setTime(0, 0);


			// set title period - SET TITLE BEFORE EXIT EMPTY DATAS
			// return array(
			// 	'title' => $title,
			// 	'date_start' => $date_start_txt,
			// 	'date_end' => $date_end_txt
			// )
			$PERIOD = stats::set_period( $Clone_Date_start, $Date_end, $period );

			// LIMIT DATE END at today
			$Date_now = new DateTime('now', $timeZone);
			$Date_now->setTime(0, 0);
			// if date_end > date_now -> date end = date now
			$Date_end = ( $Date_end > $Date_now ) ? $Date_now : $Date_end;


			// EXIT if NO have some stats
      if( empty($GET_STATS) ){

					$tab = array(
						'total_nb_products' => 0,
						'period' => $PERIOD,
						'token_api' => stats::check_token_api()
					);

					echo json_encode($tab, JSON_NUMERIC_CHECK);
					exit;
			}
			// END NO STATS


			// total number of products viewed
			$total_nb_products = 0;

			// nb days period - for calcul average
			$nb_days_period = 0;

			// index for navigate into $GET_STATS -> may miss days
			$index_datas = 0;

			// max index of GET_STATS ( server not return all days by days )
			$max_index = count($GET_STATS) - 1;


			// prepa empty array products
			$ARR_prods = array();


			// empty ARR_days for each days same if have NO DATAS for this day
			// while $date_start != date_end
			while( $Date_start <= $Date_end ){


					// check date by equivalent strings
					$date_start_format = $Date_start->format('Y-m-d');

					// watch if same date in $GET_STATS by index
					//  note : ( $index_datas <= $max_index )
					// -> day end at data may be < Date_end of period
					if( $index_datas <= $max_index
							&& $date_start_format == $GET_STATS[$index_datas]['day'] ){


							// decode json products
							$PRODS = json_decode( $GET_STATS[$index_datas]['products'], true);
							// var_export( $PRODS );

							// loop products for this day
							foreach( $PRODS as $k => $v ){

									$prod_id = (int) $v['prod_id'];
									$nb_visits = (int) $v['nb_visits'];

									// test if product is already registered
									if( !in_array( $prod_id, array_column($ARR_prods, 'prod_id') ) ){

											// no - add it :
											// cut title for render graph
											$number_to_cut = 30;
											$title = $v['title'];

											// string truncated in one line !
											$truncated_title = ( strlen($title) > 30 ) ?
											substr($v['title'], 0, strrpos(substr($v['title'], 0, $number_to_cut), " ")).' ...'
											: $title;

											$ARR_prods[] = array( 'title' => $truncated_title,
																						'prod_id' => $prod_id,
																						'nb_visits' => $nb_visits );

									}
									else{

											// update nb_visits

											// find key of product
											$key =
											array_search( $prod_id, array_column($ARR_prods, 'prod_id') );

											// add some visits to his own total
											$ARR_prods[$key]['nb_visits'] += $nb_visits;
									}

									// add to total products
									$total_nb_products += $nb_visits;

							}
							// end loop json products

							// increm index - After using value !!!
							$index_datas++;

					}
					// end watch days

					// add new day
					$Date_start->add(new DateInterval('P1D'));

					// count a new day
					$nb_days_period++;

			}
			// end  while $date_start != date_end

			// sort products by nb_visits DESC
			usort($ARR_prods, function($a, $b){
				return $b['nb_visits'] - $a['nb_visits'];
			});

			// calcul average
			$average = round(($total_nb_products / $nb_days_period), 2); // round to 0.00

			$average = tools::intl_number( $average );

			// make a title for graph
			$title_graph = tr::$TR['average_for'].' '.$nb_days_period.' '.tr::$TR['days'].'&nbsp;&nbsp;:&nbsp;&nbsp;
			 '.$average.' '.tr::$TR['products_by_day'];

			// final array splitted for chart js
			$PRODUCTS = array(
				'titles' => array_column($ARR_prods, 'title'),
				'nb_visits' => array_column($ARR_prods, 'nb_visits')
			);

			// set headers - resolve CORS error ?
			header('Access-Control-Allow-Origin: https://'.HOST.'');
			header('Content-Type: text/plain');

			// array to return
			$tab = array(
				'period' => $PERIOD,
				'products' => $PRODUCTS,
				'total_nb_products' => $total_nb_products,
				'title_graph' => $title_graph,
				'token_api' => stats::check_token_api()
			);

			echo json_encode($tab, JSON_NUMERIC_CHECK );
			exit;


	}
	/**
	 * 	stats::get_stats_products( $GET_STATS, $period, $date_start_str, $date_end_str );
	 */



	/**
	 * stats::stats_cart( $GET_STATS, $period, $date_start, $date_end );
	 *
	 * @param  {type} $GET_STATS  description
	 * @param  {type} $period     description
	 * @param  {type} $date_start description
	 * @param  {type} $date_end   description
	 * @return {type}             description
	 */
	public static function stats_cart( $GET_STATS, $period, $date_start_str, $date_end_str ){


			// set Timezone
			$timeZone = new DateTimeZone(TIMEZONE);

			// set objects date_start / date_end
			$Date_start =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );
			// set obj date at 00:00:00 !
			$Date_start->setTime(0, 0);

			// clone for send an object with ! CURRENT TIME ! to PERIOD
			// NOT SET AT 00:00:00 !
			$Clone_Date_start =
				DateTime::createFromFormat( 'Y-m-d', $date_start_str, $timeZone );

			// date end
			$Date_end =
				DateTime::createFromFormat( 'Y-m-d', $date_end_str, $timeZone );

			$Date_end->setTime(0, 0);


			// set_period() - SET TITLE BEFORE EXIT EMPTY DATAS
			// return array(
			// 	'title' => $title,
			// 	'date_start' => $date_start_txt,
			// 	'date_end' => $date_end_txt
			// )
			$PERIOD = stats::set_period( $Clone_Date_start, $Date_end, $period );

			// LIMIT DATE END at today
			$Date_now = new DateTime('now', $timeZone);
			$Date_now->setTime(0, 0);
			// if date_end > date_now -> date end = date now
			$Date_end = ( $Date_end > $Date_now ) ? $Date_now : $Date_end;


			// EXIT if NO have some stats
      if( empty($GET_STATS) ){

					$tab = array(
						'total_nb_in_cart' => 0,
						'total_nb_purchased' => 0,
						'period'	=> $PERIOD,
						'context' => 'cart',
						'token_api' => stats::check_token_api()
					);

					echo json_encode($tab, JSON_NUMERIC_CHECK);
					exit;
			}
			// END NO STATS


			// WE HAVE STATS IN CART

			// get days_nb for this period -> this render not all days
			$DAYS = stats::get_stat_days_nb_by_period( $date_start_str, $date_end_str );

			// total nb in_cart
			$total_nb_in_cart = 0;

			// total nb purchased
			$total_nb_purchased = 0;

			// total nb products processed
			$nb_products_processed = 0;

			// nb days period - for calcul average
			$nb_days_period = 0;

			// index for navigate into $GET_STATS -> may miss days
			$index_datas = 0;

			// max index of GET_STATS ( server not return all days by days )
			$max_index = count($GET_STATS) - 1;

			// prepa empty arrays products
			$ARR_in_cart = array();
			$ARR_prods = array();

			// for render by days
			$ARR_days = array(
				'days' => array(),
				'in_cart' => array(),
				'purchased' => array(),
				'days_nb_visits' => array(),
				'total_nb_visits' => 0,
			);

			// index days nb visits
			$index_days_nb = 0;
			$max_index_days_nb = count($DAYS) - 1;

			// empty ARR_days for each days same if have NO DATAS for this day
			// while $date_start != date_end
			while( $Date_start <= $Date_end ){

					// add a day date formatted to datas FOR CART DAYS
					$ARR_days['days'][] =
					ucwords( tools::format_date_locale( $Date_start, 'FULL' , 'NONE', null ) );


					// check date by equivalent strings
					$date_start_format = $Date_start->format('Y-m-d');

					// days_nb_visits[]
					// if have a some datas visits for this day
					if( $index_days_nb <= $max_index_days_nb
							&& $date_start_format == $DAYS[$index_days_nb]['day'] ){

							// add to days_nb
							$ARR_days['days_nb_visits'][] = $DAYS[$index_days_nb]['day_nb'];

							// add to totel count nb_visits
							$ARR_days['total_nb_visits'] += $DAYS[$index_days_nb]['day_nb'];

							// increm $index_days_nb
							$index_days_nb++;
					}
					else{
							$ARR_days['days_nb_visits'][] = 0;
					}


					// watch if same date in $GET_STATS by index
					//  note : ( $index_datas <= $max_index )
					// -> day end at data may be < Date_end of period
					if( $index_datas <= $max_index
							&& $date_start_format == $GET_STATS[$index_datas]['day'] ){


							// decode json in_cart
							$IN_CART = json_decode( $GET_STATS[$index_datas]['in_cart'], true);

							// empty json cart case :
							if( empty($IN_CART) ){

									// increm index $GET_STATS
									$index_datas++;

									// add new day
									$Date_start->add(new DateInterval('P1D'));

									// count a new day
									$nb_days_period++;

									// add empty datas to days
									$ARR_days['in_cart'][] = 0;
									$ARR_days['purchased'][] = 0;

									// stop loop here
									continue;
							}
							// end empty json cart case :


							// decode json purchased
							$PURCHASED = json_decode( $GET_STATS[$index_datas]['purchased'], true);

							// CART MUST BE NOT EMPTY BUT PURCHASED MUST BE EMPTY
							if( empty($PURCHASED) ){

									// set it to an empty array
									$PURCHASED = array();
							}

							// calcul nb_in_cart && nb_purchased for each days
							$days_nb_in_cart = 0;
							$days_nb_purchased = 0;


							// loop in_cart for this day
							foreach( $IN_CART as $k => $v ){

									$prod_id = (int) $v['prod_id'];
									$nb_visits = (int) $v['nb_visits'];

									// test if product is already registered in $ARR_in_cart
									if( !in_array( $prod_id, array_column($ARR_in_cart, 'prod_id') ) ){

											// no - add it :
											// cut title for render graph
											$number_to_cut = 30;
											$title = $v['title'];

											// string truncated in one line !
											$truncated_title = ( strlen($title) > 30 ) ?
											substr($v['title'], 0, strrpos(substr($v['title'], 0, $number_to_cut), " ")).' ...'
											: $title;

											$ARRAY = array( 'title' => $truncated_title,
																			'prod_id' => $prod_id,
																			'nb_in_cart' => $nb_visits,
																		 	'nb_purchased' => 0 );

											// was purchased ?
											if( in_array( $prod_id, array_column($PURCHASED, 'prod_id') ) ){

													// find his key
													$key =
													array_search( $prod_id, array_column($PURCHASED, 'prod_id') );

													$nb_purchased = (int) $PURCHASED[$key]['nb_visits'];

													$ARRAY['nb_purchased'] = $nb_purchased;

													$total_nb_purchased += $nb_purchased;

													// add to days purchaseds
													$days_nb_purchased += $nb_purchased;
											}
											// end was purchased ?

											// push new product
											$ARR_in_cart[] = $ARRAY;

									}
									else{
											// product is in array

											// find key of product
											$key =
											array_search( $prod_id, array_column($ARR_in_cart, 'prod_id') );

											// add some visits to his own total
											$ARR_in_cart[$key]['nb_in_cart'] += $nb_visits;

											// was purchased ?
											if( in_array( $prod_id, array_column($PURCHASED, 'prod_id') ) ){

													// find his key
													$key_p =
													array_search( $prod_id, array_column($PURCHASED, 'prod_id') );

													$nb_purchased = (int) $PURCHASED[$key_p]['nb_visits'];

													// add to total item
													$ARR_in_cart[$key]['nb_purchased'] += $nb_purchased;

													// add to total purchased
													$total_nb_purchased += $nb_purchased;

													// add to days purchaseds
													$days_nb_purchased += $nb_purchased;
											}
											// end was purchased ?


									}
									// end product is on in_cart array


									// add to total in cart
									$total_nb_in_cart += $nb_visits;

									// add to $days_nb_in_cart
									$days_nb_in_cart += $nb_visits;


									// count nb products processed
									if( !in_array( $prod_id, $ARR_prods ) ){

											$ARR_prods[] = $prod_id;

											// add to count
											$nb_products_processed++;
									}
									// end count nb products processed

							}
							// end loop json products

							// empty array days with numbers datas for each days
							$ARR_days['in_cart'][] = $days_nb_in_cart;
							$ARR_days['purchased'][] = $days_nb_purchased;

							// increm index - After using value !!!
							$index_datas++;

					}
					// end watch if same date in $GET_STATS by index
					else {
							// add empty datas to cart_days
							$ARR_days['in_cart'][] = 0;
							$ARR_days['purchased'][] = 0;
					}

					// add new day
					$Date_start->add(new DateInterval('P1D'));

					// count a new day
					$nb_days_period++;

			}
			// end  while $date_start != date_end


			// sort products by nb_purchased DESC
			usort($ARR_in_cart, function($a, $b){
				return $b['nb_purchased'] - $a['nb_purchased'];
			});


			// calcul average - prevent Division by zero
			$calcul = ( $total_nb_in_cart == 0 )
			? 0
			: ( $total_nb_purchased / $total_nb_in_cart ) * 100;

			$average =
				tools::intl_number( round($calcul, 2) ); // round to 0.00

			// make a title for graph
			$title_graph = $total_nb_in_cart.'&nbsp;'.tr::$TR['products_in_cart'].'&nbsp;/&nbsp;'
			.$total_nb_purchased.'&nbsp;'.tr::$TR['products_purchased'];
			$title_graph .= '<br>'.tr::$TR['conversion_rate_for'].'
											'.$nb_days_period.' '.tr::$TR['days'].'&nbsp;&nbsp;:&nbsp;&nbsp;
			 								'.$average.'&nbsp;%';


			// split arrays for return
			$ARR_return = array();
			$ARR_return['titles'] = array_column($ARR_in_cart, 'title');
			$ARR_return['in_cart'] = array_column($ARR_in_cart, 'nb_in_cart');
			$ARR_return['purchased'] = array_column($ARR_in_cart, 'nb_purchased');

			// set headers - resolve CORS error ?
			header('Access-Control-Allow-Origin: https://'.HOST.'');
			header('Content-Type: text/plain');

			// array to return
			$tab = array(
				'period' => $PERIOD,
				'cart' => $ARR_return,
				'cart_days' => $ARR_days,
				'total_nb_purchased' => $total_nb_purchased,
				'total_nb_in_cart' => $total_nb_in_cart,
				'nb_products_processed' => $nb_products_processed,
				'title_graph' => $title_graph,
				'context' => 'cart',
				'token_api' => stats::check_token_api()
			);

			echo json_encode($tab, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * stats::stats_cart( $GET_STATS, $period, $date_start, $date_end );
	 */


}
// END class stats::


?>
