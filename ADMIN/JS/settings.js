/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: admin.js
 *
 * $.deploy_settings_item();
 * $.change_admin_pass();
 * $.clear_set_admin_pass();
 * $.record_token_placido();
 * $.set_stripe_keys( context );
 * $.select_pay_mode( mode );
 * $.update_by_money();
 * $.compress_ressources();
 * $.use_compressed_ressources( use );
 * $.display_products( display );
 * $.record_api_settings();
 * $.update_mailbox( command );
 * $.confirm_switch_prod_mode();
 * $.switch_to_prod_mode();
 *
 */

// start jQuery
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({



  /**
   * $.deploy_settings_item( item );
   *
   * @param  {str} item suffix to work in icon and hidden content
   * for all settings items
   * @return {void}
   */
  deploy_settings_item : function( item ){


      // deploy details setting item
      if( $('.hidden_'+item+'').css('display') == 'none' ){

          // icon anim
          $('.ico_'+item+'')
          .addClass('fa-hand-point-down')
          .removeClass('fa-hand-point-right');

          // show
          $('.hidden_'+item+'')
          .show(200, 'linear');

      }
      else{
          // HIDE setting item

          // icon anim
          $('.ico_'+item+'')
          .addClass('fa-hand-point-right')
          .removeClass('fa-hand-point-down');

          // hide
          $('.hidden_'+item+'')
          .hide(300);

      }

  },
  /**
   * $.deploy_settings_item( item );
   */



  /**
   * $.change_admin_pass();
   *
   * @return {type}  description
   */
  change_admin_pass : function(){


      // block multiple clics
      $('#change_admin_pass').removeAttr('onclick');

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();
      // append command
      datas.append('set', 'change_admin_pass');
      datas.append('name', $('#name').val() );
      datas.append('mail', $('#mail').val() );
      datas.append('passw', $('#passw').val() );
      datas.append('token', $.o.user.token );

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


          // success
          if( data.success ){

              // renew user
              $.o.user = data.user;

              $.show_alert('success', data.success, false);

              // renew admin name
              $('#admin_name').text( $.o.user.name );

              // re-attr good value in fields
              $('#name').val( $.o.user.name );
              $('#mail').val( $.o.user.mail );

              // empty password field
              $('#passw').val('');

          } // error
          else{

              // error
              $.show_alert('warning', data.error, false);
          }

          // re-able to click
          $('#change_admin_pass').attr('onclick', '$.change_admin_pass();');

      });
      // end sender

  },
  /**
   * $.change_admin_pass();
   */



  /**
   * $.clear_set_admin_pass();
   *
   * @return {void}
   */
  clear_set_admin_pass(){

      $('#name').val( $.o.user.name );
      $('#passw').val('');
  },
  /**
   * $.clear_set_admin_pass();
   */



	/**
	 * $.record_token_placido();
	 *
	 * @return {json}  success/error
	 */
	record_token_placido : function(){


			// disable btn
			$('#btn_token_placido')
			.removeAttr('onclick')
			.append(`<span class="spinner">
			&nbsp;<i class="fas fa-circle-notch fa-spin fa-fw"></i></span>`);

			// set a FormData
			var formData = new FormData();

			// append command
			formData.append( 'set', 'record_token_placido' );

			// append token user
			formData.append( 'token', $.o.user.token );

			// append token Placido
			formData.append( 'token_placido', $('#token_placido_input').val() );

			// post datas - false on form - unbug event listener button
			$.sender(false, 'POST', 'index.php', formData, 'json',
			function(data){

					// succes
					if( data.success ){

							// assign value to token - same as server
							$.o.user.token_Placido = $('#token_placido_input').val();

							// state
							$('#token_placido_state').empty()
							.mustache('token_placido_state', $.o );

							$.show_alert('success', data.success, false);
					}

					// error
					if( data.error ){

							$.show_alert('warning', data.error, false);
					}

					// enable click btn
					$('#btn_token_placido')
					.attr('onclick', '$.record_token_placido();');

					// remove spinner
					$('.spinner').remove();

			});
			// end $.sender

	},
	/**
	 * $.record_token_placido();
	 */



  /**
   * $.set_stripe_keys( context );
   *
   * @param  {type} context  'test' / 'prod'
   * @return {type}         description
   */
  set_stripe_keys : function( context ){


      $('#btn_'+context+'_keys').removeAttr('onclick');

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();

      if( context == 'test' ){
        // append TEST keys
        datas.append('test_pub_key',  $('#test_pub_key').val() );
        datas.append('test_priv_key', $('#test_priv_key').val() );
      }
      else{
        // append PRODUCTION keys
        datas.append('prod_pub_key',  $('#prod_pub_key').val() );
        datas.append('prod_priv_key', $('#prod_priv_key').val() );
      }

      datas.append('context',  context);

      // user
      datas.append('token', $.o.user.token );

      // append command
      datas.append('set', 'set_stripe_keys');


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.show_alert('success', data.success, false);

              if( context == 'test' ){
                // test context
                $.o.shop.test_pub_key = data.test_pub_key;
                $.o.shop.test_priv_key = data.test_priv_key;
              }
              else{
                // production context -> it pass just true or false, not the key
                $.o.shop.prod_pub_key = data.prod_pub_key;
                $.o.shop.prod_priv_key = data.prod_priv_key;
              }

              // RE-OPEN VIEW SEETINGS
              $.open_vue('settings', event);

              // deploy item for see update
              $.deploy_settings_item('stripe_keys');

          } // error
          else{

              $.show_alert('warning', data.error, false);
          }

          // re-attr on click on btn
          $('#btn_'+context+'_keys').attr('onclick', "$.set_stripe_keys( '"+context+"' );");

      });
      // end sender

  },
  /**
   * $.set_stripe_keys( context );
   */



  /**
   * $.select_pay_mode( mode );
   *
   * @param  {str} mode  'by_money' / 'not_by_money'
   * @return {void}      check pay mode shop for buy by money or others payments
   */
  select_pay_mode : function( mode ){

      if( mode != 'by_money' && mode != 'not_by_money' ){
        return;
      }

      // remove checked for all
      $('label[for="by_money"] i, label[for="not_by_money"] i')
      .removeClass('fa-check-square')
      .addClass('fa-square');

      if( mode == 'by_money' ){

          $('label[for="by_money"] i')
          .addClass('fa-check-square')
          .removeClass('fa-square');

      }
      else{

          $('label[for="not_by_money"] i')
          .addClass('fa-check-square')
          .removeClass('fa-square');
      }

  },
  /**
   * $.select_pay_mode( mode );
   */



  /**
   * $.update_by_money();
   *
   * @return {type}  update shop for payments by money or others payments
   */
  update_by_money : function(){


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();

      // GET THE VALUE CHECKED -> 'by_money' / 'not_by_money'
      var val = $('input[name="by_money"]:checked').val();

      // VALUE
      datas.append('by_money', val );

      // user
      datas.append('token', $.o.user.token );

      // append command
      datas.append('set', 'update_by_money');

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.show_alert('success', data.success, false);

              // SET BY MONEY FOR RENDER CHECKBOX CHECKED IN TEMPLATE
              if( val == 'by_money' ){

                  $.o.shop.by_money = true;
              }
              else{
                  $.o.shop.by_money = false;
              }

              // RE-OPEN VIEW SEETINGS
              $.open_vue('settings', event);

              // deploy item for see update
              $.deploy_settings_item('pay_modalities');


          } // error
          else{

              $.show_alert('warning', data.error, false);
          }

      });
      // end sender

  },
  /**
   * $.update_by_money();
   */



	/**
	 * 	$.compress_ressources();
	 */
	compress_ressources : function(){


		// append a spinner to button
		$('#compress_ressources')
		.append(`&nbsp; <i class="spinner fa-circle-notch fa-fw fa-spin fas"></i>`);

		// post command
		$.post( 'index.php',
		{
			set: 'compress_ressources',
			token: $.o.user.token
		},
		function(data){

				if( data.success ){

						$.show_alert('success', $.o.tr.update_success, false);

						// re-init settings
						$.o.api_settings = data.api_settings;

						// update last compress date
						$('#COMPRESSED_DATE').text($.o.api_settings.COMPRESSED_DATE);
				}

				if( data.error ){

						$.show_alert('warning', data.error, false);
				}


				// remove spinner
				$('.spinner').remove();


		}, 'json');

	},
	/**
	 * 	$.compress_ressources();
	 */



	/**
	 * $.use_compressed_ressources( use );
	 *
	 * @param  {string} use	-> 'yes' / 'no'
	 * @return {json}   api_settings
	 */
	use_compressed_ressources : function( use ){

		$.post( 'index.php',
		{ set: 'use_compressed', use: use, token: $.o.user.token },
		function(data){

				if( data.success ){

						$.show_alert('success', $.o.tr.update_success, false);

						// re-init settings
						$.o.api_settings = data.api_settings;

						// SET COMPRESSED in view
						if( use == 'yes' ){

							$('#use_compressed_true').addClass('blue').removeClass('gray');
							$('#use_compressed_false').addClass('gray').removeClass('blue');
						}
						else {
							$('#use_compressed_false').addClass('blue').removeClass('gray');
							$('#use_compressed_true').addClass('gray').removeClass('blue');
						}

				}

				if( data.error ){

						$.show_alert('warning', data.error, false);
				}


		}, 'json');

	},
	/**
	 * $.use_compressed_ressources( use );
	 */



	/**
	 * $.display_products( display, event );
	 *
	 * @param  {string} display 'inline' / 'mozaic'
	 * @param  {event} 	event
	 * @return {void}   choose how to display products in view
	 */
	display_products : function(  display, event  ){

			if( display != 'inline' && display != 'mozaic' ){
				return;
			}

			// both
			$('label[for="display_mozaic"],label[for="display_inline"]')
			.removeClass('gree').addClass('blue');

			$('input#display_inline, input#display_mozaic')
			.removeAttr('checked');

			// display on line
			if( display == 'inline' ){

					// $('input#display_inline').attr('checked', true).prop('checked', true);

					$('label[for="display_inline"]')
					.removeClass('blue').addClass('gree');
			}
			else{

					// $('input#display_mozaic').attr('checked', true).prop('checked', true);

					$('label[for="display_mozaic"]')
					.removeClass('blue').addClass('gree');
			}

	},
	/**
	 * $.display_products( display, event );
	 */



	/**
	 * $.record_api_settings();
	 *
	 * @return {json}  api_settings
	 */
	record_api_settings : function(){


		// disable btn
		$('#record_api_settings').removeAttr('onclick')
		.append(`<span id="lil_spinner">&nbsp;<i
		class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);

		var form = document.getElementById('form_api_settings');
		var formData = new FormData(form);

		formData.append('set', 'record_api_settings' );
		formData.append('token', $.o.user.token );

		// remove img input - this is managed by $.obj.files
		formData.delete('img');

		// IF TOO MUCH IMGs -> one img for logo
		if( $.obj.files.length > 1 ){

				$.show_alert('warning', $.o.tr.one_img_for_logo, false);

				$('#record_api_settings').attr('onclick', '$.record_api_settings();');

				$('#lil_spinner').remove();

				return;
		}

		// if had files -> add files from obj.
		if( $.obj.files.length != 0 ){

				// add files to formData
				formData.append('img[]', $.obj.files[0]);
		}

		// test entries :
		// for (var variable of formData.entries() ) {
		//
		// 		console.log( 	variable );
		// }
		// return;

		// post datas - false on form - unbug event listener button
		$.sender(false, 'POST', 'index.php', formData, 'json',
		function(data){

				// success
				if( data.success ){


						// need to login again if ADMIN FOLDER change
						// test before re-init object
						if( $.o.api_settings.ADMIN_FOLDER
								!= data.api_settings.ADMIN_FOLDER ){

									// countdown to redirection
									var message = data.success+`<br>
										`+$.o.tr.administration_folder_modified;

									var timer_install = 5;

									$.show_alert( 'success',
									message+`<br><span class="xxlarge">
									`+timer_install+`</span>`, true );

									// countdown to redirection
									var myInterval = window.setInterval(function(){

											$('.toast-message')
											.html(message+`<br><span class="xxlarge">
											`+timer_install+`</span>` );

											timer_install--;

											// END OF TIMER
											if( timer_install < 0 ){

													window.clearInterval(myInterval);

													window.location.href =
														'https://'+$.o.host+'/'+data.api_settings.ADMIN_FOLDER;

											} // END OF TIMER

									}, 1000);
									// end countdown to redirection

									// STOP HERE
									return;
						}
						// end need to login again if ADMIN FOLDER change

						// render success message
						$.show_alert('success', data.success, false);

						// re-init datas object
						$.o.api_settings = data.api_settings;

						// render displaying type of products view on settings
						// 'inline'
						$.o.template.display_inline =
							( $.o.api_settings.DISPLAY_PRODUCTS == 'inline' ) ? true : false;

						// 'mozaic'
						$.o.template.display_mozaic =
							( $.o.api_settings.DISPLAY_PRODUCTS == 'mozaic' ) ? true : false;

				}
				// end success

				// error
				if( data.error ){

						$.show_alert('warning', data.error, false);
				}

				// enable btn
				$('#record_api_settings')
				.attr('onclick', '$.record_api_settings();');

				// remove spinner
				$('#lil_spinner').remove();


		});
		// end $.sender


	},
	/**
	 * $.record_api_settings();
	 */



	/**
	 * $.update_mailbox( command );
	 *
	 * @param  {string} command 'send' / 'clear'
	 * @return {json}   update datas Mailbox API
	 */
	update_mailbox : function( command ){


			// clear form
			if( command == 'clear' ){

					// reset form
					$('#form_mailbox')[0].reset();

					// exit here
					return;
			}

			// update maibox
			$('#update_mailbox')
			.removeAttr('onclick')
			.append(`<span class="spinner">
			&nbsp;<i class="fas fa-circle-notch fa-spin fa-fw"></i></span>`);

			// get datas with FormData
			var formData = new FormData( $('#form_mailbox')[0] );

			// append token user
			formData.append( 'token', $.o.user.token );

			// append command
			formData.append( 'set', 'update_mailbox' );

			// post datas - false on form - unbug event listener button
			$.sender('#form_mailbox', 'POST', 'index.php', formData, 'json',
			function(data){

					// succes
					if( data.success ){

							$.show_alert('success', data.success, false);

							// reset form on success
							$('#form_mailbox')[0].reset();
					}

					// error
					if( data.error ){

							$.show_alert('warning', data.error, false);
					}

					// enable click btn
					$('#update_mailbox')
					.attr('onclick', '$.update_mailbox("send")');

					// remove spinner
					$('.spinner').remove();

			});
			// end $.sender

	},
	/**
	 * $.update_mailbox( command );
	 */



  /**
   * $.confirm_switch_prod_mode();
   *
   * @return {html}		ask to confirm
	 * 									to switch to production mode
   */
  confirm_switch_prod_mode : function(){


      var html =
			`<p>`+$.o.tr.confirm_switch_prod_mode+`</p>
      <button class="btn deep-orange card round left"
      onclick="$.switch_to_prod_mode();" role="button">
      <i class="fa-sign-in-alt fas"></i>&nbsp; `+$.o.tr['confirm']+`</button>
      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr['abort']+`</button>`;

      $.show_alert('info', html, true);

  },
  /**
   * $.confirm_switch_prod_mode();
   */



	/**
	 * $.switch_to_prod_mode();
	 *
	 * @return {type}  description
	 */
	switch_to_prod_mode : function(){


			// disable btn
			$('#btn_prod_mode')
			.removeAttr('onclick')
			.append(`<span class="spinner">
			&nbsp;<i class="fas fa-circle-notch fa-spin fa-fw"></i></span>`);

			// set a FormData
			var formData = new FormData();

			// append command
			formData.append( 'set', 'switch_production_mode' );

			// append token user
			formData.append( 'token', $.o.user.token );

			// post datas - false on form - unbug event listener button
			$.sender(false, 'POST', 'index.php', formData, 'json',
			function(data){

					// succes
					if( data.success ){


							$.show_alert('success', data.success, false);

							// empty new sales array
							$.o.new_sales = [];

							// set nb new sales to 0
							$.o.template.nb_new_sales = 0;

							// re-init price to 0, well locally translated
							var lang_locale = $.o.api_settings.LANG_LOCALE.replace('_','-');

							$.o.template.total_amount_shop =
								new Intl.NumberFormat( lang_locale,
									{ style: 'currency',
										currency: $.o.api_settings.CURRENCY_ISO }).format(0);

							// re-init stats count to 0
							$.o.stats.today_nb_visits = '0 '+$.o.tr.visits;

							// empty array archives
							$.o.archives = [];

					}

					// error
					if( data.error ){

							$.show_alert('warning', data.error, false);
					}

					// enable click btn
					$('#btn_prod_mode')
					.attr('onclick', '$.switch_to_prod_mode();');

					// remove spinner
					$('.spinner').remove();

			});
			// end $.sender

	},
	/**
	 * $.switch_to_prod_mode();
	 */




});
// END EXTEND

});
// END jQuery
