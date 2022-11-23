/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: static_pages.js
 *
 * $.record_static_page();
 * $.empty_form_new_page();
 * $.ask_to_suppr_static_page( page_id );
 * $.suppr_static_page( page_id );
 * $.modify_static_page( page_id );
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



});
// end extend

});
// end jQuery
