/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: archives.js
 *
 * on new sales view :
 * $.click_to_archive_sale( sale_id );
 * $.abort_archive( sale_id );
 * $.archive_sale( sale_id );
 *
 * on archives view :
 * $.deploy_archive( sale_number );
 * $.send_mail_archives( sale_number );
 * $.show_bill( sale_number );
 * $.send_bill_at_customer( sale_id );
 *
 * $.confirm_update_bill_as_payed( sale_number, sale_id );
 * $.update_bill_as_payed( sale_number );
 *
 * $.open_modal_refound( sale_number );
 * $.calcul_refound( prod_id );
 * $.refound_sale();
 *
 * $.load_more_archives();
 *
 * $.timer_search = null;
 * $.timer_archives( what );
 * $.clean_search_archives();
 * $.search_archives( what );
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({



	/**
	 * $.click_to_archive_sale( sale_id );
   * @param  {int} sale_id
	 */
	click_to_archive_sale : function( sale_id ){

				// disable button
				$('#archive_sale-'+sale_id+'').prop( 'disabled', true );

				var html = `<p>`+$.o.tr.execute_archive_sale+` `+sale_id+`,
						<br>
						`+$.o.tr.confirm+`&nbsp;:
						</p>
            <button id="archive_this-`+sale_id+`"
            onclick="$.archive_sale(`+sale_id+`);"
            class="unbind_click btn card gree medium round left" role="button">
    				<i class="fas fa-sign-in-alt" aria-hidden="true"></i>&nbsp;
              `+$.o.tr.validate+`
            </button>

            <button onclick="$.abort_archive(`+sale_id+`);"
            class="btn card dark-gray medium round right" role="button">
    				<i class="fa-ban fas" aria-hidden="true"></i>&nbsp;
              `+$.o.tr.abort+`
            </button>
          `;

        // SHOW ALERT TO CONFIRM ARCHIVAGE
        $.show_alert('info', html, true);

	},
	/**
	 * $.click_to_archive_sale( sale_id );
	 */



	/**
	 * $.abort_archive( sale_id );
	 *
	 * @param  {int} 		sale_id
	 * @return {void}   abort archive sale
	 */
	abort_archive : function( sale_id ){

			// close modal
			$.show_alert(false);

			// enable button archive sale
			$('#archive_sale-'+sale_id+'').prop( 'disabled', false );

	},
	/**
	 * $.abort_archive( sale_id );
	 */



	/**
	 * $.archive_sale( sale_id );
	 *
	 * @param  {int} sale_id
	 * @return {type}
	 */
	archive_sale : function( sale_id ){


		// LAUNCH RENDER PROCESS
		$('#render_process').animate({width: '100%'}, 2000);

  	// disable button - show lil spinner
    $('#archive_this-'+sale_id+'').removeAttr('onclick');

		// append spinner
		$('#archive_sale-'+sale_id+'')
		.append(`&nbsp;<i class="spinner fa-fw fa-spin fas fa-circle-notch"></i>`);

		// create form data for AJAX POST
		var datas = new FormData();

		// SEND MAIL TRAITEMENT COMMAND TO CUSTOMER IF CHECKED
		if( $('#send_mail_confirm_treatment-'+sale_id+'').is(':checked') == true ){

					// add datas send mail
					datas.append('send_mail_confirm_treatment', true );
		}

		// SEND BILL BY MAIL TO CUSTOMER IF CHECKED
		if( $('#send_bill_by_mail-'+sale_id+'').is(':checked') == true ){

					// add datas send mail
					datas.append('send_bill_by_mail', true );
		}

    // IF NOTIF AS PAYED - if exist else -> false for do not enter in cash payemnt case
    if( $('#notif_as_payed-'+sale_id+'').length ){

        var payed = ( $('#notif_as_payed-'+sale_id+'').is(':checked') == true )
        ? 'set_payed' : 'not_payed';

        datas.append('payed', payed);
    }

    // append sale id
    datas.append('sale_id', sale_id);

    // append command
		datas.append('set', 'archive_sale');

    // token
    datas.append('token', $.o.user.token );

    var el_to_prevent = false;
    var method = 'POST';
    var url = 'index.php';
    var data_type = 'json';

  	// sender send datas to server asynchronous and return data.obj
    $.sender(el_to_prevent, method, url, datas, data_type, function(data){

				// STOP RENDER PROCESS
				$('#render_process').stop(true).css({'width': '0%'});

        // if product ARCHIVED ok
        if(data.success){


            // re-init archives - this is rendered by archives increment
            $.o.archives = data.archives;
						$.o.template.load_more_archives = data.template.load_more_archives;

						// re-init template - not all
            $.o.template.total_amount_shop = data.template.total_amount_shop;
            $.o.template.nb_new_sales = data.template.nb_new_sales;

						// re-init new-sale
            $.o.new_sales = data.new_sales;

            // render text success
            $.show_alert('success', data.success, false);

            // re-open vue home - this re-init datas in header tags
            $.open_vue('home', event);

        } // error
        else{

            $.show_alert('warning', data.error, false);

						// remove spinner
						$('.spinner').remove();
        }

    });
    // end sender

	},
	/**
	 * $.archive_sale( sale_id );
	 */



  /**
   * $.deploy_archive( sale_number );
   *
   * @param  {type} sale_number description
   * @return {type}             description
   */
  deploy_archive : function( sale_number ){


      // deploy details new sale
      if( $('#archive_id-'+sale_number+' .hidden_archive').css('display') == 'none' ){

          // icon anim
          $('#icon_archive-'+sale_number+' i')
          .addClass('fa-hand-point-down')
          .removeClass('fa-hand-point-right');

					// text button
          $('#text_icon_archive-'+sale_number+'')
					.text( $.o.tr.hide );

          // show
          $('#archive_id-'+sale_number+' .hidden_archive')
          .show('fast');

          // scroll to top on open new sale
          $.scroll_to_elem('#archive_id-'+sale_number+'', event);

      }
      else{

          // HIDE BLOCK NEW SALE DETAILS
          // icon anim
          $('#icon_archive-'+sale_number+' i')
          .addClass('fa-hand-point-right')
          .removeClass('fa-hand-point-down');

					// text button
          $('#text_icon_archive-'+sale_number+'')
					.text( $.o.tr.show );

          // hide
          $('#archive_id-'+sale_number+' .hidden_archive')
          .hide(300);

      }

  },
  /**
   * $.deploy_archive( sale_number );
   */



  /**
   * $.send_mail_archives( sale_number );
   *
   * @param  {int} 	sale_number  in archives
   * @return {json} succes/error send a mail to customer form achives
   */
  send_mail_archives : function( sale_number ){


      // set obj mail
      var Obj = {
        firstname: '',
        lastname: '',
        mail: '',
				// pass translation
				tr: $.o.tr
      };

			// search datas customer
      for( var i = 0; i < $.o.archives.length; i++ ){

          if($.o.archives[i].sale_number == sale_number ){

							// empty datas for obj mail
              Obj.firstname = $.o.archives[i].customer.firstname;
							Obj.lastname = $.o.archives[i].customer.lastname;
              Obj.mail = $.o.archives[i].customer.mail;
          }
      }

      // imbibe modal_content
			// i. -> see main.js/$.send_mail_to_customer( mail );
      $('#modal_content').mustache('send_mail_to_customer', Obj);

      $('#modal').show();

  },
  /**
   * $.send_mail_archives( sale_number );
   */



  /**
   * $.show_bill( sale_number );
   *
   * @param  {int}  sale_number 	archive number
   * @return {html} open a modal with bill rendered + button to print bill
   */
  show_bill : function( sale_number ){


    // make an object for template
    var BILL_vue = {};

		// found watcher for error
		var found = false;

		// get one archive
    for (var i = 0; i < $.o.archives.length; i++) {

        if( $.o.archives[i].sale_number == sale_number ){
					found = true;
          break;
        }
    }

		// archive not found
		if( found == false ){

				$.show_alert('warning', $.o.tr.error_archive_not_found, false);
				return;
		}

		// translate state : payed / not payed
    var state = ( $.o.archives[i].payed != 0 )
		? $.o.tr.bill_payed : $.o.tr.waiting_for_payment;

		// contruct a string customer for re-use same bill template as backend
		var customer =
			$.o.archives[i].customer.lastname+` `
			+$.o.archives[i].customer.firstname+`<br>`
			+$.o.archives[i].customer.address+`<br>`
			+$.o.archives[i].customer.post_code+` `
			+$.o.archives[i].customer.city+`<br>`
			+$.o.archives[i].customer.country;

    // OBJ bill vue for template - common bill.html template
    BILL_vue = {
      subject : $.o.shop.title+' - '+$.o.tr.your_bill,
      title : $.o.tr.your_bill,
      host : $.o.host,
			year : new Date().getFullYear(),
      logo : $.o.shop.img,
      shop_title : $.o.shop.title,
      bill_id : $.o.archives[i].sale_number,
      customer : customer,
      amount_text : $.o.archives[i].total_amount_sale,
      total_tax_sale : $.o.archives[i].total_tax_sale, // must be false
      date_now : $.o.archives[i].date_sale,
      products_selected : $.o.archives[i].archived_products,
      legal_addr : $.o.shop.legal_addr.replace(/(?:\r\n|\r|\n)/g, '<br>'),
      legal_mention : $.o.shop.legal_mention.replace(/(?:\r\n|\r|\n)/g, '<br>'),
      state : state,
			tr: $.o.tr,
			curr_sign: $.o.api_settings.CURRENCY_SIGN,
			print_bill: true
    };
    // end OBJ

		// add rep. to show tax render row
    BILL_vue.rep_tva = ( $.o.archives[i].total_tax_sale == false ) ? false : true;

    // if refounded - add redounded datas
    if( $.o.archives[i].refounded  == true ){

        BILL_vue.refounded = true;
        BILL_vue.refounded_amount_text = $.o.archives[i].total_refounded_amount;
        BILL_vue.refounded_date = $.o.archives[i].refounded_date;
    }

    // console.log(BILL_vue);

		// append print button
		$('#modal_content').empty()
		.append(`<button onclick="$.print('#print_bill');"
		role="button"
	  class="btn dark-gray left padding-small round small"
	  style="margin-top: 0;">
	    <i class="fas fa-print"></i>&nbsp; `+$.o.tr.print+`
	  </button>`);

		// load common template bill
		if( $.Mustache.has('bill') == false ){

				$.get('templates/bill.html', function(data){

						// add to Mustache templates
						$.Mustache.add('bill', data);

						// append bill
						$('#modal_content').mustache('bill', BILL_vue);

				},'html');
		}
		// template already loaded
		else {

				// append bill
				$('#modal_content').mustache('bill', BILL_vue);
		}

		// show bill
    $('#modal').show();

  },
  /**
   * $.show_bill( sale_number );
   */



	/**
	 * $.ask_to_send_bill( sale_id );
	 *
	 * @param  {int} 		sale_id
	 * @return {void}   ask to resend bill at customer
	 */
	ask_to_send_bill : function( sale_id ){


			// get infos customer
			for( var i = 0; i < $.o.archives.length; i++ ){
				if( $.o.archives[i].sale_id == sale_id ){
						// stop where archive was founded
						break;
				}
			}

			var html = `<p>
				`+$.o.tr.ask_to_send_bill+` `+$.o.archives[i].sale_number+`,
				<br>
				`+$.o.tr.at_customer+` `+$.o.archives[i].customer.lastname+`
				`+$.o.archives[i].customer.firstname+`
				<br>
				`+$.o.tr.confirm+`&nbsp;:
				</p>

				<button onclick="$.send_bill_at_customer(`+sale_id+`);"
				class="unbind_click btn card gree medium round left" role="button">
				<i class="fas fa-sign-in-alt" aria-hidden="true"></i>&nbsp;
					`+$.o.tr.validate+`
				</button>

				<button onclick="$.show_alert(false);"
				class="btn card dark-gray medium round right" role="button">
				<i class="fa-ban fas" aria-hidden="true"></i>&nbsp;
					`+$.o.tr.abort+`
				</button>
			`;

			// SHOW ALERT TO CONFIRM ARCHIVAGE
			$.show_alert('info', html, true);


	},
  /**
	 * $.ask_to_send_bill( sale_id, sale_number, lastname, firstname );
   */



	/**
	 * $.send_bill_at_customer( sale_id );
	 *
	 * @param  {int} 		sale_id
	 * @return {json}   resend bill at customer
	 */
	send_bill_at_customer : function( sale_id ){



			// close modal
			$.show_alert(false);

			// show spinner
			$('#send_bill_at_customer	i.fa-spin').show();


			var Obj = {
				set : 'send_bill_at_customer',
				token : $.o.user.token,
				sale_id : sale_id
			};

			$.post('index.php', Obj, function(data){

					if( data.success ){

							$.show_alert('success', data.success, false);
					}
					// end success

					// error
					if( data.error ){

							$.show_alert('warning', data.error, false);
					}

					// hide spinner
					$('#send_bill_at_customer	i.fa-spin').hide();

			}, 'json');
			// end $.post

	},
	/**
	 * $.send_bill_at_customer( sale_id );
	 */



	/**
	 * $.confirm_update_bill_as_payed( sale_number, sale_id );
	 *
	 * @param  {int} 		sale_id
	 * @return {void}
	 */
	confirm_update_bill_as_payed : function( sale_number, sale_id ){


		var html = `<p>
			`+$.o.tr.modify_state_bill+` `+sale_number+`,
			<br/>
			`+$.o.tr.confirm+`&nbsp;:
			</p>

			<button id="bill_as_payed_btn"
			onclick="$.update_bill_as_payed(`+sale_id+`);"
			class="unbind_click btn card gree medium round left" role="button">
			<i class="fas fa-sign-in-alt" aria-hidden="true"></i>&nbsp;
				`+$.o.tr.validate+`
			</button>

			<button onclick="$.show_alert(false);"
			class="btn card dark-gray medium round right" role="button">
			<i class="fa-ban fas" aria-hidden="true"></i>&nbsp;
				`+$.o.tr.abort+`
			</button>
		`;

		// SHOW ALERT TO CONFIRM ARCHIVAGE
		$.show_alert('info', html, true);

	},
  /**
   * $.confirm_update_bill_as_payed( sale_number, sale_id );
   */



	/**
	 * $.update_bill_as_payed( sale_id );
	 *
	 * @param  {int} 		sale_id
	 * @return {json}   archive with statut updated to 'payed'
	 */
	update_bill_as_payed : function( sale_id ){

		// disable click
		$('#bill_as_payed_btn').removeAttr('onclick');

		// show spinner
		$('#update_bill_as_payed i.fa-spin').show();

		// make an object to send
		var Obj = {
			set: 'update_bill_as_payed',
			token : $.o.user.token,
			sale_id: sale_id
		}

		$.post('index.php', Obj, function(data){


				// success
				if( data.success ){

						// update archive in api object
						var sale_number = data.one_archive.sale_number;

						// loop for update obj archives
						for (var i = 0; i < $.o.archives.length; i++) {

								// find archive to re-init.
								if( $.o.archives[i].sale_number == sale_number ){

										// renew archive refounded
										$.o.archives[i] = data.one_archive;

										break; // break here
								}
						}
						// end loop

						$.show_alert('success', data.success, false);

						// RE-OPEN ARCHIVES
						$.open_vue('archives', event);

						// re-deploy refounded archive for see result
						$.deploy_archive(sale_number);
				}

				// error
				if( data.error ){

						// enable click
						$('#bill_as_payed_btn')
						.attr('onclick', '$.update_bill_as_payed('+sale_id+');');

						// hide spinner
						$('#update_bill_as_payed i.fa-spin').hide();

						$.show_alert('warning', data.error, false);
				}

			},'json');
			// end $.post

	},
	/**
	 * $.update_bill_as_payed( sale_id );
	 */



  /**
   * $.open_modal_refound( sale_number );
   *
   * @param  {type} sale_number description
   * @return {type}             description
   */
  open_modal_refound : function( sale_number ){


      // search good archived sale
      for (var i = 0; i < $.o.archives.length; i++) {

        if( $.o.archives[i].sale_number == sale_number ){

          // BREAK REFERENCE TO OBJECT
          $.o.refound_sale = JSON.parse(JSON.stringify($.o.archives[i]));
          // break here - keep index
          break;
        }
      }
      // END FOR

      // TEMPLATE REFOUND FORM
      $('#modal_content').empty()
      .mustache('refound_form', $.o);

      // show modal adjust distance to scrollTop
      $('#modal').show();

  },
  /**
   * $.open_modal_refound( sale_number );
   */



  /**
   * $.calcul_refound( id );
   *
   * @return {type}
   */
  calcul_refound : function( id ){


      // if not a number or '.'/',' is enter
      var regex = /[0-9]|.|,/g; // only number or separator
      var regex_2 = /^[+-]/g; // a sign is not autorized !!

      if( !$('#calcul_refound-'+id+'').val().match(regex)
          || $('#calcul_refound-'+id+'').val().match(regex_2)    ){

          $('#calcul_refound-'+id+'').val('').empty();

          return;
      }

      var val = $('#calcul_refound-'+id+'').val().trim();
      // parse in int. / in cent
      value_refound = ( val == '' ) ? 0 : Math.round( val*100 );

      var refounded = ( value_refound == 0 ) ? false : true;
      var total_amount_sale = 0;
      var total_tax_sale = 0;

      // loop all products - find good row
      var len = $.o.refound_sale.archived_products.length;

      for (var i = 0; i < len; i++) {

        var item = $.o.refound_sale.archived_products[i];

        // not item refounded
        if( item.id != id ){

            total_amount_sale += Number( (item.PU_TT_text)*100 ); // cent
            total_tax_sale += item.total_tax_int; // cent
        }
        // not item refounded

        // if item refounded
        if( item.id == id ){

          // refound in price less tax
          var amout_item = Number( item.price - value_refound); // cent

          // not under 0 !
          amout_item = ( amout_item < 0 ) ? 0 : amout_item;

          // calcul price refounded less tax row by quant
          var price_tt = amout_item * item.quant; // cent

          // add tax if tax exist
          if( item.tax != false ){

            // calcul tx tax in float -> get 0.055 in float
            var tx_tax = parseFloat( Number( item.tax/100 ).toFixed(4) );

            // add tax to price TT
            price_tt += parseFloat( Number( price_tt * tx_tax ).toFixed(4) ); // cent

            // calcul new total tax item
            $.o.refound_sale.archived_products[i].total_tax_int =
            Math.round( ( (price_tt) / (1+tx_tax) ) * tx_tax ); // in cent

            // total_tax_row // str.
            $.o.refound_sale.archived_products[i].total_tax_row =
            Number($.o.refound_sale.archived_products[i].total_tax_int/100).toFixed(2); // NOT in cent

            // add tax total amount row to total_tax_sale
            total_tax_sale += $.o.refound_sale.archived_products[i].total_tax_int; // cent

          }
          // end  add tax if tax exist

          $.o.refound_sale.archived_products[i].PU_TT_text =
          Number(price_tt/100).toFixed(2); // NOT in cent

          // add total amount sale
          total_amount_sale += price_tt; // cent

          // value refounded
          $.o.refound_sale.archived_products[i].refounded_amount = value_refound; // cent
          $.o.refound_sale.archived_products[i].refounded_amount_txt =
          Number(value_refound/100).toFixed(2); // NOT in cent

          // pass refounded item to true / false
          $.o.refound_sale.archived_products[i].refounded = refounded;

        }
        // end if item

      }
      // end for

      // put in str glob values
      $.o.refound_sale.refounded = refounded;

      $.o.refound_sale.total_amount_sale =
        Number(total_amount_sale/100).toFixed(2); // NOT in cent

      // total tax sale float or false
      $.o.refound_sale.total_tax_sale =
      ( total_tax_sale == 0 ) ? false
      : Number( total_tax_sale/100 ).toFixed(2); // NOT in cent

      // total refounded
      $.o.refound_sale.refounded_amount_text = Number(value_refound/100).toFixed(2); // NOT in cent


      // renew templ. show refounded sale
      $('#modal_content').empty()
      .mustache('refound_form', $.o);

  },
  /**
   * $.calcul_refound( id );
   */



  /**
   * $.refound_row( id, sale_id );
   *
   * @return {obj}  sale refounded
   */
  refound_row : function( id, sale_id ){

			// disable button
      $('#refound_btn-'+id+'').removeAttr('onclick');

      // create form data for AJAX POST
      var datas = new FormData();

      // append command
      datas.append('set', 'refound_sale');

      // token
      datas.append('token', $.o.user.token );

      // append refounded amount
      datas.append('refounded_amount', $('#refound_row-'+id+'').val() );

      // append sale id
      datas.append('sale_id', sale_id );

      // sold product id row
      datas.append('sold_product_id', id );

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

           // if product ARCHIVED ok
           if(data.success){

                // console.log(data); //  one_archive -> array [0]
                // get sale number
                var sale_number = data.one_archive.sale_number;

                // loop for update obj archives
                for (var i = 0; i < $.o.archives.length; i++) {

                    // find archive to re-init.
                    if( $.o.archives[i].sale_number == sale_number ){

                        // renew archive refounded
                        $.o.archives[i] = data.one_archive;

                        break; // break here
                    }
                }
                // end loop

                // close modal refound
                $.close_modal();

                // success
                $.show_alert('success', data.success, false);

                // RE-OPEN ARCHIVES
                $.open_vue('archives', event);

                // re-deploy refounded archive for see result
                $.deploy_archive(sale_number);


           } // error
           else{

                $.show_alert('warning', data.error, false);

            }

            // re-att onclick on button
            $('#refound_btn-'+id+'').attr('onclick', '$.refound_row( '+id+', '+sale_id+' );');


      });
      // end sender

  },
  /**
   * $.refound_sale();
   */



	/**
	 * $.load_more_archives();
	 *
	 * @return {json}   get previous archives by interval
	 */
	load_more_archives : function(){

		// determine index min / max to load
		var min = $.o.archives.length;
		var max = $.o.template.archives_incr;

		// show spinner
		$('#loader_archives	i.fa-spin').show();

		var Obj = {
			set : 'load_more_archives',
			token : $.o.user.token,
			min : min,
			max : max
		};

		$.post('index.php', Obj, function(data){

				if( data.success ){

						// concat new array if archives
						$.o.archives = $.o.archives.concat(data.archives);

						// update more archives
						$.o.template.load_more_archives = data.load_more_archives

						// append more archives in view - nice for UI experience
						data.archives.forEach((item, i) => {

								item.tr = $.o.tr; // pass translation to item

								// construct html to render
								var render =
								$.Mustache.render('partial_archives', item);

								// append prev. archive
								$('#archives_container').append( render );
						});

						// if no more archives -> remove loader button
						if( $.o.template.load_more_archives == false ){

								$('#more_archives').remove();
						}

				}
				// end success

				// error
				if( data.error ){

						$.show_alert('warning', data.error, false);
				}

				// hide spinner
				$('#loader_archives	i.fa-spin').hide();

		}, 'json');
		// end $.post

	},
	/**
	 * $.load_more_archives();
	 */



	/**
	 * $.timer_archives( what );
	 *
	 * @param  {type} what 'date' / 'number' / 'customer'
	 * @return {type}      add a timer for not to send too much requests to server
	 */
	timer_search : null,
	timer_archives : function( what ){


			if( $.timer_search ){

					clearTimeout( $.timer_search );
			}

			$.timer_search = setTimeout( function(){

					$.timer_search = null;

					$.search_archives( what );

			}, 800 );


	},
	/**
	 * $.timer_archives( what );
	 */



	/**
	 * $.clean_search_archives();
	 *
	 * @return {type}  description
	 */
	clean_search_archives : function(){


			// end timer search
			if( $.timer_search ){

				clearTimeout( $.timer_search );
				$.timer_search = null;
			}

			// reset all values
			$('#search_archives_by_date').val('');
			$('#search_archives_by_number').val('');
			$('#search_archives_by_customer').val('');

			// empty object
			if( typeof $.o.search_archives != 'undefined' ){

					$.o.search_archives.length = 0;
			}

			// append datas to archives_container
			$('#archives_container').mustache('partial_archives_container', $.o);

			// show more_archives
			$('#more_archives').removeClass('hide');

			// hide + empty  search container
			$('#archives_search_container').addClass('hide').empty();

	},
	/**
	 * $.clean_search_archives();
	 */



	/**
	 * $.search_archives( what );
	 *
	 * @param  {type} what 'date' / 'number' / 'customer'
	 * @return {type}      return a reseach in archives
	 */
	search_archives : function( what ){


			// get value
			var val = $('#search_archives_by_'+what+'').val();

			// length == 0 -> get default view
			if( val.length == 0 ){

					// clean search archives
					$.clean_search_archives();

					// stop here
					return;
			}


			// show search container - this show a spinner
			$('#archives_search_container')
			.empty()
			.html(`<p><i class="fa-circle-notch fa-spin fas fa-fw"></i>&nbsp; `+$.o.tr.loading_datas+`</p>`)
			.addClass('show');

			// empty default container  ! -> IDs not duplicated
			$('#archives_container').empty();

			// hide more_archives
			$('#more_archives')
			.addClass('hide').removeClass('show');


			// get datas from server
			var Obj = {
				set : 'search_archives',
				token : $.o.user.token,
				what : what,
				value : val
			};

			$.post('index.php', Obj, function(data){

					if( data.success ){


							// add new datas to search archives
							$.o.search_archives = data.search_archives;

							var str_info = `<p class="large">
							<i class="fa-info-circle fas fa-fw"></i>&nbsp;
								`+$.o.tr.your_search+`<br>`+data.search_value;

							// if no more archives -> render message
							if( $.o.search_archives.length == 0 ){


									$('#archives_search_container')
									.empty()
									.html(str_info+`&nbsp;:&nbsp;&nbsp;`+$.o.tr.search_not_found+`</p>`);

									// stop here
									return;
							}


							// append partial search archives with results
							$('#archives_search_container')
							.empty()
							.mustache('partial_search', $.o)
							.prepend(str_info+`&nbsp;:&nbsp;&nbsp;
								`+$.o.search_archives.length+` `+$.o.tr.nb_archives_found+`</p>`);


					}
					// end success

					// error
					if( data.error ){

							$.show_alert('warning', data.error, true);

							// clean search archives
							$.clean_search_archives();
					}
					// end error

			}, 'json');
			// end $.post


	},
	/**
	 * $.search_archives( what );
	 */




});
// END EXTEND


});
// END jQuery
