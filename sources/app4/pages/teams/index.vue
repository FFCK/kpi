<script setup lang="ts">
import type {
  CompetitionTeam,
  CompetitionTeamInfo,
  CompetitionTeamsResponse,
  HistoricalTeam,
  TeamComposition,
  TeamAddFormData,
  TeamColorsFormData,
  DuplicateFormData,
  RegionalCommittee,
  DepartmentalCommittee,
  Club
} from '~/types/teams'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()
const config = useRuntimeConfig()
const toast = useToast()

// State
const loading = ref(false)
const teams = ref<CompetitionTeam[]>([])
const total = ref(0)
const competitionInfo = ref<CompetitionTeamInfo | null>(null)

// Selection state
const selectedIds = ref<number[]>([])
const selectAll = ref(false)

// Inline editing state
const editingCell = ref<{ id: number; field: 'poule' | 'tirage' } | null>(null)
const editingValue = ref('')
const editingOriginalValue = ref('')

// Presence sheet dropdown state
const openDropdownId = ref<number | null>(null)
const dropdownStyle = ref<{ top: string; left: string }>({ top: '0px', left: '0px' })

const togglePresenceDropdown = (teamId: number, event: MouseEvent) => {
  if (openDropdownId.value === teamId) {
    openDropdownId.value = null
    return
  }
  const btn = event.currentTarget as HTMLElement
  const rect = btn.getBoundingClientRect()
  const dropdownHeight = 160
  const spaceBelow = window.innerHeight - rect.bottom
  const openAbove = spaceBelow < dropdownHeight + 8
  dropdownStyle.value = {
    top: openAbove ? `${rect.top - dropdownHeight - 4}px` : `${rect.bottom + 4}px`,
    left: `${Math.min(rect.left, window.innerWidth - 200)}px`,
  }
  openDropdownId.value = teamId
}

const closePresenceDropdown = () => {
  openDropdownId.value = null
}

// Global PDF dropdown state (competition-level PDFs)
const globalPdfOpen = ref(false)
const globalPdfStyle = ref<{ top: string; left: string }>({ top: '0px', left: '0px' })

const toggleGlobalPdf = (event: MouseEvent) => {
  if (globalPdfOpen.value) { globalPdfOpen.value = false; return }
  const btn = event.currentTarget as HTMLElement
  const rect = btn.getBoundingClientRect()
  const dropdownHeight = 260
  const spaceBelow = window.innerHeight - rect.bottom
  const openAbove = spaceBelow < dropdownHeight + 8
  globalPdfStyle.value = {
    top: openAbove ? `${rect.top - dropdownHeight - 4}px` : `${rect.bottom + 4}px`,
    left: `${Math.min(rect.left, window.innerWidth - 220)}px`,
  }
  globalPdfOpen.value = true
}

const handleClickOutsideDropdown = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (openDropdownId.value !== null) {
    if (!target.closest('.presence-dropdown-trigger') && !target.closest('.presence-dropdown-menu')) {
      closePresenceDropdown()
    }
  }
  if (globalPdfOpen.value) {
    if (!target.closest('.global-pdf-trigger') && !target.closest('.global-pdf-menu')) {
      globalPdfOpen.value = false
    }
  }
}
onMounted(() => document.addEventListener('click', handleClickOutsideDropdown))
onUnmounted(() => document.removeEventListener('click', handleClickOutsideDropdown))

// Add modal state
const addModalOpen = ref(false)
const addFormTab = ref<'manual' | 'history'>('history')
const addFormData = ref<TeamAddFormData>(getDefaultAddFormData())
const addFormError = ref('')
const addFormSaving = ref(false)

// Club search for manual add
const clubSearchQuery = ref('')
const clubSearchResults = ref<Club[]>([])
const clubSearchLoading = ref(false)
let clubSearchTimeout: ReturnType<typeof setTimeout> | null = null

// Club filters (CR/CD cascade)
const regionalCommittees = ref<RegionalCommittee[]>([])
const departmentalCommittees = ref<DepartmentalCommittee[]>([])
const filteredClubs = ref<Club[]>([])
const selectedCR = ref('')
const selectedCD = ref('')

// Historical team search
const historySearchQuery = ref('')
const historySearchResults = ref<HistoricalTeam[]>([])
const historySearchLoading = ref(false)
let historySearchTimeout: ReturnType<typeof setTimeout> | null = null

// Composition copy state
const showCopyComposition = ref(false)
const compositions = ref<TeamComposition[]>([])
const compositionsLoading = ref(false)

// Edit colors modal state
const editModalOpen = ref(false)
const editingTeam = ref<CompetitionTeam | null>(null)
const colorsFormData = ref<TeamColorsFormData>(getDefaultColorsFormData())
const colorsFormSaving = ref(false)

// Duplicate modal state
const duplicateModalOpen = ref(false)
const duplicateFormData = ref<DuplicateFormData>(getDefaultDuplicateFormData())
const duplicateFormSaving = ref(false)

// Delete modal state
const deleteModalOpen = ref(false)
const teamToDelete = ref<CompetitionTeam | null>(null)
const bulkDeleteModalOpen = ref(false)
const isDeleting = ref(false)

// Confirm modals for special operations
const initStartersModalOpen = ref(false)
const updateLogosModalOpen = ref(false)
const specialOpLoading = ref(false)

// Permission checks
const canView = computed(() => authStore.profile <= 10)
const canEditInline = computed(() => authStore.profile <= 6)
const canManageSpecialOps = computed(() => authStore.profile <= 4)
const canAddDelete = computed(() => authStore.profile <= 3 && !competitionInfo.value?.verrou)
const canEditProperties = computed(() => authStore.profile <= 2)

// Computed: teams grouped by pool
const teamsByPool = computed(() => {
  const groups: Record<string, CompetitionTeam[]> = {}
  for (const team of teams.value) {
    const pool = team.poule || ''
    if (!groups[pool]) {
      groups[pool] = []
    }
    groups[pool].push(team)
  }
  // Sort pools: named pools first (A, B, C...), then empty pool last
  const sortedKeys = Object.keys(groups).sort((a, b) => {
    if (a === '' && b !== '') return 1
    if (a !== '' && b === '') return -1
    return a.localeCompare(b)
  })
  return sortedKeys.map(key => ({
    pool: key,
    label: key ? t('teams_page.pool_header', { letter: key }) : t('teams_page.no_pool'),
    teams: groups[key]
  }))
})

// Computed: competition options for duplicate source
const duplicateSourceOptions = computed(() => {
  if (!workContext.contextCompetitions) return []
  return workContext.contextCompetitions.filter(c => c.code !== workContext.pageCompetitionCode)
})

// Legacy base URL for PDF links
const legacyBase = computed(() => config.public.legacyBaseUrl)

// Default form data factories
function getDefaultAddFormData(): TeamAddFormData {
  return {
    mode: 'history',
    libelle: '',
    codeClub: '',
    teamNumbers: [],
    poule: '',
    tirage: 0,
    copyComposition: null
  }
}

