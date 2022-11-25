<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello - 2019, 2021, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	cats.php
 *
 * cats::get_all_categories();
 * cats::get_cats();
 * cats::return_cat_name( $ARR_CAT, $cat_id );
 * cats::insert_cat();
 * cats::update_cat();
 *
 * cats::move_cat();
 * cats::pass_nega( $cat_bl, $cat_br );
 * cats::fill_hole( $cat_bl, $cat_br, $number );
 * cats::create_space( $cible_bl, $cible_br, $cat_bl, $cat_br, $where );
 * cats::re_insert_cat( $cible_bl, $cible_br,
 *                      $cat_bl, $cat_br, $where , $cible_level, $cat_level );
 *
 */

class cats {



  /**
   * cats::get_all_categories();
   *
   * @return {array}  list of cats ordered by bl
   */
  public static function get_all_categories(){

      // prepa. db::server()
      $ARR_pdo = false;
      $sql = 'SELECT * FROM categories ORDER BY bl';
      $response = 'all';
      $last_id = false;

			$ALL_DB_CATS = db::server($ARR_pdo, $sql, $response, $last_id);

			if( empty($ALL_DB_CATS) ){

					// return an empty array
					return array();
			}

			return $ALL_DB_CATS;

  }
  /**
   * cats::get_all_categories();
   *
   * @return {array}  list of cats ordered by bl
   */


