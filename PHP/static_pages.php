<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: static_pages.php
 * 
 * static_pages::get_static_pages();
 * static_pages::return_static_page();
 *
 */

// class static_pages
class static_pages {


  /**
   * static_pages::get_static_pages();
   *
   * @return {array}  description
   */
  public static function get_static_pages(){


      // GET ALL STATIC PAGES
      $ARR_pdo = false;
      $sql = 'SELECT * FROM static_pages';
      $response = 'all';
      $last_id = false;

      $STATIC_PAGES = db::server($ARR_pdo, $sql, $response, $last_id);

      // empty case return an empty array
      if( empty($STATIC_PAGES) ){

          return array();
      }

      $ARR_return = array();

      foreach( $STATIC_PAGES as $k => $v ){

          $ARR_return[$v['page_url']] =  array( 'url' => $v['page_url'],
	                           										'page_title' => $v['page_title'] );

      }

      // return static pages array
      return $ARR_return;

  }
  /**
   * static_pages::get_static_pages();
   */



  /**
   * static_pages::return_static_page();
   *
   * @return {json}  return a static page
   */
  public static function return_static_page(){


			// data recived
			$template_asked = (string) trim(htmlspecialchars($_POST['page_url']));

			// too long request
			if( iconv_strlen($template_asked) > 500 ){

					$tab = array( 'error' => tr::$TR['error_gen'] );
		      echo json_encode( $tab, JSON_FORCE_OBJECT );
		      exit;
			}

			// path to html static page code
			$path = ROOT.'/templates/STATIC_PAGES/'.$template_asked.'.html';

			// test if page exits
			if( file_exists($path) ){

					$raw_html = file_get_contents($path);
			}
			else{

					// error file not exist
					$tab = array( 'error' => tr::$TR['page_not_found'] );
					echo json_encode( $tab, JSON_FORCE_OBJECT );
					exit;
			}

			// minify
			$html = tools::minify_html( $raw_html );

			// return inlined template
      $tab = array( 'success' => true,
										'template' => $html );

      echo json_encode( $tab );
      exit;

  }
  /**
   * static_pages::return_static_page();
   */



}
// end class static_pages
?>
