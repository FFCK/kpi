import { defineStore } from 'pinia'

interface StatsState {
  season: string
  statType: string
  competitions: string[]
  limit: number
  initialized: boolean
}

export const useStatsStore = defineStore('stats', {
  state: (): StatsState => ({
    season: '',
    statType: 'Buteurs',
    competitions: [],
    limit: 30,
    initialized: false
  }),

  actions: {
    setParams(params: Partial<Omit<StatsState, 'initialized'>>) {
      if (params.season !== undefined) this.season = params.season
      if (params.statType !== undefined) this.statType = params.statType
      if (params.competitions !== undefined) this.competitions = [...params.competitions]
      if (params.limit !== undefined) this.limit = params.limit
      this.initialized = true
    },

    setSeason(season: string) {
      this.season = season
    },

    setStatType(statType: string) {
      this.statType = statType
    },

    setCompetitions(competitions: string[]) {
      this.competitions = [...competitions]
    },

    setLimit(limit: number) {
      this.limit = Math.max(1, Math.min(500, limit))
    },

    resetCompetitions() {
      this.competitions = []
    }
  }
})
