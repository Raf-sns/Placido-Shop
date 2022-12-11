<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	program.php
 *
 * program::get_home_page();
 * program::product_request( $prod_id, $ARR );
 * program::cat_request( $ARR );
 * program::page_request( $ARR );
 * program::page_api( $ARR );
 * program::user_access_sale();
 *
 */

class program {



  /**
   * program::get_home_page();
   *
   * @return html home page or a specific page asked
   */
  public static function get_home_page(){

			if (session_status() === PHP_SESSION_NONE){

					// STORE DATAS API IN SESSION FOR AJAX REQUEST
					session_start([
						'name' => 'PLACIDO-SHOP',
						'use_strict_mode' => true,
						'cookie_samesite' => 'Strict',
						'cookie_lifetime' => 30, // 30 sec.
						'gc_maxlifetime' => 30,
						'cookie_secure' => true,
						'cookie_httponly' => true
					]);
			}


      // AJAX REQUEST WHEN SESSION EXIST - SAVE ENERGY
			// this return all datas arrays calculated before on Json format
			// for render an object api aviable
			// in a javaScript console by entering: $.o into the console ( $[dot]o )
      if( isset($_POST['req'])
          && $_POST['req'] == 'ajax'
          && isset($_SESSION['datas'])  ){

            // RENDER JS OBJECT
						header('Content-type: application/json');
            echo json_encode($_SESSION['datas'], JSON_NUMERIC_CHECK);

            // DELETING SESSION - do not deleting the session globally
            // see : https://www.php.net/manual/fr/function.session-destroy.php
            unset($_SESSION['datas']);
            exit;
      }
      // END AJAX REQUEST WHEN SESSION EXIST

			// let's start to construct our api
      // CONSTRUCTION OF $ARR => global array of website
      $ARR = array();

			// somes infos about Placido-Shop
			$ARR['api'] = array(
				'software' => 'Placido-Shop',
				'slogan' => 'Placido-Shop - Online Sale Software',
				'version' => VERSION,
				'author' => 'Raphaël Castello',
				'organization' => 'SNS - Web et Informatique',
				'license' => 'GNU AGPL',
				'note' => 'Make peace in the world - Are we humans?',
				'website' => 'www.placido-shop.com'
			);

      // ADD translation - tr::$TR is a global array of translation see: API/tr.php
			// - this allow to acces translation in JS with $.o.tr.key_for_translation
      $ARR['tr'] = tr::$TR;


			// get infos modules
			$ARR['modules'] = api::list_Modules();


			// website[] is for not visible datas DOM object
      // COMPRESSION infos
      $ARR['website']['compress_prefix'] = COMPRESSED_STAMP;
      $ARR['website']['js_compressed'] = COMPRESSED;
      $ARR['website']['css_compressed'] = COMPRESSED;
      // END COMPRESSION infos
      $ARR['website']['title'] = WEBSITE_TITLE;
      $ARR['website']['description'] = META_DESCR;
      $ARR['website']['host'] = HOST;
      $ARR['website']['logo'] = LOGO;
      $ARR['website']['year'] = date('Y');
      $ARR['website']['version'] = VERSION;
			$ARR['website']['lang'] = LANG_FRONT;
			// locales params JS are almosts same as php, replace back slash
			$ARR['website']['lang_locale'] =
				[ LANG_FRONT, str_replace('_','-', LANG_LOCALE) ];
			$ARR['website']['currency_iso'] = CURRENCY_ISO;

      // CATEGORIES MENU IN HTML
      $ARR['cats_menu'] = cats::get_cats_html();

      // // NEW CONFIG - return $ARR['cats'] - $ARR['shop'] - $ARR['products']
      $SHOP_GEN = shop::get_shop();
      $ARR['cats'] = $SHOP_GEN['cats'];
      $ARR['shop'] = $SHOP_GEN['shop'];
      $ARR['products'] = $SHOP_GEN['products'];

			// GET FEATURED PRODUCTS
			$ARR['featured_products'] =
			featured_products::get_featured_products( $ARR['products'] );

      // BY DEFAULT CART IS EMPTY
      $ARR['cart']['items'] = array();
      $ARR['cart']['nb_articles'] = 0;

      // OG TAGS
      $ARR['og']['title'] = WEBSITE_TITLE;
      $ARR['og']['url'] = 'https://'.HOST.'/';
      $ARR['og']['image'] = 'https://'.HOST.'/Logos/'.LOGO_SN;
      // inline meta og descr
      $ARR['og']['description'] = tools::inline_string( META_DESCR );
      // END OG TAGS


      // PAGINATION - HERE - RETURN -> $ARR['view'] !!
      $ARR = process::return_pagination($ARR, $ARR['products'], NB_FOR_PAGINA);


      // SET VIEW SETTINGS
      $ARR['view']['title'] =  WEBSITE_TITLE;
      $ARR['view']['logo'] = 'https://'.HOST.'/img/Logos/'.LOGO;
      // PAGE CONTEXT DEFAULT
      $ARR['view']['page_context'] = 'home';
      // slider in view
      $ARR['view']['slider'] = SLIDER;
			// add show=true to slider by default
			$ARR['view']['slider']['show'] = true;

      $ARR['view']['prod_slider'] = array(
        'min' => DEF_ARR_SIZES['min'],
        'max' => DEF_ARR_SIZES['max']
      );

			// How to display products
			$ARR['view']['display_products'] = DISPLAY_PRODUCTS;

			$ARR['view']['display_mozaic'] =
				( DISPLAY_PRODUCTS == 'mozaic' ) ? true : false;

			$ARR['view']['display_inline'] =
				( DISPLAY_PRODUCTS == 'inline' ) ? true : false;

      // GET STATIC PAGES
      $ARR['static_pages'] = process::get_static_pages();


      // GET url request  - ONE PRODUCT asked
      // if( isset($_GET['prod_id']) ){
			if( isset( api::$REQ['prod_id'] ) ){

					$ARR = program::product_request( api::$REQ['prod_id'], $ARR );
      }

      // GET url request  - CATEGORY asked
      if( isset( api::$REQ['cat_id'] ) ){

          $ARR = program::cat_request($ARR);
      }

      // GET url request  - STATIC PAGE asked
      if( isset( api::$REQ['page'] ) ){

          $ARR = program::page_request($ARR);
      }

      // api page request
      if( isset( api::$REQ['page_api'] ) ){

          $ARR = program::page_api($ARR);
      }



      // RENDER HTML WITH MUSTACHE
      $m = new Mustache_Engine(
				array(
	        'loader' =>
					new Mustache_Loader_FilesystemLoader(dirname(__DIR__).'/templates/BASE',
        							array('extension' => '.html')),
      	)
			);

      // RENDER PARTIAL STATIC PAGE
      if( isset($ARR['page_static']) ){

				$partial = new Mustache_Engine(array('loader' =>
					new Mustache_Loader_FilesystemLoader(dirname(__DIR__).'/templates/'.$ARR['page_static_folder'].'',
							array('extension' => '.html') ) )
				);

				// render datas on a partial
        $ARR['page_templ'] = $partial->render($ARR['page_static'], $ARR);
      }

			// ! KEEP THINGS IN THIS ORDER

			// render html in base <!DOCTYPE html> file
			echo $m->render('base', $ARR);

			// do the dust for js object -> no need this
      if( isset($ARR['page_templ']) ){

        	unset($ARR['page_templ']);
      }

			// put app data in session to render to js API object ajax request
			$_SESSION["datas"] = $ARR;

      exit;

  }
  /**
   * program::get_home_page();
   */



