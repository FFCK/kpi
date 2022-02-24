import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '@/views/Home.vue'
import Event from '@/views/Event.vue'
import Games from '@/views/Games.vue'
import Chart from '@/views/Chart.vue'
import Login from '@/views/Login.vue'
import Logout from '@/views/Logout.vue'
import About from '@/views/About.vue'

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
    component: () => import('../views/GameReports.vue')
  },
  {
    path: '/stat_report',
    name: 'StatReport',
    component: () => import('../views/StatReport.vue')
  },
  {
    path: '/scrutineering',
    name: 'Scrutineering',
    component: () => import('../views/Scrutineering.vue')
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
