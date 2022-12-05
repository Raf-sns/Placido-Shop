<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 process.php
 *
 * process::get_one_customer( $customer_id ); // used ?
 * process::use_compressed();
 * process::compress_js_css();
 * process::concat_ressources( $for );
 * process::minify_CSS();
 *
 */


class process {


  /**
   * process::get_one_customer( $customer_id );
   *
   * @param  {type} $customer_id description
   * @return {type}              description
   */
  public static function get_one_customer( $customer_id ){

      $ARR_pdo = array('id' => (int) $customer_id);
      $sql = 'SELECT * FROM customers WHERE id=:id';
      $response = 'one';
      $last_id = false;
      //  ->  fetch
      $ONE_CUSTOMER = db::server($ARR_pdo, $sql, $response, $last_id);

      // parse tel ...
      // remove all spaces returns tabs, ...
      $tel = trim($ONE_CUSTOMER['tel']);
      $regex = '/(\r\n|\n|\t|\r|\s| ){1,}/';
      $replacement = ""; // !! ONLY "" ARE INTERPRETED
      $tel_parsed = preg_replace($regex, $replacement, $tel);
      // trasnform in array
      $ARR_tel_pre = str_split( $tel_parsed, 1 );
      // reverse array -> walk from the end, last char of tel
      $ARR_tel = array_reverse($ARR_tel_pre);
      // var_dump($ARR_tel);
      // index for get pairs of numbers and insert space
      $indx = 0;
      $pre_tel = ''; // string tel for retunr

      $count = count($ARR_tel); // count array for loop

      // concat tel number wll formated
      for( $i=0; $i < $count; $i++ ){

        $indx++;

        // serach for modulo
        $pre_tel = ( $indx%2 == 0 ) ?
        ' '.$ARR_tel[$i].$pre_tel : $ARR_tel[$i].$pre_tel;

        // var_dump( $ARR_tel[$i] );
      }
      // end for

      // var_dump( $pre_tel );

      $ONE_CUSTOMER['tel'] = $pre_tel;

      return $ONE_CUSTOMER;

  }
  /**
   * process::get_one_customer( $customer_id );
   */



	//////////////////////////////////////////////
	/////////////   COMPRESSION   ////////////////
	//////////////////////////////////////////////