  /**
   * program::product_request( $prod_id, $ARR );
   *
   * @param  {type} $prod_id description
   * @param  {type} $ARR     description
   * @return {type}          description
   */
  public static function product_request( $prod_id, $ARR ){

      // REQUEST less 1 million length
      if( iconv_strlen( $prod_id ) > 1000001  ){
        exit("Too much looonnng request ...");
      }

      $prod_id = (int) trim(htmlspecialchars($prod_id));
      // var_dump($prod_id);

      // ONE PRODUCT
      $ONE_PRODUCT = array();

      foreach( $ARR['products'] as $k => $v ){

          if( $prod_id == (int) $v['id'] ){

              $ONE_PRODUCT = $v;
              break;
          }
      }


      // var_dump($ONE_PRODUCT);
      // exit;

      $ARR['website']['title'] = $ONE_PRODUCT['title'].' - '.WEBSITE_TITLE;
      $ARR['website']['description'] = tools::cut_string(300, $ONE_PRODUCT['text']);

      // OVERRIDE OG TAGS
      $ARR['og']['title'] = $ONE_PRODUCT['title'].' - '.WEBSITE_TITLE;

      $ARR['og']['url'] =
      'https://'.HOST.'/'.$ONE_PRODUCT['url'].'/product/'.$ONE_PRODUCT['id'];

      $ARR['og']['image'] = ( $ONE_PRODUCT['img_prez'] == false )
      ? 'https://'.HOST.'/img/'.LOGO
      : 'https://'.HOST.'/img/Products/max-'.$ONE_PRODUCT['img_prez'];

      $ARR['og']['description'] = preg_replace('/<br \/>\s{1,}/', ' ', $ONE_PRODUCT['text']);
      $regex = '/(\r\n|\n|\t|\r){1,}(\s){2,}/';
      $replacement = " "; // !! ONLY "" ARE INTERPRETED
      $ARR['og']['description'] = preg_replace($regex, $replacement, $ARR['og']['description']);
      // END OG TAGS

      $ARR['one_prod'] = $ONE_PRODUCT;

      // PAGE CONTEXT
      $ARR['view']['page_context'] = 'single_product';

			// NOT DISPLAY TOP SORT
      $ARR['view']['display_top_sort'] = 'display: none;';

			// add show = false to slider
			$ARR['view']['slider']['show'] = false;

		  // SET UP FOR HISTORY
      $ARR['histo'] = array(
        'page' => 'single_product',
        'id' => $ONE_PRODUCT['id'],
        'url' => $ONE_PRODUCT['url'] );

      // DIRECT PAGE
      $page = 'single_product';
      $ARR['page'] = true;
      $ARR['page_'.$page.''] = true;
			$ARR['page_static_folder'] = 'API';
      $ARR['page_static'] = $page;

      return $ARR;

  }
  /**
   * END program::product_request( $prod_id, $ARR );
   */



