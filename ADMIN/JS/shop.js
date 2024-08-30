/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2021
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: shop.js
 *
 * $.check_mode_shop( event );
 * $.update_shop();
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * $.check_mode_shop( event );
   *
   * @Param {event}
   * @return {type}  description
   */
  check_mode_shop : function( event ){


      // delegate click on parent if icon is clicked
      if( $(event.target).is('i') ){

          $(event.target).parent().click();
          return;
      }

      $('label[for="mode_sale"] i, label[for="mode_catalog"] i')
      .removeClass('fa-check-square')
      .addClass('fa-square');

      var icon = $(event.target).children('i');

      $(icon).removeClass('fa-square').addClass('fa-check-square');

  },
  /**
   * $.check_mode_shop( event );
   */



  /**
   *  $.update_shop();
   *
   */
  update_shop : function(){


			// disable onclick
      $('#sub_update_shop').removeAttr('onclick').append(`<span class="lil_spinner">&nbsp;<i
			class="fa-circle-notch fa-fw fa-spin fas"></i>&nbsp;</span>`);


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';
      var form = document.getElementById('form_shop');
      var datas = new FormData(form);

      // remove img input - this is managed by $.obj.files
      datas.delete('img');

      // append token
      datas.append('token', $.o.user.token);

      // append command
      datas.append('set', 'update_shop');

			// no img
			if( $.obj.files[0].name == "undefined" ){

					$.show_alert('warning', $.o.tr.add_logo_shop_image, false);

					$('#sub_update_shop').attr('onclick', '$.update_shop();');

					// remove little spinner
					$('.lil_spinner').remove();

					return;
			}

      // IF TOO MUCH IMGs -> one img for logo
      if( $.obj.files.length > 1 ){

          $.show_alert('warning', $.o.tr.one_img_for_logo, false);

          $('#sub_update_shop').attr('onclick', '$.update_shop();');

					// remove little spinner
					$('.lil_spinner').remove();

					return;
      }

      // add files from obj. to formData
      datas.append('img[]', $.obj.files[0]);


			// sender send datas to server asynchronous and return data.obj
			$.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.show_alert('success', data.success, false);

              // - clear if $.obj.files
              if( $.obj.files.length != 0 ){

                  delete $.obj.files;
                  $.obj = { files : []  };
                  $.index_box_img = 0;
              }

              // new object shop
              $.o.shop = data.shop;

              // re-init logo et admin firstname
              $('#logo_admin').attr('src', 'https://'+$.o.host+'/img/Logos/'+$.o.shop.img+'');
              $('#admin_name').text($.o.user.fname);

              // re-open vue with fresh datas
              $.open_vue('shop', event);

           } // error
           else{

              $.show_alert('warning', data.error, false);

              // re-attr onclick to button
              $('#sub_update_shop').attr('onclick', '$.update_shop();');
              // remove little spinner
              $('.lil_spinner').remove();

           }

			});
			// end sender

  },
  /**
   *  $.update_shop();
   */


});
// end extend

});
// end jQuery
