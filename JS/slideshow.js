/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello, 2022-2024
 * Organisation: SNS - Web et informatique
 * Website/contact: https://sns.pm
 *
 * Script name:	slideshow.js
 *
 * Extented:
 *
 * Global properties ...
 *
 * Methods :
 *
 * $.adjust_height_slideshow();
 * $.detectTapMobile();
 * $.slideshow_controls();
 * $.enable_mousewheel_slideshow();
 * $.slideshow_touchSwipe();
 * $.expand_slideshow();
 * $.adjust_size_img( width_img, height_img, i, reduction_factor );
 * $.add_prom_sl( ien, img );
 * $.init_slideshow( container );
 * $.apply_style_imgs();
 * $.move_slides( direction );
 * $.set_window_events();
 * $.remove_window_events();
 * $.close_slideshow();
 *
 */



// START JQUERY
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * Global properties
   */
  index_base : 0, // index base for visible img
  diapo_nav_fired : false, // watch if animation is processing
	zoomed : false, // boolean for zoom
  scroll_val : 0, // scroll value given by TouchSwipe
	width_imgs_sl : 800, // global imgs width setted for mobile devices
	out_device : window.innerHeight*2, // set a value to put out devices imgs are not before or after img visible
	direction : '', // navigation direction
	wheel_value : 0, // value of mousewheel
	old_direction_wheel : null, // keep an old direction for un-bug
	length_imgs_sl : 0, // length of imgs
	img_1 : 0, // index imgs 1 - visible
	img_2 : 0, // 2 - tranlated to right of img visible
	img_3 : 0, // 3 - transalted to left of img visible
	Arr_prom_sl : [], // array of Promises
	ARR_imgs_sl : [], // array of intrinsic imgs sizes
	ARR_sizes_imgs : [], // array of imgs sizes calculated [ {'width': ...,'height': ...} ... ]
	timer_resize : null, // a timer for execute callback on resize
	timer_controls : null, // a timer for show / hide controls
  /**
   * end Global properties
   */



	/**
	 * $.adjust_height_slideshow();
	 *
   * @return {void} adjust slideshow container padding
	 */
	adjust_height_slideshow : function(){


			// calcul height of img
			var hei_img = Math.round( $('#img_cont img').eq($.index_base).height() );

			// adjust controls to height of current img
			$('#img_cont, #slideshow_controls')
			.css({ 'height' : ''+hei_img+'px' });

			// only for mobile devices ON LANDSCAPE - recalcul padding
			if( window.screen.width < 1200 && window.innerHeight < window.innerWidth ){

					var calcul_padding =
					($.ARR_sizes_imgs[$.img_1].height * 100) / (window.innerHeight - ((window.innerHeight*2.5)/100));

					var padding =
					Number( ( (100-calcul_padding)/2 ).toFixed(2) );

					$('#render_diapo').css({ 'padding' : ''+padding+'% 0' });
			}
			else{

					$('#render_diapo').css('padding', '2% 0 0');
			}

	},
	/**
	 * $.adjust_height_slideshow();
	 */



	/**
	 * $.detectTapMobile();
	 *
   * @param  {event} event
   * @return {void}  show conttrols of the slideshow for mobile devices
	 */
	detectTapMobile: function(event) {

			// just for mobile devices
			if( window.screen.width < 1200 ){

					$('#img_cont').on('click', function(event){

							if( $('#slideshow_controls').is(':visible') == false ){

									$('#slideshow_controls').fadeIn( 800 );
							}
							else{

									$('#slideshow_controls').fadeOut( 800 );
							}

					});
					// end onclick img cont
			}
			// just for mobile devices

	},
	/**
	 * $.detectTapMobile();
	 */



  /**
   * $.slideshow_controls();
   *
   * @return {html}  show / hide slider controls
   */
  slideshow_controls : function() {


			// not display if window width <= 900
			if( window.innerWidth <= 900 ){

					// not able to expand on mobile devices
					$('#slider_expand').css('display','none');
			}

			$.detectTapMobile();

			// show controls
      $('#img_cont').on('mouseenter.slideshow dblclick.slideshow', function(){


					if( $.timer_controls != null ){

							return;
					}

					$('#slideshow_controls').fadeIn( 800 );

					$.timer_controls = window.setTimeout(function(){

							$('#slideshow_controls').fadeOut( 800 );

							window.clearTimeout( $.timer_controls );

							$.timer_controls = null;

					}, 3000);

      });
			// end show controls

  },
  /**
   * $.slideshow_controls();
   */



	/**
	 * $.enable_mousewheel_slideshow();
	 *
	 * @return {void}  scroll left or right on mouse wheel event
	 */
	enable_mousewheel_slideshow : function(){


		// ON MOUSE WHEEL
		$('#img_cont').on('wheel.slideshow mousewheel.slideshow DOMMouseScroll.slideshow',
		function(event){

			event.preventDefault();

			// check direction
			var dir = ( event.originalEvent.wheelDeltaY < 0 ) ? 'right' : 'left';

			if( $.old_direction_wheel == null ){

					$.old_direction_wheel = dir;
			}
			else if ( $.old_direction_wheel != dir ){

					$.wheel_value = 0;

					$.old_direction_wheel = null;
			}

			$.wheel_value += Math.abs( Math.round(event.originalEvent.wheelDeltaY) );

			// if diapo_nav not fired & Scroll wheel value >= Math.round($.width_imgs_sl/3)
			if( $.diapo_nav_fired == false
					&& $.wheel_value >= Math.round($.width_imgs_sl/3) ){

					// pass fired to true
					$.diapo_nav_fired = true;

					$.move_slides( dir );

					$.wheel_value = 0;
			}

		});
		// end SLIDE ON WHEEL

	},
	/**
	 * $.enable_mousewheel_slideshow();
	 */



	/**
	 * $.slideshow_touchSwipe();
	 *
	 * @return {void}  launch touchSwipe API
	 */
	slideshow_touchSwipe : function(){


		$('#img_cont').swipe({
      // ! carefull swipeStatus != swipe funct.
			// in swipe
			swipeStatus : function( event, phase, currentDirection, distance ){

					// CLOSE ON TOP
					if( currentDirection == 'up' && distance > 20 ){

							$('#render_diapo .modal_container')
							.addClass('anim_diapo_top_close');

							// QUIT ON DISTANCE TO UP
							if(  distance > 100  ){

									$.close_slideshow();
									return;
							}

					}

					// UN-ZOOM ON CANCEL
					if( phase == 'cancel' || phase == 'end' ){

							// don't scroll enough to top for close
							$('#render_diapo .modal_container')
							.removeClass('anim_diapo_top_close');

							// don't scroll enough left or right
							$('#img_cont img').eq($.index_base).css({
								'transform': 'translate(0, 0)'
							});

							// re-init scroll val
							$.scroll_val = 0;
					}
					// CLOSE ON TOP


					// on moving
					if( phase == 'move'
					&& currentDirection != 'up'
					&& currentDirection != 'down'
					&& $.diapo_nav_fired == false ){

							// easing
							// calcul distance
							var calc_dist =
							Math.round( distance - Math.round( distance*0.97 ) );

							$.scroll_val += ( currentDirection == 'right' )
							? calc_dist
							: calc_dist*-1;

							// animate image on slide - class trans add smooth
							$('#img_cont img').eq($.index_base)
							.addClass('trans')
							.css({ 'transform': 'translate('+$.scroll_val+'px, 0)' });

							// start the animation if the distance is exceeded
							if( distance > 70 && phase == 'move'
							&& currentDirection != 'up'
							&& currentDirection != 'down' ){

									// get direction
									$.direction = currentDirection;

									// pass fired to true
									$.diapo_nav_fired = true;

									$('#img_cont').swipe('disable');

									$.move_slides( $.direction );

									$.scroll_val = 0;
							}
							// end start the animation if the distance is exceeded

					}
					// end  on moving

			},
			// options :
			triggerOnTouchEnd: false,
			allowPageScroll:"vertical",
			preventDefaultEvents: true,
			threshold: 100,
		});
		// END TOUCHSWIPE

	},
	/**
	 * $.slideshow_touchSwipe();
	 */



	/**
	 * $.expand_slideshow();
	 *
	 * @return {html} zoom slider on click
	 * -> empty array $.ARR_sizes_imgs with new values
	 * - ONLY FOR DESKTOP
	 */
	expand_slideshow : function(){


			// disable if window <= 900
			if( window.innerWidth <= 900 ){

					return;
			}

			var reduction_factor;

			// set width imgs required
			if( $.zoomed == false ){

					reduction_factor = 5; // 5% reduction for zoom

					$.zoomed = true;
			}
			else{

					reduction_factor = 20; // 20% reduction not zoomed

					$.zoomed = false;
			}

			// re-init array $.ARR_sizes_imgs
			$.ARR_sizes_imgs = [];

			$.direction = '';

			// loop on $.ARR_imgs_sl -> instrinsic sizes array of imgs
			$.ARR_imgs_sl.forEach((item, i) => {

					$.adjust_size_img( item.W, item.H, i, reduction_factor );
			});

			// set CSS translate for all imgs
			$.apply_style_imgs();

			// height of controls
			$.adjust_height_slideshow();

	},
	/**
	 * $.expand_slideshow();
	 */



	/**
	 * $.adjust_size_img( width_img, height_img, i, reduction_factor );
	 *
	 * ! this is processed ON A LOOP
	 *
	 * @param  {int/float} 	width_img  intrinsic width of img
	 * @param  {int/float} 	height_img intrinsic height
	 * @param  {int} 				i          index in slider
	 * @param  {int/float} 	reduction_factor  value in percent
	 * @return {array}  		$.ARR_sizes_imgs[]
	 * ... { 'height': new_height, 'width' : new_width }
	 * array with calculated with and height of slider imgs
	 */
	adjust_size_img : function( width_img, height_img, i, reduction_factor ){


			// diminued window on desktop
			if( window.innerWidth <= 900 ){

					// force factor to 5%
					reduction_factor = 5;
			}

			// calcul factor - 20% / 5%
			var factor = parseFloat( (reduction_factor/100).toFixed(2) );

			// if landscape
			if( window.innerHeight < window.innerWidth ){

					// test ratio for adjustments ( return float rounded to 0.05 )
				 	var test_ratio =
						Number( (window.innerWidth/window.innerHeight).toFixed(2) );

					// adjust for landscape images
					// but target window height if window is 2x taller than tallest
					// only on view > 900 px
					var size_window =
					( height_img >= width_img
						|| ( test_ratio > 1.75 && window.innerWidth > 900 )  )
					? window.innerHeight - (window.innerHeight*factor)
					: window.innerWidth - (window.innerWidth*factor);
			}
			else{
			    // if portrait
					var size_window = window.innerWidth - (window.innerWidth*factor);
			}

			// calcul ratio
			// dimension to process
			var dim = ( height_img >= width_img )
			? height_img : width_img;

			var ratio =
			parseFloat( ( (size_window * 100) / dim ).toFixed(2) );

			// calcul new height
			var new_height =
			Math.round( (height_img * ratio) / 100 );

			// calcul new width
			var new_width =
			Math.round( (width_img * ratio) / 100 );

			// push on array for adjust container + translations
			// on navigation slider
			$.ARR_sizes_imgs.push({ 'height': new_height, 'width' : new_width });

	},
	/**
	 * $.adjust_size_img( width_img, height_img, i, reduction_factor );
	 */



	/**
	 * $.add_prom_sl( Promise_name, src, i );
	 *
	 * @param  {string} Promise_name 	name of the Promise -> Must be != as others
	 * @param  {string} src 	src img to load
	 * @param  {int} 		i     index of img for get width / height of img
	 * @return {array}  return an array of Promises to pass at Promise.all( [...] )
	 */
	add_prom_sl : function( Promise_name, src, i ){


			Promise_name = new Promise((resolve, reject) => {


					$('#img_cont img').eq(i).on('load', function(){

							var width_img = $(this).width();
							var height_img = $(this).height();

							$.ARR_imgs_sl.push({ 'W' : width_img,
																	 'H' : height_img });

							// get intrinsic sizes, calcul ratio,
							// set width imgs, put on array $.ARR_sizes_imgs[]
							var reduction_factor = 20;

							$.adjust_size_img( width_img, height_img, i , reduction_factor );

							resolve();
					});
					// end on load
	    });
			// end new promise

			// PUSH to array of Promises
	    $.Arr_prom_sl.push( Promise_name );
	},
	/**
	 * $.add_prom_sl( ien, img );
	 */



  /**
   * $.init_slideshow( container );
   *
   * @param  {element} container html element who contain imgs
   * @return {html}    open slideshow of imgs for one container
   */
  init_slideshow : function( container ){


    // TOTAL LENGTH IMGs
		// adjust if length of imgs < 3
		// -> the slideshow need 3 images min. to work fine
    $.length_imgs_sl = ( $(''+container+' img').length == 2 )
		? 4 : $(''+container+' img').length;

		$.length_imgs_sl = ( $(''+container+' img').length == 1 )
		? 3 : $.length_imgs_sl;

    // console.log( $.length_imgs_sl );
		// set indexes of imgs by default
		$.img_1 = 0;
		$.img_2 = 1;
		$.img_3 = $.length_imgs_sl-1;

    // navigation buttons + close button + render pagination
    var navigation_controls = `
    <div id="slideshow_controls">

			<i onclick="$.move_slides('right');"
	    class="open_modal fa-angle-left fas pointer round slider_nav slide_to_left"></i>

	    <i onclick="$.move_slides('left');"
	    class="open_modal fa-angle-right fas pointer round slider_nav slide_to_right"></i>

	    <span id="diapo_counter"
	    class="padding-small round">1/`+$.length_imgs_sl+`</span>

	    <span id="close_slider" onclick="$.close_slideshow();"
	    class="padding-small round pointer hover-shadow">
				&nbsp;<i class="fas fa-times"></i>&nbsp; `+$.o.tr.close+`&nbsp;</span>

			<span id="slider_expand" onclick="$.expand_slideshow();"
			class="padding-small round pointer hover-shadow"><i class="fas fa-expand"></i></span>

    </div>`;

    // APPEND MODAL TO BODY
    $('body').append(`<div class="animate-zoom" id="render_diapo">
								        <div class="modal_container">
													<div id="slider_loader"><i class="fas fa-circle-notch fa-spin"></i></div>
								          <div id="img_cont">`+navigation_controls+`</div>
								        </div>
								      </div>`);


		// show slideshow with loader
		$('#render_diapo').css({'visibility': 'visible'});


		// CLONE IMGS
		// Make a html collection of imgs into the container passed
		var Imgs = document.querySelectorAll(''+container+' img');

		var container_sl = document.getElementById('img_cont');


		// need to create an add_imgs() function
		// -> for add at the minimal 3 imgs into the slideshow
		function add_imgs( Imgs, container_sl ){

				Imgs.forEach((image, i) => {

						// console.log(image);
						var img = document.createElement('img');

						// if DEVICE SCREEN < 1200px get min src
						// get true src if lazy loading not fire -> true src is data-src attr
						if( image.hasAttribute('data-src') ){

								// get data-src url
								image.src = image.getAttribute('data-src');
						}

						// get min or max img | window.screen -> size of viewport NOT window
						img.src = ( window.screen.width < 1200 )
						? image.src : image.src.replace('min', 'max');

						// set alt
						img.alt = image.alt;

						// not copy properties from source
						img.removeAttribute('class');
						img.removeAttribute('onclick');
						img.removeAttribute('width');

						// append to container
						container_sl.appendChild(img);

				});
		}
		// end  function add_imgs( Imgs, container_sl );

		// add imgs to container
		add_imgs( Imgs, container_sl );

		// ADD MIN 3 IMAGES
		if( Imgs.length < 3 ){

				// x 2 if 2 imgs = 4 imgs into the slider
				if( Imgs.length == 2 ){

						add_imgs( Imgs, container_sl );
				}

				// add 1 + 1 if one img only present = 3 imgs into the slider
				if( Imgs.length == 1 ){

						add_imgs( Imgs, container_sl );
						add_imgs( Imgs, container_sl );
				}
		}


		// now the container of imgs is well empty

		// replace all urls imgs by MAX src + record indexes
		// + PUT on ARRAY of Promises + set translate css values
    $('#img_cont img').each(function( i, img ){

				// Push to array Promises $.Arr_prom_sl -> ON LOAD event
				// empty array $.ARR_imgs_sl -> { 'W': .. , 'H': .. }
				// for mobile adjustments
				// + launch $.adjust_size_img( width_img, height_img, i );
				// for each iteration
				$.add_prom_sl( 'lambda_'+i+'', $(this).attr('src'), i );

    });
    // end each all imgs


		// when all imgs are loaded
		Promise.all( $.Arr_prom_sl ).then((success) => {


				// For the start re-init. index base img to 0
				// - before adjust height slider
				$.index_base = 0;

				// re-init Arr_prom_sl : [],
				$.Arr_prom_sl = [];

				// set CSS translate for all imgs
				$.apply_style_imgs();

				// remove the loader + fading effect
				$('#slider_loader').fadeOut().remove();

				// show container images + fading effect
				$('#img_cont')
				.css({'visibility': 'visible'}).fadeIn();

				// adjust height of containers -> controls are centered dynamically
				$.adjust_height_slideshow();

				// lanch slider controls
				$.slideshow_controls();

		    // launch TouchSwipe
				$.slideshow_touchSwipe();

		    // SLIDE ON WHEEL
				$.enable_mousewheel_slideshow();

				// add window events
				$.set_window_events();

		});
		// end Promise.all


    // attach mouse grab events
    // grabbing
    $('#img_cont').on('mousedown', function(){

      	$('#img_cont').css({'cursor' : 'grabbing'});
    });
    // un-grab
    $('#img_cont').on('mouseup', function(){

      	$('#img_cont').css({'cursor' : 'grab'});
    });


  },
  /**
   * $.init_slideshow( container );
   */



	/**
	 * $.apply_style_imgs();
	 *
	 * @return {void}  apply JUST z-index + css transform styles
	 */
	apply_style_imgs : function(){

		// remove all class .trans
		// pass all to z-index = -1
		$('#img_cont img')
		.removeClass('trans')
		.css('z-index','-1');


		// loop all images
		$('#img_cont img').each(function( i, img ){


				// set width img
				$(img).css({ 'width': $.ARR_sizes_imgs[i].width+'px' });


				if( $.img_1 == i ){

						$(img).addClass('trans')
						.css({ 'z-index':'1' , 'transform': 'translate(0, 0)' });
				}

				if( $.img_2 == i ){

						if( $.direction == 'right' ){

								$(img).addClass('trans');
						}

						$(img).css({ 'z-index':'1' ,
						'transform': 'translate('+$.ARR_sizes_imgs[$.img_1].width+'px, 0)'  });
				}

				if( $.img_3 == i ){

						if( $.direction == 'left' ){

								$(img).addClass('trans');
						}

						// here translate -distance img_3 width
						$(img).css({ 'z-index':'1' ,
						'transform': 'translate(-'+$.ARR_sizes_imgs[$.img_3].width+'px, 0)' });
				}

				// others imgs
				if( $.img_1 != i
				&& $.img_2 != i
				&& $.img_3 != i ){

						$(img).css({ 'z-index': '-1',
													'transform': 'translate(0, -'+$.out_device+'px)' });
				}

		});
		// end loop imgs

		// adjust width of FIRST img container
		$('#img_cont').css( 'width', $.ARR_sizes_imgs[$.img_1].width+'px' );

	},
	/**
	 * $.apply_style_imgs();
	 */



	/**
	 * $.move_slides( direction );
	 *
	 * @param  {string} 	direction   'right'	/ 'left'
	 * @return {void}     move slider imgs
	 */
	move_slides : function( direction ){


			// pass direction to global property
			$.direction = direction;

			// calcul max index img
			var max_index  = $.length_imgs_sl-1;

      // move right
			if( $.direction == 'right' ){

					$.index_base--;

					if( $.index_base < 0 ){

							$.index_base = max_index;
					}

					// img 1 index
					$.img_1 = $.index_base;

					$.img_2 = ( $.index_base == max_index )
					? 0 : $.index_base+1;

					$.img_3 = ( $.index_base == 0 )
					? max_index	: $.index_base-1;

			}

      // move left
			if( $.direction == 'left' ){

					$.index_base++;

					if( $.index_base > max_index ){

							$.index_base = 0;
					}

					// img 1 index
					$.img_1 = $.index_base;

					$.img_2 = ( $.index_base == max_index )
					? 0 : $.index_base+1;

					// ( $.index_base == max_index ) ? max_index-1 : $.index_base-1
					$.img_3 = ( $.index_base == 0 )
					? max_index : $.index_base-1;

			}
			// end left direction


			// set pagination number
			$('#diapo_counter').text( ($.img_1+1)+'/'+$.length_imgs_sl );

			// loop imgs and apply style
			$.apply_style_imgs();

			// adjust_height_slideshow
			$.adjust_height_slideshow();

			// re-init wheel settings
			$.wheel_value = 0;

			$.old_direction_wheel = null;

			// for not multiple fires ...
			$.diapo_nav_fired = false;

			// re-enable swipe
			$('#img_cont').swipe('enable');

	},
	/**
	 * $.move_slides( direction );
	 */



	/**
	 * $.set_window_events();
	 *
	 * @return {type}  description
	 */
	set_window_events : function(){


		// ON RESIZE - only for slider
		$(window).on('resize.slideshow orientationchange.slideshow', function(event){


				// if slider is visible && window <= 900
				if( $('#render_diapo').length && window.innerWidth <= 900 ){

						// not able to expand on mobile devices
						$('#slider_expand').css('display','none');

						var reduction_factor = 5;

						// re-init zoomed
						$.zoomed = false;
				}

				if( $('#render_diapo').length && window.innerWidth > 900 ){

						// show expand
						$('#slider_expand').css('display','');

						// keep zommed if it is
						var reduction_factor = ( $.zoomed == true )
						? 5 : 20;
				}

				if( $.timer_resize != null ){

						window.clearTimeout( $.timer_resize );
				}

				$.timer_resize = window.setTimeout(
				function(){


						// re-init array $.ARR_sizes_imgs
						$.ARR_sizes_imgs = [];

						// re-init direction
						$.direction = '';

						// loop on $.ARR_imgs_sl -> instrinsic sizes array of imgs
						$.ARR_imgs_sl.forEach((item, i) => {

								$.adjust_size_img( item.W, item.H, i, reduction_factor );
						});

						// clearTimeout
						window.clearTimeout( $.timer_resize );

						// null resize timer
						$.timer_resize = null;

						// set CSS translate for all imgs
						$.apply_style_imgs();

						// adjust height of containers -> controls are centered dynamically
						$.adjust_height_slideshow();

						// lanch slider controls
						$.slideshow_controls();

						// launch touchSwipe
						$.slideshow_touchSwipe();

						// re-enable swipe
						$('#img_cont').swipe('enable');

						// SLIDE ON WHEEL
						$.enable_mousewheel_slideshow();


				}, 1000 );
				// end setTimeout

		});
		// END ON RESIZE


		// ON SCROLL - disable scroll background slideshow
		$(window).on('scroll.slideshow touchmove.slideshow', function(event) {

				// if slider is visible
				if( $('#render_diapo').length != 0  ){

						// if desktop
						if( window.screen.width > 1200 ){

								window.scrollTo(0, 0);
						}
						else{

								$('body').addClass('lock-screen');
						}
				}

		});


	},
	/**
	 * $.set_window_events();
	 */



	/**
	 * $.remove_window_events();
	 *
   * @return {void}  remove some events
	 */
	remove_window_events : function(){

			$(window).off('resize.slideshow orientationchange.slideshow');

			$(window).off('scroll.slideshow touchmove.slideshow');

			$('body').removeClass('lock-screen');

	},
	/**
	 * $.remove_window_events();
	 */


  /**
   * $.close_slideshow();
   *
   * @return {type}  description
   */
  close_slideshow : function(){


		// close slideshow with animation
    $.animateCss('.modal_container', 'zoomOutLeft', function(){

				// re-init horizontal scroll value
				$.scroll_val = 0;

				$.wheel_value = 0;

				$.old_direction_wheel = null;

				$.direction = '';

				$.zoomed = false;

				// re-init $.ARR_sizes_imgs
				$.ARR_sizes_imgs = [];

				// re-init array intrinsic img
				$.ARR_imgs_sl = [];

				// hide modal
        $('#render_diapo').css('display', 'none').remove();

				// remove slideshow event listeners
				$.remove_window_events();

				$('#img_cont').off('wheel.slideshow mousewheel.slideshow DOMMouseScroll.slideshow');
				$('#img_cont').off('mouseenter.slideshow');
				$('#img_cont').off('dblclick.slideshow');

		});
		// end $.animateCss( ... )

  },
  /**
   * $.close_slideshow();
   */


});
// end extend

});
// end jQuery
