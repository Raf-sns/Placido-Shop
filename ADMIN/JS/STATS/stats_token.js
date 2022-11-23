/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: stats_token.js
 *
 * deploy_form_token_stats();
 * record_stats_token_api(event);
 *
 * this need jQuery
 *
 */



	/**
	 * deploy_form_token_stats();
	 *
	 * @return {void}  show form token
	 */
	function deploy_form_token_stats(){


			$('#token_input').toggle(['hide', 'show']);


			if( $('#deploy_form_token_stats i').hasClass('fa-hand-point-right') == true ){

					$('#deploy_form_token_stats i')
					.addClass('fa-hand-point-down')
					.removeClass('fa-hand-point-right');
			}
			else{

					$('#deploy_form_token_stats i')
					.addClass('fa-hand-point-right')
					.removeClass('fa-hand-point-down');
			}

	}
	/**
	 * deploy_form_token_stats();
	 */



	/**
	 *	record_stats_token_api(event);
	 *
	 * @param  {event} 						event
	 * @return {error/success}    record stats token API
	 */
	function record_stats_token_api(event){


			event.preventDefault();

			// disable button
			$('#record_stats_token').removeAttr('onclick');

			$.post('index.php',
			{
				set: 'record_stats_token',
				token: $.o.user.token,
				token_api: $('#token_api').val()
			},
			function(data){

					// success
					if( data.success ){

							$.show_alert( 'success', data.success, false );

							// reset input
							$('#token_api').val('');

							// close form
							deploy_form_token_stats();

							// state token
							if( typeof data.token_api != 'undefined' ){

									// set token api
									$.o.stats.token_api = data.token_api;

									if( $.o.stats.token_api == true ){

											$('.state_token_api').removeClass('text-amber')
											.addClass('text-light-green')
											.html('(&nbsp;'+$.o.tr.stats_token_api_recorded+'&nbsp;)');
									}
									if( $.o.stats.token_api == false ){

											$('.state_token_api').removeClass('text-light-green')
											.addClass('text-amber')
											.html('(&nbsp;'+$.o.tr.empty_stats_token_api+'&nbsp;)');
									}
							}
							// end  state token

					}
					// end success

					// error
					if( data.error ){

							$.show_alert( 'warning', data.error, false );
					}

					// enable button
					$('#record_stats_token').attr('onclick', 'record_stats_token_api(event);');

			},'json');

	}
	/**
	 *	record_stats_token_api(event);
	 */
