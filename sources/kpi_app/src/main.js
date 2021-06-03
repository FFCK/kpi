import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import store from './store'
import 'jquery/src/jquery.js'
import 'bootstrap/dist/js/bootstrap.min.js'
import i18n from './services/i18n'
import ElementPlus from 'element-plus'

createApp(App).use(store).use(router).use(i18n).use(ElementPlus).mount('#app')