  /**
   * program::cat_request( $ARR );
   *
   * @param  {array} $ARR  general array api
   * @return {array} override $ARR
   */
  public static function cat_request( $ARR ){



      // REQUEST less 1 million length
      if( iconv_strlen(api::$REQ['cat_id']) > 1000001 ){

            exit("Too much looonnng request ...");
      }

      // cat asked by url request
      $cat_id_asked = (int) trim(htmlspecialchars(api::$REQ['cat_id']));

      $node_cat = false;

      $CAT;

      // get the cat for see if its a node or a leaf
      foreach ($ARR['cats'] as $k => $v) {

          if( (int) $v['cat_id'] == $cat_id_asked ){

              // its a node ?
              if( (int) $v['br'] - (int) $v['bl'] > 1 ){

                  // pass node to true
                  $node_cat = true;
              }

              // assign $CAT
              $CAT = $v;
              // var_dump( $v );
              break;
          }
      }
      // end get the cat


			// CAT NOT FOUND !
			if( empty($CAT) ){

					// -> redirect to 404
					header('Location: https://'.HOST.'/404.php');
					exit;
			}

			// get cat title
			$cat_title = $CAT['title'];

			// set category url
			$url = tools::suppr_accents($CAT['title'], $encoding='utf-8');

      // prepa.an array for pagination
      $Arr_prods_by_cat = [];

      // empty array of products by categories
      foreach ($ARR['products'] as $k => $v) {

          if( (int) $v['cat_id'] == $cat_id_asked
          || $node_cat == true
            && (int) $CAT['bl'] < (int) $v['cat_bl']
            && (int) $CAT['br'] > (int) $v['cat_br'] ){

              $Arr_prods_by_cat[] = $v;
          }
      }
      // end foreach

      // var_dump( $Arr_prods_by_cat );

      // PAGINATION - FOR ALL SHOP FIRST DEFAULT PAGE
      $ARR = process::return_pagination($ARR, $Arr_prods_by_cat, NB_FOR_PAGINA);

      // pass Arr page to false for obtain the home template by default
			// -> products_view.html
      $ARR['page'] = false;

      // ovverdie title
      $ARR['website']['title'] = $cat_title.' - '.WEBSITE_TITLE;
      $ARR['view']['title'] = WEBSITE_TITLE;
      // OVERRIDE OG TAGS
      $ARR['og']['title'] = $cat_title.' - '.WEBSITE_TITLE;
      $ARR['og']['url'] = 'https://'.HOST.'/'.$url.'/category/'.$cat_id_asked;
      $ARR['og']['description'] = $cat_title.'. '.$ARR['og']['description'];
      // cat name
      $ARR['view']['cat_name'] = $cat_title;

      // pass cat to page_context for sorting in js AND HISTORY START CAT PAGE
      $ARR['view']['page_context'] = 'cat';

      // add a display block to cat render ! DISPLAY CAT BLOCK
      $ARR['view']['cat_block_display'] = 'display: block;';

      // SET UP FOR HISTORY
      $ARR['histo'] = array(
				'cat_id' => $cat_id_asked,
				'cat_url' => $url
			);

      return $ARR;

  }
  /**
   * program::cat_request( $ARR );
   */



