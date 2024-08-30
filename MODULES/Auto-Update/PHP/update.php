<?php
/**
 * PLACIDO-SHOP MODULE AUTO-UPDATE
 * Copyright © Raphaël Castello, 2022-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	update.php
 *
 * Class: update
 *
 * __constructor( array $posts = null )
 *
 * public function fetch_source();
 * private function excluded_from_update();
 * private function update_api();
 * private function delete_folder_update( $path );
 * private function empty_the_folder( $path );
 * public function download_lang();
 * public function add_or_remove_file();
 * public function zip_api();
 * public function delete_backup();
 *
 * -> init. new Object + small switch controller
 * IN BOTTOM of script
 *
 */

	// LOAD RESSOURCES
	require_once $_SERVER['DOCUMENT_ROOT'].'/API/config.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/API/db.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/API/tr.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/API/api.php';

	// init. constants for API/constants.php
	api::init_settings(); // this define ROOT const

	// add translations
	api::init_modules( 'back' );

	// require token.php for verify token
	require_once ROOT.'/'.ADMIN_FOLDER.'/PHP/token.php';
	// require tools.php for translate date update
	require_once ROOT.'/'.ADMIN_FOLDER.'/PHP/tools.php';
	// require settings class for update VERSION and LAST_UPDATE
	require_once ROOT.'/'.ADMIN_FOLDER.'/PHP/settings.php';


class update extends config {


	// constructor of class update
	function __construct( array $posts = null ){

			if( is_null($posts) ){

					$posts = $_POST;

					// contruct object properties with $_POST datas key->value
					foreach( $posts as $key => $value ){

							$_key =
							( iconv_strlen($key) > 100 )
							? exit : (string) trim(htmlspecialchars($key));

							$_value =
							( iconv_strlen($value) > 150 )
							? exit : (string) trim(htmlspecialchars($value));

							// attr each post datas as properties
							$this->$_key = $_value;
					}
			}
	}
	// END constructor of class update



