import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Games from '../views/Games.vue'
import Chart from '../views/Chart.vue'
import Login from '../views/Login.vue'
import About from '../views/About.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/games',
    name: 'Games',
    component: Games
  },
  {
    path: '/chart',
    name: 'Chart',
    component: Chart
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/game_report',
    name: 'GameReport',
    component: () => import(/* webpackChunkName: "GameReport" */ '../views/GameReport.vue')
  },
  {
    path: '/stat_report',
    name: 'StatReport',
    component: () => import(/* webpackChunkName: "StatReport" */ '../views/StatReport.vue')
  },
  {
    path: '/scrutineering',
    name: 'Scrutineering',
    component: () => import(/* webpackChunkName: "Scrutineering" */ '../views/Scrutineering.vue')
  },
  {
    path: '/about',
    name: 'About',
    component: About
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
