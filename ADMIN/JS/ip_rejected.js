/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: ip_rejected.js
 *
 * $.get_ip_rejected();
 * $.ask_to_unban_ip( ip );
 * $.unban_ip( ip );
 *
 */

// start jQuery
$(function(){

// EXTEND ALL METHODS -> Call them width $.myMethod()
$.extend({



	/**
	 * $.get_ip_rejected();
	 *
	 * @return {json}  ask server for rejected ips
	 */
	get_ip_rejected : function(){

		// show process bar
		$.show_process();

		$.post('index.php',
			{ set: 'get_ip_rejected',
			  token: $.o.user.token },
			function(data){

				if( data.success ){

						// hide process bar
						$.show_process('end');

						// make or renew ip_rejected obj
						$.o.ip_rejected = {
							last_check : data.last_check,
							rejected : data.rejected,
							nb_rejected : data.nb_rejected
						};

						// console.log( $.o.ip_rejected );

						// re-init last check in view
						$('#ip_last_check').text( $.o.ip_rejected.last_check );

						// re-init #rejected_list
						$('#rejected_list').empty()
						.mustache('rejected_list', $.o );

				}
				// end success

			}, 'json');
			// end post

	},
	/**
	 * $.get_ip_rejected();
	 */



  /**
   * $.ask_to_unban_ip( ip );
   *
   * @param  {string}   ip
   * @return {html}     ask to confirm unban I.P.
   */
  ask_to_unban_ip : function( ip ){


      var html =
			`<p>`+$.o.tr.confirm_unban_ip+`</p>
      <button id="btn_unban" class="btn gree card round left"
      onclick="$.unban_ip('`+ip+`');" role="button">
      <i class="fa-sign-in-alt fas"></i>&nbsp; `+$.o.tr.validate+`</button>

      <button class="btn dark-gray card round right"
      onclick="$.show_alert(false);" role="button">
      <i class="fa-ban fas"></i>&nbsp; `+$.o.tr.abort+`</button>`;

      $.show_alert('info', html, true);

  },
  /**
   * $.ask_to_unban_ip( ip );
   */



	/**
	 *  $.unban_ip( ip );
	 *
	 * @param  {string} ip 	ip address to erase from banned
	 * @return {json}    new array obj ip_rejected
	 */
	unban_ip : function( ip ){

		// disable button
		$('#btn_unban').prop('disabled', true);

		// show process bar
		$.show_process();

		$.post('index.php',
			{ set: 'unban_ip',
				ip: ip,
			  token: $.o.user.token },
			function(data){

				if( data.success ){

						// hide process bar
						$.show_process('end');

						// renew ip_rejected obj
						$.o.ip_rejected = {
							last_check : data.last_check,
							rejected : data.rejected,
							nb_rejected : data.nb_rejected
						};

						// console.log( $.o.ip_rejected );

						// success
						$.show_alert('success', $.o.tr.success_unban_ip, false);

						// re-init last check in view
						$('#ip_last_check').text( $.o.ip_rejected.last_check );

						// re-init #rejected_list
						$('#rejected_list').empty()
						.mustache('rejected_list', $.o );

						// hide tag nb_rejected
						if( $.o.ip_rejected.nb_rejected == 0  ){

								$('.nb_rejected_container').css('display', 'none');
						}
						else{
								// else adjust number 
								$('.nb_rejected').text( $.o.ip_rejected.nb_rejected );
						}

				}
				// end success

				// error
				if( data.error ){

						// error
						$.show_alert('warning', data.error, false);
				}

			}, 'json');
			// end post

	},
	/**
	 *  $.unban_ip( ip );
	 */


});
// end extend

});
// end jQuery
