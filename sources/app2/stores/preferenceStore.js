import { defineStore } from 'pinia'
import { v4 as uuidv4 } from 'uuid'
import db from '~/utils/db'

export const usePreferenceStore = defineStore('preferenceStore', {
  state: () => ({
    preferences: {
      lastEvent: null
    }
  }),
  actions: {
    async fetchItems() {
      const all = await db.preferences.toArray()
      this.preferences = {}
      all.forEach(item => {
        this.preferences[item.id] = item.value
      })
    },
    async initUid() {
      if (!this.preferences.uid) {
        const newUid = uuidv4();
        await this.putItem('uid', newUid);
      }
    },
    async addItem(id, value) {
      await db.preferences.add({ id, value })
      this.fetchItems()
    },
    async putItem(id, value) {
      await db.preferences.put({ id, value })
      this.fetchItems()
    },
    async getItem(id) {
      return await db.preferences.get(id)
    },
    async removeItem(id) {
      await db.preferences.delete(id)
      this.fetchItems()
    },
    async removeAllItems() {
      await db.preferences.clear()
      this.fetchItems()
    },
    async clearAndUpdateAllItems(data) {
      try {
        await db.preferences.clear()
        await db.preferences.bulkAdd(data)
        this.fetchItems()
      }
      catch (error) {
        console.error(error)
      }
    }
  }
})