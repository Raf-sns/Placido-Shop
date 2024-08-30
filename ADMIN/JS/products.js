/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name: products.js
 *
 * PRODUCTS :
 *
 * $.add_product( event, modif, id );
 * $.ask_to_suppr_product( event, id );
 * $.suppr_product( event, id );
 * $.open_modif_product( id );
 * $.product_on_off_line( val, event );
 * $.check_on_off_line( event );
 * $.open_cats_selector_modif();
 * $.set_cat_product( prod_id, cat_id );
 * $.select_this_cat( cat_id );
 * $.check_mode( for_input,  event );
 * $.calcul_price_ttc();
 * $.add_img_prod();
 * $.clear_add_product();
 *
 * SORTING :
 *
 * $.sort_stock_ad();
 * $.search_str_in_products();
 * $.empty_search_user_products();
 * $.search_products_by_cat();
 * $.open_cats_selector_all();
 * $.search_by_quant();
 * $.search_by_on_line( state, event );
 *
 * SLIDER :
 * $.init_featured_prods();
 * $.select_for_featured_prods( id );
 * $.move_prod( id,  dir );
 * $.record_featured_prods();
 * $.choice_for_slider( for_item, enable_val );
 * $.record_slider_settings();
 *
 */

// start jQuery
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * $.add_product( event, modif, id );
   *
   * @param  {event} event     description
   * @param  {str}   modif     'modif'
   * @param  {int}   id        product id in modification case
   * renew $.o.products
   *
   */
  add_product: function(e, modif, id){

      e.preventDefault();

  		// disabled button sub
  		$('#sub_set_prod').prop( "disabled", true )
      .removeAttr('onclick');

      // get form datas
      var form = $('form#add_product_form')[0];

  		// create form data for AJAX POST
  		var formData = new FormData(form);

      // append token
      formData.append('token', $.o.user.token);

  		// append command
  		formData.append('set', 'add_product');

      // if modif add command modif and id product
      if( modif == 'modif' ){

          formData.append('modif', 'modif');
          formData.append('prod_id', id);
      }

      // REMOVE HIDDEN INPUT FILE
      formData.delete('img');

  		// add files from obj.
  		$.obj.files.forEach(function(file, kf){

  				// add files to formData
  				formData.append('img[]', $.obj.files[kf]);
  		});

      // LOADER
      var html = `
      <p class="center margin">
        <i class="fa-5x fa-circle-notch fa-spin fas text-light-gray"></i>
      </p>
      <p class="center mrg0 text-light-gray xlarge">
        <span id="percent"></span>
      </p>
      <div id="charger"
      style="width:0px; height: 4px; position: absolute; bottom: 5px; background: #327a8f;"></div>`;

      // SHOW LOADER
      $.show_alert('info', html, true);

  		// send new product -> 'set' => 'add_product' directly in hidden html templ.
  		$.ajax({
        // xhr watcher for render loading process in percent to view
        xhr: function(){

            var xhr = new window.XMLHttpRequest();

            // Upload progress
            xhr.upload.addEventListener("progress", function(render){

                // if in progress
                if( render.lengthComputable ){
                	  // calcul percent
                	  var divis = Math.floor(render.loaded / render.total *100);
                		var percent = divis+'%';
                		//Do something with upload progress
                		$('#percent').text(percent);
                    $('#charger').css('width', divis+'px');
                }

            }, false); // end xhr.upload.addEventListener

            return xhr;
        }, // end xhr watcher
        // Ajax settings
  			url : 'index.php',
  			method : 'POST',
  			data : formData,
  			contentType : false,
  			cache : false,
  			processData : false,
  			dataType : 'json'
        }).done( function(data){

            // success
            if(data.success){

                $.show_alert('success', data.success, false);

                // RE INIT OBJ PRODUCTS
                $.o.products = {};
                $.o.products = data.products;

								// re-adjust nb products in template
								$.o.template.nb_products = $.o.products.length;

								// MODIF CASE
                if( modif == 'modif' ){

                    // CLOSE MODAL MODIF
                    $.close_modal(); // re-init $.obj.files

                    // manage VIEW by pagination
                    $.pagination_init( 'products' );

                }
                else{  //  RECORD CASE

                    // here page is not on modal, just reset form
                    // for prepare new entry
                    // reset form add product - reset $.obj.files
                    $.clear_add_product();

                    // re-open view
                    $.open_vue('add_prod', event);


                } // end else

  				  }
            // END success

            // errors
            if(data.error){

                // error
                $.show_alert('warning', data.error, false);

                // error on 'modif' case
                if( modif == 'modif' ){

                    // enable button sub
                    $('#sub_set_prod').prop( "disabled", false )
                    .attr('onclick', "$.add_product(event,'modif', "+id+")");
                    return;

                } // error on record case
                else {

                    // enable button sub
                    $('#sub_set_prod').prop( "disabled", false )
                    .attr('onclick', "$.add_product(event,null,null);");
                    return;
                }
                // end else
            }
            // END errors
  		});
  		// end AJAX

  },
  /**
   * $.add_product( event, modif, id );
   */



  /**
   * $.ask_to_suppr_product( event, id );
   *
   * @param  {type} event
   * @param  {type} id    product_id
   * @return {alert}      for confirm suppr product
   */
  ask_to_suppr_product : function( e, id ){

      e.preventDefault();

      // search title of product
      for(var i = 0; i < $.o.products.length; i++){

          if( $.o.products[i].id == id ){
              break;
          }
      }

      var html =
      `<br>`+$.o.tr.confirm_suppr_product+`&nbsp;:
      <br>`+$.o.products[i].title+`<br><br>
      <span class="btn deep-orange card round left"
      onclick="$.suppr_product(event, `+id+`);">
      <i class="far fa-trash-alt"></i>&nbsp;  `+$.o.tr.suppr+`</span>

      <span class="btn dark-gray card round right"
      onclick="$.show_alert(false);">
      <i class="fas fa-ban"></i>&nbsp; `+$.o.tr.abort+`</span>`;

      $.show_alert('info', html, true);

  },
  /**
   * $.ask_to_suppr_product( event, id );
   */



  /**
   * $.suppr_product( event, id );
   *
   * @param  {type} event description
   * @param  {type} id    description
   * @return {type}       description
   */
  suppr_product : function( e, id ){

      e.preventDefault();

      // disabled button sub
  		$('#suppr_prod').prop( "disabled", true )
      .removeAttr('onclick');

      // create form data for AJAX POST
      var formData = new FormData();

      // append command
      formData.append('set', 'suppr_product');

      // append id prod
      formData.append('prod_id', id);

      // append user id
      formData.append('token', $.o.user.token );

      // LOADER
      var html = `
      <p class="center margin">
      <i class="fa-5x fa-circle-notch fa-spin fas text-light-gray"></i>
      </p>
      <p class="center mrg0 text-light-gray xlarge">
      <span id="percent"></span>
      </p>
      <div id="charger"
      style="width:0px; height: 4px; position: absolute; bottom: 5px; background: #327a8f;"></div>`;

      // SHOW LOADER
      $.show_alert('info', html, true);

      // suppr product
      $.ajax({
        // xhr watcher for render loading process in percent to view
        xhr: function(){

            var xhr = new window.XMLHttpRequest();

            // Upload progress
            xhr.upload.addEventListener("progress", function(render){

                // if in progress
                if( render.lengthComputable ){
                	  // calcul percent
                	  var divis = Math.floor(render.loaded / render.total *100);
                		var percent = divis+'%';
                		//Do something with upload progress
                		$('#percent').text(percent);
                    $('#charger').css('width', divis+'px');
                }

            }, false); // end xhr.upload.addEventListener

            return xhr;
        }, // end xhr watcher
        url : 'index.php',
        method : 'POST',
        data : formData,
        contentType : false,
        cache : false,
        processData : false,
        dataType : 'json'})
        .done(function(data){

          // if rec new product ok
          if(data.success){

              $.show_alert('success', data.success, false);

              // RE INIT OBJ PRODUCTS
              $.o.products = {};
              $.o.products = data.products;

							// re-adjust nb products in template
							$.o.template.nb_products = $.o.products.length;

              // CLOSE MODAL MODIF
              $.close_modal(); // re-init $.obj.files

              // re-open view
							$.open_vue('products', event);

          }
          // END success

          // error
          if(data.error){

              // error
              $.show_alert('warning', data.error, false);

              // enable button sub
              $('#suppr_prod').prop( "disabled", false )
              .attr('onclick', '$.ask_to_suppr_product( event, '+id+' );');

          }


      });
      // end AJAX


  },
  /**
   * $.suppr_product( event, id );
   */



