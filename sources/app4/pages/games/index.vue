<script setup lang="ts">
import type { Game, GamesListResponse, GameFormData, GameJournee, GameTeam } from '~/types/games'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t, locale } = useI18n()
const route = useRoute()
const api = useApi()
const toast = useToast()
const workContext = useWorkContextStore()
const authStore = useAuthStore()
const { bracketLabels } = useBracketDisplay()

// ─── LocalStorage filter persistence ───
const FILTERS_STORAGE_KEY = 'app4_games_filters'

interface SavedFilters {
  selectedTour: string
  selectedJournee: string
  selectedDate: string
  selectedTerrain: string
  selectedSort: string
  unlockedOnly: boolean
}

function loadSavedFilters(): Partial<SavedFilters> {
  try {
    const raw = localStorage.getItem(FILTERS_STORAGE_KEY)
    if (raw) return JSON.parse(raw)
  } catch { /* ignore */ }
  return {}
}

function saveFilters() {
  try {
    const data: SavedFilters = {
      selectedTour: selectedTour.value,
      selectedJournee: selectedJournee.value,
      selectedDate: selectedDate.value,
      selectedTerrain: selectedTerrain.value,
      selectedSort: selectedSort.value,
      unlockedOnly: unlockedOnly.value,
    }
    localStorage.setItem(FILTERS_STORAGE_KEY, JSON.stringify(data))
  } catch { /* ignore */ }
}

// ─── State ───
const loading = ref(false)
const games = ref<Game[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(50)
const totalPages = ref(0)
const phaseLibelle = ref(false)
const availableDates = ref<string[]>([])

// Filters (restored from localStorage)
const saved = loadSavedFilters()
const searchQuery = ref('')
const selectedTour = ref(saved.selectedTour ?? '')
const selectedJournee = ref(saved.selectedJournee ?? '*')
const selectedDate = ref(saved.selectedDate ?? '')
const selectedTerrain = ref(saved.selectedTerrain ?? '')
const selectedSort = ref(saved.selectedSort ?? 'date_time_terrain')
const unlockedOnly = ref(saved.unlockedOnly ?? false)

// Filter data
const journees = ref<GameJournee[]>([])

// Selection
const selectedIds = ref<number[]>([])

// Modals
const formModalOpen = ref(false)
const deleteConfirmOpen = ref(false)
const bulkDeleteConfirmOpen = ref(false)
const bulkPublishConfirmOpen = ref(false)
const bulkLockConfirmOpen = ref(false)
const bulkLockPublishConfirmOpen = ref(false)
const statusConfirmOpen = ref(false)
const bulkChangeJourneeOpen = ref(false)
const bulkRenumberOpen = ref(false)
const bulkChangeDateOpen = ref(false)
const bulkIncrementTimeOpen = ref(false)
const bulkChangeGroupOpen = ref(false)

// Bulk action form data
const bulkJourneeId = ref<number | null>(null)
const bulkJourneeOptions = ref<GameJournee[]>([])
const bulkRenumberFrom = ref(1)
const bulkNewDate = ref('')
const bulkStartTime = ref('10:00')
const bulkInterval = ref(40)
const bulkOldGroup = ref('')
const bulkNewGroup = ref('')

// Form
const editingGame = ref<Game | null>(null)
const formData = ref<GameFormData>(getDefaultFormData())
const formError = ref('')
const formSaving = ref(false)
const formTeams = ref<GameTeam[]>([])

// Inline editing
const editingCell = ref<{ id: number; field: string } | null>(null)
const editingValue = ref('')
const editingOriginalValue = ref('')

// For team inline: teams loaded per journee
const inlineTeams = ref<GameTeam[]>([])

// Status toggle pending
const statusGame = ref<Game | null>(null)

// Bulk actions dropdown
const bulkActionsOpen = ref(false)
const bulkActionsRef = ref<HTMLDivElement | null>(null)

// Documents dropdown
const documentsOpen = ref(false)
const documentsRef = ref<HTMLDivElement | null>(null)

// ─── Legacy base URL ───
const legacyBase = computed(() => useRuntimeConfig().public.legacyBaseUrl || 'https://kpi.localhost')

// ─── Permissions ───
const canEdit = computed(() => authStore.profile <= 6)
const canEditScores = computed(() => authStore.profile <= 9)
const canLock = computed(() => authStore.profile <= 4)
const canSelect = computed(() => authStore.profile <= 6)
// ─── Default form data ───
function getDefaultFormData(): GameFormData {
  return {
    idJournee: null,
    dateMatch: '',
    heureMatch: '',
    numeroOrdre: null,
    terrain: '',
    type: 'C',
    intervalle: 40,
    libelle: '',
    idEquipeA: null,
    idEquipeB: null,
    coeffA: 1,
    coeffB: 1,
    arbitrePrincipal: '',
    matricArbitrePrincipal: 0,
    arbitreSecondaire: '',
    matricArbitreSecondaire: 0,
  }
}

// ─── Computed: Filtered games (client-side unlocked filter) ───
const filteredGames = computed(() => {
  if (!unlockedOnly.value) return games.value
  return games.value.filter(g => g.validation !== 'O')
})

// ─── Load data ───
const loadGames = async (keepSelection = false) => {
  if (!workContext.season) return

  loading.value = true
  try {
    const params: Record<string, string | number> = {
      season: workContext.season,
      page: page.value,
      limit: limit.value,
      sort: selectedSort.value,
    }

    // Competition filter
    if (workContext.pageCompetitionCodeAll) {
      params.competitions = workContext.pageCompetitionCodeAll
    } else if (workContext.pageEventGroupType === 'group') {
      const group = workContext.uniqueGroups.find(g => g.code === workContext.pageEventGroupValue)
      if (group) {
        const contextCodes = new Set(workContext.competitionCodes)
        const groupCodes = group.competitions.filter(c => contextCodes.has(c))
        if (groupCodes.length > 0) params.competitions = groupCodes.join(',')
      }
    } else if (workContext.pageEventGroupType === 'event') {
      params.event = workContext.pageEventGroupValue
    } else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }
    if (selectedTour.value) {
      params.tour = selectedTour.value
    }
    if (selectedJournee.value && selectedJournee.value !== '*') {
      params.journee = selectedJournee.value
    }
    if (selectedDate.value) {
      params.date = selectedDate.value
    }
    if (selectedTerrain.value) {
      params.terrain = selectedTerrain.value
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    const response = await api.get<GamesListResponse>('/admin/games', params)
    games.value = response.games
    total.value = response.total
    totalPages.value = response.totalPages
    phaseLibelle.value = response.phaseLibelle
    availableDates.value = response.dates || []

    if (keepSelection) {
      // After bulk action: keep only IDs that still exist in the reloaded data
      const loadedIds = new Set(response.games.map((g: Game) => g.id))
      selectedIds.value = selectedIds.value.filter(id => loadedIds.has(id))
    } else {
      // Clear selection on data change (filter/pagination/sort change)
      selectedIds.value = []
    }
  } catch (error) {
    console.error('Error loading games:', error)
  } finally {
    loading.value = false
  }
}


const loadJournees = async () => {
  if (!workContext.season) return
  try {
    const params: Record<string, string> = { season: workContext.season }
    // Competition filter (same logic as loadGames)
    if (workContext.pageCompetitionCodeAll) {
      params.competitions = workContext.pageCompetitionCodeAll
    } else if (workContext.pageEventGroupType === 'group') {
      const group = workContext.uniqueGroups.find(g => g.code === workContext.pageEventGroupValue)
      if (group) {
        const contextCodes = new Set(workContext.competitionCodes)
        const groupCodes = group.competitions.filter(c => contextCodes.has(c))
        if (groupCodes.length > 0) params.competitions = groupCodes.join(',')
      }
    } else if (workContext.pageEventGroupType === 'event') {
      params.event = workContext.pageEventGroupValue
    } else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }
    if (selectedTour.value) params.tour = selectedTour.value

    const data = await api.get<{ journees: GameJournee[] }>('/admin/games/journees', params)
    journees.value = data.journees || []
  } catch {
    // Silently fail
  }
}

const loadTeamsForJournee = async (journeeId: number) => {
  try {
    const data = await api.get<{ teams: GameTeam[] }>('/admin/games/teams', { journeeId })
    return data.teams || []
  } catch {
    return []
  }
}

// ─── Init from route query ───
const router = useRouter()
// Track whether initial load is complete (to distinguish mount-time vs user-driven changes)
let initialLoadDone = false

onMounted(async () => {
  await workContext.initContext()

  const phaseFromQuery = route.query.phase as string
  if (phaseFromQuery) {
    selectedJournee.value = phaseFromQuery
    // Remove query param from URL without triggering navigation
    router.replace({ query: { ...route.query, phase: undefined } })
  } else {
    // No explicit phase requested: reset competition-specific filters
    // to avoid stale localStorage values from a different competition
    selectedJournee.value = '*'
    selectedDate.value = ''
    selectedTerrain.value = ''
  }

  initialLoadDone = true
})

// ─── Watchers ───
watch(() => [workContext.initialized, workContext.season], () => {
  if (workContext.initialized && workContext.season) {
    loadGames()
    loadJournees()
  }
}, { immediate: true })

watch([page, limit, selectedSort], () => {
  loadGames()
})

watch([() => workContext.pageCompetitionCodeAll, () => workContext.pageEventGroupSelection, selectedTour, selectedJournee, selectedDate, selectedTerrain], () => {
  page.value = 1
  loadGames()
})

// Reset journee when competition/event/tour changes (user-driven, not initial load)
watch([() => workContext.pageCompetitionCodeAll, () => workContext.pageEventGroupSelection, selectedTour], () => {
  if (!initialLoadDone) return
  selectedJournee.value = '*'
  selectedDate.value = ''
  selectedTerrain.value = ''
  loadJournees()
})

// Persist filters to localStorage
watch([selectedTour, selectedJournee, selectedDate, selectedTerrain, selectedSort, unlockedOnly], () => {
  saveFilters()
})

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadGames()
  }, 300)
})

// Close dropdowns on outside click
const onClickOutside = (e: MouseEvent) => {
  if (bulkActionsRef.value && !bulkActionsRef.value.contains(e.target as Node)) {
    bulkActionsOpen.value = false
  }
  if (documentsRef.value && !documentsRef.value.contains(e.target as Node)) {
    documentsOpen.value = false
  }
}
onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))

// ─── Format helpers ───
const formatDate = (date: string | null) => {
  if (!date) return '-'
  const d = new Date(date + 'T00:00:00')
  if (locale.value === 'fr') {
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
  }
  return date
}