function getDefaultColorsFormData(): TeamColorsFormData {
  return {
    logo: '',
    color1: '#000000',
    color2: '#FFFFFF',
    colortext: '#FFFFFF',
    propagateNext: false,
    propagatePrevious: false,
    propagateClub: false
  }
}

function getDefaultDuplicateFormData(): DuplicateFormData {
  return {
    sourceCompetition: '',
    sourceSeason: workContext.season || '',
    mode: 'append',
    copyPlayers: false
  }
}

// Load teams for selected competition
const loadTeams = async () => {
  if (!workContext.initialized || !workContext.season || !workContext.pageCompetitionCode) return

  loading.value = true
  try {
    const response = await api.get<CompetitionTeamsResponse>('/admin/competition-teams', {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    })
    teams.value = response.teams
    competitionInfo.value = response.competition
    total.value = response.total
    selectedIds.value = []
    selectAll.value = false
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('teams_page.error_load')
    toast.add({ title: t('common.error'), description: message, color: 'error', duration: 3000 })
  } finally {
    loading.value = false
  }
}

// Watch page competition changes (triggered by CompetitionSingleSelect component)
watch(
  () => workContext.pageCompetitionCode,
  (code) => {
    if (code) {
      loadTeams()
    }
    else {
      teams.value = []
      competitionInfo.value = null
      total.value = 0
    }
  },
)

// Competition change handler from selector component
function onCompetitionChange() {
  // Watch above handles the reload
}

// Load on mount
onMounted(async () => {
  await workContext.initContext()
  // Load teams if competition is already selected
  if (workContext.pageCompetitionCode) {
    await loadTeams()
  }
})

// Selection handlers
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedIds.value = teams.value.map(t => t.id)
  } else {
    selectedIds.value = []
  }
}

const isSelected = (id: number) => selectedIds.value.includes(id)

const toggleSelect = (id: number) => {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
  selectAll.value = selectedIds.value.length === teams.value.length
}

