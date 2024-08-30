<?php
/**
 * PLACIDO-SHOP FRAMEWORK - API
 * Copyright © Raphaël Castello, 2018-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	featured_products.php
 *
 * class featured_products::
 *
 * Get featured products for slider + management in back-end
 * featured_products::get_featured_products( $ARR['products'] );
 *
 */

class featured_products {



	/**
	 * featured_products::get_featured_products( $ARR['products'] );
	 *
	 * @param  {array} 	$PRODS - array of products
	 * @return {array}  featured products for the slideshow
	 */
	public static function get_featured_products( $PRODS ){


			// get featureds in DB
			$ARR_pdo = false;
			$sql = 'SELECT * FROM featured_products ORDER BY order_prod ASC';
			$response = 'all';
			$last_id = false;

			// delete all featured_products
			$GET_FEATUREDS = db::server($ARR_pdo, $sql, $response, $last_id);

			// if empty results
			if( empty($GET_FEATUREDS) ){
					// return empty array
					return array();
			}

			// create FEATUREDS ARRAY
			$FEATURED_PRODS = array();

			// loop on featureds registered in DB
			foreach( $GET_FEATUREDS as $k => $v ){

					// search same id in array prods -> filtered by array_columns()
					if( in_array( $v['featured_id'], array_column($PRODS, 'id') ) ){

							// get key of product for insert all his datas in featured_prods[]
							$key = array_search($v['featured_id'], array_column($PRODS, 'id') );

							$FEATURED_PRODS[] = $PRODS[$key];

							// keep first prod IMG in memory to shop immediatly
							// first image slider on load page
							$FEATURED_PRODS[0]['first_in_slider'] = true;
					}

			}

			return $FEATURED_PRODS;

	}
	/**
	 * featured_products::get_featured_products( $ARR['products'] );
	 */



}
// END class featured_products::

?>
