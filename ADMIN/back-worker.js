/**
 * PlACIDO-SHOP FRAMEWORK - JS BACK
 * Copyright © Raphaël Castello , 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	back-worker.js
 *
 * Web Worker
 * Progessive Web App script
 * see https://developer.mozilla.org/fr/docs/Web/Progressive_web_apps/
 *
 */

	const CacheName = 'Placido-Shop-backend';

  self.addEventListener('fetch', (e) => {

			// e.respondWith(
		  //   caches.match(e.request).then((r) => {
		  //         console.log('[Service Worker] Récupération de la ressource: '+e.request.url);
			// 				// console.log(e.request.url);
		  //     return r
			// 		|| fetch(e.request).then((response) => {
			//
			// 				return caches.open(CacheName).then((cache) => {
			//
			// 						if( e.request.url != 'https://vape.sns.pm/ADMIN/index.php' ){
			//
			// 							cache.put(e.request, response.clone());
			// 							console.log('[Service Worker] Caching new ressource: '+e.request.url);
			// 						}
	    //     				return response;
      // 				});
		  //     });
		  //   })
		  // );

  });



  // // load <script> templates :
	// const Templates = [
	// 	'templates/backend_base.html',
	// 	'templates/new_sales.html',
	// 	'templates/archives.html',
	// 	'templates/categories.html',
	// 	'templates/featured_prods.html',
	// 	'templates/messages.html',
	// 	'templates/settings.html',
	// 	'templates/shop.html',
	// 	'templates/stats.html',
	// 	'templates/products.html',
	// 	'templates/ip_rejected.html',
	// 	'templates/static_pages.html',
	// 	'templates/web_app.html'
	// ];
	//
  // // LOAD SCRIPTS
  // const ARR_scripts = [
	// 	'JS/archives.js',
	// 	'JS/cats.js',
	// 	'JS/ip_rejected.js',
	// 	'JS/main.js',
	// 	'JS/messages.js',
  //   'JS/new_sales.js',
  //   'JS/products.js',
	// 	'JS/pwa.js',
	// 	'JS/settings.js',
  //   'JS/shop.js',
	// 	'JS/static_pages.js',
	// 	'JS/tools.js',
	// 	'JS/web_app.js'
	//
  // ];
	//
	// self.addEventListener('install', (e) => {
	//
	// 		console.log('[Service Worker] install');
	//
	// 		var contentToCache = ARR_scripts.concat(Templates);
	//
	// 		e.waitUntil(
	// 	    caches.open(CacheName).then( (cache) => cache.addAll(contentToCache) )
	// 		);
	//
	// });

	// // clear old caches
	// self.addEventListener('activate', (e) => {
	//   e.waitUntil(caches.keys().then((keyList) => {
	//     return Promise.all(keyList.map((key) => {
	//       if (key === CacheName) { return; }
	//       return caches.delete(key);
	//     }))
	//   }));
	// });
