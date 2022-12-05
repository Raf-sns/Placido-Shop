<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 products.php
 * Class for manage products
 *
 * process::get_products( $ARR_CAT );
 * products::get_all_imgs_products();
 * products::get_imgs_for_one_product( $prod_id );
 * products::get_pagina( $ARR['products'] );
 * products::delete_all_imgs_product( $id );
 * products::rec_prod();
 * products::suppr_prod();
 * products::set_category_of_product();
 * products::modify_state_product();
 * products::record_slider_settings();
 * products::record_featured_products();
 *
 */

 class products {



  /**
   * process::get_products( $ARR_CAT );
   * @param  {array}    categories array
   * @return {array}    array of products parsed ordered by date_prod ASC
   */
  public static function get_products( $ARR_CAT ){


      // prepa. db::server()
      $ARR_pdo = false;
      $sql = 'SELECT * FROM products ORDER BY date_prod DESC';
      $response = 'all';
      $last_id = false;

      // get all products of an user
      $PRODS = db::server($ARR_pdo, $sql, $response, $last_id);

      // get all imgs products
      $ARR_imgs = products::get_all_imgs_products();

      // set a global dateTime Object -> no need to recreate, just modify timestamp
      $Date_Obj = new DateTime('now', new DateTimeZone(TIMEZONE));

      // loop over products
      foreach( $PRODS as $k => $v ){


          // format date
          $PRODS[$k]['stamp'] = $v['date_prod'];
          // set obj with good timestamp
          $Date_Obj->setTimestamp( (int) $v['date_prod'] );
          $PRODS[$k]['date_prod'] =
						tools::format_date_locale( $Date_Obj, 'SHORT' , 'NONE', null );

					// make a date as DB format
					$PRODS[$k]['date_db_year'] = $Date_Obj->format('Y');
					$PRODS[$k]['date_db_month'] = $Date_Obj->format('m');
					$PRODS[$k]['date_db_day'] = $Date_Obj->format('d');

          // format prices
          if( (int) $PRODS[$k]['price'] != 0 ){

              $price = (int) $PRODS[$k]['price']; // in cent

              // is taxed ?
              if( (int) $PRODS[$k]['tax'] != 0 ){

                  $tax = (float) ( (float)$PRODS[$k]['tax'] / 100 );

                  // price_tt in cent
                  $calcul_tt = $price + ( $price * $tax );
                  $PRODS[$k]['price_tt'] = round($calcul_tt, 0);

                  $PRODS[$k]['price_less_tax'] = $price;

                  // format price_less_tax
                  $PRODS[$k]['price_less_tax_format'] =
										tools::intl_currency( round(($price/100),2) );

                  // format price_less_tax FOR INPUT need '.' separator
                  $PRODS[$k]['price_less_tax_input'] =
                  	str_replace( ',', '.', (string) round(($price/100),2) );

                  $PRODS[$k]['tax_value'] = $PRODS[$k]['price_tt'] - $price;

                  // format here tax value
                  $PRODS[$k]['tax_value_format'] =
										tools::intl_currency( round(($PRODS[$k]['tax_value']/100),2) );

                  // tax input
                  $PRODS[$k]['tax_input'] =
                  	str_replace( ',', '.', $PRODS[$k]['tax'] );

                  // checkeds for template
                  $PRODS[$k]['checked_ht'] = 'checked';
                  $PRODS[$k]['is_taxed'] = true;

              }
              // not taxed
              else{

                  $PRODS[$k]['price_tt'] = $price;

                  // checkeds for template
                  $PRODS[$k]['checked_ttc'] = 'checked';
                  $PRODS[$k]['is_taxed'] = false;

              }

              // format price_tt - pass cents to float real prices
              $PRODS[$k]['price_tt_format'] =
								tools::intl_currency( ($PRODS[$k]['price_tt']/100) );

              // format price_tt for input
              $PRODS[$k]['price_tt_input'] =
              	str_replace( ',', '.', (string) ($PRODS[$k]['price_tt']/100) );


          } // no price case
          else{
              // no price
              $PRODS[$k]['price'] = false;
          }

          // GET CAT NAME
          $cat_id = (int) $PRODS[$k]['cat_id'];

          $PRODS[$k]['cat_name'] = cats::return_cat_name( $ARR_CAT, $cat_id );

          // GET imgs products
          $prod_id = (int) $PRODS[$k]['id'];

          // loop over imgs
          foreach( $ARR_imgs as $k2 => $v2 ){

              // search all imgs for one product
              if( (int) $v2['parent_id'] == $prod_id ){

                  // set img prez
                  if( $v2['order_img'] == 0 ){

                      $ARR_imgs[$k2]['img_prez'] = 'img_prez';

                      $PRODS[$k]['img_prez'] = $v2['name'];
                  }

                  // push imgs array for one product
                  $PRODS[$k]['imgs'][] = $v2;

              }
          }
          // end loop imgs


          // ON LINE / OFF LINE  - set twice for template
          $PRODS[$k]['on_line'] = boolval($PRODS[$k]['on_line']);

      }
      // end loop over products

			// return all products
      return $PRODS;

  }
  /**
   * process::get_products( $ARR_CAT );
   */



  /**
   * products::get_all_imgs_products();
   *
   * @return {array}  array of all products imgs
   */
  public static function get_all_imgs_products(){

      // prepa. db::server()
      $ARR_pdo = false;
      $sql = 'SELECT * FROM products_imgs ORDER BY parent_id ASC, order_img ASC';
      $response = 'all';
      $last_id = false;

      $GET_all_imgs = db::server($ARR_pdo, $sql, $response, $last_id);

      // empty case
      if( empty($GET_all_imgs) ){

          return array(); // empty array ?
      }

      return $GET_all_imgs;

  }
  /**
   * products::get_all_imgs_products();
   */



  /**
   * products::get_imgs_for_one_product( $prod_id );
   *
   * @return {array}  array of all imgs FOR ONE PRODUCT
   */
  public static function get_imgs_for_one_product( $prod_id ){


      // prepa. db::server()
      $ARR_pdo = array( 'parent_id' => $prod_id );

			$sql = 'SELECT * FROM products_imgs
							WHERE parent_id=:parent_id
							ORDER BY order_img ASC';

			$response = 'all';

			$last_id = false;

      $GET_imgs = db::server($ARR_pdo, $sql, $response, $last_id);

      // empty case
      if( empty($GET_imgs) ){

          return array(); // empty array ?
      }

      return $GET_imgs;

  }
  /**
   * products::get_imgs_for_one_product( $prod_id );
   */



  /**
   * products::get_pagina( $ARR['products'] )
   */
  public static function get_pagina( $ARR_products ){

      // empty products
      if( empty($ARR_products) ){
        return array();
      }

      $PAGINA_products = array();

			// un bug if too few products
			$number_to_iterate =
			( count($ARR_products) < NB_FOR_PAGINA_BACKEND )
			? count($ARR_products)
			: NB_FOR_PAGINA_BACKEND;

      for ($i=0; $i < $number_to_iterate; $i++) {

          $PAGINA_products[] = $ARR_products[$i];
      }

      // return first pagination products
      return $PAGINA_products;

  }
  /**
   * products::get_pagina( $ARR['products'] )
   */



  /**
   * products::delete_all_imgs_product( $id );
   *
   * @param  {int} 		$id    				product_id
   * @return {void} delete all imgs for one product
	 * 											on database and on folder
   */
  public static function delete_all_imgs_product( $id ){


			// fetch imgs already registered
			$IMGS_registred = products::get_imgs_for_one_product( $id );

      // delete just old imgs no more used
      foreach( $IMGS_registred as $k => $v ){

          // delete all imgs with prefixers
					array_map('unlink', glob(ROOT.'/img/Products/*-'.$v['name']) );
      }


      // DELETE ALL IMGS OF ONE PRODUCT in data base
      // prepa. db::server()
      $ARR_pdo = array( 'parent_id' => $id );
      $sql = 'DELETE FROM products_imgs WHERE parent_id=:parent_id';
      $response = false;
      $last_id = false;

			// DELETE
      $DELETE = db::server($ARR_pdo, $sql, $response, $last_id);

      // error
      if( boolval($DELETE) == false ){

					// ERROR
          $tab = array( 'error' => tr::$TR['error_delete_old_imgs'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

			// need to return true for test later
			return true;

  }
  /**
   * products::delete_all_imgs_product( $id );
   */



  /**
   * products::rec_prod();
   *
   * Record or Modify a product
   * @return {array}  array of products
   */
  public static function rec_prod(){


    //  VERIFY TOKEN
    $token = trim(htmlspecialchars($_POST['token']));
    program::verify_token($token);


    // no image uploaded
    if( empty($_FILES) || !isset($_FILES) ){

        // json return error
        $tab = array('error' => tr::$TR['no_image_uploaded'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // count nb imgs.
    $count_imgs = count($_FILES['img']['name']);

    // ALERT if NO IMG FAVORITE not if one product was definied
    if( empty($_POST['name_img_first']) && $count_imgs > 1 ){

        // json return error
        $tab = array('error' => tr::$TR['define_first_img'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }


    // CATEGORY
    //  empty
    if( empty($_POST['cat_id']) ){

        $tab = array('error' => tr::$TR['choose_category'] );
        echo json_encode($tab);
        exit;
    }
    else{

        $cat_id = (int) trim(htmlspecialchars($_POST['cat_id']));
    }


    // TITLE PROD
    // empty
    if( empty($_POST['title_prod']) ){

        // json return error
        $tab = array('error' => tr::$TR['empty_title'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }
    else{

        $title_prod =
				(string) trim(htmlspecialchars($_POST['title_prod'],ENT_NOQUOTES));

        if( iconv_strlen($title_prod) > 500 ) {

            // json return error
            $tab = array('error' => tr::$TR['too_large_title'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }
    }


		// REFERENCE PRODUCT
		$ref = (string) trim(htmlspecialchars($_POST['ref']));

		// too large ref
		if( iconv_strlen($ref) > 500 ){

				// json return error
				$tab = array('error' => tr::$TR['too_large_ref_text'] );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;
		}

		// ref. must be empty
		$ref = ( empty($ref) ) ? '' : $ref;


    // TEXT PROD
    // empty
    if( empty($_POST['text_prod']) ){

        // json return error
        $tab = array('error' => tr::$TR['empty_text'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }
    else{

        $text_prod =
				(string) trim(htmlspecialchars($_POST['text_prod'],ENT_NOQUOTES));

        if( iconv_strlen($title_prod) > 5000 ) {

            // json return error
            $tab = array('error' => tr::$TR['too_large_text'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }
    }

    // QUANTITY
    // empty
    if(  $_POST['quant'] != 0 &&  !filter_var( $_POST['quant']
            , FILTER_VALIDATE_INT, array( 'min_range' => 0) )

    ){

        // json return error
        $tab = array('error' => tr::$TR['quant_no_defined'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }
    else{

        $quant = (int) trim(htmlspecialchars($_POST['quant']));

        if( $quant > 99999 ){

            // json return error
            $tab = array('error' => tr::$TR['quant_too_large'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }
    }


    // PRICE
    $tax_mode = trim(htmlspecialchars($_POST['tax_mode']));
    $price = 0; // prev. value to 0 - keep that
    $tax = 0;
    // tax_mode :  no_tax /  w_tax
    // tax_mode PRICE 'w_tax'
    if( empty($tax_mode) ){

          // json return error
          $tab = array('error' => tr::$TR['tax_mode_not_defined'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
    }
    if( $tax_mode == 'w_tax' ){

        // price registered is the price less tax
        $price = (float) trim(htmlspecialchars($_POST['price_less_tax']));
        $price = $price * 100; // in cent

        $tax = (float) trim(htmlspecialchars($_POST['tax']));

    }
    // tax_mode PRICE 'no_tax'
    if( $tax_mode == 'no_tax' ){

        $price = (float) trim(htmlspecialchars($_POST['price_tt']));
        $price = $price * 100; // in cent

        $tax = 0;
    }

		// TOO HIGH PRICE up to 100 billion in cent
		if( $price > 10000000000000 ){

				// json return error
				$tab = array('error' => tr::$TR['price_product_too_high'] );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;
		}


    // date_prod
		$year = ( isset($_POST['year'])
		&& (int) trim(htmlspecialchars($_POST['year'])) >= 2022
		&& (int) trim(htmlspecialchars($_POST['year'])) <= 4002 )
		? (int) trim(htmlspecialchars($_POST['year']))
		: date('Y');

		$month = ( isset($_POST['month'])
		&& (int) trim(htmlspecialchars($_POST['month'])) >= 1
		&& (int) trim(htmlspecialchars($_POST['month'])) <= 12 )
		? (int) trim(htmlspecialchars($_POST['month']))
		: date('m');

		$day = ( isset($_POST['day'])
		&& (int) trim(htmlspecialchars($_POST['day'])) >= 1
		&& (int) trim(htmlspecialchars($_POST['day'])) <= 31 )
		? (int) trim(htmlspecialchars($_POST['day']))
		: date('d');

		$date_string = $year.'-'.$month.'-'.$day;

		$made_a_date_prod = new DateTime($date_string, new DateTimeZone(TIMEZONE) );

		// record timestamp for date product
		$date_prod = $made_a_date_prod->getTimestamp();


    // SET URL PRODUCT
    $url = tools::suppr_accents($title_prod, $encoding='utf-8');


    // IF MODIF ASKED
    if( isset($_POST['modif'])
    && trim(htmlspecialchars($_POST['modif'])) == 'modif'
    && isset($_POST['prod_id']) ){

        // product id
        $id = (int) trim(htmlspecialchars($_POST['prod_id'])); // for pdo next

        // delete all imgs in DATA BASE AND in FOLDER - param. prod_id - BY HIS PARENT
        products::delete_all_imgs_product( $id );


				// RECORD PRODUCT
				// array for PDO
				$ARR_pdo = array(
					'id' => $id,
					'cat_id' => $cat_id,
					'title' => $title_prod,
					'text' => $text_prod,
					'ref' => $ref,
					'quant' => $quant,
					'price' => $price,
					'tax' => $tax,
					'date_prod' => $date_prod,
					'url' => $url
				);

				$sql = 'UPDATE products SET
                cat_id=:cat_id,
                title=:title,
                text=:text,
								ref=:ref,
                quant=:quant,
                price=:price,
                tax=:tax,
								date_prod=:date_prod,
                url=:url
								WHERE id=:id';

        $last_id = false;   // i. FALSE IT IS AN UPDATE


    } // IN FIRST RECORD PRODUCT CASE
    else{

          $id = 0; // for pdo next

					// On off line in first record
					// -> i. product already recorded have a direct server management on/off line
					$on_line = ( trim(htmlspecialchars($_POST['on_off_line'])) == 'on_line' )
					? 1 : 0;

					// RECORD PRODUCT
					// array for PDO
					$ARR_pdo = array( 'id' => $id,
						'cat_id' => $cat_id,
						'title' => $title_prod,
						'text' => $text_prod,
						'ref' => $ref,
						'quant' => $quant,
						'price' => $price,
						'tax' => $tax,
						'date_prod' => $date_prod,
						'url' => $url,
						'on_line' => $on_line
					);

					// REQUEST
          $sql = 'INSERT INTO products
          VALUES( :id,
                  :cat_id,
                  :title,
                  :text,
									:ref,
                  :quant,
                  :price,
                  :tax,
                  :date_prod,
                  :url,
                  :on_line )';

        $last_id = true; // PDO return last id

	  }
    // END IF NO MODIF

    $response = false;

    // SET_PRODUCT - return last id on insert
    $SET_PRODUCT = db::server($ARR_pdo, $sql, $response, $last_id);

    // var_dump($INSERT_PRODUCT);
    if( boolval($SET_PRODUCT) == false ){

        // json return error
        $tab = array('error' => tr::$TR['error_on_set_product'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

		// define here an array to return
		$tab = array();

    if( isset($_POST['modif'])
    && trim(htmlspecialchars($_POST['modif'])) == 'modif'
    && isset($_POST['prod_id']) ){

        // modif context
        // delete to sitemap -> was recorded after
        $prod_id = $id;
        tools::suppr_to_sitemap($id);

				// evently if sucess
				$tab['success'] = tr::$TR['update_success'];
    }
    else{

        // RETURNED PROD. ID by last_id
        $prod_id = (int) $SET_PRODUCT;

				// evently if sucess
				$tab['success'] = tr::$TR['success_record_product'];
    }
    // END  SET_PRODUCT


    // RECORD IN SITEMAP
    tools::add_to_sitemap( $prod_id, $url, 'article' );


    // 2 - RECORD IMAGES
    $dir_path = ROOT.'/img/Products/';
    $ARR_sizes = DEF_ARR_SIZES;
    // return array of str. names imgs uploaded - only unic name not prefixed
		// in template we call "min-" or "max-" prefix for define
		// the different sizes
    $ARR_names_imgs = tools::img_recorder( $dir_path, $ARR_sizes );

    // prepa order imgs
    $order_img = 0;

    // var_dump( $_POST['name_img_first'] );

    $img_first = ( !isset($_POST['name_img_first']) )
    ? $_FILES['img']['name'][0] : trim(htmlspecialchars($_POST['name_img_first']));

		// replace "min-" / "max-" ( for upload case )
    $name_img_first = preg_replace('/(min-)|(max-)/', '', $img_first);

    // FOREACH ARRAY NAMES RETURNED - ADD TO DB
    foreach( $ARR_names_imgs as $k => $name_img ){


        // if img frist is defined
        if( $name_img_first == $_FILES['img']['name'][$k] ){

						// prez img to order 0
            $order_img = 0;
        }
        else{
            // order other imgs
            $order_img++;
        }

        // prep. array for bdd
        $ARR_pdo = array( 'id' => 0, // auto-increment
                          'parent_id' => $prod_id,
                          'name' => $name_img,
                          'order_img' => $order_img
                        );

        $sql = 'INSERT INTO products_imgs VALUES ( :id, :parent_id, :name, :order_img )';

        $response = false;
        $last_id = false; // no return last id

        // for each img -> ONE record
        $RECORD_IMG = db::server($ARR_pdo, $sql, $response, $last_id);

        // ERROR RECORD IMG
        if( boolval($RECORD_IMG) == false ){

            // json return error
            $tab = array('error' => tr::$TR['error_record_db_img'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
        }


    }
    // END FOREACH ARRAY NAMES RETURNED - ADD TO DB

    // END RECORD IMAGES


    // GET PRODUCTs OF ONE USER
    $tab['products'] = products::get_products( cats::get_all_categories() );

    unset($_POST);
    unset($_FILES);

    // response
    echo json_encode($tab, JSON_NUMERIC_CHECK);
    exit;

  }
  /**
   * END products::rec_prod();
   * Record or Modify a product
   * @return {array}  array of products
   */



  /**
   * products::suppr_prod();
   * Delete a product
   * @return {array}  new array of products
   */
  public static function suppr_prod(){


      //  VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);

      $prod_id =  trim(htmlspecialchars($_POST['prod_id']));

      // empty
      if( empty($prod_id)  ){

          // json return error
          $tab = array('error' => tr::$TR['error_on_set_product'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }
      else {

          $prod_id = (int) $prod_id;
      }


			// verify product IS NOT ON A NEW SALE
			$NEW_SALES = new_sales::get_new_sales_user()['new_sales'];

			// make a watcher
			$founded_on_new_sale = false;

			foreach( $NEW_SALES as $k => $v ){
				foreach ($v['products_settings'] as $k2 => $v2) {

						// if product is found on a new sale - NOT ABLE TO SUPPR PRODUCT
						if( $v2['prod_id'] == $prod_id ){

								$founded_on_new_sale = true;
						}
				} // end loop products
			} // end loop new sales

			// IF THE PRODUCT TO DELETE IS ON A NEW SALE - EXIT !
			if( $founded_on_new_sale == true ){

					// json return error
          $tab = array('error' => tr::$TR['error_product_is_on_new_sale'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


      // delete to sitemap -> was recorded after
      tools::suppr_to_sitemap( $prod_id );

      // 1 - DELETE IMGS OF ONE PRODUCT ON FLODER && ON DB
      $DELETE_PRODUCTS_IMGs = products::delete_all_imgs_product( $prod_id );

      // 2 - SUPPR ONE PRODUCT
      $ARR_pdo = array( 'id' => $prod_id );
      $sql = 'DELETE FROM products WHERE id=:id';
      $response = false;
      $last_id = false;

      // delete in table products
      $DELETE_PROD = db::server($ARR_pdo, $sql, $response, $last_id);


      // success
      if( boolval($DELETE_PROD) == true && boolval($DELETE_PRODUCTS_IMGs) == true ){

          // GET PRODUCTs OF ONE USER - pass arr cats for not ask DB
          $tab['products'] = products::get_products( cats::get_all_categories() );

          $tab['success'] = tr::$TR['product_well_suppr'];

          unset($_POST);

          // json return success
          echo json_encode($tab, JSON_NUMERIC_CHECK);
          exit;

      }
      else {
          // error

					unset($_POST);

          // json return error
          $tab = array('error' => tr::$TR['error_on_set_product'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

  }
  /**
   * END products::suppr_prod();
   */



	/**
	 * products::set_category_of_product();
	 *
	 * @return {json}  success / error for record SLIDER settings
	 */
	public static function set_category_of_product(){


			//  VERIFY TOKEN
			$token = (string) trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			// id product
			$prod_id = (int) trim(htmlspecialchars($_POST['prod_id']));

			// cat id
			$cat_id = (int) trim(htmlspecialchars($_POST['cat_id']));

			// GET ONE PRODUCT
			$ARR_pdo = array( 'id' => $prod_id );
      $sql = 'SELECT * FROM products WHERE id=:id';
      $response = 'one';
      $last_id = false;

      // ONE PROD
      $ONE_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

			// product not found
			if( boolval($ONE_PROD) == false ){

					// json return error
          $tab = array('error' => tr::$TR['product_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// GET ALL CATEGORIES
			$ALL_CATS = cats::get_all_categories();

			$new_cat_finded = false;

			// verify category asked exist
			foreach( $ALL_CATS as $k => $v ){

					if( (int) $v['cat_id'] == $cat_id ){

							$new_cat_finded = true;
							break;
					}
			}
			// end foreach

			// if cat not found
			if( $new_cat_finded == false ){

					// json return error
          $tab = array('error' => tr::$TR['cat_not_found'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}


			// update category of one product
			$ARR_pdo = array( 'id' => $prod_id, 'cat_id' => $cat_id );
      $sql = 'UPDATE products SET cat_id=:cat_id WHERE id=:id';
      $response = false;
      $last_id = false;

      // UPDATE CAT PROD
      $UPDATE_CAT_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

			// error update cat prod
			if( boolval($UPDATE_CAT_PROD) == false ){

					// json return error
          $tab = array('error' => tr::$TR['error_on_set_product'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// return success
			$tab = array(
				'success' => tr::$TR['success_update_cat_product'],
				'cat_name' => $ALL_CATS[$k]['title'],
				'cat_id' => $ALL_CATS[$k]['cat_id']
			);

			echo json_encode($tab, JSON_NUMERIC_CHECK);
			exit;

	}
	/**
	 * END products::set_category_of_product();
	 */



	/**
	 * products::modify_state_product();
	 *
	 * @return {json}  success / error for modify state product
	 * on_line / off_line
	 */
	public static function modify_state_product(){


			//  VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			// id product
			$prod_id = (int) trim(htmlspecialchars($_POST['prod_id']));

			// state
			$state = (string) trim(htmlspecialchars($_POST['state']));

			// state error
			if( $state != 'on_line' && $state != 'off_line' ){

					// json return error
          $tab = array('error' => tr::$TR['bad_context'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// set value on_line for DB
			$on_line = ( $state == 'on_line' ) ? 1 : 0;

			$ARR_pdo = array( 'id' => $prod_id, 'on_line' => $on_line );
      $sql = 'UPDATE products SET on_line=:on_line WHERE id=:id';
      $response = false;
      $last_id = false;

      // update state prod
      $UPDATE_STATE_PROD = db::server($ARR_pdo, $sql, $response, $last_id);

			// error
			if( boolval($UPDATE_STATE_PROD) == false ){

					// json return error
          $tab = array('error' => tr::$TR['error_on_set_product'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// success
			$tab = array('success' => tr::$TR['success_modif_state_product'] );
			echo json_encode($tab, JSON_FORCE_OBJECT);
			exit;

	}
  /**
   * END products::modify_state_product();
   */



	/**
	 * products::record_slider_settings();
	 *
	 * @return {json}  success / error for record SLIDER settings
	 */
	public static function record_slider_settings(){


			//  VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
			program::verify_token($token);

			// pass all datas as strings
			$display = (string) trim(htmlspecialchars($_POST['SLIDER-display'])); // 'true'/'false'
			$play = (string) trim(htmlspecialchars($_POST['SLIDER-play'])); // 'true'/'false'
			$delay = (string) trim(htmlspecialchars($_POST['SLIDER-delay'])); // 4000
			$speed = (string) trim(htmlspecialchars($_POST['SLIDER-speed'])); // 800


			// if too long request
			if( iconv_strlen($display) > 10
			|| iconv_strlen($play) > 10
			|| iconv_strlen($delay) > 10
			|| iconv_strlen($speed) > 10 ){

          // json return error
          $tab = array('error' => tr::$TR['error_gen'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

			}
			// end if too long


			// affect good values for json
			$display = ( $display == 'true' ) ? true : false;
			$play = ( $play == 'true' ) ? true : false;

			// test good interval of values
			$delay = (int) $delay;
			if( $delay < 1000 || $delay > 30000 ){

				// json return error
				$tab = array('error' => tr::$TR['error_delay_time'] );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;
			}

			$speed = (int) $speed;
			if( $speed < 100 || $speed > 5000 ){

				// json return error
				$tab = array('error' => tr::$TR['error_speed_time'] );
				echo json_encode($tab, JSON_FORCE_OBJECT);
				exit;
			}

			// set up an array to insert in api.json
			$ARR_SET = array(
				'SLIDER' =>
						array(
							'display' => $display,
							'play' => $play,
							'delay' => $delay,
							'speed' => $speed,
						)
			);

			// this return an array of json API settings ref. -> API/api.json
			$API_SETTINGS = settings::set_settings_api( $ARR_SET );

			// tab to return
			$tab = array(
			'success' => tr::$TR['well_inserted_featured_prods'],
			'api_settings' => $API_SETTINGS
			);

			// return json
			echo json_encode( $tab, JSON_NUMERIC_CHECK );
			exit;

  }
  /**
   * END products::record_slider_settings();
   */



	/**
	 * products::record_featured_products();
	 *
	 * @return {json}  success / error for record featured products
	 */
	public static function record_featured_products(){


			//  VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);

			$LIST_ids = json_decode( $_POST['list_ids'], true );

			// test array
			if( !is_array($LIST_ids) ){
				exit(tr::$TR['bad_context']);
			}

			// verify if empty array -> this is permitted
			$is_empty = ( empty($LIST_ids) ) ? true : false;

			// delete all in db
			$ARR_pdo = false;
			$sql = 'DELETE FROM featured_products';
			$response = false;
			$last_id = false;

			// delete all featured_products
			$DELETE_PRODUCTS_IMGs = db::server($ARR_pdo, $sql, $response, $last_id);

			// if empty case -> exit here
			if( $is_empty == true ){

					// json return success
					$tab = array('success' => tr::$TR['well_inserted_featured_prods'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// prepa. string to concat the request SQL
			$featureds = '';

			// concat sql
			foreach( $LIST_ids as $k => $v ){

				$featureds .= (  $k != (count($LIST_ids)-1)  )
				? '( '.$k.','.(int)$v.' ),' : '( '.$k.','.(int)$v.' )';
			}

			// insert news
			$ARR_pdo = false;
			$sql = 'INSERT INTO featured_products (order_prod, featured_id)
			VALUES '.$featureds.'';
			$response = false;
			$last_id = false;

			// insert news
			$INSERT_NEW_FEATUREDS = db::server($ARR_pdo, $sql, $response, $last_id);

			// error insert in DB
			if( boolval($INSERT_NEW_FEATUREDS) == false ){

					// json return error
          $tab = array('error' => tr::$TR['error_on_insert_featured_prods'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
			}

			// return featureds ?? - no need for record - js obj. is persistant

			// json return success
			$tab = array('success' => tr::$TR['well_inserted_featured_prods'] );
			echo json_encode($tab, JSON_FORCE_OBJECT);
			exit;


	}
	/**
	 * products::record_featured_products();
	 */




 }
 // end class products::


 ?>
