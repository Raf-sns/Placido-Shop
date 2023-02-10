/**
 * PlACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 *
 * Script name:	pagination.js
 *
 * $.open_choice_nb_pagina();
 * $.adjust_nb_pagina( number );
 * $.return_for_pagina( OBJECT );
 * $.pagina_on_animation = false;
 * $.pagina( dir );
 *
 */

$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


	/**
	 * $.open_choice_nb_pagina();
	 *
	 * @return {html}  show dropdown to choice number of products by page
	 */
	open_choice_nb_pagina : function(){


		if( $('#select_nb_options').hasClass('show') == false ){

				$('#select_nb_options').addClass('show');
				$('button.open_choice_nb_pagina').attr('aria-expanded','true');
	  }
		else {
	    	$('#select_nb_options').removeClass('show');
				$('button.open_choice_nb_pagina').attr('aria-expanded','false');

		}

	},
	/**
	 * $.open_choice_nb_pagina();
	 */



 	/**
 	 * $.adjust_nb_pagina( number );
 	 *
 	 * @param  {int} number number of items wanted for view
 	 * @return {html}     	set hte number and return view
 	 */
 	adjust_nb_pagina : function( number ){


    $.o.view.nb_wanted = parseInt(number, 10);

    var OBJ = {};
    var div;

    if( $.o.view.page_context == 'cat' || $.o.view.page_context == 'home' ){

      OBJ = $.o.view.temp; // TEMP ARRAY
      div = '#center_page';

    }
    else{

      OBJ = $.o.products; // PRODUCTS ARRAY
      div = '#center_page';
    }

    // adjust obj.view
    $.return_for_pagina(OBJ);

		// template products in context 'inline' / 'mozaic'
		var templ_products =
			( $.o.view.display_products == 'inline')
				? 'products_view_inl' : 'products_view';

    $.show_center_page('fade', div, templ_products);

		$('#view_nb_wanted').text($.o.view.nb_wanted);

	},
  /*
   *  $.adjust_nb_pagina( number );
   */



  /**
   * $.return_for_pagina( OBJECT );
   *
   * @param  {object} OBJ  Object arrays to be paginated
	 *
   */
  return_for_pagina : function( OBJ ){


    // console.log(OBJ);
    var count_obj = OBJ.length;

		$.o.view.temp = [];
		$.o.view.products = [];
		$.o.view.page = 1;

    // IF NO PRODUCTS
    if( count_obj == 0 ){

        $.o.view.temp = $.o.products;

    } // HAVE SOME ARTICLES
    else{

        // INCLUDE OBJ
        $.o.view.temp = OBJ;
		}

    // calcul pages need

    // if count products is LESS of nb wanted
    // nb_wanted == count_obj
    if( count_obj < $.o.view.nb_wanted ){

        $.o.view.nb_wanted = count_obj;
		}

		// rep. added for assign active nb_wanted
    var added = false;

    // test if option exist / else add it to the selector
    $('#select_nb_options li').each(function(k, item){

					// remove attr selected
					$(this).addClass('color_inverse');

          var val_opt = parseInt( $(this).data('nb_option') , 10 );

          // if equals pass selected to true
          if( $.o.view.nb_wanted == val_opt ){

              $('#select_nb_options li').eq(k).removeClass('color_inverse');

              added = true;
          }

          // insert if not found
          if( $.o.view.nb_wanted < val_opt
							&& $.o.view.nb_wanted != val_opt
							&& added == false ){

              $('#select_nb_options li').eq(k).before(
                $(`<li onclick="$.adjust_nb_pagina(`+$.o.view.nb_wanted+`);"
								data-nb_option="`+$.o.view.nb_wanted+`"
								class="bar-item
								border button round hover-opacity"
								aria-label="`+$.o.tr.adjust_nb_pagina+` `+$.o.view.nb_wanted+` `+$.o.tr.articles_button+`">
									<i class="fa-fw fa-hand-point-right far"></i>&nbsp;
									`+$.o.view.nb_wanted+`&nbsp;`+$.o.tr.articles_button+`
								</li>`) );

              added = true;

          }
					// insert if not found

    });
    // end each select / option


    $.o.view.pages_need = Math.ceil( (count_obj / $.o.view.nb_wanted) );

		// loop for add good items for nb_wanted
		for (var i = 0; i < $.o.view.temp.length; i++) {

				// put items for first page
				if( i < $.o.view.nb_wanted ){

						$.o.view.products.push($.o.view.temp[i]);
				}
		}
		// end loop

		$('#view_nb_wanted').text($.o.view.nb_wanted);
    $('.page_number').text(1);
    $('.pages_need').text($.o.view.pages_need);


  },
  /**
   * END $.return_for_pagina( OBJECT );
   */


	/* $.pagina_on_animation */
	pagina_on_animation : false,


	/**
	 * $.pagina( dir );
	 *
	 * @param  {str} dir 	'prev' / 'next' -> direction to pagina
	 * @return {html}     another page of products
	 */
	pagina : function( dir ){


		// return if pagnation is already run
		if( $.pagina_on_animation == true ){

			 return;
		}

		// animation running
		$.pagina_on_animation = true;

		$('.prev_btn').removeAttr('onclick');
		$('.next_btn').removeAttr('onclick');

		// incr. or decr. page number [1->n]
		$.o.view.page = ( dir == 'next' ) ? $.o.view.page+=1 : $.o.view.page-=1;
		// loop pagination if page < 1
		$.o.view.page = ( $.o.view.page < 1 ) ? $.o.view.pages_need : $.o.view.page;
		// loop at page 1 if page number is over
		$.o.view.page = ( $.o.view.page > $.o.view.pages_need ) ? 1 : $.o.view.page;
		// set page number in view
		$('.page_number').text($.o.view.page);

		// console.log('page '+ $.o.view.page );

		// calcul index end
		var index_end = ($.o.view.page * $.o.view.nb_wanted) - 1;
		// calcul index start
		var index_start = index_end - $.o.view.nb_wanted;
		// first index start always == 0
		index_start = ( index_start < 0 ) ? 0 : index_start;
		// lil hack to capture good indexes
		index_start = ( index_start > 0
		|| ( index_start == 0 && $.o.view.page != 1) ) ? index_start+=1 : index_start;

		// console.log(index_start);
		// console.log(index_end );


		// re-init view products
		$.o.view.products = [];

		// assing products between good indexes
		for(var i = 0; i < $.o.view.temp.length; i++) {

        if( i >= index_start && i <= index_end ){

            $.o.view.products.push($.o.view.temp[i]);
        }
    }

		// console.log( $.o.products );
		// console.log( $.o.view.products );

    // on animation
    var width_center = $('#center_page').width();
    var height_center = Math.round( $('#center_page').height() );
    var move_new = 0;
    var move_suppr = 0;
    var anim_time = 450;


    // set main height
    $('#center_page').css( 'height', height_center+'px' );

		// wrap products view
		$('#products_view')
		.wrap(`<div class="to_suppr_content"
						style="position: absolute;
						top: 0;
						left: 0;
						max-width: 1200px;
						width: `+width_center+`px;
						height: 100%;"></div>`)
		.removeAttr('id')
		.attr('id','products_view_old');

		// make wrapper for new items
		$('#center_page').append('<div class="new_wrapper"></div>');

    // add 100 for marge between wrappers
    move_new = ( dir == 'next' )
    ? ( width_center + 100 ) : ( width_center + 100 )*-1 ;

		// add style to new wrapper
    $('.new_wrapper').css({
      'position': 'absolute',
      'top': '0',
      'left' : move_new+'px',
      'max-width': '1200px',
			'width' : width_center+'px',
      'height': '100%'
    });

		// template products in context 'inline' / 'mozaic'
		var templ_products =
			( $.o.view.display_products == 'inline')
				? 'products_view_inl' : 'products_view';

		// add products new page in wrapper
		$('.new_wrapper').mustache( templ_products, $.o );


		// launch previously lazy_load_imgs()
    $.lazy_load_imgs();

		move_suppr = ( dir == 'next' )
		? ((window.innerWidth/2)+width_center)*-1 : width_center+(window.innerWidth/2);

		// console.log( move_suppr );

		// get out old wrapper
		$('.to_suppr_content').animate({'left' : move_suppr+'px'},
		{ duration : anim_time, queue : false });


		// append new items
		$('.new_wrapper').animate({'left' : '0'},
		{ duration : anim_time,
			queue : false,
			done : function(){

					// remove old content
					$('.to_suppr_content').remove();

			    $('#products_view').unwrap('.new_wrapper');

					$('.prev_btn').attr('onclick', "$.pagina('prev')" );
					$('.next_btn').attr('onclick', "$.pagina('next')" );

					$.pagina_on_animation = false;

					$('#center_page').removeAttr( 'style' );

			}
			// end done
		});
		// end  append new items


	},
	/**
	 * $.pagina( dir );
	 */




});
// end extend
});
// end jquery
