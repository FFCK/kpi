import { defineStore } from 'pinia'

export const useGroupStore = defineStore('groupStore', {
  state: () => ({
    sections: [],        // Groups organized by section (for optgroup display)
    selectedGroup: null, // { code: 'N1H', libelle: '...', libelle_en: '...' }
    selectedSeason: null, // Selected season (e.g. "2026")
    loading: false,
    error: null
  }),

  actions: {
    setSections(sections) {
      this.sections = sections
    },

    selectGroup(group) {
      this.selectedGroup = group
    },

    selectSeason(season) {
      this.selectedSeason = season
    },

    clearSelection() {
      this.selectedGroup = null
      // Keep the season when clearing
    },

    clearAll() {
      this.sections = []
      this.selectedGroup = null
      this.selectedSeason = null
    },

    // Initialize default season (current year)
    initDefaultSeason() {
      if (!this.selectedSeason) {
        this.selectedSeason = new Date().getFullYear().toString()
      }
    }
  },

  getters: {
    getGroupByCode: (state) => (code) => {
      for (const section of state.sections) {
        const found = section.groups.find(g => g.code === code)
        if (found) return found
      }
      return null
    },

    getCurrentSeason: (state) => {
      return state.selectedSeason || new Date().getFullYear().toString()
    },

    hasGroups: (state) => state.sections.length > 0,

    groupCount: (state) => {
      return state.sections.reduce((total, section) => total + section.groups.length, 0)
    }
  }
})
