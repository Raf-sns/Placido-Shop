/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello, 2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	server_worker.js
 *
 * async function postDatas( request );
 *
 */

// Web Worker : message recived
onmessage = function(event){


    // make a FormData to send at the server
    let Request = new FormData();

    switch( event.data ){

        case 'get_templates':
          Request.append('set', 'get_templates');
        break;

        case 'get_object_api':
          Request.append('set', 'get_obj');
          Request.append('req', 'ajax');
        break;
    }
    // end switch

    // launch POST function
    postDatas(Request);

}
// end Web Worker : message recived


/**
 * async postDatas( request );
 *
 * @param  {object} Request FormData Request
 * @return {json}   postMessage() to templates_loader.js
 */
async function postDatas( Request ){

  try {

      const response = await fetch('/', {
        method: 'POST',
        mode: 'same-origin', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        body: Request
      });

      // error Network
      if( !response.ok ){

          throw new Error("Network response was not OK");
      }

      const result = await response.json();

      // error API
      if( result.error ){

          console.error("Error:", result.error);
      }

      // success
      // Web Worker reply
      postMessage(result);

  }
  catch (error){

      // error
      console.error("Error: ", error);
  }

}
/**
 * END async postDatas( request );
 */
