/**
 * PlACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 *
 * Script name:	history.js
 *
 * ONPOPSTATE events
 *
 */

////////////////////////////////////////////////////////////////////
////////////         H I S T O R Y         /////////////////////////
////////////////////////////////////////////////////////////////////

// ON POSPSTATE :
window.onpopstate = function(event) {

    if( history.scrollRestoration ){
      history.scrollRestoration = 'manual';
    }

    var Obj_state = history.state;

    // console.log(Obj_state.page);

    // TRAIT. DIFFERENTS PAGE's STATES
    switch( Obj_state.page ) {

      case 'home':
            $.open_home();
      break;

      case 'single_product':
            $.open_product(Obj_state.id, event);
      break;

      case 'cat':
            // Obj_state.id == cat_id
            $.open_a_cat(Obj_state.id, event);
      break;

      case 'cart':
            $.open_payment_form();
      break;

      case 'sale':
            $.open_render_sale();
      break;

      case Obj_state.page :
            // Obj_state.page = page_url
            $.open_static_page( event, Obj_state.page );
      break;


      default:
          // GET HOME
          $.open_home();

    }
    // END SWITCH

};
// END WINDOWS.POSPSTATE
