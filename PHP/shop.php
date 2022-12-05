<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2021-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	shop.php
 *
 * shop::get_shop();
 * shop::get_infos_shop();
 * shop::get_all_products_on_line();
 * shop::get_all_products_imgs();
 * shop::get_all_categories();
 * shop::get_imgs_product( $IMGS, $prod_id );
 * shop::get_cat_product( $CATS, $cat_id );
 *
 */

class shop {


  /**
   * shop::get_shop();
   * @return  $ARR['cats'] - $ARR['vendors'] - $ARR['products']
   */
  public static function get_shop(){

      // MAKE (only) 4 SERVER REQUESTS

      // GET ALL PRODUCTS ON LINE
      // * @return {} id
      // * @return {} cat_id
      // * @return {} title
      // * @return {} text
      // * @return {} ref
      // * @return {} quant
      // * @return {} price
      // * @return {} tax
      // * @return {} date_prod
      // * @return {} url
      // * @return {} on_line
      $PRODUCTS = shop::get_all_products_on_line();

      // GET INFO SHOP
      // * @return {array} user_shop datas
      // * @return {int}  id
      // * @return {str}  title
      // * @return {str}  descr
      // * @return {str}  addr
      // * @return {str}  mail
      // * @return {str}  tel
      // * @return {str}  img
      // * @return {str}  legal_addr
      // * @return {str}  legal_mention
      // * @return {bool} mode
      // * @return {bool} by_money
      // * @return {bool} test_pub_key
      // * @return {bool} test_priv_key
      // * @return {bool} prod_pub_key
      // * @return {bool} prod_priv_key
      $SHOP = shop::get_infos_shop();

      // GET ALL PRODUCTS IMGS
      // * @return {int} id
      // * @return {int} parent_id
      // * @return {str} name
      // * @return {int} order_img
      $IMGS = shop::get_all_products_imgs();

      // GET ALL CATEGORIES
      // * @return {array} categories OR {bool} -> false
      // * @return {int} cat_id
      // * @return {str} title
      // * @return {int} bl - born left
      // * @return {int} br - born right
      // * @return {int} level
      // * @return {str} url
      $CATS = shop::get_all_categories();
      // var_dump( $CATS );

      // MAKE AN ARRAY OF CATS IDs USED
			// - IF A CAT DONT HAVE PRODUCT -> REMOVE CAT LATER
      $CATS_USED = array();


      // FOREACH PRODUCTS
      foreach( $PRODUCTS as $k_prod => &$v_prod ){

          // GET CATEGORY for one product
          $cat_id = (int) $PRODUCTS[$k_prod]['cat_id'];
          $CAT_product = shop::get_cat_product($CATS, $cat_id);

          $PRODUCTS[$k_prod]['cat_name'] = $CAT_product['title'];

          // CAT URL
          $PRODUCTS[$k_prod]['cat_url'] =
          	tools::suppr_accents($CAT_product['title'], $encoding='utf-8');

          // PASS CAT BR  / CAT BL
          $PRODUCTS[$k_prod]['cat_bl'] = $CAT_product['bl'];
          $PRODUCTS[$k_prod]['cat_br'] = $CAT_product['br'];

          // Partial text product
					// - SHORT_TEXT from api.json ( this value must be setted see "settings" )
          $PRODUCTS[$k_prod]['short_text'] =
          	tools::cut_string( SHORT_TEXT , $PRODUCTS[$k_prod]['text']);


          // MODE (online sale true[mode->true] or catalog[mode->false])
          // - neccessit stripe keys (whatever test or prod) to be in sale online mode
          if( $SHOP['mode'] == true ){

              $PRODUCTS[$k_prod]['mode_sale'] = true;
              $PRODUCTS[$k_prod]['mode_catalog'] = false;

              // ENABLE PAYMENT BY CB - test stripe keys
              if( $SHOP['test_pub_key'] == true || $SHOP['prod_pub_key'] == true ){

                  $PRODUCTS[$k_prod]['CB_enabled'] = true;

              }
              else{

                  // if don't have strpie keys and by_money is disabled
                  // !! -> NOT AUTORIZE TO buy anything !! - OVERRIDE [ mode_ ]
                  if( $SHOP['by_money'] == false ){

                      $PRODUCTS[$k_prod]['mode_sale'] = false;
                      $PRODUCTS[$k_prod]['mode_catalog'] = true;
                  }

                  $PRODUCTS[$k_prod]['CB_enabled'] = false;


              } // END ELSE : NOT PAYMENT BY CB

          }
          else{

              $PRODUCTS[$k_prod]['mode_sale'] = false;
              $PRODUCTS[$k_prod]['mode_catalog'] = true;
          }


          // PARSE TEXT PRODUCT
          $PRODUCTS[$k_prod]['text'] = nl2br(trim($PRODUCTS[$k_prod]['text']));


          // GET ALL IMGS FOR ONE PRODUCT
          //  ->  fetch all img for 1 prod.
          $prod_id = (int) $PRODUCTS[$k_prod]['id'];
          $IMGS_PROD = shop::get_imgs_product( $IMGS, $prod_id );


          // LOOP FOR SORT IMG FIRS AND OTHER_IMG FOR TEMPLATE
          foreach( $IMGS_PROD as $k_img => $v_img ){

              if( $k_img == 0 ){

                  $PRODUCTS[$k_prod]['img_prez'] = $IMGS_PROD[$k_img]['name'];
              }
              else{

                  $PRODUCTS[$k_prod]['img'][] = $IMGS_PROD[$k_img];
              }
          }
          // END  GET ALL IMGS PRODUCTs

          // calcul price product
          $p_u = (int) $PRODUCTS[$k_prod]['price']; // int in cent
          $price = $p_u; // keep p_u if no tax

          // is taxed ?
          if( (float) $PRODUCTS[$k_prod]['tax'] != 0 ){


							// same as backend
						  $tax = (float) ( (float) $PRODUCTS[$k_prod]['tax'] / 100 );

							// price_tt in cent
							$price = round(($price + ( $price * $tax )), 0); // cent
							$tax_val = $price - $p_u;

              // tax text render
              $PRODUCTS[$k_prod]['tax_text'] =
								tools::intl_number( (float) $PRODUCTS[$k_prod]['tax'] );

              // tax value w. money sign
              $PRODUCTS[$k_prod]['tax_value'] =
								tools::intl_currency( ($tax_val/100) );

          }

          // FORMAT PRICE
          $PRODUCTS[$k_prod]['price_text'] =
						tools::intl_currency( ($price/100) );

					// FORMAT PRICE 2 - for scheme.org tags - keep '.' as decimal separator
					// -> ! NOT USE tools::intl_currency( $ );
          $PRODUCTS[$k_prod]['price_scheme'] =
          number_format( ($price/100) , 2, '.', ' ');

					// price_tt_cent for sorting
					$PRODUCTS[$k_prod]['price_tt_cent'] = $price;

      }
      // END FOREACH $PRODUCTS


      // SHUFFLE PRODUCTS
      shuffle($PRODUCTS);

			// remove shop id
			unset($SHOP['id']);

      // FINAL RETURN
      return array( 'cats' => $CATS,
                    'shop' => $SHOP,
                    'products' => $PRODUCTS );

  }
  /**
   * shop::get_shop();
   */



