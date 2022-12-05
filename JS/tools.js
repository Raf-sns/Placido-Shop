/**
 * PlACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello  2019-2021
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	tools.js
 *
 * $.clean_sort_block();
 * $.prevent_links( elem );
 * $.sender( el_to_prevent, method, url, datas, data_type, callback );
 * $.show_alert( type, html, still_open );
 * $.show_center_page( dir, div, template );
 * $.scroll_to_elem( elem, event );
 * $.scroll_top();
 * $.loadXHR( url );
 * $.animateCss( element, animationName, callback );
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({



  /**
   * $.clean_sort_block();
   *
   * @return {void}  remove slider breadcrumb sorting elements
   */
  clean_sort_block : function(){

			if( $.swiper != null ){

          $.swiper.destroy(true, true);
      }

      $('#featured_products, #sort_block, #title_cat_block, .display_top_sort')
      .css('display', 'none');

      // remove breadcrumb
      $('#breadcrumb').remove();

  },
  /**
   * $.clean_sort_block();
   */



  /**
   *  $.prevent_links( elem );
   *
   * @param  {element} elem html element
   * @return {void}      Prevent default link
   */
  prevent_links : function( elem ){

      $(elem).on('click', function(e){ e.preventDefault(); });
  },
  /**
   *  $.prevent_links( elem );
   */



  /**
   * $.sender( el_to_prevent, method, url, datas, data_type, callback );
   *
   * @param  {element} el_to_prevent  element to prevent OR false
   * @param  {str} method             method for send 'POST' or 'GET'
   * @param  {str} url                url to send request
   * @param  {FormData} datas         obj. FormData of datas sended
   * @param  {str} data_type          dataType 'json' or 'html' ...
   * @param  {function} callback      function callback
   * @return {json}                   JSON returned by the server
   */
  sender : function(el_to_prevent, method, url, datas, data_type, callback ){

      if(el_to_prevent != false ){
        // fonction $.prevent() -> preventDefault on submit
        $.prevent_links(el_to_prevent);
      }

      // ajax req.
      $.ajax({
        method: method,
        url: url,
        data: datas,
        processData: false,  // indique à jQuery de ne pas traiter les données
        contentType: false,   // indique à jQuery de ne pas configurer le contentType
        async : true, // ASYNC IS TRUE !! - callback rocks !
        cache : false,
        dataType : data_type,
        success: function(data, textStatus, xhr) {

            // console.log(xhr.status);

            if( xhr.status == 200 ){

                if(typeof callback === 'function'){

                    callback(data);
                }
            }

            if( xhr.status != 200 ){

                console.log( textStatus );
                $.show_alert('error', xhr.textStatus, false);
            }

        },
        error: function(data) {
        	// called when there is an error
        	$.show_alert('error', data.error, false);
        }

      });
      // END AJAX


  },
  /**
   * $.sender( el_to_prevent, method, url, datas, data_type, callback );
   */



  /**
   * $.show_alert( type, html, still_open );
   *
   * Alerts where managed by toastr.js
   * @param  {str} type :
   * 'info'
   * 'success'
   * 'warning'
   * 'error'
   * @param  {str} html         html alert content
   * @param  {bool} still_open  true/false -> alert should rest open
   *
   */
  show_alert : function( type, html, still_open ){

      if( type == false ){

          toastr.clear();
          return;
      }

      toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": toastr.remove(),
        "showDuration": "50",
        "hideDuration": "200",
        "timeOut": "2000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "slideDown",
        "hideMethod": "slideUp"
      };

      if( still_open == true ){
        // get alert still opened
        toastr.options.timeOut = 0;
        toastr.options.extendedTimeOut = 0;
      }

      toastr[type]( html );

  },
  /**
   * $.show_alert( type, html, still_open );
   */



  /**
   *  $.show_center_page( dir, div, template );
   *
   * @param  {str}         dir      dir -> 'next', 'prev', 'fade'
   * @param  {HTMLelement} div      description
   * @param  {str}         template description
   * @return {html}        show a page with diferent animation
   */
  show_center_page : function(dir, div, template){


    // TO DECIDE TO SCROLL OR NOT TO TOP PAGE
    var scroll_to_top = false;

    // HIDE SORT BTNs IN PAGE TEXT TEMPLATE
    if( template == 'single_product' ||
        template == 'about_us' ||
        template == 'terms_of_sale' ||
        template == 'right_to_retract'  ){

        $('#sort_block, #featured_products').css('display', 'none');

        scroll_to_top = true;

    }
    else{

        // show slider
        if( $.o.view.slider != false ){

            $.launch_slider(); // launch slider
        }

        // IF SORT BLOCK WAS HIDDEN - SHOW IT
        $('#sort_block').css('display', 'flex');

        // TO MEDIT ......
        // if( template == 'products_view' ){
        //
        //    scroll_to_top = true;
        // }
    }


    var hei =  $('#center_page').height();
    hei = Number( hei.toFixed(0) );
    // console.log(hei);

    var class_anim_1;
    var class_anim_2;
    var duration;

    // slide animation
    if( dir == 'next' || dir == 'prev' ){

        class_anim_1 = 'slideOutLeft';
        class_anim_2 = 'slideInRight';

        // override for next
        if( dir == 'next' ){

            class_anim_1 = 'slideOutRight';
            class_anim_2 = 'slideInLeft';
        }

        $(div).css('animation-duration', '0.3s');

        duration = 300;

    }

    if( dir == 'fade' ){

        class_anim_1 = 'fadeOut';
        class_anim_2 = 'fadeIn';

        $(div).css('animation-duration', '.3s'); // .3s

        duration = 300;

    }

    // $('#center_page').css('height', hei+'px');

    // anim start
    $.animateCss(div, class_anim_1, duration, function(){


        // fill the VUE
        $(div).empty().mustache(template, $.o );


        // anim 2
        $.animateCss(div, class_anim_2, duration, function(){

            // if( scroll_to_top == true ){
						//
            //     $.scroll_top();
            // }

            $.lazy_load_imgs(); // here lazy load

						$.put_aria_hiddens();

            // $.swipe_imgs();

        });
        // end anim 2

    });
    // end start anim 1

  },
  /**
   *  $.show_center_page( dir, div, template );
   */


