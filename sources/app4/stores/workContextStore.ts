import { defineStore } from 'pinia'
import type { Season, Competition, CompetitionGroup, FilterEvent } from '~/types'

// Selection type for work context
export type SelectionType = 'section' | 'group' | 'competition' | 'event' | null

// Section info
export interface Section {
  id: number
  labelKey: string
}

// Group info (extracted from competitions)
export interface Group {
  code: string
  libelle: string
  section: number
  competitions: string[]
}

// Work context state
interface WorkContextState {
  // Season
  season: string

  // Selection type
  selectionType: SelectionType

  // Selection values (one filled based on type)
  sectionId: number | null
  groupCode: string | null
  competitionCode: string | null
  eventId: number | null

  // Computed competition codes (result of selection)
  competitionCodes: string[]

  // Reference data (loaded from API)
  seasons: Season[]
  groups: CompetitionGroup[]
  competitions: Competition[]
  events: FilterEvent[]

  // State flags
  initialized: boolean
  initializing: boolean // Guard against concurrent initialization
  loading: boolean
}

// LocalStorage keys
const STORAGE_KEYS = {
  season: 'kpi_admin_work_season',
  selectionType: 'kpi_admin_work_type',
  sectionId: 'kpi_admin_work_section',
  groupCode: 'kpi_admin_work_group',
  competitionCode: 'kpi_admin_work_competition',
  eventId: 'kpi_admin_work_event',
}

// Sections definition
const SECTIONS: Section[] = [
  { id: 1, labelKey: 'context.sections.1' },
  { id: 2, labelKey: 'context.sections.2' },
  { id: 3, labelKey: 'context.sections.3' },
  { id: 4, labelKey: 'context.sections.4' },
  { id: 5, labelKey: 'context.sections.5' },
  { id: 100, labelKey: 'context.sections.100' },
]

