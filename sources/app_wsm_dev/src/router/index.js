import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '@/views/Home'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/faker',
    name: 'Faker',
    component: () => import(/* webpackChunkName: "Faker" */ '../views/Faker.vue')
  },
  {
    path: '/manager',
    name: 'Manager',
    component: () => import(/* webpackChunkName: "Manager" */ '../views/Manager.vue')
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
