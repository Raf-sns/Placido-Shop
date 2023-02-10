/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: login.js
 *
 * $.send_login();
 * $.login_fetch(event);
 * $.prevent_links(elem);
 * $.sender();
 * $.show_process(end);
 * $.show_alert( type, html, still_open );
 * $.forgot_password();
 * $(document).one('keydown') => 'Enter'
 *
 */

	const DEV_MODE = true;

// start jQuery
$(function(){



/* LOGIN BY PRESS ENTER KEY */
$(document).on('keydown', function(e){

		if( e.key == 'Enter' ){

				$.send_login();
		}
});
/* END LOGIN BY PRESS ENTER KEY */


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  // INIT. PLACIDO OBJECT
     //  //
     o : {},
    //---//


  /**
   * $.send_login();
   *
   * @return {api}  login to the back-office
   */
  send_login : function( event ){


      // show login spinner
      $('#login_spinner').css('visibility','visible');

      var el_to_prevent = '#form_login';
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var formElement = document.getElementById("form_login");
      var datas = new FormData(formElement);
      // append command
      datas.append('set', 'login');


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


          // success - LOGGED !
          if( data.success ){

							// off press enter
							$(document).off('keydown');

							$.o = data.response;

              // PASS DATAS API TO $.o OBJECT enter -> $.o <- in a console to see object
							if( DEV_MODE ){

									// SHOW OBJECT API
									console.log($.o);
							}


							// LOAD JS BACKEND BASE TEMPLATE
							$.Mustache.load('templates/new_sales.html',
								function(){

										$.Mustache.load('templates/backend_base.html',
											function(){

													// APPEND PAGE ADMIN when template is charged
													$('#center_page').empty().mustache('admin_page', $.o );

											});

								});

							// LOAD API SCIPTS
							$.getScript('JS/tools.js');

              // REMOVE LOGIN
              $('script[src="JS/login.js"]').remove();

          } // error
          else{

              // hide login spinner
              $('#login_spinner').css('visibility','hidden');

              // error
              $.show_alert('warning', data.error, false);

          }

          $.show_process('end');

      });
      // end sender

  },
  /**
   * $.send_login();
   */



	/**
	 * $.login_fetch(event);
	 *
	 * @return {type}  description
	 */
	login_fetch : function(event){


			event.preventDefault();

			var formElement = document.getElementById("form_login");

			let Datas = new FormData(formElement);

			// append command
      Datas.append('set', 'login');

			var MyInit = {
				method: 'POST',
				mode: 'cors',
				cache: 'default',
				credentials: 'include',
				body: Datas
			};

			// send request to server - Test USE FETCH API
			fetch('index.php', MyInit).then(function(response){

						// first we wait for a response from the server ...
						if(response.ok) {

							// then treat the response as json
							return response.json();
						}
						else{
							// error
							alert( $.o.tr.error_server );
						}

				}).then(function(response){

						// off press enter
						$(document).off('keydown');

						// PASS DATAS API TO $.o OBJECT enter -> $.o <- in a console to see object
						$.o = response.response;

						// SHOW OBJECT API
						console.log($.o);

						// APPEND SCRIPTS - tools is a charger
						$('body').append(
							`<script type="text/javascript" src="JS/tools.js"></script>`);

						// // add templates to Mustache
						// Templates.forEach((item, i) => {
						//
						// 		$.Mustache.load(item);
						// });
						//
						//
						// // LOAD JS BACKEND BASE TEMPLATE
						// new Promise(function(resolve, reject) {
						//
						// 	$.Mustache.load('templates/backend_base.html',
						// 	function(){
						//
						// 		resolve();
						// 	});
						// }).then(function(result) {
						//
						// 	$.Mustache.load('templates/new_sales.html',
						// 	function(){

								// APPEND PAGE ADMIN when template is charged
								$('#center_page').empty().mustache('admin_page', $.o );

						// 	});
						//
						// });


						// REMOVE LOGIN
						$('script[src="JS/login.js"]').remove();

						return;

			})
			.catch(function(error) {

					console.log(error.message);
			});
			// end send request

	},

  /**
   * $.prevent_links(elem);
   *
   * @param  {htmlElement} 	elem -> elem was clicked '#some_elem'
   * @return {void}
   */
  prevent_links : function(elem){

      $(elem).on('click', function(e){ e.preventDefault(); });
  },
  /**
   * $.prevent_links(elem);
   */



  /**
   * $.sender(); - Ajax function
   *
   * @param  {htmlElement} 	el_to_prevent html element to prevent classical behaviour
   * @param  {string} 			method        'post/get'
   * @param  {string} 			url           url to send	datas
   * @param  {object} 			datas         data to send at server
   * @param  {string} 			data_type     ex. 'json'
   * @param  {function} 		callback      excecute this on server response
   * @return {ajax}
   */
  sender : function(el_to_prevent, method, url, datas, data_type, callback ){


    if(el_to_prevent != false ){

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
        	// called when there is an error
        	console.log(data);
        }

    });
    // END AJAX

  },
  /**
   * $.sender();
   */



  /**
   *  $.show_process(end);
   *
   * @param  {type} end description
   * @return {type}     description
   */
  show_process : function(end){

      if(end == 'end'){
        $('#render_process').css({'width': '0%'});
        return;
      }
      $('#render_process')
      .animate({'width': '100%'}, 2000, function(){
        $('#render_process').css({'width': '0%'});
      });

  },
  /**
   *  $.show_process(end);
   */



  /**
   * $.show_alert( type, html, still_open );
   *
   * @param  {str} type :
   * info
   * success
   * warning
   * error
   * @param  {str} html
   * @param  {bool} still_open true/false
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

      if( still_open ){
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
   * $.forgot_password();
   *
   * @return {success/error}  send a new passord to user
   */
  forgot_password : function(){


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();
      // append command
      datas.append('set', 'forgot_password');
      datas.append('mail', $('#mail').val() );


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


          // success
          if( data.success ){

              $.show_alert('success', data.success, true);

          } // error
          else{

              // error warning
              $.show_alert('warning', data.error, true);
          }

      });
      // end sender

  },
  /**
   * $.forgot_password();
   */


});
///////////////////////    END EXTEND



});
// END JQUERY
