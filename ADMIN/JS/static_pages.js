/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2022-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: static_pages.js
 *
 * $.record_static_page();
 * $.empty_form_new_page();
 * $.ask_to_suppr_static_page( page_id );
 * $.suppr_static_page( page_id );
 * $.modify_static_page( page_id );
 * $.show_infos_static_page();
 * $.show_form_static_page();
 * $.edit_static_page( event, page_id );
 * $.save_edit_static_page();
 *
 */

// start jQuery
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
$.extend({



	/**
	 * $.record_static_page();
	 *
	 * @return {json}  record a new satic page
	 */
	record_static_page : function(){

		// remove onclick
		$('#record_static_page').removeAttr('onclick');

		// show spinner
		$('.spinner').toggle('hide');

		$.post('index.php',
			{ set: 'record_static_page',
			  token: $.o.user.token,
			  page_title : $('#page_title').val(),
			 	page_url : $('#page_url').val()
			},
			function(data){

				if( data.success ){

						// show success alert
						$.show_alert('success', data.success, false);

						// empty form
						$.empty_form_new_page();

						// renew static_pages obj
						$.o.static_pages = data.static_pages;

						// re-init #static_list
						$('#static_list').empty()
						.mustache('static_list', $.o );

						// scroll to new element
						$.scroll_to_elem('#static_page-'+data.page_id+'', event );

				}
				// end success

				// error
				if( data.error ){

						$.show_alert('warning', data.error, false);
				}

				// for both cases : hide spinner
				$('.spinner').toggle('hide');

				// re-attr onclick
				$('#record_static_page').attr('onclick', '$.record_static_page()');


			}, 'json');
			// end post

	},
	/**
	 * $.record_static_page();
	 */



  /**
   * $.empty_form_new_page();
   *
   * @return {void}     empty form new page
   */
  empty_form_new_page : function(){

      $('#form_new_page')[0].reset();
  },
  /**
   * $.empty_form_new_page( ip );
   */



	/**
	 *  $.ask_to_suppr_static_page( page_id, page_title );
	 *
	 * @param  {int}   page_id 			id of a static page
	 * @param  {str}   page_title 	title of a static page
	 * @return {void}  ask to confirm suppress a page
	 */
	ask_to_suppr_static_page : function( page_id, page_title ){


      var html =
			`<p>`+$.o.tr.confirm_suppr_static_page+`&nbsp;:
			<br>`+page_title+`
			<br>`+$.o.tr.confirm+`
			</p>
      <button class="btn deep-orange card round left"
      onclick="$.suppr_static_page(`+page_id+`);" role="button">
      <i class="fa-trash-alt far"></i>&nbsp; `+$.o.tr.suppr+`</button>
      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr.abort+`</button>`;

      $.show_alert('info', html, true);

	},
  /**
   * $.ask_to_suppr_static_page( page_id, page_title );
   */



	/**
	 *  $.suppr_static_page( page_id );
	 *
	 * @param  {int} page_id 	id of a static page
	 * @return {json}    			new array obj static_pages
	 */
	suppr_static_page : function( page_id ){

		// disable button
		$('#suppr_static-'+page_id+'').prop('disabled', true);

		// show process bar
		$.show_process();

		$.post('index.php',
			{ set: 'suppr_static_page',
				token: $.o.user.token,
				page_id: page_id,
			},
			function(data){

				if( data.success ){

						// hide process bar
						$.show_process('end');

						// success
						$.show_alert('success', data.success, false);

						// renew static_pages obj
						$.o.static_pages = data.static_pages;

						// re-init #static_list
						$('#static_list').empty()
						.mustache('static_list', $.o );

				}
				// end success

				// error
				if( data.error ){

						// error
						$.show_alert('warning', data.error, false);
				}

				// re-attr onclick button
				$('#suppr_static-'+page_id+'').prop('disabled', false);

			}, 'json');
			// end post

	},
	/**
	 *  $.suppr_static_page( page_id );
	 */



	/**
	 *  $.ask_to_modif_static_page( page_id, page_title );
	 *
	 * @param  {int}   page_id 			id of a static page
	 * @param  {str}   page_title 	title of a static page
	 * @return {void}  confirm modify a page
	 */
	ask_to_modif_static_page : function( page_id, page_title ){


      var html =
			`<p>`+$.o.tr.confirm_modif_static_page+`&nbsp;:
			<br>`+page_title+`
			<br>`+$.o.tr.confirm+`
			</p>
      <button class="btn blue card round left"
      onclick="$.modify_static_page(`+page_id+`);" role="button">
      <i class="fa-sign-in-alt fas"></i>&nbsp; `+$.o.tr.modify+`</button>
      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr.abort+`</button>`;

      $.show_alert('info', html, true);

	},
  /**
   * $.ask_to_suppr_static_page( page_id, page_title );
   */



	/**
	 * $.modify_static_page( page_id );
	 *
	 * @param  {int} 	page_id 	id of page to modif
	 * @return {json}         	new array of static pages
	 */
	modify_static_page : function( page_id ){


		// test if nothing change -> not run the server
		// get the object of page by id
		var STATIC_page = $.o.static_pages.find( item => item.page_id === page_id);

		// bad request
		if( STATIC_page  === 'undefined' ){

				$.show_alert('warning', $.o.tr.page_not_found, false);
				return;
		}

		// get inputs values
		var page_title = $('input[name="page_title-'+page_id+'"]').val();
		var page_url = $('input[name="page_url-'+page_id+'"]').val();

		// return here if nothing change
		if( page_title == STATIC_page.page_title
				&& page_url == STATIC_page.page_url   ){

					// console.log('nothing change!');
					return;
		}
		// end nothing change


		// disable button
		$('#modify_static-'+page_id+'').removeAttr('onclick');

		// show process bar
		$.show_process();


		$.post('index.php',
			{ set: 'modify_static_page',
			  token: $.o.user.token,
				page_id: page_id,
			  page_title : page_title,
			 	page_url : page_url
			},
			function(data){

				if( data.success ){

						// show success alert
						$.show_alert('success', data.success, false);

						// hide process bar
						$.show_process('end');

						// renew static_pages obj
						$.o.static_pages = data.static_pages;

						// re-init #static_list
						$('#static_list').empty()
						.mustache('static_list', $.o );

				}
				// end success

				// error
				if( data.error ){

						$.show_alert('warning', data.error, false);
				}

				// re-attr onclick
				$('#modify_static-'+page_id+'')
				.attr('onclick', '$.ask_to_modif_static_page( '+page_id+', "'+page_title+'" );');

			}, 'json');
			// end post

	},
	/**
	 * $.modify_static_page( page_id );
	 */



	/**
	 * $.show_infos_static_page();
	 *
	 * show/hide infos about static page management
	 */
	show_infos_static_page : function(){

			$('#info_text').toggle('hide');
	},
	/**
	 * $.show_infos_static_page();
	 */



	/**
	 * $.show_form_static_page();
	 *
	 * @return {void}  show/hide the form to add a static page
	 */
	show_form_static_page : function(){

			$('#form_new_page').toggle('hide');

			$('#open_form_new_page')
			.toggleClass(['fa-hand-point-right', 'fa-hand-point-down']);
	},
	/**
	 * $.show_form_static_page();
	 */



  /**
   * $.edit_static_page( event, page_id );
   *
   * @param  {event} event
   * @param  {int}  page_id  id of page to edit
   * @return {html} trumbowyg page editor
   */
  edit_static_page : function( event, page_id ){


      $(event.currentTarget)
      .append(`<span class="spinner">
			&nbsp;<i class="fas fa-circle-notch fa-spin fa-fw"></i></span>`);

      let STATIC_page = $.o.static_pages.find( item => item.page_id === page_id);

      // construct obj to post
      let Obj = {
        set: 'edit_static_page',
        token: $.o.user.token,
        page_url: STATIC_page.page_url,
        lang: $.o.api_settings.LANG_BACK
      };

      $.post('index.php', Obj, function(data){

          // success
          if( data.success ){

              // get CSS and JS scripts for trumbowyg
              // put CSS on <head> and append scritps to <body>
              $.getScript( 'JS/trumbowyg_loader.js', function(){

                  let Copy_object_placido = {
                    tr: {...$.o.tr},
                    STATIC_page: {...STATIC_page},
                    host: $.o.api_settings.HOST
                  };

                  $('#page_gen').empty()
                  .mustache('page_editor', Copy_object_placido );

                  // add lang
                  if( data.lang != 'default' ){

                      $('body').append(`
          							<script type="text/javascript"
                        src="JS/apps/trumbowyg/dist/langs/`+data.lang+`.min.js"></script>
          						`);
                  }

                  // init. object of trumbowyg options
                  $.getScript( 'JS/trumbowyg_init.js', function(){

                      // launch editor -> launch_trumbowyg() see trumbowyg_init.js
                      launch_trumbowyg('#page_editor', data.lang);

                      // empty trumbowyg editor with html page content
                      $('#page_editor').trumbowyg('html', data.html);

                  });
              });
          }
          // end success

          // error
          if( data.error ){

              $.show_alert('warning', data.error, false);
          }

          // remove spinner
          $('.spinner').remove();

      }, 'json');
      // end post
  },
  /**
   * $.edit_static_page( event, page_id );
   */



  /**
   * $.save_edit_static_page( event, page_id );
   *
   * @param  {event} event
   * @param  {int}  page_id  id of page to record
   * @return {json}  record HTML static page
   */
  save_edit_static_page : function( event, page_id ){


      $(event.currentTarget)
      .append(`<span class="spinner">
			&nbsp;<i class="fas fa-circle-notch fa-spin fa-fw"></i></span>`);

      let STATIC_page = $.o.static_pages.find( item => item.page_id === page_id);

      // construct obj to post
      let Obj = {
        set: 'record_edit_static_page',
        token: $.o.user.token,
        page_url: STATIC_page.page_url,
        html: $('#page_editor').trumbowyg('html')
      };

      $.post('index.php', Obj, function(data){

          // success
          if( data.success ){

              // success message
              $.show_alert('success', $.o.tr.success_edit_page, false);
          }

          // error
          if( data.error ){

              $.show_alert('success', data.error, true);
          }

          // remove spinner
          $('.spinner').remove();


      }, 'json');
      // end post
  },
  /**
   * $.save_edit_static_page( page_id );
   */



});
// end extend

});
// end jQuery