export const useWorkContextStore = defineStore('workContext', {
  state: (): WorkContextState => ({
    season: '',
    selectionType: null,
    sectionId: null,
    groupCode: null,
    competitionCode: null,
    eventId: null,
    competitionCodes: [],
    seasons: [],
    groups: [],
    competitions: [],
    events: [],
    initialized: false,
    initializing: false,
    loading: false,
  }),

  getters: {
    // Check if context is valid
    hasValidContext(): boolean {
      return this.season !== '' && this.selectionType !== null && this.competitionCodes.length > 0
    },

    // Get active season object
    activeSeason(): Season | undefined {
      return this.seasons.find(s => s.code === this.season)
    },

    // Get sections list
    sections(): Section[] {
      return SECTIONS
    },

    // Get available sections (that have competitions)
    availableSections(): Section[] {
      const sectionsWithCompetitions = new Set(this.groups.map(g => g.section))
      return SECTIONS.filter(s => sectionsWithCompetitions.has(s.id))
    },

    // Get unique groups (by codeRef)
    uniqueGroups(): Group[] {
      const groupMap = new Map<string, Group>()

      for (const sectionGroup of this.groups) {
        for (const comp of sectionGroup.competitions) {
          const groupCode = comp.codeRef || comp.code
          if (!groupMap.has(groupCode)) {
            groupMap.set(groupCode, {
              code: groupCode,
              libelle: comp.libelle,
              section: sectionGroup.section,
              competitions: [],
            })
          }
          groupMap.get(groupCode)!.competitions.push(comp.code)
        }
      }

      return Array.from(groupMap.values())
    },

    // Get groups for a specific section
    groupsBySection(): (sectionId: number) => Group[] {
      return (sectionId: number) => {
        return this.uniqueGroups.filter(g => g.section === sectionId)
      }
    },

    // Competition count
    competitionCount(): number {
      return this.competitionCodes.length
    },

    // Is single competition
    isSingleCompetition(): boolean {
      return this.competitionCodes.length === 1
    },

    // First competition (for single-select pages)
    firstCompetition(): Competition | undefined {
      if (this.competitionCodes.length === 0) return undefined
      return this.competitions.find(c => c.code === this.competitionCodes[0])
    },

    // Get competitions from context
    contextCompetitions(): Competition[] {
      return this.competitions.filter(c => this.competitionCodes.includes(c.code))
    },

    // Get context label for display
    contextLabel(): string {
      if (!this.selectionType) return ''

      switch (this.selectionType) {
        case 'section':
          return `Section ${this.sectionId}`
        case 'group':
          return `Groupe ${this.groupCode}`
        case 'competition':
          return this.competitionCode || ''
        case 'event':
          const event = this.events.find(e => e.id === this.eventId)
          return event?.libelle || ''
        default:
          return ''
      }
    },
  },

  actions: {
    // Initialize context from localStorage
    async initContext() {
      // Prevent concurrent or duplicate initialization
      if (this.initialized || this.initializing) return

      // Only run on client side
      if (import.meta.server) return

      this.initializing = true
      this.loading = true

      try {
        // Load API data first
        const api = useApi()

        // Load seasons
        const seasonsResponse = await api.get<{ seasons: Season[]; activeSeason: string }>('/admin/filters/seasons')
        if (seasonsResponse) {
          this.seasons = seasonsResponse.seasons
          // Set default season from localStorage or active season
          const storedSeason = localStorage.getItem(STORAGE_KEYS.season)
          this.season = storedSeason || seasonsResponse.activeSeason || this.seasons[0]?.code || ''
        }

        // Load competitions and events for the season
        if (this.season) {
          await this.loadSeasonData(api)
        }

        // Load events
        await this.loadEvents(api)

        // Restore selection from localStorage
        const storedType = localStorage.getItem(STORAGE_KEYS.selectionType) as SelectionType
        if (storedType) {
          this.selectionType = storedType

          switch (storedType) {
            case 'section':
              const sectionId = localStorage.getItem(STORAGE_KEYS.sectionId)
              if (sectionId) {
                this.sectionId = parseInt(sectionId, 10)
                this.computeCompetitionCodes()
              }
              break
            case 'group':
              const groupCode = localStorage.getItem(STORAGE_KEYS.groupCode)
              if (groupCode) {
                this.groupCode = groupCode
                this.computeCompetitionCodes()
              }
              break
            case 'competition':
              const competitionCode = localStorage.getItem(STORAGE_KEYS.competitionCode)
              if (competitionCode) {
                this.competitionCode = competitionCode
                this.computeCompetitionCodes()
              }
              break
            case 'event':
              const eventId = localStorage.getItem(STORAGE_KEYS.eventId)
              if (eventId) {
                this.eventId = parseInt(eventId, 10)
                await this.loadEventCompetitions(api)
              }
              break
          }
        }

        this.initialized = true
      }
      catch (error) {
        // Context loading failed - continue without context
        console.error('[WorkContext] Failed to load work context:', error)
        // Allow retry on failure
        this.initializing = false
      }
      finally {
        this.loading = false
      }
    },

    // Load season-specific data (competitions grouped by section)
    async loadSeasonData(apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      try {
        const response = await api.get<{ season: string; groups: CompetitionGroup[] }>(
          '/admin/filters/competitions',
          { season: this.season },
        )

        if (response) {
          this.groups = response.groups

          // Flatten competitions for easy access
          this.competitions = response.groups.flatMap(g => g.competitions)
        }
      }
      catch (error) {
        console.error('[WorkContext] loadSeasonData() failed:', error)
        throw error
      }
    },

    // Load events
    async loadEvents(apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()

      try {
        const response = await api.get<{ events: FilterEvent[] }>('/admin/filters/events')
        if (response) {
          this.events = response.events
        }
      }
      catch (error) {
        console.error('[WorkContext] loadEvents() failed:', error)
        throw error
      }
    },

    // Load competitions for an event
    async loadEventCompetitions(apiInstance?: ReturnType<typeof useApi>) {
      if (!this.eventId) return

      const api = apiInstance ?? useApi()

      const response = await api.get<{ eventId: number; competitions: Array<{ code: string; libelle: string; codeRef: string | null }> }>(
        '/admin/filters/event-competitions',
        { eventId: this.eventId },
      )

      if (response) {
        this.competitionCodes = response.competitions.map(c => c.code)
      }
    },

    // Set season (reloads data)
    async setSeason(seasonCode: string, apiInstance?: ReturnType<typeof useApi>) {
      if (this.season === seasonCode) return

      this.season = seasonCode
      localStorage.setItem(STORAGE_KEYS.season, seasonCode)

      // Clear current selection
      this.clearSelection()

      // Reload season data
      this.loading = true
      try {
        await this.loadSeasonData(apiInstance)
      }
      finally {
        this.loading = false
      }
    },

    // Select by section
    selectSection(sectionId: number) {
      this.selectionType = 'section'
      this.sectionId = sectionId
      this.groupCode = null
      this.competitionCode = null
      this.eventId = null

      this.saveToStorage()
      this.computeCompetitionCodes()
    },

    // Select by group
    selectGroup(groupCode: string) {
      this.selectionType = 'group'
      this.sectionId = null
      this.groupCode = groupCode
      this.competitionCode = null
      this.eventId = null

      this.saveToStorage()
      this.computeCompetitionCodes()
    },

    // Select single competition
    selectCompetition(code: string) {
      this.selectionType = 'competition'
      this.sectionId = null
      this.groupCode = null
      this.competitionCode = code
      this.eventId = null

      this.saveToStorage()
      this.computeCompetitionCodes()
    },

    // Select by event
    async selectEvent(eventId: number, apiInstance?: ReturnType<typeof useApi>) {
      this.selectionType = 'event'
      this.sectionId = null
      this.groupCode = null
      this.competitionCode = null
      this.eventId = eventId

      this.saveToStorage()
      await this.loadEventCompetitions(apiInstance)
    },

    // Compute competition codes based on selection type
    computeCompetitionCodes() {
      switch (this.selectionType) {
        case 'section':
          // All competitions from the section
          this.competitionCodes = this.competitions
            .filter(c => {
              const group = this.groups.find(g => g.competitions.some(gc => gc.code === c.code))
              return group?.section === this.sectionId
            })
            .map(c => c.code)
          break

        case 'group':
          // All competitions with matching codeRef
          this.competitionCodes = this.competitions
            .filter(c => (c.codeRef || c.code) === this.groupCode)
            .map(c => c.code)
          break

        case 'competition':
          // Single competition
          this.competitionCodes = this.competitionCode ? [this.competitionCode] : []
          break

        case 'event':
          // Already loaded via loadEventCompetitions()
          break

        default:
          this.competitionCodes = []
      }
    },

    // Clear selection (but keep season)
    clearSelection() {
      this.selectionType = null
      this.sectionId = null
      this.groupCode = null
      this.competitionCode = null
      this.eventId = null
      this.competitionCodes = []

      localStorage.removeItem(STORAGE_KEYS.selectionType)
      localStorage.removeItem(STORAGE_KEYS.sectionId)
      localStorage.removeItem(STORAGE_KEYS.groupCode)
      localStorage.removeItem(STORAGE_KEYS.competitionCode)
      localStorage.removeItem(STORAGE_KEYS.eventId)
    },

    // Clear everything
    clearContext() {
      this.season = ''
      this.clearSelection()
      localStorage.removeItem(STORAGE_KEYS.season)
    },

    // Save to localStorage
    saveToStorage() {
      if (this.selectionType) {
        localStorage.setItem(STORAGE_KEYS.selectionType, this.selectionType)
      }
      if (this.sectionId !== null) {
        localStorage.setItem(STORAGE_KEYS.sectionId, String(this.sectionId))
      }
      else {
        localStorage.removeItem(STORAGE_KEYS.sectionId)
      }
      if (this.groupCode) {
        localStorage.setItem(STORAGE_KEYS.groupCode, this.groupCode)
      }
      else {
        localStorage.removeItem(STORAGE_KEYS.groupCode)
      }
      if (this.competitionCode) {
        localStorage.setItem(STORAGE_KEYS.competitionCode, this.competitionCode)
      }
      else {
        localStorage.removeItem(STORAGE_KEYS.competitionCode)
      }
      if (this.eventId !== null) {
        localStorage.setItem(STORAGE_KEYS.eventId, String(this.eventId))
      }
      else {
        localStorage.removeItem(STORAGE_KEYS.eventId)
      }
    },
  },
})
