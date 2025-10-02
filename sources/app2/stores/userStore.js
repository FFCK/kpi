import { defineStore } from 'pinia'
import db from '~/utils/db'

export const useUserStore = defineStore('userStore', {
  state: () => ({
    user: null
  }),
  actions: {
    async fetchUser() {
      const users = await db.user.toArray()
      if (users.length > 0) {
        this.user = users[0]
      } else {
        this.user = null
      }
    },
    async setUser(userData) {
      await db.user.clear()
      await db.user.add(userData)
      this.user = userData
    },
    async clearUser() {
      await db.user.clear()
      this.user = null
    }
  }
})
