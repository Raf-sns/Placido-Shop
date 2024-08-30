<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2019-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	stats.php
 *
 * stats::record_location_stat();
 * stats::get_token_api();
 * stats::call_API( $ip, $token_API );
 * stats::record_stat_product();
 * stats::record_stat_cart();
 * stats::stat_token( $prod_id, $command );
 * stats::record_stat_products_purchased( $ARR_prods );
 *
 * // test - for imbibe rand datas
 * stats::record_some_cart();
 *
 */
class stats {


  /**
   * stats::record_location_stat();
   *
   * @return {void}  record a new localisation stats
	 *  www.ipinfo.io return datas
   */
  public static function record_location_stat(){


			// get good ip
			if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){

					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			elseif( isset( $_SERVER['HTTP_CLIENT_IP'] ) ){

					$ip  = $_SERVER['HTTP_CLIENT_IP'];
			}
			else{

					$ip = $_SERVER['REMOTE_ADDR'];
			}

      // check ip for security
      if( filter_var($ip, FILTER_VALIDATE_IP) == false  ){

					exit;
      }

			// array country code
			// this get: $countries[]
      require 'STATS/countries.php';

			// get lang posted by navigator
			$lang_posted = trim(htmlspecialchars($_POST['lang']));

			// substr lang for get only 2 first chars
			$lang = strtoupper( substr($lang_posted, 0, 2) ); // in CAPITALS

			// get token API
			// this return token API OR 'undefined'
			$token_API = stats::get_token_api();


			// get datas if have a token
			if( $token_API != 'undefined' ){

					// return array[] OR 'no_datas'
					// !! WARNING: ipinfo.io return some datas same if
					// $token_API == ''
					$RESP = stats::call_API( $ip, $token_API );

					// no response from API
					if( $RESP == 'no_datas' ){

							// if lang send by navigator is in array countries
							if( array_key_exists($lang, $countries) ){

									// get the country name
									$RESP['country'] = $countries[$lang];
							}
							else{
									// no country found -> pass to undefined
									$RESP['country'] = 'undefined';
							}
					}
					// API RESPONSE :
					else{

							// test if country exist
							if( array_key_exists($RESP['country'], $countries) ){

								// get the country name
								$RESP['country'] = $countries[$RESP['country']];
							}
							else{
								// no country found -> pass to undefined
								$RESP['country'] = 'undefined';
							}
					}
					// end API response

			}
			// end if have a token

			// NO TOKEN
			if( $token_API == 'undefined' ){


					// try to get the country by lang sended by the navigator
					if( array_key_exists($lang, $countries) ){

							// get the country name
							$RESP['country'] = $countries[$lang];
					}
					else{
							// no country found -> pass to undefined
							$RESP['country'] = 'undefined';
					}

					// make undefined values
					$RESP['loc'] = 'undefined';
					$RESP['timezone'] = 'undefined';
					$RESP['region'] = 'undefined';
					$RESP['city'] = 'undefined';

			}
			// end NO TOKEN

      // fetch old json
			$Date = new DateTime('now', new DateTimeZone(TIMEZONE));
			// set date to 00:00:00 for to avoid some problems with day/hour changement
			$Date->setTime(0,0,0);
			$day = $Date->format('Y-m-d');

      $ARR_pdo = array( 'day' => $day );
      $sql = 'SELECT * FROM stats_loca WHERE day=:day';
      $response = 'one';
      $last_id = false;

      $FETCH_STAT = db::server($ARR_pdo, $sql, $response, $last_id);

      // var_dump($FETCH_STAT);

      // if new day
      if( boolval($FETCH_STAT) == false ){

					// create new array for this day -> this will be convert in json
          $_ARR_STAT = array(
              'loc' => array(array('loc_txt' => $RESP['loc'], 'loc_nb' => 0)),
              'timezone' => array(array('timezone_txt' => $RESP['timezone'], 'timezone_nb' => 0)),
              'country' =>  array(array('country_txt' => $RESP['country'], 'country_nb' => 0)),
              'region' =>  array(array('region_txt' => $RESP['region'], 'region_nb' => 0)),
              'city' =>  array(array('city_txt' => $RESP['city'], 'city_nb' => 0))
          );

					// first visit for today
          $day_nb = 1;

      }
      else{

					// take array DB and add or update datas
          $_ARR_STAT = json_decode($FETCH_STAT['json'], true, JSON_NUMERIC_CHECK);

					// increm. nb visits for today
          $day_nb = (int) $FETCH_STAT['day_nb'] + 1;
      }


      foreach( $_ARR_STAT as $k => $v ){

          // for watch if an item is found
          $find = false;

          foreach( $v as $k2 => $v2 ) {

              if( $v2[''.$k.'_txt'] == $RESP[''.$k.''] ){


                  $val = (int) $v2[''.$k.'_nb'];
                  $val++;
                  $_ARR_STAT[''.$k.''][$k2][''.$k.'_nb'] = $val;

                  $find = true;

              }

          }
          // end foreach 2

          // ADD NEW ITEM
          if( $find == false ){

              $_ARR_STAT[''.$k.''][] =
              array(''.$k.'_txt' => $RESP[''.$k.''], ''.$k.'_nb' => 1);

          }

      }
      // end foreach 1


      // encode in json
      $json = json_encode($_ARR_STAT);

      // update stats
      $ARR_pdo = array( 'day' => $day, 'json' => $json, 'day_nb' => $day_nb  );
      $sql = 'INSERT INTO stats_loca (day, json, day_nb)
      VALUES (:day, :json, :day_nb) ON DUPLICATE KEY UPDATE json=:json, day_nb=:day_nb';
      $response = false;
      $last_id = false;

      $RECORD_STAT = db::server($ARR_pdo, $sql, $response, $last_id);

      unset($_POST);
      exit;

  }
  /**
   * end stats::record_location_stat();
   */



