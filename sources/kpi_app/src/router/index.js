import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Games from '../views/Games.vue'
import Ranking from '../views/Ranking.vue'
import Login from '../views/Login.vue'
import GameReport from '../views/GameReport.vue'
import StatReport from '../views/StatReport.vue'
import Scrutineering from '../views/Scrutineering.vue'

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
    path: '/ranking',
    name: 'Ranking',
    component: Ranking
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/game_report',
    name: 'GameReport',
    component: GameReport
  },
  {
    path: '/stat_report',
    name: 'StatReport',
    component: StatReport
  },
  {
    path: '/scrutineering',
    name: 'Scrutineering',
    component: Scrutineering
  },
  {
    path: '/about',
    name: 'About',
    component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
