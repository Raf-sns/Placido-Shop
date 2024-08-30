<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2022-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	web_app.php
 *
 * web_app::record_web_app_settings();
 * web_app::return_web_app_settings();
 *
 */

class web_app {



	/**
	 * web_app::record_web_app_settings();
	 *
	 * @return {json}  	set the file ROOT.'/manifest.webmanifest'
	 * 									&& record all images for PWA application
	 */
	public static function record_web_app_settings(){


			// VERIFY token
      token::verify_token();

			// test empty datas - if just one is empty -> error
			$empty = false;

			// datas recived
			$short_name = ( empty($_POST['short_name']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['short_name']));
			$name = ( empty($_POST['name']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['name']));
			$descr = ( empty($_POST['descr']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['descr']));
			$theme_color = ( empty($_POST['theme_color']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['theme_color']));
			$bkg_color = ( empty($_POST['bkg_color']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['bkg_color']));
			$display = ( empty($_POST['display']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['display']));
			$start_url = ( empty($_POST['start_url']) ) ? $empty=true
			:(string) trim(htmlspecialchars($_POST['start_url']));

			// FOR (public/private web app)
			$for = ( empty($_POST['for'])
								|| ( $_POST['for'] != 'public'
										&& $_POST['for'] != 'private' ) )
			? exit('Bad datas ...')
			: (string) trim(htmlspecialchars($_POST['for']));

			// test empty for all
			if( $empty == true ){

						// error
						$tab = array( 'error' => tr::$TR['global_empty_fields'] );
						echo json_encode($tab, JSON_FORCE_OBJECT);
						exit;
			}

			// length of name and short name wae app
			if( iconv_strlen($short_name) > 100 || iconv_strlen($name) > 150 ){

					// error
					$tab = array( 'error' => tr::$TR['too_large_title_shop'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// length of description
			if( iconv_strlen($descr) > 300 ){

					// error
					$tab = array( 'error' => tr::$TR['too_large_descr_shop'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// bad format color
			$error_color = false;

			// test hexa string
			// remove # tag for test
			$colorCode = ltrim($theme_color, '#');

			// ctype_xdigit -> verify hexadecimal expression
			$theme_color =
			( !ctype_xdigit($colorCode)
				&& iconv_strlen($colorCode) > 6 )
			? $error_color=true : '#'.$colorCode;

			$colorCode = ltrim($bkg_color, '#');
			$bkg_color = ( !ctype_xdigit($colorCode)
				&& iconv_strlen($colorCode) > 6 )
			? $error_color=true : '#'.$colorCode;

			if( $error_color == true ){

					// error
					$tab = array( 'error' => tr::$TR['error_color_selected_hexa'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}


			// displays aviables
			$ARR_displays = array(
				'fullscreen',
				'standalone',
				'minimal-ui'
			);

			// bad display
			if( !in_array($display, $ARR_displays) ){

					// error
					$tab = array( 'error' => tr::$TR['bad_pwa_display'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// test toot long start url
			if( iconv_strlen($start_url) > 800 ){

					// error
					$tab = array( 'error' => tr::$TR['pwa_start_url_too_long'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}


			// test image square - REQUIRED ! + min size
			$tmp_name = trim(htmlspecialchars($_FILES['img']['tmp_name'][0]));

			// return an array of informtions about image
			$INFOS_img = getimagesize($tmp_name);

			// $INFOS_img[0] = width img in int. / $INFOS_img[1] = height img in int.
			if( (int) $INFOS_img[0] != (int) $INFOS_img[1] ){

					// error
					$tab = array( 'error' => tr::$TR['square_image_required'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}

			// PWA REQUIERE IMG min. WIDTH >= 512px
			if( (int) $INFOS_img[0] < 512 || (int) $INFOS_img[1] < 512 ){

					// error
					$tab = array( 'error' => tr::$TR['min_size_pwa_required'] );
					echo json_encode($tab, JSON_FORCE_OBJECT);
					exit;
			}
			// end test square + size

			// manage images
			$ARR_sizes = array(
				'web-app-48x48' => 48,
				'web-app-57x57' => 57,
				'web-app-60x60' => 60,
				'web-app-72x72' => 72,
				'web-app-76x76' => 76,
				'web-app-96x96' => 96,
				'web-app-114x114' => 114,
				'web-app-120x120' => 120,
				'web-app-144x144' => 144,
				'web-app-152x152' => 152,
				'web-app-180x180' => 180,
				'web-app-192x192' => 192,
				'web-app-256x256' => 256,
				'web-app-384x384' => 384,
				'web-app-512x512' => 512,
			);

			$dir_path = ( $for == 'public' )
			? ROOT
			: ROOT.'/'.ADMIN_FOLDER;

			// delete olds images with prefixers
			array_map('unlink', glob($dir_path.'/img/Web-App/web-app*') );

			// get just firs index name is common without prefix
			// img_recorder return array of names img recorded
			$common_name_imgs =
				tools::img_recorder( $dir_path.'/img/Web-App/', $ARR_sizes )[0];

			$id = ( $for == 'public' )
			? (string) time()
			: 'Backend-'.(string) time();

			// construct manifest
			$MANIFEST = array(
				'id' => 'Placido-Shop-PWA-'.$id,
				'name' => $name,
				'short_name' => $short_name,
				'description' => $descr,
				'icons' => array(),
				'theme_color' => $theme_color,
				'background_color' => $bkg_color,
				'display' => $display,
				'start_url' => $start_url,
				'orientation' => 'any'
			);


			$link_path = ( $for == 'public' )
			? '' : '/'.ADMIN_FOLDER;

			// loop contruct isons pwa manifest
			foreach( $ARR_sizes as $k => $v) {

					$MANIFEST['icons'][] =
					array( 	'src' => $link_path.'/img/Web-App/'.$k.'-'.$common_name_imgs,
									'sizes' => $v.'x'.$v,
									'type' => $_FILES['img']['type'][0],
									'purpose' => 'any' 	);
			}

			// add default png - this is required by browser
			// PS_LOGO-512.png
			$MANIFEST['icons'][] =
			array( 	'src' => $link_path.'/img/Web-App/PS_LOGO-512.png',
							'sizes' => '512x512',
							'type' => 'image/png',
							'purpose' => 'any' 	);

			// pass default img for render in form
			$MANIFEST['default_img'] = 'web-app-512x512-'.$common_name_imgs;

			// print_r( $MANIFEST );

			// get root of the FRONT manifest
			$webmanifest = ( $for == 'public' )
			? $dir_path.'/manifest.webmanifest'
			: $dir_path.'/manifest.webmanifest';

			$json_manifest = json_encode( $MANIFEST,
			JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION
			| JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

			if( file_put_contents($webmanifest, $json_manifest) ){

					// get fresh web-app settings
					// this return an array ( 'public'=>[], 'private'=>[] )
					$WEB_APP = web_app::return_web_app_settings();

					// success
					$tab = array( 'success' => tr::$TR['pwa_well_updated'],
												'web_app' => $WEB_APP  );

					echo json_encode($tab, JSON_NUMERIC_CHECK);
					exit;
			}

			// error
			$tab = array( 'error' => tr::$TR['error_pwa_record'] );
			echo json_encode($tab, JSON_FORCE_OBJECT);
			exit;


	}
	/**
	 * web_app::record_web_app_settings();
	 */



	/**
	 * web_app::return_web_app_settings();
	 *
	 * @return {array}  array of PWA
	 * 									settings for render input values
	 * cf. the file ROOT.'/manifest.webmanifest'
	 */
	public static function return_web_app_settings(){


			// PUBLIC
			// get root of the FONT manifest
			$webmanifest = ROOT.'/manifest.webmanifest';

			$json_manifest = file_get_contents($webmanifest);

			// on error / empty file
			if( !$json_manifest ){

					// return empty array
					return array();
			}

			// else .. decode manifest as array
			$PUBLIC_MANIFEST = json_decode($json_manifest, true);


			// PRIVATE
			// get root of the PRIVATE manifest
			$webmanifest = ROOT.'/'.ADMIN_FOLDER.'/manifest.webmanifest';

			$json_manifest = file_get_contents($webmanifest);

			// on error / empty file
			if( !$json_manifest ){

					// return empty array
					return array();
			}

			// else .. decode manifest as array
			$PRIVATE_MANIFEST = json_decode($json_manifest, true);


			// return array manifest
			return array(
				'public' => $PUBLIC_MANIFEST,
				'private' => $PRIVATE_MANIFEST
			);

	}
	/**
	 * web_app::return_web_app_settings();
	 */




}
// end class web_app::




?>