//////////////////////////////////////////////////////////////
////////////////       S C R O L L I N G         /////////////
//////////////////////////////////////////////////////////////


  /**
   * $.scroll_to_elem( elem, event );
   *
   * @param  {HTMLelement}  elem  description
   * @param  {event}        event description
   * @return {void}         srcoll to an alement
   */
  scroll_to_elem : function(elem, event){

    event.preventDefault();

		// include header bar pdding
		var pad_top = ( $('#featured_products').is(':visible') == true )
		? $('#header_bar').outerHeight(true)
		: 0;

    var speed = 700; // Durée de l'animation (en ms)

		// animate the scroll
    $('html, body')
		.animate( { scrollTop: Math.round($(elem).offset().top-pad_top) }, speed );

  },
  /**
   * $.scroll_to_elem( elem, event );
   */



  /**
   * $.scroll_top();
   *
   * @return {void}  scroll to top
   */
  scroll_top : function(){

    var speed = 300;
    $('html, body').animate( { scrollTop: 0 }, speed );

  },
  /**
   * $.scroll_top();
   */


////////////////////////////////////////////////
//////////   LOAD  XHR    //////////////////////
////////////////////////////////////////////////


  /**
   * $.loadXHR( url );
   *
   * @param  {str} url      promise xhr to render xhr infos
   * @return {promise}      promise
   */
  loadXHR : function( url ){

    return new Promise(function(resolve, reject) {
       try {
           var xhr = new XMLHttpRequest();
           xhr.open("GET", url);
           xhr.responseType = "blob";
           xhr.onerror = function() {reject("Network error.")};
           xhr.onload = function() {
               if (xhr.status === 200) {resolve(xhr.response)}
               else {reject("Loading error:" + xhr.statusText)}
           };
           xhr.send();
       }
       catch(err){ reject(err.message) }
    });

  },
  /**
   * $.loadXHR( url );
   */

//////////////////////////////////////////////////////////////
///////    A N I M A T E   C S S   F U N C °    //////////////
//////////////////////////////////////////////////////////////


  /**
   * $.animateCss( element, animationName, callback );
   *
   * @param  {type} element       description
   * @param  {type} animationName description
   * @param  {type} duration      description
   * @param  {type} callback      description
   * @return {type}               description
   */
  animateCss : function(element, animationName, duration, callback) {


    function whichTransitionEvent(){

      var el = document.createElement('fake');

      var transEndEventNames = {
          'WebkitAnimation' : 'webkitAnimationEnd',// Saf 6, Android Browser
          'MozAnimation'    : 'animationend',      // only for FF < 15
          'animation'       : 'animationend'       // IE10, Opera, Chrome, FF 15+, Saf 7+
      };

      for( var t in transEndEventNames ){

          if( el.style[t] !== undefined ){

              return transEndEventNames[t];
          }
      }

    }
    // end funct.


    var transEndEventName = whichTransitionEvent();

    // 'webkitAnimationEnd oanimationend msAnimationEnd animationend'
    $(element).addClass("animated "+animationName+"");

    $(element).one(transEndEventName, function(e) {

        if (typeof callback === 'function') {

            callback();

            $(element).removeClass("animated "+animationName+"");

        }

    });

  },
  /**
   * $.animateCss( element, animationName, callback );
   */



});
// END EXTEND


  // animateCSS in pure js
  const animateCSS = (element, animation, prefix = 'animate__') =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {

      const animationName = `${prefix}${animation}`;

      const node = document.querySelector(element);

      node.classList.add(`${prefix}animated`, animationName);

      // When the animation ends, we clean the classes and resolve the Promise
      function handleAnimationEnd() {
        node.classList.remove(`${prefix}animated`, animationName);
        resolve('Animation ended');
      }

      node.addEventListener('animationend', handleAnimationEnd, {once: true});
  });
  // animateCSS in pure js



});
// END jQuery