	/**
	 * public function fetch_source();
	 *
	 * @return {type}  description
	 */
	public function fetch_source(){


		  // VERIFY TOKEN
      token::verify_token();

			// FIRST clean UPLOAD_TEMP directory
			$this->delete_folder_update();

			// prepare an array for processing header
			// will contain :
			// 'content-type'
			// 'host'
			// 'hash'
			$HEADERS = array();

			// prepa. empty response
			$response = '';

			$POST_VARS = array(
				'command' => $this->command,
				'host' => $this->host,
				'version' => $this->version,
				'token_Placido' => $this->token_Placido,
			);

			// prepare request
      $url_Update_Center = 'https://update.placido-shop.com';

			// init. cURL + options
      $cURL = curl_init();
      curl_setopt( $cURL , CURLOPT_URL , $url_Update_Center );
      curl_setopt( $cURL , CURLOPT_POST, 1 );
		  curl_setopt( $cURL , CURLOPT_POSTFIELDS , $POST_VARS );
			curl_setopt( $cURL , CURLOPT_HEADER, 0 );
		  curl_setopt( $cURL , CURLOPT_RETURNTRANSFER , 1 );
			curl_setopt( $cURL , CURLOPT_AUTOREFERER, 0 );
		  curl_setopt( $cURL , CURLOPT_CONNECTTIMEOUT , 300 );
		  curl_setopt( $cURL , CURLOPT_TIMEOUT , 300 );
			curl_setopt( $cURL , CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt( $cURL , CURLOPT_SSL_VERIFYPEER, 1 );
			curl_setopt( $cURL , CURLOPT_HTTPHEADER ,
      array( 'Content-Type: multipart/form-data',
			'Accept: application/zip, text/plain, application/json' ));

			// call-back for process headers - EMPTY $HEADERS[...]
			curl_setopt( $cURL, CURLOPT_HEADERFUNCTION,
				  function( $cURL, $header) use (&$HEADERS){

				    	$len = strlen($header);
				    	$header = explode(':', $header, 2);
							// ignore invalid headers
				    	if( count($header) < 2 )	return $len;

				    	$HEADERS[strtolower(trim($header[0]))] = trim($header[1]);

				    	return $len;
				  } // callback function
			);
			// END cURL options


			// catch error
			try{

					// execute cURL
					$response = curl_exec($cURL);

					// close cURL
					curl_close( $cURL );
			}
			catch(Throwable $t){

					// close
					curl_close( $cURL );

					// error
					$Arr = array( 'error' => $t->getMessage() );
					echo json_encode( $Arr, JSON_FORCE_OBJECT);
					exit;
			}


			// if empty response
			if( empty($response) ){

					// error
					$Arr = array( 'error' => tr::$TR['update_not_available'] );
					echo json_encode( $Arr, JSON_FORCE_OBJECT);
					exit;
			}

			// var_dump( $HEADERS );
			// var_dump( $HEADERS['host'] );
			// var_dump( $HEADERS['hash'] );
			// var_dump(curl_getinfo($cURL, CURLINFO_CONTENT_TYPE));


			// sanitize content-type
			$Split_charset = preg_split('/(\;.)/i',$HEADERS['content-type']);
			$HEADERS['content-type'] = $Split_charset[0];

			// verify ERRORS returned by update center
			// -> return always data error on .json
			if( curl_getinfo($cURL, CURLINFO_CONTENT_TYPE) == 'application/json'
			|| $HEADERS['content-type'] == 'application/json' ){

						header('Content-Type: application/json');
						echo $response;
						exit;
			}

			// verify .zip file
			if( curl_getinfo($cURL, CURLINFO_CONTENT_TYPE) != 'application/zip'
			|| $HEADERS['content-type'] != 'application/zip'
			|| $HEADERS['host'] != str_replace('https://','',$url_Update_Center)
			){

					// error
					$ARR = array( 'error' => tr::$TR['update_not_available'] );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

			// path to temp folder TO RECORD .zip RECIVED
			$temp_path =
				$_SERVER['DOCUMENT_ROOT'].'/MODULES/Auto-Update/UPLOAD_TEMP';


			// create FOLDER in not exist -> api not compress empty folders ...
			if( !is_dir($temp_path) ){

					// CREATE FOLDER UPLOAD_TEMP
					mkdir( $temp_path, 0700 );
			}

			// previous name of zip file -> will be 1.9.99.zip
			// 1.9.99 -> version number asked by client
			$achive_zip_path =  $temp_path.'/'.$this->version.'.zip';

			// catch error
			try{

					// RECORD .zip file recived on the temp folder
					file_put_contents( $achive_zip_path, $response );

					// NOW here, we can check hash of file
					if( !hash_equals(hash_file('md5',$achive_zip_path), $HEADERS['hash']) ){

								// delete file and display error
								unlink($achive_zip_path);

								// error
								$ARR = array( 'error' =>
								'Extraction error: The file may be corrupted ...' );
								echo json_encode( $ARR, JSON_FORCE_OBJECT);
								exit;
					}

					// CREATE FOLDER TO UNCOMPRESS SOURCES
					$unzip_folder = $temp_path.'/UNZIP';

					// CREATE FOLDER UNZIP
					mkdir($unzip_folder, 0700);

					// CREATE ZIP MANAGER - for unzip archive app
					$zip = new ZipArchive;

					// open zip file and extract it
					if( $zip->open( $achive_zip_path ) === TRUE ){

							// WELL EXTRACTED
						  if(  $zip->extractTo($unzip_folder)  ){

									// END EXTRACTION ZIP
									$zip->close();

									// UPDATE REPLACE ALL FILES by UPDATED
									$this->update_api();

									// DELETE 'UNZIP' folder + .zip update file
									$this->delete_folder_update();


									// success
									$ARR = array( 'success' => true );
									echo json_encode( $ARR, JSON_FORCE_OBJECT);
									exit;
							}

					}
					else{

							// error
							$ARR = array( 'error' => 'Extraction error' );
							echo json_encode( $ARR, JSON_FORCE_OBJECT);
							exit;
					}


			}
			catch(Throwable $t){

					// error
					$ARR = array( 'error' => $t->getMessage() );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}


	}
	/**
	 * public function fetch_source();
	 */



	/**
	 * private function excluded_from_update();
	 *
	 * @return {type}  description
	 */
	private function excluded_from_update(){


			// fetch json excluded_from_update
			$path_json = dirname(__DIR__).'/excluded_from_update.json';

			// If no json
			if( !file_exists($path_json) ){

					// make an ampty array
					$EXCLUDED = array();
			}
			else{

					// get json
					$excluded_from_update = file_get_contents($path_json);

					// create array
					$EXCLUDED = json_decode($excluded_from_update, true);
			}

			// replace 'ADMIN' by real name of ADMIN_FOLDER
			if( count($EXCLUDED) != 0 ){

					// loop all / not test by regex
					foreach( $EXCLUDED as $k => $value ) {

							$EXCLUDED[$k] = str_replace('ADMIN', ADMIN_FOLDER, $value);
					}
			}

			// return array -> same if empty
			return $EXCLUDED;

	}
	/**
	 * private function excluded_from_update();
	 */



	/**
	 * private function update_api();
	 *
	 * @return {type}     description
	 */
	private function update_api(){


			// DELETE Stripe folder before renew
			$this->empty_the_folder( ROOT.'/PHP/LIBS/Stripe' );


			// GET list of user excluded files for update
			// as ARRAY
			$Exclude_list = $this->excluded_from_update();

			// get absolute path to unzip folder
			$unzip_folder = ROOT.'/MODULES/Auto-Update/UPLOAD_TEMP/UNZIP';

			// iterator
			// $mode ::SELF_FIRST -> list parents first
			$mode = RecursiveIteratorIterator::SELF_FIRST;

			$it =
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$unzip_folder, RecursiveDirectoryIterator::SKIP_DOTS
					), $mode
				);


			// loop iterator
			foreach( $it as $file ){


					// replace admin folder name in paths
					if( preg_match( '/(ADMIN)/', $it->getSubPathName() ) === true ){

							$path_source =
								str_replace('ADMIN', ADMIN_FOLDER, $it->getSubPathName() );
					}
					else{
							$path_source = $it->getSubPathName();
					}

					// NOT update excluded files or folders
					foreach( $Exclude_list as $k => $v) {

							if( preg_match( '/^('.preg_quote($v,'/').')/', $path_source ) === true ){

									// break this loop and parent loop
									continue 2;
							}
					}
					// end loop list

					// for Test
					// echo $path_source."\r\n";
					// echo $it->getSubPath()."\r\n";

					// create folder if not exist
					if( is_dir( $unzip_folder.'/'.$it->getSubPath() )
					&& !is_dir( ROOT.'/'.$it->getSubPath() )  ){

							mkdir(ROOT.'/'.$it->getSubPath(), 0700, true);
					}

					// copy : The first parameter cannot be a directory
					// then continue ...
					if( is_dir( $unzip_folder.'/'.$path_source ) ){

							continue;
					}

					// UPDATE Placido-Shop !
					copy( $unzip_folder.'/'.$path_source, ROOT.'/'.$path_source );

			}
			// end  loop iterator


			// Manage OLD API/api.json
			if( file_exists(ROOT.'/API/api.json')
					&& ( VERSION == '2.1.6' || VERSION == '2.2.3' )  ){


					// UPDATE new API/constants.php with old api.json for VERION <= 2.2.3
					$old_json_file = file_get_contents( ROOT.'/API/api.json' );

					// decode old api.json in array
					$OLD_json = json_decode($old_json_file, true);

					// require settings class for update VERSION and LAST_UPDATE
					require_once ROOT.'/'.ADMIN_FOLDER.'/PHP/settings.php';

					// UPDATE new constants from old old api.json
					settings::set_settings_api( $OLD_json );

					// delete old json - for sup. versions
					// unlink( ROOT.'/API/api.json' );

					// require tools class for update VERSION and LAST_UPDATE
					require_once ROOT.'/'.ADMIN_FOLDER.'/PHP/tools.php';

					// manage robots.txt
					tools::record_robots_txt( HOST, $context='allow' );
			}


			// UPDATE VERSION API

			// UPDATE last updated date
			// make date object
			$New_update_date = new DateTime( 'now', new DateTimeZone(TIMEZONE) );

			// format date in locale
			$last_update =
				tools::format_date_locale( $New_update_date, 'FULL' , 'SHORT', null );

			// array update version + last update
			$ARR_to_replace = array(
				'VERSION' => $this->version,
				'LAST_UPDATE' => $last_update
			);

			// write new datas in API/PHP/constants.php
			settings::set_settings_api( $ARR_to_replace );

	}
	/**
	 * private function update_api();
	 */