/////////////////////////////////
///////   MODIFY PRODUCT  ///////
/////////////////////////////////


  /**
   * $.open_modif_product( id );
   *
   * @param  {int} id  product id
   * @return {modal}
   */
  open_modif_product : function( id ){

      // clean obj
      $.o.one_prod = {};

      // FIND OBJECT by id
      for(var i = 0; i < $.o.products.length; i++) {

          if( $.o.products[i].id == id ){

              // clone object
              $.o.one_prod = JSON.parse(JSON.stringify( $.o.products[i] ));

              break;
          }
      }
      // end for

      // not found case ?
      if( $.o.one_prod.length == 0 ){

          // return an alert
          $.show_alert('warning', $.o.tr.product_not_found, false);
          return;
      }

      // pass tr to $.o.one_prod
      $.o.one_prod.tr = $.o.tr;

      // pass cats to $.o.one_prod
      $.o.one_prod.cats = $.o.cats;

      // add param. modif to true
      $.o.one_prod.modif = true;

      // imbibe form modif product
      $('#modal_content').empty()
      .mustache('form_modif_products', $.o.one_prod );

      // show modal - animate to top - prevent scroll restauration
      $('#modal').show()
			.animate( { scrollTop: 0 }, 100);

      // search cat product - pass to selected
      $('#cat option[value="'+$.o.one_prod.cat_id+'"]')
      .prop('selected', true);


      // LANCH img_modifier
      $.img_modifier(id);

			// if product is already taxed
			if( $.o.one_prod.tax != 0 ){

					// LAUNCH WATCHER CALCUL PRICE W. TAX
					$.calcul_price_ttc();
			}

  },
  /**
   * $.open_modif_product( id, event );
   */



  /**
   * $.product_on_off_line( val, event );
	 *
   * @return {void}  modify state product for first record
   */
  product_on_off_line : function( val, e ){


			e.preventDefault();

			var checked_context = 'fa-check-square';
			var un_checked_context = 'fa-square';


			if( val == 'off_line' ){

					$('input[name="on_off_line"]').val('off_line');

					$('label[for="oon_line"] i')
					.removeClass(checked_context)
					.addClass(un_checked_context);

					$('label[for="ooff_line"] i')
					.removeClass(un_checked_context)
					.addClass(checked_context);

			}
			else{

					$('input[name="on_off_line"]').val('on_line');

					$('label[for="oon_line"] i')
					.removeClass(un_checked_context)
					.addClass(checked_context);

					$('label[for="ooff_line"] i')
					.removeClass(checked_context)
					.addClass(un_checked_context);

			}

	},
  /**
   * $.product_on_off_line();
   */



  /**
   * $.check_on_off_line( state, id );
   *
   * @param  {string} state 	'on_line' / 'off_line'
   * @param  {int}    id 			product id
   * @return {json}  	modify state product directly on server
   */
  check_on_off_line : function( state, id ){

      let One_product = {};

      // get product object
      for (var i = 0; i < $.o.products.length; i++) {

          if( $.o.products[i].id == id ){

              One_product = $.o.products[i];
              break;
          }
      }
      // end loop

			// post modif
			$.post('index.php',
			{
				set: 'modify_state_product',
				token: $.o.user.token,
				prod_id: id,
        url: One_product.url,
				state: state
			}, function(data){

					// success
					if( data.success ){


							$.show_alert('success', data.success, false);

							// pass all icons uncheck
							$('#on_line i, #off_line i')
				      .removeClass('fa-check-square')
				      .addClass('fa-square');

							// manage icon
				      if( state == 'on_line' ){

									$('#on_line i')
									.removeClass('fa-square')
									.addClass('fa-check-square');
				      }
							else{
									$('#off_line i')
									.removeClass('fa-square')
									.addClass('fa-check-square');
							}

							// manage object
							// set product online/offline
              One_product.on_line =
              ( state == 'on_line' ) ? true : false;

							// re-open products for apply modif
							$.open_vue('products', event);

					}
					// end success

					if( data.error ){

							$.show_alert('warning', data.error, false);
					}

			}, 'json');
			// end post

  },
  /**
   * $.check_on_off_line( state, id );
   */



  /**
   * $.open_cats_selector_modif();
   *
   * @return {type}  description
   */
  open_cats_selector_modif : function(){

      $('#cat_selector_modif').toggleClass('show','hide');

  },
  /**
   * $.open_cats_selector_modif();
   */



  /**
   * $.set_cat_product( prod_id, cat_id );
   *
   * @param  {int} prod_id 	product id
   * @param  {int} cat_id  	category id
   * @return {json}        	success / error : modify directly a category
	 * 												of a product
   */
  set_cat_product : function( prod_id, cat_id ){


			// post modif
			$.post('index.php',
			{
				set: 'set_cat_product',
				token: $.o.user.token,
				prod_id: prod_id,
				cat_id: cat_id
			}, function(data){

					// success
					if( data.success ){


							$.show_alert('success', data.success, false);

							// set cat in view
							$('#cat_name').text(data.cat_name);

							// manage obj
							for (var i = 0; i < $.o.products.length; i++) {

									if( $.o.products[i].id == prod_id ){

											// set cat refs in product
											$.o.products[i].cat_name = data.cat_name;
											$.o.products[i].cat_id = data.cat_id;
											break;
									}
							}
							// end loop

							// re-open products for apply modif
							$.open_vue('products', event);

					}
					// end success

					// error
					if( data.error ){

							// close hidden cat selector
							$.open_cats_selector_modif();

							$.show_alert('warning', data.error, false);
					}
					// end error

			}, 'json');
			// end post

  },
  /**
   * $.set_cat_product( prod_id, cat_id );
   */



  /**
   * $.select_this_cat( cat_id );
   *
   * @param  {type}     cat_id
   * @return {type}     description
   */
  select_this_cat : function( cat_id ){


		  if( typeof $.o.one_prod == 'undefined' ){

          $.o.one_prod = {
            cat_id: 0,
            cat_name: '',
          };
      }

      // attr new cat to object
      for (var i = 0; i < $.o.cats.length; i++) {

          if( $.o.cats[i].cat_id == cat_id ){
              break;
          }
      }

      $.o.one_prod.cat_id = $.o.cats[i].cat_id;
      $.o.one_prod.cat_name = $.o.cats[i].title;

      // modify title cat in view
      $('#cat_name').text( $.o.cats[i].title );

      // prop. select good item
      $('#cat option[value="'+cat_id+'"]').prop('selected', true);

      // close cat selector
      $.open_cats_selector_modif();

  },
  /**
   * $.select_this_cat( cat_id );
   */



  /**
   * $.check_mode( for_input,  event );
   *
   * @param  {str} for_input 'no_tax' / 'w_tax'
   * @return {type}       description
   */
  check_mode : function( for_input,  event ){

      event.stopImmediatePropagation();

      $('label[for="no_tax"] i, label[for="w_tax"] i')
      .removeClass('fa-check-square')
      .addClass('fa-square');

      // if for taxed price
      if( for_input == 'w_tax' ){

          // manage icon
          $('label[for="w_tax"] i')
          .removeClass('fa-square')
          .addClass('fa-check-square');

          // show block tax
          $('#block_ht').css('display', 'block');

          // CALCUL TAX RENDER
          // calcul price TT with tax
          $.calcul_price_ttc();

      }
      // price not taxed
      else {

          // manage icon
          $('label[for="no_tax"] i')
          .removeClass('fa-square')
          .addClass('fa-check-square');

          // hide block tax
          $('#block_ht').css('display', 'none');

      }
      // end price not taxed

  },
  /**
   * $.check_mode( for_input,  event );
   */



  /**
   * $.calcul_price_ttc();
   *
   * @return {type}  description
   */
  calcul_price_ttc : function(){

			$('#price_less_tax, #tax').on('input', function(){

					// KEEP VALUES
	        var price_less_tax = Number( $('#price_less_tax').val() );
	        var tax = Number( $('#tax').val() );

	        // apply tax percent -> give 1.055 or 1.2
	        var calc_tax = ( tax / 100 ) + 1;

	        // TOTAL WITH TAX
	        var price_tt = price_less_tax  * calc_tax;

	        // FIX PRICE TO 0.00
	        var price_tt_fixed = price_tt.toFixed(2);
	        // console.log(ttc_price_fixed);

	        // ATTR VALUE OF ALL TAXES PRICE
	        $('#price_tt').val(price_tt_fixed);

			});

  },
  /**
   * $.calcul_price_ttc();
   */



  /**
   * $.add_img_prod();
   *
   * @return {type}  description
   */
  add_img_prod : function(){

      $('#img').click();
      $.img_viewer(); // see it in tools.js
  },
  /**
   * $.add_img_prod();
   */



  /**
   * $.clear_add_product();
   *
   * @return {type}  description
   */
  clear_add_product : function(){

				// empty cat name
				$('#cat_name').text('');

        // reset form
        $('#add_product_form')[0].reset();

        // EMPTY IMGs
        $('#rows_img').empty();

        // CLEAR OBJ.FILES
        if( $.obj.files.length != 0 ){

            delete $.obj.files;
            $.obj = { files : []  };
            $.index_box_img = 0;
        }

  },
  /**
   * $.clear_add_product();
   */


