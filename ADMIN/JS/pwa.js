/**
 * PlACIDO-SHOP FRAMEWORK - JS BACK
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	pwa.js
 *
 * Progessive Web App (PWA) - BACK
 * see https://developer.mozilla.org/fr/docs/Web/Progressive_web_apps/
 *
 * serviceWorker
 * pwa_install_app();
 *
 */


	/**
	 * run service worker
	 */
	if( 'serviceWorker' in navigator ){

			navigator.serviceWorker.register( 'back-worker.js' )
			.then((registration) => {

					// console.log('Service Worker Registered');
					// console.log(registration);
					registration.update();
			});
	}
	/**
	 * run service worker
	 */


  // INSTALL APPLICATION - MANUAL INSTALLATION BY EVENT
  // Code to handle install prompt()
  var DeferredPrompt = null;

  // capture event before install APP
  window.addEventListener('beforeinstallprompt', function(event){

	    // Prevent Chrome 67 and earlier from automatically showing the prompt
	    event.preventDefault();

	    // Stash the event so it can be triggered later.
	    DeferredPrompt = event;

  });
  // INSTALL APPLICATION - MANUAL INSTALLATION BY EVENT


  /**
   * function pwa_install_app();
   *
   * install website as an application on the device of the user
   * this a Progressive Web Application (PWA) installer
   * Fire on event
   * @return {application}
   * Note: Lots of parts of this code is from the Mozilla Javascipt documention
   */
  function pwa_install_app(){


			// Show the prompt
			if( DeferredPrompt ){

					DeferredPrompt.prompt();

			}
			else{

					$.show_alert('info',
					`Please clear your browser cache and relaunch your application`,
					true);

					// try to destroy registration worker ...
					if( 'serviceWorker' in navigator ){

							navigator.serviceWorker.getRegistrations().then(function(registrations) {
									for(let registration of registrations){
											registration.unregister();
									}
							});
					}

					return;
			}
			// end beforeinstallprompt in unaviable


			// Wait for the user to respond to the prompt
			// USER CHOICE -> INSTALL / DECLINE
			DeferredPrompt.userChoice.then((choiceResult) => {

				// accept installation
				if( choiceResult.outcome === 'accepted' ){

						// console.log('User accepted the A2HS prompt');

				}
				else{

						// decline installation
						// console.log('User dismissed the A2HS prompt');
				}

				// Pass deferred to null
				DeferredPrompt = null;

		 });
		 // end USER CHOICE -> INSTALL / DECLINE

  }
  /**
   * function pwa_install_app();
   */

  // END INSTALL APPLICATION
