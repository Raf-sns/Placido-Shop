/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: main.js
 *
 * $.open_menu();
 * $.open_vue();
 * $.pagination_init( OBJ );
 * $.pagina_show();
 * $.pagina_nav( direction );
 * $.open_modal_mail( index );
 * $.send_mail_to_customer( mail );
 * $.log_out();
 *
 * jQuery not extended :
 *
 * $(window).on('resize')
 * window.onpopstate()
 * $(document).click()
 * $(document).on('scroll')
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({



  /**
   * $.open_menu();
   */
  open_menu : function(){

      if( $('#navbar').css('display') == 'none'
			&& window.innerWidth < 993 ){

          $('#navbar').css('display', 'block');

          $.animateCss('#navbar', 'slideInLeft', function(){

              $('#navbar').removeClass('animated slideInLeft');
          });

      }
      else if( window.innerWidth < 993 ){

          $.animateCss('#navbar', 'slideOutLeft', function(){

              $('#navbar').removeClass('animated slideOutLeft')
              .css('display', 'none');
          });

      }

  },
  /**
   * $.open_menu();
   */



  /**
   * $.open_vue( vue, module_name );
   *
   * @param  {type} vue   				'name_of_template' / 'module'
   * @param  {type} module_name 	'' / 'name_of_module'
   */
  open_vue : function( vue, module_name ){


      // CLEAN OBJ TEMPLATE - INSERT HERE PAGE LIST
      $.o.vue = {};

      // switch view settings
      // for icon / title  and specific templates behaviours
      switch (vue) {

        case 'home':
          $.o.vue.home = true;
          $.o.vue.title = $.o.tr.home;
          $.o.vue.icon = 'fas fa-home';
        break;

        case 'products':
          $.o.vue.products = true;
          $.o.vue.title = $.o.tr.your_products
          +`&nbsp;:&nbsp;&nbsp;`+$.o.template.nb_products+`&nbsp;&nbsp;`+ $.o.tr.entries;
          $.o.vue.icon = 'fas fa-th';
        break;

				case 'featured_prods':
          $.o.vue.featured_prods = true;
          $.o.vue.title = $.o.tr.featured_prods;
          $.o.vue.icon = 'fa-image far';
        break;

        case 'add_prod':
          $.o.vue.add_prod = true;
          $.o.vue.title = $.o.tr.add_prod;
          $.o.vue.icon = 'fas fa-plus';
          // pass product online && checked for no-taxed-price by default
          $.o.on_line = true;
          $.o.checked_ttc = 'checked';
        break;

        case 'categories':
          $.o.vue.categories = true;
          $.o.vue.title = $.o.tr.website_categories;
          $.o.vue.icon = 'fas fa-stream';
        break;

        case 'shop':
          $.o.vue.shop = true;
          $.o.vue.title = $.o.tr.shop_user;
          $.o.vue.icon = 'fas fa-store-alt';
        break;

        case 'messages':
          $.o.vue.messages = true;
          $.o.vue.title = $.o.tr.messages;
          $.o.vue.icon = 'fa-envelope far';
        break;

        case 'stats':
          $.o.vue.stats = true;
          $.o.vue.title = $.o.tr.website_stats;
          $.o.vue.icon = 'fas fa-chart-bar';
        break;

        case 'archives':
          $.o.vue.archives = true;
          $.o.vue.title = $.o.tr.archived_sales;
          $.o.vue.icon = 'far fa-folder-open';
        break;

        case 'settings':
          $.o.vue.settings = true;
          $.o.vue.title = $.o.tr.settings;
          $.o.vue.icon = 'fas fa-cogs';
        break;

				case 'web_app':
					$.o.vue.web_app = true;
					$.o.vue.title = $.o.tr.web_app;
					$.o.vue.icon = 'fas fa-mobile-alt';
				break;

				case 'ip_rejected':
          $.o.vue.ip_rejected = true;
          $.o.vue.title = $.o.tr.ip_rejected;
          $.o.vue.icon = 'fa-exclamation-triangle fas';
        break;

				case 'static_pages':
          $.o.vue.static_pages = true;
          $.o.vue.title = $.o.tr.static_pages;
          $.o.vue.icon = 'fa-file-alt far';
        break;

				case 'module':
          $.o.vue.module = true;
          $.o.vue.title = $.o.tr.module+' - '+$.o.modules[module_name].name;
          $.o.vue.icon = 'fa-flask fas';
        break;


        default:
          $.o.vue.home = true;
          $.o.vue.title = $.o.tr.home;
          $.o.vue.icon = 'fas fa-home';
      }
      // switch view settings

      // COLORIZE MENU ITEM
      // remove all style active items
      $('.menu_left').removeClass('dark-gray');

      // addClass visited for item clicked by data-target attr. for history
      $('[data-target="'+vue+'"]').addClass('dark-gray');

      // APPEND VIEW TO MAIN
      $('#main').empty().mustache('main_center', $.o );

      // Scroll to top
      $.scroll_top();

      // _CALLBACKS AJUSTEMENTS FOR TEMPLATES

      // - products  launch img lazy loader for stock vue
      if( $.o.vue.products == true ){

          // determine pagination
          $.pagination_init( 'products' );
      }

			// featured_prods
			if( $.o.vue.featured_prods == true ){

					$.init_featured_prods();
      }

			// add product
			if( $.o.vue.add_prod == true ){

					// wrap add_product_form with padding
					$('#add_product_form')
					.wrap('<div class="pad_product_form"></div>');
      }

      // - shop  in shop user case
      if( $.o.vue.shop == true ){

          // manage IMG SHOP
          if( $.o.shop.img == false ){
              // launch viewer if no img
              $.img_viewer();
          }
          else{
              // launch viewer if img already recorded
              $.append_img_shop( $.o.shop.img );
          }

      }
      // end shop

			// settings
			if( $.o.vue.settings == true ){

					$.append_img_shop( $.o.api_settings.LOGO_SN );

			}

      // stats - get stats dynamically when calling page
      if( $.o.vue.stats == true ){

					$.getScript('JS/STATS/loader.js');
      }


			// ip_rejected - get ip_rejected dynamically when calling page
      if( $.o.vue.ip_rejected == true ){

          // this function is in ip_rejected.js
          $.get_ip_rejected();
      }

			// MODULE
      if( $.o.vue.module == true ){

					// insert template of module
					// $.o.modules[module_name].package.main_html
					// -> main template entry without ".html"
          $('#page_gen').empty()
					.mustache($.o.modules[module_name].package.main_html, $.o );
      }

      // set history
      if( history.state.template != vue ){

          history.pushState({template : vue}, '','');
      }

  },
  /**
   * END $.open_vue( vue, module_name );
   */



  /**
   * $.pagination_init( OBJ );
   *
   * calcul index min/max by defaut and nb pages needed
   * @param  {str} 'OBJ'    name of object for get nb pagina pages
   * @return {html} + show result   show pages navigation buttons if pages > 1
   */
  pagination_init: function( OBJect ){

      $.o.obj_for_pagination = OBJect;

      var count_items = $.o[OBJect].length;
      var nb_pages = Math.ceil( (count_items / $.o.template.nb_for_pagina) );

      if( nb_pages > 1 ){

          // get nb pages needed
          $.o.template.nb_pages = nb_pages;

          // set page active
          $.o.template.nb_page_active = 1;

          // set index min by default
          $.o.template.index_min = 0;

          // set index max by default
          $.o.template.index_max =
          ( $.o.template.index_min + $.o.template.nb_for_pagina ) - 1;

          // show pages navigation buttons
          $('.block_pagina').empty().mustache('pagina', $.o);
      }
      else {

					// get nb pages needed
          $.o.template.nb_pages = nb_pages;

          // set page active
          $.o.template.nb_page_active = 1;

          // set index min by default
          $.o.template.index_min = 0;

          // set index max by default
          $.o.template.index_max = count_items - 1;

          // hide pages navigation buttons
          $('.block_pagina').empty();
      }

      // show results
      $.pagina_show();

  },
  /**
   * $.pagination_init( OBJ );
   */



  /**
   * $.pagina_show();
   *
   * @return {html}  show a page in pagination
   */
  pagina_show: function(){


      // set page active in view
      $('.nb_page_active').text( $.o.template.nb_page_active );

      var len = $.o[$.o.obj_for_pagination].length;

      // create new object in template obj.
      $.o.template[$.o.obj_for_pagination] = [];

      // insert objects by interval asked
      for (var i = 0; i < len; i++) {

          if( i >= $.o.template.index_min  &&  i <= $.o.template.index_max ){
              // push to array in template for view
              $.o.template[$.o.obj_for_pagination].push( $.o[$.o.obj_for_pagination][i] );
          }
      }

			// in products context
      if( $.o.obj_for_pagination == 'products' ){
          // render view
          $('#stock_content').empty()
					.mustache( 'partial_template_stock', $.o );
      }

			// search result context
      if( $.o.obj_for_pagination == 'search_user_products' ){

          // append results
          $('#render_search_user_products')
          .empty()
          .mustache( 'search_render_prods', $.o )
          .css('display', 'block');
      }

			// in featured products view
			if( $.o.obj_for_pagination == 'featured_prods_selector' ){

					$('#featured_prods_selector').empty()
					.mustache( 'partial_prods_selector', $.o );
			}

      // need to lanch $.lazy_load_imgs();
      $.lazy_load_imgs();

  },
  /**
   * $.pagina_show();
   */



  /**
   * $.pagina_nav( direction );
   *
   * @param  {str} direction      'prev' / 'next'
   * -> $.o.obj_for_pagination    'name_of_an_object_array'
   * @return {html}               a page
   */
  pagina_nav: function( direction ){

      var len = $.o[$.o.obj_for_pagination].length;

      // classic behaviour
      if( direction == 'next' ){

          $.o.template.index_min = $.o.template.index_min + $.o.template.nb_for_pagina;
          $.o.template.index_max = $.o.template.index_min + ($.o.template.nb_for_pagina - 1);

          $.o.template.nb_page_active++;
      }

      // special case
      if( direction == 'next' && $.o.template.nb_page_active > $.o.template.nb_pages ){

          $.o.template.index_min = 0;
          $.o.template.index_max = $.o.template.nb_for_pagina - 1;

          $.o.template.nb_page_active = 1;

      }

      // classic behaviour
      if( direction == 'prev' ){

          $.o.template.index_min = $.o.template.index_min - $.o.template.nb_for_pagina;
          $.o.template.index_max = $.o.template.index_min + ($.o.template.nb_for_pagina - 1);

          $.o.template.nb_page_active--;
      }

      // special case
      if( direction == 'prev' && $.o.template.nb_page_active <= 0 ){

          // calc rest items on last page
          var rest = len % $.o.template.nb_for_pagina;

          // it seems like a big hack but it works ...
          if( rest == 0 ){
              $.o.template.index_min = len - $.o.template.nb_for_pagina;
          }
          else{
              $.o.template.index_min = len - rest;
          }

          $.o.template.index_max = $.o.template.index_min + ($.o.template.nb_for_pagina - 1);

          $.o.template.nb_page_active = $.o.template.nb_pages;
      }

        // console.log( 'nb_for_pagina : '+ $.o.template.nb_for_pagina );
        // console.log( 'rest : '+rest );
        //
        // console.log( $.o.template.index_min );
        // console.log( $.o.template.index_max );

      // SHOW RESULT
      $.pagina_show();

  },
  /**
   * $.pagina_nav(direction);
   */



  /**
   * $.open_modal_mail( index );
   *
   * @param  {int} index of object
   *
   */
  open_modal_mail : function( index ){

      // set obj mail
      var Obj = $.o.new_sales[index].customer_settings;

			// Pass translation
			Obj.tr = $.o.tr;

      // imbibe modal_content
      $('#modal_content').mustache('send_mail_to_customer', Obj);

      $('#modal').show();

  },
  /**
   * $.open_modal_mail( index );
   */



  /**
   * $.send_mail_to_customer( mail );
   *
   * @param  {type} mail description
   * @return {type}      description
   */
  send_mail_to_customer : function(mail){

      // disable button
      $('#sub_mess_mail').removeAttr('onclick')
      .prop('disabled', true);

      // show spinner
      $('#sub_mess_mail').append(`
        &nbsp;<i id="lil_spinner" class="fas fa-spin fa-circle-notch"
				style="vertical-align: middle;"></i>`);

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();

      // user
      datas.append('token', $.o.user.token );
      // mail to send
      datas.append('mail', mail );
      // subject and message
      datas.append('subject', $('#subject').val() );
      datas.append('message', $('#message').val() );
      // append command
      datas.append('set', 'send_mail_to_customer');


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.close_modal();

              $.show_alert('success', data.success, false);

          } // error
          else{

              $.show_alert('warning', data.error, false);
          }

          // enable  button
          $('#sub_mess_mail')
          .attr('onclick','$.send_mail_to_customer("'+mail+'");')
          .prop('disabled', false);

          // remove spinner
          $('#lil_spinner').remove();

      });
      // end sender

  },
  /**
   * END $.send_mail_to_customer( mail );
   */



	/**
	 * $.log_out();
	 *
	 * @return {type}  description
	 */
	log_out : function(){

			var Obj = {
				set : 'log_out',
				token: $.o.user.token
			};

			$.post('index.php', Obj, function(data){

					// success token is erased
					if( data.success ){

							// redirect to connection page
							window.location.href = '/'+$.o.api_settings.ADMIN_FOLDER;
					}

			},'json');
	},
	/**
	 * $.log_out();
	 */


});
// END EXTEND