// Inline editing
const startEdit = (team: CompetitionTeam, field: 'poule' | 'tirage') => {
  if (!canEditInline.value) return
  editingCell.value = { id: team.id, field }
  const val = field === 'poule' ? team.poule : String(team.tirage)
  editingValue.value = val
  editingOriginalValue.value = val
  nextTick(() => {
    const desktopEl = document.getElementById(`inline-edit-${team.id}-${field}`)
    const mobileEl = document.getElementById(`mobile-edit-${team.id}-${field}`)
    // Focus the visible one (desktop is inside hidden lg:block, mobile is inside lg:hidden)
    const el = mobileEl && mobileEl.offsetParent !== null ? mobileEl : desktopEl
    if (el) {
      el.focus()
      if (el instanceof HTMLInputElement) {
        el.select()
      }
    }
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return

  const { id, field } = editingCell.value
  const team = teams.value.find(t => t.id === id)
  if (!team) return

  const poule = field === 'poule' ? editingValue.value.toUpperCase().trim() : team.poule
  const tirage = field === 'tirage' ? parseInt(editingValue.value) || 0 : team.tirage

  // Close editing
  editingCell.value = null

  // Only PATCH if value actually changed
  const newVal = field === 'poule' ? poule : String(tirage)
  if (newVal === editingOriginalValue.value) return

  try {
    await api.patch(`/admin/competition-teams/${id}/pool-draw`, { poule, tirage })
    team.poule = poule
    team.tirage = tirage
    toast.add({ title: t('common.success'), description: t('teams_page.success_updated'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_save'), color: 'error', duration: 3000 })
  }
}

const cancelInlineEdit = () => {
  editingCell.value = null
}

const handleInlineKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') {
    saveInlineEdit()
  } else if (e.key === 'Escape') {
    cancelInlineEdit()
  }
}

// Add modal
const openAddModal = () => {
  addFormData.value = getDefaultAddFormData()
  addFormError.value = ''
  addFormTab.value = 'history'
  clubSearchQuery.value = ''
  clubSearchResults.value = []
  historySearchQuery.value = ''
  historySearchResults.value = []
  showCopyComposition.value = false
  compositions.value = []
  selectedCR.value = ''
  selectedCD.value = ''
  addModalOpen.value = true
  loadRegionalCommittees()
}

const loadRegionalCommittees = async () => {
  try {
    regionalCommittees.value = await api.get<RegionalCommittee[]>('/admin/regional-committees')
  } catch {
    // Ignore
  }
}

const loadDepartmentalCommittees = async () => {
  if (!selectedCR.value) {
    departmentalCommittees.value = []
    return
  }
  try {
    departmentalCommittees.value = await api.get<DepartmentalCommittee[]>('/admin/departmental-committees', { cr: selectedCR.value })
  } catch {
    // Ignore
  }
}

const loadFilteredClubs = async () => {
  if (!selectedCD.value) {
    filteredClubs.value = []
    return
  }
  try {
    filteredClubs.value = await api.get<Club[]>('/admin/clubs', { cd: selectedCD.value })
  } catch {
    // Ignore
  }
}

watch(selectedCR, () => {
  selectedCD.value = ''
  filteredClubs.value = []
  loadDepartmentalCommittees()
})

watch(selectedCD, () => {
  loadFilteredClubs()
})

// Club autocomplete search
const searchClubs = () => {
  if (clubSearchTimeout) clearTimeout(clubSearchTimeout)
  clubSearchTimeout = setTimeout(async () => {
    if (clubSearchQuery.value.length < 2) {
      clubSearchResults.value = []
      return
    }
    clubSearchLoading.value = true
    try {
      clubSearchResults.value = await api.get<Club[]>('/admin/clubs/search', { q: clubSearchQuery.value })
    } catch {
      clubSearchResults.value = []
    } finally {
      clubSearchLoading.value = false
    }
  }, 300)
}

const selectClub = (club: Club) => {
  addFormData.value.codeClub = club.code
  clubSearchQuery.value = `${club.code} - ${club.libelle}`
  clubSearchResults.value = []
}

// History search
const searchHistoryTeams = () => {
  if (historySearchTimeout) clearTimeout(historySearchTimeout)
  historySearchTimeout = setTimeout(async () => {
    if (historySearchQuery.value.length < 2) {
      historySearchResults.value = []
      return
    }
    historySearchLoading.value = true
    try {
      historySearchResults.value = await api.get<HistoricalTeam[]>('/admin/teams/search', { q: historySearchQuery.value })
    } catch {
      historySearchResults.value = []
    } finally {
      historySearchLoading.value = false
    }
  }, 300)
}

const toggleHistoryTeam = (numero: number) => {
  const index = addFormData.value.teamNumbers.indexOf(numero)
  if (index > -1) {
    addFormData.value.teamNumbers.splice(index, 1)
  } else {
    addFormData.value.teamNumbers.push(numero)
  }
}

const isHistoryTeamSelected = (numero: number) => addFormData.value.teamNumbers.includes(numero)

// Load compositions for selected team (history mode)
const loadCompositions = async (numero: number) => {
  if (!workContext.season) return
  compositionsLoading.value = true
  try {
    compositions.value = await api.get<TeamComposition[]>(`/admin/teams/${numero}/compositions`, { season: workContext.season })
  } catch {
    compositions.value = []
  } finally {
    compositionsLoading.value = false
  }
}

// Save add form
const saveAddForm = async () => {
  addFormError.value = ''

  if (addFormData.value.mode === 'manual') {
    if (!addFormData.value.libelle.trim()) {
      addFormError.value = 'Le nom de l\'équipe est obligatoire'
      return
    }
  } else {
    if (addFormData.value.teamNumbers.length === 0) {
      addFormError.value = 'Sélectionnez au moins une équipe'
      return
    }
  }

  addFormSaving.value = true
  try {
    const body: Record<string, unknown> = {
      season: workContext.season,
      competition: workContext.pageCompetitionCode,
      mode: addFormData.value.mode,
      poule: addFormData.value.poule,
      tirage: addFormData.value.tirage
    }

    if (addFormData.value.mode === 'manual') {
      body.libelle = addFormData.value.libelle
      body.codeClub = addFormData.value.codeClub
    } else {
      body.teamNumbers = addFormData.value.teamNumbers
      if (addFormData.value.copyComposition) {
        body.copyComposition = addFormData.value.copyComposition
      }
    }

    await api.post('/admin/competition-teams', body)
    toast.add({ title: t('common.success'), description: t('teams_page.success_added'), color: 'success', duration: 3000 })
    addModalOpen.value = false
    loadTeams()
  } catch (error: unknown) {
    addFormError.value = (error as { message?: string })?.message || t('teams_page.error_save')
  } finally {
    addFormSaving.value = false
  }
}

// Edit colors modal
const openEditModal = (team: CompetitionTeam) => {
  editingTeam.value = team
  colorsFormData.value = {
    logo: team.logo || '',
    color1: team.color1 || '#000000',
    color2: team.color2 || '#FFFFFF',
    colortext: team.colortext || '#FFFFFF',
    propagateNext: false,
    propagatePrevious: false,
    propagateClub: false
  }
  editModalOpen.value = true
}

const saveColorsForm = async () => {
  if (!editingTeam.value) return

  colorsFormSaving.value = true
  try {
    await api.patch(`/admin/competition-teams/${editingTeam.value.id}/colors`, colorsFormData.value)
    // Update local state
    const team = teams.value.find(t => t.id === editingTeam.value!.id)
    if (team) {
      team.logo = colorsFormData.value.logo || null
      team.color1 = colorsFormData.value.color1 || null
      team.color2 = colorsFormData.value.color2 || null
      team.colortext = colorsFormData.value.colortext || null
    }
    toast.add({ title: t('common.success'), description: t('teams_page.success_updated'), color: 'success', duration: 3000 })
    editModalOpen.value = false
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_save'), color: 'error', duration: 3000 })
  } finally {
    colorsFormSaving.value = false
  }
}

// Duplicate modal
const openDuplicateModal = () => {
  duplicateFormData.value = getDefaultDuplicateFormData()
  duplicateModalOpen.value = true
}

const saveDuplicateForm = async () => {
  if (!duplicateFormData.value.sourceCompetition) return

  duplicateFormSaving.value = true
  try {
    await api.post('/admin/competition-teams/duplicate', {
      season: workContext.season,
      targetCompetition: workContext.pageCompetitionCode,
      sourceCompetition: duplicateFormData.value.sourceCompetition,
      sourceSeason: duplicateFormData.value.sourceSeason || workContext.season,
      mode: duplicateFormData.value.mode,
      copyPlayers: duplicateFormData.value.copyPlayers
    })
    toast.add({ title: t('common.success'), description: t('teams_page.success_duplicated'), color: 'success', duration: 3000 })
    duplicateModalOpen.value = false
    loadTeams()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_save'), color: 'error', duration: 3000 })
  } finally {
    duplicateFormSaving.value = false
  }
}

// Delete handlers
const openDeleteModal = (team: CompetitionTeam) => {
  teamToDelete.value = team
  deleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!teamToDelete.value) return

  isDeleting.value = true
  try {
    await api.del(`/admin/competition-teams/${teamToDelete.value.id}`)
    toast.add({ title: t('common.success'), description: t('teams_page.success_deleted'), color: 'success', duration: 3000 })
    deleteModalOpen.value = false
    teamToDelete.value = null
    loadTeams()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_delete'), color: 'error', duration: 3000 })
  } finally {
    isDeleting.value = false
  }
}

const openBulkDeleteModal = () => {
  if (selectedIds.value.length === 0) return
  bulkDeleteModalOpen.value = true
}

const confirmBulkDelete = async () => {
  if (selectedIds.value.length === 0) return

  isDeleting.value = true
  try {
    await api.post('/admin/competition-teams/bulk-delete', {
      ids: selectedIds.value,
      season: workContext.season,
      competition: workContext.competition
    })
    toast.add({ title: t('common.success'), description: t('teams_page.success_deleted'), color: 'success', duration: 3000 })
    bulkDeleteModalOpen.value = false
    selectedIds.value = []
    selectAll.value = false
    loadTeams()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_delete'), color: 'error', duration: 3000 })
  } finally {
    isDeleting.value = false
  }
}

// Special operations
const confirmInitStarters = async () => {
  specialOpLoading.value = true
  try {
    await api.post('/admin/competition-teams/init-starters', {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    })
    toast.add({ title: t('common.success'), description: t('teams_page.success_init_starters'), color: 'success', duration: 3000 })
    initStartersModalOpen.value = false
    loadTeams() // Reload to update lock state
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_save'), color: 'error', duration: 3000 })
  } finally {
    specialOpLoading.value = false
  }
}

const confirmUpdateLogos = async () => {
  specialOpLoading.value = true
  try {
    await api.post('/admin/competition-teams/update-logos', {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    })
    toast.add({ title: t('common.success'), description: t('teams_page.success_update_logos'), color: 'success', duration: 3000 })
    updateLogosModalOpen.value = false
    loadTeams()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('teams_page.error_save'), color: 'error', duration: 3000 })
  } finally {
    specialOpLoading.value = false
  }
}

// Toggle competition lock
const toggleLock = async () => {
  if (!competitionInfo.value) return
  try {
    const response = await api.patch<{ verrou: boolean }>('/admin/competition-teams/toggle-lock', {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    })
    competitionInfo.value.verrou = response.verrou
    toast.add({
      title: t('common.success'),
      description: competitionInfo.value.verrou ? t('teams_page.locked') : t('teams_page.unlocked'),
      color: 'success',
      duration: 2000
    })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || 'Erreur', color: 'error', duration: 3000 })
  }
}