const formatDateShort = (date: string | null) => {
  if (!date) return ''
  const d = new Date(date + 'T00:00:00')
  if (locale.value === 'fr') {
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
  }
  const parts = date.split('-')
  return `${parts[1]}-${parts[2]}`
}

// ─── Row state helpers ───
const isLocked = (game: Game) => game.validation === 'O'
const hasScore = (game: Game) => !!(game.scoreA || game.scoreB)
const isGameEditable = (game: Game) => canEdit.value && !isLocked(game) && game.authorized
const isScoreEditable = (game: Game) => canEditScores.value && !isLocked(game) && game.authorized
const isDeletable = (game: Game) => isGameEditable(game) && !hasScore(game)

// ─── Selection ───
const toggleSelectAll = () => {
  if (selectedIds.value.length === filteredGames.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = filteredGames.value.map(g => g.id)
  }
}

const toggleSelect = (id: number) => {
  const idx = selectedIds.value.indexOf(id)
  if (idx > -1) {
    selectedIds.value.splice(idx, 1)
  } else {
    selectedIds.value.push(id)
  }
}

// ─── Toggle Publication ───
const togglePublication = async (game: Game) => {
  const oldValue = game.publication
  game.publication = game.publication === 'O' ? 'N' : 'O' // optimistic
  try {
    const response = await api.patch<{ publication: string }>(`/admin/games/${game.id}/publication`)
    game.publication = response.publication
  } catch {
    game.publication = oldValue
  }
}

// ─── Toggle Validation/Lock ───
const toggleValidation = async (game: Game) => {
  const oldValue = game.validation
  game.validation = game.validation === 'O' ? 'N' : 'O' // optimistic
  try {
    const response = await api.patch<{ validation: string }>(`/admin/games/${game.id}/validation`)
    game.validation = response.validation
  } catch {
    game.validation = oldValue
  }
}

// ─── Toggle Type C/E ───
const toggleType = async (game: Game) => {
  const oldValue = game.type
  game.type = game.type === 'C' ? 'E' : 'C' // optimistic
  try {
    const response = await api.patch<{ type: string }>(`/admin/games/${game.id}/type`)
    game.type = response.type
  } catch {
    game.type = oldValue
  }
}

// ─── Toggle Statut ───
const openStatusConfirm = (game: Game) => {
  statusGame.value = game
  statusConfirmOpen.value = true
}

const confirmToggleStatut = async () => {
  if (!statusGame.value) return
  const game = statusGame.value
  statusConfirmOpen.value = false
  try {
    const response = await api.patch<{ statut: string }>(`/admin/games/${game.id}/statut`)
    game.statut = response.statut
  } catch {
    // Error already shown
  }
}

// ─── Toggle Printed ───
const togglePrinted = async (game: Game) => {
  const oldValue = game.imprime
  game.imprime = game.imprime === 'O' ? 'N' : 'O' // optimistic
  try {
    const response = await api.patch<{ imprime: string }>(`/admin/games/${game.id}/printed`)
    game.imprime = response.imprime
  } catch {
    game.imprime = oldValue
  }
}

// ─── Inline Editing ───

// Custom directive: auto-focus, select content, and open pickers when mounted.
// On mobile, nextTick + getElementById loses the user-gesture context so focus() is ignored.
// A directive's mounted hook fires on DOM insertion, which is reliable on all devices.
const vInlineFocus = {
  mounted(el: HTMLElement) {
    el.focus()
    if (el instanceof HTMLInputElement && el.type !== 'date' && el.type !== 'time') {
      el.select()
    }
    if (el instanceof HTMLInputElement && (el.type === 'date' || el.type === 'time')) {
      try { el.showPicker() } catch { /* ignore */ }
    }
    if (el instanceof HTMLSelectElement) {
      try { el.showPicker() } catch { /* ignore */ }
    }
  },
}

const inlineFieldMap: Record<string, keyof Game> = {
  Numero_ordre: 'numeroOrdre',
  Date_match: 'dateMatch',
  Heure_match: 'heureMatch',
  Libelle: 'libelle',
  Terrain: 'terrain',
  ScoreA: 'scoreA',
  ScoreB: 'scoreB',
}

const startInlineEdit = (game: Game, field: string) => {
  // Check permissions
  if (field === 'ScoreA' || field === 'ScoreB') {
    if (!isScoreEditable(game)) return
  } else {
    if (!isGameEditable(game)) return
  }

  editingCell.value = { id: game.id, field }
  const prop = inlineFieldMap[field]
  let val = prop ? String(game[prop] ?? '') : ''
  if (field === 'Date_match' && val.length > 10) {
    val = val.substring(0, 10)
  }
  editingValue.value = val
  editingOriginalValue.value = val
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return
  const { id, field } = editingCell.value
  const value = editingValue.value

  editingCell.value = null

  if (value === editingOriginalValue.value) return

  try {
    await api.patch(`/admin/games/${id}/inline`, { field, value })
    const game = games.value.find(g => g.id === id)
    if (game) {
      const prop = inlineFieldMap[field]
      if (prop) {
        const numericFields = ['numeroOrdre']
        if (numericFields.includes(prop)) {
          ;(game as any)[prop] = value ? parseInt(value) : null
        } else {
          ;(game as any)[prop] = value || null
        }
      }
    }
    toast.add({ title: t('common.saved'), color: 'success', timeout: 2000 })
  } catch {
    // Error already shown by useApi
  }
}

const cancelInlineEdit = () => {
  editingCell.value = null
}

const handleInlineKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') saveInlineEdit()
  else if (e.key === 'Escape') cancelInlineEdit()
}

const onScoreInput = () => {
  editingValue.value = editingValue.value.toUpperCase().replace(/[^0-9F]/g, '')
}

// ─── Inline Team editing ───
const startTeamEdit = async (game: Game, team: 'A' | 'B') => {
  if (!isGameEditable(game)) return
  editingCell.value = { id: game.id, field: `Team_${team}` }
  const currentId = team === 'A' ? game.idEquipeA : game.idEquipeB
  editingValue.value = currentId ? String(currentId) : ''
  editingOriginalValue.value = editingValue.value

  // Load teams for the journee
  inlineTeams.value = await loadTeamsForJournee(game.idJournee)
}

const saveTeamEdit = async () => {
  if (!editingCell.value) return
  const { id, field } = editingCell.value
  const value = editingValue.value
  editingCell.value = null

  if (value === editingOriginalValue.value) return

  const team = field.replace('Team_', '') as 'A' | 'B'
  const idEquipe = value ? parseInt(value) : 0

  try {
    const response = await api.patch<{ idEquipe: number | null; equipe: string | null }>(`/admin/games/${id}/team`, {
      team,
      idEquipe,
    })
    const game = games.value.find(g => g.id === id)
    if (game) {
      if (team === 'A') {
        game.idEquipeA = response.idEquipe
        game.equipeA = response.equipe
      } else {
        game.idEquipeB = response.idEquipe
        game.equipeB = response.equipe
      }
    }
    toast.add({ title: t('common.saved'), color: 'success', timeout: 2000 })
  } catch {
    // Error already shown
  }
}

const handleTeamKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') cancelInlineEdit()
}

// ─── Referee helpers ───
const extractTeamFromReferee = (text: string): string => {
  const match = text.match(/\(([^)]+)\)/)
  return match ? match[1] : ''
}

const updateRefereeTeam = (position: 'principal' | 'secondaire', newTeam: string) => {
  const field = position === 'principal' ? 'arbitrePrincipal' : 'arbitreSecondaire'
  const current = formData.value[field]
  if (current.includes('(')) {
    formData.value[field] = current.replace(/\([^)]*\)/, `(${newTeam})`)
  }
  else {
    formData.value[field] = `${current} (${newTeam})`
  }
}

// ─── Inline Referee editing ───
const editingRefereeMatric = ref(0)

const startRefereeEdit = (game: Game, position: 'principal' | 'secondaire') => {
  if (!isGameEditable(game)) return
  const field = position === 'principal' ? 'Referee_1' : 'Referee_2'
  editingCell.value = { id: game.id, field }
  const currentValue = position === 'principal' ? (game.arbitrePrincipal || '') : (game.arbitreSecondaire || '')
  const currentMatric = position === 'principal' ? (game.matricArbitrePrincipal || 0) : (game.matricArbitreSecondaire || 0)
  editingValue.value = currentValue
  editingOriginalValue.value = currentValue
  editingRefereeMatric.value = currentMatric
}

const saveRefereeEdit = async (value: string, matric: number) => {
  if (!editingCell.value) return
  const { id, field } = editingCell.value
  editingCell.value = null

  const dbField = field === 'Referee_1' ? 'Arbitre_principal' : 'Arbitre_secondaire'

  try {
    await api.patch(`/admin/games/${id}/inline`, { field: dbField, value, matric })
    const game = games.value.find(g => g.id === id)
    if (game) {
      if (field === 'Referee_1') {
        game.arbitrePrincipal = value || null
        game.matricArbitrePrincipal = matric
      }
      else {
        game.arbitreSecondaire = value || null
        game.matricArbitreSecondaire = matric
      }
    }
    toast.add({ title: t('common.saved'), color: 'success', timeout: 2000 })
  }
  catch {
    // Error already shown by useApi
  }
}

const onRefereeConfirm = () => {
  if (!editingCell.value) return
  saveRefereeEdit(editingValue.value, editingRefereeMatric.value)
}

// ─── Inline Phase/Journee editing ───
const startPhaseEdit = (game: Game) => {
  if (!isGameEditable(game)) return
  editingCell.value = { id: game.id, field: 'Phase' }
  editingValue.value = String(game.idJournee)
  editingOriginalValue.value = editingValue.value
}

const savePhaseEdit = async () => {
  if (!editingCell.value) return
  const { id } = editingCell.value
  const value = editingValue.value
  editingCell.value = null

  if (value === editingOriginalValue.value) return

  const newJourneeId = parseInt(value)
  if (!newJourneeId || newJourneeId <= 0) return

  try {
    await api.patch(`/admin/games/${id}/journee`, { idJournee: newJourneeId })
    // Reload to get updated data
    await loadGames()
    toast.add({ title: t('common.saved'), color: 'success', timeout: 2000 })
  } catch {
    // Error already shown
  }
}

