/**
 * PLACIDO-SHOP FRAMEWORK - JS FRONT
 * Copyright © Raphaël Castello , 2021-2024
 * Organisation: SNS - Web et informatique
 * Website/contact: https://sns.pm
 *
 * Script name:	pwa.js
 *
 * Progessive Web App (PWA) - FRONT
 * see https://developer.mozilla.org/fr/docs/Web/Progressive_web_apps/
 */

  // Register service worker to control making site work offline
  // AND permited to Install the web site like native APP
  if( 'serviceWorker' in navigator ){

      navigator.serviceWorker.register('worker.js')
	      .then(() => {

	          // console.log('Service Worker Registered');
	      });
  }

  // INSTALL APPLICATION - MANUAL INSTALLATION BY EVENT
  // Code to handle install prompt()
  let deferredPrompt;

  // capture event before install APP
  window.addEventListener('beforeinstallprompt', (e) => {

    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();

    // Stash the event so it can be triggered later.
    deferredPrompt = e;

  }, false);
  // INSTALL APPLICATION - MANUAL INSTALLATION BY EVENT


  /**
   * function install_app();
   *
   * install website as an application on the device of the user
   * this is a Progressive Web Application (PWA) installer
   * Fire on event
   * @return {application}
   * Note: Lots of parts of this code is from the Mozilla Javascipt documention
   */
  function install_app(){


      // close menu
      $.open_sidebar();

      // Show the prompt
      deferredPrompt.prompt();

      // Wait for the user to respond to the prompt
      // USER CHOICE -> INSTALL / DECLINE
      deferredPrompt.userChoice.then((choiceResult) => {

        // acept installation
        if( choiceResult.outcome === 'accepted' ){

            // console.log('User accepted the A2HS prompt');
        }
        else{

          // decline installation
          // console.log('User dismissed the A2HS prompt');
        }

        // Pass deferred to null
        deferredPrompt = null;

        // remove event listener
        window.removeEventListener('beforeinstallprompt', false);

      });
      // end USER CHOICE -> INSTALL / DECLINE

  }
  /**
   * function install_app();
   */
