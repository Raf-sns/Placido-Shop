<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	tools.php
 *
 * tools::suppr_accents($str, $encoding='utf-8');
 * tools::format_in_url( $str );
 * tools::fetch_mail_admin($mail); // used by renew password
 * tools::img_recorder( $dir_path, $ARR_sizes );
 * tools::add_to_sitemap($id, $title, $for);
 * tools::suppr_to_sitemap($id);
 * tools::parse_line_breaks($text);
 * tools::intl_number( $num );
 * tools::intl_currency( $price );
 * tools::format_date_locale( $date, $dateType , $timeType, $pattern );
 * tools::get_locales_settings();
 * tools::get_timezones();
 *
 */

class tools {



	/**
	 * tools::suppr_accents($str, $encoding='utf-8');
	 *
	 * @param  {str} $str     string to transform in url string
	 * @param  {encoding}     'utf-8'
	 * @return {type}         url formatted
	 */
	public static function suppr_accents($str, $encoding='utf-8'){


			$str = trim($str);
			// transformer les caractères accentués en entités HTML
			$str = htmlentities($str, ENT_NOQUOTES, $encoding);

			// remplacer les entités HTML pour avoir juste le premier caractères non accentués
			// Exemple : "&ecute;" => "e", "&Ecute;" => "E", "à" => "a" ...
			$str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);

			// Remplacer les ligatures tel que : , Æ ...
			// Exemple "œ" => "oe"
			$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
			// Supprimer tout le reste
			$str = preg_replace('#&[^;?]+;#', '', $str); // [ ]{2,}
			$str = preg_replace("#['\s]+#", '-', $str);
			$str = preg_replace('#\?+$#', '', $str);
			$str = preg_replace('#!{1,}#', '', $str); // new add
			$str = preg_replace('/-{1,}/', '-', $str);
			$str = preg_replace('/\.{1,}/', '-', $str);
			$str = preg_replace('/"{1,}/', '', $str);
			$str = preg_replace('/°|\}{1,}|\{{1,}|`|«|‹|»|›|„|“|‟|”|’|"|❝|❞|❮|❯|⹂|〝|〞|〟|＂|‚|‘|‛|❛|❜|❟/','',$str);
			$str = preg_replace('/[^a-zA-Z0-9]/', '-', $str);

