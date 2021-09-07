/* eslint-disable */
const version = '1.0.0'
importScripts('../third_party/workbox-v6.1.5/workbox-sw.js')
workbox.setConfig({
  modulePathPrefix: '../third_party/workbox-v6.1.5',
  debug: true // Dev / Prod !!
})

workbox.core.setCacheNameDetails({ prefix: 'kpi_cache' })

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting()
  }
})

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [].concat(self.__precacheManifest || [])
workbox.precaching.precacheAndRoute(self.__precacheManifest, {})


// Broadcast d'un message à l'application (à toutes les fenêtres ouvertes)
const broadcast = async (message) => {
  const windows = await self.clients.matchAll({ type: 'window' })
  for (const win of windows) {
    win.postMessage(message)
  }
}

// Création de la file d'attente
const queue = new workbox.backgroundSync.Queue('ApiQueue')

self.addEventListener('fetch', (event) => {
  // Clone la requête pour être sûr de la lire lors de l'ajout à la file d'attente.
  const promiseChain = fetch(event.request.clone()).catch((err) => {
    // Absence de connectivité
    broadcast('OFFLINE')
    return queue.pushRequest({ request: event.request })
  })

  event.waitUntil(promiseChain)
})

self.addEventListener('sync', () => {
  // Retour de la connectivité
  broadcast('ONLINE')
})

// No cache for api routes
workbox.routing.registerRoute(
  ({url}) => url.pathname.startsWith('/api/'),
  new workbox.strategies.NetworkOnly()
);


// const navigationHandler = async (params) => {
//   try {
//     // Attempt a network request.
//     return await networkOnly.handle(params);
//   } catch (error) {
//     console.log('Hors ligne !')
//     // If it fails, return the cached HTML.
//     // return caches.match(FALLBACK_HTML_URL, {
//     //   cacheName: CACHE_NAME,
//     // });
//   }
// };

// Register this strategy to handle all navigations.
// workbox.routing.registerRoute(
//   new workbox.routing.NavigationRoute(navigationHandler)
// );

// Cache js requests with a Network First strategy
// workbox.routing.registerRoute(
//   // Check to see if the request is a js file
//   new RegExp('.+\\.js$'),
//   // Use a Network First caching strategy
//   new workbox.strategies.NetworkFirst({
//     // Put all cached files in a cache named 'js_assets'
//     cacheName: 'js_assets',
//     plugins: [
//       // Ensure that only requests that result in a 200 status are cached
//       new workbox.cacheableResponse.Plugin({
//         statuses: [200]
//       })
//     ]
//   })
// )

// Cache CSS requests with a Stale While Revalidate strategy
// workbox.routing.registerRoute(
//   // Check to see if the request's destination is css file
//   new RegExp('.+\\.css$'),
//   // Use a Stale While Revalidate caching strategy
//   new workbox.strategies.StaleWhileRevalidate({
//     // Put all cached files in a cache named 'css_assets'
//     cacheName: 'css_assets',
//     plugins: [
//       // Ensure that only requests that result in a 200 status are cached
//       new workbox.cacheableResponse.Plugin({
//         statuses: [200]
//       })
//     ]
//   })
// )

// Cache images with a Cache First strategy
// workbox.routing.registerRoute(
//   // Check to see if the request's destination is style for an image
//   new RegExp('.+\\.png$'),
//   // Use a Cache First caching strategy
//   new workbox.strategies.CacheFirst({
//     // Put all cached files in a cache named 'images'
//     cacheName: 'images',
//     plugins: [
//       // Ensure that only requests that result in a 200 status are cached
//       new workbox.cacheableResponse.Plugin({
//         statuses: [200]
//       }),
//       // Don't cache more than 50 items, and expire them after 30 days
//       new workbox.expiration.Plugin({
//         maxEntries: 50,
//         maxAgeSeconds: 60 * 60 * 24 * 30 // 30 Days
//       })
//     ]
//   })
// )

// const bgSyncPlugin = new workbox.backgroundSync.Plugin('ApiQueue', {
//   maxRetentionTime: 24 * 60 // Retry for max of 24 Hours (specified in minutes)
// });

// workbox.routing.registerRoute(
//   'https://jsonplaceholder.typicode.com/photos',
//   new workbox.strategies.NetworkOnly({
//     plugins: [bgSyncPlugin]
//   }),
//   'POST'
// );
