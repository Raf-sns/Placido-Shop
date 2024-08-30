/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2021-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * script name: messages.js
 *
 * $.get_new_messages();
 * $.show_message( mess_id, event );
 * $.pass_mess_to_readed( mess_id, index );
 * $.ask_to_suppr_mess( mess_id );
 * $.erase_message( mess_id );
 * $.respond_to_message( mess_id );
 *
 */

// start jQuery
$(function(){


// EXTEND ALL METHODS -> Call them width $.myMethod()
// EXTEND ALL OBJECTS -> Call them width $.myOject
$.extend({


  /**
   * $.get_new_messages();
   *
   * @return {type}  description
   */
  get_new_messages : function(){


      // animate spinner
      $('.spinner_messages').addClass('fa-spin');

			var Obj = {
				set : 'get_fresh_messages',
				token : $.o.user.token,
				last_id : $.o.messages[0].mess_id
			};

			$.post('index.php', Obj, function(data){

					// success
          if( data.success ){

							if( data.messages.length == 0 ){

									// info no new messages
									$.show_alert('info', $.o.tr.empty_messages, false);

                  // stop spinner
                  $('.spinner_messages').removeClass('fa-spin');
                  
									return; // stop here
							}

							// insert at start index new messages
							data.messages.forEach((item, i) => {

									$.o.messages.unshift( item );
							});

							// nb total messages
							$.o.template.nb_messages = $.o.messages.length;
							$('.nb_messages').text( $.o.messages.length );

							// add nb messges not readed
							$.o.template.count_not_read += data.count_not_read;

							// if have new messages - manage count_not_read in view
							if( $.o.template.count_not_read != 0 ){

								var render_mess =  $.o.tr.you_have+`
								`+$.o.template.count_not_read+`
								`+$.o.tr.mess_not_read;

								// show a render alert nb mess not read
								$.show_alert('info', render_mess, false);

								$('.nb_new_mess').text( $.o.template.count_not_read );

								$('.nb_new_mess_container').css('display','inline-block');
							}

          } // error
          else{

              // error
              $.show_alert('warning', data.error, false);
          }

          // stop spinner
          $('.spinner_messages').removeClass('fa-spin');

			}, 'json');

   },
  /**
   * $.get_new_messages();
   */



  /**
   * $.show_message( mess_id, event );
   *
   * @param  {int} 		mess_id
   * @param  {event} 	event
   * @return {html}   deploy message and check if is read / not readed
   */
  current_id_mess : false,
  show_message : function( mess_id, event ){


      // hide all hidden mess was show
      $('.hidden_mess').hide();

      // all icons by default
      $('#messages i.fa-hand-point-down')
      .addClass('fa-hand-point-right').removeClass('fa-hand-point-down');

			// all buttons text by default
			$('#messages span.ico_mess_rep').text( $.o.tr.show );

      // manage open/close same message or open an another message
      if( $.current_id_mess !== mess_id ){

          // keep current id for close item already opened
          $.current_id_mess = mess_id;

          // manage icon
          $('#info_show-'+mess_id+'').prev('i.ico_mess_rep')
          .addClass('fa-hand-point-down').removeClass('fa-hand-point-right');

					// manage text icon
					$('#info_show-'+mess_id+'').text( $.o.tr.hide );

      }
      else if( $.current_id_mess == mess_id ){

          // if same item hide hidden div
          $('#mess-'+mess_id+'').hide();

          $.current_id_mess = false;

          // manage icon
          $('#info_show-'+mess_id+'').prev('i.ico_mess_rep')
          .addClass('fa-hand-point-right').removeClass('fa-hand-point-down');

					// manage text icon
					$('#info_show-'+mess_id+'').text( $.o.tr.show );

          return; // close message - stop here
      }


      // test if read/not readed
      for (var i = 0; i < $.o.messages.length; i++) {

          if( $.o.messages[i].mess_id == mess_id ){

              if( $.o.messages[i].readed == false ){

                  // pass index too
                  $.pass_mess_to_readed(mess_id, i);
              }

              break;
          }

      }
      // end for


			// scroll to top of message
			$.scroll_to_elem('#box_mess-'+mess_id+'', event);

			// show hidden message
      $('#mess-'+mess_id+'').show('slow');

  },
  /**
   * $.show_message( mess_id, event );
   */



  /**
   * $.pass_mess_to_readed( mess_id, index );
   *
   * @param  {int} mess_id  id of message to pass readed state
   * @param  {int} index  index of message to pass readed state
   * @return {type}         description
   */
  pass_mess_to_readed : function( mess_id, i ){


      // create form data for AJAX POST
      var datas = new FormData();

      // append command
      datas.append('set', 'pass_mess_to_readed');

      // append mess_id
      datas.append('mess_id', mess_id);

      // append user
      datas.append('token', $.o.user.token);

      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          // SUCCESS
          if(data.success){

              // update state icon + text to readed
              $('#render_state-'+mess_id+'')
              .html(`
                <i class="fa-check fas text-lime large"></i>&nbsp; `+$.o.tr.readed+`
              `);

              // template top render new messages
              $.o.template.count_not_read--;

              if( $.o.template.count_not_read == 0 ){

                	$('.nb_new_mess_container').css('display','none');
              }
              else{

                	$('.nb_new_mess').text($.o.template.count_not_read);
              }

              // Pass mess readed
              $.o.messages[i].readed = true;

          }
          // END SUCCESS

          // ERROR
          if(data.error){

              $.show_alert('warning', data.error, true);
          }

      });
      // END AJAX

  },
  /**
   * $.pass_mess_to_readed( mess_id, index );
   */



  /**
   * $.ask_to_suppr_mess( mess_id );
   *
   * @param  {int}      mess_id
   * @return {html}     ask to confirm suppr. new sale
   */
  ask_to_suppr_mess : function( mess_id ){


      var html = $.o.tr.confirm_suppr_message+`<br><br>
      <span class="btn deep-orange card round left"
      onclick="$.erase_message(`+mess_id+`);">
      <i class="fa-trash-alt far"></i>&nbsp; `+$.o.tr.suppr+`</span>
      <span class="btn dark-gray card round right"
      onclick="$.show_alert(false);">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr.abort+`</span>`;

      $.show_alert('info', html, true);

  },
  /**
   * $.ask_to_suppr_mess( mess_id );
   */



  /**
   * $.erase_message( mess_id );
   *
   * @param  {type} mess_id description
   * @return {type}         description
   */
  erase_message : function( mess_id ){


      // create form data for AJAX POST
      var datas = new FormData();

      // append command
      datas.append('set', 'erase_message');

      // append token
      datas.append('token', $.o.user.token);

      // append mess_id
      datas.append('mess_id', mess_id);


      var el_to_prevent = false;
      var method = 'POST';
      var url = 'index.php';
      var data_type = 'json';

      $.sender(el_to_prevent, method, url, datas, data_type, function(data){

          if(data.success){

              $.show_alert('success', data.success, false);

              $('#box_mess-'+mess_id+'').remove();

              // update obj
              for (var i = 0; i < $.o.messages.length; i++) {

                  if( $.o.messages[i].mess_id == mess_id ){

											// remove message deleted
                      $.o.messages.splice(i, 1);

                      break;
                  }

              }
              // end for

							// decrem template.nb_messages
							$.o.template.nb_messages = $.o.messages.length;
							$('.nb_messages').text( $.o.messages.length );

              // IF NO MORE MESSAGES
              if( $.o.messages.length == 0 ){

                  $('#messages ul').html(`
                    <li>
                      <p class="large center">
                        `+$.o.tr.empty_messages+`
                      </p>
                    </li>`);
              }

          }
          // end success

          if(data.error){

              $.show_alert('warning', data.error, false);
          }

      });
      // END SENDER

  },
  /**
   * $.erase_message( mess_id );
   */



  /**
   * $.respond_to_message( mess_id );
   *
   * @return {type}  description
   */
  respond_to_message : function( mess_id ){

      // get message
      for (var i = 0; i < $.o.messages.length; i++) {

          if( $.o.messages[i].mess_id == mess_id ){

              // make an object selected for template
              $.o.mess_selected = $.o.messages[i];
              break;
          }
      }

      // imbibe modal with form send mail
      $('#modal_content').empty()
      .mustache('send_mail_contact', $.o );

      $('#modal').show();

  },
  /**
   * $.respond_to_message( mess_id );
   */


});
// END EXTEND

});
// END JQUERY
