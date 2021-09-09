import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import store from './store'
import 'bootstrap/dist/js/bootstrap.min.js'
import i18n from './services/i18n'
import VueAnimXyz from '@animxyz/vue3'
import '@animxyz/core'

createApp(App)
  .use(store)
  .use(router)
  .use(i18n)
  .use(VueAnimXyz)
  .mount('#app')
