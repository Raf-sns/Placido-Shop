<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello , 2019-2021
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	cats.php
 *
 * cats::get_cats_html();
 *
 */

class cats {


  /**
   * Function cats::get_cats_html();
   * return str. html list categories
   */
  public static function get_cats_html(){

      $ARR_pdo = false;

      $sql = 'SELECT * FROM categories ORDER BY bl';

      $response = 'all';
      $last_id = false;

      // GET ALL CATS
      $CATS = db::server($ARR_pdo, $sql, $response, $last_id);

      // ul gen
      $LISTING = '<ul class="ul">';

      // make an array repair where to store parents nodes
      $ARR_repair = array();

      foreach( $CATS as $k => $v ){

        // test nodes level
        // if br item is tallest
        $count_array = count($ARR_repair);

        if( $count_array > 0
          && $ARR_repair[$count_array-1]['br'] < (int) $v['br'] ){

          // loop recursive
          for ( $i=$count_array-1; $i >= 0; $i-- ) {

              // test items ARR_repair by the end of the array
              if( (int) $v['br'] > $ARR_repair[$i]['br'] ){

                  $LISTING .= '</ul></li>';
                  array_pop($ARR_repair);
              }
              else{
                // no need to continue loop if $v['br'] is smaller than item repair
                break;
              }
          }

        }
        // end test nodes level

        // for all add a <li> tag
        // for render bl/br : &nbsp; Cat bl : '.$v['bl'].'	&nbsp; Cat br : '.$v['br'].'
        $LISTING .= '
        <li class="li">';

        // if is a node not open -> pass deploy cat
        if( ( (int) $v['br'] - (int) $v['bl'] ) != 1 ){

            $LISTING .= '
            <button onclick="$.deploy_cat('.$v['cat_id'].', event);"
            class="button btn_deploy">
            <i id="cat_icon-'.$v['cat_id'].'" class="cat_icon fas fa-chevron-right"></i>&nbsp;
            </button>
						<a onclick="$.open_a_cat('.$v['cat_id'].', event);"
						href="/'.tools::suppr_accents($v['title'], $encoding='utf-8').
						'/category/'.$v['cat_id'].'" title="'.$v['title'].'"
						class="button">'.$v['title'].'</a>
            ';

        }
        else{

            $LISTING .= '
						<a onclick="$.open_a_cat('.$v['cat_id'].', event);"
						href="/'.tools::suppr_accents($v['title'], $encoding='utf-8').
						'/category/'.$v['cat_id'].'" title="'.$v['title'].'"
						class="button">'.$v['title'].'</a>
						';
        }

        // if is a node add new ol
        if( ( (int) $v['br'] - (int) $v['bl'] ) != 1 ){

          $LISTING .= '<ul class="ul deploy off" id="deploy_'.$v['cat_id'].'">';
          // store cat bl/br for survey level node
          $ARR_repair[] = array( 'bl' => (int) $v['bl'], 'br' => (int) $v['br'] );
        }
        else {
          // just close leaf
          $LISTING .= '</li>';
        }

      }
      // END FOREACH

      // at the end verify if it rest some items repair and close htmls tags
      $count_array = count($ARR_repair);

			// if rest some tag not closed
      if( $count_array > 0 ){

					// loop close tags
	        for ($i=0; $i < $count_array; $i++) {

	            $LISTING .= '</ul></li>';
	        }
      }

      // close global ol
      $LISTING .= '</ul>';

      return $LISTING;

  }
  /*
   * Function cats::get_cats_html();
   * return str. html list categories
   */


}
// end class cats::
?>
