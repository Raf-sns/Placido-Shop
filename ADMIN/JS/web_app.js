/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: web_app.js
 *
 * $.deploy_web_app_item( div );
 * $.init_img_web_app();
 * $.record_web_app();
 *
 */

// start jQuery
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
$.extend({



	/**
	 * $.deploy_web_app_item( div );
	 *
	 * @param  {string} div '#public_web_app' / '#management_web_app'
	 * @return {html}     	put form in partial page
	 */
	deploy_web_app_item : function( div ){


			$('.icon_pwa').removeClass('fa-hand-point-down')
			.addClass('fa-hand-point-right');

			// close on click opened item
			if( $(div).hasClass('show') == true ){

					$(div).removeClass('show').addClass('hide').empty();
					return;
			}

			// empty both
			$('#public_web_app')
			.removeClass('show').addClass('hide').empty();

			$('#management_web_app')
			.removeClass('show').addClass('hide').empty();


			// show partial and pass datas
			$.o.web_app.formDatas = ( div == '#public_web_app' )
			? JSON.parse(JSON.stringify($.o.web_app.public))
			: JSON.parse(JSON.stringify($.o.web_app.private));

			// public / private settings ?
			$.o.web_app.formDatas.public = ( div == '#public_web_app' )
			? true
			: false;

			// partial already loaded
			$(div).mustache('form_public_web_app', $.o ).addClass('show');

			$.init_img_web_app();

			// scroll to title
			$.scroll_to_elem(div+'_title', event);

			// toggle icon to bottom
			$(div+'_title .icon_pwa')
			.toggleClass(['fa-hand-point-right', 'fa-hand-point-down']);

	},
	/**
	 * $.deploy_web_app_item( div );
	 */


	/**
	 * $.init_img_web_app();
	 *
	 * @return {img}  show the default img of web app
	 */
	init_img_web_app : function(){

			// App image by default
			var Img_App = 'PS_LOGO-512.png';

			// select the display state already registered + get img alr. selected
			if( typeof $.o.web_app != 'undefined'
					&& $.o.web_app.length != 0 ){

					$('select[name="display"] option[value="'+$.o.web_app.formDatas.display+'"]')
					.attr('selected', true);

					Img_App = $.o.web_app.formDatas.default_img;
			}

			// prepa tab FILES - clear if $.obj.files
      if( $.obj.files.length != 0 ){

          delete $.obj.files;
          $.obj = { files : []  };
          $.index_box_img = 0;
      }

			// img is on admin folder ?
			var admin = ( $.o.web_app.formDatas.public == true )
			? '' : '/'+$.o.api_settings.ADMIN_FOLDER;

			var src =
			'https://'+$.o.host+admin+'/img/Web-App/'+Img_App+'';

      // load img
      $.loadXHR(src).then(function(blob){

          var file = new File([blob], Img_App, blob);
					// console.log( file );

          // insert in obj files à son index
          $.obj.files.splice( 0, 0 , file );
          // console.log( $.obj.files );
          $.index_box_img = 0;

          $.show_img_selected( $.index_box_img, src, $.obj.files[0].name, 'check-', 'checked' );

      });

			// manage render color + color text
			$('input[name="theme_color"], input[name="bkg_color"]').on('input', function(){

					// render background color
					$('label[for="'+$(this).attr('name')+'"] .pwa_color_render')
					.css('background', $(this).val() );

					// render text color
					$('label[for="'+$(this).attr('name')+'"] .pwa_color_text').text( $(this).val() );

			});

			// assing start_url path
			var start_url = ( $.o.web_app.formDatas.public == true )
			? '/' : '/'+$.o.api_settings.ADMIN_FOLDER+'/';

			$('input[name="start_url"]').val(start_url);

	},
	/**
	 * $.init_img_web_app();
	 */



	/**
	 * $.record_web_app();
	 *
	 * @return {json}  record web application settings and return a new object web_app
	 */
	record_web_app : function(){


			// test image
			var intrinsic_img = document.querySelector('.box_img img');
			var img_W = intrinsic_img.naturalWidth;
			var img_H = intrinsic_img.naturalHeight;
			// console.log(img_W);
			// console.log(img_H);

			// not square image !
			if( img_W != img_H ){

					$.show_alert('warning', $.o.tr.square_image_required, true);
					return;
			}

			if( img_W < 512 || img_H < 512 ){

					$.show_alert('warning', $.o.tr.min_size_pwa_required, true);
					return;
			}


			// disable btn
			$('#record_web_app')
			.removeAttr('onclick')
			.append(`<span class="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);


			var form = document.getElementById('form_public_web_app');

			var formData = new FormData(form);

			// remove img input - this is managed by $.obj.files
			formData.delete('img');

			// IF TOO MUCH IMGs -> one img for logo
			if( $.obj.files.length > 1 ){

					$.show_alert('warning', $.o.tr.one_img_for_logo, false);

					$('#record_web_app').attr('onclick', '$.record_web_app();');

					$('.lil_spinner').remove();

					return;
			}

			// if had files -> add files from obj.
			if( $.obj.files.length != 0 ){

					// add files to formData
					formData.append('img[]', $.obj.files[0]);
			}

			// append command
			formData.append('set', 'record_web_app');
			// append token user
			formData.append('token', $.o.user.token);

			var public_web_app = false;

			// append for what web app
			if( $.o.web_app.formDatas.public == true ){

					formData.append('for', 'public');
					public_web_app = true;
			}
			else{

					formData.append('for', 'private');
			}

			// post datas - false on form - unbug event listener button
			$.sender(false, 'POST', 'index.php', formData, 'json',
				function(data){


					// success
					if( data.success ){

							$.show_alert('success', data.success, false);

							// re-init object - this erase web_app.formData
							$.o.web_app = data.web_app;

							// re-init object formData if need to udpate same object
							// already open
							$.o.web_app.formDatas = ( public_web_app == true )
							? JSON.parse(JSON.stringify($.o.web_app.public))
							: JSON.parse(JSON.stringify($.o.web_app.private));

					}
					// end success

					// error
					if( data.error ){

							$.show_alert('warning', data.error, false);
					}
					// end error

					// remove spinner
					$('.lil_spinner').remove();

					//re-attr onclick
					$('#record_web_app').attr('onclick', '$.record_web_app();');


			});
			// end $.sender


	},
	/**
	 * $.record_web_app();
	 */




});
// end extend

});
// end jQuery
