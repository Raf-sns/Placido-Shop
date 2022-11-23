/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * script name: stats_date.js
 *
 * set_obj_date_now( date_string );
 * getDateOfISOWeek( w, y );
 * set_month( date_string, value );
 * set_year( year_string, value );
 *
 ** var Sort_date = { now : // object,  obj : // object };
 * Sort_date.set_date( date_string );
 * Sort_date.get_full_string();
 * Sort_date.get_year();
 * Sort_date.get_month();
 * Sort_date.get_day();
 * Sort_date.get_week();
 * re_init_date();
 * date_overflow_test();
 *
 */


	/**
	 * set_obj_date_now( date_string );
	 *
	 * @param  {type} date_string optionnal - if exist set date form string / get date now
	 * @return {object}           return Date Object witout TimeZone offset
	 */
	function set_obj_date_now( date_string ){


			let date_now = ( !date_string ) ? new Date() : new Date(date_string);

			date_now.setHours(0, 0, 0);

			return date_now;

			// offset test  ... but js return date/hour locally
			// const offset = date_now.getTimezoneOffset();
			//
			// let Da = new Date( date_now.getTime() - (offset*60*1000) );
			//
			// Da.setHours(0, 0, 0); // return stamp in int. in milliseconds
			//
			// return new Date( Da ); // return Date objet at UTC time at 00:00:00
	}
	/**
	 * set_obj_date_now();
	 */


	/**
	 * getDateOfISOWeek( w, y );
	 *
	 * @param  {type} 	w 	number of week // works in str. '22' or in int. 22
	 * @param  {type} 	y 	number of year // works in str. '2022' or in int. 2022
	 * @return {object}   	Date object at the first monday of the week
	 */
	function getDateOfISOWeek(w, y) {

			// week overflow
			if( w > 52 ){
				w = 1;
				y += 1;
			}
			// week under 1
			if( w < 1 ){
				w = 52;
				y -= 1;
			}

			let simple = new Date(y, 0, 1 + (w - 1) * 7);

			let dow = simple.getDay();

			let ISOweekStart = simple;

			if( dow <= 4 ){

				ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
			}
			else{

				ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
			}

			// set at 00:00:00 for odate overflow test
			ISOweekStart.setHours(0, 0, 0);

			return ISOweekStart;
	}
	/**
	 * getDateOfISOWeek( w, y );
	 */


	/**
	 * set_month( date_string, value );
	 *
	 * @param  {string} date_string 'yyyy-mm-dd'
	 * @param  {int} 		value       operation : -/+1
	 * @return {object} 						return a new date object
	 */
	function set_month( date_string, value ) {

			var date = set_obj_date_now( date_string ); // return object UTC + Time: 00:00:00

			// set obj day to the first day of the month
			date.setDate(1);

			// add or remove a month
			date.setMonth(date.getMonth()+value);

			return date;
	}
	/**
	 * set_month( date_string, value );
	 */


	/**
	 * set_year( year_string, value );
	 *
	 * @param  {string} year_string 'yyyy'
	 * @param  {int} 		value       operation : -/+1
	 * @return {object} 						return a new date object
	 */
	function set_year( year_string, value ) {

			var date = set_obj_date_now( year_string ); // return object UTC + Time: 00:00:00

			// set obj day to the first day of the month
			date.setDate(1);

			// set month to first month
			date.setMonth(0);

			// remove OR add one year
			date.setFullYear(date.getFullYear() + value);

			return date;
	}
	/**
	 * set_year( year_string, value );
	 */


	// make a date OBJECT to navigate for sort stats
	// Sort_date object is directly accessible no need to instantiate
	var Sort_date = {

			now : set_obj_date_now(), // object
			obj : set_obj_date_now() // object

	};
	// end Sort_date


	////   ADD FUNCTIONS TO Sort_date   ////

	/**
	 * Sort_date.set_date( date_string );
	 *
	 * @return {object}   modify Sort_date.obj by date string ex. '2022-05-24'
	 */
	Sort_date.set_date = function( date_string ){

			// set obj to date
			Sort_date.obj = new Date( date_string );

	}
	/**
	 * Sort_date.set_date();
	 */


	/**
	 * Sort_date.get_full_string();
	 *
	 * @param  {object} 		Sort_date
	 * @return {string}     year in string 'yyyy-mm-dd'
	 */
	Sort_date.get_full_string = function(){

			let date_now = new Date( this.obj );

			const offset = date_now.getTimezoneOffset();

			date_now = new Date( date_now.getTime() - (offset*60*1000) );

			return date_now.toISOString().split('T')[0];
	}
	/**
	 * Sort_date.get_full_string();
	 */


	/**
	 * Sort_date.get_year();
	 *
	 * @param  {object} 		Sort_date
	 * @return {string}     year in string 'yyyy'
	 */
	Sort_date.get_year = function(){

			return this.obj.getFullYear().toString();
	}
	/**
	 * Sort_date.get_year();
	 */


	/**
	 * Sort_date.get_month();
	 *
	 * @return {string}  return month in string ex. '05' for May
	 */
	Sort_date.get_month = function(){

			let month = this.obj.getMonth()+1;

			return ( month < 10 ) ? '0'+month.toString() : month.toString();
	}
	/**
	 * Sort_date.get_month();
	 */


	/**
	 * Sort_date.get_day();
	 *
	 * @return {string} return day number in string 0-filled ex. '05'
	 */
	Sort_date.get_day = function(){

			var day = this.obj.getDate();

			return ( day < 10 ) ? '0'+day.toString() : day.toString();

	}
	/**
	 * Sort_date.get_day();
	 */


	/**
	 * Sort_date.get_week();
	 *
	 * @return {int}     INT. week number for an object date
	 */
	Sort_date.get_week = function(){


			let date = new Date( Sort_date.get_full_string() );

			// ISO week date weeks start on Monday, so correct the day number
			var nDay = (date.getDay() + 6) % 7;

			// ISO 8601 states that week 1 is the week with the first Thursday of that year
			// Set the target date to the Thursday in the target week
			date.setDate(date.getDate() - nDay + 3);

			// Store the millisecond value of the target date
			var n1stThursday = date.valueOf();

			// Set the target to the first Thursday of the year
			// First, set the target to January 1st
			date.setMonth(0, 1);

			// Not a Thursday? Correct the date to the next Thursday
			if (date.getDay() !== 4) {
				date.setMonth(0, 1 + ((4 - date.getDay()) + 7) % 7);
			}

			// The week number is the number of weeks between the first Thursday of the year
			// and the Thursday in the target week (604800000 = 7 * 24 * 3600 * 1000)
			let response = 1 + Math.ceil((n1stThursday - date) / 604800000);

			// return in string
			return response.toString();

	}
	/**
	 * Sort_date.get_week();
	 */


	/**
	 * re_init_date();
	 *
	 * @return {object}  Sort_date.obj  - just re-init Sort_date.obj at today
	 */
	function re_init_date(){

			// select period year, day, ...
			let input = document.getElementById('sort_stats');

			// set the period key today -> 'd'
			input.value = 'd';

			// re_init_date at TODAY
			Sort_date.obj = set_obj_date_now();

	}
	/**
	 * re_init_date();
	 */



	/**
	 * date_overflow_test();
	 *
	 * @return {bool}   true  / false
	 * i. if false -> re-init Sort_date.obj AT TODAY - 00:00:00
	 */
	function date_overflow_test(){


			// test in new date setted is bigger than date today
			if( Sort_date.obj.getTime() > Sort_date.now.getTime() ){

					// re-init Sort_date.obj AT TODAY - 00:00:00
					Sort_date.obj = set_obj_date_now();

					return false;
			}

			return true;
	}
	/**
	 * date_overflow_test();
	 */

////  end  ADD FUNCTIONS TO Sort_date   ////
