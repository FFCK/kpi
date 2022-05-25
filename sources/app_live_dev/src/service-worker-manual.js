// src/service-worker.js

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js')
    .then((reg) => {
      // registration worked
      console.log('Enregistrement réussi')
    }).catch((error) => {
      // registration failed
      console.log('Erreur : ' + error)
    })
}

var CACHE_NAME = 'kpi_app-cache-v1'
var urlsToCache = [
  '/',
  '/styles/main.[hash].css',
  '/script/main.[hash].js'
]

self.addEventListener('install', function (event) {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function (cache) {
        console.log('Opened cache')
        return cache.addAll(urlsToCache)
      })
  )
})

self.addEventListener('activate', function (event) {
  var cacheWhitelist = [CACHE_NAME]

  event.waitUntil(
    // Check de toutes les clés de cache.
    caches.keys().then(function (cacheNames) {
      return Promise.all(
        cacheNames.map(function (cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName)
          }
        })
      )
    })
  )
})

// online-first (online-first, offline-first, WhileRevalidate, cache & network race)
self.addEventListener('fetch', function (event) {
  event.respondWith(
    fetch(event.request).catch(function () {
      return caches.match(event.request)
    })
  )
})