// Global PDF URLs (competition-level)
const globalPdfPoolsUrl = computed(() => `${legacyBase.value}/admin/FeuilleGroups.php`)
const globalPdfPresenceFrUrl = computed(() => `${legacyBase.value}/admin/FeuillePresence.php`)
const globalPdfPresenceEnUrl = computed(() => `${legacyBase.value}/admin/FeuillePresenceEN.php`)
const globalPdfPresenceCatUrl = computed(() => `${legacyBase.value}/admin/FeuillePresenceCat.php`)
const globalPdfPresencePhotoUrl = computed(() => {
  if (competitionInfo.value?.code === 'POOL') return `${legacyBase.value}/admin/FeuillePresencePhotoRef.php`
  return `${legacyBase.value}/admin/FeuillePresencePhoto.php`
})
const globalPdfControlUrl = computed(() => `${legacyBase.value}/admin/FeuilleControle.php`)

// Status badge colors
const getStatusColor = (status: string) => {
  switch (status) {
    case 'ATT': return 'bg-[#888888] text-[#CCEEDD] italic'
    case 'ON': return 'bg-[#008800] text-[#CCEEDD] italic'
    case 'END': return 'bg-[#334F64] text-[#CCEEDD]'
    default: return 'bg-[#888888] text-[#CCEEDD]'
  }
}

const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-blue-100 text-blue-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

// Legacy PDF links
const getPresenceUrl = (team: CompetitionTeam) =>
  `${legacyBase.value}/admin/FeuillePresence.php?equipe=${team.id}`

const getPresenceEnUrl = (team: CompetitionTeam) =>
  `${legacyBase.value}/admin/FeuillePresenceEN.php?equipe=${team.id}`

const getPresencePhotoUrl = (team: CompetitionTeam) =>
  `${legacyBase.value}/admin/FeuillePresencePhoto.php?equipe=${team.id}`

const getControlUrl = (team: CompetitionTeam) =>
  `${legacyBase.value}/admin/FeuillePresenceVisa.php?equipe=${team.id}`

