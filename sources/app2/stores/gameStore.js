import { defineStore } from 'pinia'

export const useGameStore = defineStore('gameStore', {
  state: () => ({
    games: [],
    loading: false,
    error: null
  }),
  actions: {
    async clearAndUpdateGames(games) {
      this.games = games
    }
  }
})
