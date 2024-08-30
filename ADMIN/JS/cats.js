/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: cats.js
 *
 * Organize categories:
 *
 * $.deploy_cat( cat_id, event );
 * $.deploy_ALL_cats( event );
 * $.render_info_depla_cat( cat_id );
 * $.move_CAT( event, cat_id );
 * $.move_obj( event, cible_id , where );
 *
 * $.insert_new_cat( 'abort' );
 * $.confirm_suppr_cat( cat_name, cat_id );
 * $.set_cat( context, id );
 *
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({



  /**
   * $.deploy_cat( cat_id, event );
   * deploy sub cats
   */
  deploy_cat: function( cat_id, e ){

      e.stopImmediatePropagation();

      if( $('#deploy_'+cat_id+'').hasClass('off') ){

        $('#deploy_'+cat_id+'').removeClass('off').addClass('in');

        $('#cat_icon-'+cat_id+'')
        .css('transform', 'rotate(90deg)');

      }
      else{

        $('#deploy_'+cat_id+'')
        .addClass('off').removeClass('in');

        $('#deploy_'+cat_id+' ul.deploy')
        .addClass('off').removeClass('in');

        $('#cat_icon-'+cat_id+'')
        .css('transform', 'rotate(0deg)');

        $('#deploy_'+cat_id+' i.cat_icon')
        .css('transform', 'rotate(0deg)');
      }

  },
  /**
   * $.deploy_cat( cat_id, event );
   */


  /**
   * $.deploy_ALL_cats(event);
   * deploy all
   */
   cats_ALL_deployed : false,
   deploy_ALL_cats : function( e ){

			e.preventDefault();
      e.stopImmediatePropagation();

      // clear field modif evenlty
      $.clear_field_cats( event );

      if( $.cats_ALL_deployed == false ){

        $('i.deploy_all')
        .css('transform', 'rotate(90deg)');

        $('.deploy').removeClass('off').addClass('in');

        $('.cat_icon')
        .css('transform', 'rotate(90deg)');

        $.cats_ALL_deployed = true;

      }
      else{

        $('i.deploy_all')
        .css('transform', 'rotate(0deg)');

        $('.deploy').removeClass('in').addClass('off');

        $('.cat_icon')
        .css('transform', 'rotate(0deg)');

        $.cats_ALL_deployed = false;

      }

  },
  /**
   * $.deploy_ALL_cats(event);
   */


  /**
   * $.render_info_depla_cat( cat_id );
   *
   * @param  {type} cat_id description
   * @return {type}        description
   */
  render_info_depla_cat : function( cat_id ){

      for (var i = 0; i < $.o.cats.length; i++) {

          if( $.o.cats[i].cat_id == cat_id ){

              // get info title cat deplaced
              $('#render_info_depla_cat')
              .html(`<span class="text-amber">
                <i class="fa-hand-point-right far"></i>&nbsp;
                `+$.o.tr['move_cat']+` `+$.o.cats[i].title+`</span>`)
              .addClass('sticked_info_depla_cat');

              // stop here
              break;
          }
      }

  },
  /**
   * $.render_info_depla_cat( cat_id );
   *
   */


  /**
   * $.move_CAT( event, cat_id );
   *
   */
   move_CAT_inter : false,
   Obj_to_move : {},
   move_CAT: function( e, cat_id ){

      e.stopImmediatePropagation();

      if( $.move_CAT_inter == false ){

          // need to keep id of object moved
          $.Obj_to_move.cat_id = cat_id;

          $.move_CAT_inter = true;

					// empty field #new_cat
					$.clear_field_cats( e );

          // render info cat moved
          $.render_info_depla_cat( cat_id );

          // hide settings_board
          $('.settings_board').css({'display':'none'});

          // show moving board
          $('.move_board').not('#cat_id-'+cat_id+' .move_board').css({'display':'block'});

          // colorize title cat to move
          $('#cat_id-'+cat_id+'').addClass('amber').removeClass('gray');

          // scroll to element - we add blocks and page automatic scroll ...
          // $.scroll_to_elem('#cat_id-'+cat_id+'', event);

					$('html,body')
					.animate( { scrollTop : $('#cat_id-'+cat_id+'').offset().top-60 }, 400 );

      }
      else {

          $.Obj_to_move.cat_id = null;

          $.move_CAT_inter = false;

          // hide moving board
          $('.move_board').css({'display':'none'});

          // show settings_board
          $('.settings_board').css({'display':'block'});

          // empty render info cat moved
          $('#render_info_depla_cat')
          .removeClass('sticked_info_depla_cat').empty();

          $('#cats div').removeClass('amber');

      }


  },
  /**
   * $.move_CAT( event, cat_id );
   */


  /**
   * $.move_obj( event, cible_id , where );
   * where -> 'after',  'before', 'inside'
   * move one leaf or a node ANYWHERE !
   */
  move_obj: function( e, cible_id , where ){

      e.stopImmediatePropagation();

      var cat_id = $.Obj_to_move.cat_id;

      var ARR_cats = { cat:{}, cat_cible:{} };

      for (var i = 0; i < $.o.cats.length; i++) {

          // get obj cat origin
          if( $.o.cats[i].cat_id == cat_id ){
            ARR_cats.cat = $.o.cats[i];
          }

          // get obj cat cible
          if( $.o.cats[i].cat_id == cible_id ){
            ARR_cats.cat_cible = $.o.cats[i];
          }

      }
      // end for

      // console.log( ARR_cats );

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      // create form data for AJAX POST
      var datas = new FormData();
      // append command
      datas.append('set', 'move_cat');
      datas.append('token', $.o.user.token );
      datas.append('ARR_cats', JSON.stringify(ARR_cats) );
      datas.append('where', where );


      $('#render_process').animate({width: '100%'}, 1000);

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          $('#render_process').stop(true).css({'width': '0%'});

          // success
          if( data.success ){

              // re-init obj. with fresh datas
              $.o.cats_html = data.cats_html;
              $.o.cats = data.cats;

							// pass watcher to false
							$.move_CAT_inter = false;

							// pass watcher to false
							$.cats_ALL_deployed = false;

              // re-open vue -> html update
              $.open_vue('categories', event);

              // alert success
              $.show_alert('success', $.o.tr.update_success, false);


          } // error
          else{

              // error
              $.show_alert('warning', data.error, false);

          }

      });
      // end sender

  },
  /**
   * $.move_obj( event, cible_id , where );
   */


  /**
   * $.clear_field_cats( event );
   *
   * @return {void}
   */
  clear_field_cats : function( e ){

      e.preventDefault();
      e.stopImmediatePropagation();

      // modifie label input cat
      $('#label_input_cat')
			.html(`<i class="fas fa-plus"></i>&nbsp; `+$.o.tr.record_new_cat);

      // re-attr onclick insert new cat by default
      $('#btn_record_cat')
      .off('click')
      .attr('onclick', '$.insert_new_cat()' );

      // empty field
      $('#new_cat').val('');

  },
  /**
   * $.clear_field_cats();
   *
   */


  /**
   *  $.insert_new_cat();
   *  if 'abort' clear input new cat
   *
   */
  insert_new_cat : function(){


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      var datas = new FormData();
      // append command
      datas.append('set', 'insert_new_cat');

      // new cat value
      datas.append('title', $('#new_cat').val() );

      // token
      datas.append('token', $.o.user.token );

     // sender send datas to server asynchronous and return data.obj
     $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.o.cats_html = data.cats_html;
              $.o.cats = data.cats;

              $.cats_ALL_deployed = false;

              $.open_vue('categories', event);

          }
          else{

              // error
              $.show_alert('warning', data.error, false);
          }

       });
       // end sender


  },
  /**
   *  END $.insert_new_cat('abort');
   */



  /**
   * $.modif_cat( event, cat_id );
   *
   * @param  {int} cat_id
   * @return {type}        description
   */
  modif_cat : function( e, cat_id ){

      e.stopImmediatePropagation();

      // find cat
      for (var i = 0; i < $.o.cats.length; i++) {
        if( $.o.cats[i].cat_id == cat_id ){
          break;
        }
      }

      $('#btn_record_cat')
      .off('click')
      .removeAttr('onclick')
      .on('click', (e) => $.set_cat(  e, 'modif', $.o.cats[i].cat_id ) );

      // modifie label input cat
      $('#label_input_cat').html(`<i class="cat_icon fa-cogs fas"></i>&nbsp; `
        +$.o.tr.modify+` `+$.o.cats[i].title
      );

      // append old value in field
      $('#new_cat').val( $.o.cats[i].title );

      // scroll to top
      $.scroll_top();


  },
  /**
   * $.modif_cat( event, cat_id );
   */


  /**
   * $.ask_to_suppr_cat( e, cat_id );
   *
   * @param  {type} e     description
   * @param  {type} cat_id description
   * @return {type}       description
   */
  ask_to_suppr_cat : function( e, cat_id ){

      e.stopImmediatePropagation();

      // find cat
      for (var i = 0; i < $.o.cats.length; i++) {
        if( $.o.cats[i].cat_id == cat_id ){
          break;
        }
      }

      // test if cat is a node
      var warning_node = '';
      if( ($.o.cats[i].br - $.o.cats[i].bl) > 1 ){

          warning_node = `<br>`+$.o.tr.warning_node;
      }

      var html = `<p>`+$.o.tr.confirm_suppr_cat+`
      <br>`+$.o.cats[i].title+`
      `+warning_node+`
      </p>
      <br>
      <div>
      <span onclick="$.set_cat( event, 'suppr', `+$.o.cats[i].cat_id+` );"
      class="btn deep-orange card round margin-right left">
      <i class="far fa-trash-alt"></i>&nbsp; `+$.o.tr.suppr+`</span>

      <span onclick="$.show_alert(false);"
      class="btn dark-gray card round right">
      <i class="fas fa-ban"></i>&nbsp; `+$.o.tr.abort+`</span>
      </div>
      `;

      $.show_alert('info', html, true);

  },
  /**
   * $.ask_to_suppr_cat( e, index );
   */


  /**
   * $.set_cat( event, context, cat_id );
   * context 'modif'|'suppr'
   *
   */
  set_cat : function( e, context, cat_id ){

      e.preventDefault();

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      var datas = new FormData();
      // append command
      datas.append('set', 'set_cat');

      // context
      if( context == 'modif' ){

          // context user
          datas.append('context', 'modif');
      }
      else{

          // context user
          datas.append('context', 'suppr');
      }
      // end context

      datas.append('token', $.o.user.token);
      // cat value modified
      datas.append('title', $('#new_cat').val() );
      datas.append('cat_id', cat_id);

      // sender send datas to server asynchronous and return data.obj
      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // success
          if( data.success ){

              $.o.cats_html = data.cats_html;
              $.o.cats = data.cats;

              $.cats_ALL_deployed = false;

							// pass watcher to false
							$.move_CAT_inter = false;

							$.show_alert('success', $.o.tr.update_success, false);

							$.open_vue('categories', event);

          }
          else{

              // error
              $.show_alert('warning', data.error, false);
          }

      });
      // end sender


  },
  /**
   * $.set_cat( context, id );
   */





});
// END EXTEND

});
// END jQuery
