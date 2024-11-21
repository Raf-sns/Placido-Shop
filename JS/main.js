/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 *
 * Script name:	main.js
 *
 * $.show_page( event, about );
 * $.open_home();
 * $.open_render_sale();
 * $.open_a_cat( id, event );
 * $.cat_render( cat_name );
 * $.close_all_menus();
 * $.open_product( id, event );
 * $.put_related_products();
 * $.open_static_page( event, page_url );
 *
 * $.link_prod_copied
 * $.copy_link( elem, event );
 * $.getter_open_sidebar
 * $.open_sidebar();
 * $.getter_cats_menu
 * $.open_cats_menu();
 * $.deploy_cat( cat_id );
 * $.send_message();
 * $.drop_show( elem );
 *
 * $.fire_search_on_enter( input, event );
 * $.render_search : [],
 * $.search( input, event );
 * $.erase_search( input );
 * $.show_sort_options();
 * $.sort_by_price( order );
 *
 * $.display_products();
 *
 * $.swipe_imgs();
 * $.show_img( id, event );
 *
 * $.mobile_dev_all( event );
 *
 * $.home_slider;
 * $.init_keen_slider();
 * $.launch_slider();
 *
 * $.breadcrumb( cat_id );
 * $.lazy_load_imgs();
 *
 * $.sticky_header_init : true / false;
 * $.sticky_header();
 * $.swipe_products();
 *
 *
 * jQuery not extended :
 *
 * function online_status( event );
 * $(window).on('resize')
 * $(window).on('click')
 * $(window).scroll()
 * $('#to_top').click()
 *
 */