/////////////////////////////////
/////////////////////////////////
    //  SORT STOCK
/////////////////////////////////
/////////////////////////////////


  /**
   *  $.sort_stock_ad();
   */
  rep_sort : false,
  sort_stock_ad : function(){

      //  IF SEARCH IS OPEN -> HIDE IT -> SORT BY DATE ONLY IN PRODUCTS, NOT IN SEARCH
      if( $('#render_search_user_products').css('display') == 'block' ){

          	$('#render_search_user_products').empty().css('display', 'none');
      	    $('#stock_content').css('display', 'flex');
      }

      // DO THE DUST on inputs
      $('#search_product_by_cat option').eq(0).prop("selected", true);
      // DO THE DUST on inputs
      $('#search_user_products').val('');
      // DO THE DUST on inputs
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');


      if( $.rep_sort == false ){
        $('#sort_a_d').removeClass('fa-arrow-down')
        .addClass('fa-arrow-up');
        $.rep_sort = true;
      }
      else{
        $('#sort_a_d').removeClass('fa-arrow-up')
        .addClass('fa-arrow-down');
        $.rep_sort = false;
      }

      // reverse object
      var OBJ_reversed = $.o.template.products.reverse();

      // show on view
      $('#stock_content').empty().mustache( 'partial_template_stock', $.o );

      // need to launch $.lazy_load_imgs();
      $.lazy_load_imgs();

  },
  /**
   *  $.sort_stock_ad();
   */



	/**
	 * $.search_str_in_products();
	 * -> with "onkeyup"
	 * @return {html}  results of a string researched in products titles
	 */
	search_str_in_products : function(){


      // DO THE DUST on inputs - quant
      $('#search_by_quant').val('');

      // DO THE DUST on inputs - on / off line
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');

			// empty $.o.search_user_products
			$.o.search_user_products = [];

      // VALUE IN LOWER CASE
		  var value = $('#search_user_products').val().toLowerCase();

      // if multiples spaces only don't fire research
      while ( value.length > 0 && value.trim() == '') {
        return;
      }

      // KEEP length FOR STATE and substr word to search
			var len = value.trim().length;

			// re-init if no entries
			if( len == 0 ){

          // re-init view paginated by defaut on products
          $.empty_search_user_products();

      	  return;
      }
      // end len == 0

			// regex - check only first chars of a word
			var reg = new RegExp('^('+value.trim()+')', 'gi');

			// LOOP 1
			$.o.products.forEach(function(item, k){

				    var all_title = item.title.toLowerCase();

            // REMOVE FRENCH ARTICLES
            var reg_rpl = /( l')/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( de )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( des )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( au )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( en )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( à )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( le )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( les )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( la )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /(\+)/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /(-)/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /(d')/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( aux )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( au )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( un )/g;
            all_title = all_title.replace(reg_rpl, ' ');
            reg_rpl = /( une )/g;
            all_title = all_title.replace(reg_rpl, ' ');

            all_title = all_title.replace(/\s{2,}/g, ' ');

            // console.log(all_title);

            var ARR_words_title = all_title.split(' ');

            // console.log(ARR_words_title);

            ARR_words_title.forEach((item2, i2) => {

                var chars_title_sub = item2.substr(0, len);

                var found = chars_title_sub.match(reg);

                if( found != null && found.indexOf(value) != -1 ){

                  // INCLUDE PRODUCT
                  $.o.search_user_products.push(item);
                }
                // END IF MATCHED

            });
            // END FOREACH ARR_words_title LOOP 2

			});
			// LOOP 1


      // IF NOT FOUND
			if( $.o.search_user_products.length == 0 ){

          // hide pages navigation buttons
          $('.block_pagina').empty();

          // show render_search with not found message
          $('#render_search_user_products')
          .empty()
          .html(`<p class="left-align mrg0 padding">`+$.o.tr.search_not_found+`</p>`)
          .css('display', 'block');

          // hide stock_content
          $('#stock_content').css('display', 'none');

          return;
			}
      // end not found

      // search_nb_render items
      $.o.search_nb_render = $.o.search_user_products.length;

      // this show result
      $.pagination_init( 'search_user_products' );

			// hide stock_content
			$('#stock_content').css('display', 'none');

	},
	/**
	 * $.search_str_in_products();
	 * -> with "onkeyup"
	 */



  /**
   * $.empty_search_user_products();
   * -> call $.pagination_init( 'products' );
   */
  empty_search_user_products : function() {

      $.o.search_user_products = [];

      // DO THE DUST on inputs - search by str
      $('#search_user_products').val('');

      // DO THE DUST on inputs - quant
      $('#search_by_quant').val('');

      // DO THE DUST on inputs - need this here !
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');

      // empty render_search
      $('#render_search_user_products').empty().css('display', 'none');

      // render view
      $.pagination_init( 'products' );

      // show stock content
      $('#stock_content').css('display', 'flex');

      return;
  },
  /**
   * $.empty_search_user_products();
   */



	/**
	 * $.search_products_by_cat( cat_id );
	 * @Param {int}    cat_id
	 * @return {vue}  results of a category researched in products
	 */
	search_products_by_cat : function( cat_id ){


      // no value - reset
      if( !cat_id ){

          // hide modal cat selector_all
          $.open_cats_selector_all();

          // reset view by default
        	$.empty_search_user_products();

          return;
      }

      // DO THE DUST on inputs - search by str
      $('#search_user_products').val('');

      // DO THE DUST on inputs - quant
      $('#search_by_quant').val('');

      // DO THE DUST on inputs - on / off line
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');

      // re-init render_search vue
			$('#render_search_user_products').empty();

			// list cat ids
			var CAT_ids = [];
			var ref_bl;
			var ref_br;

			// search infos cat
			for (var i = 0; i < $.o.cats.length; i++) {

					if( $.o.cats[i].cat_id == cat_id ){

							// keep br / bl ref
							ref_bl = $.o.cats[i].bl;
							ref_br = $.o.cats[i].br;
							// push this cat
							CAT_ids.push( $.o.cats[i].cat_id );
							break;
					}
			}

			// it is a node ?
			if( (ref_br - ref_bl) > 1 ){

					// loop for empty array of childrens catégories
					for (var i = 0; i < $.o.cats.length; i++) {

							if( $.o.cats[i].bl > ref_bl
							&& $.o.cats[i].br < ref_br ){

									CAT_ids.push( $.o.cats[i].cat_id );
							}
					}
			}

      // prepa search products
      $.o.search_user_products = [];

      // loop in products
			for (var i = 0; i < $.o.products.length; i++) {

					// add products by cat_id
					if(  CAT_ids.indexOf($.o.products[i].cat_id) != -1 ){

							// push in array
							$.o.search_user_products.push($.o.products[i]);
					}
			}

      // IF NOT FOUND
			if( $.o.search_user_products.length == 0 ){

          // hide modal cat selector_all
          $.open_cats_selector_all();

          // hide pages navigation buttons
          $('.block_pagina').empty();

          // hide stock_content
    			$('#stock_content').css('display', 'none');

          $('#render_search_user_products')
          .html(`<p class="left-align mrg0 padding">`+$.o.tr.search_not_found+`</p>`)
          .css('display', 'block');

          return;
			}

      // search_nb_render items
      $.o.search_nb_render = $.o.search_user_products.length;

      // hide modal cat selector_all
      $.open_cats_selector_all();

      // append results
      $.pagination_init( 'search_user_products' );

      // hide stock_content
			$('#stock_content').css('display', 'none');

  },
	/**
	 * $.search_products_by_cat();
	 */



  /**
   * $.open_cats_selector_all();
   *
   * @return {type}  description
   */
  open_cats_selector_all: function(){

      $('#cat_selector_all').toggleClass('show','hide');

  },
  /**
   * $.open_cats_selector_all();
   */



	/**
	 * $.search_by_quant();
	 *
	 * @return {vue}  results of a research by quantity in products
	 */
	search_by_quant : function(){

      var value = $('#search_by_quant').val();

      // if empty don't fire research
      if( value.trim() == '' ){

        	// re-init view paginated by defaut on products
          $.empty_search_user_products(); // this fire $.pagination_init('products');

				  return;
      }

      // DO THE DUST on inputs - str search
      $('#search_user_products').val('');

      // DO THE DUST on inputs - cats
      $('#search_product_by_cat option').eq(0).prop("selected", true);

      // DO THE DUST on inputs - on / off line
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');

      // re-init render_search vue
			$('#render_search_user_products').empty();

      var number = parseInt( value.trim() , 10);

      $.o.search_user_products = [];

      $.o.products.forEach(function(item, k){

          if( item.quant <= number ){

              $.o.search_user_products.push(item);
          }

      });

      // IF NOT FOUND
			if( $.o.search_user_products.length == 0 ){

          // hide pages navigation buttons
          $('.block_pagina').empty();

          // hide stock_content
    			$('#stock_content').css('display', 'none');

          $('#render_search_user_products')
          .html(`<p class="left-align mrg0 padding">`+$.o.tr.search_not_found+`</p>`)
          .css('display', 'block');

          return;
			}

      // search_nb_render items
      $.o.search_nb_render = $.o.search_user_products.length;

      // append results
      $.pagination_init( 'search_user_products' );

      // hide stock_content
			$('#stock_content').css('display', 'none');

  },
	/**
	 * $.search_by_quant();
	 */



	/**
	 * $.search_by_on_line( state, event );
	 *
	 * @param  {type} state description
	 * @param  {type} e     description
	 * @return {type}       description
	 */
	search_by_on_line : function(state, e){


			// DO THE DUST on inputs - search by str
      $('#search_user_products').val('');

      // DO THE DUST on inputs - cats
      $('#search_product_by_cat option').eq(0).prop("selected", true);

      // DO THE DUST on inputs - quant
      $('#search_by_quant').val('');

      // DO THE DUST on inputs - need this here !
      $('.on_line_state').removeClass('fa-check-square')
      .addClass('fa-square');

      // re-init render_search vue
			$('#render_search_user_products').empty();

      $(e.target).removeClass('fa-square')
      .addClass('fa-check-square');

      $.o.search_user_products = [];

      var on_line_asked = ( state == 'on_line') ? true : false;

      $.o.products.forEach(function(item, k){

          // on line products
          if( item.on_line == true && on_line_asked == true ){

              $.o.search_user_products.push(item);
          }

          // off line products
          if( item.on_line == false && on_line_asked == false ){

              $.o.search_user_products.push(item);
          }

      });

      // IF NOT FOUND
			if( $.o.search_user_products.length == 0 ){

          // hide pages navigation buttons
          $('.block_pagina').empty();

          // hide stock_content
    			$('#stock_content').css('display', 'none');

          $('#render_search_user_products')
          .html(`<p class="left-align mrg0 padding">`+$.o.tr.search_not_found+`</p>`)
          .css('display', 'block');

          return;
			}

      // search_nb_render items
      $.o.search_nb_render = $.o.search_user_products.length;

      // append results
      $.pagination_init( 'search_user_products' );

      // hide stock_content
			$('#stock_content').css('display', 'none');

  },
	/**
	 * $.search_by_on_line( state, event );
	 */



	/**
	 * $.init_featured_prods();
	 *
	 * @return {html}  produtcs ready for pagination
	 * - remove prods already selected
	 */
	init_featured_prods : function(){


			// clone products
			$.o.featured_prods_selector =
			JSON.parse(JSON.stringify($.o.products));

			// remove products already selected
			$.o.featured_prods.forEach((item, i) => {

					// loop $.o.featured_prods_selector
					for (var i = 0; i < $.o.featured_prods_selector.length; i++) {

							// serach by same id
							if( $.o.featured_prods_selector[i].id == item.id ){

									// remove on selectors
									$.o.featured_prods_selector.splice(i,1);
									// stop loop here
									break;
							}
					}
					// end lopp $.o.featured_prods_selector

			});


      // init. pagination
      $.pagination_init( 'featured_prods_selector' );

	},
	/**
	 * init_featured_prods - description
	 */



	/**
	 * $.select_for_featured_prods( id );
	 *
	 * @return {html}  paste an article to featured products
	 */
	select_for_featured_prods : function( id ){


			// loop for find product to add
			for (var i = 0; i < $.o.featured_prods_selector.length; i++) {

					if( $.o.featured_prods_selector[i].id == id ){
						break;
					}
			}

			// init array if no exist -> for construction must be defined in shop::
			if( typeof $.o.featured_prods == 'undefined' ){

					$.o.featured_prods = [];
			}

			// check doublons - findIndex search by attribute in obj.
			const is_already_in = (item) => item.id == id;

			// sotp here if is already in featureds prods
			if( $.o.featured_prods.findIndex(is_already_in) != -1 ){

					// error
					$.show_alert('warning', $.o.tr.featured_doublon, false);
					return;
			}

			// append to featureds prods
			$.o.featured_prods.push( $.o.featured_prods_selector[i] );

			// clean block for first add
			if( $.o.featured_prods.length == 1 ){
				$('#featured_prods').empty();
			}

			// set nb_featured_prods
			$('#nb_featured_prods').text($.o.featured_prods.length);
			// in obj.
			$.o.template.nb_featured_prods = $.o.featured_prods.length;

			// append to selected view
			$('#featured_prods').append()
			.mustache('one_featured_prod', $.o.featured_prods_selector[i]);

			// remove Html Element from list
			$('#feat-'+id+'').detach();

			// success
			$.show_alert('success', $.o.tr.product_added, false);

	},
	/**
	 * $.select_for_featured_prods( id );
	 */



	/**
	 * $.move_prod( id,  dir );
	 *
	 * @param  {int}   id  id product in $.o.featured_prods
	 * @param  {str}   dir 'prev' or 'next'
	 * @return {type}     description
	 */
	move_prod : function( id, dir ){


		// find index of item in featured_prods
		const find_index_by_id = (item) => item.id == id;

		var index_prod = $.o.featured_prods.findIndex(find_index_by_id)
		// console.log( index_prod );


		// for all cases REMOVE HTML Element
		var element = $('#cont_prod-'+id+'').detach();


		// suppr item
		if( dir == 'suppr' ){

				// remove item to obj
				var item = $.o.featured_prods.splice(index_prod, 1);

				// re-assing to selectors list origin
				$.o.featured_prods_selector.push(item);

				// if no more products
				if( $.o.featured_prods.length == 0 ){

						$('#featured_prods').empty()
						.mustache('partial_featured_prods', $.o );
				}

				// re init for have good products and good pagination
				$.init_featured_prods();

				return;
		}

		// dir -> 'prev' and index item == 0
		if( dir == 'prev' && index_prod == 0 ){

				// remove first item
				var item = $.o.featured_prods.shift();
				// append it to end
				$.o.featured_prods.push(item);

				// append html to the end
				$('#featured_prods').append(element);

				return;
		}

		// dir -> 'next' and index item == length
		if( dir == 'next' && index_prod == ($.o.featured_prods.length-1) ){

				// remove last item
				var item = $.o.featured_prods.pop();
				// append it to first index
				$.o.featured_prods.splice(0, 0, item);

				// append html to the end
				$('#featured_prods').prepend(element);

				return;
		}

		if( dir == 'prev' && index_prod != 0 ){

				// first keep id previous html element - before do the mess !
				var id_prev_elem = $.o.featured_prods[index_prod-1].id;
				// remove item to obj
				var item = $.o.featured_prods.splice(index_prod, 1);
				// append it to prev index
				$.o.featured_prods.splice( (index_prod-1), 0, item[0] );
				// append html to his index
				$(element).insertBefore( $('#cont_prod-'+id_prev_elem+'') );

				return;
		}

		if( dir == 'next' ){

				// first keep id next html element - before do the mess !
				var id_next_elem = $.o.featured_prods[index_prod+1].id;
				// remove item to obj
				var item = $.o.featured_prods.splice(index_prod, 1);
				// append it to prev index
				$.o.featured_prods.splice( (index_prod+1), 0, item[0] );
				// append html to his index
				$(element).insertAfter( $('#cont_prod-'+id_next_elem+'') );

				return;
		}


	},
	/**
	 * $.move_prod( id,  dir );
	 */



	/**
	 * $.record_featured_prods();
	 * template : featured_prods.html
	 * @return {json} record featured products to display them in the slideshow
	 */
	record_featured_prods : function(){

		// disable btn
		$('#record_featured_prods').removeAttr('onclick')
    .append(`&nbsp; <span id="slider_settings_spinner" class="spinner">
    <i class="fas fa-circle-notch fa-spin"></i>
    </span>`);

		var ARR_ids = [];

		// get featured ids
		for (var i = 0; i < $.o.featured_prods.length; i++) {
				ARR_ids.push( $.o.featured_prods[i].id );
		}

		// post datas
		$.post('index.php',
		{
			set : 'record_featured_products',
			token : $.o.user.token,
			list_ids : JSON.stringify(ARR_ids),
		 }, function(data){

				// succes
			 	if( data.success ){
						$.show_alert('success', data.success, false);
				}

				// error
				if( data.error ){
						$.show_alert('warning', data.error, false);
				}

				// enable pnclick button
				$('#record_featured_prods')
				.attr('onclick', '$.record_featured_prods();');

        // remove spinner
        $('#slider_settings_spinner').remove();

		}, 'json');
		// end $.post

	},
	/**
	 * $.record_featured_prods();
	 */



	/**
	 * $.choice_for_slider( for_item, enable_val );
	 * template : featured_prods.html
	 * @param  {string} for        'display' (show/hide slider) or 'play' (slider autoplay)
	 * @param  {int}    enable_val  1 -> enable || 0 -> disable
	 * @return {void}   set hiddens inputs
	 */
	choice_for_slider : function( for_item, enable_val ){

			if( enable_val == 1 ){

  				$('#slider-'+for_item+'-yes').removeClass('gray').addClass('blue');
  				$('#slider-'+for_item+'-no').removeClass('blue').addClass('gray');
			}
			else{

  				$('#slider-'+for_item+'-no').removeClass('gray').addClass('blue');
  				$('#slider-'+for_item+'-yes').removeClass('blue').addClass('gray');
			}

			// set value
			$('#SLIDER-'+for_item+'').val(enable_val);

	},
	/**
	 * $.choice_for_slider( for_item, enable_val );
	 */



	/**
	 * $.record_slider_settings();
	 * template : featured_prods.html
	 * @return {json}  return slider settings
	 */
	record_slider_settings : function(){

		// disable btn
		$('#record_slider_settings').removeAttr('onclick')
    .append(`&nbsp; <span id="slider_settings_spinner" class="spinner">
    <i class="fas fa-circle-notch fa-spin"></i>
    </span>`);

		var form = document.getElementById('slider_settings');
		var formData = new FormData(form);

		formData.append('set', 'record_slider_settings' );
		formData.append('token', $.o.user.token );

		// post datas
		$.sender('#slider_settings', 'POST', 'index.php', formData, 'json',
		function(data){

				// succes
				if( data.success ){

            $.show_alert('success', data.success, false);

						// re-init datas object
						$.o.api_settings.SLIDER = data.slider;

						// re-init view slider settings form
						$('#slider_settings_form').empty()
						.mustache('slider_settings_form', $.o);
				}

				// error
				if( data.error ){

            $.show_alert('warning', data.error, false);
				}

				// enable btn
				$('#record_slider_settings')
				.attr('onclick', '$.record_slider_settings();');

        // remove spinner
        $('#slider_settings_spinner').remove();

		}, 'json');
		// end $.sender

	},
	/**
	 * $.record_slider_settings();
	 */


});
// END EXTEND


});
// END jQuery
