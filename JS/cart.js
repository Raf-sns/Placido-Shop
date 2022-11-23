/**
 * PlACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	cart.js
 *
 * $.add_to_cart( id , buy_now );
 * $.suppr_cart_item( id );
 * $.render_dynamic_prices();
 * $.quant_manager( id, op );
 * $.Intl_currency( price );
 * $.calcul_total_cart();
 * $.open_payment_form();
 * $.set_countries();
 * $.get_storage_cart_datas();
 * $.check_cart_in_memory();
 * $.toggle_address_sup();
 * $.check_this( for_label );
 * $.valid_form_payment();
 * $.ask_to_pay();
 * $.clear_error_card();
 * $.submit_card( event );
 * $.go_direct_sale();
 * $.get_sale( sale_id, hash_customer );
 * $.get_sale_user();
 * $.destroy_card_payment();
 *
 */

$(function(){


$.extend({


  /**
   * $.add_to_cart( id , buy_now );
   *
   * @param  {int} id       product id
   * @param {str}  buy_now  'buy_now' || null for direct buy a product
   * @return {void}         add a product in cart
   */
  add_to_cart : function( id , buy_now ){

      // check if already in cart
      var check_dblons;

      // CHECK DOUBLONS
      if( $.o.cart.items.length > 0 ){

          // loop over procucts already in cart
          $.o.cart.items.forEach(function(item, k){

              // if item is find
              if( item.id == id ){

                  // checked
                  check_dblons = true;
              }
          });
      }
      // END CHECK DOUBLONS

      // finally return if already in cart
      if( check_dblons == true ){

          // - alert for doublons
          var dblon_cart_mess = $.o.tr.already_in_cart;

          $.show_alert('info', dblon_cart_mess, false);

          // new version -> open direct if doublon $.open_payment_form();
          $.open_payment_form();
          return;
      }

      // Make an object with datas needed to trait in cart
      var Cart_item = {
        id: 0,
        title: '',
        img_prez: '',
        price: 0,
        price_text: '',
        quant_aviable: 0,
        quant_wanted: 1,
        tax: 0
      };

      // loop over products
      for (var i = 0; i < $.o.products.length; i++) {

          // if item was found
          if( $.o.products[i].id == id ){

              // ADD TO CART
              var Clone_obj = JSON.parse(JSON.stringify($.o.products[i]));

              Cart_item = {

                id: Clone_obj.id,
                title: Clone_obj.title,
                img_prez: Clone_obj.img_prez,
                price: Clone_obj.price,
                price_text: Clone_obj.price_text,
                quant_aviable: Clone_obj.quant,
                quant_wanted: 1,
                tax: 0
              };

              // add tax if
              if( Clone_obj.tax != 0 ){

                  Cart_item.tax = Clone_obj.tax;
                  Cart_item.tax_text = Clone_obj.tax_text;
                  Cart_item.tax_value = Clone_obj.tax_value;
              }

              // push on items
              $.o.cart.items.push( Cart_item );

							// add to stats
							$.record_stats_from_cart( Clone_obj.id, 'add' );

							// stop here
							break;
          }

      }
      // END loop over products


      // increm cart nb_articles
      $.o.cart.nb_articles++;

      // calcul totals
      $.calcul_total_cart();

      // console.log( $.o.cart );

      // Save data to sessionStorage
      sessionStorage.setItem('cart', JSON.stringify($.o.cart));

      // if direct buy -> direct open payment form
      if( buy_now == 'buy_now' ){

          $.open_payment_form();
      }
      else{
          // show alert and aniamate button cart
          // show alert success
          $.show_alert('success', $.o.tr.added_to_cart , false);
      }


      // animate + pop up success the cart block
      $.animateCss('#cart_block', 'tada', null);
      // end animate + pop-up success

  },
  /**
   * $.add_to_cart( id , buy_now );
   */



  /**
   * $.suppr_cart_item( id );
   *
   * @param  {int} id   id prod to suppr in cart
   * @return {html}     suppr a product in cart
   */
  suppr_cart_item: function( id ){


      // decrem. cart nb_articles
      $.o.cart.nb_articles--;

      $.o.cart.nb_articles = ( $.o.cart.nb_articles < 0 ) ? 0 : $.o.cart.nb_articles;

      // CART GESTION - loop over cart to supress item in CART
      for (var i = 0; i < $.o.cart.items.length; i++) {

          // find the item to supress in CART
          if( $.o.cart.items[i].id == id ){

              // supress item to Obj. CART
              $.o.cart.items.splice(i, 1);

							// remove to stats
							$.record_stats_from_cart( id, 'remove' );

              break;
          }
      }
      // end loop to suppr


      // adjust nb articles in cart button
      // $('#nb_articles').html( $.o.cart.nb_articles+` `+$.o.tr.articles_button );

      // remove article form view
      $('#purchase_id-'+id+'').remove();

      // calcul total cart
      $.calcul_total_cart();

      // Save data in sessionStorage - SAME IF CAR ITEMS IS EMPTY ! (for view)
      sessionStorage.setItem('cart', JSON.stringify($.o.cart));

      // if have articles -> renew all prices
      if( $.o.cart.nb_articles > 0 ){

	        // render dynamic prices
	        $.render_dynamic_prices();
      }

      // if after delete article -> cart is empty - manage view
      if( $.o.cart.nb_articles == 0 ){

          // delete session storage
          sessionStorage.removeItem('cart');

          // immediatly show empty cart
          $('#center_page').empty().mustache( 'payment_form', $.o );

          // scroll to top
          $.scroll_top();

					// push home by default
					history.pushState({page : 'home'}, '','');

      }


  },
  /**
   * $.suppr_cart_item( id );
   */



  /**
   * $.render_dynamic_prices();
   *
   * @return {html}  render cart prices directly in their html tags
   */
  render_dynamic_prices : function(){


    // loop over all item form cart for renew prices
    $.o.cart.items.forEach((item, i) => {

        // render quantity
        $('#render_quant_'+item.id+'').text( item.quant_wanted );

        // render sub taxt if exist
        if( item.tax != 0 ){

            $('#sub_tax_'+item.id+'').html(
              $.o.tr.total_tax_row+`&nbsp;(`+item.tax_text+`%)&nbsp;:&nbsp;`
              +item.total_tax );
        }
        // end render sub taxt if exist

        // sub price item with quantity and tax
        $('#sub_price_'+item.id+'')
        .html( item.amount_tt_item );

    });

    // renew total_cart + total_tax
    $('#total_cart').html( $.o.cart.total_cart );

    // render total_tax if exist
    if( $.o.cart.total_tax_sale != 0 ){

        $('#total_tax')
        .html( $.o.tr.total_tax_row+`&nbsp;`+$.o.cart.total_tax_sale );
    }

    // if no tax remove container total tax sale
    if( $.o.cart.total_tax_sale == false ){

        $('#cont_render_tax').remove();
    }


  },
  /**
   * $.render_dynamic_prices();
   */



  /**
   * $.quant_manager( id, op );
   *
   * @param  {int} id   product id
   * @param  {str} op   'add' / 'sub'
   * @return {html}     add or remove a quantity
   */
  quant_manager : function( id, op ){


    // CART GESTION - loop over cart to supress item in CART
    for (var i = 0; i < $.o.cart.items.length; i++) {

        // find the item to supress in CART
        if( $.o.cart.items[i].id == id ){

            break;
        }
    }
    // end loop to suppr

    // if product quant_wanted is already to 1 -> return
    if( $.o.cart.items[i].quant_wanted == 1 && op == 'sub' ){
      return;
    }

    // not too much is aviable
    if( $.o.cart.items[i].quant_wanted == $.o.cart.items[i].quant_aviable
        && op == 'add' ){
      return;
    }

    if( op == 'sub' ){

        // decrem quant wanted
        $.o.cart.items[i].quant_wanted--;
    }

    if( op == 'add' ){

        // decrem quant wanted
        $.o.cart.items[i].quant_wanted++;
    }


    // calcul total cart
    $.calcul_total_cart();

    // Save data in sessionStorage - SAME IF CAR ITEMS IS EMPTY ! (for view)
    sessionStorage.setItem('cart', JSON.stringify($.o.cart));

    // render dynamic prices
    $.render_dynamic_prices();

  },
  /**
   * $.quant_manager( id, op );
   */



	/**
	 * $.Intl_currency( price );
	 *
	 * @param  {number/string} 	price description
	 * @return {string}   			price in string locale formmatted
	 */
	Intl_currency : function( price ){


			// test if translation is aviable
			try {

					var array_lang_server =
						Intl.getCanonicalLocales($.o.website.lang_locale); // array

			}
			catch (err) {

					console.log(err.toString());
			}

			const Formatter = new Intl.NumberFormat( array_lang_server, {
			  style: 'currency',
			  currency: $.o.website.currency_iso,
				minimumFractionDigits: 2
			});

			return Formatter.format( price );
	},
	/**
	 * $.Intl_currency( price );
	 */



  /**
   * $.calcul_total_cart();
   *
   * @return {type}  description
   */
  calcul_total_cart : function(){

      // render text nb_articles
      var format = ( $.o.cart.nb_articles == 1 )
			? $.o.tr.one_article : $.o.cart.nb_articles+' '+$.o.tr.articles;
      format = ( $.o.cart.nb_articles == 0 ) ? $.o.tr.empty_basket : format;

      $('#nb_articles').text(format);

      // prepa total
      $.o.cart.total_cart = 0;
      $.o.cart.total_tax_sale = 0;

      // calutate total price
      // loop over products selected
      $.o.cart.items.forEach(function( item, k ){

          var amount_tt_item = 0;
          var calcul_price = 0;
          var total_tax_row = 0;

          if( item.tax != 0 ){

              // add tax value to price
              calcul_price =
              ( item.price + ( item.price * ( item.tax / 100 ) ) ).toFixed(0); // cent
              // console.log('calcul_price_w_tax '+calcul_price );

              total_tax_row =
              ( item.price * ( item.tax / 100 ) ) * item.quant_wanted;
          }
          else{

            calcul_price = item.price; // cent
            // console.log('calcul_price '+calcul_price );
          }

          // ADD quantity - calut amount_tt_item
          amount_tt_item = calcul_price * item.quant_wanted; // cent
          // console.log('amount_tt_item '+amount_tt_item );

          // ADD to $.o.cart.total_tax_sale if exist
          $.o.cart.total_tax_sale +=
          ( total_tax_row == 0 ) ? 0 : total_tax_row; // cent

          // SET total_tax (float) row OR false
          $.o.cart.items[k].total_tax =
          ( total_tax_row == 0 )
					? false
					: $.Intl_currency( (total_tax_row/100) );

					// ADD to total cart
          $.o.cart.total_cart += amount_tt_item; // cent

          // attr amount_tt_item in obj in float string
          $.o.cart.items[k].amount_tt_item =
						$.Intl_currency( (amount_tt_item/100) );
					// (amount_tt_item/100).toFixed(2); // str float

      });
      // end loop over products selected

      // console.log('total_tax_sale '+$.o.cart.total_tax_sale );

      // pass total cart in string
      $.o.cart.total_cart =
				$.Intl_currency( ($.o.cart.total_cart/100) );
			// ($.o.cart.total_cart/100).toFixed(2);
      // console.log('total_cart '+$.o.cart.total_cart );

      // pass total cart in string
      $.o.cart.total_tax_sale = ( $.o.cart.total_tax_sale == 0 )
      ? false
			: $.Intl_currency( ($.o.cart.total_tax_sale/100) );

      // by MONEY
      $.o.cart.by_money = $.o.shop.by_money;

      // CB ENABLED - true by default ?
      $.o.cart.CB_enabled = true;

  },
  /**
   * $.calcul_total_cart();
   */


////////////////////////////////////////////////////////////////////////
/////       O P E N   P A Y M E N T   F O R M       ////////////////////
////////////////////////////////////////////////////////////////////////


  /**
   * $.open_payment_form();
   *
   * @return {html}  OPEN MODAL PAYMENT FORM
   */
  open_payment_form : function(){


		// set page context
		$.o.view.page_context = 'cart';

    // get the session storage if exist -> this exit function
    $.get_storage_cart_datas();


    // ERROR - IF CART IS EMPTY
    if( $.o.cart.items.length == 0 ){

        // alert
        $.show_alert('info', $.o.tr.empty_cart, false);

				return;
    }

    // calcul total cart
    $.calcul_total_cart();

    // console.log( $.o.cart );

    // show page function
    $.show_page( event, 'cart' );

		// add countries if not loaded or set country registered
		$.set_countries();

		// remove sort block
    $.clean_sort_block();

    // scroll to top
    $.scroll_top();

		// set good title of page
		document.title = $.o.view.title+' - '+$.o.tr.cart_url;

    // HISTORY
    if( history.state === null || history.state.page != 'cart' ){

        history.pushState({ page: 'cart' }, '','/cart/'+$.o.tr.cart_url);
    }

  },
  /**
   * $.open_payment_form();
   */



	/**
	 * $.set_countries();
	 *
	 * @return {html}  insert countries list in selects for country field
	 * if already charged - manage country already selected
	 */
	set_countries : function(){


			function add_countries_options(){


					// for both add selest string
					$('#form_payment select')
					.append(`<option value=" ">`+$.o.tr.choose_your_country+`</option>`);


					Countries.forEach((item, i) => {

							if( typeof $.o.cart.customer != 'undefined' ){

									if( typeof $.o.cart.customer.country != 'undefined'
											&& item == $.o.cart.customer.country ){

											$('#form_payment select#country')
											.append(`<option value="`+item+`" selected>`+item+`</option>`);
									}
									else if( typeof $.o.cart.customer.country_sup != 'undefined'
													 && item == $.o.cart.customer.country_sup ){

											$('#form_payment select#country_sup')
											.append(`<option value="`+item+`" selected>`+item+`</option>`);
									}
									else{
											$('#form_payment select')
											.append(`<option value="`+item+`">`+item+`</option>`);
									}

							}
							else{
									$('#form_payment select')
									.append(`<option value="`+item+`">`+item+`</option>`);
							}

					});
					// end loop Countries[]

			}
			// end function add_countries_options()


			// load countries array if not charged
			if( typeof Countries == 'undefined' ){

					$.getScript('JS/countries.js', function(){

							add_countries_options();
					});
			}
			else{

					add_countries_options();
			}

	},
	/**
	 * $.set_countries();
	 */



	/**
	 * $.get_storage_cart_datas();
	 *
	 * @return {type}  description
	 */
	get_storage_cart_datas : function(){

	  // get the session storage if exist
		if( !sessionStorage.getItem('cart') ){

				return;
		}

		// get object from session storage
		var retrievedObject_cart = sessionStorage.getItem('cart');

		// assign storage to cart
		$.o.cart = JSON.parse(retrievedObject_cart);

	},
	/**
	 *  $.get_storage_cart_datas();
	 */



	/**
	 * $.check_cart_in_memory();
	 *
	 * @return {type}  description
	 */
	check_cart_in_memory : function(){

			$.get_storage_cart_datas();

			if( $.o.cart.nb_articles != 0 ){

				  // render text nb_articles
		      var format = ( $.o.cart.nb_articles == 1 )
					? $.o.tr.one_article : $.o.cart.nb_articles+' '+$.o.tr.articles;
		      format = ( $.o.cart.nb_articles == 0 ) ? $.o.tr.empty_basket : format;

		      $('#nb_articles').text(format);
			}
	},
	/**
	 * $.check_cart_in_memory();
	 */



  /**
   * $.toggle_address_sup();
   *
   * @return {void}  TOGGLE BLOCK ADRESS SUP. IN PAYMENT
   */
  toggle_address_sup : function(){

    // IF ADRESS SUP IS HIDE
    if( $('#address_sup').hasClass('hide') ){

        // show adress sup
        $('#address_sup')
        .removeClass('hide')
        .addClass('show');

        // pass aria-expanded to true
        $('.toggle_address_sup')
        .attr('aria-expanded', 'true' );

    }
    else{

        // hide adress sup
        $('#address_sup')
        .removeClass('show')
        .addClass('hide');

        // pass aria-expanded to false
        $('.toggle_address_sup')
        .attr('aria-expanded', 'false' );
    }

  },
  /**
   * $.toggle_address_sup();
   */



  /**
   * $.check_this( for_label );
   *
   * @param  {str} for_label  label[for="for_label"]
   * 'CARD' / 'OTHER'
   * @return {void}      check
   */
  check_this : function( for_label ){


    // remove ICON checked for all
    $('label[for="CARD"] i, label[for="OTHER"] i')
    .removeClass('fa-check-square')
    .addClass('fa-square');

    // pass ARIA false for all
    $('label[for="CARD"], label[for="OTHER"]')
    .attr('aria-checked', 'false');

		// remove checked for all
    $('#CARD, #OTHER')
    .removeAttr('checked', 'false');

		// pass datas checked in obj to false for all
		$.o.cart.CARD_checked = false;
		$.o.cart.OTHER_checked = false;


    if( for_label == 'CARD' ){

        // set checkbox for item
        $('label[for="CARD"] i')
        .addClass('fa-check-square')
        .removeClass('fa-square');

        // need to change value - this really change value of radio
        $('input[name="pay_with"]').val('CARD');

        // manage ARIA attributes
        $('label[for="CARD"]').attr('aria-checked', 'true');

        // need to attr checked="checked"
        $('#CARD')
        .attr('checked','checked');

				// pass checked to true for view in template
				$.o.cart.CARD_checked = true;

    }
    else{

        $('label[for="OTHER"] i')
        .addClass('fa-check-square')
        .removeClass('fa-square');

        $('input[name="pay_with"]').val('OTHER');

        // manage ARIA attributes
        $('label[for="OTHER"]').attr('aria-checked', 'true');

        // need to attr checked="checked"
        $('#OTHER')
        .attr('checked','checked');

				// pass checked to true for view in template
				$.o.cart.OTHER_checked = true;

    }

    // console.log( $('input[name="pay_with"]').val() );

  },
  /**
   * $.check_this( for_label );
   */



  /**
   * $.check_sale_conditions();
   *
   * @return {void}  customer accept or decline sale conditions
   */
  check_sale_conditions : function(){


    // if is checked - uncheck
    if( $('#sale_conditions').val() == 'signed' ){

        // remove checked for all
        $('label[for="sale_conditions"] i')
        .addClass('fa-square')
        .removeClass('fa-check-square');

        $('#sale_conditions').val('no');

        // manage ARIA labels - no checked
        $('label[for="sale_conditions"]')
        .attr('aria-checked', 'false');

				$.o.cart.CONDS_checked = false;

				return;
    }

    if( $('#sale_conditions').val() == 'no' ){

        $('label[for="sale_conditions"] i')
        .addClass('fa-check-square')
        .removeClass('fa-square');

        $('#sale_conditions').val('signed');

        // manage ARIA labels - checked
        $('label[for="sale_conditions"]')
        .attr('aria-checked', 'true');

				$.o.cart.CONDS_checked = true;

        return;
    }

  },
  /**
   * $.check_sale_conditions();
   */


/////////////////////////////////////////////////
//////////   VALIDATE FORM CUSTOMER    //////////
/////////////////////////////////////////////////


  /**
   * $.valid_form_payment();
   *
   * @return {success/error}
   */
  valid_form_payment : function(){


    // disable onclick
    $('#btn_form_payment').removeAttr('onclick');

    // create FormData
    var datas = new FormData( $('form#form_payment')[0] );

    // append datas customer to cart ! before append to FormData
    // create obj.customer
    $.o.cart.customer = {};
    // push entries
    for(var item of datas.entries()) {

      $.o.cart.customer[item[0]] = item[1]; // [0] -> key, [1] -> value
    }

    // Save data to sessionStorage
    sessionStorage.setItem('cart', JSON.stringify($.o.cart));
    // console.log( $.o.cart );
    // return;

    // delete customer to cart before treatment
    // -> send data from the form to the server
    delete $.o.cart.customer;

    // append object cart - without customer
    datas.append('cart', JSON.stringify($.o.cart) );

    // append command
    datas.append('set', 'valid_form_payment');

    //  START SPINNER PROCESS
    $('#spinner_process').css('display','inline-block');

    var el_to_prevent = 'form#form_payment';
    var method = 'POST';
    var url = '/';
    var data_type = 'json';

    // sender send datas to server asynchronous and return data.obj
    $.sender(el_to_prevent, method, url, datas, data_type, function(data){


        // IF process_form CALL TOKEN
        if(data.process_form === true){

            // console.log( data.key );

            // pass total amount caluled by server TO CART
            $.o.cart.total_amount_sale_text = data.total_amount_sale_text;

            // pass two keys in global context for use it on ask_to_pay + submit card
            $.client_key = data.client_key;
            $.public_key = data.public_key;

						// global context :
            $.customer_id = data.customer_id;
            $.sale_id = data.sale_id;
            $.hash_customer = data.hash_customer;
            $.payment_id = data.payment_id; // only for card for abort
            // pass payment_mode to cart -> test or production
            $.o.cart.payment_mode_test = data.payment_mode_test;

            // ASK TO PAY
            $.ask_to_pay();

        }
        // END IF process_form

        // IF success -> PAY with MONEY or OTHER
        if(data.direct_sale){

            // pass total amount caluled by server TO CART
            $.o.cart.total_amount_sale_text = data.total_amount_sale_text;

            // global context :
            $.customer_id = data.customer_id;
            $.sale_id = data.sale_id;
            $.hash_customer = data.hash_customer;

            $.go_direct_sale();

        }
        // END IF PAY with MONEY or OTHER


        // error
        if( data.error ){

            // RENDER ERROR
            $.show_alert('warning', data.message, false );

            // re-attr onclick
            $('#btn_form_payment').attr('onclick', '$.valid_form_payment();');

        }
        // end error

        // hide spinner
        $('#spinner_process').css('display','none');


    });
    // end sender

  },
  /**
   * $.valid_form_payment();
   */



  // prepa. objects for Stripe
  stripe : {},
  elements : {},
  cardNumberElement : {},
  cardExpiryElement : {},
  cardCvcElement : {},

  /**
   * $.ask_to_pay();
   *
   * i. $.client_key     client_key returned by the server
   * @return {html}      open modal card payment
   */
  ask_to_pay : function(){


    // empty template card form
    $('#modal_payment .modal-content').empty().mustache('payment_card_form', $.o);

    // prevent onclick in card form
    $.prevent_links('#card_form');


    // open_form_card - get stripe API js
    var prom = new Promise(function(resolve, reject) {

      $.getScript( 'https://js.stripe.com/v3/', function( data, textStatus, jqxhr ){

          // error fetch Stripe JS
          if( jqxhr.status != 200 ){

              $.show_alert('warning', $.o.tr.error_payment_not_init, true);

              return;
          }
          // END error

          // success fetch Stripe JS
          if( jqxhr.status == 200 && textStatus == 'success' ){

              resolve(data);
          }

      });
      // end getScript

    });
    // END PROMISE

    // Promise resolved
    prom.then(function(){

        // init Stripe w. public vendor key
        $.stripe = Stripe( $.public_key );

        $.elements = $.stripe.elements({
          fonts: [
            { cssSrc: 'https://fonts.googleapis.com/css?family=Roboto', },
          ],
          // Stripe's examples are localized to specific languages,
          // but use `locale: 'auto'` instead.
          locale: 'auto'
        });

        // STYLE FOR STRIPE ELEMENT
        var Style = {
            base: {
                iconColor: '#2d2d2d',
                color: '#2d2d2d',
                fontWeight: 500,
                fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
                fontSize: '20px',
                fontSmoothing: 'antialiased',
                ':-webkit-autofill': {
                  color: '#2d2d2d',
                  background : '#fff'
                },
                '::placeholder': {
                  color: '#848484',
                },
            },
            invalid: {
              iconColor: '#e48b4d',
              color: '#ff5819',
            },
        };


        // CREATE 3 ELEMENTS CARD NUMBER, EXP., CVC
        $.cardNumberElement = $.elements.create('cardNumber', {
          style: Style,
          placeholder: '1234 1234 1234 1234',
        });
        $.cardNumberElement.mount('#card-number-element');
        // clear error on change
        $.cardNumberElement.on('change', ()=>{ $.clear_error_card() } );

        $.cardExpiryElement = $.elements.create('cardExpiry', {
          style: Style,
          placeholder: 'MM/YY',
        });
        $.cardExpiryElement.mount('#card-expiry-element');
        // clear error on change
        $.cardExpiryElement.on('change', ()=>{ $.clear_error_card() } );

        $.cardCvcElement = $.elements.create('cardCvc', {
          style: Style,
          placeholder: '123',
        });
        $.cardCvcElement.mount('#card-cvc-element');
        // clear error on change
        $.cardCvcElement.on('change', ()=>{ $.clear_error_card() } );


        // SHOW TEMPLATE
        $('.amount').text( $.o.cart.total_amount_sale_text );
        $('#modal_payment').show();

        // able to click on #submit_card
        $('#submit_card').attr('onclick', '$.submit_card(event);' );

    });
    // end Promise resolved

  },
  /**
   * $.ask_to_pay();
   */



  /**
   *  $.clear_error_card();
   *
   * @return {void}  clear error card text
   */
  clear_error_card : function(){

    if( $('#card-errors').css('display') == 'block' ){

        $('#card-errors').hide().empty();
    }

  },
  /**
   *  $.clear_error_card();
   */



  /**
   * $.submit_card( event );
   *
   * @param  {event} event
   * @return {ajax}  success / error
   */
  submit_card : function(e){

    e.preventDefault();

    // block onclick card
    $('#submit_card').removeAttr('onclick');
    // block abort card
    $('#destroy_card').removeAttr('onclick');

    // hide $('#card-errors') if is visible
    if( $('#card-errors').is(':visible') == true ){

        $('#card-errors').hide();
    }

    // show wait message
    $('#render_pay_work').show();

    // send payment datas to stripe - Promise
    $.stripe.confirmCardPayment( $.client_key, {

        payment_method: {
          card: $.cardNumberElement,
        },
        return_url:  window.location.href,
        receipt_email : $('#mail').val(),

    }).then(function(result) {

      // errors payment
      if( result.error ){

          // console.log(result.error.message);
          $('#card-errors').text( result.error.message ).show();

          $('#render_pay_work').hide();

          // re-attr onclick
          $('#submit_card').attr('onclick', '$.submit_card( event );' );

          // re-attr onclick on abort card
          $('#destroy_card').attr('onclick', '$.destroy_card_payment();' );

          // exit function here
          return;

      }
      // end errors payement


      // success payment // result.paymentIntent.status
      if( result.paymentIntent.status === 'succeeded' ){

        // console.log( result.paymentIntent );

        // confirm user payment to server
        $.post('/',
        { set: 'confirm_user_payment',
          payment_id: result.paymentIntent.id,
          sale_id: $.sale_id
        }, function(data){

              // get page render SALE - this re-init API obj.
              $.get_sale( $.sale_id, $.hash_customer );

        });

				// render personnal message success
				$.o.pay_success = {
						title: $.o.tr.payment_successful,
						kind_order : $.o.tr.your_payment_of,
						firstname : $('#firstname').val(),
						total_amount : $.o.cart.total_amount_sale_text,
						sale_id : $.sale_id,
						mail : $('#mail').val()
				};

        var html_success = $.Mustache.render('payment_success', $.o );

        // SHOW SUCCESS
        $.show_alert('success', html_success, true);

				// delete $.o.obj_pay_success -> no need anymore
				delete $.o.pay_success;

        // this close modal card payment
        $.close_modal_card_payment();

        // empty cart infos
        $('#nb_articles').text( $.o.tr.empty_basket );

        // delete cart in sessionStorage
        sessionStorage.removeItem('cart');

        // re-init cart
        delete $.o.cart;

        $.o.cart = {
            items: [],
            nb_articles: 0
        };

        // scroll top
        $.scroll_top();

      }
      // end success payment

    });
    // end stripe - Promise - resolved

  },
  /**
   * $.submit_card( event );
   */



  /**
   * $.go_direct_sale();
   *
   * @return {html}
   */
  go_direct_sale : function(){


      // render personnal message success
			$.o.pay_success = {
					title: $.o.tr.order_successful,
					kind_order : $.o.tr.your_order_well_accepted,
					firstname : $('#firstname').val(),
					total_amount : $.o.cart.total_amount_sale_text,
					sale_id : $.sale_id,
					mail : $('#mail').val()
			};

			var html_success = $.Mustache.render('payment_success', $.o );

      // SHOW SUCCESS
      $.show_alert('success', html_success, true);

      // empty cart infos
      $('#nb_articles').text( $.o.tr.empty_basket );

      // delete cart in sessionStorage
      sessionStorage.removeItem('cart');

      // re-init cart
      delete $.o.cart;

      $.o.cart = {
          items: [],
          nb_articles: 0
      };

      // get page SALE
      $.get_sale( $.sale_id, $.hash_customer );

      // scroll top
      $.scroll_top();

  },
  /**
   * $.go_direct_sale();
   */



  /**
   * $.get_sale( sale_id, hash_customer );
   *
   * @param  {int} sale_id      new sale id
   * @return {html}             return the sale user page
   */
  get_sale : function( sale_id, hash_customer ){


    // get ajax infos + new SALE - renew object API with good quantities products
    $.get( '/sale/'+sale_id+'/'+hash_customer+'',
    { req : 'ajax' }, function(data){

          // console.log(data);

          // renew Objet API
          $.o = data;

          $.clean_sort_block();

          // get render_sale page
          $('#center_page').empty().mustache('render_sale', $.o );

					// set good title of page
					document.title = $.o.view.title+' - '+$.o.tr.your_order;

          // HISTORY
          if( history.state.page != 'sale'
          && history.state.sale_id != sale_id ){

              history.pushState({
                page: 'sale',
                sale_id: sale_id,
                hash_customer: hash_customer
              },'', '/sale/'+sale_id+'/'+hash_customer );
          }
          // end history

      }, 'json');
      // end get

  },
  /**
   * $.get_sale( sale_id, hash_customer );
   */



  /**
   * $.get_sale_user( event );
   *
   * @param  {type} event description
   * @return {type}       description
   */
  get_sale_user : function( event ){


			event.preventDefault();

      // disable onclick
      $('#get_sale_user').removeAttr('onclick');

      // create FormData
      var datas = new FormData( $('form#form_render_sale')[0] );

      // append command
      datas.append('set', 'get_sale_user');

      //  START SPINNER PROCESS
      $('#spinner_process').css('display','inline-block');

      var el_to_prevent = 'form#form_payment';
      var method = 'POST';
      var url = '/';
      var data_type = 'json';

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // info -> sale is treated 
          if( data.info ){

              // show error
              $.show_alert( 'info', data.info, false );

              // re-attr click
              $('#get_sale_user').attr('onclick', '$.get_sale_user( event );');
          }
          // end info

          // success
          if( data.success ){

              // renew Objet API
              $.o.SALE = data.SALE;

              // globals sale id + hash customer
              $.sale_id = $.o.SALE.sale_id;
              $.hash_customer = data.hash_customer;

              // get render_sale page
              $('#center_page').empty().mustache('render_sale', $.o );

              // HISTORY
              history.pushState({
                page: 'sale',
                sale_id: $.sale_id,
                hash_customer: $.hash_customer
              },'', '/sale/'+$.sale_id+'/'+$.hash_customer );
          }
          // end success

          // error
          if( data.error ){

              // show error
              $.show_alert( 'warning', data.error, false );

              // re-attr click
              $('#get_sale_user').attr('onclick', '$.get_sale_user( event );');
          }
          // end error

      });
      // end sender

  },
  /**
   * $.get_sale_user( event );
   */



  /**
   * $.destroy_card_payment();
   *
   * @return {success/error}  abort a card payment
   */
  destroy_card_payment : function(){


    // show wait message
    $('#render_pay_work').show();

    // block onclick card
    $('#submit_card').removeAttr('onclick');
    // block abort card
    $('#destroy_card').removeAttr('onclick');

    $.post('/',
    {
      set: 'destroy_new_sale',
      sale_id : $.sale_id,
      customer_id : $.customer_id,
      hash_customer : $.hash_customer,
      payment_id : $.payment_id
    }, function(data){

      // success
      if( data.success ){

          $.show_alert('success', $.o.tr.payment_well_aborted, false);

          $.close_modal_card_payment();
      }

      // error
      if( data.error ){

          $.show_alert('error', data.message, true);

          $.close_modal_card_payment();
      }

    }, 'json');
    // end $.post

  },
  /**
   * $.destroy_card_payment();
   */



  /**
   * $.close_modal_card_payment();
   *
   * @return {void}  close card payment form and destroy Stripe Elements
   */
  close_modal_card_payment : function(){

      $.cardNumberElement.destroy();
      $.cardExpiryElement.destroy();
      $.cardCvcElement.destroy();

      // destroy keys
      $.public_key = '';
      $.client_key = '';
      $.payment_id = '';

      // empty and close modal
      $('#modal_payment').hide();
      $('#modal_payment .modal-content').empty();

      // re-attr onclick on form customer
      $('#btn_form_payment').attr('onclick', '$.valid_form_payment();');
  },
  /**
   * $.close_modal_card_payment();
   */



});
// END EXTEND

});
// END JQUERY
