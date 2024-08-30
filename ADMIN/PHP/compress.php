<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	process.php
 *
 * compress::use_compressed();
 * Note : i. -> files to compress are in API/files_to_compress.php
 * compress::compress_js_css();
 * compress::concat_ressources( $for );
 * compress::minify_CSS();
 *
 */

class compress {


	/**
	 * compress::use_compressed();
	 *
	 * @return {type}  description
	 */
	public static function use_compressed(){


			// VERIFY token
			token::verify_token();

			// use must be 1 OR 0
			$use = (int) trim(htmlspecialchars($_POST['use']));

			// test
			if( $use != 1 && $use != 0 ){

					// error
	        $tab = array('error' => tr::$TR['bad_context'] );
	        echo json_encode($tab);
	        exit;
			}

			// this return an array for API/constants.php
			$API_SETTINGS = settings::set_settings_api( array('COMPRESSED' => $use) );

			// array to return
			$tab = array(
				'success' => true,
				'COMPRESSED' => $API_SETTINGS['COMPRESSED']
			);

			// return json
			echo json_encode( $tab, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * compress::use_compressed();
	 */



	/**
	 * compress::compress_js_css();
	 *
	 * @return {type}  description
	 */
	public static function compress_js_css(){


      // VERIFY token
			token::verify_token();

			// retrieve resources constants to compress
			require ROOT.'/API/files_to_compress.php';

			$common_stamp = time();

			array_map('unlink', glob( ROOT.'/JS/*-api.min.js') );

			// MINIFY ALL JS resources IN JS/[timestamp]-api.min.js
			$GET_JS = compress::concat_ressources( 'js' );

			// require JShrink
			require_once ROOT.'/PHP/LIBS/MINIFIER-JS/Minifier.php';

			// COMPOSE $JS_API_min
			$JS_API_min = JShrink\Minifier::minify($GET_JS, array('flaggedComments' => false) );

			// REWRITE FILE JS/api.js
			file_put_contents( ROOT.'/JS/'.$common_stamp.'-api.min.js',  $JS_API_min );

			// minify_CSS edit './CSS/[stamp-last-update]-api.min.css'
			compress::minify_CSS( $common_stamp );

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
		 		'COMPRESSED_STAMP' => $API_SETTINGS['COMPRESSED_STAMP'],
        'COMPRESSED_DATE' => $API_SETTINGS['COMPRESSED_DATE']
			);

			// return json
			echo json_encode( $tab, JSON_NUMERIC_CHECK );
			exit;

	}
	/**
	 * compress::compress_js_css();
	 */



  /**
   * compress::concat_ressources( $for );
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
   * compress::concat_ressources( $for );
   */



	/**
	 * compress::minify_CSS( $common_stamp );
	 *
	 * @return {str}  all css in a string
	 */
	public static function minify_CSS( $common_stamp ){

		array_map('unlink', glob( ROOT.'/CSS/*-api.min.css' ) );

		$GET_CSS = compress::concat_ressources( 'css' );

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
	 * compress::minify_CSS();
	 */



}
// END CLASS compress::

?>
