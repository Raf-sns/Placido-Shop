<?php
/**
 * © Copyright - Raphaël Castello, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	api.php
 *
 * IN GLOBAL CONTEXT :
 * const ROOT -> root path of api
 *  css files paths to compress
 *  js files paths to compress
 *
 *
 * CLASS api::
 *
 * DEFINE API const - aviables for all scripts
 *
 * // root path of api
 * define const root path : ROOT
 *
 * // global usage array
 * public static $REQ = array();
 *
 * METHODS :
 *
 * api::init_settings(); // this build CONSTANTS by api.json file
 *
 * api::require_Module($module_name, $script); // for call a PHP module file
 * api::list_Modules(); // get complete architecture of MODULES folder
 * api::$MODULES=; // array of globally accessible modules
 * api::init_modules( $context ); // init modules for context 'front' / 'back'
 * api::translate_module( $path_translation );
 *
 * api::api_crypt( $string, $action ); // 'encr' / 'decr'
 * api::get_static_properties(); // for test and render infos api object
 *
 */

	// define root_path
	define('ROOT', $_SERVER['DOCUMENT_ROOT']);

	// architecture folders
	// for not rename an admin folder as a folder api
	const ARCH = array(
		'.well-known',
		// 'ADMIN',
		'API',
		'CSS',
		'FAV',
		'INSTALL',
		'JS',
		'Mustache',
		'PHP',
		'cgi-bin',
		'img',
		'templates',
		'translate',
		'webfonts'
	);


/**
 * class api:: extended by class config for ::api_crypt()
 * define API_SETTINGS - constants for API
 */
class api extends config {



	// for assign globals values in router see: PHP/control.php/control::router()
	// use:  api::$REQ[]
	public static $REQ = array();


  /**
   * api::init_settings();
   * define constants api for all scripts
   *
   */
  public static function init_settings(){


      $get_json_settings = file_get_contents(ROOT.'/API/api.json');

      $SETTINGS = json_decode($get_json_settings, true);
			// var_export( $SETTINGS );
			// exit;

			// define array of API settings - not used in front
			define('API_SETTINGS', $SETTINGS);

			// new - define constants settings
      foreach( $SETTINGS as $key => $value ){

          // attr values to class api statics properties
					define($key, $value);
      }

  }
  /**
   * END api::init_settings();
   */



  /**
   * api::require_Module( $module_name, $script );
   *
   * @param  {string} $module_name  NAME OF THE MODULE
   * @param  {string} $script      	NAME OF THE SCRIPT
	 * i. can use '/PHP/my_script.php' || 'index.php'
	 * || 'IMGS/my_image.jpg' || 'HTML/my_page.html' || '...'
   * @return {void}   include script/file from $module_name FOLDER
   */
  public static function require_Module($module_name, $script){

			// module path
      $module = ROOT.'/MODULES/'.$module_name.'/'.$script;

      include_once $module;

  }
  /**
   * api::require_Module( $module_name, $script );
   */



	/**
	 * api::list_dir_recursive($dir);
	 *
	 * @param  {string} 	$dir 	path to the folder to scan
	 * @return {array}     return complete architecture of a folder
	 */
	public static function list_dir_recursive( $dir ){


			$result = array();

			$cdir = scandir($dir);

			// loop
			foreach( $cdir as $key => $value ){

					if( in_array($value, array('.','..')) ){

							continue;
					}

					// if is directory
					if( is_dir($dir.DIRECTORY_SEPARATOR.$value) ){

							$result[$value] =
								api::list_dir_recursive($dir.DIRECTORY_SEPARATOR.$value);

					} // if is package
					else if( $value == 'package.json' ){

							// get package
							$Get_package = file_get_contents($dir.DIRECTORY_SEPARATOR.$value);
							$PACKAGE = json_decode($Get_package, true);
							// pass name at the first level - for render it on template
							$result['name'] = $PACKAGE['name'];
							// package informations
							$result['package'] = $PACKAGE;
					}
					else{

							// if is file
							$result[] = $value;
					}
			}
			// end  loop

			return $result;

	}
	/**
	 * end  api::list_dir_recursive( $dir );
	 */



	/**
	 * api::list_Modules();
	 *
	 * source code : https://www.php.net/manual/en/function.scandir.php#110570
	 * @return {array}
	 * return an array of all FOLDERS and FILES present on MODULES
	 *
	 */
	public static function list_Modules(){


			// root path
			$dir = ROOT.'/MODULES';

			// return architecture of MODULES FOLDER
			return api::list_dir_recursive($dir);

	}
	/**
	 * api::list_Modules();
	 */



	/**
	 * 	api::$MODULES
	 *  treeview of modules + autoload list foreach module
	 */
	public static $MODULES;



