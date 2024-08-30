<?php
/**
 * PLACIDO-SHOP FRAMEWORK - API
 * Copyright © Raphaël Castello, 2022-2023
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	tr.php
 *
 * class tr::
 *
 * DEFINE GLOBAL ARRAY OF TRANSLATION FOR ALL SCRIPTS use:  tr::$TR['key']
 *
 * tr::get_langs_to_tr();
 * tr::init_tr( $context );
 * tr::sanitize_translation_text( $file_tr_raw ); // remove comments
 * tr::get_translations($req="");
 *
 */



/**
 * class tr
 * manage translation API
 */
class tr {



	/**
	 * 	Global array of translation use: tr::$TR['key']
	 */
	public static $TR = array();



  /**
   * tr::get_langs_to_tr(); - description
   *
   * @return {array}  code lang for front and back-office
   */
  public static function get_langs_to_tr(){


      return array( 'front' => LANG_FRONT,
                      'back' => LANG_BACK   );

  }
  /**
   * END tr::get_langs_to_tr();
   */



  /**
   * tr::init_tr( $context );
   *
   * @param  {str} $context 'front' / 'back'
   * define TR   array of lang_keys => values translated
   */
  public static function init_tr( $context ){


      // FRONT TRANSLATION
      if( $context == 'front' ){

          // get file lang FRO FRONT
          $file_front_tr_raw =
          file_get_contents( dirname(__DIR__).'/translate/'.tr::get_langs_to_tr()['front'].'.txt' );

          // REMOVE COMMENTS
          $file_front_tr = tr::sanitize_translation_text( $file_front_tr_raw );

          // explode by '***'
          $FILE_front_tr = explode('***', $file_front_tr);

          $ARR_front_tr = array();

          // empty an array with common translate keys and a specific lang asked as value
          foreach ( $FILE_front_tr as $k => $v ){

              if( $k == 0 ){

                  continue;
              }
              if( $k % 2 == 0 ){

                  $key = trim($FILE_front_tr[$k-1]);
                  $ARR_front_tr[$key] = trim($v);
              }

          }

          // print_r( $ARR_front_tr );

          // add CURRENCY_SIGN - for public front
					$ARR_front_tr['curr_iso'] = CURRENCY_ISO; // in CAPITALS - NEED THIS

					// ASSIGN ARRAY TRANSLATE
					tr::$TR = $ARR_front_tr;

      }
      // END FRONT TRANSLATION


      // BACK OFFICE  TRANSLATION
      if( $context == 'back' ){

					// get code lang to translate back-office
					$lang = tr::get_langs_to_tr()['back'];

          // get file lang FOR BACK OFFICE
          $file_back_tr_raw =
          file_get_contents( '../'.ADMIN_FOLDER.'/translate/'.$lang.'.txt' );

          $file_back_tr = tr::sanitize_translation_text( $file_back_tr_raw );

          $FILE_back_tr = explode('***', $file_back_tr);

          $ARR_back_tr = array();

          foreach ( $FILE_back_tr as $k => $v ){

              if( $k == 0 ){

                  continue;
              }
              if( $k % 2 == 0 ){

                  $key = trim($FILE_back_tr[$k-1]);
                  $ARR_back_tr[$key] = trim($v);
              }

          }

					// add lang - for back-end
					$ARR_back_tr['lang'] = $lang;

          // ASSIGN ARRAY TRANSLATE
					tr::$TR = $ARR_back_tr;

      }
      // END BACK OFFICE  TRANSLATION

  }
  /**
   * END tr::init_tr( $context );
   */



  /**
   * tr::sanitize_translation_text( $file_tr_raw );
   * remove comment '<<< ... >>>' in translate text
   *
   * @return {text}  description
   */
  public static function sanitize_translation_text( $file_tr_raw ){

      // remove comments like : // | remove multilines comments : /*[^*](.|\n)*?*/
      $replacement = " "; // !! ONLY "" ARE INTERPRETED
      $regex_1 = '/(\r\n)+|\n+|\t+|\r+|\s\s+/';
      $regex_2 = '/<<<(\s+)(.*)(\s+)>>>|<<<.*>>>/';
      $file_tr_1 = preg_replace( $regex_2, $replacement, $file_tr_raw );
      $file_tr = preg_replace( $regex_1, $replacement, $file_tr_1 );

      return $file_tr;

  }
  /**
   * END tr::sanitize_translation_text( $file_tr_raw );
   */



	/**
	 * tr::get_translations($req="");
	 *
	 * @param  {string} 			$req : '' or 'json' -> context for return datas
	 * @return {json/array}
	 *  2 arrays -> 'api_lang_FRONT' => []
	 *           -> 'api_lang_BACK' => []
	 */
	public static function get_translations($req){


		$dir_tr_front = ROOT.'/translate/';
		$dir_tr_back = ROOT.'/'.ADMIN_FOLDER.'/translate/';

		$scan_tr_front = array_diff(scandir($dir_tr_front), array('..', '.'));
		$scan_tr_back = array_diff(scandir($dir_tr_back), array('..', '.'));

		$API_LANG_FRONT = array();

		foreach ( $scan_tr_front as $key => $value) {

			$code = str_replace(".txt", "", $value);
			$selected = ( LANG_FRONT == $code ) ? 'selected' : '';
			$API_LANG_FRONT[] = array( 'code' => $code, 'selected' => $selected );
		}


		$API_LANG_BACK = array();

		foreach ( $scan_tr_back as $key => $value) {

			$code = str_replace(".txt", "", $value);
			$selected = ( LANG_BACK == $code ) ? 'selected' : '';
			$API_LANG_BACK[] = array( 'code' => $code, 'selected' => $selected );
		}

		$ARR_return = array(
			'api_lang_FRONT' => $API_LANG_FRONT,
			'api_lang_BACK' => $API_LANG_BACK,
		);

		if( $req == 'json' ){

				echo json_encode( $ARR_return );
				exit;
		}

		// else ...
		// return 2 arrays
		// -> 'api_lang_FRONT' => [ code: 'fr', selected: 'selected' , ... ]
		// -> 'api_lang_BACK' => []
		return $ARR_return;

	}
	/**
	 * tr::get_translations($req="");
	 */



}
// END class tr::

?>
