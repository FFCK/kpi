import { defineStore } from 'pinia'

export const useGameStore = defineStore('gameStore', {
  state: () => ({
    games: [],
    selectedGame: null,
    loading: false,
    error: null
  }),
  actions: {
    async fetchGames() {
      this.loading = true
      this.error = null
      try {
        const result = await Games.query().orderBy('g_id', 'desc').get()
        this.games = Array.isArray(result) ? result : []
      } catch (err) {
        this.error = err
      } finally {
        this.loading = false
      }
    },
    selectGame(gameId) {
      this.selectedGame = this.games.find(g => g.g_id === gameId) || null
    },
    clearGames() {
      this.games = []
      this.selectedGame = null
      Games.deleteAll()
    }
  }
})