	/**
	 * private function delete_folder_update();
	 *
	 * @param  {string} 	$path 	DELETE ALL ON MODULES/Auto-Update/UPLOAD_TEMP
	 * @return {type}     use:  	$this->delete_folder();
	 */
	private function delete_folder_update(){


		  // path
			$temp_path =
				$_SERVER['DOCUMENT_ROOT'].'/MODULES/Auto-Update/UPLOAD_TEMP';

			if( !is_dir($temp_path) ){	return; }

			$it =
			new RecursiveDirectoryIterator($temp_path, FilesystemIterator::SKIP_DOTS);
	    $it =
			new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

			foreach($it as $file) {
	        if( $file->isDir() ){ rmdir( $file->getPathname() ); }
	        else{ unlink( $file->getPathname() ); }
	    }

	}
	/**
	 * private function delete_folder_update();
	 */



	/**
	 * private function empty_the_folder( $path );
	 *
	 * @param  {string} $path DELETE EVERYTHING IN THE FOLDER BY PATH
	 * @return {void}
	 *
	 */
	private function empty_the_folder( $path ){


		  // is dir ?
			if( !is_dir($path) ){	return; }

			$it =
			new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
	    $it =
			new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

			// delete content of folder
			foreach($it as $file) {
	        if( $file->isDir() ){ rmdir( $file->getPathname() ); }
	        else{ unlink( $file->getPathname() ); }
	    }

	}
	/**
	 * private function empty_the_folder( $path );
	 */



