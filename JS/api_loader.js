/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2021-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	api_loader.js
 *
 * const DEV_MODE = false; // show object api
 * const STICKY_HEADER = false; // enable / disable sticky header on scroll
 *
 * call a Web Worker to get templates and datas API
 * var Worker = new Worker('/JS/server_worker.js');
 *
 * const get_templates : Promise - call the Worker
 * function get_object_api(); - Call the worker
 *
 * Launch :
 * $.lazy_load_imgs();
 * $.put_aria_hiddens();
 * get_templates -> async get_object_api();
 *
 */

// set dev mode true / false
const DEV_MODE = false;

// enable sticky header when scrolling page - set : true / false
const STICKY_HEADER = false;

// Call Web Worker for donwload all templates and API datas
var Worker = new Worker('/JS/server_worker.js');


// start jQuery
$(function(){


  /**
   * const get_templates : Promise
   */
  const get_templates = new Promise(function(resolve, reject){


      // about request
      let request = 'get_templates';

      // ask worker to load templates
      Worker.postMessage(request);

      // on response from the Worker
      Worker.onmessage = function(event){

          // Templtes Object recivded from the server
          let Templates = event.data.templates;

          // server return array[ templates['name']/['html'] ]
          Templates.forEach( (item, i) => {

              // create template mustache by html source
              $.Mustache.add(item.name, item.html);

              // resolve when all templates are added to Mustache
              if( i == Templates.length-1 ){

                  resolve();
              }
          });

      }
      // end on response from the Worker

  });
  /**
   *  end const get_templates : Promise
   */



  /**
   * function get_object_api();
   *
   * @return {json} get datas API and make $.o Object API
   */
  function get_object_api(){


      // about request
      let request = 'get_object_api';

      // ask worker to load Object API
      Worker.postMessage(request);

      // on response from the Worker
      Worker.onmessage = function(event){


        	// PLACIDO OBJECT EXTEND jQuery
    	    $.extend({
            // log : $.o for watch object
    				o : event.data,
    			});
    	    // END EXTEND

    			// RENDER API OBJECT
    			if( DEV_MODE ){

    					console.log($.o);
    			}

    			// remove splash screen
    			// or slideOutUp or class ...
    			$('#splash_screen').addClass('animated fadeOut');

    			var ti_splash = window.setTimeout(function(){

    					$('#splash_screen').remove();

    					window.clearTimeout( ti_splash );

    			}, 800 );
    			// end splash screen

    			// enable sticky header when scrolling page
          $.sticky_header_init = STICKY_HEADER;

    			// check cart in memory
    			$.check_cart_in_memory();

    			// launch swiper -> header slideshow
    			$.launch_slider();

    			// enable swipe thumbnails products see : JS/main.js
    			$.swipe_products();


          // FOR START HISTORY - FOR THINGS ASKED BY URL
          switch( $.o.view.page_context ){


      				// HOME PAGE
              case 'home':

                  // i. the 3rd parameter must be ' ' for Firefox
      						history.replaceState({page : 'home'}, '',' ');
      				break;

      				// CATEGORY
              case 'cat':

      						history.replaceState({
      						page : 'cat',
      						id: $.o.histo.cat_id }, '',
      						$.o.histo.cat_url+'/category/'+$.o.histo.cat_id);

      						// make a breadcrumb and show it
      						$.breadcrumb($.o.histo.cat_id);

      						// scroll to top sort block
      						$.scroll_to_elem( '#sort_block', event );
              break;

      				// SINGLE PRODUCT VIEW
              case 'single_product':

      						history.replaceState({
      						page: 'single_product',
      						id: $.o.histo.id }, '',
      						$.o.histo.url+'/product/'+$.o.histo.id);

      						// launch swipe thunbails
      						$.swipe_imgs();

      						// put related products in bottom of product
      						$.put_related_products();

      						// record stat for this product
      						$.record_stat_for_one_product( $.o.histo.id );
              break;

      				// PAGE VIEW
              case 'page':

      						// disable touchSwipe - touchSwipe disable inputs
      						$('#center_page').swipe('disable');

      						history.replaceState({
      						page: $.o.histo.page }, '', $.o.histo.url);
      				break;

      				// CART
              case 'cart':

      						// disable touchSwipe
      						$('#center_page').swipe('disable');

      						// no set history here / see JS/cart.js
      						$.open_payment_form();
              break;

      				// RENDER SALE
              case 'sale':

      						// disable touchSwipe
      						$('#center_page').swipe('disable');

      						history.replaceState({
      						page: 'sale',
      						sale_id: $.o.histo.sale_id,
      						hash_customer: $.o.histo.hash_customer }, '', $.o.histo.url);

      						// pass globals
      						$.sale_id = $.o.histo.sale_id;
      						$.hash_customer = $.o.histo.hash_customer;
      				break;

      				// HOME BY DEFAULT
              default:
      						history.replaceState({page : 'home'}, '',' ');

          }
          // END switch $.o.view.page_context


      }
      // end on response from the Worker

  }
  /**
   * end function get_object_api();
   */


  // FUNCTIONS TO FIRE FIRST FOR THE DOM :

	// launch lazy load imgs
	$.lazy_load_imgs();

	// pass icons to aria-hidden in actual view
	$.put_aria_hiddens();

  // get templates before
  get_templates.then( () => {

      // and then : get datas API
      get_object_api();
  });



});
// END jQuery