	/**
	 * stats::get_token_api();
	 *
	 * @return {string}  return decrypted token or 'undefined'
	 */
	public static function get_token_api(){


			// fetch token in DB
			$ARR_pdo = array('id' => 0);
      $sql = 'SELECT token FROM stats_token WHERE id=:id';
			$response = 'one';
      $last_id = false;

      $GET_STATS_TOKEN = db::server($ARR_pdo, $sql, $response, $last_id);

			// error fetch token
			if( boolval($GET_STATS_TOKEN) == false ){

					return 'undefined';
			}

			// uncrypt token user
			// WARNING: api_crypt() -> encrypt too an empty value
			$token_API = api::api_crypt( $GET_STATS_TOKEN['token'], 'decr' ); // 'encr' / 'decr'

			// empty token
			if( empty(trim($token_API)) ){

					return 'undefined';
			}

			// return decrypted token
			return $token_API;

	}
	/**
	 * stats::get_token_api();
	 */



	/**
	 * stats::call_API( $ip, $token_API );
	 *
	 * @return {array}  return array from ipinfo API
	 * array (
	 *   'ip' => '255.255.255.255',
	 *   'hostname' => 'placido-shop.com',
	 *   'city' => 'Sévérac-le-Château',
	 *   'region' => 'Occitanie',
	 *   'country' => 'FR',
	 *   'loc' => '44.3220,3.0712',
	 *   'org' => 'SNS - Web & informatique',
	 *   'postal' => '12150',
	 *   'timezone' => 'Europe/Paris',
	 * )
	 *
	 */
	public static function call_API( $ip, $token_API ){


			// prepare request to ipinfo
      $API = 'https://ipinfo.io/'.$ip.'/geo?token='.$token_API.'';

			// here we manage the request by cURL, this is the lighter solution
      $cURL = curl_init();
      curl_setopt( $cURL , CURLOPT_URL , $API );
      curl_setopt( $cURL , CURLOPT_HTTPGET , true );
      curl_setopt( $cURL , CURLOPT_RETURNTRANSFER , true );
      curl_setopt( $cURL , CURLOPT_HTTPHEADER ,
      array( 'Content-Type: application/json', 'Accept: application/json' ));

			// execute request
      $REQ = curl_exec($cURL);

			// render as array
			$RESPONSE = json_decode($REQ, true);

      // error cURL
      if( array_key_exists('error', $RESPONSE) ){

          return 'no_datas';
      }

			// return API response as array
			return $RESPONSE;

	}
	/**
	 * stats::call_API( $ip, $token_API );
	 */



