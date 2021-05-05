// src/service-worker.js
importScripts('../third_party/workbox/workbox-sw.js')
workbox.setConfig({
  modulePathPrefix: '../third_party/workbox',
  debug: true // Dev / Prod !!
})

console.log('Hello from service-worker.js')

workbox.core.setCacheNameDetails({ prefix: 'kpi_app' })

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

// Cache js requests with a Network First strategy
workbox.routing.registerRoute(
  // Check to see if the request is a js file
  new RegExp('.+\\.js$'),
  // Use a Network First caching strategy
  new workbox.strategies.NetworkFirst({
    // Put all cached files in a cache named 'js_assets'
    cacheName: 'js_assets',
    plugins: [
      // Ensure that only requests that result in a 200 status are cached
      new workbox.cacheableResponse.Plugin({
        statuses: [200]
      })
    ]
  })
)

// Cache CSS requests with a Stale While Revalidate strategy
workbox.routing.registerRoute(
  // Check to see if the request's destination is css file
  new RegExp('.+\\.css$'),
  // Use a Stale While Revalidate caching strategy
  new workbox.strategies.StaleWhileRevalidate({
    // Put all cached files in a cache named 'css_assets'
    cacheName: 'css_assets',
    plugins: [
      // Ensure that only requests that result in a 200 status are cached
      new workbox.cacheableResponse.Plugin({
        statuses: [200]
      })
    ]
  })
)

// Cache images with a Cache First strategy
workbox.routing.registerRoute(
  // Check to see if the request's destination is style for an image
  new RegExp('.+\\.png$'),
  // Use a Cache First caching strategy
  new workbox.strategies.CacheFirst({
    // Put all cached files in a cache named 'images'
    cacheName: 'images',
    plugins: [
      // Ensure that only requests that result in a 200 status are cached
      new workbox.cacheableResponse.Plugin({
        statuses: [200]
      }),
      // Don't cache more than 50 items, and expire them after 30 days
      new workbox.expiration.Plugin({
        maxEntries: 50,
        maxAgeSeconds: 60 * 60 * 24 * 30 // 30 Days
      })
    ]
  })
)
