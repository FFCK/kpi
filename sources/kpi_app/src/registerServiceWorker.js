navigator.serviceWorker.addEventListener('message', (event) => {
  if (event.data.type === 'CACHE_UPDATED') {
    const { updatedURL } = event.data.payload

    console.log(`A newer version of ${updatedURL} is available!`)
  }
})

navigator.serviceWorker.addEventListener('controllerchange', function (event) {
  console.log(
    '[controllerchange] A "controllerchange" event has happened ' +
    'within navigator.serviceWorker: ', event
  )

  navigator.serviceWorker.controller.addEventListener('statechange',
    function () {
      console.log('[controllerchange][statechange] ' +
      'A "statechange" has occured: ', this.state
      )
    }
  )
})

navigator.serviceWorker.register('service-worker.js', {
  scope: '.'
}).then(function (registration) {
  console.log('The service worker has been registered')
})
