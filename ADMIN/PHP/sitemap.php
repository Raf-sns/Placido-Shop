<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2023-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	sitemap.php
 * version: 2.0.0
 *
 * sitemap::add_to_sitemap( $id, $url, $for );
 * sitemap::suppr_to_sitemap( $id );
 * sitemap::build_sitemap();
 * sitemap::empty_the_sitemap();
 *
 */

class sitemap {


	/**
   * sitemap::add_to_sitemap( $id, $url, $for );
   *
   * @param  {mixed} 	 $id     product id  OR  category id  OR  null
   * @param  {string}  $url    in url string
   * @param  {string}  $for    'product' / 'category' / 'static_page'
   * @return {void}    add -> product | category | static_page on sitemap
   */
  public static function add_to_sitemap( $id, $url, $for ){


      // id must be null
      $id = $id ?? '';

      // set it here for translate before
			switch ($for) {

					case 'product':
						$location = 'https://'.HOST.'/'.$url.'/product/'.$id;
					break;

					case 'category':
						$location = 'https://'.HOST.'/'.$url.'/category/'.$id;
					break;

					case 'static_page':
						$location = 'https://'.HOST.'/'.$url;
					break;

					default:
						return;
					break;
			}
			// end switch

      $dom = new DOMDocument;
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true; // true : well indented
      $path = ROOT.'/sitemap.xml';
      $dom->load($path);

      // make a date in yyyy-mm-dd format
      $Date = new DateTime('now', new DateTimeZone(TIMEZONE) );
      $date = $Date->format('Y-m-d');

      $ns = 'http://www.sitemaps.org/schemas/sitemap/0.9';

      $urlElt = $dom->createElementNS($ns, 'url');
      $urlElt->appendChild($dom->createElementNS($ns, 'loc', $location));
      $urlElt->appendChild($dom->createElementNS($ns, 'lastmod', $date ));
      $urlElt->appendChild($dom->createElementNS($ns, 'changefreq', 'weekly'));
      $urlElt->appendChild($dom->createElementNS($ns, 'priority', '1.0'));

      $dom->documentElement->appendChild($urlElt);

      // save sitemap.xml
      $dom->save($path);

  }
  /**
   *   END sitemap::add_to_sitemap( $id, $url, $for );
   */



  /**
   * sitemap::suppr_to_sitemap( $param );
   *
   * @param  {mixed} 	$param   product_id, category_id, url of a static page
   * @return {void}   delete an item on the sitemap
   */
  public static function suppr_to_sitemap( $param ){


      // $param must be int OR string
      $param = ( is_numeric($param) ) ? (int) $param : (string) $param;

      // suppression d'une page avec l'id
      $dom = new DOMDocument;
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;  // true : well indented
      $path = ROOT.'/sitemap.xml';
      $dom->load($path);

      // get <urlset> tag
      $ns = 'http://www.sitemaps.org/schemas/sitemap/0.9';

      // loop through <urlset> tag and catch <loc> tag
      foreach( $dom->getElementsByTagNameNS($ns, 'loc') as $locNode ){

          // get text value of <loc> tag e.g.: https://my-shop.com/my-product/product/5
          $nodeValue = $locNode->nodeValue;

          // id is always in last on url, url of static pages too
          $locID = explode('/', $nodeValue );

					// get last array entry
          $end = end($locID);

          // test in end is numeric -> else it's a string
          $Thing = ( is_numeric($end) ) ? (int) $end : (string) $end;

					// $Thing must be an int. or a string url for suppress refs to static pages
          // && test if parentNode is well <url> tag
          if( $Thing === $param
              && $locNode->parentNode->nodeName === 'url' ){

              $dom->documentElement->removeChild($locNode->parentNode);
              break;
          }
      }

      // save sitemap.xml
      $dom->save($path);

  }
  /**
   * sitemap::suppr_to_sitemap( $id );
   */



	/**
	 * sitemap::build_sitemap();
	 *
	 * @return {json}  completely rebuild the sitemap.xml file
	 */
	public static function build_sitemap(){


			// VERIFY token
      token::verify_token();

      // before empty the sitemap.xml file
      sitemap::empty_the_sitemap();

			// GET ONLINE PRODUCTS - return id + url of products ONLINE ONLY
			$ONLINE_PRODS = products::get_online_products();

			// loop products and add them to sitemap
			foreach( $ONLINE_PRODS as $v ){

					try {

							// add products to sitemap with new date
							sitemap::add_to_sitemap( $v['id'], $v['url'], 'product' );
					}
					catch (Exception $e) {

							// error
							$Arr = array( 'error' =>
                tr::$TR['rebuild_sitemap_error'].' - '.$e->getMessage() );
							echo json_encode( $Arr );
							exit;
					}

			}
			// end loop products

			// GET CATEGORIES
			$CATEGORIES = cats::get_all_categories();

			// loop categories and add them to sitemap
			foreach( $CATEGORIES as $v ){

					try {

							// format url of a category
							$url = tools::suppr_accents( $v['title'], $encoding='utf-8' );

							// add categories to sitemap with new date
							sitemap::add_to_sitemap( $v['cat_id'], $url, 'category' );
					}
					catch (Exception $e) {

							// error
							$Arr = array( 'error' =>
                tr::$TR['rebuild_sitemap_error'].' - '.$e->getMessage() );
							echo json_encode( $Arr );
							exit;
					}

			}
			// end loop categories

			// GET STATIC PAGES
			$PAGES = static_pages::get_static_pages();

      // loop static pages and add them to sitemap
			foreach( $PAGES as $v ){

					try {

							// add static pages to sitemap with new date
							sitemap::add_to_sitemap( null, $v['page_url'], 'static_page' );
					}
					catch (Exception $e) {

							// error
							$Arr = array( 'error' =>
                tr::$TR['rebuild_sitemap_error'].' - '.$e->getMessage() );
							echo json_encode( $Arr );
							exit;
					}

			}
			// end loop static pages


			// success
			$Arr = array( 'success' => tr::$TR['rebuild_sitemap_success'] );
			echo json_encode( $Arr );
			exit;

	}
	/**
	 * sitemap::build_sitemap();
	 */



	/**
	 * sitemap::empty_the_sitemap();
	 *
	 * @return {void}  empty the sitemap.xml file
	 */
	public static function empty_the_sitemap(){


      // empty sitemap - no nedd to close <urlset> tag
      $empty_sitemap = '<?xml version="1.0" encoding="UTF-8"?>
      <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>';

      try {
          // write an empty sitemap
          file_put_contents( ROOT.'/sitemap.xml', tools::inline_string($empty_sitemap) );
      }
      catch (Exception $e) {

          // error
          $Arr = array( 'error' =>
            tr::$TR['rebuild_sitemap_error'].' - '.$e->getMessage() );
          echo json_encode( $Arr );
          exit;
      }

  }
	/**
	 * sitemap::empty_the_sitemap();
	 */



}
// end class sitemap
?>