  /**
   * shop::get_infos_shop();
   *
   * @return {array} user_shop datas
   * @return {int}  id
   * @return {str}  title
   * @return {str}  descr
   * @return {str}  addr
   * @return {str}  mail
   * @return {str}  tel
   * @return {str}  img
   * @return {str}  legal_addr
   * @return {str}  legal_mention
   * @return {bool} mode
   * @return {bool} by_money
   * @return {bool} test_pub_key
   * @return {bool} test_priv_key
   * @return {bool} prod_pub_key
   * @return {bool} prod_priv_key
   */
  public static function get_infos_shop(){

      // prepa. db::server()
      $ARR_pdo = false;
      $sql = 'SELECT * FROM user_shop';
      $response = 'one';
      $last_id = false;

      $SHOP = db::server($ARR_pdo, $sql, $response, $last_id);

      // TEST IF STRIPE KEYS WAS ENTERED
      // pass keys to boolean, no need true datas here
      $SHOP['test_pub_key'] =
      ( iconv_strlen(trim($SHOP['test_pub_key'])) == 0 ) ? false : true;
      $SHOP['test_priv_key'] =
      ( iconv_strlen(trim($SHOP['test_priv_key'])) == 0 ) ? false : true;

      // production keys
      $SHOP['prod_pub_key'] =
      ( iconv_strlen(trim($SHOP['prod_pub_key'])) == 0 ) ? false : true;
      $SHOP['prod_priv_key'] =
      ( iconv_strlen(trim($SHOP['prod_priv_key'])) == 0 ) ? false : true;

      // pass true / false values
      $SHOP['by_money'] = boolval( $SHOP['by_money'] );
      $SHOP['mode'] = boolval( $SHOP['mode'] );

      return $SHOP;

  }
  /**
   * shop::get_infos_shop();
   */