/////////////////////////////////////////////////////////
////////////     C L A S S I C A L   JS      ////////////
/////////////////////////////////////////////////////////

	//  WEB  CONNECTED / NOT CONNECTED
  function online_status( event ){

      if( navigator.onLine == false ){

          $.show_alert('warning', $.o.tr.waning_not_connected , true);
      }

      if( navigator.onLine == true ){

          $.show_alert('info', $.o.tr.connection_restored , false);
      }
  }

  window.addEventListener('offline', online_status);
  window.addEventListener('online', online_status);


	// ON RESIZE
	$(window).on('resize', function(){

	    // hide navbar on mobile
	    if( window.innerWidth > 993 && $('#navbar').css('display') == 'none' ){

	        // show navbar on large screen
	        $('#navbar').css('display', 'block');
	    }
	    if( window.innerWidth < 993 && $('#navbar').css('display') == 'block' ){

	        // hide navbar on mobiles
	        $('#navbar').css('display', 'none');
	    }

	});
	// END ON RESIZE



	////  HISTORY

  // first insert home in history
  history.pushState({template : 'home'}, '','');

	// open good view on popstate
  window.onpopstate = function(event){

      $.open_vue( history.state.template , event );

  }
	//// end  HISTORY

  // ONCLICK DOCUMENT
  $(document).on('click', function(event){

      // hide menu mobile on click on document
      var container = $('.unbind_click, .open_modal');

      // IF NOT TARGETTED
      if(  !container.is(event.target)
      && container.has(event.target).length === 0  ){

          // hide navbar on mobile
          if( $('#navbar').css('display') == 'block'
					&& window.innerWidth < 993 ){

              $.open_menu();
          }

					// hide cat selector
					if( $('#cat_selector_all').is(':visible') == true  ){

							// close cat selectors
							$.open_cats_selector_all();
					}
					if( $('#cat_selector_modif').is(':visible') == true  ){

							$.open_cats_selector_modif();
					}

					// close modal on click in document - JUST FOR IMG REMINDER  !
					if( $('#modal').is(':visible') == true
					&& $('#reminder_img').is(':visible') == true ){

							// close modal
							$.close_modal();
					}

      }

  });
  // END ONCLICK DOCUMENT



  // DOCUMENT ON SCROLL
  var isScrolling;
  $(document).on('scroll', function(){


    	// Clear our timeout throughout the scroll
    	window.clearTimeout( isScrolling );

    	// Set a timeout to run after scrolling ends
    	isScrolling = setTimeout(function() {

      		var val_scroll = Math.floor( $(document).scrollTop() );
          // console.log(val_scroll);

          if( val_scroll > 200 ){

              // show to top button
              $('#to_top').animate({'left':'12px'}, 300);

              // fire lazy load
              $.lazy_load_imgs();
          }
          else{

              $('#to_top').animate({'left':'-50px'}, 300);
          }

    	}, 100);
      // end set tie out

  });
  // END DOCUMENT ON SCROLL


});
// END JQUERY
