/**
 * PlACIDO-SHOP AUTO-UPDATE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	update.js
 *
 * $.check_api_update();
 * $.ask_to_update( version );
 * $.update_to_version( version );
 * $.see_infos_files_not_updated(event);
 * $.prevent_file_update( file_path );
 * $.open_customize_update();
 * $.download_lang( lang , event );
 * $.see_infos_update( version );
 *
 */


// start jQuery
$(function(){

// extend
$.extend({


	/**
	 * $.check_api_update();
	 *
	 * @return {type}  description
	 */
	check_api_update : function(){


			// disable btn
			$('#check_api_update').removeAttr('onclick')
			.append(`<span id="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);

			// make an object of datas to send
			var Obj = {
					command : 'check_update',
					host : $.o.host,
					version_used : $.o.api_settings.VERSION,
					token_Placido : $.o.user.token_Placido
			};

			// Post request to Placio-Shop Update center
			$.post('https://update.placido-shop.com', Obj, function(data){

				// console.log( data.downloads );

				// success
				if( data.success ){

						if( typeof $.o.downloads === 'undefined' ){

								$.o.downloads = {
									translations: {},
									versions: [],
									update_checked: false,
								};
						}

						// init or re-inti some prt of object
						$.o.downloads.translations = data.downloads.translations;
						$.o.downloads.versions = data.downloads.versions;

						// pass checked info
						$.o.downloads.update_checked = true;

						// get string version of last version
						var last_version = $.o.downloads.versions[0].version;

						// if use latest version
						if( last_version == $.o.api_settings.VERSION ){

								// pass version user to true
								$.o.downloads.version_updated = true;

								$('#check_version_render')
								.empty()
								.html(`<span class="text-cyan"><i class="fa-check fas"></i>&nbsp; `
								+$.o.tr.version_up_to_date+`</span>`)
								.show();
						}
						else{

								// pass version user to false
								$.o.downloads.version_updated = false;

								// advert use not last version
								$('#check_version_render')
								.empty()
								.html(`<span class="text-orange"><i class="fa-exclamation-triangle fas"></i>&nbsp; `
								+$.o.tr.version_not_up_to_date+`</span>`)
								.show();
						}

						// put versions aviables
						$('#versions_aviables').empty()
						.mustache('versions_aviables', $.o );

						// put translations aviables
						$('#translations_aviables').empty()
						.mustache('translations_aviables', $.o );

				}
				// end success


				// error
				if( data.error ){

						$.show_alert( 'warning', data.error, false );
				}

				// remove spinner
				$('#lil_spinner').remove();

				// re-attr onclick
				$('#check_api_update').attr('onclick', '$.check_api_update();');

			}, 'json');
			// end Post request

	},
	/**
	 * end $.check_api_update();
	 */



	/**
	 * $.ask_to_update( version );
	 *
	 * @param  {string} version 	version to install
	 * @return {html}   open a pop-up to ask
	 * 									for validation and alert for backup data
	 */
	ask_to_update : function( version ){


			// check is use the latest version before update
			if( version == $.o.api_settings.VERSION ){

					// show message already updated
					$.show_alert( 'info', $.o.tr.already_have_last_version, false );

					// stop here
					return;
			}

			// Message warning update
		  var html =
			`<p><i class="fa-exclamation-triangle fa-fw fas text-orange"></i>&nbsp;
			 `+$.o.tr.confirm_update+`</p>
			<p>`+$.o.tr.confirm+`</p>
      <button class="btn gree card round left"
      onclick="$.update_to_version('`+version+`');" role="button">
      <i class="fa-sign-in-alt fas"></i>&nbsp; `+$.o.tr.validate+`</button>
      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr.abort+`</button>`;

			// ask to confirm udpate
      $.show_alert('info', html, true);

	},
	/**
	 * $.ask_to_update( version, event );
	 */



	/**
	 * $.update_to_version( version );
	 *
	 * @param  {string} version 	version ref
	 * @return {zip File}        fetch a version and install it
	 */
	update_to_version : function( version ){


			// disable btn
			$('[data-version="'+version+'"]').attr('disabled',true);

			// add spinner
			$('[data-version="'+version+'"]').append(`<span class="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i></span>`);


			// make an object of datas to send
			var Obj = {
					command : 'install_version',
					host : $.o.host,
					version : version,
					token : $.o.user.token,
					token_Placido : $.o.user.token_Placido
			};

			// Post request to Placio-Shop Update center
			$.post('/MODULES/Auto-Update/PHP/update.php',
				Obj, function(data){

				var timer;

				// success
				if( data.success ){

						$.show_alert( 'success', $.o.tr.app_updated_successfully, true );

						// redirection to login admin page
						timer = window.setTimeout( function(){

								window.clearTimeout(timer);

								// relocation to admin login
								$.log_out();

						}, 4000 );

				}
				// end success


				// error
				if( data.error ){

						$.show_alert( 'warning', data.error, false );
				}


				// remove spinner
				$('.lil_spinner').remove();

				// re-attr onclick
				$('.btn[disabled="disabled"]').removeAttr('disabled');


			}, 'json');
			// end Post request

	},
	/**
	 * $.update_to_version( version );
	 */



	/**
	 * $.see_infos_files_not_updated();
	 *
	 * @return {html}   See files and folders
	 * 									not updated by default in a pop-up
	 */
	see_infos_files_not_updated : function(){


			// not re-load list if alredy loaded -> save server ressources
			if( typeof $.o.downloads.api_files_not_updated !== 'undefined' ){

					var html = $.Mustache.render('files_not_updated', $.o);

					// show list of API files not updated by default
					$.show_alert('info', html, true);

					// stop here
					return;
			}
			// end not re-load list


			// disable btn
			$('#see_infos_files_not_updated')
			.attr('disabled', true)
			.append(`<span class="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);


			// make an object of datas to send
			var Obj = {
					command : 'infos_files_not_updated',
					host : $.o.host,
					token_Placido : $.o.user.token_Placido
			};

			// Post request to Placio-Shop Update center
			$.post('https://update.placido-shop.com',
			Obj, function(data){

				// success
				if( data.success ){

						// create object downloads if not created
						if( typeof $.o.downloads === 'undefined' ){

								$.o.downloads = {
									api_files_not_updated : []
								};
						}

						// update or init. object
						$.o.downloads.api_files_not_updated = data.api_files_not_updated;

						var html = $.Mustache.render('files_not_updated', $.o);

						// show list of API files not updated by default
						$.show_alert('info', html, true);

				}
				// end success


				// error
				if( data.error ){

						$.show_alert( 'warning', data.error, false );
				}

				// remove spinner
				$('.lil_spinner').remove();

				// re-attr onclick
				$('#see_infos_files_not_updated').removeAttr('disabled');


			}, 'json');
			// end Post request

	},
	/**
	 * $.see_infos_files_not_updated();
	 */



	/**
	 * $.prevent_file_update( file_path );
	 *
	 * @param  {type} file_path 'add' / 'fetch' OR file path or folder path to remove
	 * 													from the list of excluded file to update
	 * @return {json}      downloads.not_upload_list
	 */
	prevent_file_update : function( file_path ){


			var file, execute;

			// add a file
			if( file_path == 'add' ){

					file = $('#not_up_this').val();

					// pass a command to do 'add' or 'remove'
					execute = 'add';
			}
			else if( file_path == 'fetch' ){

					file = 'fetch';

					// pass a command to do 'add' or 'remove'
					execute = 'fetch';
			}
			else {

					// remove a file
					file = file_path;

					execute = 'remove';
			}

			// test if empty
			if( file.trim() == '' || file.length == 0 ){

					$.show_alert('info', $.o.tr.enter_a_file_name, false);
					// stop here
					return;
			}


			// Post to server
			var Obj = {
				command : 'add_remove_to_update',
				token : $.o.user.token,
				file : file,
				execute : execute
			};

			// Post request to Placio-Shop Update center
			$.post('/MODULES/Auto-Update/PHP/update.php',
				Obj, function(data){


						// success
						if( data.success ){

								// create object downloads if not created
								if( typeof $.o.downloads === 'undefined' ){

										$.o.downloads = {
											not_upload_list : []
										};
								}

								// re-init object
								$.o.downloads.not_upload_list = data.not_upload_list;

								// show list
								$('#not_upload_list').empty()
								.mustache('not_upload_list', $.o);

								// manage empty / full list
								if( $.o.downloads.not_upload_list.length == 0 ){

										$('.not_excluded_file').show();
								}
								else{
										$('.not_excluded_file').hide();
								}

								// empty field - on 'add' context
								if( execute == 'add' ){

										$('#not_up_this').val('');
								}

						}
						// end success

						// error
						if( data.error ){

								$.show_alert('warning', data.error, true);
						}


			},'json');
			// end POST
	},
	/**
	 * $.prevent_file_update( file_path );
	 */



	/**
	 * $.open_customize_update();
	 *
	 * @return {html}  fetch excluded files for updtae and
	 * 									show field and lis
	 */
	open_customize_update : function(){

			// if field + list excluded files for upload
			// -> SHOW
			if( $('#custom_update').is(':visible') == false ){

					// fetch fresh list is object is not defined
					if( typeof $.o.downloads === 'undefined'
							|| typeof $.o.downloads.not_upload_list === 'undefined' ){

							// with 'fetch' in param. fetch list of excluded files
							// this show the list reurned by server
							$.prevent_file_update( 'fetch' );
					}

					// manage icon button
					$('.ico_open_customize')
					.removeClass('fa-hand-point-right')
					.addClass('fa-hand-point-down');

					// show block
					$('#custom_update').css('display','block');

					return;
			}
			else{
					// -> HIDE
					// manage icon button
					$('.ico_open_customize')
					.removeClass('fa-hand-point-down')
					.addClass('fa-hand-point-right');

					// hide block
					$('#custom_update').css('display','none');
			}

	},
	/**
	 * $.open_customize_update();
	 */



	/**
	 * $.download_lang( lang , for_interface, event );
	 *
	 * @param  {string}  lang          	'lang.txt'
	 * @param  {string}  for_interface 	'back' / 'front'
	 * @param  {event}   event
	 * @return {json}    download a lang for back-end or front-end
	 */
	download_lang : function( lang, for_interface, event ){


			// disable btn
			$(event.currentTarget).attr('disabled',true);

			// add spinner
			$(event.currentTarget).append(`<span class="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);


			// make an object of datas to send
			var Obj = {
					command : 'download_lang',
					lang : lang,
					for_interface : for_interface,
					host : $.o.host,
					token : $.o.user.token,
					token_Placido : $.o.user.token_Placido
			};

			// Post request to Placio-Shop Update center
			$.post('/MODULES/Auto-Update/PHP/update.php',
				Obj, function(data){

					// success
					if( data.success ){

							$.show_alert( 'success', $.o.tr.lang_updated_successfully, true );

					}
					// end success


					// error
					if( data.error ){

							$.show_alert( 'warning', data.error, true );
					}


					// remove spinner
					$('.lil_spinner').remove();

					// re-attr onclick
					$('.btn[disabled="disabled"]').removeAttr('disabled');


			}, 'json');
			// end Post request


	},
	/**
	 * $.download_lang( lang , for_interface, event );
	 */



	/**
	 * $.see_infos_update( version );
	 *
	 * @param  {string} version version to get infos
	 * @return {text/html}      infos for this version
	 */
	see_infos_update : function( version ){


			// get the good info text
			for (var i = 0; i < $.o.downloads.versions.length; i++) {
					if( $.o.downloads.versions[i].version == version ){
							break;
					}
			}

			// manage empty / full infos text
			var infos =
				( typeof $.o.downloads.versions[i].infos != 'undefined'
					&& $.o.downloads.versions[i].infos.trim() != '' )
				? $.o.downloads.versions[i].infos : $.o.tr.no_infos_message;

			// show infos
			$('#modal_content').empty()
			.html( `<div class="large padding-24 padding-large">
			<h3 class="border-bottom border-gray text-gray">
			<i class="fas fa-info-circle"></i>&nbsp; `+$.o.tr.infos_title+`</h3>
			<p lang="en">`+infos+`</p>
			</div>` );

			$('#modal').show();

	},
	/**
	 * $.see_infos_update( version );
	 */



});
// end extend

});
// end jQuery