// ─── Modal: Add/Edit ───
const openAddModal = async () => {
  editingGame.value = null
  formData.value = getDefaultFormData()
  formError.value = ''

  // Pre-select journee if one is selected in filter
  if (selectedJournee.value && selectedJournee.value !== '*') {
    formData.value.idJournee = parseInt(selectedJournee.value)
    // Load teams for this journee
    formTeams.value = await loadTeamsForJournee(formData.value.idJournee)
    // Set type from journee
    const j = journees.value.find(jn => jn.id === formData.value.idJournee)
    if (j) formData.value.type = j.type || 'C'
  }

  formModalOpen.value = true
}

const openEditModal = async (game: Game) => {
  editingGame.value = game
  formData.value = {
    idJournee: game.idJournee,
    dateMatch: game.dateMatch || '',
    heureMatch: game.heureMatch || '',
    numeroOrdre: game.numeroOrdre,
    terrain: game.terrain || '',
    type: game.type || 'C',
    intervalle: 40,
    libelle: game.libelle || '',
    idEquipeA: game.idEquipeA,
    idEquipeB: game.idEquipeB,
    coeffA: game.coeffA || 1,
    coeffB: game.coeffB || 1,
    arbitrePrincipal: game.arbitrePrincipal || '',
    matricArbitrePrincipal: game.matricArbitrePrincipal || 0,
    arbitreSecondaire: game.arbitreSecondaire || '',
    matricArbitreSecondaire: game.matricArbitreSecondaire || 0,
  }
  formError.value = ''
  formTeams.value = await loadTeamsForJournee(game.idJournee)
  formModalOpen.value = true
}

const onFormJourneeChange = async () => {
  if (formData.value.idJournee && formData.value.idJournee > 0) {
    formTeams.value = await loadTeamsForJournee(formData.value.idJournee)
    // Inherit type from journee
    const j = journees.value.find(jn => jn.id === formData.value.idJournee)
    if (j) formData.value.type = j.type || 'C'
  } else {
    formTeams.value = []
  }
}

const submitForm = async () => {
  formError.value = ''

  if (!formData.value.idJournee || formData.value.idJournee <= 0) {
    formError.value = t('games.select_journee')
    return
  }
  if (!formData.value.dateMatch) {
    formError.value = t('games.date_empty')
    return
  }

  formSaving.value = true
  try {
    if (editingGame.value) {
      await api.put(`/admin/games/${editingGame.value.id}`, formData.value)
      toast.add({ title: t('common.success'), description: t('games.updated'), color: 'success' })
    } else {
      await api.post('/admin/games', formData.value)
      toast.add({ title: t('common.success'), description: t('games.added'), color: 'success' })
    }
    formModalOpen.value = false
    await loadGames()
  } catch (error: any) {
    formError.value = error.message || t('common.error')
  } finally {
    formSaving.value = false
  }
}

// ─── Delete ───
const gameToDelete = ref<Game | null>(null)

const openDeleteConfirm = (game: Game) => {
  gameToDelete.value = game
  deleteConfirmOpen.value = true
}

const confirmDelete = async () => {
  if (!gameToDelete.value) return
  formSaving.value = true
  try {
    await api.del(`/admin/games/${gameToDelete.value.id}`)
    toast.add({ title: t('common.success'), description: t('games.deleted'), color: 'success' })
    deleteConfirmOpen.value = false
    await loadGames()
  } catch (error: any) {
    const code = error?.code
    if (code === 'HAS_EVENTS') {
      toast.add({ title: t('common.error'), description: t('games.delete_error_events'), color: 'error' })
    } else if (code === 'LOCKED') {
      toast.add({ title: t('common.error'), description: t('games.delete_error_locked'), color: 'error' })
    } else if (code === 'HAS_SCORE') {
      toast.add({ title: t('common.error'), description: t('games.delete_error_score'), color: 'error' })
    }
    deleteConfirmOpen.value = false
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Actions ───
const confirmBulkDelete = async () => {
  formSaving.value = true
  try {
    const response = await api.del<{ deleted: number; skipped: any[] }>('/admin/games/bulk', { ids: selectedIds.value })
    const msg = response.skipped?.length > 0
      ? t('games.bulk_deleted_partial', { deleted: response.deleted, skipped: response.skipped.length })
      : t('games.bulk_deleted', { deleted: response.deleted })
    toast.add({ title: t('common.success'), description: msg, color: 'success' })
    bulkDeleteConfirmOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const confirmBulkPublish = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/publication', { ids: selectedIds.value })
    toast.add({ title: t('common.success'), description: t('games.bulk_published', { count: response.updated }), color: 'success' })
    bulkPublishConfirmOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const confirmBulkLock = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/validation', { ids: selectedIds.value })
    toast.add({ title: t('common.success'), description: t('games.bulk_locked', { count: response.updated }), color: 'success' })
    bulkLockConfirmOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const confirmBulkLockPublish = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/lock-publish', { ids: selectedIds.value })
    toast.add({ title: t('common.success'), description: t('games.bulk_lock_published', { count: response.updated }), color: 'success' })
    bulkLockPublishConfirmOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Toggle Printed ───
const bulkTogglePrinted = async () => {
  for (const id of selectedIds.value) {
    const game = games.value.find(g => g.id === id)
    if (game) await togglePrinted(game)
  }
}

// ─── Bulk Change Journée ───
const openBulkChangeJournee = async () => {
  bulkActionsOpen.value = false
  bulkJourneeId.value = null
  // Load journées from same competition context
  bulkJourneeOptions.value = journees.value
  bulkChangeJourneeOpen.value = true
}

const confirmBulkChangeJournee = async () => {
  if (!bulkJourneeId.value) return
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/journee', {
      ids: selectedIds.value,
      journeeId: bulkJourneeId.value,
    })
    toast.add({ title: t('common.success'), description: t('games.bulk_journee_changed', { count: response.updated }), color: 'success' })
    bulkChangeJourneeOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Renumber ───
const openBulkRenumber = () => {
  bulkActionsOpen.value = false
  bulkRenumberFrom.value = 1
  bulkRenumberOpen.value = true
}

const confirmBulkRenumber = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/renumber', {
      ids: selectedIds.value,
      startNumber: bulkRenumberFrom.value,
    })
    toast.add({ title: t('common.success'), description: t('games.bulk_renumbered', { count: response.updated }), color: 'success' })
    bulkRenumberOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Change Date ───
const openBulkChangeDate = () => {
  bulkActionsOpen.value = false
  // Default to first selected game's date
  const firstGame = games.value.find(g => selectedIds.value.includes(g.id))
  bulkNewDate.value = firstGame?.dateMatch?.substring(0, 10) || ''
  bulkChangeDateOpen.value = true
}

const confirmBulkChangeDate = async () => {
  if (!bulkNewDate.value) return
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/date', {
      ids: selectedIds.value,
      date: bulkNewDate.value,
    })
    toast.add({ title: t('common.success'), description: t('games.bulk_date_changed', { count: response.updated }), color: 'success' })
    bulkChangeDateOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Increment Time ───
const openBulkIncrementTime = () => {
  bulkActionsOpen.value = false
  bulkStartTime.value = '10:00'
  bulkInterval.value = 40
  bulkIncrementTimeOpen.value = true
}

const confirmBulkIncrementTime = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/time', {
      ids: selectedIds.value,
      startTime: bulkStartTime.value,
      interval: bulkInterval.value,
    })
    toast.add({ title: t('common.success'), description: t('games.bulk_time_changed', { count: response.updated }), color: 'success' })
    bulkIncrementTimeOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Change Group ───
const openBulkChangeGroup = () => {
  bulkActionsOpen.value = false
  bulkOldGroup.value = ''
  bulkNewGroup.value = ''
  bulkChangeGroupOpen.value = true
}

const forceUppercaseLetters = (event: Event) => {
  const input = event.target as HTMLInputElement
  input.value = input.value.replace(/[^A-Za-z]/g, '').toUpperCase()
  // Update the corresponding ref based on input name
  if (input.name === 'oldGroup') bulkOldGroup.value = input.value
  else if (input.name === 'newGroup') bulkNewGroup.value = input.value
}

const confirmBulkChangeGroup = async () => {
  if (!bulkOldGroup.value || !bulkNewGroup.value) return
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/games/bulk/group', {
      ids: selectedIds.value,
      oldGroup: bulkOldGroup.value,
      newGroup: bulkNewGroup.value,
    })
    toast.add({ title: t('common.success'), description: t('games.bulk_group_changed', { count: response.updated }), color: 'success' })
    bulkChangeGroupOpen.value = false
    await loadGames(true)
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Match Sheets (PDF) ───
const openBulkMatchSheets = () => {
  bulkActionsOpen.value = false
  const ids = selectedIds.value.join(',')
  window.open(`${legacyBase.value}/admin/FeuilleMatchMulti.php?listMatch=${ids}`, '_blank')
}

// ─── Document export URLs (all games with current filters) ───
// Les fichiers PHP cibles utilisent $urlMode pour ignorer les variables de session
// lorsque des paramètres GET de filtre sont présents (Compet, idEvenement, S).
const docBaseParams = computed(() => {
  const params = new URLSearchParams()
  params.set('S', workContext.season || '')

  if (workContext.pageEventGroupType === 'event') {
    // Event selected → pass idEvenement only (no Compet, it would override)
    params.set('idEvenement', workContext.pageEventGroupValue)
  } else if (workContext.pageCompetitionCodeAll) {
    // A specific single competition selected
    params.set('Compet', workContext.pageCompetitionCodeAll)
  }
  // When "All competitions" with a group or no specific filter: pass S alone to trigger urlMode

  return params.toString()
})

const docUrl = (file: string, isPublic = false) => {
  const folder = isPublic ? '' : 'admin/'
  return `${legacyBase.value}/${folder}${file}?${docBaseParams.value}`
}

// ─── Journee label for dropdown ───
const journeeLabel = (j: GameJournee) => {
  if (j.codeTypeclt === 'CP') {
    return `[${j.id}] ${j.codeCompetition} (${j.etape}) ${j.phase || ''}`
  }
  return `[${j.id}] ${j.codeCompetition} ${j.dateDebut ? formatDate(j.dateDebut) : ''} ${j.lieu || ''}`
}

// ─── Status label ───
const statusLabel = (game: Game) => {
  switch (game.statut) {
    case 'ON': return t('games.status_ongoing')
    case 'END': return t('games.status_ended')
    default: return t('games.status_waiting')
  }
}

const statusColor = (game: Game) => {
  switch (game.statut) {
    case 'ON': return 'text-green-600'
    case 'END': return 'text-red-600'
    default: return 'text-gray-400'
  }
}

const statusBtnClass = (game: Game) => {
  switch (game.statut) {
    case 'ON': return 'bg-blue-100 text-blue-700 border-blue-200'
    case 'END': return 'bg-green-100 text-green-700 border-green-200'
    default: return 'bg-gray-100 text-gray-600 border-gray-200'
  }
}
</script>