	/**
	 * api::init_modules( $context );
	 *
	 * @param  {string} 	$context 	'front' / 'back'
	 * @return {void}     autoload 	modules PHP, javascript, translation
	 */
	public static function init_modules( $context ){


			$LIST_MODULES = api::list_Modules();

			if( empty($LIST_MODULES) ){

					return;
			}

			// loop modules for check auto-load
			foreach( $LIST_MODULES as $k => $module ){

					// check if had autoload directives
					if( !isset($module['package'])
					|| !isset($module['package']['autoload'])
					|| !isset($module['package']['autoload'][$context])  ){

							continue;
					}

					// test if empty
					if( empty($module['package']['autoload'][$context]) ){

							continue;
					}

					// INDICATE AUTO-LOAD LIST in TOP OF MODULE ARRAY
					$LIST_MODULES[$k]['autoload'] = array();

					// manage autoload directives
					foreach( $module['package']['autoload'][$context] as $k_ctx => $value ){

							// push AUTO-LOAD LIST in TOP OF MODULE ARRAY
							$LIST_MODULES[$k]['autoload'][] = $value;

							// init PHP / translation
							if( $value == 'translation' ){

									// get lang to translate for context 'front' / 'back'
									$lang_api = tr::get_langs_to_tr()[$context];

									// get translation for the module for this lang
									$path_translation =
										ROOT.'/MODULES/'.$k.'/translation/'.$context.'/'.$lang_api.'.txt';

									// if a translation for context exist
									if( file_exists($path_translation) ){

											// add to global translation tr::$TR[]
											api::translate_module( $path_translation );
									}
									else{

											// get english translation by default ...
											$path_translation =
											ROOT.'/MODULES/'.$k.'/translation/'.$context.'/en.txt';

											// add to global translation tr::$TR[]
											api::translate_module( $path_translation );
									}

							}
							// end translation module


					}
					// end loop autoload directives
			}
			// end loop

			// SET public static api::$MODULES this is accessible everywhere
			self::$MODULES = $LIST_MODULES;

	}
	/**
	 * api::init_modules( $context );
	 */



	/**
	 * api::translate_module( $path_translation );
	 *
	 * @param  {string} 	$path_translation path to translation text in MODULES
	 * @return {array}    add to global translation tr::$TR[]
	 */
	public static function translate_module( $path_translation ){


			$TRANS_MODULE = file_get_contents($path_translation);

			$file_to_translate = tr::sanitize_translation_text( $TRANS_MODULE );

			$TRANSLATE_array = explode('***', $file_to_translate);

			$ADD_in_tranlation = array();

			foreach ( $TRANSLATE_array as $k => $v ){

					if( $k == 0 ){

							continue;
					}
					if( $k % 2 == 0 ){

							$key = trim($TRANSLATE_array[$k-1]);
							$ADD_in_tranlation[$key] = trim($v);
					}

			}


			// ASSIGN TO GLOBAL ARRAY TRANSLATE
			tr::$TR = array_merge(tr::$TR, $ADD_in_tranlation);

	}
	/**
	 * api::translate_module( $path_translation );
	 */



  /**
   *  api::api_crypt( $string, $action ); 'encr' / 'decr'
	 * - encrypt or decrypt
   *
   * @param  {str} $string   string to encrypt or decrypt
   * @param  {str} $action = 'encr' / 'decr'
   */
  public static function api_crypt( $string, $action ) {


      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash( 'sha256', self::SEC_API_KEY );
      $iv = substr( hash( 'sha256', self::SEC_API_IV ), 0, 16 );

      if( $action == 'encr' ) {
          $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
      }
      else if( $action == 'decr' ){
          $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
      }
      else{
          $output = 'Error crypt';
      }

      return $output;
  }
  /**
   *  END api::api_crypt( $string, $action );
   */



	// FOR TESTS ...

	/**
	 * api::get_static_properties($class_name);
	 * NO USED , for test
	 * @param  {type} $class_name
	 * @return {log}  return all static properties of API object
	 */
	public static function get_static_properties($class_name) {


			$class = new ReflectionClass($class_name);

			echo '<b>infos Class : '.$class->name.'</b><br>';

			$staticMembers = $class->getStaticProperties();

			foreach( $staticMembers as $key => $value ){

					echo '<pre>';
					echo $key. ' -> ';

					if( is_array($value) ){
							var_export($value);
					}
					else if( is_bool($value) ){

							var_export($value);
					}
					else{

							echo $value;
					}

					echo '</pre>';

			}
			// end foreach

  }
	/**
	 * END api::get_static_properties();
	 */


}
// END class api::

?>