  /**
   * cats::get_cats();
   * @return {html} html list categories
   */
  public static function get_cats(){


      // GET ALL CATS
      $CATS = cats::get_all_categories();

      // ul gen
      $LISTING = '<ul class="ul" id="cats">';

      // make an array repair where to store parents nodes
      $ARR_repair = array();

      foreach( $CATS as $k => $v ){

        // add an index
        $CATS[$k]['index'] = $k;
        $v['index'] = $k; // need to add new value to v

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

        $move_CAT = '<br>
        <span class="settings_board">
          <span class="btn gree round small small_btn margin-right"
          onclick="$.move_CAT( event, '.$v['cat_id'].' );">
          <i class="fa-arrows-alt fas"></i>&nbsp; '.tr::$TR['move_cat'].'</span>

          <span class="btn dark-gray round small small_btn margin-right"
          onclick="$.modif_cat( event, '.$v['cat_id'].' );">
          <i class="fa-cog fas"></i>&nbsp; '.tr::$TR['modify'].'</span>

          <span class="btn deep-orange round small small_btn margin-right"
          onclick="$.ask_to_suppr_cat( event, '.$v['cat_id'].' );">
          <i class="fa-trash-alt far"></i>&nbsp; '.tr::$TR['suppr'].'</span>
          <br>
        </span>
        <span class="move_board" style="display: none;">

          <span onclick="$.move_obj( event, '.$v['cat_id'].', \'before\' );"
          class="blue btn items_depla round small small_btn">
            <i class="fas fa-arrow-up"></i>&nbsp; '.tr::$TR['before_cat'].'
          </span>

          <span onclick="$.move_obj( event, '.$v['cat_id'].', \'after\' );"
          class="blue btn items_depla round small small_btn">
            <i class="fas fa-arrow-down"></i>&nbsp; '.tr::$TR['after_cat'].'
          </span>

          <span onclick="$.move_obj( event, '.$v['cat_id'].', \'inside\' );"
          class="blue btn items_depla round small small_btn">
            <i class="fas fa-sign-in-alt"></i>&nbsp; '.tr::$TR['inside_cat'].'
          </span>

          <span onclick="$.move_CAT( event, null );"
          class="btn dark-gray items_depla round small small_btn">
          <i class="fa-ban fas"></i>&nbsp; '.tr::$TR['abort'].'</span>

        </span>';

        // if is a node not open -> pass deploy cat
        if( ( (int) $v['br'] - (int) $v['bl'] ) != 1 ){

            $LISTING .= '
            <div onclick="$.deploy_cat('.$v['cat_id'].', event);"
            class="node cat_container card pointer hover-shadow left-align padding round"
            id="cat_id-'.$v['cat_id'].'">
            <i id="cat_icon-'.$v['cat_id'].'" class="cat_icon fas fa-angle-right"></i>&nbsp;
            <strong>'.$v['title'].'</strong>
            '.$move_CAT.'
            </div>';

        }
        else{

            $LISTING .= '
            <div
            class="cat_container border left-align padding round"
            id="cat_id-'.$v['cat_id'].'">
            <strong>'.$v['title'].'</strong>
            '.$move_CAT.'
            </div>';
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

      if( $count_array > 0 ){

        for ($i=0; $i < $count_array; $i++) {

            $LISTING .= '</ul></li>';
        }

      }

      // close global ol
      $LISTING .= '</ul>';

      // CLEAN CATS HTML
      $regex = '/(\r\n|\n|\t|\r){1,}/';
      $replacement = ""; // !! ONLY "" ARE INTERPRETED
      $LISTING = preg_replace($regex, $replacement, $LISTING);

      $regex = '/(\s){2,}/';
      $replacement = " "; // !! ONLY "" ARE INTERPRETED
      $LISTING = preg_replace($regex, $replacement, $LISTING);

			// SORT APLHABETICALLY CATS TITLES OBJECT
			function cmp($a, $b){
			    return strcmp($a['title'], $b['title']);
			}

      usort($CATS, 'cmp' );

      return array( 'cats' => $CATS, 'cats_html' => $LISTING );

  }
  /**
   * cats::get_cats();
   */



  /**
   * cat::return_cat_name( $ARR_CAT, $cat_id );
   *
   * @param  {array} $ARR_CAT
   * @param  {int} $cat_id
   * @return {str} cat title / OR FALSE if no exist - no category assigned case
   */
  public static function return_cat_name( $ARR_CAT, $cat_id ){


      // loop for search cat title
			if( !empty($ARR_CAT) ){

					foreach( $ARR_CAT as $k => $v ){

							if( (int) $v['cat_id'] == $cat_id ){

									$cat_title = $v['title'];
									break;
							}
					}
			}
			else{

					$cat_title = '';
			}

      return $cat_title;

  }
  /**
   * cat::return_cat_name( $ARR_CAT, $cat_id );
   */



  /**
   * cats::insert_cat();
   *
   * @return {array}  cats_html + cats
   */
  public static function insert_cat() {

      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token( $token );

      $title = trim($_POST['title']);

      if( empty($title) ){

          // json return error
          $tab = array('error' => tr::$TR['empty_title_cat']);
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // update all cat -> we insert in first item
      $ARR_pdo = false;
      $sql = 'UPDATE categories SET br=br+2, bl=bl+2';
      $response = false;
      $last_id = false;

      // return array br => num. str
      $UPDATE_SPACE = db::server($ARR_pdo, $sql, $response, $last_id);

      // INSERT NEW CAT
      $ARR_pdo = array(
        'title' => $title,
        'bl' => 1,
        'br' => 2,
        'level' => 0
      );

      $sql = 'INSERT INTO categories ( title, bl, br, level )
      VALUES ( :title, :bl, :br, :level )';
      $response = false;
      $last_id = true; // return last id
      // insert new cat at start
      $INSERT_CAT = db::server($ARR_pdo, $sql, $response, $last_id);

      if( boolval($INSERT_CAT) == false ){

          // json return error
          $tab = array('error' => tr::$TR['error_insert_cat']);
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }

      // INSERT CATEGORY IN SITEMAP
      $url = tools::suppr_accents($title, $encoding='utf-8');
      // $for = 'article' / 'category'
      tools::add_to_sitemap( (int) $INSERT_CAT, $url, 'category' );

      // SUCCESS
      $CATS = cats::get_cats(); // get fresh datas

      $tab = array(
                    'success' => true,
                    'cats_html' => $CATS['cats_html'],
                    'cats' => $CATS['cats']
                  );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;


  }
  /**
   * cats::insert_cat();
   *
   * @return {array}  cats_html + cats
   */



  /**
   * cats::update_cat();
   *
   * @return {array}  cats_html + cats
   */
  public static function update_cat() {


      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token( $token );

      // context for suppress OR update
      $context = trim(htmlspecialchars($_POST['context']));

      // bad_context
      if( $context != 'modif' && $context != 'suppr' ){

          //  error bad_context
          echo json_encode( array('error' => tr::$TR['bad_context'])
          , JSON_FORCE_OBJECT);
          exit;
      }

      // title cat
      $title = trim($_POST['title']);

      // empty title - Only on modification case
      if( empty($title) && $context != 'suppr' ){

          // json return error
          echo json_encode( array('error' => tr::$TR['empty_title_cat'])
          , JSON_FORCE_OBJECT);
          exit;
      }
      // too long title
      if( iconv_strlen($title) > 1000 ){

          // json return error
          echo json_encode( array('error' => tr::$TR['too_large_title_shop'])
          , JSON_FORCE_OBJECT);
          exit;
      }


      // cat_id - TEST NO CAT ID
      if( empty($_POST['cat_id']) ){

          // json return error
          echo json_encode( array('error' => tr::$TR['error_update_cat'])
          , JSON_FORCE_OBJECT);
          exit;
      }

      // this is used by update and suppr cat
      $cat_id = (int) trim(htmlspecialchars($_POST['cat_id']));

      // MODIFICATION CASE
      if( $context == 'modif' ){


        // update one cat
        $ARR_pdo = array( 'cat_id' => $cat_id, 'title' => $title );
        $sql = 'UPDATE categories SET title=:title WHERE cat_id=:cat_id';
        $response = false;
        $last_id = false;

        // return array br => num. str
        $UPDATE_CAT = db::server($ARR_pdo, $sql, $response, $last_id);

        if( boolval($UPDATE_CAT) == false ){

          // json return error
          $tab = array('error' => tr::$TR['error_update_cat']);
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
        }

        // manage sitemap
        // delete old to sitemap
        tools::suppr_to_sitemap( $cat_id );

        // INSERT CATEGORY IN SITEMAP
        $url = tools::suppr_accents($title, $encoding='utf-8');
        // $for = 'article' / 'category'
        tools::add_to_sitemap( $cat_id, $url, 'category' );

      }
      // END MODIFICATION CASE


      // SUPPRESSION CASE
      if( $context == 'suppr' ){

          // get cat borns
          $ARR_pdo = array( 'cat_id' => $cat_id );
          $sql = 'SELECT * FROM categories WHERE cat_id=:cat_id';
          $response = 'one';
          $last_id = false;
          $CAT = db::server($ARR_pdo, $sql, $response, $last_id);

          // error
          if( boolval($CAT) == false ){

            // json return error
            $tab = array('error' => tr::$TR['error_update_cat']);
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
          }


          // test cat bl / br
          // if it's a node
          if( (int) $CAT['br'] - (int) $CAT['bl'] > 1 ){

              $number = ( (int) $CAT['br'] - (int) $CAT['bl'] ) + 1;
          }
          // if it's a leaf
          if( (int) $CAT['br'] - (int) $CAT['bl'] == 1 ){

              $number = 2;
          }

          // min born to ref.
          $bl_ref = (int) $CAT['bl'];
          $br_ref = (int) $CAT['br'];

          // ok
          // DELETE CAT
          $ARR_pdo = array( 'bl_ref' => $bl_ref, 'br_ref' => $br_ref );
          $sql = 'DELETE FROM categories
          WHERE bl>=:bl_ref AND br<=:br_ref';
          $response = false;
          $last_id = false;

          // delete
          $DELETE_CAT = db::server($ARR_pdo, $sql, $response, $last_id);

          if( boolval($DELETE_CAT) == false ){

            // json return error
            $tab = array('error' => tr::$TR['error_update_cat']);
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
          }


          // UPDATE borns of all cats
          $ARR_pdo = array( 'num' => $number,
          'bl_ref' => $bl_ref, 'br_ref' => $br_ref );
          $sql = 'UPDATE categories SET
          bl=bl-:num WHERE bl>:bl_ref;
          UPDATE categories SET
          br=br-:num WHERE br>:br_ref';
          $response = false;
          $last_id = false;

          // return array br => num. str
          $UPDATE_ALL_CATS = db::server($ARR_pdo, $sql, $response, $last_id);

          if( boolval($UPDATE_ALL_CATS) == false ){

            // json return error
            $tab = array('error' => tr::$TR['error_update_cat']);
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
          }


          // delete to sitemap
          tools::suppr_to_sitemap( $cat_id );

      }
      // SUPPRESSION CASE


      // SUCCESS
      $CATS = cats::get_cats();

      $tab = array(
                    'success' => true,
                    'cats_html' => $CATS['cats_html'],
                    'cats' => $CATS['cats']
                  );

      echo json_encode($tab, JSON_NUMERIC_CHECK);
      exit;

  }
  /**
   * cats::update_cat();
   *
   * @return {array}  cats_html + cats
   */

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
///////  INTERVAL DATA TREE REPRESENTATION  //////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////


  /**
   * Function  cats::move_cat();
   *
   * @param		 $cible_id -> id cat parente
   * @param    $cat_id -> id de la catégorie
   * @param    $where -> id de la catégorie
   * @static
   */
    public static function move_cat() {


        // verify token
        $token = trim(htmlspecialchars($_POST['token']));
        program::verify_token( $token );

        $ARR_cats = json_decode($_POST['ARR_cats'], true);

        // var_dump( $ARR_cats["cat_cible"] );
        // exit;


        // categorie moved
        $CAT = $ARR_cats['cat'];
        $cat_id = (int) $CAT['cat_id'];
        $cat_bl = (int) $CAT['bl'];
        $cat_br = (int) $CAT['br'];
        $cat_level = (int) $CAT['level'];

        // categorie targetted
        $CAT_CIBLE = $ARR_cats['cat_cible'];
        $cible_id = (int) $CAT_CIBLE['cat_id'];
        $cible_bl = (int) $CAT_CIBLE['bl'];
        $cible_br = (int) $CAT_CIBLE['br'];
        $cible_level = (int) $CAT_CIBLE['level'];

        $where = trim(htmlspecialchars($_POST['where']));

        // // TEST IF A CATEGORY CONTAINER IS MOVED INTO HIMSELF
        // // test if cat moved is same as cat cible
        // if( $cible_bl >= $cat_bl && $cible_br <= $cat_br
        // || $CAT_CIBLE['cat_id'] == $CAT['cat_id'] ){
        //
        //     throw new Exception('NO_SENSE_MOVE');
        //     exit;
        // }


        // pass nega -> create hole
        $PASS_NEGA = cats::pass_nega( $cat_bl, $cat_br ); // ok

        // fill the hole
        $number = ($cat_br - $cat_bl) + 1;
        $FILL_HOLE = cats::fill_hole( $cat_bl, $cat_br, $number ); // ok

        //  create space for insert
        $CREATE_SPACE =
        cats::create_space( $cible_bl, $cible_br, $cat_bl, $cat_br, $where ); // ok

        // // re-insert in good place // ok
        $INSERT =
        cats::re_insert_cat( $cible_bl, $cible_br, $cat_bl, $cat_br, $where, $cible_level, $cat_level );


        // return
        $CATS = cats::get_cats();

        $tab = array(
                      'success' => true,
                      'cats_html' => $CATS['cats_html'],
                      'cats' => $CATS['cats']
                    );

        echo json_encode($tab, JSON_NUMERIC_CHECK);
        exit;

    }
  /**
   * END  Function  cats::move_cat( $cible_id, $cat_id, $where );
   */



  /**
   * cats::pass_nega( $cat_bl, $cat_br );
   *
   * @param  {type} $cat_bl description
   * @param  {type} $cat_br description
   * @return {type}         description
   */
  public static function pass_nega( $cat_bl, $cat_br ) {

        $ARR_pdo = array(
          'bl' => $cat_bl,
          'br' => $cat_br,
        );
        $sql = 'UPDATE categories SET bl=bl*-1, br=br*-1
        WHERE  bl>=:bl AND br<=:br';

        $response = false;
        $last_id = false; // last id

        // return array br => num. str
        $PASS_NEGA = db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * cats::pass_nega( $cat_bl, $cat_br );
   */



  /**
   * cats::fill_hole( $cat_bl, $cat_br, $number );
   *
   * @param  {type} $cat_bl description
   * @param  {type} $cat_br description
   * @param  {type} $number description
   * @return {type}         description
   */
  public static function fill_hole( $cat_bl, $cat_br, $number ) {

        // fill br
        $ARR_pdo = array(
          'br' => $cat_br,
          'num' => $number
        );
        $sql = 'UPDATE categories SET br=br-:num WHERE br>:br';

        $response = false;
        $last_id = false; // last id

        // exec.
        $FILL_BR = db::server($ARR_pdo, $sql, $response, $last_id);

        // fill bl
        $ARR_pdo = array(
          'bl' => $cat_bl,
          'num' => $number
        );
        $sql = 'UPDATE categories SET bl=bl-:num WHERE bl>:bl';

        $response = false;
        $last_id = false;

        // exec.
        $FILL_BL = db::server($ARR_pdo, $sql, $response, $last_id);

  }
  /**
   * cats::fill_hole( $cat_bl, $cat_br, $number );
   */



  /**
   * cats::create_space( $cible_bl, $cible_br, $cat_bl, $cat_br, $where );
   *
   * @param  {type} $cible_bl description
   * @param  {type} $cible_br description
   * @param  {type} $cat_bl   description
   * @param  {type} $cat_br   description
   * @param  {type} $where    description
   * @return {type}           description
   */
  public static function create_space( $cible_bl, $cible_br, $cat_bl, $cat_br, $where ){


      $number = ($cat_br - $cat_bl) + 1;

      $response = false;
      $last_id = false;

      // right space
      $ARR_pdo_BR = array(
        'br' => 0, // must defined before
        'num' => $number,
      );

      // left space
      $ARR_pdo_BL = array(
        'bl' => 0,
        'num' => $number,
      );

      if( $where == 'before' ){

          // test if cible is under or upper than cat
          // based on bl for touch internals nodes
          $ARR_pdo_BR['br'] = ( $cible_bl < $cat_bl ) ? $cible_bl : $cible_bl - $number;
          $sql_BR = 'UPDATE categories SET br=br+:num WHERE br>=:br';

          $ARR_pdo_BL['bl'] = ( $cible_bl < $cat_bl ) ? $cible_bl : $cible_bl - $number;
          $sql_BL = 'UPDATE categories SET bl=bl+:num WHERE bl>=:bl';
      }

      if( $where == 'after' ){

          $ARR_pdo_BR['br'] = ( $cible_bl < $cat_bl ) ? $cible_br : $cible_br - $number;
          // if child
          if( $cible_bl < $cat_bl && $cible_br > $cat_br ){
            $ARR_pdo_BR['br'] = $cible_br - $number;
          }
          $sql_BR = 'UPDATE categories SET br=br+:num WHERE br>:br';

          // pass $cible_br here
          $ARR_pdo_BL['bl'] = ( $cible_bl < $cat_bl ) ? $cible_br : $cible_br - $number;
          // if child
          if( $cible_bl < $cat_bl && $cible_br > $cat_br ){
            $ARR_pdo_BL['bl'] = $cible_br - $number;
          }
          $sql_BL = 'UPDATE categories SET bl=bl+:num WHERE bl>:bl';
      }

      if( $where == 'inside' ){

          // inside create space always at the end of cible node
          $ARR_pdo_BR['br'] = ( $cible_bl < $cat_bl )
          ? $cible_br : $cible_br - $number;
          $sql_BR = 'UPDATE categories SET br=br+:num WHERE br>=:br';

          // pass $cible_br here
          $ARR_pdo_BL['bl'] = ( $cible_bl < $cat_bl )
          ? $cible_br : $cible_br - $number;
          $sql_BL = 'UPDATE categories SET bl=bl+:num WHERE bl>:bl';
      }

      // exec.
      $CREATE_SPACE_BR = db::server($ARR_pdo_BR, $sql_BR, $response, $last_id);

      // exec.
      $CREATE_SPACE_BL = db::server($ARR_pdo_BL, $sql_BL, $response, $last_id);

  }
  /**
   * cats::create_space( $cible_bl, $cible_br, $cat_bl, $cat_br, $where );
   */



  /**
   * cats::re_insert_cat( $cible_bl, $cible_br, $cat_bl, $cat_br, $where , $cible_level, $cat_level );
   *
   * @param  {type} $cible_bl    description
   * @param  {type} $cible_br    description
   * @param  {type} $cat_bl      description
   * @param  {type} $cat_br      description
   * @param  {type} $where       description
   * @param  {type} $cible_level description
   * @param  {type} $cat_level   description
   * @return {type}              description
   */
  public static function re_insert_cat( $cible_bl, $cible_br, $cat_bl, $cat_br, $where , $cible_level, $cat_level ) {


      if( $where == 'before' ){

          // move in superior place in tree
          if( $cat_bl > $cible_bl ){

              $number = $cible_bl-$cat_bl;
          }
          // move in inferior place in tree
          if( $cat_bl < $cible_bl ){

              $number = ($cible_bl-1) - $cat_br;
          }

      } // end before

      // after
      if( $where == 'after' ){

          // move in superior place in tree
          if( $cat_bl > $cible_bl && $cat_br > $cible_br ){

              $number = ( $cible_br-$cat_bl ) + 1;
          }
          // move in inferior place in tree
          if( $cat_bl < $cible_bl || $cat_bl > $cible_bl && $cat_br < $cible_br ){

              $pre_num = $cible_br - ( ( $cat_br - $cat_bl ) + 1 );
              $number = ( $pre_num - $cat_bl ) + 1;
          }

      }
      // end after

      // determine level

      $level_number = ( $cible_level - $cat_level );

      // inside
      if( $where == 'inside' ){

          $level_number = $level_number + 1; // add one if inside

          // move in superior place in tree
          if( $cat_bl > $cible_bl ){

              $number = ( $cat_bl - $cible_br ) * -1; // pass nega for retry number to items
          }
          // move in inferior place in tree
          if( $cat_bl < $cible_bl ){

              $pre_num = $cible_br - ( ( $cat_br - $cat_bl ) + 1 );
              $number = ( $pre_num - $cat_bl );
          }

      }
      // end inside

      $ARR_pdo = array(
        'num' => $number,
        'level_nb' => $level_number
      );
      $sql = 'UPDATE categories
      SET br = (br*-1)+:num , bl = (bl*-1)+:num , level = level+:level_nb WHERE bl<0';

      $response = false;
      $last_id = false;

      // replace cat to good place
      $INSERT_CAT = db::server($ARR_pdo, $sql, $response, $last_id);


  }
  /**
   * cats::re_insert_cat( $cible_bl, $cible_br, $cat_bl, $cat_br, $where , $cible_level, $cat_level );
   */




}
// end class cats::

?>
