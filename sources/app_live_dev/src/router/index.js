import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '@/views/Home'
import Live from '@/views/Live'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/:event(\\d+)/:pitch(\\d+)/:options*',
    name: 'Live',
    component: Live
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
