import { createStore } from 'vuex'
import VuexORM from '@vuex-orm/core'
import database from '@/store/database'

export default createStore({
  modules: {
  },
  plugins: [VuexORM.install(database)],
  state: {
  },
  getters: {
  },
  actions: {
  },
  mutations: {
  }
})
