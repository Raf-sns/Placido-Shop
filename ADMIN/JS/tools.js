/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: tools.js
 *
 * - Scripts js loader
 * - Templates js loader
 * $.token_timer();
 *
 * Extented :
 *
 * $.lazy_load_imgs();
 * $.prevent_links( elem );
 * $.sender( el_to_prevent, method, url, datas, data_type, callback );
 * $.show_process( end );
 * $.show_alert( type, html, still_open );
 * $.scroll_to_elem( elem, event );
 * $.scroll_top();
 * $.close_modal();
 * $.animateCss( element, animationName, callback );
 * $.obj : { 'files' : []  };
 * $.index_box_img;
 * $.img_viewer();
 * $.show_img_selected( index_box_img , img , name_img, check , checked );
 * $.loadXHR( url );
 * $.img_modifier( id );
 * $.remove_product_img( box_img );
 * $.move_img( name_box, dir );
 * $.append_img_shop( img );
 *
 */


// start jQuery
$(function(){

  // LOAD SCRIPTS
  const ARR_scripts = [
		'JS/archives.js',
		'JS/cats.js',
		'JS/ip_rejected.js',
		'JS/main.js',
		'JS/messages.js',
    'JS/new_sales.js',
    'JS/products.js',
		'JS/pwa.js',
		'JS/settings.js',
    'JS/shop.js',
		'JS/static_pages.js',
		'JS/web_app.js'
  ];

	// load all scripts API
	ARR_scripts.forEach((item, i) => {
		// use getScript jQuery funct.
		$.getScript( item );
	});


  // load templates :
	const Templates = [
		'templates/archives.html',
		'templates/categories.html',
		'templates/featured_prods.html',
		'templates/messages.html',
		'templates/settings.html',
		'templates/shop.html',
		'templates/stats.html',
		'templates/products.html',
		'templates/ip_rejected.html',
		'templates/static_pages.html',
		'templates/web_app.html'
	];

	// add templates to Mustache
	Templates.forEach((item, i) => {

			$.Mustache.load(item);
	});



	// LOAD HTML AND JS FROM MODULES
	if( $.o.modules.length != 0 ){

			let ARR_scripts_modules = [];

			let ARR_HTML_modules = [];

			// loop modules
			for (let module in $.o.modules ) {

					// continue if no scripts
					if( typeof $.o.modules[module].autoload == 'undefined' ){
						continue;
					}

					// loop in autoload and charge JS or HTML modules scripts
					$.o.modules[module].autoload.forEach((item, i) => {

							// if JS module
							var regex_JS = /^JS\//;

							if( item.match( regex_JS ) ){

									// in autoload, we find path of modules
									ARR_scripts_modules.push(module+'/'+item);
							}

							// if HTML module
							var regex_HTML = /^HTML\//;

							if( item.match( regex_HTML ) ){

									// in autoload, we find path of modules
									ARR_HTML_modules.push(module+'/'+item);
							}

					});
					// end loop in autoload


			}
			// end loop modules

			// console.log( ARR_HTML_modules );

			// load all modules scripts
			ARR_scripts_modules.forEach((item, i) => {
					// use getScript jQuery funct.
					$.getScript( '/MODULES/'+item );
			});

			// load all HTML modules
			// add them to Mustache
			ARR_HTML_modules.forEach((item, i) => {

					$.Mustache.load( '/MODULES/'+item );
			});

	}
	// end  LOAD HTML AND JS FROM MODULES



  /**
   * token_timer();
   */
  var token_timer_interval = null;

  function token_timer(){

      if( token_timer_interval != null  ){

					window.clearInterval( token_timer_interval );
      }

      var include_once_countDown = false;
      var stamp_now = Math.floor(Date.now() / 1000);
      var max_stamp = stamp_now + $.o.user.token_max_time;

      var txt_cool = 'text-light-green';
      var txt_warn = 'text-amber';
      var txt_danger = 'text-deep-orange';

      // Update the count down every 1 second
      token_timer_interval = setInterval(function() {

          // Get today's date and time
          stamp_now = Math.floor(Date.now() / 1000); // in sec.
          // console.log(stamp_now);

          // Set the date we're counting down to
          var countDownDate = new Date(max_stamp).getTime();
          // Find the distance between now and the count down date
          var distance = countDownDate - stamp_now;
          // console.log(distance);

          // Time calculations for days, hours, minutes and seconds
          var days = Math.floor(distance / (60 * 60 * 24));
          var hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
          var minutes = Math.floor((distance % (60 * 60)) / (60));
          var seconds = Math.floor((distance % (60)) / 1);

          // console.log( days + ` jour(s) ` + hours + ` h `
          // + minutes + ` m ` + seconds + ` s ` );

          var countDown = ``;

          countDown = ( days > 0 ) ?
          countDown+ days + ` `+$.o.tr.days+` ` : countDown;

          countDown = ( hours > 0 ) ?
          countDown+ hours +$.o.tr.hours+` ` : countDown;

          countDown = ( minutes > 0  ) ?
          countDown+minutes +$.o.tr.minutes+` ` : countDown;

          seconds = ( seconds < 10 ) ? '0'+seconds : seconds;
          // show seconds under 6 minutes
          countDown = ( seconds >= 0 && minutes < 5 ) ?
          countDown+seconds +$.o.tr.seconds : countDown;

          // txt_cool > 300
          if( distance > 300 ){

              // Display the result in the element with id="token_timer"
              $('#token_timer').addClass(txt_cool)
              .html($.o.tr.validity_token+' '+countDown);
          }
          // 300 < txt_warn > 120
          if( distance <= 300 && distance > 120 ){

              $('#token_timer').removeClass(txt_cool)
              .addClass(txt_warn)
              .html($.o.tr.validity_token+' '+countDown);
          }
          // 120 < txt_danger > 0
          if( distance <= 120 && distance > 0 ){

              $('#token_timer').removeClass(''+txt_warn+' '+txt_cool+'')
              .addClass(txt_danger)
              .html($.o.tr.validity_token+' '+countDown);
          }
          // over time token
          if( distance <= 0 ){

              $('#token_timer')
              .removeClass(''+txt_warn+' '+txt_cool+'')
              .addClass(txt_danger)
              .html($.o.tr.expired_token);

              // clear interval
              window.clearInterval( token_timer_interval );

              // delete object
              delete $.o;

              // reload to login page
              location.reload();

              return;
          }


      }, 1000 ); // fire all seconds

  };
  /**
   * token_timer();
   */

  // start token timer
  token_timer();

// EXTEND ALL METHODS -> Call them width $.myMethod()
$.extend({



  /**
   *  $.lazy_load_imgs();
   *  Lazy Loading Image Loader
   *  set data-src and class="lazyload" for each imgs
   */
  lazy_load_imgs : function(){


      // height of viewport
      var window_height = Math.round( $(window).height() ); // int.

      // value of scroll
      var window_scroll = Math.round( $(window).scrollTop() ); // int.

      // console.log('window_height '+window_height);
      // console.log('window_scroll '+window_scroll);

      // loop over imgs
      $('img.lazyload').each(function( i, elem ){

          // calcul offset of img
          var offset_img = Math.round( $(this).offset().top );

          // must we show img ?
          var show_img =
					( (window_height + window_scroll) > offset_img )
          ? true : false;

          // watch if img src = loader or product img
          // - && watch if img is on the wiewport and if we must render it
          if( $(this).attr('data-src') && show_img == true ){

              // if not -> attr good src to img and delete data-src
              $(this).attr('src', $(this).data('src') )
              .removeAttr('data-src');
          }
          // end if img is on wiew port

      });
      // END loop over imgs

  },
  /**
   *  $.lazy_load_imgs();
   */



  /**
   * $.prevent_links( elem );
   *
   * @param  {htmlElement} 	elem
   * @return {void}      		Prevent default behaviour
   */
  prevent_links : function(elem){

      $(elem).on('click', function(e){ e.preventDefault(); });
  },
  /**
   * $.prevent_links( elem );
   */



  /**
   * $.sender( el_to_prevent, method, url, datas, data_type, callback );
   *
   * @param  {string} 	el_to_prevent element to prevent OR false
   * @param  {string} 	method        method for send 'POST' or 'GET'
   * @param  {string} 	url           url to send
   * @param  {object} 	datas         obj of datas sended
   * @param  {string} 	data_type     datas type returned by the server 'json' or 'html' ...
   * @param  {function} callback      function to call at the return of server
   * @return {ajax}               		provide an ajax function
   */
  sender : function( el_to_prevent, method, url, datas, data_type, callback ){


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
        beforeSend : $.show_process(),
        success: function(data) {

            if (typeof callback === 'function'){

                callback(data);
            }

        },
        error: function(data) {

						//called when there is an error
	        	console.log(data);
        }

      });
      // END AJAX

  },
  /**
   * $.sender( el_to_prevent, method, url, datas, data_type, callback );
   */



  /**
   * $.show_process( end );
   *
   * @param  {string} end terminate the process
   * @return {void}     	show / hide a process bar
   */
  show_process : function(end){

      if(end){

					$('#render_process').css({'width': '0%'});
        	return;
      }

      $('#render_process')
      .animate({'width': '100%'}, 2000, function(){
        	$('#render_process').css({'width': '0%'});
      });

  },
  /**
   * $.show_process( end );
   */



  /**
   * $.show_alert( type, html, still_open );
   *
   * @param  {str} type :
   * 'info'
   * 'success'
   * 'warning'
   * 'error'
   * @param  {str} html
   * @param  {bool} still_open true/false
   *
   */
  show_alert : function( type, html, still_open ){

			toastr.clear();

      if( type == false ){
        return;
      }

      toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-center",
        "preventDuplicates": true,
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
   * $.scroll_to_elem( elem, event );
   *
   * @param  {htmlElement} 	elem
   * @param  {event} 				event js event
   * @return {void}       	scroll to an element
   */
  scroll_to_elem : function(elem, event){

      event.preventDefault();

			// speed of scroll (in ms)
      var speed = 600;

			// Go
      $('html, body').animate( { scrollTop: $(elem).offset().top }, speed );

  },
  /**
   * $.scroll_to_elem( elem, event );
   */



  /**
   * $.scroll_top();
   *
   * @return {type}  description
   */
  scroll_top : function(){

    var speed = 750;
    $('html, body').animate( { scrollTop: 0 }, speed );

  },
  /**
   * $.scroll_top();
   */



  /**
   * $.close_modal();
   *
   * @return {void}  open / close API modal
   */
  close_modal : function(){


      if( $('#modal').css('display') == 'block' ){

          $('#modal').css('display', 'none');
          $('#modal_content').empty();

          if( typeof $.datas != 'undefined' ){

              delete $.datas.products;
          }

          // CLEAR OBJ.FILES
          if( $.obj.files.length != 0 ){

              delete $.obj.files;
              $.obj = { files : []  };
              $.index_box_img = 0;
          }
      }
      else{

					$('#modal').css('display', 'block');
      }

  },
  /**
   * $.close_modal();
   */



  /**
   * $.animateCss( element, animationName, callback );
   *
   * @param  {element} 	element       description
   * @param  {string} 	animationName animate.CSS animation name
   * @param  {callback} callback      function to call on animation end
   * @return {css}
   */
  animateCss : function(element, animationName, callback) {

    function whichTransitionEvent() {

        var el = document.createElement('fake'),
            transEndEventNames = {
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

    var transEndEventName = whichTransitionEvent();

    //'webkitAnimationEnd oanimationend msAnimationEnd animationend'
    $(element).addClass("animated "+animationName+"");
    $(element).one(transEndEventName, function(e) {


        if (typeof callback === 'function') callback();
        $(element).removeClass(""+animationName+"");

    });

 	},
  /**
   * $.animateCss( element, animationName, callback );
   */


/////////////////////////////////////////////
///////////   IMG   VIEWER   ////////////////
/////////////////////////////////////////////


  // OBJ files
  obj : { files : []  },
  // $.index_box_img
  index_box_img : 0,

  /**
   * $.img_viewer();
   *
   * img viewer ADD IMG PRODUCT - FOR RETRIVE SEE img_modifer(); next
   * init. $.obj -> obj to trait Files
   */
  img_viewer: function(){


      var fileInput = document.querySelector('#img');

      // start on change
      fileInput.addEventListener('change', function(e) {

          // stop propagation
          e.stopImmediatePropagation();

					// if no file - stop here
          if(!fileInput.files){ return false;}

          var files = fileInput.files;
          // console.log(files);

          // if multiple imgs files is an array
          var len = files.length;

          if( $.o.vue.shop == true
							|| $.o.vue.settings == true
							|| $.o.vue.web_app == true ){

							// re-init for have just one file
              $.obj.files = [];
              $.index_box_img = 0;
          }

          // in a loop
          for (var i = 0; i < len; i++) {

              // increm. here
              $.index_box_img++;

              var file = files[i];
              $.obj.files.splice($.index_box_img, 0, file);

              // create blob
              img = URL.createObjectURL(file);

              // show files
              // manage checked for first item
              var check = '';
              var checked = '';
              if( i == 0 && $.obj.files.length == 1 ){
                  check = 'check-';
                  checked = 'checked';
              }

              $.show_img_selected( $.index_box_img, img, file.name, check, checked);

              // revoke after loading
              img.onload = function() {
                URL.revokeObjectURL(img); // clear
              }

          }
          // end loop for multiples imgs


					$('#img').val('');

          // console.log($.obj);

      }, false);
      // end on change

  },
  /**
   * end $.img_viewer();
   */



  /**
   * $.show_img_selected( index_box_img , img , name_img, check , checked );
   *
   * @param  {int} index_box_img  for move
   * @param  {str} img            url OR Blob
   * @param  {str} name_img       name_img for name_img_first
   * @param  {str} check          'check' -> perfix for icon
   * @param  {str} checked        'checked' -> checked input
   * @return {html}
   */
  show_img_selected : function( index_box_img, img, name_img, check, checked ){


      var OBJ_imgs = {
        index_box_img : index_box_img,
        img : img,
        name_img : name_img,
        check : check,
        checked : checked,
        not_for_logo : true,
				tr : $.o.tr
      };

      if( $.o.vue.shop == true
					|| $.o.vue.settings == true
				 	|| $.o.vue.web_app == true ){

          // empty rows_img for get just one img for logo shop
          $('#rows_img').empty();

          OBJ_imgs.not_for_logo = false;
      }

      $('body #rows_img').mustache('images_render', OBJ_imgs);

  },
  /**
   *  $.show_img_selected( index_box_img , img , name_img, check , checked );
   */



  /**
   * $.loadXHR( url );
	 * i. is used !
   *
   * @param  {str} url   url of an image
   * @return {blob}     Blog get by  XMLHttpRequest
   */
  loadXHR : function( url ){
    return new Promise( function(resolve, reject){
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
        catch(err) {reject(err.message)}
    });

  },
  /**
   * $.loadXHR( url );
   */


/////////////////////////////////
/////   SHOW IMGS PRODUCT   /////
/////////////////////////////////


  /**
   * $.img_modifier( id );
   *
   * @param  {int} id   product id
   * @return {html}    	show all imgs of a product
   */
  img_modifier :function( id ){


      // prepa tab FILES - clear if $.obj.files
      if( $.obj.files.length != 0 ){

          delete $.obj.files;
          $.obj = { files : []  };
          $.index_box_img = 0;
      }

      // LOOP OVER PRODUCTS IMGS FOR CREATE FILE OBJECT

      $.o.one_prod.imgs.forEach(function(item, k){

          var src = 'https://'+$.o.host+'/img/Products/max-'+item.name+'';

          $.loadXHR(src).then(function(blob){

              var file = new File([blob], item.name, blob);
              // console.log(file.lastModified);

              // insert in obj files to his index
     					$.obj.files.splice( k, 0 , file );

              var checked = (k == 0) ? 'checked' : ''; // for hidden checkbox
              var check = (k == 0) ? 'check-' : ''; // for icon class

              $.index_box_img = k;

              // show files - show 'min-' => we take the 'max-' file for insert in object
              $.show_img_selected( $.index_box_img,
                '../img/Products/min-'+item.name, 'min-'+item.name, check, checked );

          });
          // END loadXHR

      });
      // END FOREACH

  },
  /**
   * $.img_modifier( id );
   */



////////////////////////////////////////////////////////
//////////////   REMOVE  PRODUCT  IMG   ////////////////
////////////////////////////////////////////////////////


  /**
   * $.remove_product_img( box_img );
   *
   * @param  {htmlElement} 	box_img  id of a box img product
   * @return {html}         Remove an img product to the list
   */
  remove_product_img : function(box_img){


      var index_box = $(box_img).index();

      // delete to obj. file
      for (var i = 0; i < $.obj.files.length; i++) {

          if( i == index_box ){

              $.obj.files.splice(i, 1);
              break;
          }

      } // end for

      // console.log($.obj);
      // delete to DOM
      $(box_img).remove();

  },
  /**
   * $.remove_product_img( box_img );
   */


////////////////////////////////////////////////////////
//////////////   MOVE  IMG   ///////////////////////////
////////////////////////////////////////////////////////


  /**
   * $.move_img( name_box, dir );
   *
   * @param  {str} name_box   name_box
   * @param  {str} dir        direction -> str 'left' OR 'right'
   * @return {html}           move an img product on the list
   */
  move_img: function( name_box, dir ){


      var box = $('#'+name_box+'');

      var index_box = $(box).index();

      // if first box and left
      if( index_box == 0 && dir == 'left' ){

          // replace at the end
          var box_removed = $(box).remove();
          var len = $('.box_img').length-1;
          $(box_removed).insertAfter( $('.box_img').eq(len) );
          var new_index = $(box_removed).index();
      }

      // if last box and right OR FIRST IMG ASKED
      else if( index_box == ($('.box_img').length-1) && dir == 'right'
      || dir == 'fav' ){

          // replace at the end
          var box_removed = $(box).remove();
          $(box_removed).insertBefore( $('.box_img').eq(0) );
          var new_index = $(box_removed).index();
      }

      // classic behaviour
      else if( dir == 'left' ){

          // replace before prev element
          var box_removed = $(box).remove();
          $(box_removed).insertBefore( $('.box_img').eq(index_box-1) );
          var new_index = $(box_removed).index();

      }
      else if( dir == 'right' ){

          // replace After next element
          var box_removed = $(box).remove();
          $(box_removed).insertAfter( $('.box_img').eq(index_box) );
          var new_index = $(box_removed).index();
      }


      // splice to his orgin Keep him in memory
      var file = $.obj.files.splice(index_box,1);

      // re-insert to new destination
      $.obj.files.splice(new_index, 0, file[0]);


      // all icons uncheck
      $('.fav_checked i.fa-check-square').addClass('fa-square')
      .removeClass('fa-check-square');

      // attr checked false for all
      $('input[name="name_img_first"]').each(function(){

          $(this).prop('checked', false);
      });

      // FOR THE FIRST ITEM -> IMG FIRST - PREZ
      // check hidden input
      $('.fav_checked').eq(0)
      .find('input[name="name_img_first"]')
      .prop('checked', true);

      // icon checked
      $('.fav_checked').eq(0).find('i.fa-square').addClass('fa-check-square')
      .removeClass('fa-square');

  },
  /**
   * $.move_img( name_box, dir );
   */



  /**
   * $.append_img_shop( img );
   *
   * @param  {image} img 	logo for shop
   * @return {html}     	select an image or a logo for the shop
   */
  append_img_shop : function(img){


      // prepa tab FILES - clear if $.obj.files
      if( $.obj.files.length != 0 ){

          delete $.obj.files;
          $.obj = { files : []  };
          $.index_box_img = 0;
      }

      var src = 'https://'+$.o.host+'/img/Logos/'+img+'';

      // load img
      $.loadXHR(src).then(function(blob){

          var file = new File([blob], img, blob);
          // insert in obj files à son index
          $.obj.files.splice( 0, 0 , file );
          // console.log( $.obj.files );
          $.index_box_img = 0;

          $.show_img_selected( $.index_box_img, src, $.obj.files[0].name, 'check-', 'checked' );

      });

  },
  /**
   * $.append_img_shop(img);
   */



});
// END EXTEND

});
// END jQuery
