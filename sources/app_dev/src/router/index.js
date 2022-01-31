import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '@/views/Home'
import Event from '@/views/Event'
import Games from '@/views/Games'
import Chart from '@/views/Chart'
import Login from '@/views/Login'
import Logout from '@/views/Logout'
import About from '@/views/About'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/event/:event_id',
    name: 'Event',
    component: Event
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
    path: '/logout',
    name: 'Logout',
    component: Logout
  },
  {
    path: '/game_reports',
    name: 'GameReports',
    component: () => import(/* webpackChunkName: "GameReports" */ '../views/GameReports.vue')
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