			return $str;

	}
	/**
	 * tools::suppr_accents($str, $encoding='utf-8');
	 */



  /**
   * tools::format_in_url( $str );
   *
   * @param  {string}		$str  string to transform in url string
   * @return {string}     		string url formatted
   */
  public static function format_in_url( $str ){


			$str = trim($str);
    	// suppr accents and ligatured chars
			$str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
			$str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
			$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
			// keep specify chars like : ? & = >
			$str = html_entity_decode($str, ENT_QUOTES, 'utf-8' );
			$str = htmlspecialchars_decode($str, ENT_QUOTES);

      // suppr others things we don't want
      $str = preg_replace('/\!{2,}/', '', $str);
      $str = preg_replace('/\?{2,}/', '', $str);
      $str = preg_replace('/\-{2,}/', '-', $str);
      $str = preg_replace('/\.{1,}/', '-', $str);
			$str = preg_replace('/\s{1,}/', '-', $str);
			$str = preg_replace('/~{1,}/', '-', $str);
			$str = preg_replace('/&{2,}/', '&', $str);
			$str =
			preg_replace('/\/|\^|\*|\(|\)|\[|\]|\{|\}|\\{1,}|§|:|°|@|`|\'|"|ù|%|ø|µ|</', '', $str);

			// var_dump( $str );

      return $str;

			// test
			// $?name=raf&amp;state=admin/!#§:}=+])°@0à^ç_`é2~&&ù%£$øµ* > <
  }
  /**
   * tools::format_in_url( $str );
   */



  /**
   * tools::fetch_mail_admin($mail);
   *
   * @param  {str}        $mail
   * @return {bool}       search if this mail is the admin's mail
   */
  public static function fetch_mail_admin($mail){


      $ARR_pdo = array( 'mail' => $mail );
      $sql = 'SELECT mail FROM admins WHERE mail=:mail';
      $response = 'one';
      $last_id = false; // RETURN last id

      //  ->  fetch
      $FETCH_MAIL_ADMIN = db::server($ARR_pdo, $sql, $response, $last_id);

      // TEST RETURNED VALUE
      if( boolval($FETCH_MAIL_ADMIN) == true AND $FETCH_MAIL_ADMIN['mail'] == $mail ){

        return true;
      }
      else{

        return false;
      }


  }
  /**
   * tools::fetch_mail_admin($mail);
   */



  /**
   * tools::img_recorder( $dir_path, $ARR_sizes );
   *
   * Manage all imgs records case
   * NOTE : imgs must be sended with the name="img[]"
   * or 'img[]' key in FormData js
	 *
	 * NOTE : it supports the following image types :
	 * 	'image/png'
	 *	'image/jpg'
	 *	'image/gif' (not animated)
	 *	'image/webp' (not animated)
   *
   * @param  {string} $dir_path  	DIRECTORY WHERE RECORD IMGS
   * @param  {array} 	$ARR_sizes 	Array of sizes wanted
   *                							ex.: array( 'min'=> 300, 'max' => 1200 )
	 * 															- imgs names will be prefixed as min-..., max-...
	 * 															and recorded as it in the folder BUT NOT IN DATABASE.
   * @return {array}  Array of new names imgs WITHOUT prefix
	 *
   */
  public static function img_recorder( $dir_path, $ARR_sizes ){


			if( empty($_FILES) || !isset($_FILES) ){
        return;
      }

      // var_dump( $_FILES );

      // count imgs
      $count_imgs = count($_FILES['img']['name']);
      // var_dump( $count_imgs );

      // TYPES ACCEPTED
      $ARR_types_accepted = array(
        "image/png",
        "image/jpg",
        "image/jpeg",
        "image/gif",
				"image/webp",
      );

      // verify types
      for( $i=0; $i < $count_imgs; $i++ ){

          // !! no index on one img send
          $img_recived_name = $_FILES['img']['name'][$i];
          // var_dump($img_recived_name);

          // get extension
          $type = new SplFileInfo($img_recived_name);
          $type_img = 'image/'.strtolower( $type->getExtension() );
          // var_dump($type_img);

          // if type of an img is not on types accepted array -> error
          if( !in_array( $type_img , $ARR_types_accepted ) ){

              // json return error
              $tab = array('error' => tr::$TR['type_file_not_accepted'] );
              echo json_encode($tab, JSON_FORCE_OBJECT);
              exit;
          }

      }
      // end for verify types

      // prepa array new names
      $ARR_names_imgs = [];

      // sizes by default
      if( empty($ARR_sizes) ){

          // key become prefix ex min-dqjdhqshkh46546.jpg
          $ARR_sizes = DEF_ARR_SIZES;
      }


      // LOOP IMGS
      for( $i=0; $i < $count_imgs; $i++ ){


          $img_name = trim(htmlspecialchars($_FILES['img']['name'][$i]));
          $img_size = trim(htmlspecialchars($_FILES['img']['size'][$i]));
          $img_type = trim(htmlspecialchars($_FILES['img']['type'][$i]));
          $tmp_name = trim(htmlspecialchars($_FILES['img']['tmp_name'][$i]));

          // var_dump($_FILES['img']['name'][$i]);

          // create a BLOB of img
          $blob = getimagesize($tmp_name);

          // width && height of orginal image
          $width_origin = $blob[0];
          $height_origin = $blob[1];

          // check if image is in landscape or portrait
          $check_width = ( $width_origin > $height_origin )
          ? "landscape" : "portrait";

          // check Extension (jpeg, png, etc)
          $check_type = explode('/', $blob['mime'] );
          $extension = strtolower($check_type[1]);

          // transform JPEG on JPG
          if( $extension == 'jpeg' ){ $extension = 'jpg'; }

          // set new image name - here before loop size format
          // stamp
          $make_a_date = new DateTime('now', new DateTimeZone(TIMEZONE) );
          $stamp = $make_a_date->getTimestamp();

          // get a randomed name for one image
          $input = array("a","b","c","d","e","f","g","h","i",
          "j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
          // rajoutte des strings au hasard
          shuffle($input);
          $rand = '';
          for($ix=0; $ix < count($input); $ix++){
            $rand .= $input[$ix];
          }
          // var_dump($rand);
          // set new image name
          $new_name = $rand.'-'.$i.'-'.$stamp;


          // foreach SIZES ASKED
          foreach( $ARR_sizes as $prefix => $size ){


              // calcul new format image
              if( $check_width == "landscape" ){

                  $new_width = $size;
                  // round to integer
                  $new_height = round( ( $height_origin * ( $new_width/$width_origin ) ) , 0 );
              }
              else {
                  $new_height = $size;
                  $new_width = round( ( $width_origin * ( $new_height/$height_origin ) ) , 0 );
              }

              // imagecreatefrom $extension
              switch($extension){
                case 'gif': $img = imagecreatefromgif($tmp_name); break;
                case 'jpg': $img = imagecreatefromjpeg($tmp_name); break;
                case 'png': $img = imagecreatefrompng($tmp_name); break;
								case 'webp': $img = imagecreatefromwebp($tmp_name); break;
                default : imagecreatefromjpeg($tmp_name); break;
              }

              // creation nouvelle miniature selon les nouvelles dimentions connues
              $new = imagecreatetruecolor($new_width, $new_height);

              // preserve transparency
              if( $extension == 'gif'
							|| $extension == 'png'
							|| $extension == 'webp'  ){
                  imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
                  imagealphablending($new, false);
                  imagesavealpha($new, true);
              }

              // recopie l'image l'originale dans la nouvelle $new
              imagecopyresampled( $new, $img, 0, 0, 0, 0,
                $new_width, $new_height, $width_origin, $height_origin );


              // CREATION et ENRESITREMENT DE LA MINIATURE
              //!! pour gif -> pas de compression,
              // jpg-> compress 0 - 100(def 75), png-> compress 0 à 9 (def 6)
              switch ($extension) {
                case 'gif':
									imagegif($new, $dir_path.'/'.$prefix.'-'.$new_name.'.'.$extension.'');
								break;
                case 'jpg':
									$quality = 75; // range 0 - 100 -> best
									imagejpeg($new, $dir_path.'/'.$prefix.'-'.$new_name.'.'.$extension.'', $quality );
								break;
                case 'png':
									$quality = 5; // degree to compress 0 -> no / 9 -> max compression
									imagepng($new, $dir_path.'/'.$prefix.'-'.$new_name.'.'.$extension.'', $quality );
								break;
								case 'webp':
									$quality = 75; // range 0 - 100 -> best
									imagewebp($new, $dir_path.'/'.$prefix.'-'.$new_name.'.'.$extension.'', $quality );
								break;
							}

              // NAME NEW image
              $new_name_img = $new_name.'.'.$extension;

              // destroy image after process
              imagedestroy($new);

          }
          // foreach SIZES ASKED


          // ADD IMG NAME
          $ARR_names_imgs[] = $new_name_img;

      }
      // END FOR

      // return array of str. names imgs uploaded
      return $ARR_names_imgs;

  }
  /**
   * END tools::img_recorder( $dir_path, $ARR_sizes );
   *
   */



  /**
   * tools::add_to_sitemap( $id, $url, $for );
   *
   * @param  {int} 		$id     product_id OR category_id
   * @param  {string} $url    in url string
   * @param  {string} $for    'article' / 'category' / 'static_page'
   * @return {void}         	add article, category, static_page on sitemap
   */
  public static function add_to_sitemap( $id, $url, $for ){


      $id = (int) $id;

      // set it here for translate before
      // $for = ($for == 'article') ? 'article' : 'category';

			switch ($for) {

					case 'article':
						$location = HOST.'/'.$url.'/article/'.$id;
					break;

					case 'category':
						$location = HOST.'/'.$url.'/category/'.$id;
					break;

					case 'static_page':
						$location = HOST.'/'.$url;
					break;

					default:
						return;
					break;
			}
			// end switch

      $dom = new DOMDocument;
      $path = ROOT.'/sitemap.xml';
      $dom->formatOutput = true;
      $dom->preserveWhiteSpace = false;
      $dom->load($path);

      // FORMAT THE TITLE IN CORRECT URL
      // $location : www.myWebSite.com/my-product-is-something/article/42
      // or www.myWebSite.com/my-category-is-something/category/42


      // make a date
      $da = new DateTime('now', new DateTimeZone(TIMEZONE) );
      $date = $da->format('Y-m-d');

      $ns = 'http://www.sitemaps.org/schemas/sitemap/0.9';

      $urlElt = $dom->createElementNS($ns, 'url');
      $urlElt->appendChild($dom->createElementNS($ns, 'loc', $location));
      $urlElt->appendChild($dom->createElementNS($ns, 'lastmod', $date ));
      $urlElt->appendChild($dom->createElementNS($ns, 'changefreq', 'weekly'));
      $urlElt->appendChild($dom->createElementNS($ns, 'priority', '1.0'));

      $dom->documentElement->appendChild($urlElt);

      $dom->save($path);

  }
  /**
   *   END tools::add_to_sitemap( $id, $url, $for );
   */



  /**
   * tools::suppr_to_sitemap($id);
   *
   * @param  {int} 	$id   product_id, category_id, url of a static page
   * @return {void}     	delete an item on the sitemap
   */
  public static function suppr_to_sitemap($id){


      $path = ROOT.'/sitemap.xml';

      // suppression d'une page avec l'id
      $dom = new DOMDocument;
      $dom->formatOutput = true;
      $dom->preserveWhiteSpace = false;
      $dom->load($path);

      $ns = 'http://www.sitemaps.org/schemas/sitemap/0.9';

      foreach( $dom->getElementsByTagNameNS($ns, 'loc') as $locNode ){

          $nodeValue = $locNode->nodeValue;
					// id is always in last on url, url of static pages too
          $locID = explode('/', $nodeValue );
          $ID = end($locID);

					// id must be an int. or a string url
					// for suppress refs to static pages
          if( $ID == $id ){

              $dom->documentElement->removeChild($locNode->parentNode);
              break;
          }
      }

      $dom->save($path);

  }
  /**
   * tools::suppr_to_sitemap($id);
   */



  /**
   * tools::parse_line_breaks($text);
   *
   * @param  {str}    $text
   * @return {str}    remove too much line breaks in a text
   */
  public static function parse_line_breaks($text){


      $regex = '/(\r\n|\n|\t|\r){3,}/';
      $replacement = "\r\n\r\n"; // !! ONLY "" ARE INTERPRETED
      $text = preg_replace($regex, $replacement, $text);

      return $text;
  }
  /**
   * tools::parse_line_breaks($text);
   */



	/**
	 * tools::intl_number( $num );
	 *
	 * @param  {float/string} $num  number to format in local number
	 * @return {string}      	number formatted locally
	 */
	public static function intl_number( $num ){


			// transform in float
			$num = (float) $num;

			// format price by const LANG_LOCALE
			$Number = new NumberFormatter( LANG_LOCALE, NumberFormatter::DECIMAL );

			// return: 142.23 or 142,23, ...
			return $Number->format( $num );

	}
	/**
	 * tools::intl_number( $num );
	 */



	/**
	 * tools::intl_currency( $price );
	 *
	 * @param  {float/string} $price  number to format in local price + currency sign
	 * @return {string}      	price formatted locally
	 */
	public static function intl_currency( $price ){


			// transform in float
			$price = (float) $price;

			// format price by const LANG_LOCALE
			$Number = new NumberFormatter( LANG_LOCALE, NumberFormatter::CURRENCY );

			// return: £ 142.23 or 142,23 €, ...
			return $Number->formatCurrency( $price,
												$Number->getTextAttribute(NumberFormatter::CURRENCY_CODE) );


	}
	/**
	 * tools::intl_currency( $price );
	 */



	/**
	 * tools::format_date_locale( $date_Object, $dateType , $timeType, $pattern );
	 *
	 * @param  {object} $date_Object  DateTime object or object accepted
	 * @param  {string} $dateType 		'NONE', 'SHORT', 'MEDIUM', 'LONG', 'FULL'
	 * @param  {string} $timeType 		'NONE', 'SHORT', 'MEDIUM', 'LONG', 'FULL'
	 * @param  {pattern} $pattern  		pattern to apply or null 'MMMM y' -> may 2022
	 * see how to set patterns: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
	 * @return {string}         			date formated locally
	 */
	public static function format_date_locale( $date_Object, $dateType , $timeType, $pattern ){

			// date format
			switch ( $dateType  ) {
				case 'NONE':
					$Date_Format = IntlDateFormatter::NONE; // 20220606 08:16 AM
				break;
				case 'SHORT':
					$Date_Format = IntlDateFormatter::SHORT; // 06/06/2022
				break;
				case 'MEDIUM':
					$Date_Format = IntlDateFormatter::MEDIUM; // 6 juin 2022 in [fr] must vary
				break;
				case 'LONG':
					$Date_Format = IntlDateFormatter::LONG; // 6 juin 2022
				break;
				case 'FULL':
					$Date_Format = IntlDateFormatter::FULL; // lundi 6 juin 2022
				break;

				default:
					$Date_Format = IntlDateFormatter::SHORT;
				break;
			}

			// time format
			switch ( $timeType  ) {
				case 'NONE':
					$Time_Format = IntlDateFormatter::NONE; // ''
				break;
				case 'SHORT':
					$Time_Format = IntlDateFormatter::SHORT; // 08:11
				break;
				case 'MEDIUM':
					$Time_Format = IntlDateFormatter::MEDIUM; // 08:11:10
				break;
				case 'LONG':
					$Time_Format = IntlDateFormatter::LONG; // 08:09:33 UTC+2
				break;
				case 'FULL':
					$Time_Format = IntlDateFormatter::FULL; // 08:10:38 heure d’été d’Europe centrale
				break;

				default:
					$Time_Format = IntlDateFormatter::SHORT;
				break;
			}

			// create date formatter
			//  LANG_BACK, TIMEZONE, -> const API
			$local_date = IntlDateFormatter::create(
			    LANG_LOCALE, // lang
			    $Date_Format, // date
			    $Time_Format, // time
			    TIMEZONE, // timezone
			    null, // type of calendar / null -> const IntlDateFormatter::GREGORIAN
			    $pattern // pattern to apply or null
			);

			// return date formatted
			return $local_date->format( $date_Object );

	}
	/**
	 * tools::format_date_locale( $date, $dateType , $timeType, $pattern );
	 */



	/**
	 * tools::get_locales_settings();
	 *
	 * @return {array}  Array
	 * (
	 *     [code_min] => az
	 *     [code] => az_Latn_AZ
	 *     [locale] => Azerbaijani (Latin, Azerbaijan)
	 *     [currency_iso] => AZN // or ''
	 *     [symbol] => ₼ // or ''
	 * 		 [selected] => 'selected' // only for one item
	 * )
	 */
	public static function get_locales_settings(){


		// get array of Locales codes included in PHP
		$ARR_LANGS_PHP = ResourceBundle::getLocales('');

		// prepa. an array for rendering
		$LANGS = array();


		foreach ($ARR_LANGS_PHP as $k => $v) {


				// currency ISO code if exist
				$currency_iso = NumberFormatter::create(
					$v,NumberFormatter::CURRENCY)
					->getTextAttribute(NumberFormatter::CURRENCY_CODE);

				// if no currency ISO code -> 'XXX' is returned,  $currency_iso => ''
				$currency_iso = ( $currency_iso == 'XXX') ? '' : $currency_iso;

				// currency symbol if exist
				$currency_symbol = new NumberFormatter( $v, NumberFormatter::DECIMAL );
				$symbol = $currency_symbol->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

				// if no symbol -> '¤' is returned,  $symbol => ''
				$symbol = ( $symbol == '¤' ) ? '' : $symbol;

				// return name of locale with details ex. Arabic (Tunisia)
				$descr_locale = Locale::getDisplayName($v, 'en');


				// add datas to lang array
				$LANGS[] = array(

						'code_min' => strtolower(substr($v, 0, 2)),
						// code -> ex. 'fr_FR'
						'code' => $v,
						// locale string in english for all
						'name' => $descr_locale,
						// currency_iso -> ex. 'EUR'
						'currency_iso' => $currency_iso,
						// ex. €
						'symbol' => $symbol
				);

				// selected ?
				if( LANG_LOCALE == $v ){

						// get index for entry
						$index = count($LANGS)-1;

						// attr selected for this entry only
						$LANGS[$index]['selected'] = 'selected';
				}


		}
		// end loop

		// return array locales
		return $LANGS;

	}
	/**
	 * tools::get_locales_settings();
	 */



	/**
	 * tools::get_timezones();
	 *
	 * @return {array}  array of ALL TIMEZONES string aviables in PHP
	 */
	public static function get_timezones(){

			// GET ALL TIMEZONES
			$ARR_timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

			$ARR_return = array();

			// push in array - watch selected
			foreach ( $ARR_timezones as $k => $v ) {

					$ARR_return[] = ( $v == TIMEZONE )
					? array( 'timezone' => $v, 'selected' => 'selected' )
					: array( 'timezone' => $v, 'selected' => '' );
			}

			return  $ARR_return;

	}
	/**
	 * tools::get_timezones();
	 */



}
// END CLASS tools::

?>