  /**
   * shop::get_all_products_on_line();
   * @return {array}  array of all products online
   * @return {} id
   * @return {} cat_id
   * @return {} title
   * @return {} text
   * @return {} ref
   * @return {} quant
   * @return {} price
   * @return {} tax
   * @return {} date_prod
   * @return {} url
   * @return {} on_line
   */
  public static function get_all_products_on_line(){


      // GET ALL PRODUCTS
      $ARR_pdo = false;
      $sql = 'SELECT * FROM products WHERE on_line=1 ORDER BY id';
      $response = 'all';
      $last_id = false;

      // -> fetch all products on line
      return  db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * END shop::get_all_products_on_line();
   */



  /**
   * shop::get_all_products_imgs();
   *
   * @return {array}  datas of products imgs ordered by parent_id, order_img
   * @return {int} id
   * @return {int} parent_id
   * @return {str} name
   * @return {int} order_img
   */
  public static function get_all_products_imgs(){


      // GET ALL imgs of a PRODUCT
      $ARR_pdo = false;
      $sql = 'SELECT * FROM products_imgs ORDER BY parent_id, order_img';
      $response = 'all';
      $last_id = false;

      // -> fetch all products_imgs
      return  db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * END shop::get_all_products_imgs();
   */



  /**
   * shop::get_all_categories();
   *
   * @return {array} | {bool}  array of categories or bool = false
   * @return {int} cat_id
   * @return {str} title
   * @return {int} bl - born left
   * @return {int} br - born right
   * @return {int} level
   * @return {str} url
   */
  public static function get_all_categories(){


      // GET ALL CATEGORIES
      $ARR_pdo = false;
      $sql = 'SELECT * FROM categories ORDER BY bl ASC';
      $response = 'all';
      $last_id = false;

      //  ->  fetch all categories
      $ALL_CATS = db::server($ARR_pdo, $sql, $response, $last_id);

      if( count($ALL_CATS) == 0 ){

          // no cats defined
          return false;
      }
      else{

          // add url for each cat
          foreach ($ALL_CATS as $k => $v) {
            $ALL_CATS[$k]['url'] = tools::suppr_accents($v['title'], $encoding='utf-8');
          }

					// - WHY ?
          // SORT ALPHABETICALLY CATEGORIES
          // function cmp($a, $b){
					//
          //     if( $a['title'] == $b['title'] ){
          //         return 0;
          //     }
          //     // strcmp — Comparaison binaire de chaînes Retourne < 0 si str1 est inférieure à str2
          //     return strcmp(
          //       strtolower(tools::suppr_accents($a['title'], $encoding='utf-8'))  ,
          //       strtolower(tools::suppr_accents($b['title'], $encoding='utf-8'))
          //     );
					//
          // }
          // // SORT ALPHABETICALLY CATEGORIES
          // usort($ALL_CATS, "cmp");

          return $ALL_CATS;
      }

  }
  /**
   * shop::get_all_categories();
   */



  /**
   * shop::get_imgs_product( $IMGS, $prod_id );
   *
   * @param  {array}  $IMGS     all imgs products
   * @param  {int}    $prod_id
   * @return {array}  array of all imgs FOR ONE PRODUCT
   */
  public static function get_imgs_product( $IMGS, $prod_id ){


      $ARR_temp_imgs = array();

      foreach ($IMGS as $k => $v) {

          if( (int) $v['parent_id'] ==  (int) $prod_id ){

              // PUSH ALL IMGS W. SAME PROD_PARENT_ID
              $ARR_temp_imgs[] = $IMGS[$k];
          }
      }

      return $ARR_temp_imgs;

  }
  /**
   * shop::get_imgs_product( $IMGS, $prod_id );
   */



  /**
   *  shop::get_cat_product( $CATS, $cat_id );
   *
   * @param  {array}  $CATS   array of categories
   * @param  {int}    $cat_id
   * @return {array}  ONE CATEGORY
   */
  public static function get_cat_product($CATS, $cat_id){

			$CAT = array();

      $count = count($CATS);

      for ($i=0; $i < $count; $i++) {

          if( (int) $CATS[$i]['cat_id'] == (int) $cat_id ){

							$CAT = $CATS[$i];
              break;
          }
      }

			// if no cat return array with empty title cat
			if( empty($CAT) ){

					$CAT = array( 'title' => '' );
			}

      return $CAT;

  }
  /**
   *  shop::get_cat_product( $CATS, $cat_id );
   */



}
// END CLASS shop::



?>
