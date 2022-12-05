<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: tools.php
 *
 * const TEMPLATES[] // templates to load
 *
 *
 * tools::get_templates();
 * tools::minify_html( $html );
 * tools::get_static_page();
 * tools::cut_string( $nb_chars, $str );
 * tools::suppr_accents($str, $encoding='utf-8');
 * tools::parse_line_breaks( $text );
 * tools::inline_string( $str );
 * tools::intl_number( $num );
 * tools::intl_currency( $price );
 * tools::format_date_locale( $date_Object, $dateType , $timeType, $pattern );
 *
 */

	// LIST OF TEMPLATE MUST BE CHARGED IN FIRST
	// - OTHERS ARE DYNAMICALLY CHARGED SEE js code
	const TEMPLATES = [
			'featured_products',
			'payment_card_form',
			'payment_form',
			'payment_form_partial',
			'payment_success',
			'products_view',
			'products_view_inl',
			'related_prods',
			'render_sale',
			'render_sale_login',
			'single_product',
	];


// start class tools::
class tools {



  /**
   * tools::get_templates();
   *
   * @return {json}  arrays of name && html content of templates
   */
  public static function get_templates(){

      $ARR_return = array();

      // LIST OF TEMPLATE MUST BE CHARGED IN FIRST
      // - OTHERS ARE DYNAMICALLY CHARGED SEE js code
      $ARR_templates = TEMPLATES;

      foreach( $ARR_templates as $k => $v ){

					$path_file = ROOT.'/templates/API/'.$v.'.html';

					$html = file_get_contents( $path_file );

          // REMPLACE COMMENTS IN THE HTML CODE
          // $ARR_return[$k]['html'] = preg_replace('/<!--(.|\s)*?-->/', '', $html);
					$ARR_return[$k]['html'] = tools::minify_html( $html );

          $ARR_return[$k]['name'] = $v;

      }

      $tab = array('templates' => $ARR_return );

      echo json_encode( $tab );
      exit;

  }
  /**
   * tools::get_templates();
   */



	/**
	 * tools::minify_html( $html );
	 *
	 * @param  {string} 	$html  a script html to inline
	 * @return {string}   returns HTML code without spaces or comments
	 */
	public static function minify_html( $html ){

			$search = array(
				'/(\s)+/s',         	// shorten multiple whitespace sequences
				'/<!--(.|\s)*?-->/', 	// remove HTML comments
				'/(\t)+/' 						// remove tabs
			);

			// be carrefull !! all "" are interpreted correctly
			$replace = array(
				" ",
				"",
				" "
			);

			$minified_html = preg_replace($search, $replace, $html);

			return $minified_html;

	}
	/**
	 * tools::minify_html( $html );
	 */



  /**
   * tools::get_static_page();
   *
   * @return {json}  return a static page
   */
  public static function get_static_page(){


			// data recived
			$template_asked = (string) trim(htmlspecialchars($_POST['page_url']));

			// too long request
			if( iconv_strlen($template_asked) > 100 ){

					$tab = array( 'error' => tr::$TR['error_gen'] );
		      echo json_encode( $tab, JSON_FORCE_OBJECT );
		      exit;
			}

			// path to html static page code
			$path = ROOT.'/templates/STATIC_PAGES/'.$template_asked.'.html';

			// test if page exits
			if( file_exists($path) ){

					$raw_html = file_get_contents($path);
			}
			else{

					// error file not exist
					$tab = array( 'error' => tr::$TR['page_not_found'] );
					echo json_encode( $tab, JSON_FORCE_OBJECT );
					exit;
			}

			// minify
			$html = tools::minify_html( $raw_html );

			// return inlined template
      $tab = array( 'success' => true,
										'template' => $html );

      echo json_encode( $tab );
      exit;

  }
  /**
   * tools::get_static_page();
   */



  /**
   * tools::cut_string( $nb_chars, $str );
   *
   * @param  {int} $nb_chars  number or '' -> nb chars to cut string evently
   * @param  {str} $str       string to cut
   * @return {str}            string truncated at $nb_chars to nearest space
   */
  public static function cut_string( $nb_chars, $str ){

      $nb_chars_default = 250;

      $nb_chars = ( $nb_chars != 0
      || $nb_chars != '' ) ? $nb_chars : $nb_chars_default;

      $len_str = strlen($str);

      if( $len_str > $nb_chars ){

        return substr( $str, 0, strrpos($str, ' ', $nb_chars-$len_str) );
      }
      else{

        return $str;
      }
  }
  /**
   * tools::cut_string( $nb_chars, $str );
   */



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
      $str = preg_replace('#&[^;?]+;#', "", $str); // [ ]{2,}
      $str = preg_replace("#['\s]+#", "-", $str);
      $str = preg_replace('#\?+$#', "", $str);
      $str = preg_replace('#!{1,}#', "", $str); // new add
      $str = preg_replace('/-{1,}/', "-", $str);
      $str = preg_replace('/\.{1,}/', "-", $str);

      return $str;

  }
  /**
   * tools::suppr_accents($str, $encoding='utf-8');
   */



  /**
   * tools::parse_line_breaks( $text );
   *
   * @param  {str} $text
   * @return {str} Remove too much line breaks in a text
   */
  public static function parse_line_breaks( $text ){

      $regex = '/(\r\n|\n|\t|\r){3,}/';
      $replacement = "\r\n\r\n"; // !! ONLY "" ARE INTERPRETED
      $text = preg_replace($regex, $replacement, $text);

      return $text;

  }
  /**
   * tools::parse_line_breaks( $text );
   */



	/**
	 * tools::inline_string( $str ); // used ?
	 * remove all line breaks and +2 spaces
	 *
	 * @param  {string} $str 	string to inline
	 * @return {string}      	return string without any line break
	 */
	public static function inline_string( $str ){

			$regex = '/(\r\n|\n|\t|\r){1,}|\s+/';
			$replacement = " "; // !! ONLY "" ARE INTERPRETED
			$str = preg_replace($regex, $replacement, $str);

			return $str;

	}
	/**
	 * END tools::inline_string( $str );
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




}
// END CLASS tools::



?>