$(function(){


  // EXTEND ALL METHODS -> Call them width $.myMethod()
  // EXTEND ALL OBJECTS -> Call them width $.myOject
  $.extend({



    /**
     * $.show_page( event, about );
     *
     * @param  {event}  event
     * @param  {string} about  key for template
     * @return {html}   return an html page according to the requested template
     */
    show_page: function(event, about){

      // template to call
      var template;

      // home page
      if( about == 'home' ){

          // template products in context 'inline' / 'mozaic'
          template =
            ($.o.view.display_products == 'inline') ?
            'products_view_inl' : 'products_view';
      }

      // category page
      if( about == 'cat' ){

          template = 'products_view';
      }

      // contact page
      if( about == 'contact' ){

          template = 'contact_form';
      }

      // one product page
      if( about == 'single_product' ){

          template = 'single_product';
      }

      // cart page
      if( about == 'cart' ){

          template = 'payment_form';
      }

      // render sale
      if( about == 'render_sale' ){

          template = 'render_sale';
      }

      // render sale login page
      if( about == 'render_sale_login' ){

          template = 'render_sale_login';
      }

      // page_url
      if( $.o.static_pages.hasOwnProperty(about)
          && $.o.static_pages[about].url == about ){

          template = about; // about == page_url
      }


      var prom = new Promise( function(resolve, reject){

          // wrap old content
          $('#center_page').contents()
            .wrap(`<div class="old_content"></div>`);

          $('#center_page').append().mustache(template, $.o);

          resolve();

      }).then((success) => {

          $('.old_content').remove();

          $.lazy_load_imgs();

          $.put_aria_hiddens();

          $.close_all_menus();

      });

      // HIDE SIDEBAR
      // getter sidebar
      if($.getter_open_sidebar == true) {

          $.open_sidebar();
      }

    },
    /**
     * $.show_page( event, about );
     */



    /**
     *  $.open_home();
     *
     * @return {html}  get home page
     */
    open_home: function() {


      // set page page_context
      $.o.view.page_context = 'home';

      // launch slider
      $.o.view.slider.show = true;

      $.launch_slider();

      // DISPLAY SORTINGS - all
      $('.display_top_sort').css('display', 'inline-block');

      $.o.view.display_top_sort = 'display: block;';

      // pagination with default nb products
      $.o.view.nb_wanted = $.o.view.def_nb_prods;

      $.return_for_pagina($.o.products); // by default

      // IF SORT BLOCK WAS HIDDEN - SHOW IT
      $('#sort_block').css('display', 'flex');

      // remove breadcrumb
      $('#breadcrumb').remove();

      // NOT display cat title render
      $.cat_render();

      // show page
      $.show_page(event, 'home');

      // enable touchSwipe
      $('#center_page').swipe('enable');

      // scoll to top
      $.scroll_top();

      // set good title of page
      document.title = $.o.view.title;

      // HISTORY
      if( history.state.page != 'home' ){

          // keep space into the last argument for Firefox bug
          history.pushState({ page: 'home' }, '', ' ');
      }

    },
    /**
     *  $.open_home();
     */



    /**
     *  $.open_render_sale();
     *
     *  open render sale login
     *
     * @return {html}  open render sale page or ask to login for show render sale page
     */
    open_render_sale: function() {


      var template = (typeof($.o.SALE) === 'undefined') ?
        'render_sale_login' : 'render_sale';

      $.clean_sort_block();

      // show page : render sale OR render sale login
      $.show_page(event, template);

      // disable touchSwipe
      $('#center_page').swipe('disable');

      // set good title of page
      document.title = $.o.view.title + ' - ' + $.o.tr.your_order;

      // if sale exist - push in history
      if( typeof($.o.SALE) !== 'undefined' ){

          // HISTORY
          history.pushState({
            page: 'sale',
            sale_id: $.sale_id,
            hash_customer: $.hash_customer
          }, '', '/sale/' + $.sale_id + '/' + $.hash_customer);

      }
      else{

          // new sale is not in object $.o.SALE
          // HISTORY - push state home -> on url page for render_sale_login
          if( history.state.page != 'home' ){

            history.pushState({ page: 'home' }, '', ' ');
          }
      }

    },
    /**
     *  $.open_render_sale();
     */



    /**
     * $.open_a_cat( cat_id, event );
     *
     * @param  {int}    cat_id
     * @param  {event}  event
     * @return {html}   show all products of a category
     */
    open_a_cat: function(cat_id, event) {

      event.preventDefault();

      // PAGE CONTEXT
      $.o.view.page_context = 'cat';

      // reset nb products in view by default
      $.o.view.nb_wanted = $.o.view.def_nb_prods;

      var This_cat;

      // get the cat
      for(var i = 0; i < $.o.cats.length; i++) {

          if($.o.cats[i].cat_id == cat_id) {

              This_cat = $.o.cats[i];
              // assign a category name to display the title on the template

              $.o.view.cat_id = This_cat.cat_id;
              $.o.view.cat_name = This_cat.title;
              $.o.view.cat_url = This_cat.url;

              break;
          }
      }
      // end get the cat


      var OBJ_to_push = [];

      // Loop in product list for agregate by categories
      $.o.products.forEach(function(item, k) {

          // if cat is a leaf -> pass same cat products
          // if cat is a node -> pass all childs for all levels
          if( item.cat_id == cat_id && (This_cat.br - This_cat.bl) == 1
              || (This_cat.br - This_cat.bl) > 1 && This_cat.bl < item.cat_bl && This_cat.br > item.cat_br
              || item.cat_id == cat_id ){

              // Push article
              OBJ_to_push.push(item);

          }
          // END IF FOUND ITEM ID_cat

      });
      // END Loop in product list for agregate by categories


      // empty category case
      if( OBJ_to_push.length == 0 ){

          // show empty message
          $('#cat_title').empty().html($.o.tr.empty_product_cat);

          // SHOW BLOCK ERROR
          if($('#title_cat_block').css('display') == 'none') {

              $('#title_cat_block').css('display', 'block');
          }

          // close categories menu
          $.open_cats_menu();

          return;
      }

      // then continue ...

      // launch slider
      $.o.view.slider.show = true;
      $.launch_slider();

      $('.display_top_sort').css('display', 'inline-block');

      // IF SORT BLOCK WAS HIDDEN - SHOW IT
      $('#sort_block').css('display', 'flex');

      // breadcrumb render
      $.breadcrumb(cat_id);

      // TITLE CAT RENDER
      $.cat_render($.o.view.cat_name);

      // ADJUST $.o.view -> see paginatoin.js
      $.return_for_pagina(OBJ_to_push);

      // template products in context 'inline' / 'mozaic'
      var template =
        ($.o.view.display_products == 'inline') ?
        'products_view_inl' : 'products_view';

      $.show_center_page('fade', '#center_page', template);

      // alternative
      // $.show_page( event, 'cat' );

      // launch lazyload
      $.lazy_load_imgs();

      // add to icons aria-hidden=true attribute
      $.put_aria_hiddens();

      // close api modals
      $.close_all_menus();

      // enable touchSwipe
      $('#center_page').swipe('enable');

      // close menu sidebar if opened
      if($('#sidebar').css('display') == 'block') {

          $.open_sidebar();
      }

      // scroll to top sort block - need to add a lil timer
      // for wait the end of animation
      var ti_scroll = window.setTimeout(function() {

          $.scroll_to_elem('#sort_block', event);

          window.clearTimeout(ti_scroll);

      }, 100);


      // set good title of page
      document.title = $.o.view.cat_name + ' - ' + $.o.view.title;

      // HISTORY
      if(history.state.id != cat_id) {

          history.pushState({ page: 'cat', id: cat_id },
            '', $.o.view.cat_url + '/category/' + cat_id);
      }

    },
    /**
     * $.open_a_cat( id, event );
     */



    /**
     * $.cat_render( cat_name );
     *
     * @param  {string} cat_name  name of the category
     * @return {html}   show / hide title of the category requested
     */
    cat_render: function( cat_name ){

      if( cat_name ){

          $('#cat_title_text').empty().text(cat_name);
          $('#title_cat_block').css('display', 'block');

      }
      else {

          $('#title_cat_block').css('display', 'none');
          $('#cat_title_text').empty();
      }

    },
    /**
     * $.cat_render( cat_name );
     */



    /**
     * $.close_all_menus();
     *
     * @return {void}  close all menus -> #cats_menu / #select_nb_options
     * if opened
     */
    close_all_menus: function() {


      // close categories menu
      if( $('#cats_menu').is(':visible') ){

          $.open_cats_menu();
      }

      // close options nb_wanted
      if( $('#select_nb_options').is(':visible') ){

          $.open_choice_nb_pagina();
      }

      // Show / hide BUTTON #mobile_dev_all
      $.mobile_dev_all(event);

    },
    /**
     * $.close_all_menus();
     */



    /**
     * $.open_product( id, event );
     *
     * @param  {int}    id     product id
     * @param  {event}  event
     * @return {html}   open a product in details
     */
    open_product: function( id, event ){

      event.preventDefault();

      for( var i = 0; i < $.o.products.length; i++ ){

          if( $.o.products[i].id == id ){

              $.o.one_prod = $.o.products[i];
              break;
          }
      }

      // pass page context
      $.o.view.page_context = 'single_product';

      // remove sort block
      $.clean_sort_block();

      // show page single product
      $.show_page(event, 'single_product');

      // scroll to top
      $.scroll_top();

      // disable touchSwipe
      $('#center_page').swipe('disable');

      // launch swipe for product thunbails
      $.swipe_imgs();

      // put related products on bottom
      $.put_related_products();

      // record a stat for this product
      $.record_stat_for_one_product(id);

      // set good title of page
      document.title = $.o.one_prod.title + ' - ' + $.o.view.title;

      // HISTORY don't test state.page -> add new navigation products
      if( history.state.id != id ){

          history.pushState({ page: 'single_product', id: id }, '',
            $.o.one_prod.url + '/product/' + $.o.one_prod.id);
      }

    },
    /**
     * $.open_product( id, event );
     */



    /**
     * $.put_related_products();
     *
     * @return {html} add related products to one product view
     */
    slider_related_products: null,
    put_related_products: function(){


      // prep. an array
      var Related_prods = [];

      // Push same category and childs
      for( var i = 0; i < $.o.products.length; i++ ){

          // not push the product on page
          if( $.o.products[i].id == $.o.one_prod.id ){

              continue;
          }

          // push same cat or child cat
          if( $.o.products[i].cat_bl >= $.o.one_prod.cat_bl
              && $.o.products[i].cat_br <= $.o.one_prod.cat_br ){

              Related_prods.push($.o.products[i]);
          }
      }
      // end for

      // remove if already present
      if( $('#related_prods').length != 0 ){

          $('#related_prods').remove();
      }

      // no related -> pass related_prods.list to false
      if( Related_prods.length == 0 ){

          // exit here
          return;
      }
      // no related


      // add more duplicated products if not enought related products
      let win_width = window.innerWidth;

      // minimum number of related products desired for rendering related products
      let nb_wanted;

      // how number to complete the array
      switch( win_width ){

          case win_width >= 1024:
            nb_wanted = 5; // ( 4 + 1 )
          break;
          case win_width < 1024 && win_width >= 768:
            nb_wanted = 4; // ( 3 + 1 )
          break;
          case win_width < 768:
            nb_wanted = 3; // ( 2 + 1 )
          break;
          default:
            nb_wanted = 5;
      }
      // end switch

      // if there are not enough related products -> duplicate related products
      if( Related_prods.length < nb_wanted ){

          // the number that must be filled
          let nb_to_fill = nb_wanted - Related_prods.length;

          // loop to complete
          for( let i = 0; i < nb_to_fill; i++ ){

              Related_prods.push(Related_prods[i]);
          }
      }


      // assign related products
      $.o.related_prods = Related_prods;

      // append related products at the end of main#center_page
      $('#center_page').append().mustache('related_prods', $.o);


      // init. slider
      // re-init Slider $.slider_related_products
      if( $.slider_related_products ){

          $.slider_related_products.destroy();
          $.slider_related_products = null;
      }

      // easing functions for param `defaultAnimation`
      const easeOutCubic = (x)=>{
          return 1 - Math.pow(1 - x, 3);
      }
      // or
      const easeOutSine = (x)=>{
          return Math.sin((x * Math.PI) / 2);
      }
      // or
      const easeInOutSine = (x)=>{
          return -(Math.cos(Math.PI * x) - 1) / 2;
      }

      // keen-slider container #id
      let Keen_Container = '#related_prods_wrapper';

      // space between slides
      let Spacing = 10;

      // animation duration
      let AnimationDuration = 500;

      // slides per view by default
      let SlidesPerview = 4;

      // KEEN-SLIDER
      $.slider_related_products = new KeenSlider( Keen_Container,
        {

            mode: 'free-snap',
            // renderMode: 'performance',
            defaultAnimation : {
              duration: AnimationDuration,
              easing:  easeInOutSine,
            },
            loop: true,
            slides: {
              perView: SlidesPerview,
              spacing: Spacing,
            },
            breakpoints: {
                '(max-width: 1024px)': {
                  slides: {
                    perView: 3,
                    spacing: 16,
                  },
                },
                '(max-width: 767px)': {
                  slides: {
                    perView: 2,
                    spacing: 10,
                  },
                }
            }
        },
        [
          // slider functions
          (slider) => {

            $('.related_prods_prev').click(function(){
                slider.prev();
            });

            $('.related_prods_next').click(function(){
                slider.next();
            });
          }
          // end slider functions
        ]
      );

    },
    /**
     * $.put_related_products();
     */



    /**
     *  $.open_static_page( event, page_url );
     *
     * @param  {event}  event
     * @param  {string} page_url   string-url-formatted
     * @return {html}   open a static page
     */
    open_static_page: function( event, page_url ){


      event.preventDefault();

      // create an observer to check if this static file exists in the array
      var page_found = false;

      // find page by key url
      for( var url in $.o.static_pages ){

          if( $.o.static_pages.hasOwnProperty(url) ){

              if( url == page_url ){

                  page_found = true;
                  break;
              }
          }
      }

      // page not found
      if( page_found == false ){

          $.show_alert('warning', $.o.tr.page_not_found, false);
          return;
      }

      // make a Promise to load template if needed
      var prom = new Promise( function(resolve, reject){

        // load template if not loaded
        if( $.Mustache.has(page_url) == false ){

          $.post('/', { set: 'return_static_page', page_url: page_url },
            function(data) {

              if( data.success ){

                  // add html templ
                  $.Mustache.add(page_url, data.template);
                  // resolve promise
                  resolve(page_url);
              }

              if( data.error ){

                  $.show_alert('warning', data.error, false);

                  // reject
                  reject();

                  // end here
                  return;
              }

            }, 'json');

        }
        else{

            resolve(page_url);
        }

      });
      // END PROMISE

      // promise success
      prom.then( (page_url) => {


        $.clean_sort_block();

        $.show_page(event, page_url);

        $.scroll_top();

        // disable touchSwipe
        $('#center_page').swipe('disable');

        // set $.o.view.page_context
        $.o.view.page_context = 'page';

        // set good title of page
        document.title = $.o.static_pages[page_url].page_title + ' - ' + $.o.view.title;

        // HISTORY
        if( history.state.page != page_url ){

            history.pushState({ page: page_url }, '', '' + page_url + '.html');
        }

      });
      // end promise success

    },
    /**
     *  $.open_static_page( event, page_url );
     */



    /**
     * $.link_prod_copied
     * switcher for $.copy_link
     * @param  {bool}
     */
    link_prod_copied: false,


    /**
     * $.copy_link( elem, event );
     *
     * @param  {element}  elem  #copy_state -> id of a link
     * @param  {event}    event
     * Copy link product
     */
    copy_link: function( elem, event ){

      event.preventDefault();

      // if alerady copied ...
      if( $.link_prod_copied == true ){

          $('#copy_link_ico').removeClass('fa-check')
            .addClass('fa-share-alt');

          $('#copy_state').text($.o.tr.share_this_article)
            .addClass('underline').removeClass('no-deco');

          // pass to false
          $.link_prod_copied = false;

          return;
      }

      // KEEP URL OF LINK
      var url = $(elem).attr('href');

      // copy to clipboard
      navigator.clipboard.writeText(url).then( function(){

          // console.log('Async: Copying to clipboard was successful!');
          // Alert the copied text
          $('#copy_link_ico').removeClass('fa-share-alt')
            .addClass('fa-check');

          $('#copy_state').text($.o.tr.text_link_copied)
            .removeClass('underline').addClass('no-deco');

          // pass to true - is copied !
          $.link_prod_copied = true;

        },
        function(error) {

          $.show_alert('warning', error, false);
        });

    },
    /**
     * $.copy_link( elem, event );
     */



    /**
     *  $.getter_open_sidebar
     *  switcher for sidebar
     */
    getter_open_sidebar: false,


    /**
     * $.open_sidebar();
     *
     * OPEN MENU NAVBAR
     */
    open_sidebar: function(){


      if( $.getter_open_sidebar == false ){

          $('#sidebar').removeClass('animated zoomOutRight')
            .addClass('animated zoomInRight').show();

          $.getter_open_sidebar = true;

      }
      else{

          $('#sidebar').removeClass('animated zoomInRight')
            .addClass('animated zoomOutRight');

          $.animateCss('#sidebar', 'zoomOutRight', function() {

              $('#sidebar').css('display', 'none');
          });

          $.getter_open_sidebar = false;

      }

    },
    /**
     * $.open_sidebar();
     */



    /**
     *  $.open_cats_menu();
     */
    getter_cats_menu: false,
    open_cats_menu: function(){


      if( $.getter_cats_menu == false ){

          $('#cats_menu').removeClass('animated zoomOutLeft')
            .addClass('animated zoomInLeft').show();

          $.getter_cats_menu = true;

      }
      else{

          $('#cats_menu').removeClass('animated zoomInLeft')
            .addClass('animated zoomOutLeft');

          $.animateCss('#cats_menu', 'zoomOutLeft', function() {

              $('#cats_menu').css('display', 'none');

              $('#cats_menu ul.deploy')
                .addClass('off').removeClass('in');

              $('#cats_menu i.cat_icon')
                .css('transform', 'rotate(0deg)');

          });

          $.getter_cats_menu = false;
      }

    },
    /**
     *  $.open_cats_menu();
     */



    /**
     * $.deploy_cat( cat_id );
     * deploy sub categories
     */
    deploy_cat: function( cat_id ){

      if( $('#deploy_' + cat_id + '').hasClass('off') ){

          $('#deploy_' + cat_id + '').removeClass('off').addClass('in');

          $('#cat_icon-' + cat_id + '')
            .css('transform', 'rotate(90deg)');

      }
      else {

          $('#deploy_' + cat_id + '')
            .addClass('off').removeClass('in');

          $('#deploy_' + cat_id + ' ul.deploy')
            .addClass('off').removeClass('in');

          $('#cat_icon-' + cat_id + '')
            .css('transform', 'rotate(0deg)');

          $('#deploy_' + cat_id + ' i.cat_icon')
            .css('transform', 'rotate(0deg)');
      }

    },
    /**
     * $.deploy_cat( cat_id );
     */



    /**
     * $.send_message();
     *
     * @return {json} success / error -> send a message to admin
     */
    send_message: function(){


      $('#btn_send_mess').removeAttr('onclick')
        .append('&nbsp;<i class="spinner fas fa-circle-notch fa-spin fa-fw"></i>');

      var el_to_prevent = '#form_contact';
      var method = 'POST';
      var url = '/';
      var data_type = 'json';

      // create form data for AJAX POST
      var formElement = document.getElementById("form_contact");
      var datas = new FormData(formElement);

      // append command
      datas.append('set', 'send_mail_to_admin');

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data) {

          // success registration
          if( data.success ){

              // RENDER MESSAGE
              $.show_alert('success', data.success, false);

              // reset form
              $('#form_contact')[0].reset();

          }

          // error
          if( data.error ){

              // RENDER ERROR
              $.show_alert('warning', data.error, false);
          }
          // end error

          // remove spinner
          $('.spinner').remove();

          // re-attr onclick
          $('#btn_send_mess').attr('onclick', '$.send_message();');

      });
      // end sender

    },
    /**
     * $.send_message();
     */



    /**
     * $.drop_show( elem );
     *
     * @param  {element} elem
     * @return {html}    show / hide a dropdown element
     */
    drop_show: function( elem ){


      if( $(elem).css('display') == 'none' ){

          $('.drop_show').css('display', 'none');

          $(elem).css('display', 'block');

          return;

      }
      else {

          $('.drop_show').css('display', 'none');
      }

    },
    /**
     * $.drop_show( elem );
     */



    /**
     * $.fire_search_on_enter( input, event );
     *
     * Called by onkeydown event
     * @param  {string} input id of the field to watch
     * @param  {event}  event
     * @return {void}   call $.search() function on press key enter
     */
    fire_search_on_enter: function( input, event ){

      // if the key pressed is Enter & the field is filled
      if( event.key == 'Enter'
          && $('#search_input').val().length != 0 ){

          $.search(input, event);

      }
      else {

          return;
      }

    },
    /**
     * $.fire_search_on_enter( input, event );
     */



    /**
     *  $.render_search;
     *  {array}
     */
    render_search: [],

    /**
     * $.search( input, event );
     * -> carrefull pass '#myId' in param
     *
     * @param  {string} input id of input ex. '#myId'
     * @return {html}		search in products
     */
    search: function( input, event ){


      // text entered
      var value = $(input).val().toLowerCase();

      // length of text entered
      var len = value.length;

      // when length == 0 -> get default page
      if( len == 0 ){

          $.open_home();

          return;
      }

      // regex search word who start with chars entered
      var reg = new RegExp('^(' + value + ')', 'gi');

      // empty array if prev full
      $.o.render_search = [];

      // LOOP
      $.o.products.forEach( function(item, k){

        // keep title do the dust, explode words in an array
        // to lower in TITLE
        var search_in = item.title.toLowerCase();

        // do the dust
        search_in.replace(/(l')|(d')|(t')|(')|(-)|(s')|( -)|(- )|( - )|(°)|(<br \/>)|(.)/g, ' ');

        // init. ARR_words[]
        var ARR_words = search_in.split(' ');


        // for each words
        ARR_words.forEach( function(item_2, k_2){

            // take the number of letter entered as mask
            // to watch in an item of ARR_words
            item_2.substring(0, len);

            // if chars were matched but not if already present in array render_search
            if( item_2.match(reg) && $.o.render_search.indexOf(item) == -1 ){

                // put item in render search
                $.o.render_search.push(item);
            }
            // end if

        });
        // end SUB loop


      });
      // end  BIG loop

      // open home if search is requested from another page
      if( $.o.view.page_context != 'home' ){

          // this set $.o.view.page_context = 'home'
          $.open_home();
      }

      // IF NOT FOUND
      if( $.o.render_search.length == 0 ){

          // hide render searched value
          // note: use category title block to render searched value
          if( $('#title_cat_block').css('display') == 'block' ){

              $('#title_cat_block').hide();

              $('#cat_title_text').empty();
          }

          $('#center_page').empty()
            .html(`<div id="search_not_found">
            <p class="center xlarge mrg0 padding-large">
            ` + $.o.tr.empty_search + `</p></div>`);

      }
      else {

          // something was found

          // show .display_top_sort elements
          $('.display_top_sort').show();

          // ADJUST $.o.view -> see: pagination.js
          // -> $.return_for_pagina( OBJECT, NB_items_by page )
          $.return_for_pagina($.o.render_search);

          var template_products =
            ($.o.view.display_products == 'inline') ?
            'products_view_inl' : 'products_view';

          $.show_center_page('fade', '#center_page', template_products);

          // use category title block to render searched value
          $('#cat_title_text').html($.o.tr.your_research + `&nbsp;:&nbsp;` + value);

          $('#title_cat_block').show();

      }

      // scroll to sort block
      $.scroll_to_elem('#sort_block', event);

      // empty search field
      $(input).val('');

    },
    /**
     * $.search(input); -> carrefull pass '#myId' in param
     */



    /**
     *  $.erase_search( input );
     *
     * @param  {string} input search field to erase
     * @return {html}   re-init. products page by default, open home
     */
    erase_search: function( input ){

      // empty search field
      $(input).val('');

      $.open_home();

    },
    /**
     *  $.erase_search( input );
     */



    /**
     * $.show_sort_options();
     *
     * @return {type}  description
     */
    show_sort_options: function() {


      $('.display_top_sort')
        .not('.pagina, #cat_render, .pagination_arrows')
        .toggleClass('hide-small');

      $('#show_sort_options').removeClass('hide-small');

      if( $('.ico_show_sort').hasClass('fa-plus') == true ){

          $('.ico_show_sort').removeClass('fa-plus')
            .addClass('fa-minus');

      }
      else {

          $('.ico_show_sort').removeClass('fa-minus')
            .addClass('fa-plus');
      }

    },
    /**
     * $.show_sort_options();
     */



    /**
     * $.sort_by_price( order );
     *
     * @param  {str} 		order -> 'highest' / 'lowest'
     * @return {html}   products sorteds by price in context
     * 'home' -> products obj. 'cat' -> temp obj.
     */
    sort_by_price: function( order ){


      // makean array to store items
      var ARR = [];
      var OBJ = ($.o.view.page_context == 'home') ? $.o.products : $.o.view.temp;

      OBJ.forEach(function(item, k) {

          ARR.push(item);
      });

      if( order == 'highest' ){

          ARR.sort(function(a, b) {
            return b.price_tt_cent - a.price_tt_cent;
          });

      }
      else {

          ARR.sort(function(a, b) {
            return a.price_tt_cent - b.price_tt_cent;
          });

      }

      /* see : JS/pagination.js */
      $.return_for_pagina(ARR);

      // template products in context 'inline' / 'mozaic'
      var templ_products =
        ($.o.view.display_products == 'inline') ?
        'products_view_inl' : 'products_view';

      $.show_center_page('fade', '#center_page', templ_products);

    },
    /**
     * $.sort_by_price( order );
     */



    /**
     * $.display_products();
     *
     * Display products view 'inline' or 'mozaic'
     * SET : $.o.view.display_products = 'inline'/'mosaic';
     *
     */
    display_products: function(){

      // not run if window < 1000
      if( $('#products_view').length == 0
          || $(window).width() < 1000 ){

          return;
      }

      // inline view
      if( $('#products_view').hasClass('prods_inl') == false ){

          // manage icon display render
          $('.ico_display')
            .removeClass('fa-th-list')
            .addClass('fa-th');

          // set object
          $.o.view.display_products = 'inline';

          // re-init view with inline template
          $('#center_page').empty()
            .mustache('products_view_inl', $.o);

          // assign class .prods_inl for sepcial inline style
          $('#products_view').addClass('prods_inl');

      }
      else {
          // mozaic view

          $('.ico_display')
            .removeClass('fa-th')
            .addClass('fa-th-list');

          $.o.view.display_products = 'mosaic';

          // re-init view with mozaic template
          $('#center_page').empty()
            .mustache('products_view', $.o);

          // remove class .prods_inl
          $('#products_view').removeClass('prods_inl');
      }

      // lazy load img
      $.lazy_load_imgs();

      // put aria hiddens
      $.put_aria_hiddens();

    },
    /**
     * $.display_products();
     */



    /**
     * $.swipe_imgs();
     *
     * @return {void}  enable swipe on thunbails in single products view
     */
    swipe_imgs: function(){

      var max_swipe = 0;
      var imagesLoaded = 0;
      var total_width = 0;
      var width_container = Math.round($('.box_width_imgs_sup').width());

      $('.content_imgs_sup').css({ 'transition': 'margin-left 1s ease' });

      $('.content_imgs_sup img').on('load', function(event){

        // increm. imgs loaded
        imagesLoaded++;

        // all imgs are loaded
        if( imagesLoaded == $('.content_imgs_sup img').length ){

            $('.content_imgs_sup img').each(function(i, img) {

                total_width += $(img).width() + 10; // 10 margin-right
            });

            // calcul max swipe
            // this give negative int. (remove last margin (total_width-10) )
            max_swipe = Math.round(width_container - total_width);

        }
        // end if all imgs are loaded

      });
      // end on load


      var slide_value = 0;

      // SWIPE
      $('.content_imgs_sup').swipe({

        // Generic swipe handler for all directions
        swipeStatus: function( event, phase, direction, distance, duration, fingerCount, fingerData ){


          // SWIPE IN FUNCTION TO DIRECTION
          if( phase == 'move' ){


              if( direction == 'right' ){

                  // add a ponderator at distance ...
                  slide_value += Math.round(distance / 5);
              }
              else {
                  slide_value -= Math.round(distance / 5);
              }

              // not slide too much after
              if( slide_value <= max_swipe ){

                  slide_value = max_swipe;
              }

              // not slide too much before
              if( slide_value > 0 ){

                  slide_value = 0;
              }

              // apply marin-right with animation css to the thunbails
              $(this).css({ 'margin-left': slide_value + 'px' });

          }
          // end  phase == 'move'

        },
        // TouchSwipe options :
        triggerOnTouchEnd: false,
        allowPageScroll: "horizontal",
        threshold: 100,

      });
      // END SWIPE

    },
    /**
     * $.swipe_imgs();
     */



    /**
     * $.show_img( event );
     *
     * @param  {event} event
     * @return {void}  show thunbails on img first in one product view
     */
    show_img: function( event ){

      var src_first = $('img.first_img').attr('src');
      var alt_first = $('img.first_img').attr('alt');

      var target = event.target;

      // APPEND FIRST IMG TO THE END OF OTHERS IMG
      $('.content_imgs_sup')
        .append(`<img onclick="$.show_img( event);"
                  class="other_imgs"
                  src="` + src_first + `"
                  alt="` + alt_first + `">`);

      // IF TARGET IS THE BIG FIRST IMG
      if( $(target).hasClass('first_img') ){

          var next_src = $('img.other_imgs').first().attr('src');
          var next_alt = $('img.other_imgs').first().attr('alt');
          $('img.other_imgs').first().remove();

      }
      else {

          // IF TARGET IS A SMALL IMG
          var index_other = $(target).index();
          index_other++;

          var next_src = $('img.other_imgs:nth-child(' + index_other + ')').attr('src');
          var next_alt = $('img.other_imgs:nth-child(' + index_other + ')').attr('alt');
          $('img.other_imgs:nth-child(' + index_other + ')').remove();
      }

      // re-ATTR FIRST IMG
      $('img.first_img').prop('src', next_src);
      $('img.first_img').prop('alt', next_alt);

    },
    /**
     * $.show_img( event );
     */



    /**
     * $.mobile_dev_all( event );
     *
     * @return {html}  Show / hide BUTTON #mobile_dev_all
     * - ON MOBILE
     */
    mobile_dev_all: function( event ){

      // not fire if not visible
      if( $('#mobile_dev_all').is(':visible') == false ){

          return;
      }

      // HIDE
      if( $('#mobile_dev_all').data('mobile_open') == 'open'
          || $(event.currentTarget).attr('id') != 'mobile_dev_all' ){

          $('#cart_container, #cat_search_bar').hide('fast');

          $('#icon_mobile_dev_all')
            .addClass('fa-bars')
            .removeClass('fa-angle-double-up');

          $('#mobile_dev_all').data('mobile_open', false);

          return;
      }


      // SHOW
      if( !$('#mobile_dev_all').data('mobile_open') ){

          $('#cart_container, #cat_search_bar').show('slow');

          $('#icon_mobile_dev_all')
            .addClass('fa-angle-double-up')
            .removeClass('fa-bars');

          $('#mobile_dev_all').data('mobile_open', 'open');

          return;
      }

    },
    /**
     * $.mobile_dev_all();
     */



    /**
     * $.home_slider
     * Home slider by default
     */
    home_slider : null,

    /**
     * $.init_keen_slider();
     */
    init_keen_slider : function(){

        // show slideshow if not displayed
        if( $('#featured_products').css('display') == 'none' ){

            $('#featured_products').css('display', 'block');
        }

        // re-init Slider
        if( $.home_slider ){

            $.home_slider.destroy();
            $.home_slider = null;
        }

        // easing functions for param `defaultAnimation`
        const easeOutCubic = (x)=>{
            return 1 - Math.pow(1 - x, 3);
        }
        // or
        const easeOutSine = (x)=>{
            return Math.sin((x * Math.PI) / 2);
        }
        // or
        const easeInOutSine = (x)=>{
            return -(Math.cos(Math.PI * x) - 1) / 2;
        }

        // keen-slider container #id
        let Keen_Container = '#home_keen_slider';

        // space between slides
        let Spacing = 16;

        // animation duration
        let AnimationDuration = $.o.view.slider.speed;

        // delay to slide next image
        let DelayToSlide = $.o.view.slider.delay;

        // attibute to stop slider on dragEnded
        let SliderStopped = false;

        // KEEN-SLIDER
        $.home_slider = new KeenSlider( Keen_Container,
          {

              mode: 'snap',
              // renderMode: 'performance',
              defaultAnimation : {
                duration: AnimationDuration,
                easing:  easeInOutSine,
              },
              loop: true,
              slides: {
                perView: 1,
                spacing: Spacing,
              }
          },
          [
            (slider) => {

                // variable for Timeout
                let timeout = null;

                // clear Timeout
                function clearNextTimeout(){

                    clearTimeout(timeout);
                }

                // relaucnh a nwe Timeout
                function nextTimeout(){

                    if( timeout ){

                        clearTimeout(timeout);
                    }

                    // if drag end -> stop slider
                    // not auto-play -> stop here
                    if( SliderStopped || $.o.view.slider.play == false ){

                        return;
                    }

                    // timer for next slide
                    timeout = setTimeout(() => {

                        slider.next();

                    }, DelayToSlide );
                }

                // launch auto play on start
                slider.on('created', nextTimeout );
                // launch auto play next slide
                slider.on('animationEnded', nextTimeout );
                // launch slider on resize event
                slider.on('updated', nextTimeout );
                // stop slider on drag end
                slider.on('dragEnded', function(){
                    SliderStopped = true;
                });

            }
            // end slider functions
          ]
        );
        // end KEEN-SLIDER
    },
    /**
     * $.init_keen_slider();
     */



    /**
     * $.launch_slider();
     */
    launch_slider: function(){


      // return if slideshow is setted to display false
      if( $.o.view.slider.display == false
          || $.o.view.slider.show == false ){

          return;
      }

      // init swiper
      // $.init_swiper();
      $.init_keen_slider();

    },
    /**
     * $.launch_slider();
     */



    /**
     * $.breadcrumb( cat_id );
     *
     * @param  {int}    cat_id  id of a category
     * @return {html}   return a breadcrumb of subcategories
     */
    breadcrumb: function( cat_id ){

      // renew breadcrumb -> remove if
      if( $('#breadcrumb').is(':visible') ){

          $('#breadcrumb').remove();
      }

      // set a promise
      var prom = new Promise( function(resolve, reject){

          // get the cat
          for( var i = 0; i < $.o.cats.length; i++ ){

              if( $.o.cats[i].cat_id == cat_id ){

                  var This_cat = $.o.cats[i];
                  break;
              }
          }
          // end get the cat

          // prepa array parents to work
          var ARR_parents = [];

          // aggregate the parents of the cat
          for( var i = 0; i < $.o.cats.length; i++ ){

              // if bl is inferior and IF ITS A NODE
              if( $.o.cats[i].bl < This_cat.bl
                  && ( $.o.cats[i].br - $.o.cats[i].bl ) > 1
                  && $.o.cats[i].br > This_cat.br ){

                  // push only parents node
                  ARR_parents.push($.o.cats[i]);
              }
          }

          // if no prent - return !
          if( ARR_parents.length == 0 ){

              return;
          }

          // sort parent by bl < this cat
          ARR_parents.sort( (a, b) => a.bl - b.bl );

          // finally push This_cat in the end for comfort UI
          ARR_parents.push(This_cat);

          var string_bread = '';
          var arrow = '';

          // compose html breadcrumb
          for( var i = 0; i < ARR_parents.length; i++ ){

              arrow = (i == ARR_parents.length - 1) ? `` :
                `<span><i class="fa-angle-right fa-fw fas"></i></span>`;

              string_bread += `<a href="https://` + $.o.website.host +
                `/` + ARR_parents[i].url + `/category/` + ARR_parents[i].cat_id + `"
                title="` + ARR_parents[i].title + `"
                onclick="$.open_a_cat(` + ARR_parents[i].cat_id + `, event);"
                class="underline">` + ARR_parents[i].title + `</a> ` + arrow;
          }

          // resolve and return string_bread
          resolve(string_bread);

      });
      // end  set a promise

      // when promise
      prom.then( (string_bread) => {

          // insert breadcrumb
          $('#title_cat_block').before(`
            <div id="breadcrumb">` + string_bread + `</div>`);

          $('#breadcrumb').css('display', 'block');

      });

    },
    /**
     * end $.breadcrumb( cat_id );
     */



    /*
     *  $.lazy_load_imgs();
     *  Lazy Loading Image Loader
     */
    lazy_load_imgs: function(){


      // height of viewport
      var window_height = Math.round($(window).height()); // int.

      // value of scroll
      var window_scroll = Math.round($(window).scrollTop()); // int.

      // loop over imgs
      $('img.lazyload').each( function(i, elem){

          // calcul offset of img
          var offset_img = Math.round($(this).offset().top);
          // console.log('img '+(i+1)+ ' offset : '+offset_img);

          // must we show img ?
          var show_img = ((window_height + window_scroll) > offset_img) ?
            true : false;

          // watch if img src = loader or product img
          // - && watch if img is on the wiewport and if we must render it
          if($(this).attr('data-src') && show_img == true) {

            // -> attr good src to img and delete data-src
            $(this).attr('src', $(this).data('src'))
              .removeAttr('data-src')
              .removeClass('lazyload');
          }
          // end if img is on wiew port

      });
      // END loop over imgs

    },
    /*
     *  END $.lazy_load_imgs();
     */



    /**
     * $.sticky_header_init : false; // by default
     * enable / disable sticky header
     * see: const STICKY_HEADER = true/false; in JS/api_loader.js
     */
    sticky_header_init: false,


    /**
     * $.sticky_header();
     * see: const STICKY_HEADER = true/false; in JS/api_loader.js
     * for enable or diasble sticky header
     *
     * @return {void}  fix header on scroll or resize
     */
    sticky_header: function(){


      // no sticky header
      if( $.sticky_header_init == false ){

          return;
      }

      // if orientation == 'landscape' on mobile + NOT IN CART -> this bad UX
      if( $(window).width() < 800
          || ( $(window).width() < 800 && window.screen.orientation.type == 'landscape-primary' )
          || $.o.view.page_context == 'cart'
          || $.o.view.page_context == 'cat' ){


          // not fix header top -> screen is too few height
          $('#header_bar').css({
            'position': 'initial',
            'top': '-200px',
            'left': 'inherit',
            'width': 'inherit'
          });

          $('header').css('margin-top', '');

          return;
      }


      // sticky header on scoll - It make me crap !
      if( $(this).scrollTop() > Math.round($('#header_bar').innerHeight()) ){

          var width_win = $('html').innerWidth();
          var width_header = $('body').width(); // retr. padding body -> use with()
          var left = (width_win - width_header) / 2; // let left in float

          $('#header_bar').css({
            'position': 'fixed',
            'top': '0',
            'left': left + 'px',
            'width': width_header + 'px',
            'z-index': '90'
          });

          $('header').css('margin-top', Math.round($('#header_bar').innerHeight()));

      }
      else if( $(this).scrollTop() == 0 ){

          $('#header_bar').css({
            'position': 'initial',
            'top': '-200px',
            'left': 'inherit',
            'width': 'inherit'
          });

          $('header').css('margin-top', '');
      }
      // end  sticky header on scoll

    },
    /**
     * $.sticky_header();
     */



    /**
     * $.put_aria_hiddens();
     *
     * @return {void}  pass all icons to aria-hidden="true"
     */
    put_aria_hiddens: function(){

        $('i.fas, i.far').attr('aria-hidden', 'true');
    },
    /**
     * $.put_aria_hiddens();
     */



    /**
     * $.swipe_products();
     *
     * i. touchSwipe disable inputs, must be enabled / disabled
     * @return {html}  Swipe left / right on the products
     * for simulate a pagination click
     */
    swipe_products: function() {


      // max distance to fire
      var distance_max = 90;

      $('#center_page').swipe({

        // ! carrefull swipeStatus != swipe funct.
        // in swipe
        swipeStatus: function(event, phase, direction, distance) {

          if( phase == 'move'
              && ( direction != 'right' && direction != 'left' ) ){

              return;
          }

          // enable it only on products page
          if( $.o.view.page_context != 'home'
              && $.o.view.page_context != 'cat' ){

              return;
          }

          // move
          if( phase == 'move'
              && distance <= distance_max ){

              distance = (direction == 'right') ?
                distance : Math.round(distance * -1);

              $('#products_view').css({
                'transition': 'transform 0.6s ease',
                'transform': 'translateX(' + distance + 'px)'
              });

          }

          // end
          if( (phase == 'end' || phase == 'cancel')
              && distance <= distance_max ){

              $('#products_view').css({
                'transition': 'transform 0.6s ease',
                'transform': 'translateX(0)'
              });
          }

          // next page
          // move
          if( (phase == 'move')
              && distance > distance_max ){


              // need to pass overflow-x hidden for unbug mobile devices
              $('body').css('overflow-x', 'hidden');

              // go to prev / next page
              distance = (direction == 'right') ?
                $.pagina('prev') :
                $.pagina('next');

              // remove start transition after fire $.pagina(...)
              $('#products_view').css({
                'transition': '',
                'transform': ''
              });

              // remove overflow-x hidden form body
              var ti = window.setTimeout(function(){

                  $('body').css('overflow-x', '');

                  window.clearTimeout(ti);

              }, 600);
          }

        },
        // options :
        triggerOnTouchEnd: false,
        allowPageScroll: "horizontal",
        preventDefaultEvents: true,
        threshold: 100,

      });
      // END TOUCHSWIPE

    },
    /**
     * $.swipe_products();
     */



  });
  // END EXTEND


  /////////////////////////////////
  /////  E N D   E X T E N D  /////
  /////////////////////////////////

  //////////////////////////
  /////   JS/jQuery   //////
  //////////////////////////

  ///////////////////////////////////////////////
  ///////   WEB  CONNECTED / NOT CONNECTED  /////
  ///////////////////////////////////////////////

  function online_status( event ){

      if( navigator.onLine == false ){

          $.show_alert('warning', $.o.tr.waning_not_connected, true);
      }

      if( navigator.onLine == true ){

          $.show_alert('info', $.o.tr.connection_restored, false);
      }
  }

  window.addEventListener('offline', online_status);
  window.addEventListener('online', online_status);


  ///////////////////////////////////
  /////    O N  R E S I Z E    //////
  ///////////////////////////////////

  $(window).on('resize', function(){


      // set view in mozaic products view if window < 1000 px
      if( $(window).width() < 1000
          && $('#products_view').length != 0
          && $('#products_view').hasClass('prods_inl') == true ){


          $('.ico_display')
            .removeClass('fa-th')
            .addClass('fa-th-list');

          $.o.view.display_products = 'mosaic';

          // re-init view with mozaic template
          $('#center_page').empty()
            .mustache('products_view', $.o);

          // remove class .prods_inl
          $('#products_view').removeClass('prods_inl');

      }

      // launch lazyload on resize
      $.lazy_load_imgs();

      // fix header
      $.sticky_header();

      // show / dev items header_bar
      if( $(window).width() > 768
          && $('#cart_container, #cat_search_bar').is(':visible') == false ){

          $('#cart_container, #cat_search_bar').show('slow');
      }

  });

  ////////////////////////////////////////////
  /////   D O C U M E N T    C L I C K   /////
  ////////////////////////////////////////////

  // DOCUMENT CLICK
  $(window).on('click', function(e){


      // TARGETs to unbind click // #open_login, #open_registration
      var container = $(`#sidebar,
        #close_sidebar,
        #open_sidebar,
        .modal-content,
        .open_modal,
        #img_cont,
        #cats_menu`);

      // IF NOT TARGETTED
      if( !container.is(e.target) && container.has(e.target).length === 0 ){

          // getter sidebar
          if( $.getter_open_sidebar == true ){

              $.open_sidebar();
          }

          // DROP DONW - If ANY ONE OF VENORS LIST OR CATS LIST IS SHOW - HIDE BY CLASS
          if( $('#vendors_list.drop_show').css('display') == 'block'
              || $('#cats_list.drop_show').css('display') == 'block'
              || $('#user_cats_list.drop_show').css('display') == 'block' ){

              $('.drop_show').css('display', 'none');
          }

          // HIDE DIAPORAMA
          if( $('#render_diapo').css('display') == 'block' ){

              $.close_slideshow();
          }

          // close select nb_options
          if( $('#select_nb_options').css('display') == 'block' ){

              $.open_choice_nb_pagina();
          }

          // close #cats_menu
          if( $.getter_cats_menu == true ){

              $.open_cats_menu();
          }

      }
      // end IF NOT TARGETTED

  });
  // END DOCUMENT CLICK


  /////////////////////////////////
  /////   O N   S C R O L L   /////
  /////////////////////////////////

  // ON SCROLL
  var Scroll_top_action = false;
  var Scroll_Timer;

  // TO TOP BTN - loader imgs
  $(window).on('scroll touchmove mousewheel', function(e){


    // fix header
    // see: $.sticky_header_init = true/false; in JS/api_loader.js
    // for enable or diasble sticky header
    $.sticky_header();

    // TO TOP
    if( $(this).scrollTop() > 50 ){

        $('#to_top').fadeIn();
    }
    else {

        $('#to_top').fadeOut();
    }

    // for update on end of the scroll
    if( Scroll_top_action === false ){

        clearTimeout(Scroll_Timer);

        Scroll_Timer = setTimeout(function(){

            // launch img loader
            $.lazy_load_imgs();

        }, 300);

    }
    // end if Scroll_top_action === false

  });
  // END WINDOW.SCROLL()



  // click btn TOP
  $('#to_top').click(function(e){

      e.stopImmediatePropagation();

      Scroll_top_action = true;

      $('html,body').animate({ scrollTop: 0 }, 300, function(){

          Scroll_top_action = false;
      });

  });
  // END TO TOP



});
// END JQUERY
