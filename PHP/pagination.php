<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	pagination.php
 *
 * pagination::return_pagination( $ARR, $ARR_products, $nb_wanted );
 *
 */

class pagination {



  /**
   *  pagination::return_pagination( $ARR, $ARR_products, $nb_wanted );
   *
   * @param  {array}  $ARR          -> GLOBAL ARRAY()
   * @param  {array}  $ARR_products -> array of products on line returned by server
   * @param  {int}    $nb_wanted    -> nb products by page (int)
   * @return {array}  $ARR[]  + [view] + [select_nb_opt]
   */
  public static function return_pagination( $ARR, $ARR_products, $nb_wanted ){


		// count nb products
    $count_products = count($ARR_products);

    // var_dump($count_products);

    // MANAGE  SELECT OPTION VIEW
    $ARR['select_nb_opt'] = array(); // FOR RENDER ARRAY TEMPLATE

    // DEFAULT  ARRAY TO <select> OPTIONS
    $seletable = [1, 2, 3, 4, 5, 10, 15, 20, 25, 50];

    // an new option is inserted ?? -> class page:: [$page == 'get_vendor'] case
    $option_inserted = false;

    // un-bug IF NB_WANTED IS TALLER THAN NB_FOR_PAGINA -> this win !!
    if( $nb_wanted > NB_FOR_PAGINA ){

        $nb_wanted = NB_FOR_PAGINA;
    }

    // if nb products is smaller THAN NB_FOR_PAGINA
    if( $count_products < NB_FOR_PAGINA ){

        $nb_wanted = $count_products;
    }

		$opt_added = false;

    // loop over select
    foreach( $seletable as $i => &$value ){

				// if same item was found -> pass it to selected
        if( $seletable[$i] == $nb_wanted ){

          	$ARR['select_nb_opt'][] =
						array( 'value' => $seletable[$i], 'opt' => 'selected' );

						$opt_added = true;
        }

				// if specific item and lowest of this ite -> insert before
				else if( $seletable[$i] > $nb_wanted && $opt_added == false ){

          // in case of shop have less product than NB_FOR_PAGINA DEFAULT
          $ARR['select_nb_opt'][$i-1] = array( 'value' => $nb_wanted, 'opt' => 'selected' );

          $opt_added = true;

        }
        else{
          // default <select> OPTION false
          $ARR['select_nb_opt'][] = array( 'value' => $seletable[$i], 'opt' => false  );
        }

    }
    // END FOR SELECT OPTION VIEW


    // IF NO PRODUCTS -> !! do it here we need prev. $ARR[datas]
    if( $count_products == 0 ){

        $ARR['view']['products'] = [];

        // DEFINE A NUMBER OF PRODS FOR VIEW BY DEFAULT
        $ARR['view']['def_nb_prods'] = NB_FOR_PAGINA;

        return $ARR;
        // stop here
    }


    // THEN CONTINUE ...


		// calcul pages need
    $pages_need = ceil( ($count_products / $nb_wanted) );

		// make a temp array result
		$ARR['view']['temp'] = $ARR_products;
    $ARR['view']['pages_need'] = $pages_need;
		$ARR['view']['page'] = 1;
    $ARR['view']['nb_wanted'] = $nb_wanted;
    $ARR['view']['products'] = array();
		// DEFINE A NUMBER OF PRODS FOR VIEW BY DEFAULT
		$ARR['view']['def_nb_prods'] = NB_FOR_PAGINA;

		// put first nb products first page
    for( $i=0; $i < $nb_wanted; $i++) {

        $ARR['view']['products'][] = $ARR_products[$i];

    }
    // end for


    return $ARR;

  }
  /**
   * pagination::return_pagination( $ARR, $ARR_products, $nb_wanted );
   */



}
// end class pagination
?>
