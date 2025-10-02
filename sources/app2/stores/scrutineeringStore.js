import { defineStore } from 'pinia'

export const useScrutineeringStore = defineStore('scrutineeringStore', {
  state: () => ({
    players: [],
    loading: false,
    error: null
  }),
  actions: {
    setPlayers(players) {
      this.players = players
    },
    clearPlayers() {
      this.players = []
    },
    updatePlayerEquipment(playerId, field, value) {
      const player = this.players.find(p => p.player_id === playerId)
      if (player) {
        player[field] = value
      }
    }
  }
})
