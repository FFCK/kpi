import { defineStore } from 'pinia'
import createDb from '../../plugins/dexie'
import { get } from '@vueuse/core'

const db = createDb('select', 'maj_ref')

export const useMajRefStore = defineStore('majRef', {
    state: () => ({
        maj_ref: [],
    }),
    actions: {
        async fetchItems() {
            this.maj_ref = await db.maj_ref.toArray()
        },
        async addItem(id, value) {
            await db.maj_ref.add({ id, value })
            this.fetchItems()
        },
        async putItem(id, value) {
            await db.maj_ref.put({ id, value })
            this.fetchItems()
        },
        async getItem(id) {
            return await db.maj_ref.get(id)
        },
        async removeItem(id) {
            await db.maj_ref.delete(id)
            this.fetchItems()
        },
        async removeAllItems() {
            await db.maj_ref.clear()
            this.fetchItems()
        },
        async clearAndUpdateAllItems(data) {
            try {
                await db.maj_ref.clear()
                await db.maj_ref.bulkAdd(data)
                this.fetchItems()
            }
            catch (error) {
                console.error(error)
            }
        }
    }
})
