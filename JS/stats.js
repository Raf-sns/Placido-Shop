/**
 * PlACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 *
 * Script name:	stats.js
 *
 * set_stat();
 * $(document).on('mouseenter.stats touchstart.stats', function(){ ... } );
 *
 * Extended :
 *
 * $.record_stat_for_one_product( prod_id );
 * $.record_stats_from_cart( prod_id, title, command );
 *
 */

$(function(){


	/**
	 * set_stat();
	 *
	 * @return {void}  send a request to server for produce statistics
	 */
	function set_stat(){

		$.post('/', { set:'stat', lang: navigator.language });
	}
	/**
	 * set_stat();
	 */

	// test - send always a stat
	// set_stat()


	/**
	 * document.on('mouseenter.stats touchstart.stats', fn() )
	 *
	 * @param  {events} 	'mouseenter.stats touchstart.stats'
	 * @param  {callback} function()
	 * @return {void}     detect when a user interacts with the window
	 * -> launch a request to the server for produce statistics
	 */
	$(document).on('mouseenter.stats touchstart.stats', function(){


			// 30 sec. for demo 30*1000 and comment $this.off line  / 4*60*60*1000
			var time_to_watch = 4*60*60*1000; // 4 hours in prod
			var d = new Date();
			var stamp = d.getTime(); // stamp in millisec.

			// console.log('Human authentified !');

			// IF LOCAL STORAGE DONT EXIST - NEW USER
			if( !sessionStorage.getItem('stamp') ){

					// store stamp
					sessionStorage.setItem('stamp', stamp);

					set_stat(); // LANCH STAT

					// console.log('First storage :'+localStorage.getItem('stamp'));

			}
			else{

					// IF STORAGE STAMP EXIST
					var old_stamp_in_storage = sessionStorage.getItem('stamp');
					stamp_in_storage = parseInt(old_stamp_in_storage, 10);


					// IF TIME TO WATCH IS PASSED CONSIDER USER NEW USER
					if( (stamp - stamp_in_storage)  >  time_to_watch ){

							// console.log('NEW human authentified !');
							// store his new stamp in Storage
							sessionStorage.setItem('stamp', stamp);

							set_stat(); // LANCH STAT

							// console.log('New storage :'+sessionStorage.getItem('stamp'));

					}

			}
			// END IF LOCAL STORAGE

			// COMMENT TO SHOW HARD DETECT FUNCTION DEMO
			$(document).off('mouseenter.stats touchstart.stats');

	});
	/**
	 * end document.on('mouseenter.stats touchstart.stats', fn() )
	 */


	$.extend({

		/**
		* $.record_stat_for_one_product( prod_id );
		*
		* @return {void}
		*/
		record_stat_for_one_product : function( prod_id ){

				$.post('/',{
					set : 'record_stat_for_one_product',
					prod_id : prod_id
				});

		},
		/**
		* $.record_stat_for_one_product( prod_id );
		*/



		/**
		 * $.record_stats_from_cart( prod_id, command );
		 *
		 * @param  {array} prod_id 		product id
		 * @param  {array} command 		'add' or 'remove'
		 * @return {void}       			record a new entry for stats or remove
		 */
		token_stats : '',
		record_stats_from_cart : function( prod_id, command ){

				// send a simple request to add or remove product to stats
				$.post('/', {
					set: 'record_stat_from_cart',
					prod_id : prod_id,
					command : command,
					token : $.token_stats
				},function(data){

						$.token_stats = data.token_stats;
						// console.log($.token_stats);

				},'json');

		},
		/**
		 * $.record_stats_from_cart( prod_id, command );
		 */


	});
	// end extend

});
// END jQuery