	/**
	 * public function download_lang();
	 *
	 * @return {type}  description
	 */
	public function download_lang(){


		  // VERIFY USER
      token::verify_token();

			// prepare an array for processing header
			// will contain :
			// 'content-type'
			// 'host'
			// 'hash'
			$HEADERS = array();

			// prepa. empty response
			$response = '';

			$POST_VARS = array(
				'command' => $this->command,
				'host' => $this->host,
				'lang' => $this->lang,
				'for_interface' => $this->for_interface, // 'back' / 'front'
				'token_Placido' => $this->token_Placido,
			);

			// prepare request
      $url_Update_Center = 'https://update.placido-shop.com';

			// init. cURL + options
      $cURL = curl_init();
      curl_setopt( $cURL , CURLOPT_URL , $url_Update_Center );
      curl_setopt( $cURL , CURLOPT_POST, 1 );
		  curl_setopt( $cURL , CURLOPT_POSTFIELDS , $POST_VARS );
			curl_setopt( $cURL , CURLOPT_HEADER, 0 );
		  curl_setopt( $cURL , CURLOPT_RETURNTRANSFER , 1 );
			curl_setopt( $cURL , CURLOPT_AUTOREFERER, 0 );
		  curl_setopt( $cURL , CURLOPT_CONNECTTIMEOUT , 300 );
		  curl_setopt( $cURL , CURLOPT_TIMEOUT , 300 );
			curl_setopt( $cURL , CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt( $cURL , CURLOPT_SSL_VERIFYPEER, 1 );
			curl_setopt( $cURL , CURLOPT_HTTPHEADER ,
      array( 'Content-Type: multipart/form-data',
			'Accept: text/plain, application/json' ));

			// call-back for process headers - EMPTY $HEADERS[...]
			curl_setopt( $cURL, CURLOPT_HEADERFUNCTION,
				  function( $cURL, $header) use (&$HEADERS){

							// var_dump($header);
				    	$len = strlen($header);
				    	$header = explode(':', $header, 2);
							// ignore invalid headers
				    	if( count($header) < 2 )	return $len;

				    	$HEADERS[strtolower(trim($header[0]))] = trim($header[1]);

				    	return $len;
				  } // callback function
			);
			// END cURL options


			// catch error
			try{

					// execute cURL
					$response = curl_exec($cURL);

					// close
					curl_close( $cURL );
			}
			catch(Throwable $t){

					// close
					curl_close( $cURL );

					// error
					$ARR = array( 'error' => $t->getMessage() );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

			// var_dump( $response );

			// if no response or bad type mime ?
			if( empty($response) ){

					// error
					$ARR = array( 'error' => tr::$TR['update_not_available'] );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

			// var_dump( curl_getinfo($cURL, CURLINFO_CONTENT_TYPE) );
			// var_dump( $HEADERS['host']);
			// var_dump( $HEADERS['hash']);

			// sanitize content-type
			$Split_charset = preg_split('/(\;.)/i',$HEADERS['content-type']);
			$HEADERS['content-type'] = $Split_charset[0];

			// verify .txt file
			if( !preg_match('/(text\/plain)/i',
					curl_getinfo($cURL, CURLINFO_CONTENT_TYPE))
			|| $HEADERS['content-type'] != 'text/plain'
			|| $HEADERS['host'] != str_replace('https://','',$url_Update_Center)
			){

					// error
					$ARR = array( 'error' => tr::$TR['update_not_available'] );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}


			// MAKE A TEMP FOLDER
			$temp_path = ROOT.'/MODULES/Auto-Update/UPLOAD_TEMP';

			// create folder if not exist
			if( !is_dir($temp_path) ){

					mkdir( $temp_path, 0700 );
			}

			// A TEMP NAME - $this->lang eg. "en.txt"
			$temp_file = $temp_path.'/'.$this->lang;

			// PUT FILE FOR TEST HASH
			file_put_contents( $temp_file, $response, LOCK_EX );

			// NOW here, we can check hash of file
			if( !hash_equals(hash_file('md5',$temp_file), $HEADERS['hash']) ){

						// delete TEMP file and display error
						unlink($temp_file);

						// error
						$ARR = array( 'error' =>
						'Extraction error: The file may be corrupted ...' );
						echo json_encode( $ARR, JSON_FORCE_OBJECT);
						exit;
			}

			// set file path for 'back'  or 'front'
			$file_path = ( $this->for_interface == 'front' )
			? ROOT.'/translate/'.$this->lang
			: ROOT.'/'.ADMIN_FOLDER.'/translate/'.$this->lang;


			// catch error
			try{

					// insert downloaded file or replace
					if( file_put_contents( $file_path, $response, LOCK_EX ) ){

							// DELETE 'UNZIP' folder + .zip update file
							$this->delete_folder_update();

							// success
							$ARR = array( 'success' => true );
							echo json_encode( $ARR, JSON_FORCE_OBJECT);
							exit;
					}

			}
			catch(Throwable $t){

					// error
					$ARR = array( 'error' => $t->getMessage() );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

	}
	/**
	 * end public function download_lang();
	 */



	/**
	 * public function add_or_remove_file();
	 *
	 * @return {json}  add / list / or remove file or folder for exclude to the update
	 */
	public function add_or_remove_file(){


			// VERIFY TOKEN
      token::verify_token();

			// empty file name
			if( empty($this->file) ){

					// error
					$ARR = array( 'error' => tr::$TR['enter_a_file_name'] );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

			// i. Note: $this->file alredy trimmed

			// fetch json excluded_from_update
			$path_json = dirname(__DIR__).'/excluded_from_update.json';

			// make an empty array
			$EXCLUDED = array();

			// test file exist - OR let array EXCLUDED empty
			if( file_exists($path_json) ){

					// get json
					$excluded_from_update = file_get_contents($path_json);

					// create array
					$EXCLUDED = json_decode($excluded_from_update, true);

					$EXCLUDED = ( $EXCLUDED === NULL ) ? array() : $EXCLUDED;
			}

			// if $this->execute == 'fetch' && $this->file == 'fetch'
			// -> DO nothing-> return just array

			// add item to exclude
			if( $this->execute == 'add' ){

					$file_to_insert = $this->file;

					// REPLACE ADMIN by REAL NAME OF ADMIN FOLDER
					// for test if file exist
					$file_to_insert = str_replace('ADMIN', ADMIN_FOLDER, $file_to_insert);

					// test if file path of file exist
					if( !file_exists( ROOT.'/'.$file_to_insert ) ){

							// error
							$ARR = array( 'error' => tr::$TR['bad_path_file'] );
							echo json_encode( $ARR, JSON_FORCE_OBJECT);
							exit;
					}

					// test if alerady in array
					if( in_array($this->file, $EXCLUDED) == true ){

							// error
							$ARR = array( 'error' => tr::$TR['file_path_already_in_list'] );
							echo json_encode( $ARR, JSON_FORCE_OBJECT);
							exit;
					}

					// SANITIZE FILE NAME
					// suppr spaces
					$this->file = preg_replace('/ {1,}/','', $this->file);
					// suppr '/something' and 'something/'
					$this->file = preg_replace('/^\/{1,}|\/{1,}$/','', $this->file );
					// replace 'something//something' by 'something/something'
					$this->file = preg_replace('/(\/){2,}/','/', $this->file);

					// add to array
					// -> FOR ADMIN FOLDER folders or files
					// -> record with the "ADMIN/" fake path
					$EXCLUDED[] = $this->file;

			}
			// end add item to exclude


			// remove item to exclude
			if( $this->execute == 'remove' ){

					// loop find item to remove
					for ($i=0; $i < count($EXCLUDED); $i++) {

							// if item was found -> remove it from the array
							if( $EXCLUDED[$i] == $this->file ){

									array_splice($EXCLUDED, $i, 1);
									break;
							}
					}
					// end loop find item to remove
			}
			// end remove item to exclude


			if( !empty($EXCLUDED) ){

					// sort array alphabetically
					sort( $EXCLUDED, SORT_STRING );
			}


			// catch error
			try{

					// if not A 'fetch' REQUEST
					if( $this->execute != 'fetch' ){

							// encode $EXCLUDED in json
							$json = json_encode( $EXCLUDED );

							// record new json
							file_put_contents( $path_json , $json );
					}

					// else -> return just the ARRAY of $EXCLUDED files

					// success
					$ARR = array( 'success' => true,
				 								'not_upload_list' => $EXCLUDED );

					echo json_encode( $ARR );
					exit;
			}
			catch(Throwable $t){

					// error
					$ARR = array( 'error' => $t->getMessage() );
					echo json_encode( $ARR, JSON_FORCE_OBJECT);
					exit;
			}

	}
	/**
	 * end public function add_or_remove_file();
	 */



	/**
	 * public function zip_api();
	 *
	 * @return {json}	ZIP THE ENTIRE API + database
	 */
	public function zip_api(){


			// VERIFY TOKEN
      token::verify_token();

			try{

					$ZIP_FOLDER = 'MODULES/Auto-Update/ZIP_API';

					// create folder ZIP_API
					if( !is_dir( ROOT.'/'.$ZIP_FOLDER )  ){

							// works with chmod to 0711
							mkdir( ROOT.'/'.$ZIP_FOLDER, 0711 );
					}

					// DIRECTORY TO COMPRESS
					$dir_to_zip = ROOT;

					$micro_time = microtime(true)*10000;

					// name of .zip file ex: Backup_koko.com_2024_07_30-1212121212.zip
					$zip_name = 'Backup'.'_'.HOST.'_'.date('Y_m_d').'-'.$micro_time.'.zip';

					// real path to FUTURE ARCHIVE
					$zip_dest = ROOT.'/'.$ZIP_FOLDER.'/'.$zip_name;

					// browse API files and folders and compress them

					// create zip archive - the .zip file will be automatically created
					$zip = new ZipArchive;
					$zip->open( $zip_dest,
						ZipArchive::CREATE | ZipArchive::OVERWRITE );

					// iterator
					$it =
						new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_to_zip,
												RecursiveDirectoryIterator::SKIP_DOTS));

					// loop iterator
					foreach( $it as $file ){


							// ignore ZIP_API folder
							if( preg_match('/(ZIP_API)/', $it->getSubPath()) == true ){

									continue;
							}

							// no need to check if is a DIRECTORY with $it->getSubPathName()
							// DIRECTORIES are added automatically
							$zip->addFile( $it->getPathname(),  $it->getSubPathName() );

					}
					// end  loop


					// BACKUP DATABASE

					// create folder to save database
					if( !is_dir( ROOT.'/'.$ZIP_FOLDER.'/DB' )  ){

							// works with chmod to 0711
							mkdir( ROOT.'/'.$ZIP_FOLDER.'/DB', 0711 );
					}

					// directory where record backup database
					$dir_db = ROOT.'/'.$ZIP_FOLDER.'/DB/';

					// execute
					$output = null;
					$retval = null;
					$command =
					'MYSQL_PWD='.self::DB_PASSWORD.' mysqldump -u '.self::DB_USER.' --single-transaction --skip-lock-tables '.self::DB_NAME.' -h '.self::DB_HOST.' > '.$dir_db.self::DB_NAME.'.sql';
					exec( $command, $output, $retval );

					// add database backup to .zip files
					$zip->addFile( $dir_db.self::DB_NAME.'.sql',  self::DB_NAME.'.sql' );
					// END BACKUP DATABASE


					$zip->close();
					// END ZIP API


					// delete database file
					unlink( $dir_db.self::DB_NAME.'.sql' );

					// modify permissions of .zip file
					chmod( ROOT.'/'.$ZIP_FOLDER.'/'.$zip_name, 0755 );

					// get zip url
					$zip_url = 'https://'.HOST.'/'.$ZIP_FOLDER.'/'.$zip_name;

					// success
					$Arr = array(
						'success' => tr::$TR['back_up_success'],
					 	'zip_url' => $zip_url,
						'zip_name' => $zip_name
					);

					echo json_encode( $Arr );
					exit;


			// end try
			}
			catch(Throwable $t){

					// error
					$Arr = array( 'error' => $t->getMessage() );
					echo json_encode( $Arr );
					exit;
			}

	}
	/**
	 * public function zip_api();
	 */



	/**
	 * public function delete_backup();
	 *
	 * @return {json} delete the backup  folder entirely
	 */
	public function delete_backup(){


	 		// VERIFY USER
	    token::verify_token();

			try{

					// backup folder path
					$backup_folder = ROOT.'/MODULES/Auto-Update/ZIP_API';

					// empty the folder
					$this->empty_the_folder( $backup_folder );

					// remove the folder
					rmdir( $backup_folder );

			}
			catch(Throwable $t){

					// error
					$Arr = array( 'error' => $t->getMessage() );
					echo json_encode( $Arr );
					exit;
			}


			// all ok - return success
			$Arr = array(
				'success' => tr::$TR['back_up_deleted']
			);

			echo json_encode( $Arr );
			exit;
	}
	/**
	 * public function delete_backup();
	 */




}
///////////////////////
// end class update  //
///////////////////////


	// init object
	$Update = new update();

	// var_dump( $Update->command );

	// switch command
	switch( $Update->command ){

			case 'install_version':
				$Update->fetch_source();
			break;

			case 'download_lang':
				$Update->download_lang();
			break;

			case 'add_remove_to_update':
				$Update->add_or_remove_file();
			break;

			case 'backup':
				$Update->zip_api();
			break;

			case 'delete_backup':
				$Update->delete_backup();
			break;

			default:
				exit('Upload Module : Bad command ...');
			break;
	}
	// end switch command

?>
