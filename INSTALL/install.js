/**
 * PLACIDO-SHOP FRAMEWORK - INSTALL
 * Copyright © Raphaël Castello, 2020-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	install.js
 *
 * $.test_database();
 * $.set_install();
 * $.allow_search_engines();
 * $.showImage( src, container );
 * $.choose_img( for_input );
 * $.render_range( elem );
 * $.prevent_links( elem );
 * $.sender( el_to_prevent, method, url, datas, data_type, callback );
 * $.show_alert( type, html, still_open );
 *
 * $('input').on('focus', function(){ ... });
 * $(window).scroll( ... );
 * $('#to_top').click( ... );
 *
 */


// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * $.test_database();
   * @return success/error
   */
  test_database: function(){

			// DISABLE ONCLICK
			$('#install_db').removeAttr('onclick');

			// LOADER install_db_loader
      $('#install_db_loader').show();

      var el_to_prevent = '#install';
      var method = 'POST';
      var url = '/INSTALL/';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();

      // append command
      datas.append('set', 'test_database');
			datas.append('data_base_host', $('#data_base_host').val() );
      datas.append('data_base_name', $('#data_base_name').val() );
      datas.append('data_base_user', $('#data_base_user').val() );
      datas.append('data_base_passw', $('#data_base_passw').val() );

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


					$('#install_db_loader').hide();

					// enable ONCLICK
					$('#install_db').attr('onclick', '$.test_database();');

          // success registration DATABASE
          if( data.success ){

              $('.check_db').removeClass('fa-times text-orange')
              .addClass('fa-check text-green');

              $('#db_ok').css({'display':'block'});

          }

					// error
          if( data.error ){

							$.show_alert( 'warning', data.error, true );

              $('#db_ok').css({'display':'none'});

							if( data.el ){

									$('html, body')
									.animate({
										scrollTop: $('label[for="'+data.el+'"]').offset().top }, 600 );

									$('input[name="'+data.el+'"]')
									.addClass('input_error');
          		}

          }
          // end error



      });
      // end sender

  },
  /**
   * END $.test_database();
   */


	// auto-complete fields with placeholders - for tests
	// $('input, textarea').each(function(){
	// 	$(this).val( $(this).attr('placeholder') );
	// });

  /**
   * $.set_install();
   *
   * @return {json}  Set the installation
   */
  set_install: function(){

      // remove onclick on install button
			$('#install_app').removeAttr('onclick');

      // LOADER INSTALL
      $('#install_loader').show();

      var el_to_prevent = '#install_app';
      var method = 'POST';
      var url = '/INSTALL/';
      var data_type = 'json';

      // create form data for AJAX POST
      var form = document.getElementById('install_form');
      var datas = new FormData(form);

			datas.append('set', 'install_app');


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


          // success
          if( data.success ){

              // hide button loader
    					$('#install_loader').hide();

							// countdown to redirection
							var message = data.success;

							var timer_install = 5;

							$.show_alert( 'success',
							message+`<br><span class="xxlarge">`+timer_install+`</span>`, true );

							// countdown to redirection
							var myInterval = window.setInterval(function(){

								$('.toast-message')
								.html(message+`<br><span class="xxlarge">`+timer_install+`</span>` );

								timer_install--;

								if( timer_install < 0 ){
									window.clearInterval(myInterval);
								}

							}, 1000);
							// end countdown to redirection


							// REDIRECTION TO ADMIN LOGIN PAGE
							var ti = window.setTimeout( function(){

									window.location.href = 'https://'+window.location.hostname+'/'+data.admin_folder;
									window.clearTimeout(ti);

							}, 5000 );

          }
          // end success

          // error
          if( data.error ){

              // hide button loader
    					$('#install_loader').hide();

              // re-attr onclick install()
              $('#install_app').attr('onclick', '$.set_install();');

							$.show_alert( 'warning', data.error, true );

							if( data.el ){

								$('html, body')
								.animate({scrollTop:
									$('label[for="'+data.el+'"]').offset().top}, 600,
									function(){

											$('input[name="'+data.el+'"], select[name="'+data.el+'"], textarea[name="'+data.el+'"]')
											.addClass('input_error');
									});
							}
					}
          // end error

      });
      // end sender

  },
  /**
   * $.set_install();
   */



  /**
   * $.allow_search_engines();
   *
   * @param  {event} e
   * @return {void}  set value of input[name="ALLOW_SEARCH_ENGINES"]
   */
  allow_search_engines : function(e){

      e.stopImmediatePropagation();

      // check this
      if( $('input[name="ALLOW_SEARCH_ENGINES"]').val() == '' ){

          // allow
          $('input[name="ALLOW_SEARCH_ENGINES"]').val('allow');
          return;
      }
      else{

          // un-check this - disallow
          $('input[name="ALLOW_SEARCH_ENGINES"]').val('');
          return;
      }

  },
  /**
   * $.allow_search_engines();
   */


	/**
	 * $.showImage( src, container );
	 *
	 * @param  {string} 	src       scr of image
	 * @param  {string} 	container container where append image
	 * @return {html}     show image selected
	 */
	showImage : function( src, container ) {

			if( src.files[0] === undefined ){

					$(container).empty();
					$(src).val('');
					return;
			}

			var fr = new FileReader();

			fr.onload = function(){
					var img = document.createElement('img');
					img.src = fr.result;
					img.style.width = '250px';
					$(container).empty().append(img);
	    }

			fr.readAsDataURL( src.files[0] );

	},
	/**
	 * $.showImage( src, container );
	 */



	/**
	 * $.choose_img( for_input );
	 *
	 * @param  {type} for_input description
	 * @return {type}           description
	 */
	choose_img : function( for_input ){

			$(for_input).click();

			$(for_input).on('change', function(){

					$.showImage( $(for_input)[0] , for_input+'_img_viewer');
			});

	},
	/**
	 * $.choose_img( for_input );
	 */



  /**
   * $.render_range( elem );
   *
   * @param  {type} elem element
   * @return {type}      description
   */
  render_range : function( elem ){

      $('#range_img_sn')
			.html( $('input[name="'+elem+'"]').val()+'&nbsp;px' );
  },
  /**
   * $.render_range( elem );
   */



  /**
   * $.prevent_links( elem );
   *
   * @param  {type} elem element was clicked
   * @return {type}      description
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
   * @param  {type} el_to_prevent element to prevent OR false
   * @param  {type} method        method for send 'POST' or 'GET'
   * @param  {type} url           url to send
   * @param  {type} datas         obj of datas sended
   * @param  {type} data_type     'json' or 'html' ...
   * @param  {type} callback      function
   * @return {json}               send a request to server and recive json response
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
          success: function(data) {

              if (typeof callback === 'function'){ callback(data); }
              // return $.response = {data};
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

});
// END EXTEND



	// hide error database on focus in input
	$('input').on('focus', function(){

			$.show_alert( false );
	    $('.input_error').removeClass(('input_error'));
	});


  // ON SCROLL

  // TO TOP BTN - loader imgs
  $(window).scroll(function() {

	    // TO TOP
	    if( $(this).scrollTop() > 50 ){

					$('#to_top').fadeIn();
	    }
			else{
	      	$('#to_top').fadeOut();
			}

  });
  // END WINDOW.SCROLL()



  // click btn TOP
  $('#to_top').click(function(e){

      e.stopImmediatePropagation();

      $('html,body').animate({ scrollTop: 0 }, 300);
  });
  // END TO TOP


});
// END jQuery