  /**
   *  program::page_request( $ARR );
   *  IF STATIC PAGE ASKED
   *
   * @param  {array} $ARR     api array
   * @return {array} $ARR     api array for one static page
   */
  public static function page_request( $ARR ){


      // PAGE ASKED - without ".html"
      $page = trim(htmlspecialchars(api::$REQ['page']));

      // TOO LOONNG REQUEST
      if( iconv_strlen(trim(htmlspecialchars(api::$REQ['page']))) > 500 ){

          // -> redirect to 404
          header('Location: https://'.HOST.'/404.php');
          exit;
      }


      // empty static pages array
      if( empty($ARR['static_pages']) ){

          // -> redirect to 404
          header('Location: https://'.HOST.'/404.php');
          exit;
      }

      // AUTHORIZED PAGES !
      $page_found = false; // watcher for page found / not found

      // loop over permitted pages
      foreach( $ARR['static_pages'] as $k => $v ){

          // if key page exist $k -> page_url
          if( $k == $page ){

              // yeah a static page is found
              $page_found = true;
              break; // break here
          }
      }
      // end loop

      // if page NOT in array -> EXIT -> 404
      if( $page_found == false ){

          // -> redirect to 404
          header('Location: https://'.HOST.'/404.php');
          exit;
      }
      // END AUTHORIZED PAGES !


      // OG TAGS FOR STATIC PAGES
      $ARR['og']['title'] = WEBSITE_TITLE.' - '.$v['page_title'];
			// for browser urls
			$ARR['website']['title'] = $v['page_title'].' - '.WEBSITE_TITLE;
      $ARR['og']['url'] = 'https://'.HOST.'/'.$page.'.html';
      $ARR['og']['image'] = 'https://'.HOST.'/img/'.LOGO;
      $ARR['og']['description'] = META_DESCR;
      $regex = '/(\r\n|\n|\t|\r){1,}(\s){2,}/';
      $replacement = " "; // !! ONLY "" ARE INTERPRETED
      $ARR['og']['description'] = preg_replace($regex, $replacement, $ARR['og']['description']);
      // END OG TAGS FOR STATIC PAGES

      // PAGE CONTEXT
      $ARR['view']['page_context'] = 'page';

      // NOT DISPLAY TOP SORT
      $ARR['view']['display_top_sort'] = 'display: none;';

			// add show = false to slider
			$ARR['view']['slider']['show'] = false;


      $ARR['histo'] = array(
        'page' => $page,
        'url' => $page.'.html' );

      // DIRECT PAGE
      $ARR['page'] = true;
			$ARR['page_static_folder'] = 'STATIC_PAGES';
      $ARR['page_static'] = $page; // template asked

      unset($_GET);

      return $ARR;

  }
  /**
   *  program::page_request( $ARR );
   */



