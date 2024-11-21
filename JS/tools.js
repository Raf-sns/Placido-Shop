/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello  2019-2024
 * Organisation: SNS - Web et informatique
 * Website/contact: https://sns.pm
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
   * @return {void}  remove the slideshow and breadcrumbs as well as the block for sorting items
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
   * @param  {element}  elem html element
   * @return {void}     Prevent default link behavior
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

            if( xhr.status == 200 ){

                if(typeof callback === 'function'){

                    callback(data);
                }
            }

            if( xhr.status != 200 ){

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
   * @param  {string} type :
   * 'info'     -> blue background
   * 'success'  -> green background
   * 'warning'  -> orange background
   * 'error'    -> red background
   * @param  {string} html         pop-up message content
   * @param  {bool}   still_open   true/false -> pop-up should rest open or not
   *
   */
  show_alert : function( type, html, still_open ){

      // close pop-up
      if( type == false ){

          toastr.clear();

          // end here
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

          // pop-up should rest open
          toastr.options.timeOut = 0;
          toastr.options.extendedTimeOut = 0;
      }

      // launch toastr function
      toastr[type]( html );
  },
  /**
   * $.show_alert( type, html, still_open );
   */



  /**
   *  $.show_center_page( direction, div, template );
   *
   * @param  {string}       direction  dir -> 'next', 'prev', 'fade'
   * @param  {HTMLelement}  div        div to move
   * @param  {string}       template   template to show
   * @return {html}         display a page with diferent animation
   */
  show_center_page : function( direction, div, template ){


    // DECIDE WHETHER OR NOT TO SCROLL THE PAGE TO TOP
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

        // TO MEDIT ...
        // if( template == 'products_view' ){
        //
        //    scroll_to_top = true;
        // }
    }

    var class_anim_1;
    var class_anim_2;

    // slide animation
    if( direction == 'next' || direction == 'prev' ){

        class_anim_1 = 'slideOutLeft';
        class_anim_2 = 'slideInRight';

        // override for next
        if( direction == 'next' ){

            class_anim_1 = 'slideOutRight';
            class_anim_2 = 'slideInLeft';
        }

        $(div).css('animation-duration', '0.3s');

    }
    // end slide animation

    // fade animation
    if( direction == 'fade' ){

        class_anim_1 = 'fadeOut';
        class_anim_2 = 'fadeIn';

        $(div).css('animation-duration', '.3s'); // .3s
    }
    // end fade animation

    // animation start
    $.animateCss(div, class_anim_1, function(){

        // fill the view
        $(div).empty().mustache(template, $.o );

        // anim 2
        $.animateCss(div, class_anim_2, function(){

						// here lazy load
            $.lazy_load_imgs();

						$.put_aria_hiddens();

        });
        // end anim 2

    });
    // end start anim 1

  },
  /**
   *  $.show_center_page( direction, div, template );
   */


//////////////////////////////////////////////////////////////
////////////////       S C R O L L I N G         /////////////
//////////////////////////////////////////////////////////////


  /**
   * $.scroll_to_elem( elem, event );
   *
   * @param  {HTMLelement}  elem
   * @param  {event}        event
   * @return {void}         srcoll to an element
   */
  scroll_to_elem : function(elem, event){

    event.preventDefault();

    // Animation duration (in ms)
    var speed = 700;

		// animate the scroll
    $('html, body')
		.animate( { scrollTop: Math.round($(elem).offset().top) }, speed );

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


/////////////////////////////////////////////
///////   ANIMATE   CSS   FUNCTION    ///////
/////////////////////////////////////////////


  /**
   * $.animateCss( element, animationName, callback );
   *
   * @param  {HTMLelement}  element  element to animate
   * @param  {string}   animationName animate CSS animation name
   * @param  {function/null}   callback  function to fire after animation end
   * @return {void}   animate an element and call a function at the end of animation
   */
  animateCss : function(element, animationName, callback){


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
    // end function


    var transEndEventName = whichTransitionEvent();

    // 'webkitAnimationEnd oanimationend msAnimationEnd animationend'
    $(element).addClass("animated "+animationName+"");

    $(element).one(transEndEventName, function(e){

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
