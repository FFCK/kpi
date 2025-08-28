import { defineStore } from 'pinia'

export const useEventStore = defineStore('eventStore', {
  state: () => ({
    events: [],
    selectedEvent: null,
    loading: false,
    error: null
  }),
  actions: {
    addEvent(event) {
      this.events.push(event)
    },

    removeEvent(id) {
      this.events = this.events.filter(event => event.id !== id)
    },

    clearEvents() {
      this.events = []
    },

    clearAndUpdateEvents(data) {
      try {
        this.clearEvents()
        this.events = data.map(event => ({
          ...event,
          id: parseInt(event.id)
        }))
      } catch (error) {
        console.error(error)
      }
    }
  },

  getters: {
    eventCount: (state) => state.events.length,
    getEventById: (state) => (id) => {
      return state.events.find((e) => e.id === id)
    },
  }
})