// Logo image URL - fallback to club code convention like legacy PHP
// Logo column should contain relative path within img/ (e.g. "KIP/logo/3512-logo.png" or "Nations/GER.png")
// French clubs: code length === 4, path = KIP/logo/{code}-logo.png
// International teams: code length !== 4, path = Nations/{code 3 chars}.png
const getLogoUrl = (team: CompetitionTeam) => {
  if (team.logo) {
    if (team.logo.includes('/')) return `${legacyBase.value}/img/${team.logo}`
    // Malformed entry without path — reconstruct based on club code length
    if (team.codeClub && team.codeClub.length !== 4) {
      return `${legacyBase.value}/img/Nations/${team.codeClub.substring(0, 3)}.png`
    }
    return `${legacyBase.value}/img/KIP/logo/${team.logo}`
  }
  if (team.codeClub) {
    if (team.codeClub.length === 4) return `${legacyBase.value}/img/KIP/logo/${team.codeClub}-logo.png`
    return `${legacyBase.value}/img/Nations/${team.codeClub.substring(0, 3)}.png`
  }
  return null
}
</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader
      :title="t('teams_page.title')"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
      @competition-change="onCompetitionChange"
    >
      <template #badges>
        <div v-if="competitionInfo" class="flex items-center gap-2 flex-wrap">
          <span
            class="px-2 py-1 text-xs font-medium rounded"
            :class="getLevelColor(competitionInfo.codeNiveau)"
          >
            {{ competitionInfo.codeNiveau }}
          </span>
          <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
            {{ competitionInfo.codeTypeclt }}
          </span>
          <span
            class="px-2 py-1 text-xs font-medium rounded uppercase"
            :class="getStatusColor(competitionInfo.statut)"
          >
            {{ t(`competitions.status.${competitionInfo.statut}`) }}
          </span>

          <!-- Lock toggle -->
          <button
            v-if="canManageSpecialOps"
            class="p-1.5 rounded-lg transition-colors"
            :class="competitionInfo.verrou ? 'text-red-600 hover:bg-red-50' : 'text-gray-400 hover:bg-gray-50'"
            :title="competitionInfo.verrou ? t('teams_page.locked') : t('teams_page.unlocked')"
            @click="toggleLock"
          >
            <UIcon
              :name="competitionInfo.verrou ? 'heroicons:lock-closed-solid' : 'heroicons:lock-open-solid'"
              class="w-5 h-5"
            />
          </button>
          <UIcon
            v-else-if="competitionInfo.verrou"
            name="heroicons:lock-closed-solid"
            class="w-6 h-6 text-red-600"
          />
        </div>
      </template>
      <template #notices>
        <div
          v-if="competitionInfo?.verrou"
          class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800"
        >
          <UIcon name="heroicons:exclamation-triangle" class="w-6 h-6 shrink-0" />
          {{ t('teams_page.competition_locked_notice') }}
        </div>
      </template>
    </AdminPageHeader>

    <!-- No competition selected -->
    <div v-if="!workContext.pageCompetitionCode" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ t('teams_page.no_competition') }}
    </div>

    <!-- Teams content -->
    <template v-if="workContext.pageCompetitionCode">
      <!-- Toolbar -->
      <div class="mb-4 bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap items-center gap-2">
          <!-- Add button -->
          <button
            v-if="canAddDelete"
            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center gap-1"
            @click="openAddModal"
          >
            <UIcon name="heroicons:plus" class="w-6 h-6" />
            {{ t('teams_page.add') }}
          </button>

          <!-- Duplicate button -->
          <button
            v-if="canAddDelete"
            class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm flex items-center gap-1"
            @click="openDuplicateModal"
          >
            <UIcon name="heroicons:document-duplicate" class="w-6 h-6" />
            {{ t('teams_page.duplicate') }}
          </button>

          <!-- Init starters -->
          <button
            v-if="canManageSpecialOps"
            class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
            @click="initStartersModalOpen = true"
          >
            {{ t('teams_page.init_starters') }}
          </button>

          <!-- Update logos -->
          <button
            v-if="canEditProperties"
            class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
            @click="updateLogosModalOpen = true"
          >
            {{ t('teams_page.update_logos') }}
          </button>

          <!-- Global PDF dropdown -->
          <button
            v-if="teams.length > 0"
            class="global-pdf-trigger px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm flex items-center gap-1"
            @click="toggleGlobalPdf($event)"
          >
            <UIcon name="heroicons:document-text" class="w-6 h-6" />
            {{ t('teams_page.global_pdf') }}
          </button>

          <div class="flex-1" />

          <!-- Select all / Deselect all -->
          <template v-if="canAddDelete && teams.length > 0">
            <button
              class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
              @click="selectAll = !selectAll; toggleSelectAll()"
            >
              {{ selectAll ? t('teams_page.deselect_all') : t('teams_page.select_all') }}
            </button>

            <!-- Bulk delete -->
            <button
              v-if="selectedIds.length > 0"
              class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm flex items-center gap-1"
              @click="openBulkDeleteModal"
            >
              <UIcon name="heroicons:trash-solid" class="w-6 h-6" />
              {{ t('teams_page.delete_selected') }} ({{ selectedIds.length }})
            </button>
          </template>

          <!-- Total count -->
          <span class="text-sm text-gray-500">
            {{ t('teams_page.total', { count: total }) }}
          </span>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading && teams.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
        {{ t('common.loading') }}
      </div>

      <!-- Empty state -->
      <div v-else-if="teams.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        {{ t('teams_page.empty') }}
      </div>

      <!-- Teams grouped by pool -->
      <div v-else class="space-y-4">
        <div v-for="group in teamsByPool" :key="group.pool" class="bg-white rounded-lg shadow overflow-hidden">
          <!-- Pool header -->
          <div class="px-4 py-2 bg-gray-100 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700">
              {{ group.label }}
            </h3>
          </div>

          <!-- Desktop table -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-if="canAddDelete" class="px-3 py-2 w-10">
                    <span class="sr-only">Select</span>
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">
                    {{ t('teams_page.columns.poule') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">
                    {{ t('teams_page.columns.tirage') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-20">
                    {{ t('teams_page.columns.logo') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                    {{ t('teams_page.columns.equipe') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">
                    {{ t('teams_page.columns.club') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16">
                    {{ t('teams_page.columns.matchs') }}
                  </th>
                  <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-40">
                    {{ t('teams_page.columns.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="team in group.teams"
                  :key="team.id"
                  class="hover:bg-gray-50"
                  :class="{ 'bg-blue-50': isSelected(team.id) }"
                >
                  <!-- Checkbox -->
                  <td v-if="canAddDelete" class="px-3 py-2">
                    <input
                      :checked="isSelected(team.id)"
                      type="checkbox"
                      class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                      @change="toggleSelect(team.id)"
                    >
                  </td>

                  <!-- Poule (inline editable) -->
                  <td class="px-3 py-2 text-sm">
                    <template v-if="editingCell?.id === team.id && editingCell.field === 'poule'">
                      <input
                        :id="`inline-edit-${team.id}-poule`"
                        v-model="editingValue"
                        type="text"
                        maxlength="5"
                        class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-sm uppercase focus:ring-2 focus:ring-blue-500"
                        @keydown="handleInlineKeydown"
                        @blur="saveInlineEdit"
                      >
                    </template>
                    <template v-else>
                      <span
                        :class="canEditInline ? 'editable-cell' : ''"
                        @click="startEdit(team, 'poule')"
                      >
                        {{ team.poule || '-' }}
                      </span>
                    </template>
                  </td>

                  <!-- Tirage (inline editable) -->
                  <td class="px-3 py-2 text-sm">
                    <template v-if="editingCell?.id === team.id && editingCell.field === 'tirage'">
                      <input
                        :id="`inline-edit-${team.id}-tirage`"
                        v-model="editingValue"
                        type="number"
                        min="0"
                        max="99"
                        class="w-14 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                        @keydown="handleInlineKeydown"
                        @blur="saveInlineEdit"
                      >
                    </template>
                    <template v-else>
                      <span
                        :class="canEditInline ? 'editable-cell' : ''"
                        @click="startEdit(team, 'tirage')"
                      >
                        {{ team.tirage }}
                      </span>
                    </template>
                  </td>

                  <!-- Logo + Colors -->
                  <td
                    class="px-3 py-2 text-center"
                    :class="canEditProperties ? 'cursor-pointer hover:bg-blue-50' : ''"
                    @click="canEditProperties && openEditModal(team)"
                  >
                    <div class="flex items-center justify-center">
                      <img
                        v-if="getLogoUrl(team)"
                        :src="getLogoUrl(team)!"
                        :alt="team.libelle"
                        class="w-20 h-10 object-contain pe-2"
                        @error="($event.target as HTMLImageElement).style.display = 'none'"
                      >
                      <span
                        v-if="team.color1 || team.color2"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-sm aspect-square font-bold"
                        :style="{
                          backgroundColor: team.color1 || '#000',
                          borderColor: team.color2 || 'transparent',
                          borderWidth: '5px',
                          borderStyle: 'solid',
                          color: team.colortext || '#FFF',
                          boxSizing: 'border-box'
                        }"
                      >
                        1
                      </span>
                      <span v-if="!getLogoUrl(team) && !team.color1 && !team.color2" class="text-gray-300 text-xs">-</span>
                    </div>
                  </td>

                  <!-- Team name -->
                  <td class="px-3 py-2 text-sm font-medium text-gray-900">
                    {{ team.libelle }}
                  </td>

                  <!-- Club -->
                  <td class="px-3 py-2 text-sm text-gray-500 text-center">
                    <NuxtLink
                      :to="`/clubs?code=${team.codeClub}`"
                      class="link-value"
                      :title="t('teams_page.columns.club')"
                    >
                      {{ team.codeClub }}
                    </NuxtLink>
                  </td>

                  <!-- Matches -->
                  <td class="px-3 py-2 text-sm text-center text-gray-500">
                    {{ team.nbMatchs }}
                  </td>

                  <!-- Actions -->
                  <td class="px-3 py-2">
                    <div class="flex items-center justify-end gap-1">
                      <!-- Players link -->
                      <NuxtLink
                        :to="`/presence/team/${team.id}`"
                        class="p-1 text-purple-600 hover:bg-purple-50 rounded"
                        :title="t('teams_page.players')"
                      >
                        <UIcon name="heroicons:user-group" class="w-6 h-6" />
                      </NuxtLink>

                      <!-- Presence sheet dropdown -->
                      <button
                        class="presence-dropdown-trigger p-1 text-gray-600 hover:bg-gray-50 rounded"
                        :title="t('teams_page.presence_sheet')"
                        @click="togglePresenceDropdown(team.id, $event)"
                      >
                        <UIcon name="heroicons:document-text" class="w-6 h-6" />
                      </button>

                      <!-- Delete -->
                      <button
                        v-if="canAddDelete && team.nbMatchs === 0"
                        class="p-1 text-red-600 hover:bg-red-50 rounded"
                        :title="t('common.delete')"
                        @click="openDeleteModal(team)"
                      >
                        <UIcon name="heroicons:trash-solid" class="w-6 h-6" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile cards -->
          <div class="lg:hidden divide-y divide-gray-200">
            <div
              v-for="team in group.teams"
              :key="team.id"
              class="p-4"
              :class="{ 'bg-blue-50': isSelected(team.id) }"
            >
              <div class="flex items-start gap-3">
                <!-- Checkbox -->
                <input
                  v-if="canAddDelete"
                  :checked="isSelected(team.id)"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 mt-0.5 cursor-pointer"
                  @change="toggleSelect(team.id)"
                >

                <!-- Logo + Color swatch (clickable to edit) -->
                <div
                  class="flex flex-col items-center gap-1 shrink-0"
                  :class="canEditProperties ? 'cursor-pointer' : ''"
                  @click="canEditProperties && openEditModal(team)"
                >
                  <img
                    v-if="getLogoUrl(team)"
                    :src="getLogoUrl(team)!"
                    :alt="team.libelle"
                    class="w-8 h-8 object-contain"
                    @error="($event.target as HTMLImageElement).style.display = 'none'"
                  >
                  <span
                    v-if="team.color1 || team.color2"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-sm aspect-square text-[10px] font-bold"
                    :style="{
                      backgroundColor: team.color1 || '#000',
                      borderColor: team.color2 || 'transparent',
                      borderWidth: '3px',
                      borderStyle: 'solid',
                      color: team.colortext || '#FFF',
                      boxSizing: 'border-box'
                    }"
                  >
                    1
                  </span>
                </div>

                <div class="flex-1 min-w-0">
                  <div class="font-medium text-gray-900">{{ team.libelle }}</div>
                  <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
                    <!-- Poule inline edit (mobile) -->
                    <template v-if="editingCell?.id === team.id && editingCell.field === 'poule'">
                      <input
                        :id="`mobile-edit-${team.id}-poule`"
                        v-model="editingValue"
                        type="text"
                        maxlength="5"
                        class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-xs uppercase focus:ring-2 focus:ring-blue-500"
                        @keydown="handleInlineKeydown"
                        @blur="saveInlineEdit"
                      >
                    </template>
                    <span
                      v-else
                      :class="canEditInline ? 'editable-cell' : ''"
                      @click="canEditInline && startEdit(team, 'poule')"
                    >
                      {{ t('teams_page.columns.poule') }}: {{ team.poule || '-' }}
                    </span>
                    <span>|</span>
                    <!-- Tirage inline edit (mobile) -->
                    <template v-if="editingCell?.id === team.id && editingCell.field === 'tirage'">
                      <input
                        :id="`mobile-edit-${team.id}-tirage`"
                        v-model="editingValue"
                        type="number"
                        min="0"
                        max="99"
                        class="w-14 px-1 py-0.5 border border-blue-400 rounded text-center text-xs focus:ring-2 focus:ring-blue-500"
                        @keydown="handleInlineKeydown"
                        @blur="saveInlineEdit"
                      >
                    </template>
                    <span
                      v-else
                      :class="canEditInline ? 'editable-cell' : ''"
                      @click="canEditInline && startEdit(team, 'tirage')"
                    >
                      #{{ team.tirage }}
                    </span>
                    <span>|</span>
                    <span>{{ team.codeClub }}</span>
                    <span>|</span>
                    <span>{{ team.nbMatchs }} {{ t('teams_page.columns.matchs') }}</span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1">
                  <NuxtLink
                    :to="`/presence/team/${team.id}`"
                    class="p-1 text-purple-600"
                  >
                    <UIcon name="heroicons:user-group" class="w-6 h-6" />
                  </NuxtLink>
                  <button
                    class="presence-dropdown-trigger p-1 text-gray-600"
                    :title="t('teams_page.presence_sheet')"
                    @click="togglePresenceDropdown(team.id, $event)"
                  >
                    <UIcon name="heroicons:document-text" class="w-6 h-6" />
                  </button>
                  <button
                    v-if="canAddDelete && team.nbMatchs === 0"
                    class="p-1 text-red-600"
                    @click="openDeleteModal(team)"
                  >
                    <UIcon name="heroicons:trash-solid" class="w-6 h-6" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom total -->
        <div class="text-center text-sm text-gray-500 py-2">
          {{ t('teams_page.total', { count: total }) }}
        </div>
      </div>
    </template>

    <!-- Presence sheet dropdown (teleported to body to avoid overflow clipping) -->
    <Teleport to="body">
      <div
        v-if="openDropdownId !== null"
        class="presence-dropdown-menu fixed w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        :style="dropdownStyle"
      >
        <NuxtLink
          :to="`/presence/team/${openDropdownId}`"
          class="block px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 rounded-t-lg"
          @click="closePresenceDropdown"
        >
          <UIcon name="i-heroicons-clipboard-document-list" class="w-6 h-6 inline mr-1" />
          {{ t('teams_page.manage_composition') }}
        </NuxtLink>
        <div class="border-t border-gray-100"/>
        <a :href="getPresenceUrl({ id: openDropdownId } as CompetitionTeam)" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="closePresenceDropdown">
          {{ t('teams_page.presence_sheet') }} (FR)
        </a>
        <a :href="getPresenceEnUrl({ id: openDropdownId } as CompetitionTeam)" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="closePresenceDropdown">
          {{ t('teams_page.presence_sheet_en') }}
        </a>
        <a :href="getPresencePhotoUrl({ id: openDropdownId } as CompetitionTeam)" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="closePresenceDropdown">
          {{ t('teams_page.presence_sheet_photo') }}
        </a>
        <a
          v-if="canEditProperties"
          :href="getControlUrl({ id: openDropdownId } as CompetitionTeam)"
          target="_blank"
          class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-100 rounded-b-lg"
          @click="closePresenceDropdown"
        >
          {{ t('teams_page.control_sheet') }}
        </a>
      </div>
    </Teleport>

    <!-- Global PDF dropdown (teleported to body) -->
    <Teleport to="body">
      <div
        v-if="globalPdfOpen"
        class="global-pdf-menu fixed w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        :style="globalPdfStyle"
      >
        <a :href="globalPdfPoolsUrl" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg" @click="globalPdfOpen = false">
          {{ t('teams_page.pdf_pools') }}
        </a>
        <a :href="globalPdfPresenceFrUrl" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="globalPdfOpen = false">
          {{ t('teams_page.presence_sheet') }} (FR)
        </a>
        <a :href="globalPdfPresenceEnUrl" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="globalPdfOpen = false">
          {{ t('teams_page.presence_sheet_en') }}
        </a>
        <a :href="globalPdfPresenceCatUrl" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="globalPdfOpen = false">
          {{ t('teams_page.presence_sheet_cat') }}
        </a>
        <a :href="globalPdfPresencePhotoUrl" target="_blank" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="globalPdfOpen = false">
          {{ t('teams_page.presence_sheet_photo') }}
        </a>
        <a
          v-if="canEditProperties"
          :href="globalPdfControlUrl"
          target="_blank"
          class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-100 rounded-b-lg"
          @click="globalPdfOpen = false"
        >
          {{ t('teams_page.control_sheet') }}
        </a>
      </div>
    </Teleport>

    <!-- ========================================= -->
    <!-- MODALS -->
    <!-- ========================================= -->

    <!-- Add Team Modal -->
    <AdminModal
      :open="addModalOpen"
      :title="t('teams_page.add_modal.title')"
      max-width="xl"
      @close="addModalOpen = false"
    >
      <form @submit.prevent="saveAddForm">
        <div class="space-y-4 max-h-[70vh] overflow-y-auto px-1">
          <!-- Error -->
          <div
            v-if="addFormError"
            class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-6 h-6 shrink-0 mt-0.5" />
            <span class="text-sm">{{ addFormError }}</span>
          </div>

          <!-- Tab selector -->
          <div class="flex border-b border-gray-200">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
              :class="addFormTab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              @click="addFormTab = 'history'; addFormData.mode = 'history'"
            >
              {{ t('teams_page.add_modal.tab_history') }}
            </button>
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
              :class="addFormTab === 'manual' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              @click="addFormTab = 'manual'; addFormData.mode = 'manual'"
            >
              {{ t('teams_page.add_modal.tab_manual') }}
            </button>
          </div>

          <!-- Manual creation tab -->
          <template v-if="addFormTab === 'manual'">
            <!-- Team name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('teams_page.add_modal.libelle') }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model="addFormData.libelle"
                type="text"
                :placeholder="t('teams_page.add_modal.libelle_placeholder')"
                maxlength="30"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <!-- Club autocomplete -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('teams_page.add_modal.club') }}
              </label>
              <div class="relative">
                <input
                  v-model="clubSearchQuery"
                  type="text"
                  :placeholder="t('teams_page.add_modal.search_club')"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @input="searchClubs"
                >
                <div
                  v-if="clubSearchResults.length > 0"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                >
                  <button
                    v-for="club in clubSearchResults"
                    :key="club.code"
                    type="button"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 border-b border-gray-100 last:border-0"
                    @click="selectClub(club)"
                  >
                    <span class="font-medium">{{ club.code }}</span> - {{ club.libelle }}
                  </button>
                </div>
              </div>

              <!-- Club filters (collapsible) -->
              <details class="mt-2">
                <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                  {{ t('teams_page.add_modal.filter_cr') }} / {{ t('teams_page.add_modal.filter_cd') }}
                </summary>
                <div class="mt-2 grid grid-cols-3 gap-2">
                  <select
                    v-model="selectedCR"
                    class="px-2 py-1 border border-gray-300 rounded text-sm"
                  >
                    <option value="">{{ t('teams_page.add_modal.all') }}</option>
                    <option v-for="cr in regionalCommittees" :key="cr.code" :value="cr.code">
                      {{ cr.libelle }}
                    </option>
                  </select>
                  <select
                    v-model="selectedCD"
                    class="px-2 py-1 border border-gray-300 rounded text-sm"
                    :disabled="!selectedCR"
                  >
                    <option value="">{{ t('teams_page.add_modal.all') }}</option>
                    <option v-for="cd in departmentalCommittees" :key="cd.code" :value="cd.code">
                      {{ cd.libelle }}
                    </option>
                  </select>
                  <select
                    class="px-2 py-1 border border-gray-300 rounded text-sm"
                    :disabled="!selectedCD"
                    @change="(e) => { const v = (e.target as HTMLSelectElement).value; if (v) { addFormData.codeClub = v; const club = filteredClubs.find(c => c.code === v); if (club) clubSearchQuery = `${club.code} - ${club.libelle}`; } }"
                  >
                    <option value="">{{ t('teams_page.add_modal.all') }}</option>
                    <option v-for="club in filteredClubs" :key="club.code" :value="club.code">
                      {{ club.code }} - {{ club.libelle }}
                    </option>
                  </select>
                </div>
              </details>
            </div>
          </template>

          <!-- History tab -->
          <template v-if="addFormTab === 'history'">
            <!-- Search teams -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('teams_page.add_modal.search_team') }}
              </label>
              <input
                v-model="historySearchQuery"
                type="text"
                :placeholder="t('teams_page.add_modal.search_team')"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="searchHistoryTeams"
              >
            </div>

            <!-- Search results -->
            <div v-if="historySearchResults.length > 0" class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto">
              <!-- France teams -->
              <div v-if="historySearchResults.filter(t => !t.international).length > 0">
                <div class="px-3 py-1 bg-gray-50 text-xs font-medium text-gray-500 uppercase sticky top-0">
                  {{ t('teams_page.add_modal.france') }}
                </div>
                <div
                  v-for="ht in historySearchResults.filter(t => !t.international)"
                  :key="ht.numero"
                  class="flex items-center gap-2 px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
                  @click="toggleHistoryTeam(ht.numero)"
                >
                  <input
                    :checked="isHistoryTeamSelected(ht.numero)"
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 pointer-events-none"
                  >
                  <span class="text-sm flex-1">{{ ht.libelle }}</span>
                  <span class="text-xs text-gray-400">{{ ht.codeClub }}</span>
                </div>
              </div>

              <!-- International teams -->
              <div v-if="historySearchResults.filter(t => t.international).length > 0">
                <div class="px-3 py-1 bg-gray-50 text-xs font-medium text-gray-500 uppercase sticky top-0">
                  {{ t('teams_page.add_modal.international') }}
                </div>
                <div
                  v-for="ht in historySearchResults.filter(t => t.international)"
                  :key="ht.numero"
                  class="flex items-center gap-2 px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
                  @click="toggleHistoryTeam(ht.numero)"
                >
                  <input
                    :checked="isHistoryTeamSelected(ht.numero)"
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 pointer-events-none"
                  >
                  <span class="text-sm flex-1">{{ ht.libelle }}</span>
                  <span class="text-xs text-gray-400">{{ ht.codeClub }}</span>
                </div>
              </div>
            </div>

            <!-- Selected teams summary -->
            <div v-if="addFormData.teamNumbers.length > 0" class="text-sm text-blue-600 font-medium">
              {{ t('teams_page.add_modal.selected_teams', { count: addFormData.teamNumbers.length }) }}
            </div>

            <!-- Copy composition -->
            <div v-if="addFormData.teamNumbers.length === 1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="showCopyComposition"
                  type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-blue-600"
                  @change="showCopyComposition && loadCompositions(addFormData.teamNumbers[0])"
                >
                <span class="text-sm">{{ t('teams_page.add_modal.copy_composition') }}</span>
              </label>

              <div v-if="showCopyComposition" class="mt-2 ml-6">
                <select
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  @change="(e) => {
                    const val = (e.target as HTMLSelectElement).value
                    if (val) {
                      const [s, c] = val.split('|')
                      addFormData.copyComposition = { season: s, competition: c }
                    } else {
                      addFormData.copyComposition = null
                    }
                  }"
                >
                  <option value="">{{ t('teams_page.add_modal.select_source') }}</option>
                  <option
                    v-for="comp in compositions"
                    :key="`${comp.season}-${comp.competition}`"
                    :value="`${comp.season}|${comp.competition}`"
                  >
                    {{ comp.season }} - {{ comp.competitionLibelle }} ({{ t('teams_page.add_modal.players', { count: comp.playerCount }) }})
                  </option>
                </select>
                <p v-if="compositions.length === 0 && !compositionsLoading" class="text-xs text-gray-400 mt-1">
                  {{ t('teams_page.add_modal.no_compositions') }}
                </p>
              </div>
            </div>
          </template>

          <!-- Common fields: Poule and Tirage -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('teams_page.add_modal.poule') }}</label>
              <input
                v-model="addFormData.poule"
                type="text"
                :placeholder="t('teams_page.add_modal.poule_placeholder')"
                maxlength="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('teams_page.add_modal.tirage') }}</label>
              <input
                v-model.number="addFormData.tirage"
                type="number"
                min="0"
                max="99"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="addModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="addFormSaving"
          >
            <span v-if="addFormSaving" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin" />
              {{ t('common.save') }}
            </span>
            <span v-else>{{ t('common.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Edit Colors Modal -->
    <AdminModal
      :open="editModalOpen"
      :title="t('teams_page.edit_modal.title')"
      max-width="md"
      @close="editModalOpen = false"
    >
      <form @submit.prevent="saveColorsForm">
        <div class="space-y-4">
          <!-- Team name (read only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('teams_page.columns.equipe') }}</label>
            <div class="px-3 py-2 bg-gray-100 rounded-lg font-medium">{{ editingTeam?.libelle }}</div>
          </div>

          <!-- Logo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('teams_page.edit_modal.logo') }}</label>
            <input
              v-model="colorsFormData.logo"
              type="text"
              :placeholder="t('teams_page.edit_modal.logo_placeholder')"
              maxlength="50"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Colors -->
          <div class="grid grid-cols-3 gap-4 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('teams_page.edit_modal.color1') }}</label>
              <input
                v-model="colorsFormData.color1"
                type="color"
                class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer"
              >
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('teams_page.edit_modal.color2') }}</label>
              <input
                v-model="colorsFormData.color2"
                type="color"
                class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer"
              >
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('teams_page.edit_modal.colortext') }}</label>
              <input
                v-model="colorsFormData.colortext"
                type="color"
                class="w-full h-10 border border-gray-300 rounded-lg cursor-pointer"
              >
            </div>
          </div>

          <!-- Preview -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('teams_page.edit_modal.preview') }}</label>
            <div class="flex items-center gap-4">
              <span
                class="inline-flex items-center justify-center w-14 h-14 rounded-lg text-2xl font-bold"
                :style="{
                  backgroundColor: colorsFormData.color1,
                  borderColor: colorsFormData.color2,
                  borderWidth: '6px',
                  borderStyle: 'solid',
                  color: colorsFormData.colortext,
                }"
              >
                1
              </span>
              <span class="font-medium text-gray-700">{{ editingTeam?.libelle }}</span>
            </div>
          </div>

          <!-- Propagation options -->
          <div class="border border-gray-200 rounded-lg p-3 space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="colorsFormData.propagateNext" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
              <span class="text-sm">{{ t('teams_page.edit_modal.propagate_next') }}</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="colorsFormData.propagatePrevious" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
              <span class="text-sm">{{ t('teams_page.edit_modal.propagate_previous') }}</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-amber-700">
              <input v-model="colorsFormData.propagateClub" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-amber-600" >
              <span class="text-sm font-medium">{{ t('teams_page.edit_modal.propagate_club') }}</span>
            </label>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="editModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="colorsFormSaving"
          >
            <span v-if="colorsFormSaving" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin" />
              {{ t('common.save') }}
            </span>
            <span v-else>{{ t('common.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Duplicate Modal -->
    <AdminModal
      :open="duplicateModalOpen"
      :title="t('teams_page.duplicate_modal.title')"
      max-width="md"
      @close="duplicateModalOpen = false"
    >
      <form @submit.prevent="saveDuplicateForm">
        <div class="space-y-4">
          <!-- Source competition -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('teams_page.duplicate_modal.source_competition') }}
            </label>
            <select
              v-model="duplicateFormData.sourceCompetition"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">{{ t('teams_page.duplicate_modal.source_competition_placeholder') }}</option>
              <option v-for="comp in duplicateSourceOptions" :key="comp.code" :value="comp.code">
                {{ comp.code }} - {{ comp.libelle }}
              </option>
            </select>
          </div>

          <!-- Mode -->
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="duplicateFormData.mode" type="radio" value="append" class="w-4 h-4 text-blue-600" >
              <span class="text-sm">{{ t('teams_page.duplicate_modal.mode_append') }}</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="duplicateFormData.mode" type="radio" value="replace" class="w-4 h-4 text-blue-600" >
              <span class="text-sm">{{ t('teams_page.duplicate_modal.mode_replace') }}</span>
            </label>
          </div>

          <!-- Warning for replace mode -->
          <div
            v-if="duplicateFormData.mode === 'replace'"
            class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-6 h-6 shrink-0 mt-0.5" />
            <span class="text-sm">{{ t('teams_page.duplicate_modal.warning_replace') }}</span>
          </div>

          <!-- Copy players -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="duplicateFormData.copyPlayers" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
            <span class="text-sm">{{ t('teams_page.duplicate_modal.copy_players') }}</span>
          </label>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="duplicateModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="duplicateFormSaving || !duplicateFormData.sourceCompetition"
          >
            <span v-if="duplicateFormSaving" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin" />
              {{ t('teams_page.duplicate') }}
            </span>
            <span v-else>{{ t('teams_page.duplicate') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Delete Confirmation Modal -->
    <AdminConfirmModal
      :open="deleteModalOpen"
      :title="t('common.delete')"
      :message="t('teams_page.confirm_delete', { name: teamToDelete?.libelle || '' })"
      :item-name="teamToDelete?.libelle"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      danger
      @close="deleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <!-- Bulk Delete Confirmation Modal -->
    <AdminConfirmModal
      :open="bulkDeleteModalOpen"
      :title="t('teams_page.delete_selected')"
      :message="t('teams_page.confirm_delete_multiple', { count: selectedIds.length })"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      danger
      @close="bulkDeleteModalOpen = false"
      @confirm="confirmBulkDelete"
    />

    <!-- Init Starters Confirmation Modal -->
    <AdminConfirmModal
      :open="initStartersModalOpen"
      :title="t('teams_page.init_starters')"
      :message="t('teams_page.confirm_init_starters')"
      :confirm-text="t('common.confirm')"
      :cancel-text="t('common.cancel')"
      :loading="specialOpLoading"
      @close="initStartersModalOpen = false"
      @confirm="confirmInitStarters"
    />

    <!-- Update Logos Confirmation Modal -->
    <AdminConfirmModal
      :open="updateLogosModalOpen"
      :title="t('teams_page.update_logos')"
      :message="t('teams_page.confirm_update_logos')"
      :confirm-text="t('common.confirm')"
      :cancel-text="t('common.cancel')"
      :loading="specialOpLoading"
      @close="updateLogosModalOpen = false"
      @confirm="confirmUpdateLogos"
    />

    <!-- Scroll to top -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