	/**
	 * process::use_compressed();
	 *
	 * @return {type}  description
	 */
	public static function use_compressed(){


			// verify token
			$token = trim(htmlspecialchars($_POST['token']));
			program::verify_token( $token );

			// use must be "true" OR "false" in string
			$use = trim(htmlspecialchars($_POST['use']));

			// test
			if( $use != 'yes' && $use != 'no' ){

					// error
	        $tab = array('error' => tr::$TR['bad_context'] );
	        echo json_encode($tab);
	        exit;
			}

			// pass $use as boolean
			$use = ( $use == 'yes' ) ? true : false;

			// this return an array of json API settings ref. -> API/api.json
			$API_SETTINGS = settings::set_settings_api( array('COMPRESSED' => $use ) );

			// tab to return
			$tab = array(
				'success' => true,
				'api_settings' => $API_SETTINGS
			);

			// return json
			echo json_encode( $tab, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * process::use_compressed();
	 */



	/**
	 * process::compress_js_css();
	 *
	 * @return {type}  description
	 */
	public static function compress_js_css(){


      // verify token
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token( $token );

			// REQUIRE CONTS ARRAYS PATH TO RESSOURCES
			require ROOT.'/API/files_to_compress.php';

			$common_stamp = time();

			array_map('unlink', glob( ROOT.'/JS/*-api.min.js') );

			//// TEST MINIFIER - OK MINIFY ALL IN JS/API.JS
			$GET_JS = process::concat_ressources( 'js' );

			// require JShrink
			require_once ROOT.'/PHP/LIBS/MINIFIER-JS/Minifier.php';

			// COMPOSE $JS_API_min
			$JS_API_min = JShrink\Minifier::minify($GET_JS, array('flaggedComments' => false) );

			// REWRITE FILE JS/api.js
			file_put_contents( ROOT.'/JS/'.$common_stamp.'-api.min.js',  $JS_API_min );

			// minify_CSS edit './CSS/[stamp-last-update]-api.min.css'
			process::minify_CSS( $common_stamp );

			// last compress date SET A HUMAN READABLE DATE
			$Date_Obj = new DateTime('now', new DateTimeZone(TIMEZONE));
			$Date_Obj->setTimestamp($common_stamp);
			$date_update_compress =
				tools::format_date_locale( $Date_Obj, 'FULL' , 'SHORT', null );

			// this return an array of json API settings ref. -> API/api.json
			$API_SETTINGS = settings::set_settings_api(
				array(	'COMPRESSED_STAMP' => $common_stamp,
								'COMPRESSED_DATE' => ucfirst($date_update_compress) )
			);

			// tab to return
			$tab = array(
				'success' => true,
		 		'api_settings' => $API_SETTINGS
			);

			// return json
			echo json_encode( $tab, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * process::compress_js_css();
	 */



  /**
   * process::concat_ressources( $for );
   *
   * @param  {str} $for 'js' / 'css'
   * @return {type}      description
   */
  public static function concat_ressources( $for ){

    // GET JS RESSOURCES
    if( $for == 'js' ){

        // PUT HERE RESSOURCES JS YOU WANT TO COMPRESS with COMPRESSED MODE ENABLED
        $ARR_js_paths = JS_RESSOURCES_TO_COMPRESS;
        // end $ARR_js_paths

        // prepare a loonng string of code ...
        $JS_CONCAT = '';

        foreach( $ARR_js_paths as $key => $val ){

            // concat all js
            $JS_CONCAT .= file_get_contents($val);
        }
        // end foreach

        return $JS_CONCAT;

    }
    // end if( $for == 'js'


    // GET CSS RESSOURCES
    if( $for == 'css' ){

        // PUT HERE RESSOURCES CSS YOU WANT TO COMPRESS with COMPRESSED MODE ENABLED
        $ARR_css_paths = CSS_RESSOURCES_TO_COMPRESS;

        // prepare a loonng string of code ...
        $CSS_CONCAT = '';

        foreach( $ARR_css_paths as $key => $val ){

            // concat all js
            $CSS_CONCAT .= file_get_contents($val);
        }
        // end foreach

        return $CSS_CONCAT;

    }
    // end if( $for == 'css'

  }
  /**
   * process::concat_ressources( $for );
   */



	/**
	 * process::minify_CSS( $common_stamp );
	 *
	 * @return {str}  all css in a string
	 */
	public static function minify_CSS( $common_stamp ){

		array_map('unlink', glob( ROOT.'/CSS/*-api.min.css' ) );

		$GET_CSS = process::concat_ressources( 'css' );

		$contenu = $GET_CSS;

		// note : preg_replace only "" are interpreted !not->''
		$contenu = preg_replace(",/\*(\*|[^!].*\*)/,Ums", "", $contenu);
		$contenu = preg_replace(",\s//[^\n]*\n,Ums", "", $contenu);
		// espaces autour des retour lignes
		$contenu = str_replace("\r\n", "\n", $contenu);
		$contenu = preg_replace(",\s+\n,ms", "\n", $contenu);
		$contenu = preg_replace(",\n\s+,ms", "\n", $contenu);
		// pas d'espaces consecutifs
		$contenu = preg_replace(",\s(?=\s),Ums", "", $contenu);
		// pas d'espaces avant et apres { ; ,
		$contenu = preg_replace("/\s?({|;|,)\s?/ms", "$1", $contenu);
		// supprimer les espaces devant : sauf si suivi d'une lettre (:after, :first...)
		$contenu = preg_replace("/\s:([^a-z])/ims", ":$1", $contenu);
		// supprimer les espaces apres :
		$contenu = preg_replace("/:\s/ms", ":", $contenu);
		// pas d'espaces devant }
		$contenu = preg_replace("/\s}/ms", "}", $contenu);
		// ni de point virgule sur la derniere declaration
		$contenu = preg_replace("/;}/ms", "}", $contenu);
		// pas d'espace avant !important
		$contenu = preg_replace("/\s!\s?important/ms", "!important", $contenu);
		// passser les codes couleurs en 3 car si possible
		// uniquement si non precedees d'un [="'] ce qui indique qu'on est dans un filter(xx=#?...)
		$contenu =
		preg_replace(";([:\s,(])#([0-9a-f])(\\2)([0-9a-f])(\\4)([0-9a-f])(\\6)(?=[^\w\-]);i", "$1#$2$4$6", $contenu);
		// // remplacer font-weight:bold par font-weight:700
		// $contenu = preg_replace("/font-weight:bold(?!er)/ims", "font-weight:700", $contenu);
		// // remplacer font-weight:normal par font-weight:400
		// $contenu = preg_replace("/font-weight:normal/ims", "font-weight:400", $contenu);
		// si elle est 0, enlever la partie entière des unites decimales
		$contenu = preg_replace("/\b0+\.(\d+em)/ims", ".$1", $contenu);
		// supprimer les declarations vides
		$contenu = preg_replace(",(^|})([^{}]*){},Ums", "$1", $contenu);
		// supprimer l'unité quand la valeur est zéro (sauf pour % car casse les @keyframes cf https://core.spip.net/issues/3128 et sauf pour les chaînes en base64 cf https://core.spip.net/issues/3991)
		$contenu = preg_replace("/([^0-9.]\b0)(em|px|pt|rem|ex|pc|vh|vw|vmin|vmax|cm|mm|in|ch)\b/ms", '$1', $contenu);

		// renommer les couleurs par leurs versions courtes quand c'est possible
		$colors = array(
					'source' => array(
									'black',
									'fuchsia',
									'white',
									'yellow',
									'#800000',
									'#ffa500',
									'#808000',
									'#800080',
									'#008000',
									'#000080',
									'#008080',
									'#c0c0c0',
									'#808080',
									'#f00'
					),
					'replace' => array(
									'#000',
									'#F0F',
									'#FFF',
									'#FF0',
									'maroon',
									'orange',
									'olive',
									'purple',
									'green',
									'navy',
									'teal',
									'silver',
									'gray',
									'red'
					)
		);
		foreach ($colors['source'] as $k => $v) {
					$colors['source'][$k] = ";([:\s,(])" . $v . "(?=[^\w\-]);ms";
					$colors['replace'][$k] = "$1" . $colors['replace'][$k];
		}
		$contenu = preg_replace($colors['source'], $colors['replace'], $contenu);

		// raccourcir les padding qui le peuvent (sur 3 ou 2 valeurs)
		$contenu = preg_replace(",padding:([^\s;}]+)\s([^\s;}]+)\s([^\s;}]+)\s(\\2),ims", "padding:$1 $2 $3", $contenu);
		$contenu = preg_replace(",padding:([^\s;}]+)\s([^\s;}]+)\s(\\1)([;}!]),ims", "padding:$1 $2$4", $contenu);

		// raccourcir les margin qui le peuvent (sur 3 ou 2 valeurs)
		$contenu = preg_replace(",margin:([^\s;}]+)\s([^\s;}]+)\s([^\s;}]+)\s(\\2),ims", "margin:$1 $2 $3", $contenu);
		$contenu = preg_replace(",margin:([^\s;}]+)\s([^\s;}]+)\s(\\1)([;}!]),ims", "margin:$1 $2$4", $contenu);

		$contenu = trim($contenu);

		// REWRITE FILE CSS/api.min.css
		file_put_contents( ROOT.'/CSS/'.$common_stamp.'-api.min.css', $contenu );

	}
	/**
	 * process::minify_CSS();
	 */



}
// END CLASS process::

?>