<template>
  <div class="space-y-4">
    <!-- Page header -->
    <AdminPageHeader
      :title="t('games.title')"
      :show-all-option="true"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
      @event-group-change="() => { page = 1 }"
      @competition-change="() => { page = 1 }"
    >
      <template #filters>
        <!-- Tour -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.filter_round') }}</label>
          <select v-model="selectedTour" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">{{ t('games.all_rounds') }}</option>
            <option v-for="n in 5" :key="n" :value="String(n)">{{ t('games.round_n', { n }) }}</option>
          </select>
        </div>

        <!-- Journee -->
        <div class="min-w-48">
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.filter_journee') }}</label>
          <select v-model="selectedJournee" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="*">{{ t('games.all_journees') }}</option>
            <option v-for="j in journees" :key="j.id" :value="String(j.id)">{{ journeeLabel(j) }}</option>
          </select>
        </div>

        <!-- Date -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.filter_date') }}</label>
          <select v-model="selectedDate" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">{{ t('games.all_dates') }}</option>
            <option v-for="d in availableDates" :key="d" :value="d">{{ formatDate(d) }}</option>
          </select>
        </div>

        <!-- Terrain -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.filter_terrain') }}</label>
          <select v-model="selectedTerrain" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">{{ t('games.all_terrains') }}</option>
            <option v-for="n in 8" :key="n" :value="String(n)">{{ n }}</option>
          </select>
        </div>

        <!-- Sort -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.filter_sort') }}</label>
          <select v-model="selectedSort" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="date_time_terrain">{{ t('games.sort.date_time_terrain') }}</option>
            <option value="competition_date">{{ t('games.sort.competition_date') }}</option>
            <option value="competition_phase">{{ t('games.sort.competition_phase') }}</option>
            <option value="terrain_date">{{ t('games.sort.terrain_date') }}</option>
            <option value="number">{{ t('games.sort.number') }}</option>
          </select>
        </div>

        <!-- Unlocked only checkbox -->
        <label class="flex items-center gap-1.5 px-3 py-2 text-sm cursor-pointer" :class="unlockedOnly ? 'text-blue-700 font-medium' : 'text-gray-600'">
          <input v-model="unlockedOnly" type="checkbox" class="rounded border-gray-300 text-blue-600">
          {{ t('games.unlocked_only') }}
        </label>

        <!-- Loading spinner -->
        <UIcon v-if="loading" name="heroicons:arrow-path" class="w-5 h-5 text-blue-500 animate-spin" />
      </template>
    </AdminPageHeader>

    <!-- ═══════ TOOLBAR ═══════ -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('games.search_placeholder')"
      :add-label="t('games.add')"
      :show-add="canEdit"
      :selected-count="selectedIds.length"
      @add="openAddModal"
    >
      <template #left>
        <!-- Bulk actions dropdown -->
        <div v-if="canSelect && selectedIds.length > 0" ref="bulkActionsRef" class="relative">
          <button
            class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100"
            @click="bulkActionsOpen = !bulkActionsOpen"
          >
            <UIcon name="heroicons:bolt" class="w-6 h-6" />
            {{ t('games.bulk.actions') }}
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {{ selectedIds.length }}
            </span>
            <UIcon name="heroicons:chevron-down" class="w-6 h-6 transition-transform" :class="{ 'rotate-180': bulkActionsOpen }" />
          </button>
          <div v-show="bulkActionsOpen" class="absolute z-20 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-lg py-1 left-0">
            <!-- ── Toggle section ── -->
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.bulk.toggle_section') }}</div>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="bulkPublishConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:eye" class="w-5 h-5 text-green-600" />
              {{ t('games.bulk.publish') }}
            </button>
            <button
              v-if="canLock"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="bulkLockConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:lock-closed" class="w-5 h-5 text-blue-600" />
              {{ t('games.bulk.lock') }}
            </button>
            <button
              v-if="canLock"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="bulkLockPublishConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:lock-closed" class="w-5 h-5 text-purple-600" />
              {{ t('games.bulk.lock_publish') }}
            </button>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="bulkTogglePrinted(); bulkActionsOpen = false"
            >
              <UIcon name="heroicons:printer" class="w-5 h-5 text-gray-600" />
              {{ t('games.bulk.toggle_printed') }}
            </button>

            <!-- ── Edit section ── -->
            <div class="border-t border-gray-100 my-1" />
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.bulk.edit_section') }}</div>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkChangeJournee"
            >
              <UIcon name="heroicons:arrow-right-circle" class="w-5 h-5 text-indigo-600" />
              {{ t('games.bulk.change_journee') }}
            </button>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkRenumber"
            >
              <UIcon name="heroicons:hashtag" class="w-5 h-5 text-orange-600" />
              {{ t('games.bulk.renumber') }}
            </button>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkChangeDate"
            >
              <UIcon name="heroicons:calendar" class="w-5 h-5 text-blue-600" />
              {{ t('games.bulk.change_date') }}
            </button>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkIncrementTime"
            >
              <UIcon name="heroicons:clock" class="w-5 h-5 text-teal-600" />
              {{ t('games.bulk.increment_time') }}
            </button>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkChangeGroup"
            >
              <UIcon name="heroicons:arrow-path" class="w-5 h-5 text-amber-600" />
              {{ t('games.bulk.change_group') }}
            </button>

            <!-- ── Documents section ── -->
            <div class="border-t border-gray-100 my-1" />
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.bulk.documents_section') }}</div>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="openBulkMatchSheets"
            >
              <UIcon name="heroicons:document-text" class="w-5 h-5 text-red-600" />
              {{ t('games.bulk.match_sheets') }}
            </button>

            <!-- ── Danger section ── -->
            <div class="border-t border-gray-100 my-1" />
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              @click="bulkDeleteConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:trash" class="w-5 h-5" />
              {{ t('games.bulk.delete') }}
            </button>
          </div>
        </div>
      </template>
      <template #before-search>
        <!-- Documents dropdown (all games with current filters) -->
        <div ref="documentsRef" class="relative">
          <button
            class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="documentsOpen = !documentsOpen"
          >
            <UIcon name="heroicons:document-arrow-down" class="w-5 h-5 text-gray-500" />
            {{ t('games.documents.title') }}
            <UIcon name="heroicons:chevron-down" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': documentsOpen }" />
          </button>
          <div v-show="documentsOpen" class="absolute z-20 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-lg py-1 right-0">
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.documents.admin_section') }}</div>
            <a
              :href="docUrl('FeuilleListeMatchs.php')"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:document-text" class="w-5 h-5 text-blue-600" />
              {{ t('games.documents.game_list_fr') }}
            </a>
            <a
              :href="docUrl('FeuilleListeMatchsEN.php')"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:document-text" class="w-5 h-5 text-blue-600" />
              {{ t('games.documents.game_list_en') }}
            </a>
            <a
              :href="docUrl('FeuilleMatchMulti.php')"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:document-duplicate" class="w-5 h-5 text-red-600" />
              {{ t('games.documents.scoresheets_all') }}
            </a>
            <a
              :href="docUrl('tableau_openspout.php')"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:table-cells" class="w-5 h-5 text-green-600" />
              {{ t('games.documents.export_ods') }}
            </a>
            <div class="border-t border-gray-100 my-1" />
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.documents.public_section') }}</div>
            <a
              :href="docUrl('PdfListeMatchs.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:document-text" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.public_list_fr') }}
            </a>
            <a
              :href="docUrl('PdfListeMatchsEN.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:document-text" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.public_list_en') }}
            </a>
            <div class="border-t border-gray-100 my-1" />
            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ t('games.documents.pitches_section') }}</div>
            <a
              :href="docUrl('PdfListeMatchs4TerrainsEn.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:table-cells" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.pitches_1_4_teams') }}
            </a>
            <a
              :href="docUrl('PdfListeMatchs4TerrainsEn3.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:table-cells" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.pitches_5_8_teams') }}
            </a>
            <a
              :href="docUrl('PdfListeMatchs4TerrainsEn2.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:table-cells" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.pitches_1_4_phases') }}
            </a>
            <a
              :href="docUrl('PdfListeMatchs4TerrainsEn4.php', true)"
              target="_blank"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="documentsOpen = false"
            >
              <UIcon name="heroicons:table-cells" class="w-5 h-5 text-gray-500" />
              {{ t('games.documents.pitches_5_8_phases') }}
            </a>
          </div>
        </div>
      </template>
    </AdminToolbar>

    <!-- ═══════ DESKTOP TABLE ═══════ -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
          <thead class="bg-gray-50">
            <tr>
              <!-- Checkbox -->
              <th v-if="canSelect" class="w-8 px-1 py-2">
                <input
                  type="checkbox"
                  class="rounded border-gray-300"
                  :checked="selectedIds.length === filteredGames.length && filteredGames.length > 0"
                  :indeterminate="selectedIds.length > 0 && selectedIds.length < filteredGames.length"
                  @change="toggleSelectAll"
                >
              </th>
              <!-- Publication -->
              <th class="w-8 px-1 py-2 text-center"><UIcon name="heroicons:eye" class="w-6 h-6" /></th>
              <!-- N° -->
              <th class="w-10 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.number') }}</th>
              <!-- Actions -->
              <th v-if="canEdit" colspan="2" class="w-20 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.actions') }}</th>
              <!-- Time -->
              <th class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.time') }}</th>
              <!-- Terrain -->
              <th class="w-8 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.terrain') }}</th>
              <!-- Cat -->
              <th class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.category') }}</th>
              <!-- Phase (conditional) -->
              <th v-if="phaseLibelle" class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.phase') }}</th>
              <!-- Type -->
              <th class="w-8 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.type') }}</th>
              <!-- Code (conditional) -->
              <th v-if="phaseLibelle" class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.code') }}</th>
              <!-- Code (non-phaseLibelle) -->
              <th v-if="!phaseLibelle" class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.code') }}</th>
              <!-- Lieu (non-phaseLibelle) -->
              <th v-if="!phaseLibelle" class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.location') }}</th>
              <!-- Team A -->
              <th class="px-1 py-2 text-right text-gray-500 font-medium">{{ t('games.field.team_a') }}</th>
              <!-- Score A -->
              <th class="w-8 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.score_a') }}</th>
              <!-- Lock -->
              <th class="w-10 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.lock') }}</th>
              <!-- Score B -->
              <th class="w-8 px-1 py-2 text-center text-gray-500 font-medium">{{ t('games.field.score_b') }}</th>
              <!-- Team B -->
              <th class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.team_b') }}</th>
              <!-- Referee 1 -->
              <th class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.referee_1') }}</th>
              <!-- Referee 2 -->
              <th class="px-1 py-2 text-left text-gray-500 font-medium">{{ t('games.field.referee_2') }}</th>
              <!-- Printed -->
              <th class="w-8 px-1 py-2 text-center"><UIcon name="heroicons:inbox-arrow-down" class="w-6 h-6" /></th>
              <!-- Delete -->
              <th v-if="canEdit" class="w-8 px-1 py-2" />
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading -->
            <tr v-if="loading && games.length === 0">
              <td :colspan="22" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>
            <!-- Empty -->
            <tr v-else-if="filteredGames.length === 0">
              <td :colspan="22" class="px-4 py-8 text-center text-gray-500">
                {{ t('games.no_results') }}
              </td>
            </tr>
            <!-- Rows -->
            <tr
              v-for="g in filteredGames"
              :key="g.id"
              class="hover:bg-gray-50"
              :class="{
                'bg-blue-50': selectedIds.includes(g.id),
                'bg-amber-50/50': isLocked(g),
              }"
            >
              <!-- Checkbox -->
              <td v-if="canSelect" class="px-1 py-1" @click.stop>
                <input
                  type="checkbox"
                  class="rounded border-gray-300"
                  :checked="selectedIds.includes(g.id)"
                  @change="toggleSelect(g.id)"
                >
              </td>

              <!-- Publication toggle -->
              <td class="px-1 py-1 text-center">
                <AdminToggleButton
                  :active="g.publication === 'O'"
                  active-icon="heroicons:eye-solid"
                  inactive-icon="heroicons:eye-slash"
                  active-color="green"
                  size="md"
                  :active-title="t('games.published')"
                  :inactive-title="t('games.unpublished')"
                  @toggle="canEdit && togglePublication(g)"
                />
              </td>

              <!-- N° (inline editable) -->
              <td class="px-1 py-1 text-center">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Numero_ordre'">
                  <input
                    :id="`inline-${g.id}-Numero_ordre`"
                    v-model="editingValue" v-inline-focus
                    type="tel"
                    maxlength="4"
                    class="w-10 px-0.5 py-0 text-xs text-center border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Numero_ordre')"
                  >{{ g.numeroOrdre ?? '' }}</span>
                </template>
              </td>

              <!-- Actions -->
              <td v-if="canEdit" class="px-1 py-1 text-center" @click.stop>
                <button v-if="!isLocked(g)" :title="t('common.edit')" class="text-blue-600 hover:text-blue-800" @click="openEditModal(g)">
                  <UIcon name="heroicons:pencil" class="w-6 h-6" />
                  <br>
                  <span class="text-xs text-gray-500">Edit</span>
                </button>
              </td>
              <td v-if="canEdit" class="px-1 py-1 text-center" @click.stop>
                <div class="flex items-center text-center gap-0.5">
                  <a :href="`${legacyBase}/admin/FeuilleMatchMulti.php?listMatch=${g.id}`" target="_blank" :title="t('games.scoresheet_pdf')" class="p-0.5 text-red-600 hover:text-red-800">
                    <UIcon name="heroicons:document-text" class="w-6 h-6" />
                    <br>
                    <span class="text-xs text-gray-500">PDF</span>
                  </a>
                  <a v-if="authStore.profile <= 2" :href="`${legacyBase}/admin/FeuilleMarque2.php?idMatch=${g.id}`" target="_blank" :title="t('games.scoresheet_online_v2')" class="p-0.5 text-emerald-600 hover:text-emerald-800">
                    <UIcon name="heroicons:device-tablet" class="w-6 h-6" />
                    <br>
                    <span class="text-xs text-gray-500">V2</span>
                  </a>
                  <a v-if="authStore.profile <= 2" :href="`${legacyBase}/admin/FeuilleMarque3.php?idMatch=${g.id}`" target="_blank" :title="t('games.scoresheet_online_v3')" class="p-0.5 text-purple-600 hover:text-purple-800">
                    <UIcon name="heroicons:device-tablet" class="w-6 h-6" />
                    <br>
                    <span class="text-xs text-gray-500">V3</span>
                  </a>
                </div>
              </td>

              <!-- Time (Date + Heure on same line, inline editable) -->
              <td class="px-1 py-1 whitespace-nowrap">
                <div class="flex items-center gap-1">
                  <!-- Date inline -->
                  <template v-if="editingCell?.id === g.id && editingCell.field === 'Date_match'">
                    <input
                      :id="`inline-${g.id}-Date_match`"
                      v-model="editingValue" v-inline-focus
                      type="date"
                      class="w-28 px-0.5 py-0 text-xs border border-blue-400 rounded"
                      @keydown="handleInlineKeydown"
                      @blur="saveInlineEdit"
                    >
                  </template>
                  <span
                    v-else
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    class="text-gray-500"
                    @click="startInlineEdit(g, 'Date_match')"
                  >{{ formatDateShort(g.dateMatch) }}</span>
                  <!-- Heure inline -->
                  <template v-if="editingCell?.id === g.id && editingCell.field === 'Heure_match'">
                    <input
                      :id="`inline-${g.id}-Heure_match`"
                      v-model="editingValue" v-inline-focus
                      type="time"
                      class="w-20 px-0.5 py-0 text-xs border border-blue-400 rounded"
                      @keydown="handleInlineKeydown"
                      @blur="saveInlineEdit"
                    >
                  </template>
                  <span
                    v-else
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    class="font-medium text-gray-900"
                    @click="startInlineEdit(g, 'Heure_match')"
                  >{{ g.heureMatch || '' }}</span>
                </div>
              </td>

              <!-- Terrain (inline editable) -->
              <td class="px-1 py-1 text-center">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Terrain'">
                  <input
                    :id="`inline-${g.id}-Terrain`"
                    v-model="editingValue" v-inline-focus
                    type="tel"
                    maxlength="2"
                    class="w-8 px-0.5 py-0 text-xs text-center border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Terrain')"
                  >{{ g.terrain || '' }}</span>
                </template>
              </td>

              <!-- Category -->
              <td class="px-1 py-1 text-gray-600">{{ g.soustitre2 || g.codeCompetition }}</td>

              <!-- Phase (conditional, inline editable via journee select) -->
              <td v-if="phaseLibelle" class="px-1 py-1">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Phase'">
                  <select
                    :id="`inline-${g.id}-Phase`"
                    v-model="editingValue" v-inline-focus
                    class="w-full px-0.5 py-0 text-xs border border-blue-400 rounded"
                    @change="savePhaseEdit"
                    @keydown="handleTeamKeydown"
                    @blur="savePhaseEdit"
                  >
                    <option v-for="j in journees" :key="j.id" :value="String(j.id)">{{ j.phase || j.codeCompetition }}</option>
                  </select>
                </template>
                <template v-else>
                  <span
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startPhaseEdit(g)"
                  >{{ g.phase || '-' }}</span>
                </template>
              </td>

              <!-- Type toggle -->
              <td class="px-1 py-1 text-center">
                <button
                  :title="g.type === 'C' ? t('games.type_classification') : t('games.type_elimination')"
                  class="p-0.5 rounded"
                  :class="isGameEditable(g) ? 'hover:bg-gray-100 cursor-pointer' : 'cursor-default opacity-60'"
                  :disabled="!isGameEditable(g)"
                  @click="isGameEditable(g) && toggleType(g)"
                >
                  <UIcon
                    :name="g.type === 'C' ? 'heroicons:bars-3' : 'heroicons:arrows-right-left'"
                    class="w-6 h-6"
                    :class="g.type === 'C' ? 'text-blue-600' : 'text-orange-600'"
                  />
                </button>
              </td>

              <!-- Code / Libelle (inline editable) -->
              <td class="px-1 py-1">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Libelle'">
                  <input
                    :id="`inline-${g.id}-Libelle`"
                    v-model="editingValue" v-inline-focus
                    type="text"
                    maxlength="30"
                    class="w-full px-0.5 py-0 text-xs border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Libelle')"
                  >{{ g.libelle || '-' }}</span>
                </template>
              </td>

              <!-- Lieu (non-phaseLibelle, read-only) -->
              <td v-if="!phaseLibelle" class="px-1 py-1 text-gray-500">{{ g.lieu || '-' }}</td>

              <!-- Team A (inline select, right-aligned) -->
              <td class="px-1 py-1 text-right">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Team_A'">
                  <select
                    :id="`inline-${g.id}-Team_A`"
                    v-model="editingValue" v-inline-focus
                    class="w-full px-0.5 py-0 text-xs border border-blue-400 rounded text-right"
                    @change="saveTeamEdit"
                    @keydown="handleTeamKeydown"
                    @blur="saveTeamEdit"
                  >
                    <option value="">{{ t('games.none') }}</option>
                    <option v-for="tm in inlineTeams" :key="tm.id" :value="String(tm.id)">{{ tm.libelle }}</option>
                  </select>
                </template>
                <template v-else>
                  <span
                    v-if="g.equipeA"
                    class="text-gray-900"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startTeamEdit(g, 'A')"
                  >{{ g.equipeA }}</span>
                  <span
                    v-else-if="bracketLabels(g.libelle).teamA"
                    class="text-orange-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    :title="bracketLabels(g.libelle).teamA!"
                    @click="startTeamEdit(g, 'A')"
                  >({{ bracketLabels(g.libelle).teamA }})</span>
                  <span
                    v-else
                    class="text-red-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startTeamEdit(g, 'A')"
                  >{{ t('games.team_undefined') }}</span>
                  <NuxtLink
                    v-if="g.idEquipeA"
                    :to="`/presence/match/${g.id}/team/A`"
                    class="block text-[10px] text-blue-500 hover:text-blue-700 hover:underline"
                    :title="t('presence.title_match')"
                    @click.stop
                  >
                    <UIcon name="heroicons:user-group" class="w-4 h-4 mt-2 me-4" />
                  </NuxtLink>
                </template>
              </td>

              <!-- Score A (inline editable) -->
              <td class="px-1 py-1 text-center font-bold">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'ScoreA'">
                  <input
                    :id="`inline-${g.id}-ScoreA`"
                    v-model="editingValue" v-inline-focus
                    type="text" inputmode="numeric"
                    maxlength="4"
                    class="w-10 px-0.5 py-0 text-xs text-center border border-blue-400 rounded"
                    @input="onScoreInput"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="isScoreEditable(g) ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'ScoreA')"
                  >{{ g.scoreA || '' }}</span>
                </template>
              </td>

              <!-- Lock/Validation + Statut -->
              <td class="px-1 py-1 text-center">
                <!-- Lock toggle -->
                <AdminToggleButton
                  v-if="canLock"
                  :active="g.validation === 'O'"
                  active-icon="heroicons:lock-closed-solid"
                  inactive-icon="heroicons:lock-open"
                  active-color="blue"
                  size="md"
                  :active-title="t('games.locked')"
                  :inactive-title="t('games.unlocked')"
                  @toggle="toggleValidation(g)"
                />
                <UIcon v-else-if="g.validation === 'O'" name="heroicons:lock-closed-solid" class="w-6 h-6 text-blue-500" :title="t('games.locked')" />
                <!-- Statut -->
                <div class="mt-0.5">
                  <button
                    v-if="isGameEditable(g)"
                    class="px-1.5 py-1 text-[10px] font-medium rounded-full border leading-tight text-nowrap"
                    :class="statusBtnClass(g)"
                    @click="openStatusConfirm(g)"
                  >{{ statusLabel(g) }}</button>
                  <span
                    v-else
                    class="px-1.5 py-1 text-[10px] font-medium rounded-full border leading-tight inline-block text-nowrap"
                    :class="statusBtnClass(g)"
                  >{{ statusLabel(g) }}</span>
                </div>
                <!-- Provisional score when ON or END -->
                <div v-if="(g.statut === 'ON' || g.statut === 'END') && (g.scoreDetailA || g.scoreDetailB)" class="text-[9px] text-gray-500 leading-tight">
                  {{ g.scoreDetailA || '0' }}-{{ g.scoreDetailB || '0' }}
                  <span v-if="g.periode">({{ g.periode }})</span>
                </div>
              </td>

              <!-- Score B (inline editable) -->
              <td class="px-1 py-1 text-center font-bold">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'ScoreB'">
                  <input
                    :id="`inline-${g.id}-ScoreB`"
                    v-model="editingValue" v-inline-focus
                    type="text" inputmode="numeric"
                    maxlength="4"
                    class="w-10 px-0.5 py-0 text-xs text-center border border-blue-400 rounded"
                    @input="onScoreInput"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="isScoreEditable(g) ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'ScoreB')"
                  >{{ g.scoreB || '' }}</span>
                </template>
              </td>

              <!-- Team B (inline select) -->
              <td class="px-1 py-1">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Team_B'">
                  <select
                    :id="`inline-${g.id}-Team_B`"
                    v-model="editingValue" v-inline-focus
                    class="w-full px-0.5 py-0 text-xs border border-blue-400 rounded"
                    @change="saveTeamEdit"
                    @keydown="handleTeamKeydown"
                    @blur="saveTeamEdit"
                  >
                    <option value="">{{ t('games.none') }}</option>
                    <option v-for="tm in inlineTeams" :key="tm.id" :value="String(tm.id)">{{ tm.libelle }}</option>
                  </select>
                </template>
                <template v-else>
                  <span
                    v-if="g.equipeB"
                    class="text-gray-900"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startTeamEdit(g, 'B')"
                  >{{ g.equipeB }}</span>
                  <span
                    v-else-if="bracketLabels(g.libelle).teamB"
                    class="text-orange-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    :title="bracketLabels(g.libelle).teamB!"
                    @click="startTeamEdit(g, 'B')"
                  >({{ bracketLabels(g.libelle).teamB }})</span>
                  <span
                    v-else
                    class="text-red-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    @click="startTeamEdit(g, 'B')"
                  >{{ t('games.team_undefined') }}</span>
                  <NuxtLink
                    v-if="g.idEquipeB"
                    :to="`/presence/match/${g.id}/team/B`"
                    class="block text-[10px] text-blue-500 hover:text-blue-700 hover:underline"
                    :title="t('presence.title_match')"
                    @click.stop
                  >
                    <UIcon name="heroicons:user-group" class="w-4 h-4 mt-2 ms-4" />
                  </NuxtLink>
                </template>
              </td>

              <!-- Referee 1 -->
              <td class="px-1 py-1 max-w-32">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Referee_1'">
                  <AdminRefereeAutocomplete
                    v-model="editingValue"
                    :matric="editingRefereeMatric"
                    :journee-id="g.idJournee"
                    compact
                    @update:matric="editingRefereeMatric = $event"
                    @confirm="onRefereeConfirm"
                    @cancel="cancelInlineEdit"
                  />
                </template>
                <template v-else>
                  <span
                    v-if="g.arbitrePrincipal"
                    class="truncate block"
                    :class="[
                      isGameEditable(g) ? 'editable-cell' : '',
                      g.matricArbitrePrincipal === 0 ? 'text-gray-400 italic' : 'text-gray-600',
                    ]"
                    :title="g.arbitrePrincipal"
                    @click="startRefereeEdit(g, 'principal')"
                  >{{ g.arbitrePrincipal }}</span>
                  <span
                    v-else-if="bracketLabels(g.libelle).refereePrincipal"
                    class="truncate block text-orange-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    :title="bracketLabels(g.libelle).refereePrincipal!"
                    @click="startRefereeEdit(g, 'principal')"
                  >({{ bracketLabels(g.libelle).refereePrincipal }})</span>
                  <span
                    v-else
                    :class="isGameEditable(g) ? 'editable-cell text-gray-300' : 'text-gray-300'"
                    @click="startRefereeEdit(g, 'principal')"
                  >-</span>
                </template>
              </td>

              <!-- Referee 2 -->
              <td class="px-1 py-1 max-w-32">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Referee_2'">
                  <AdminRefereeAutocomplete
                    v-model="editingValue"
                    :matric="editingRefereeMatric"
                    :journee-id="g.idJournee"
                    compact
                    @update:matric="editingRefereeMatric = $event"
                    @confirm="onRefereeConfirm"
                    @cancel="cancelInlineEdit"
                  />
                </template>
                <template v-else>
                  <span
                    v-if="g.arbitreSecondaire"
                    class="truncate block"
                    :class="[
                      isGameEditable(g) ? 'editable-cell' : '',
                      g.matricArbitreSecondaire === 0 ? 'text-gray-400 italic' : 'text-gray-600',
                    ]"
                    :title="g.arbitreSecondaire"
                    @click="startRefereeEdit(g, 'secondaire')"
                  >{{ g.arbitreSecondaire }}</span>
                  <span
                    v-else-if="bracketLabels(g.libelle).refereeSecondaire"
                    class="truncate block text-orange-400 italic"
                    :class="isGameEditable(g) ? 'editable-cell' : ''"
                    :title="bracketLabels(g.libelle).refereeSecondaire!"
                    @click="startRefereeEdit(g, 'secondaire')"
                  >({{ bracketLabels(g.libelle).refereeSecondaire }})</span>
                  <span
                    v-else
                    :class="isGameEditable(g) ? 'editable-cell text-gray-300' : 'text-gray-300'"
                    @click="startRefereeEdit(g, 'secondaire')"
                  >-</span>
                </template>
              </td>

              <!-- Printed toggle -->
              <td class="px-1 py-1 text-center">
                <AdminToggleButton
                  :active="g.imprime === 'O'"
                  active-icon="heroicons:inbox-arrow-down-solid"
                  inactive-icon="heroicons:inbox-arrow-down"
                  active-color="green"
                  size="sm"
                  @toggle="isGameEditable(g) && togglePrinted(g)"
                />
                <!-- Coefficients -->
                <div v-if="g.coeffA !== 1 || g.coeffB !== 1" class="text-[9px] text-purple-500 leading-tight">
                  {{ g.coeffA }}/{{ g.coeffB }}
                </div>
              </td>

              <!-- Delete -->
              <td v-if="canEdit" class="px-1 py-1" @click.stop>
                <button
                  v-if="isDeletable(g)"
                  class="p-0.5 text-red-500 hover:text-red-700"
                  :title="t('common.delete')"
                  @click="openDeleteConfirm(g)"
                >
                  <UIcon name="heroicons:trash" class="w-6 h-6" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AdminPagination
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('games.total', { count: total })"
        :limit-options="[25, 50, 100, 200]"
        show-all
      />
    </div>

    <!-- ═══════ MOBILE CARDS ═══════ -->
    <AdminCardList class="lg:hidden" :loading="loading && games.length === 0" :empty="filteredGames.length === 0" :empty-text="t('games.no_results')">
      <AdminCard
        v-for="g in filteredGames"
        :key="g.id"
        :selected="selectedIds.includes(g.id)"
        :show-checkbox="canSelect"
        :checked="selectedIds.includes(g.id)"
        @toggle-select="toggleSelect(g.id)"
      >
        <template #header>
          <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-900">
              <!-- Match number (inline editable) -->
              <template v-if="editingCell?.id === g.id && editingCell.field === 'Numero_ordre'">
                <input
                  :id="`inline-${g.id}-Numero_ordre`"
                  v-model="editingValue" v-inline-focus
                  type="tel"
                  maxlength="4"
                  class="w-12 px-1 py-0 text-sm text-center border border-blue-400 rounded"
                  @keydown="handleInlineKeydown"
                  @blur="saveInlineEdit"
                >
              </template>
              <template v-else>
                <span
                  :class="isGameEditable(g) ? 'editable-cell' : ''"
                  @click="startInlineEdit(g, 'Numero_ordre')"
                >{{ t('games.field.game_number') }}{{ g.numeroOrdre }}</span>
              </template>
              — {{ g.soustitre2 || g.codeCompetition }}
              <!-- Phase/Poule (inline editable) -->
              <template v-if="g.phase">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Phase'">
                  <select
                    :id="`inline-${g.id}-Phase`"
                    v-model="editingValue" v-inline-focus
                    class="px-1 py-0 text-sm border border-blue-400 rounded"
                    @change="savePhaseEdit"
                    @keydown="handleTeamKeydown"
                    @blur="savePhaseEdit"
                  >
                    <option v-for="j in journees" :key="j.id" :value="String(j.id)">{{ j.phase || j.codeCompetition }}</option>
                  </select>
                </template>
                <span
                  v-else
                  class="text-gray-500 font-normal"
                  :class="isGameEditable(g) ? 'editable-cell' : ''"
                  @click="startPhaseEdit(g)"
                >{{ g.phase }}</span>
              </template>
            </div>
            <!-- Libelle (inline editable) -->
            <div class="text-sm text-gray-600">
              <template v-if="editingCell?.id === g.id && editingCell.field === 'Libelle'">
                <input
                  :id="`inline-${g.id}-Libelle`"
                  v-model="editingValue" v-inline-focus
                  type="text"
                  maxlength="30"
                  class="w-full px-1 py-0 text-sm border border-blue-400 rounded"
                  @keydown="handleInlineKeydown"
                  @blur="saveInlineEdit"
                >
              </template>
              <template v-else>
                <span
                  v-if="g.libelle || isGameEditable(g)"
                  :class="isGameEditable(g) ? 'editable-cell' : ''"
                  @click="startInlineEdit(g, 'Libelle')"
                >{{ g.libelle || '-' }}</span>
              </template>
            </div>
          </div>
        </template>
        <template #header-right>
          <div class="flex items-center gap-1">
            <AdminToggleButton
              v-if="canLock"
              :active="g.validation === 'O'"
              active-icon="heroicons:lock-closed-solid"
              inactive-icon="heroicons:lock-open"
              active-color="blue"
              size="md"
              :active-title="t('games.locked')"
              :inactive-title="t('games.unlocked')"
              @toggle="toggleValidation(g)"
            />
            <UIcon v-else-if="g.validation === 'O'" name="heroicons:lock-closed-solid" class="w-5 h-5 text-blue-500" :title="t('games.locked')" />
            <AdminToggleButton
              :active="g.publication === 'O'"
              active-icon="heroicons:eye-solid"
              inactive-icon="heroicons:eye-slash"
              active-color="green"
              size="md"
              @toggle="canEdit && togglePublication(g)"
            />
          </div>
        </template>

        <div class="space-y-2 text-sm">
          <!-- Date / Time / Terrain (inline editable) -->
          <div class="flex items-center gap-2">
            <UIcon name="heroicons:calendar" class="w-5 h-5 text-gray-400 shrink-0" />
            <!-- Date inline -->
            <template v-if="editingCell?.id === g.id && editingCell.field === 'Date_match'">
              <input
                :id="`inline-${g.id}-Date_match`"
                v-model="editingValue" v-inline-focus
                type="date"
                class="px-1 py-0.5 text-sm border border-blue-400 rounded"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              >
            </template>
            <span
              v-else
              :class="isGameEditable(g) ? 'editable-cell' : ''"
              @click="startInlineEdit(g, 'Date_match')"
            >{{ formatDate(g.dateMatch) }}</span>
            <!-- Time inline -->
            <template v-if="editingCell?.id === g.id && editingCell.field === 'Heure_match'">
              <input
                :id="`inline-${g.id}-Heure_match`"
                v-model="editingValue" v-inline-focus
                type="time"
                class="px-1 py-0.5 text-sm border border-blue-400 rounded"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              >
            </template>
            <span
              v-else-if="g.heureMatch"
              class="font-medium"
              :class="isGameEditable(g) ? 'editable-cell' : ''"
              @click="startInlineEdit(g, 'Heure_match')"
            >{{ g.heureMatch }}</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon name="heroicons:map-pin" class="w-5 h-5 text-gray-400 shrink-0" />
            <!-- Terrain inline -->
            <template v-if="editingCell?.id === g.id && editingCell.field === 'Terrain'">
              <input
                :id="`inline-${g.id}-Terrain`"
                v-model="editingValue" v-inline-focus
                type="tel"
                maxlength="2"
                class="w-10 px-1 py-0.5 text-sm text-center border border-blue-400 rounded"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              >
            </template>
            <span
              v-else
              :class="isGameEditable(g) ? 'editable-cell' : ''"
              @click="startInlineEdit(g, 'Terrain')"
            >{{ t('games.field.terrain') }} {{ g.terrain || '-' }}</span>
            <span v-if="g.lieu" class="text-gray-500">| {{ g.lieu }}</span>
          </div>

          <!-- Score: centered, team names close to score -->
          <div class="flex items-center justify-center gap-2 py-1">
            <span class="text-right flex-1 truncate">
              <span v-if="g.equipeA" class="font-medium">{{ g.equipeA }}</span>
              <span v-else-if="bracketLabels(g.libelle).teamA" class="font-medium text-orange-400 italic">({{ bracketLabels(g.libelle).teamA }})</span>
              <span v-else class="font-medium text-red-400 italic">{{ t('games.team_undefined') }}</span>
              <NuxtLink
                v-if="g.idEquipeA"
                :to="`/presence/match/${g.id}/team/A`"
                class="block text-[10px] text-blue-500 hover:text-blue-700 hover:underline"
                @click.stop
              >{{ t('games.presence') }}</NuxtLink>
            </span>
            <!-- Score A inline -->
            <template v-if="editingCell?.id === g.id && editingCell.field === 'ScoreA'">
              <input
                :id="`inline-${g.id}-ScoreA`"
                v-model="editingValue" v-inline-focus
                type="text" inputmode="numeric"
                maxlength="4"
                class="w-10 px-0.5 py-0 text-center font-bold text-lg border border-blue-400 rounded"
                @input="onScoreInput"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              >
            </template>
            <span
              v-else
              class="font-bold text-lg min-w-[1.5rem] text-center"
              :class="isScoreEditable(g) ? 'editable-cell' : ''"
              @click="startInlineEdit(g, 'ScoreA')"
            >{{ g.scoreA || '-' }}</span>
            <span class="text-gray-400">-</span>
            <!-- Score B inline -->
            <template v-if="editingCell?.id === g.id && editingCell.field === 'ScoreB'">
              <input
                :id="`inline-${g.id}-ScoreB`"
                v-model="editingValue" v-inline-focus
                type="text" inputmode="numeric"
                maxlength="4"
                class="w-10 px-0.5 py-0 text-center font-bold text-lg border border-blue-400 rounded"
                @input="onScoreInput"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              >
            </template>
            <span
              v-else
              class="font-bold text-lg min-w-[1.5rem] text-center"
              :class="isScoreEditable(g) ? 'editable-cell' : ''"
              @click="startInlineEdit(g, 'ScoreB')"
            >{{ g.scoreB || '-' }}</span>
            <span class="text-left flex-1 truncate">
              <span v-if="g.equipeB" class="font-medium">{{ g.equipeB }}</span>
              <span v-else-if="bracketLabels(g.libelle).teamB" class="font-medium text-orange-400 italic">({{ bracketLabels(g.libelle).teamB }})</span>
              <span v-else class="font-medium text-red-400 italic">{{ t('games.team_undefined') }}</span>
              <NuxtLink
                v-if="g.idEquipeB"
                :to="`/presence/match/${g.id}/team/B`"
                class="block text-[10px] text-blue-500 hover:text-blue-700 hover:underline"
                @click.stop
              >{{ t('games.presence') }}</NuxtLink>
            </span>
          </div>

          <!-- Type + Status -->
          <div class="flex items-center gap-3 text-xs text-gray-500">
            <button
              :title="g.type === 'C' ? t('games.type_classification') : t('games.type_elimination')"
              class="p-0.5 rounded"
              :class="isGameEditable(g) ? 'hover:bg-gray-100 cursor-pointer' : 'cursor-default opacity-60'"
              :disabled="!isGameEditable(g)"
              @click="isGameEditable(g) && toggleType(g)"
            >
              <UIcon
                :name="g.type === 'C' ? 'heroicons:bars-3' : 'heroicons:arrows-right-left'"
                class="w-5 h-5"
                :class="g.type === 'C' ? 'text-blue-600' : 'text-orange-600'"
              />
            </button>
            <span class="text-gray-300">|</span>
            <!-- <span class="text-gray-500">{{ t('games.field.status') }}</span> -->
            <button
              v-if="isGameEditable(g)"
              class="px-2 py-0.5 text-xs font-medium rounded-full border"
              :class="statusBtnClass(g)"
              @click="openStatusConfirm(g)"
            >{{ statusLabel(g) }}</button>
            <span
              v-else
              class="px-2 py-0.5 text-xs font-medium rounded-full border"
              :class="statusBtnClass(g)"
            >{{ statusLabel(g) }}</span>
          </div>

          <div v-if="g.arbitrePrincipal || g.arbitreSecondaire || bracketLabels(g.libelle).refereePrincipal || bracketLabels(g.libelle).refereeSecondaire" class="text-xs text-gray-500">
            <span v-if="g.arbitrePrincipal" :class="{ 'italic text-gray-400': g.matricArbitrePrincipal === 0 }">{{ g.arbitrePrincipal }}</span>
            <span v-else-if="bracketLabels(g.libelle).refereePrincipal" class="italic text-orange-400">({{ bracketLabels(g.libelle).refereePrincipal }})</span>
            <template v-if="g.arbitreSecondaire || bracketLabels(g.libelle).refereeSecondaire"> /
              <span v-if="g.arbitreSecondaire" :class="{ 'italic text-gray-400': g.matricArbitreSecondaire === 0 }">{{ g.arbitreSecondaire }}</span>
              <span v-else-if="bracketLabels(g.libelle).refereeSecondaire" class="italic text-orange-400">({{ bracketLabels(g.libelle).refereeSecondaire }})</span>
            </template>
          </div>
        </div>

        <template #footer-right>
          <AdminActionButton v-if="canEdit && !isLocked(g)" icon="heroicons:pencil" @click="openEditModal(g)">
            {{ t('common.edit') }}
          </AdminActionButton>
          <a v-if="canEdit" :href="`${legacyBase}/admin/FeuilleMatchMulti.php?listMatch=${g.id}`" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800">
            <UIcon name="heroicons:document-text" class="w-4 h-4" />
            {{ t('games.scoresheet_pdf') }}
          </a>
          <a v-if="canEdit && authStore.profile <= 6" :href="`${legacyBase}/admin/FeuilleMarque2.php?idMatch=${g.id}`" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-600 hover:text-emerald-800">
            <UIcon name="heroicons:device-tablet" class="w-4 h-4" />
            v2
          </a>
          <a v-if="canEdit && authStore.profile <= 2" :href="`${legacyBase}/admin/FeuilleMarque3.php?idMatch=${g.id}`" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-purple-600 hover:text-purple-800">
            <UIcon name="heroicons:device-tablet" class="w-4 h-4" />
            v3
          </a>
          <AdminActionButton v-if="isDeletable(g)" variant="danger" icon="heroicons:trash" @click="openDeleteConfirm(g)">
            {{ t('common.delete') }}
          </AdminActionButton>
        </template>
      </AdminCard>

      <!-- Mobile Pagination -->
      <AdminPagination
        v-if="filteredGames.length > 0"
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('games.total', { count: total })"
        :limit-options="[25, 50, 100, 200]"
        show-all
        class="mt-4 rounded-lg shadow"
      />
    </AdminCardList>

    <!-- ═══════ ADD/EDIT MODAL ═══════ -->
    <AdminModal
      :open="formModalOpen"
      :title="editingGame ? t('games.edit') : t('games.add')"
      max-width="xl"
      @close="formModalOpen = false"
    >
      <form class="space-y-4" @submit.prevent="submitForm">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
          <UIcon name="heroicons:exclamation-triangle" class="w-6 h-6 inline mr-1" />
          {{ formError }}
        </div>

        <!-- Journee -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.filter_journee') }} *</label>
          <select
            v-model.number="formData.idJournee"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            @change="onFormJourneeChange"
          >
            <option :value="null">-- {{ t('games.all_journees') }} --</option>
            <option v-for="j in journees" :key="j.id" :value="j.id">{{ journeeLabel(j) }}</option>
          </select>
        </div>

        <!-- Date + Heure + N° -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.date') }} *</label>
            <input v-model="formData.dateMatch" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.time') }}</label>
            <input v-model="formData.heureMatch" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.game_number') }}</label>
            <input v-model.number="formData.numeroOrdre" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>

        <!-- Terrain + Type + Intervalle -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.terrain') }}</label>
            <input v-model="formData.terrain" type="text" maxlength="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.type') }}</label>
            <div class="flex items-center gap-4 mt-2">
              <label class="flex items-center gap-2">
                <input v-model="formData.type" type="radio" value="C" class="text-blue-600">
                <span class="text-sm">{{ t('games.type_classification') }}</span>
              </label>
              <label class="flex items-center gap-2">
                <input v-model="formData.type" type="radio" value="E" class="text-orange-600">
                <span class="text-sm">{{ t('games.type_elimination') }}</span>
              </label>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.interval') }}</label>
            <input v-model.number="formData.intervalle" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>

        <!-- Libelle -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.label_coding') }}</label>
          <input v-model="formData.libelle" type="text" maxlength="30" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <!-- Teams -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.team_a') }}</label>
            <select v-model.number="formData.idEquipeA" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option :value="null">{{ t('games.none') }}</option>
              <option v-for="tm in formTeams" :key="tm.id" :value="tm.id">{{ tm.libelle }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.team_b') }}</label>
            <select v-model.number="formData.idEquipeB" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option :value="null">{{ t('games.none') }}</option>
              <option v-for="tm in formTeams" :key="tm.id" :value="tm.id">{{ tm.libelle }}</option>
            </select>
          </div>
        </div>

        <!-- Coefficients -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.coefficient') }} A</label>
            <input v-model.number="formData.coeffA" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.field.coefficient') }} B</label>
            <input v-model.number="formData.coeffB" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>

        <!-- Referees -->
        <div class="space-y-4">
            <!-- Referee 1 -->
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.field.referee_1') }}</label>
              <AdminRefereeAutocomplete
                v-model="formData.arbitrePrincipal"
                :matric="formData.matricArbitrePrincipal"
                :journee-id="formData.idJournee"
                :disabled="!formData.idJournee"
                @update:matric="formData.matricArbitrePrincipal = $event"
              />
              <!-- Team override for nominative referee -->
              <div v-if="formData.matricArbitrePrincipal > 0" class="mt-1">
                <label class="block text-[10px] text-gray-400 mb-0.5">{{ t('games.referee_team_label') }}</label>
                <select
                  class="w-full px-2 py-1 text-xs border border-gray-200 rounded bg-gray-50"
                  :value="extractTeamFromReferee(formData.arbitrePrincipal)"
                  @change="updateRefereeTeam('principal', ($event.target as HTMLSelectElement).value)"
                >
                  <option v-for="tm in formTeams" :key="tm.id" :value="tm.libelle">{{ tm.libelle }}</option>
                </select>
              </div>
            </div>
            <!-- Referee 2 -->
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('games.field.referee_2') }}</label>
              <AdminRefereeAutocomplete
                v-model="formData.arbitreSecondaire"
                :matric="formData.matricArbitreSecondaire"
                :journee-id="formData.idJournee"
                :disabled="!formData.idJournee"
                @update:matric="formData.matricArbitreSecondaire = $event"
              />
              <!-- Team override for nominative referee -->
              <div v-if="formData.matricArbitreSecondaire > 0" class="mt-1">
                <label class="block text-[10px] text-gray-400 mb-0.5">{{ t('games.referee_team_label') }}</label>
                <select
                  class="w-full px-2 py-1 text-xs border border-gray-200 rounded bg-gray-50"
                  :value="extractTeamFromReferee(formData.arbitreSecondaire)"
                  @change="updateRefereeTeam('secondaire', ($event.target as HTMLSelectElement).value)"
                >
                  <option v-for="tm in formTeams" :key="tm.id" :value="tm.libelle">{{ tm.libelle }}</option>
                </select>
              </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="formModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            :disabled="formSaving"
          >
            {{ formSaving ? t('common.loading') : t('common.save') }}
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- ═══════ DELETE CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="deleteConfirmOpen"
      :title="t('games.delete_confirm_title')"
      :message="t('games.delete_confirm_message', { id: gameToDelete?.id })"
      :loading="formSaving"
      danger
      @close="deleteConfirmOpen = false"
      @confirm="confirmDelete"
    />

    <!-- ═══════ BULK DELETE CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkDeleteConfirmOpen"
      :title="t('common.delete_selected')"
      :message="t('games.bulk_delete_confirm', { count: selectedIds.length })"
      :loading="formSaving"
      danger
      @close="bulkDeleteConfirmOpen = false"
      @confirm="confirmBulkDelete"
    />

    <!-- ═══════ BULK PUBLISH CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkPublishConfirmOpen"
      :title="t('games.bulk.publish')"
      :message="t('games.bulk_publish_confirm', { count: selectedIds.length })"
      :loading="formSaving"
      @close="bulkPublishConfirmOpen = false"
      @confirm="confirmBulkPublish"
    />

    <!-- ═══════ BULK LOCK CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkLockConfirmOpen"
      :title="t('games.bulk.lock')"
      :message="t('games.bulk_lock_confirm', { count: selectedIds.length })"
      :loading="formSaving"
      @close="bulkLockConfirmOpen = false"
      @confirm="confirmBulkLock"
    />

    <!-- ═══════ BULK LOCK+PUBLISH CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkLockPublishConfirmOpen"
      :title="t('games.bulk.lock_publish')"
      :message="t('games.bulk_lock_publish_confirm', { count: selectedIds.length })"
      :loading="formSaving"
      @close="bulkLockPublishConfirmOpen = false"
      @confirm="confirmBulkLockPublish"
    />

    <!-- ═══════ STATUS CHANGE CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="statusConfirmOpen"
      :title="t('games.confirm_status_change')"
      :message="statusGame ? `${statusGame.statut || 'ATT'} → ${statusGame.statut === 'ATT' ? 'ON' : statusGame.statut === 'ON' ? 'END' : 'ATT'}` : ''"
      variant="warning"
      @close="statusConfirmOpen = false"
      @confirm="confirmToggleStatut"
    />

    <!-- ═══════ BULK CHANGE JOURNÉE ═══════ -->
    <AdminModal
      :open="bulkChangeJourneeOpen"
      :title="t('games.bulk.change_journee_title')"
      max-width="md"
      @close="bulkChangeJourneeOpen = false"
    >
      <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">{{ t('games.bulk.change_journee_label') }}</label>
        <select
          v-model.number="bulkJourneeId"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
        >
          <option :value="null" disabled>--</option>
          <option v-for="j in bulkJourneeOptions" :key="j.id" :value="j.id">{{ journeeLabel(j) }}</option>
        </select>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          @click="bulkChangeJourneeOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          :disabled="formSaving || !bulkJourneeId"
          @click="confirmBulkChangeJournee"
        >
          {{ formSaving ? t('common.loading') : t('common.confirm') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══════ BULK RENUMBER ═══════ -->
    <AdminModal
      :open="bulkRenumberOpen"
      :title="t('games.bulk.renumber_title')"
      max-width="sm"
      @close="bulkRenumberOpen = false"
    >
      <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">{{ t('games.bulk.renumber_from') }}</label>
        <input
          v-model.number="bulkRenumberFrom"
          type="number"
          min="1"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
        >
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          @click="bulkRenumberOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          :disabled="formSaving"
          @click="confirmBulkRenumber"
        >
          {{ formSaving ? t('common.loading') : t('common.confirm') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══════ BULK CHANGE DATE ═══════ -->
    <AdminModal
      :open="bulkChangeDateOpen"
      :title="t('games.bulk.change_date_title')"
      max-width="sm"
      @close="bulkChangeDateOpen = false"
    >
      <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">{{ t('games.bulk.new_date') }}</label>
        <input
          v-model="bulkNewDate"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
        >
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          @click="bulkChangeDateOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          :disabled="formSaving || !bulkNewDate"
          @click="confirmBulkChangeDate"
        >
          {{ formSaving ? t('common.loading') : t('common.confirm') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══════ BULK INCREMENT TIME ═══════ -->
    <AdminModal
      :open="bulkIncrementTimeOpen"
      :title="t('games.bulk.increment_time_title')"
      max-width="sm"
      @close="bulkIncrementTimeOpen = false"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.bulk.start_time') }}</label>
          <input
            v-model="bulkStartTime"
            type="time"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.bulk.interval_minutes') }}</label>
          <input
            v-model.number="bulkInterval"
            type="number"
            min="1"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
          >
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          @click="bulkIncrementTimeOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          :disabled="formSaving"
          @click="confirmBulkIncrementTime"
        >
          {{ formSaving ? t('common.loading') : t('common.confirm') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══════ BULK CHANGE GROUP ═══════ -->
    <AdminModal
      :open="bulkChangeGroupOpen"
      :title="t('games.bulk.change_group_title')"
      max-width="sm"
      @close="bulkChangeGroupOpen = false"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.bulk.old_group') }}</label>
          <input
            :value="bulkOldGroup"
            name="oldGroup"
            type="text"
            maxlength="5"
            pattern="[A-Z]{1,5}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase"
            @input="forceUppercaseLetters"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('games.bulk.new_group') }}</label>
          <input
            :value="bulkNewGroup"
            name="newGroup"
            type="text"
            maxlength="5"
            pattern="[A-Z]{1,5}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase"
            @input="forceUppercaseLetters"
          >
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          @click="bulkChangeGroupOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          :disabled="formSaving || !bulkOldGroup || !bulkNewGroup"
          @click="confirmBulkChangeGroup"
        >
          {{ formSaving ? t('common.loading') : t('common.confirm') }}
        </button>
      </template>
    </AdminModal>

    <!-- Scroll to top -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
