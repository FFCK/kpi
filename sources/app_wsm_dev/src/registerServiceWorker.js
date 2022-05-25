// navigator.serviceWorker.addEventListener('message', (event) => {
//   if (event.data.type === 'CACHE_UPDATED') {
//     const { updatedURL } = event.data.payload
//     console.log(`A newer version of ${updatedURL} is available!`)
//   }
// })

// navigator.serviceWorker.addEventListener('controllerchange', function (event) {
//   console.log(
//     '[controllerchange] A "controllerchange" event has happened ' +
//     'within navigator.serviceWorker: ', event
//   )

//   navigator.serviceWorker.controller.addEventListener('statechange',
//     function () {
//       console.log('[controllerchange][statechange] ' +
//         'A "statechange" has occured: ', this.state
//       )
//     }
//   )
// })

// navigator.serviceWorker.register('service-worker.js', {
//   scope: '.'
// }).then(function (registration) {
//   // console.log('The service worker has been registered')
// })
import { register } from 'register-service-worker'

register('service-worker.js', {
  registrationOptions: { scope: '.' },
  ready (registration) {
    // console.log('Service worker is active.')
  },
  registered (registration) {
    // console.log('Service worker has been registered.')
  },
  cached (registration) {
    // console.log('Content has been cached for offline use.')
  },
  updatefound (registration) {
    // console.log('New content is downloading.')
  },
  updated (registration) {
    // console.log('New content is available; please refresh.')
    document.dispatchEvent(
      new CustomEvent('swUpdated', { detail: registration })
    )
  },
  offline () {
    // console.log('No internet connection found. App is running in offline mode.')
  },
  error (error) {
    console.error('Error during service worker registration:', error)
  }
})