  /**
   * program::page_api( $ARR );
   *
   * @param  {array} $ARR   array of api datas
   * @return {array}        array api datas for specific api static page
   */
  public static function page_api( $ARR ){


      // TOO LOONNG REQUEST - in first
      if( iconv_strlen(trim(htmlspecialchars(api::$REQ['page_api']))) > 500 ){

	        // -> redirect to 404
	        header('Location: https://'.HOST.'/404.php');
	        exit;
      }

      // PAGE API ASKED by GET method ->  'cart' / 'sale'
      $page_api = trim(htmlspecialchars(api::$REQ['page_api']));

      // PAGE api
      switch( $page_api ){


        // CART access by url
        case 'cart':

          $ARR['view']['page_context'] = 'cart';

					// for browser urls
					$ARR['website']['title'] = WEBSITE_TITLE.' - '.tr::$TR['cart_url'];

          $ARR['histo'] = array(
            'page' => $page_api, // 'cart'
            'url' => '/cart/'.tr::$TR['cart_url'] .''	);

					$ARR['page_static_folder'] = 'API';
          $ARR['page_static'] = 'payment_form'; // template asked

        break;
				// END CART

        // SALE access by url
        case 'sale':

          $sale_id = (int) trim(htmlspecialchars(api::$REQ['sale_id']));
          $hash_customer = (string) trim(htmlspecialchars(api::$REQ['hash_customer']));

          // if isset $_SESSION['render_sale']
					// -> render direct session without login while session exist
					// - Exit if time() session is over 60 sec.
          if( isset($_SESSION['render_sale']) &&
							$_SESSION['render_sale_stamp'] > (time()-60) ){


							// get sale array()
              $SALE = $_SESSION['render_sale'];

              $SALE['origin_session'] = true;

              // render the sale object
              $ARR['SALE'] = $SALE;

              // set partial template for center page
							$ARR['page_static_folder'] = 'API';
              $ARR['page_static'] = 'render_sale';

              // page context for set history in api_loader.js
              $ARR['view']['page_context'] = 'sale';

							// for browser urls
							$ARR['website']['title'] = WEBSITE_TITLE.' - '.tr::$TR['your_order'];

              // set histo for api_loader.js - keep 2 versions for context for future
              $ARR['histo'] = array(
                'page' => 'sale',
                'sale_id' => $sale_id,
                'hash_customer' => $hash_customer,
                'url' => '/sale/'.$sale_id.'/'.$hash_customer );


          }
          else{

              // ASK FOR LOGIN RENDER SALE

              // set partial template for center page
							$ARR['page_static_folder'] = 'API';
              $ARR['page_static'] = 'render_sale_login';
              $ARR['view']['page_context'] = 'sale';


              // pass histo - keep 2 versions for context for future
              $ARR['histo'] = array(
                'page' => 'sale',
                'sale_id' => $sale_id,
                'hash_customer' => $hash_customer,
                'url' => '/sale/'.$sale_id.'/'.$hash_customer );

          }

        break;
				// END SALE

				// 404 - KEEP IT !
        case '404':

					// PAGE CONTEXT
		      $ARR['view']['page_context'] = 'page';

		      // NOT DISPLAY TOP SORT
		      $ARR['view']['display_top_sort'] = 'display: none;';

					// set partial template for center page
					$ARR['page_static_folder'] = 'API';
					$ARR['page_static'] = '404';

					// pass histo
					$ARR['histo'] = array(
						'page' => '404',
						'url' => '/' );

        break;
				// END 404

        default:
          exit('Bad request ...');
        break;
      }
      // END SWITCH PAGE api


      // NOT DISPLAY TOP SORT
      $ARR['view']['display_top_sort'] = 'display: none;';

			// add show = false to slider
			$ARR['view']['slider']['show'] = false;

      // DIRECT PAGE
      $ARR['page'] = true;


      // return only object api in JSON
      if( isset($_GET['req'])
      && trim(htmlspecialchars($_GET['req'])) == 'ajax' ){

          echo json_encode( $ARR, JSON_NUMERIC_CHECK );
          unset($_GET);
          exit;
      }

      unset($_GET);
      return $ARR;
  }
  /**
   * program::page_api( $ARR );
   */



