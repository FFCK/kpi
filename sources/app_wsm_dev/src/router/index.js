import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '@/views/Home'
import Login from '@/views/Login'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/faker',
    name: 'Faker',
    component: () => import(/* webpackChunkName: "Faker" */ '../views/Faker.vue')
  },
  {
    path: '/stats',
    name: 'Stats',
    component: () => import(/* webpackChunkName: "Stats" */ '../views/Stats.vue')
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
