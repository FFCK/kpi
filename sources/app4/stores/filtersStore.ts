import { defineStore } from 'pinia'

interface FiltersState {
  season: string
  competition: string
  initialized: boolean
}

export const useFiltersStore = defineStore('filters', {
  state: (): FiltersState => ({
    season: '',
    competition: '',
    initialized: false
  }),

  actions: {
    setSeason(season: string) {
      this.season = season
      this.initialized = true
    },

    setCompetition(competition: string) {
      this.competition = competition
    },

    setSeasonAndCompetition(season: string, competition: string) {
      this.season = season
      this.competition = competition
      this.initialized = true
    }
  }
})