	/**
	 * stats::record_stat_product();
	 *
	 * @return {void}  record a statistic for one products
	 */
	public static function record_stat_product(){


			// pass in integer
			$prod_id = (int) trim(htmlspecialchars($_POST['prod_id']));

			// get absolute value (not want -25!)
			$prod_id = abs($prod_id);

			// good value ?
			if( $prod_id > 4000000000 ){

					exit('Very Bad Value !');
			}

			// get the product
			$PROD = shop::get_one_product( $prod_id );

			// exit if empty
			if( empty($PROD) ){
				exit;
			}

			// true prod id
			$prod_id = (int) $PROD['id'];

			// get title
			$title_prod = $PROD['title'];

			// fetch datas for today
			$Date = new DateTime('now', new DateTimeZone(TIMEZONE));
			// set date to 00:00:00 for to avoid some problems with day/hour changement
			$Date->setTime(0,0,0);
			$day = $Date->format('Y-m-d');

      $ARR_pdo = array( 'day' => $day );
      $sql = 'SELECT * FROM stats_prods WHERE day=:day';
      $response = 'one';
      $last_id = false;
			// db request
      $FETCH_STAT = db::server($ARR_pdo, $sql, $response, $last_id);

			// make an array
			// no stats products today
			if( empty($FETCH_STAT) ){

					// create array to insert
					$ARR_stats = array(
						array(
							'title' => $title_prod,
							'prod_id' => $prod_id,
							'nb_visits' => 1
						)
					);

			}
			// have stats for today
			else{

					// decode existing json as array
					$ARR_stats = json_decode($FETCH_STAT['products'], true);

					// test if the product is already present in stats
					$found_prod = false;

					// loop for increment visits AND find prod
					for( $i=0; $i < count($ARR_stats); $i++ ){

							// if found an entry with same id -> increment it
							if( $ARR_stats[$i]['prod_id'] == $prod_id ){

									// product already in stats
									$found_prod = true;

									// increment visits
									$ARR_stats[$i]['nb_visits']++;

									// stop loop here
									break;
							}
							// end if found an entry
					}
					// end loop for increment visits


					// PRODUCT NOT FOUND ON DATAS TODAY
					if( $found_prod == false ){

							// make a new array product for push new entry
							$ARR_stats[] = array(
								'title' => $title_prod,
								'prod_id' => $prod_id,
								'nb_visits' => 1
							);
					}
					// end  PRODUCT NOT FOUND

					// date alerady registred
					$day = $FETCH_STAT['day'];

			}
			// end have stats for today


			// encode json products
			$products = json_encode($ARR_stats);

			// insert new stats
			$ARR_pdo = array( 'day' => $day, 'products' => $products );

			$sql = 'INSERT INTO stats_prods (day, products)
      VALUES (:day, :products)
			ON DUPLICATE KEY UPDATE products=:products';

			$response = false;
      $last_id = false;
			// db request
      $RECORD_STAT_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

			// // for test
			// if( boolval($RECORD_STAT_PROD) == true ){
			//
			// 		echo 'Done !';
			// }

			unset($_POST['prod_id']);

			exit;

	}
	/**
	 * stats::record_stat_product();
	 */



