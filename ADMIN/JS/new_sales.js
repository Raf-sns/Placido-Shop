/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2021-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: new_sales.js
 *
 * $.deploy_new_sale( sale_id );
 * $.ask_to_suppr_sale( sale_id );
 * $.suppr_sale( sale_id );
 * $.reminder_img_prod( event, name );
 * $.check_this( item,  sale_id );
 * $.print( element );
 * $.get_new_sales();
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * $.deploy_new_sale( sale_id );
   *
   */
  deploy_new_sale : function( sale_id ){

    // deploy details new sale
    if( $('#new_sale_id-'+sale_id+' .sale_item').css('display') == 'none' ){

        // icon anim
        $('#deploy_new_sale-'+sale_id+'')
        .addClass('fa-hand-point-down')
        .removeClass('fa-hand-point-right');

        // show img product dynamically
        var true_src;

        // loop all imgs products - lazy load
        $('#new_sale_id-'+sale_id+' img').each(function(){

            if( $(this).hasClass('img_charged') == false ){

                true_src = $(this).data('src');

                // pass src to data src
                // // add class for not reload images
                $(this).attr('src', true_src )
                .removeAttr('data-src')
                .addClass('img_charged');
            }

        });

        // show
        $('#new_sale_id-'+sale_id+' .sale_item')
        .show('fast');

        // scroll to top on open new sale
        $.scroll_to_elem('#new_sale_id-'+sale_id+'', event);

    }
    else{

        // HIDE BLOCK NEW SALE DETAILS
        // icon anim
        $('#deploy_new_sale-'+sale_id+'')
        .addClass('fa-hand-point-right')
        .removeClass('fa-hand-point-down');

        // hide
        $('#new_sale_id-'+sale_id+' .sale_item')
        .hide(300);

    }

  },
  /**
   * $.deploy_new_sale( sale_id );
   */



  /**
   * $.ask_to_suppr_sale( sale_id );
   *
   * @param  {int}      sale_id
   * @return {html}     ask to confirm suppr. new sale
   */
  ask_to_suppr_sale : function( sale_id ){


      var html =
			`<p>`+$.o.tr.confirm_suppr_new_sale+`&nbsp;`+sale_id+`</p>
      <button class="btn deep-orange card round left"
      onclick="$.suppr_sale(`+sale_id+`);" role="button">
      <i class="fa-sign-in-alt fas"></i>&nbsp; `+$.o.tr['suppr']+`</button>
      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr['abort']+`</button>`;

      $.show_alert('info', html, true);

  },
  /**
   * $.ask_to_suppr_sale( sale_id );
   */



  /**
   * $.suppr_sale( sale_id );
   *
   * @param  {type} sale_id description
   * @return {type}         description
   */
  suppr_sale : function( sale_id ){


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();

      // token
      datas.append('token', $.o.user.token );

      // sale id
      datas.append('sale_id', sale_id );

      // append command
      datas.append('set', 'suppr_sale');

			// LAUNCH RENDER PROCESS
			$('#render_process').animate({width: '100%'}, 2000);


      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

					// STOP RENDER PROCESS
					$('#render_process').stop(true).css({'width': '0%'});

          // success
          if( data.success ){

							// translate success server return true or error string
              $.show_alert('success', $.o.tr.success_delete_new_sale, false);

              // re-init. new sales array[]
              $.o.new_sales = data.new_sales;

							// nb new sales
							$.o.template.nb_new_sales = data.nb_new_sales;

							// all products for have fresh quantities
							$.o.products = data.products;

							// total amount shop for render it in view
              $.o.template.total_amount_shop = data.total_amount_shop


              // re-affect new amount - in vue
              $('#total_amount').text($.o.template.total_amount_shop);

              // re-adjust html count_new_sales
              $('#count_new_sales').text($.o.template.nb_new_sales);


              // remove sale from the DOM - with animation
              $('#new_sale_id-'+sale_id+'').addClass('slideOutRight');

              $('#new_sale_id-'+sale_id+'').one('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd',
              function(){
                  $('#new_sale_id-'+sale_id+'').remove();
              });

							// re open view if new sale == 0 -> this unbug message empty sale
							// for templating
							if( $.o.template.nb_new_sales == 0 ){

									$.open_vue('home', event);
							}


          } // ERROR
          else{

              $.show_alert('warning', data.error, false);
          }


      });
      // end sender

  },
  /**
   * $.suppr_sale( sale_id );
   */



	/**
	 * $.reminder_img_prod( name );
	 *
	 * @param  {str} name  name of img to show
	 * @return {modal} modal with img prez of a product
	 */
	reminder_img_prod : function( name ){

      // paste img to modal
      $('#modal_content').empty()
			.append( `<div class="dark-gray padding-large round">
			<img id="reminder_img"
			src="../img/Products/max-`+name+`"
			class="round-large"/>
			</div>` );

			// adjust css for modal
			$('#reminder_img')
			.css({
							'width': '95%',
              'margin': '5% auto',
              'display': 'block'
			});

      // show modal
      $('#modal').show();

	},
	/**
	 * $.reminder_img_prod( event, name );
	 */



  /**
   * $.check_this( item,  sale_id );
   * @param  {str} element
   * @param  {int} sale_id
   */
  check_this : function( item, sale_id ){

      if( $('#'+item+'-'+sale_id+'').is(':checked') ){

          $('label[for="'+item+'-'+sale_id+'"]')
          .children("i:first")
          .addClass('fa-square')
          .removeClass('fa-check-square');

      }
      else{

        $('label[for="'+item+'-'+sale_id+'"]')
        .children("i:first")
        .addClass('fa-check-square')
        .removeClass('fa-square');
      }

  },
  /**
   * $.check_this( item,  sale_id );
   */



  /**
   * $.print( element );
   *
   * @param  {string} element '#something' / '.something' / ...
   * @return {printable}  prepare an element to be printed
   */
  print: function( element ){


    // first hide body
    $('body').hide();

    // div to print
    var render = $(element).html();

    // append new element to html - it become the only visible element
    $('html').append(`<div id="printable">`+render+`</div>`);

    // adjust line height
    $('#printable').css({'line-height':'26px'});

    // REMOVE IMG LOGO if have
    $('#printable .no_img_for_print').remove();


    // make a time out
    var timeoutID = setTimeout(function() {

        // print
        window.print();

        $('#printable').remove(); // remove new html element to print
        $('body').show(); // show body
        window.clearTimeout(timeoutID);

    }, 50);

  },
  /**
   * $.print( element );
   */



  /**
   * $.get_new_sales();
   *
   * @return {type}  description
   */
  get_new_sales : function(){

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();
      // append command
      datas.append('set', 'get_new_sales');
      // token
      datas.append('token', $.o.user.token );

			// animate process render bar
      $('#render_process').animate({width: '100%'}, 1000);

      // animate spinner
      $('.spinner_new_sales').addClass('fa-spin');

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){


          // success
          if( data.success ){


							// hide render process
              $('#render_process').stop(true).css({'width': '0%'});

							// re-init. new sales array[]
              $.o.new_sales = data.new_sales;

							// nb new sales
							$.o.template.nb_new_sales = data.nb_new_sales;

							// all products for have fresh quantities
							$.o.products = data.products;

							// total amount shop for render it in view
              $.o.template.total_amount_shop = data.total_amount_shop


              // re-affect new amount - in vue
              $('#total_amount').text($.o.template.total_amount_shop);

              // re-adjust html count_new_sales
              $('#count_new_sales').text($.o.template.nb_new_sales);

							// re-open home -> new sales view for udpate
              $.open_vue('home', event);

          }
          else{

							// error
              $.show_alert('error', data.error, false );
          }

          // hide spinner
          $('.spinner_new_sales').removeClass('fa-spin');

      });
      // end sender


  },
  /**
   * $.get_new_sales();
   */





});
// end EXTEND

});
// end jQuery