  /**
   * program::user_access_sale();
   *
   * @return {array}  new sale + hash customer
   */
  public static function user_access_sale(){


      // MANAGE TOO MUCH POSTS
      session_start([
          'name' => 'PLACIDO-SHOP',
          'use_strict_mode' => true,
          'cookie_samesite' => 'Strict',
          'cookie_lifetime' => 3600, // 1 hour.
          'gc_maxlifetime' => 3600,
          'cookie_secure' => true,
          'cookie_httponly' => true
      ]);

      // put a session to not repost multiple
      if( !isset($_SESSION['nb_user_access_sale']) ){

          $_SESSION['nb_user_access_sale'] = 1;
      }
      else{

          $_SESSION['nb_user_access_sale']++;
      }

      // var_dump($_SESSION['nb_post_contact']);

      if( isset($_SESSION['nb_user_access_sale']) && $_SESSION['nb_user_access_sale'] > 10 ){

          // ERROR
          $tab = array('error' => tr::$TR['quota_requests_exceeded'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


      // MAIL
      $mail = (string) trim(htmlspecialchars($_POST['mail']));

      // EMPTY
      if( empty($mail) ){

	        $tab = array( 'error' => tr::$TR['empty_mail'] );
	        echo json_encode($tab, JSON_FORCE_OBJECT);
	        exit;
      }

      // too long request
      if( iconv_strlen($mail) > 100 ){

	        $tab = array( 'error' => tr::$TR['too_large_mail'] );
	        echo json_encode($tab, JSON_FORCE_OBJECT);
	        exit;
      }

      // IF BAD FORMAT
      if( filter_var($mail, FILTER_VALIDATE_EMAIL) == false ){

          $tab = array( 'error' => tr::$TR['invalid_mail'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // pass in fliter
      $mail = filter_var($mail, FILTER_VALIDATE_EMAIL);
      // end  MAIL


      // SALE ID
      $comm_number = (string) trim(htmlspecialchars($_POST['comm_number']));

      // EMPTY
      if( empty($comm_number) ){

        $tab = array( 'error' => tr::$TR['empty_command_number'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

      }

      // too long request
      if( iconv_strlen($comm_number) > 100 ){

        $tab = array( 'error' => tr::$TR['too_large_command_number'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
      }

      // IF BAD FORMAT
      if( filter_var($comm_number, FILTER_VALIDATE_INT) == false ){

          $tab = array( 'error' => tr::$TR['invalid_command_number'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // pass to int
      $sale_id = (int) $comm_number;
      // end SALE ID

			// make an array sale to return
			$SALE = array();

			// GET class new_sales
			require_once dirname(__DIR__).'/'.ADMIN_FOLDER.'/PHP/new_sales.php';

			// GET class archives
			require_once dirname(__DIR__).'/'.ADMIN_FOLDER.'/PHP/archives.php';

			// GET ONE ARCHIVE - return false if not found
			$ARCH = archives::get_ONE_archive( $sale_id );

			if( $ARCH != false ){

					// authorize 1 month view from archives
					$TimeZone = new DateTimeZone(TIMEZONE);

					// Object Date of archive in 'Y-m-d'
					$Date_db =
					DateTime::createFromFormat( 'Y-m-d', $ARCH['date_db'], $TimeZone );
					$Date_db->setTime(0,0,0);

					// Object Date now
					$Date_now = new DateTime( 'now', $TimeZone );
					$Date_now->setTime(0,0,0);

					// calcul interval
					$interval = date_diff($Date_db, $Date_now);

					// sale is archived more than 31 days
					// return message info processed
					if( $interval->days > 31 ){

							// render a message of information
							$tab = array( 'info' => tr::$TR['order_well_processed'] );
							echo json_encode($tab, JSON_FORCE_OBJECT);
							exit;

					}
					else{
					// return sale from archive

							// set good key
							$ARCH['products_settings'] = $ARCH['archived_products'];
							$ARCH['customer_settings'] = $ARCH['customer'];
							$ARCH['amount_text'] = $ARCH['total_amount_sale'];

							// delete bad key
							unset($ARCH['archived_products']);
							unset($ARCH['customer']);
							unset($ARCH['total_amount_sale']);

							// assign archive at sale
							$SALE = $ARCH;
					}
					// return sale from archive

			}
			// end if $ARCH != false

			// var_export( $SALE );

			// if an archive is not found
			// -> search sale in new sale
			if( empty($SALE) ){

					// - exit if not found
					$SALE = new_sales::get_ONE_sale( $sale_id );
			}

      // verify hash customer
      $hash_customer_server =
      api::api_crypt( $SALE['customer_settings']['mail'].$SALE['sale_id'] , 'encr');

      $hash_customer =
      api::api_crypt( $mail.$sale_id , 'encr');

      if( $hash_customer_server != $hash_customer ){

          $tab = array( 'error' => tr::$TR['sale_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


		  // start a session if not exist
      if( session_status() === PHP_SESSION_NONE ){

          // put sale in session
          session_start([
            'name' => 'PLACIDO-SHOP',
            'use_strict_mode' => true,
            'cookie_samesite' => 'Strict',
            'cookie_lifetime' => 60, // 1 min. by default - no effect if server config
            'gc_maxlifetime' => 60,
            'cookie_secure' => true,
            'cookie_httponly' => true
          ]);
      }

			$_SESSION['render_sale_stamp'] = time();
      $_SESSION['render_sale'] = $SALE;
      // end put sale in session


      // return success
      $tab = array( 'success' => true,
                    'SALE' => $SALE,
                    'hash_customer' => $hash_customer_server );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

  }
  /**
   * program::user_access_sale();
   */




}
// END CLASS program::



?>
