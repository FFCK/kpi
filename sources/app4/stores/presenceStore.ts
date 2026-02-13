import { defineStore } from 'pinia'
import type {
  Player,
  TeamInfo,
  CompetitionInfo,
  MatchInfo,
  LastUpdateInfo,
  PresenceMode,
  TeamPlayersResponse,
  MatchPlayersResponse,
  AddPlayerFormData,
  MatchAddPlayerFormData,
  CopyCompositionFormData,
  AvailableComposition,
  CopyableMatch
} from '~/types/presence'

interface PresenceState {
  // Mode detection
  mode: PresenceMode | null

  // Team mode
  teamId: number | null

  // Match mode
  matchId: number | null
  teamCode: 'A' | 'B' | null

  // Context info
  team: TeamInfo | null
  competition: CompetitionInfo | null
  match: MatchInfo | null

  // Lock status (Verrou for team, Validation for match)
  isLocked: boolean

  // Players
  players: Player[]
  lastUpdate: LastUpdateInfo | null

  // UI state
  loading: boolean
  initialized: boolean
}

export const usePresenceStore = defineStore('presence', {
  state: (): PresenceState => ({
    mode: null,
    teamId: null,
    matchId: null,
    teamCode: null,
    team: null,
    competition: null,
    match: null,
    isLocked: false,
    players: [],
    lastUpdate: null,
    loading: false,
    initialized: false
  }),

  getters: {
    // Check if team mode
    isTeamMode: (state): boolean => state.mode === 'team',

    // Check if match mode
    isMatchMode: (state): boolean => state.mode === 'match',

    // Get active players (-, C)
    activePlayers: (state): Player[] => {
      return state.players.filter(p => ['-', 'C'].includes(p.capitaine))
    },

    // Get inactive players (E, A, X)
    inactivePlayers: (state): Player[] => {
      return state.players.filter(p => ['E', 'A', 'X'].includes(p.capitaine))
    },

    // Check if national competition (N* or CF*)
    isNationalCompetition: (state): boolean => {
      if (!state.competition) return false
      const code = state.competition.code
      return code.startsWith('N') || code.startsWith('CF')
    },

    // Get context label for display
    contextLabel: (state): string => {
      if (!state.team) return ''
      if (state.mode === 'team') {
        return `${state.team.libelle} (${state.competition?.code}-${state.team.codeSaison})`
      } else {
        return `${state.team.libelle} vs ${state.match?.libelle || ''}`
      }
    }
  },

  actions: {
    // Initialize Team Mode
    async initTeamMode(teamId: number, apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      this.mode = 'team'
      this.teamId = teamId
      this.matchId = null
      this.teamCode = null
      this.loading = true
      this.initialized = false

      try {
        const response = await api.get<TeamPlayersResponse>(`/admin/teams/${teamId}/players`)

        this.team = response.team
        this.competition = response.competition
        this.players = response.players
        this.lastUpdate = response.lastUpdate || null
        this.isLocked = response.competition.verrou
        this.initialized = true
      } catch (error) {
        console.error('Failed to load team players:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    // Initialize Match Mode
    async initMatchMode(matchId: number, teamCode: 'A' | 'B', apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      this.mode = 'match'
      this.matchId = matchId
      this.teamCode = teamCode
      this.teamId = null
      this.loading = true
      this.initialized = false

      try {
        const response = await api.get<MatchPlayersResponse>(
          `/admin/matches/${matchId}/players`,
          { teamCode }
        )

        this.match = response.match
        this.team = response.team
        this.competition = response.competition
        this.players = response.players
        this.isLocked = response.match.validation
        this.initialized = true
      } catch (error) {
        console.error('Failed to load match players:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    // Reload current mode data
    async reload(apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      if (this.mode === 'team' && this.teamId) {
        await this.initTeamMode(this.teamId, api)
      } else if (this.mode === 'match' && this.matchId && this.teamCode) {
        await this.initMatchMode(this.matchId, this.teamCode, api)
      }
    },

    // Team Mode: Add player
    async addPlayer(data: AddPlayerFormData, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.teamId) throw new Error('Team ID not set')

      const api = apiInstance ?? useApi()
      await api.post(`/admin/teams/${this.teamId}/players/add`, data)
      await this.reload(api)
    },

    // Match Mode: Add player
    async addMatchPlayer(data: MatchAddPlayerFormData, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.matchId || !this.teamCode) throw new Error('Match ID or team code not set')

      const api = apiInstance ?? useApi()
      await api.post(`/admin/matches/${this.matchId}/players/add`, {
        ...data,
        teamCode: this.teamCode
      })
      await this.reload(api)
    },

    // Match Mode: Initialize from team composition
    async initializeFromTeam(apiInstance?: ReturnType<typeof useApi>) {
      if (!this.matchId || !this.teamCode) throw new Error('Match ID or team code not set')

      const api = apiInstance ?? useApi()
      await api.post(`/admin/matches/${this.matchId}/players/initialize`, {
        teamCode: this.teamCode
      })
      await this.reload(api)
    },

    // Delete players (both modes)
    async deletePlayers(matricIds: number[], apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      if (this.mode === 'team' && this.teamId) {
        await api.del(`/admin/teams/${this.teamId}/players`, { matricIds })
      } else if (this.mode === 'match' && this.matchId && this.teamCode) {
        await api.del(`/admin/matches/${this.matchId}/players`, {
          matricIds,
          teamCode: this.teamCode
        })
      }

      await this.reload(api)
    },

    // Update player inline (numero or capitaine)
    async updatePlayerInline(matric: number, field: 'numero' | 'capitaine', value: number | string, apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      if (this.mode === 'team' && this.teamId) {
        await api.patch(`/admin/teams/${this.teamId}/players/${matric}`, {
          [field]: value
        })
      } else if (this.mode === 'match' && this.matchId && this.teamCode) {
        await api.patch(`/admin/matches/${this.matchId}/players/${matric}`, {
          [field]: value,
          teamCode: this.teamCode
        })
      }

      // Update local state optimistically
      const player = this.players.find(p => p.matric === matric)
      if (player) {
        if (field === 'numero') {
          player.numero = value as number
        } else if (field === 'capitaine') {
          player.capitaine = value as Player['capitaine']
        }
      }
    },

    // Team Mode: Copy composition from another competition
    async copyComposition(data: CopyCompositionFormData, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.teamId) throw new Error('Team ID not set')

      const api = apiInstance ?? useApi()
      await api.post(`/admin/teams/${this.teamId}/players/copy`, data)
      await this.reload(api)
    },

    // Team Mode: Get available compositions for copy
    async getAvailableCompositions(season?: string, apiInstance?: ReturnType<typeof useApi>): Promise<AvailableComposition[]> {
      if (!this.teamId) throw new Error('Team ID not set')

      const api = apiInstance ?? useApi()
      const response = await api.get<{ compositions: AvailableComposition[] }>(
        `/admin/teams/${this.teamId}/compositions`,
        season ? { season } : undefined
      )
      return response.compositions
    },

    // Match Mode: Clear all players
    async clearMatchPlayers(apiInstance?: ReturnType<typeof useApi>) {
      if (!this.matchId || !this.teamCode) throw new Error('Match ID or team code not set')

      const api = apiInstance ?? useApi()
      await api.del(`/admin/matches/${this.matchId}/players/clear`, {
        teamCode: this.teamCode
      })
      await this.reload(api)
    },

    // Match Mode: Copy to other matches (day or competition)
    async copyToMatches(scope: 'day' | 'competition', matchIds: number[], apiInstance?: ReturnType<typeof useApi>) {
      if (!this.matchId || !this.teamCode) throw new Error('Match ID or team code not set')

      const api = apiInstance ?? useApi()

      if (scope === 'day') {
        await api.post(`/admin/matches/${this.matchId}/players/copy-to-day`, {
          teamCode: this.teamCode,
          matchIds
        })
      } else {
        await api.post(`/admin/matches/${this.matchId}/players/copy-to-competition`, {
          teamCode: this.teamCode,
          matchIds
        })
      }
    },

    // Match Mode: Get copyable matches
    async getCopyableMatches(scope: 'day' | 'competition', apiInstance?: ReturnType<typeof useApi>): Promise<CopyableMatch[]> {
      if (!this.matchId || !this.teamCode) throw new Error('Match ID or team code not set')

      const api = apiInstance ?? useApi()
      const response = await api.get<{ matches: CopyableMatch[] }>(
        `/admin/matches/${this.matchId}/copyable-matches`,
        { teamCode: this.teamCode, scope }
      )
      return response.matches
    },

    // Clear state
    clearContext() {
      this.$reset()
    }
  }
})
