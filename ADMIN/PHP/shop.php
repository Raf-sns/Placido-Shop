<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	shop.php
 *
 * shop::get_shop();
 * shop::update_shop();
 * shop::delete_img_shop();
 *
 */

class shop {


  /**
   * shop::get_shop();
   *
   * @return {array}  shop datas
   */
  public static function get_shop(){


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

			// SHOP TITLE FROM api.json
			$SHOP['title'] = WEBSITE_TITLE;

			// LOGO SHOP FROM api.json -> logo + logo bill + logo mail
			$SHOP['img'] = LOGO;

      return $SHOP;

  }
  /**
   * shop::get_shop();
   */



  /**
   *  shop::update_shop();
   *
   *  @return {array}  shop array[]
   */
  public static function update_shop(){


      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token( $token );

      // ALL EMPTY
      if( empty($_POST) ){

          // ERROR
          $tab = array('error' => tr::$TR['global_empty_fields']);
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }


      // ADDRESS
      if( empty($_POST['addr']) ){

          // ERROR
          $tab = array('error' => tr::$TR['empty_public_address_shop'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;
      }
      else{

          $addr = trim(htmlspecialchars($_POST['addr']));

          // IF MAX LENGTH
          if( iconv_strlen($addr) > 500 ){

              $tab = array('error' => tr::$TR['too_large_addr_input'] );
              echo json_encode($tab);
              exit;
          }

      }

      // TEL
      if( empty($_POST['tel']) ){

          $tel = ''; // able to not put phone on the website
      }
      else{

          $tel = trim(htmlspecialchars($_POST['tel']));

          // IF MAX LENGTH
          if( iconv_strlen($tel) > 20 ){

              $tab = array('error' => tr::$TR['bad_phone_input'] );
              echo json_encode($tab);
              exit;
          }
      }

      // LEGAL ADDRESS [FOR BILL]
      if( empty($_POST['legal_addr']) ){

          $tab = array('error' => tr::$TR['empty_bill_address_shop'] );
          echo json_encode($tab);
          exit;
      }
      else{

          $legal_addr = trim(htmlspecialchars($_POST['legal_addr']));

          // IF MAX LENGTH
          if( iconv_strlen($legal_addr) > 500 ){

              $tab = array('error' => tr::$TR['too_large_bill_address'] );
              echo json_encode($tab);
              exit;
          }

      }

      // LEGAL MENTIONS [FOR BILL]
      if( empty($_POST['legal_mention']) ){

          $tab = array('error' => tr::$TR['empty_bill_legal_text'] );
          echo json_encode($tab);
          exit;
      }
      else{

          $legal_mention = htmlspecialchars($_POST['legal_mention']);

          // IF MAX LENGTH
          if( iconv_strlen($legal_mention) > 3000 ){
              $tab = array('error' => tr::$TR['too_large_bill_legal_text'] );
              echo json_encode($tab);
              exit;
          }

      }

			// ASK TO ADD 1 IMG AT LEAST
			if( empty($_FILES) ){

					$tab = array('error' => tr::$TR['add_logo_shop_image'] );
					echo json_encode($tab);
					exit;
			}

			// JUST ONE IMG IS REQUIRED
			if( count($_FILES) > 1 ){

					$tab = array('error' => tr::$TR['one_image_logo_required'] );
					echo json_encode($tab);
					exit;
			}

      // DELETE OLD IMG SHOP
      shop::delete_img_shop();

      // MAKE LOGO
      $dir_path = ROOT.'/img/Logos';

      //  -> must add anothers this return 'logo-' + name img logo
      $ARR_sizes = array( 'logo-shop' => 600 );

      // this return array of names imgs prefixed by 'keys' $ARR_sizes
      $NEW_logo = tools::img_recorder( $dir_path, $ARR_sizes );

			// UPDATE LOGO in api.json
			settings::set_settings_api( array( 'LOGO' => 'logo-shop-'.$NEW_logo[0] ) );

      // var_dump( $NEW_logo );

      // MODE if 'sale' -> sale mode | 'catalog'
      $mode = ( trim(htmlspecialchars($_POST['mode']))  == 'sale' ) ? 1 : 0;


      // UPDATE USER_SHOP
      $ARR_pdo = array( 'id' => 0,
                        'addr' => $addr,
                        'tel' => $tel,
                        'legal_addr' => $legal_addr,
                        'legal_mention' => $legal_mention,
                        'mode' => $mode
                      );

      $sql = 'UPDATE user_shop SET
      addr=:addr, tel=:tel, legal_addr=:legal_addr,
			legal_mention=:legal_mention, mode=:mode WHERE id=:id';

      $response = false;
      $last_id = false;

      //  ->  update
      $UPDATE_A_SHOP = db::server($ARR_pdo, $sql, $response, $last_id);

			// error
      if( boolval($UPDATE_A_SHOP) == false ){

					$tab = array('error' => tr::$TR['update_failed'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
      }

			// ! this return shoop with const LOGO already getted -> must renew name logo
			$SHOP = shop::get_shop();
			$SHOP['img'] = 'logo-shop-'.$NEW_logo[0];

			// success
			$tab = array( 'success' => tr::$TR['update_shop_success'],
										'shop' => $SHOP );

			echo json_encode($tab, JSON_NUMERIC_CHECK);
			exit;

  }
  /**
   *  shop::update_shop();
   */



  /**
   * shop::delete_img_shop();
   *
   * @return {bool}  suppr old logo shop_user
   */
  public static function delete_img_shop(){


      $filename = ROOT.'/img/Logos/'.LOGO;

      // ERASE IMG SHOP IN FOLDER Logos
      if( file_exists($filename) ){

          // DELETE OLD IMG SHOP
		      array_map('unlink', glob(ROOT.'/img/Logos/logo-shop*'));
          return true;
      }
      else{

					return false;
      }

   }
  /**
   * shop::delete_img_shop();
   */




}
// end class shop::


?>