	/**
	 * stats::record_stat_cart();
	 *
	 * @return {void}  add or reove a product in stats_cart
	 */
	public static function record_stat_cart(){


			// start a session if not exist
      if( session_status() === PHP_SESSION_NONE ){

          session_start([
            'name' => 'PLACIDO-SHOP-CART',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60*4, // 4 hours - no effect if server config
            'gc_maxlifetime' => 60*4,
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

			// make a session
			if( !isset($_SESSION['stats_cart']) ){

					$_SESSION['stats_cart'] = 1;
			}

			// test nb records permitted -> 20 max
			if( $_SESSION['stats_cart'] > 20 ){
					// exit if nb requests > 20
					exit;
			}

			// COMMAND POSTED
			$command = (string) trim(htmlspecialchars($_POST['command']));

			if( $command != 'add' && $command != 'remove' ){
				exit('Bad command');
			}


			// POSTED PROD_ID
			$prod_id = (int) trim(htmlspecialchars($_POST['prod_id']));
			$PROD = shop::get_one_product( $prod_id );
			$prod_id = (int) $PROD['id']; // pass true prod id

			// product not found
			if( boolval($PROD) == false || empty($PROD) == true ){
				exit('Product not found');
			}

			// verify is user can modify his own stats
			// stop recording if something is bad
			$token_stats = stats::stat_token($prod_id, $command);
			// echo json_encode(array('token_stats'=> $token));
			// exit;

			// get stats today
			// fetch datas for today
			$date_now = new DateTime('now', new DateTimeZone(TIMEZONE));
			// set DAY time at 00:00:00
			$date_now->setTime(0,0,0);
      $day = $date_now->format('Y-m-d');


      $ARR_pdo = array( 'day' => $day );
      $sql = 'SELECT * FROM stats_cart WHERE day=:day';
      $response = 'one';
      $last_id = false;
			// db request
      $FETCH_STAT = db::server($ARR_pdo, $sql, $response, $last_id);

			// NO DATAS FOR TODAY
			// if empty create new one
			if( ( empty($FETCH_STAT) || empty($FETCH_STAT['in_cart']) )
				&& $command == 'add' 	){

					$IN_cart = array(
							array(
								'prod_id' => $prod_id,
								'title' => $PROD['title'],
								'nb_visits' => 1
							)
					);

			}
			else{

					// decode json in_cart
					$IN_cart = json_decode($FETCH_STAT['in_cart'], true);

					// if product is NOT recoded
					if( !in_array( $prod_id,
								array_column( $IN_cart, 'prod_id' ) ) ){

							$IN_cart[] = array(
								'prod_id' => $prod_id,
								'title' => $PROD['title'],
								'nb_visits' => 1
							);

					}
					else{

							// location is already registred - get his key in array
							$key =
							array_search( $prod_id,
								array_column( $IN_cart, 'prod_id' ) );

							if( $command == 'add' ){

									// add visits - with the key
									$IN_cart[$key]['nb_visits']++;
							}
							if( $command == 'remove' ){

									// add visits - with the key
									$IN_cart[$key]['nb_visits']--;
							}

							// test if number == 0 - REMOVE FROM ARRAY
							if( $IN_cart[$key]['nb_visits'] <= 0 ){

									// suppr in cart at $key , 1 item
									array_splice($IN_cart, $key, 1 );
							}

					}
					// end else - already in cart
			}
			// end have datas


			// datas to record
			$Datas_rec = array(
				'day' => $day,
				'in_cart' => json_encode($IN_cart)
			);

		  // update stats_cart
      $sql = 'INSERT INTO stats_cart ( day, in_cart )
      VALUES ( :day, :in_cart ) ON DUPLICATE KEY UPDATE in_cart=:in_cart';
      $response = false;
      $last_id = false;

      $RECORD_STAT = db::server($Datas_rec, $sql, $response, $last_id);


			// return token for user - !!! NEED TO RETURN TOKEN !!
			$RESP = array(
				// 'day' => $day,
				// 'in_cart' => $IN_cart,
				'token_stats' => $token_stats // always render the token
			);

			// return json
			echo json_encode($RESP, JSON_NUMERIC_CHECK);

      unset($_POST);
      exit;

	}
	/**
	 * stats::record_stat_cart();
	 */



	/**
	 *  stats::stat_token( $prod_id, $command );
	 *
	 * ! User can modify ONLY his own statistics
	 * @param  {type} $prod_id
	 * @param  {type} $command 		'add' / 'remove'
	 * @return {string} 					= $stats_token -> test old token and return a new token
	 * -> exit on error
	 */
	public static function stat_token( $prod_id, $command ){


			// start a session if not exist
      if( session_status() === PHP_SESSION_NONE ){

          session_start([
            'name' => 'PLACIDO-SHOP-TOKEN',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60*4, // 4 hours - no effect if server config
            'gc_maxlifetime' => 60*4,
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

			// make a random string
			$new_crypto_str =
			uniqid( microtime(true).''.random_int(99999, 999999) );

			// make a session
			if( !isset($_SESSION['token']) && $command == 'add' ){

						// calcul a new token
						$old_token = '';
						$token = crypt($old_token, $new_crypto_str);

						$ARR_prods = array();
						$ARR_prods[] = $prod_id;

						$_SESSION['token'] =
							array(
								'prod_ids' => $ARR_prods,
								'token' => $token
							);

						// var_dump($_SESSION['token']['token']);
						return $token;
			}

			// if have a session
			// get old token send by client - un-trust !
			if( !isset($_POST['token']) || empty($_POST['token']) ){
					// stop here if no token id defined
					return;
			}

			$old_token = trim(htmlspecialchars($_POST['token']));

			// make a new token
			// with token send by client - un-trust !
			$token = crypt($old_token, $new_crypto_str);

			// compare if new token is equal to the hash with the token
			// stored in session - trust !
			if( hash_equals( $token,
					crypt($_SESSION['token']['token'], $new_crypto_str)) ){

					// get array of ids sotred in session
					$ARR_prods = $_SESSION['token']['prod_ids'];

					// 'remove' case
					// user can remove this item form cart stats ?
					if( in_array($prod_id, $ARR_prods) == true && $command == 'remove' ){

							// remove item by value
							// thanks the StackOverflow community !
							if( ( $key = array_search($prod_id, $ARR_prods) ) !== false ){

									unset($ARR_prods[$key]);
							}

							// re-attr aray ids to the session
							$_SESSION['token']['prod_ids'] = $ARR_prods;

							// renew token in session
							$_SESSION['token']['token'] = $token;

							// return new token
							return $token;


					} // 'add' case
					else if( !in_array($prod_id, $ARR_prods) && $command == 'add' ){

							// push id prod not present
							$ARR_prods[] = $prod_id;

							// re-attr aray ids to the session
							$_SESSION['token']['prod_ids'] = $ARR_prods;

							// renew token in session
							$_SESSION['token']['token'] = $token;

							// return new token
							return $token;


					}
					else{

							// user send a modification for bad prod id
							// -> hack case
							// exit('Not access');
							exit;
					}

			}
			else{

					// user send resquest to modify not his own stats
					// -> hack case
					// exit('Bad token');
					exit;
			}
			// end else hash not equals

	}
	/**
	 * stats::stat_token( $prod_id, $command );
	 */



	/**
	 * stats::record_stat_products_purchased( $ARR_prods );
	 *
	 * @param  {array} 		$ARR_prods		array of products purchased
	 * @return {void}     record product on stats_cart -> purchased
	 * or modify number of visits
	 */
	public static function record_stat_products_purchased( $ARR_prods ){


			// get stats today
			// fetch datas for today
			$date_now = new DateTime('now', new DateTimeZone(TIMEZONE));
			// set DAY time at 00:00:00
			$date_now->setTime(0,0,0);
			$day = $date_now->format('Y-m-d');

			$ARR_pdo = array( 'day' => $day );
			$sql = 'SELECT * FROM stats_cart WHERE day=:day';
			$response = 'one';
			$last_id = false;
			// db request
			$FETCH_STAT = db::server($ARR_pdo, $sql, $response, $last_id);

			// NO DATAS FOR TODAY
			// if empty create new one
			if( empty($FETCH_STAT) || empty($FETCH_STAT['purchased']) ){

					$PURCHASED = array();

					// Put foeach products
					foreach( $ARR_prods as $k => $v ){

							$PURCHASED[] =	array(
																'prod_id' => $v['prod_id'],
																'title' => $v['title'],
																'nb_visits' => 1
															);
					}
					// end loop $ARR_prods
			}
			else{

					// decode json in_cart
					$PURCHASED = json_decode($FETCH_STAT['purchased'], true);

					// watch if already recorded foreach
					// Put foeach products
					foreach( $ARR_prods as $k => $v ){


							// if product is NOT recoded
							if( !in_array( $v['prod_id'],
							array_column( $PURCHASED, 'prod_id' ) ) ){

								$PURCHASED[] = array(
									'prod_id' => $v['prod_id'],
									'title' => $v['title'],
									'nb_visits' => 1
								);

							}
							else{

								// location is already registred - get his key in array
								$key =
								array_search( $v['prod_id'],
								array_column( $PURCHASED, 'prod_id' ) );

								// add visits - with the key
								$PURCHASED[$key]['nb_visits']++;

							}
							// end else - already in cart
					}
					// end loop $ARR_prods

			}
			// end have datas


			// datas to record
			// $day is at 00:00:00 time
			$Datas_rec = array(
				'day' => $day,
				'purchased' => json_encode($PURCHASED)
			);

			// update stats_cart
			$sql = 'INSERT INTO stats_cart ( day, purchased )
			VALUES ( :day, :purchased ) ON DUPLICATE KEY UPDATE purchased=:purchased';
			$response = false;
			$last_id = false;

			$RECORD_STAT = db::server($Datas_rec, $sql, $response, $last_id);

	}
	/**
	 * stats::record_stat_products_purchased( $ARR_prods );
	 */



	/**
	 * ! FOR TESTING !
	 * stats::record_some_cart();
	 *
	 * @return {type}  description
	 */
	public static function record_some_cart(){


			$date_now = new DateTime('now', new DateTimeZone(TIMEZONE));
			$date_now->setTime(0,0,0);
			// set past date
			$date_now->setDate( date('Y'), '05', '01');
			$past_date = $date_now;

			// date max today
			$date_max = new DateTime('now', new DateTimeZone(TIMEZONE));
			$date_max->setTime(0,0,0);

			// get all products
			$PRODS = shop::get_all_products_on_line();
			$prods_length = count($PRODS)-1;


			// loop from past
			while ( $past_date <= $date_max ) {

					$IN_CART = array();
					$PURCHASED = array();

					shuffle($PRODS);
					$rand_prods = rand(0, $prods_length );

					// loop products
					for ($i=0; $i < $rand_prods; $i++) {

							$rand_in_cart = rand(1, 30);

							$IN_CART[] = array(
								'prod_id' => (int) $PRODS[$i]['id'],
								'title' => $PRODS[$i]['title'],
								'nb_visits' => $rand_in_cart
							);

							$rand_purchased = rand(1, $rand_in_cart);

							$PURCHASED[] = array(
								'prod_id' => (int) $PRODS[$i]['id'],
								'title' => $PRODS[$i]['title'],
								'nb_visits' => $rand_purchased
							);
					}
					// loop products

					$day = $past_date->format('Y-m-d');

					// datas to record
					$Datas_rec = array(
						'day' => $day,
						'in_cart' => json_encode($IN_CART),
						'purchased' => json_encode($PURCHASED)
					);

					// update stats_cart
					$sql = 'INSERT INTO stats_cart ( day, in_cart, purchased )
					VALUES ( :day, :in_cart, :purchased )
					ON DUPLICATE KEY UPDATE in_cart=:in_cart, purchased=:purchased';
					$response = false;
					$last_id = false;

					$RECORD_STAT = db::server($Datas_rec, $sql, $response, $last_id);

					// add 1 day
					$past_date->add(new DateInterval("P1D"));

			}
			// end while
	}
	/**
	 * ! FOR TESTING !
	 */




}
// END class stats::




?>
