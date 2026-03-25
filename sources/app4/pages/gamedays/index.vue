<script setup lang="ts">
import type { Gameday, GamedayFormData, GamedayBulkCalendarData } from '~/types/gamedays'
import type { PaginatedResponse } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t, locale } = useI18n()
const api = useApi()
const toast = useToast()
const workContext = useWorkContextStore()
const authStore = useAuthStore()

// ─── State ───
const loading = ref(false)
const gamedays = ref<Gameday[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(25)
const totalPages = ref(0)

// Filters
const searchQuery = ref('')
const selectedMonth = ref('')
const selectedSort = ref('date_asc')

// Selection
const selectedIds = ref<number[]>([])

// Modals
const formModalOpen = ref(false)
const deleteConfirmOpen = ref(false)
const duplicateConfirmOpen = ref(false)
const bulkDeleteConfirmOpen = ref(false)
const bulkPublishConfirmOpen = ref(false)
const bulkCalendarModalOpen = ref(false)
const bulkOfficialsModalOpen = ref(false)
const officialsModalOpen = ref(false)
const eventAssociationOpen = ref(false)

// Form
const editingGameday = ref<Gameday | null>(null)
const formData = ref<GamedayFormData>(getDefaultFormData())
const formError = ref('')
const formSaving = ref(false)

// Duplicate
const duplicateGameday = ref<Gameday | null>(null)
const duplicateIncludeMatches = ref(false)

// Bulk calendar
const bulkCalendarData = ref<Omit<GamedayBulkCalendarData, 'ids'>>({
  nom: '',
  dateDebut: '',
  dateFin: '',
  lieu: '',
  departement: '',
})

// Bulk officials
const bulkOfficialsSourceId = ref<number | null>(null)

// Officials modal
const officialsGameday = ref<Gameday | null>(null)
const officialsFormData = ref({
  responsableInsc: '',
  responsableR1: '',
  delegue: '',
  chefArbitre: '',
  repAthletes: '',
  arbNj1: '',
  arbNj2: '',
  arbNj3: '',
  arbNj4: '',
  arbNj5: '',
})

// Legacy base URL for PDF
const legacyBaseUrl = computed(() => useRuntimeConfig().public.legacyBaseUrl || 'https://kpi.localhost')

// Bulk actions dropdown
const bulkActionsOpen = ref(false)
const bulkActionsRef = ref<HTMLDivElement | null>(null)

// Inline editing
const editingCell = ref<{ id: number; field: string } | null>(null)
const editingValue = ref('')
const editingOriginalValue = ref('')

// Event associations (loaded when panel is open)
const eventAssociations = ref<Set<number>>(new Set())
const eventAssociationLoading = ref(false)

// ─── Permissions ───
const canEdit = computed(() => authStore.profile <= 4)
const canSelect = computed(() => authStore.profile <= 3)
const canAssociateEvents = computed(() => authStore.profile <= 3)

// Open schema page in a new tab with competition and season params
function goToSchema(competitionCode: string) {
  const season = workContext.season || ''
  window.open(`/gamedays/schema?competition=${competitionCode}&season=${season}`, '_blank')
}

// ─── Computed ───
// Check if a single CP competition is selected (to show Niveau/Tour/Equipes columns)
const showCPColumns = computed(() => {
  if (!workContext.pageCompetitionCodeAll) return false
  const comp = gamedays.value.find(g => g.codeCompetition === workContext.pageCompetitionCodeAll)
  return comp?.competitionTypeClt === 'CP'
})

// ─── Default form data ───
function getDefaultFormData(): GamedayFormData {
  return {
    codeCompetition: '',
    codeSaison: workContext.season || '',
    phase: '',
    niveau: 1,
    etape: 1,
    nbEquipes: 1,
    type: 'C',
    dateDebut: '',
    dateFin: '',
    nom: '',
    libelle: '',
    lieu: '',
    departement: '',
    planEau: '',
    organisateur: '',
    codeOrganisateur: '',
    responsableInsc: '',
    responsableR1: '',
    delegue: '',
    chefArbitre: '',
    repAthletes: '',
    arbNj1: '',
    arbNj2: '',
    arbNj3: '',
    arbNj4: '',
    arbNj5: '',
  }
}

// ─── Load data ───
const loadGamedays = async () => {
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
      // A specific competition is selected
      params.competitions = workContext.pageCompetitionCodeAll
    } else if (workContext.pageEventGroupType === 'group') {
      // "All competitions" with a group selected: resolve group to competition codes
      const group = workContext.uniqueGroups.find(g => g.code === workContext.pageEventGroupValue)
      if (group) {
        const contextCodes = new Set(workContext.competitionCodes)
        const groupCodes = group.competitions.filter(c => contextCodes.has(c))
        if (groupCodes.length > 0) params.competitions = groupCodes.join(',')
      }
    } else if (workContext.pageEventGroupType === 'event') {
      // "All competitions" with an event selected
      params.event = workContext.pageEventGroupValue
    } else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }
    if (selectedMonth.value) {
      params.month = selectedMonth.value
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    const response = await api.get<PaginatedResponse<Gameday>>('/admin/gamedays', params)
    gamedays.value = response.items
    total.value = response.total
    totalPages.value = response.totalPages

    // Clear selection on data change
    selectedIds.value = []
  } catch (error) {
    console.error('Error loading gamedays:', error)
  } finally {
    loading.value = false
  }
}

// ─── Close dropdown on outside click ───
const onClickOutside = (e: MouseEvent) => {
  if (bulkActionsRef.value && !bulkActionsRef.value.contains(e.target as Node)) {
    bulkActionsOpen.value = false
  }
}
onMounted(() => {
  workContext.initContext()
  document.addEventListener('click', onClickOutside)
})
onUnmounted(() => document.removeEventListener('click', onClickOutside))

// ─── Watchers ───
watch(() => [workContext.initialized, workContext.season], () => {
  if (workContext.initialized && workContext.season) {
    loadGamedays()
  }
}, { immediate: true })

watch([page, limit, selectedSort], () => {
  loadGamedays()
})

watch([() => workContext.pageCompetitionCodeAll, () => workContext.pageEventGroupSelection, selectedMonth], () => {
  page.value = 1
  loadGamedays()
})

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadGamedays()
  }, 300)
})

// ─── Format helpers ───
const formatDate = (date: string | null) => {
  if (!date || date === '0000-00-00') return '-'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '-'
  if (locale.value === 'fr') {
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
  }
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const formatDateRange = (debut: string | null, fin: string | null) => {
  if (!debut && !fin) return '-'
  if (!fin || debut === fin) return formatDate(debut)
  return `${formatDate(debut)} - ${formatDate(fin)}`
}

// ─── Selection ───
const toggleSelectAll = () => {
  if (selectedIds.value.length === gamedays.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = gamedays.value.map(g => g.id)
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
const togglePublication = async (gameday: Gameday) => {
  try {
    const response = await api.patch<{ publication: boolean }>(`/admin/gamedays/${gameday.id}/publication`)
    gameday.publication = response.publication
  } catch {
    // Error already shown by useApi
  }
}

// ─── Toggle Type ───
const toggleType = async (gameday: Gameday) => {
  try {
    const response = await api.patch<{ type: string }>(`/admin/gamedays/${gameday.id}/type`)
    gameday.type = response.type as 'C' | 'E'
  } catch {
    // Error already shown by useApi
  }
}

// ─── Inline Editing ───
const inlineFieldMap: Record<string, keyof Gameday> = {
  Phase: 'phase',
  Niveau: 'niveau',
  Etape: 'etape',
  Nbequipes: 'nbEquipes',
  Nom: 'nom',
  Date_debut: 'dateDebut',
  Date_fin: 'dateFin',
  Lieu: 'lieu',
  Departement: 'departement',
}

const startInlineEdit = (gameday: Gameday, field: string) => {
  if (!canEdit.value) return
  editingCell.value = { id: gameday.id, field }
  const prop = inlineFieldMap[field]
  let val = prop ? String(gameday[prop] ?? '') : ''
  // For date fields, ensure YYYY-MM-DD format for input[type=date]
  if ((field === 'Date_debut' || field === 'Date_fin') && val.length > 10) {
    val = val.substring(0, 10)
  }
  editingValue.value = val
  editingOriginalValue.value = val
  nextTick(() => {
    const el = document.getElementById(`inline-${gameday.id}-${field}`)
    if (el) {
      el.focus()
      if (el instanceof HTMLInputElement && el.type !== 'date') el.select()
      if (el instanceof HTMLInputElement && el.type === 'date') {
        try { el.showPicker() } catch { /* ignore */ }
      }
    }
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return
  const { id, field } = editingCell.value
  const value = editingValue.value

  // Close editing
  editingCell.value = null

  // Only PATCH if value actually changed
  if (value === editingOriginalValue.value) return

  try {
    await api.patch(`/admin/gamedays/${id}/inline`, { field, value })
    // Refresh the row locally
    const gameday = gamedays.value.find(g => g.id === id)
    if (gameday) {
      const prop = inlineFieldMap[field]
      if (prop) {
        const numericFields = ['niveau', 'etape', 'nbEquipes']
        if (numericFields.includes(prop)) {
          ;(gameday as any)[prop] = value ? parseInt(value) : null
        } else {
          ;(gameday as any)[prop] = value || null
        }
      }
    }
    toast.add({ title: t('common.saved'), color: 'success' })
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

// ─── Modal: Add/Edit ───
const openAddModal = () => {
  editingGameday.value = null
  formData.value = getDefaultFormData()
  formData.value.codeSaison = workContext.season || ''
  // Pre-select competition from current filter, or if only one in context
  if (workContext.pageCompetitionCodeAll) {
    formData.value.codeCompetition = workContext.pageCompetitionCodeAll
  }
  else if (workContext.competitionCodes.length === 1) {
    formData.value.codeCompetition = workContext.competitionCodes[0] || ''
  }
  formError.value = ''
  formModalOpen.value = true
}

const openEditModal = (gameday: Gameday) => {
  editingGameday.value = gameday
  formData.value = {
    codeCompetition: gameday.codeCompetition,
    codeSaison: gameday.codeSaison,
    phase: gameday.phase || '',
    niveau: gameday.niveau ?? 1,
    etape: gameday.etape,
    nbEquipes: gameday.nbEquipes,
    type: gameday.type,
    dateDebut: gameday.dateDebut || '',
    dateFin: gameday.dateFin || '',
    nom: gameday.nom || '',
    libelle: gameday.libelle || '',
    lieu: gameday.lieu || '',
    departement: gameday.departement || '',
    planEau: gameday.planEau || '',
    organisateur: gameday.organisateur || '',
    codeOrganisateur: '',
    responsableInsc: gameday.responsableInsc || '',
    responsableR1: gameday.responsableR1 || '',
    delegue: gameday.delegue || '',
    chefArbitre: gameday.chefArbitre || '',
    repAthletes: gameday.repAthletes || '',
    arbNj1: gameday.arbNj1 || '',
    arbNj2: gameday.arbNj2 || '',
    arbNj3: gameday.arbNj3 || '',
    arbNj4: gameday.arbNj4 || '',
    arbNj5: gameday.arbNj5 || '',
  }
  formError.value = ''
  formModalOpen.value = true
}

const submitForm = async () => {
  formError.value = ''

  if (!formData.value.codeCompetition || !formData.value.codeSaison || !formData.value.phase) {
    formError.value = 'Compétition, saison et phase sont obligatoires.'
    return
  }

  formSaving.value = true
  try {
    if (editingGameday.value) {
      await api.put(`/admin/gamedays/${editingGameday.value.id}`, formData.value)
      toast.add({ title: t('common.success'), description: t('gamedays.updated'), color: 'success' })
    } else {
      await api.post('/admin/gamedays', formData.value)
      toast.add({ title: t('common.success'), description: t('gamedays.added'), color: 'success' })
    }
    formModalOpen.value = false
    await loadGamedays()
  } catch (error: any) {
    formError.value = error.message || t('common.error')
  } finally {
    formSaving.value = false
  }
}

// ─── Duplicate ───
const openDuplicateConfirm = (gameday: Gameday) => {
  duplicateGameday.value = gameday
  duplicateIncludeMatches.value = false
  duplicateConfirmOpen.value = true
}

const confirmDuplicate = async () => {
  if (!duplicateGameday.value) return
  formSaving.value = true
  try {
    await api.post(`/admin/gamedays/${duplicateGameday.value.id}/duplicate`, {
      includeMatches: duplicateIncludeMatches.value,
    })
    toast.add({ title: t('common.success'), description: t('gamedays.duplicated'), color: 'success' })
    duplicateConfirmOpen.value = false
    await loadGamedays()
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Delete ───
const gamedayToDelete = ref<Gameday | null>(null)

const openDeleteConfirm = (gameday: Gameday) => {
  gamedayToDelete.value = gameday
  deleteConfirmOpen.value = true
}

const confirmDelete = async () => {
  if (!gamedayToDelete.value) return
  formSaving.value = true
  try {
    await api.del(`/admin/gamedays/${gamedayToDelete.value.id}`)
    toast.add({ title: t('common.success'), description: t('gamedays.deleted'), color: 'success' })
    deleteConfirmOpen.value = false
    await loadGamedays()
  } catch (error: any) {
    const code = error?.code
    if (code === 'HAS_MATCHES') {
      toast.add({ title: t('common.error'), description: t('gamedays.delete_error_matches'), color: 'error' })
    } else if (code === 'HAS_EVENTS') {
      toast.add({ title: t('common.error'), description: t('gamedays.delete_error_events'), color: 'error' })
    }
    deleteConfirmOpen.value = false
  } finally {
    formSaving.value = false
  }
}

// ─── Bulk Actions ───
const confirmBulkPublish = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number; publication: boolean }>('/admin/gamedays/bulk/publication', {
      ids: selectedIds.value,
    })
    toast.add({
      title: t('common.success'),
      description: t('gamedays.bulk_published', { count: response.updated }),
      color: 'success',
    })
    bulkPublishConfirmOpen.value = false
    selectedIds.value = []
    await loadGamedays()
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const openBulkCalendarModal = () => {
  bulkCalendarData.value = { nom: '', dateDebut: '', dateFin: '', lieu: '', departement: '' }
  bulkCalendarModalOpen.value = true
}

const submitBulkCalendar = async () => {
  formSaving.value = true
  try {
    const response = await api.patch<{ updated: number }>('/admin/gamedays/bulk/calendar', {
      ids: selectedIds.value,
      ...bulkCalendarData.value,
    })
    toast.add({
      title: t('common.success'),
      description: t('gamedays.bulk_calendar_updated', { count: response.updated }),
      color: 'success',
    })
    bulkCalendarModalOpen.value = false
    selectedIds.value = []
    await loadGamedays()
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const confirmBulkDelete = async () => {
  formSaving.value = true
  try {
    const response = await api.del<{ deleted: number; skipped: { id: number; reason: string }[] }>('/admin/gamedays/bulk', {
      ids: selectedIds.value,
    })
    if (response.skipped && response.skipped.length > 0) {
      toast.add({
        title: t('common.success'),
        description: t('gamedays.bulk_deleted_partial', { deleted: response.deleted, skipped: response.skipped.length }),
        color: 'warning',
      })
    } else {
      toast.add({
        title: t('common.success'),
        description: t('gamedays.bulk_deleted', { deleted: response.deleted }),
        color: 'success',
      })
    }
    bulkDeleteConfirmOpen.value = false
    selectedIds.value = []
    await loadGamedays()
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

// ─── Event Association ───
const selectedEventId = computed(() => {
  if (workContext.pageEventGroupType === 'event') {
    return parseInt(workContext.pageEventGroupValue, 10)
  }
  return null
})

const openEventAssociation = async () => {
  if (!selectedEventId.value) return
  eventAssociationOpen.value = true
  eventAssociationLoading.value = true

  // Load current associations for this event
  try {
    const params: Record<string, string | number> = {
      season: workContext.season || '',
      event: selectedEventId.value,
      limit: 9999,
    }
    if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }
    const response = await api.get<PaginatedResponse<Gameday>>('/admin/gamedays', params)
    eventAssociations.value = new Set(response.items.map(g => g.id))
  } catch {
    // Silently fail
  } finally {
    eventAssociationLoading.value = false
  }
}

const toggleEventAssociation = async (gamedayId: number) => {
  const eventId = selectedEventId.value
  if (!eventId) return

  try {
    if (eventAssociations.value.has(gamedayId)) {
      await api.del(`/admin/gamedays/${gamedayId}/event/${eventId}`)
      eventAssociations.value.delete(gamedayId)
      toast.add({ title: t('common.success'), description: t('gamedays.event_unlinked'), color: 'success' })
    } else {
      await api.put(`/admin/gamedays/${gamedayId}/event/${eventId}`)
      eventAssociations.value.add(gamedayId)
      toast.add({ title: t('common.success'), description: t('gamedays.event_linked'), color: 'success' })
    }
  } catch {
    // Error already shown
  }
}

// ─── Months list ───
const months = computed(() => {
  return Array.from({ length: 12 }, (_, i) => ({
    value: String(i + 1),
    label: t(`gamedays.months.${i + 1}`),
  }))
})

// ─── Sort options ───
const sortOptions = computed(() => [
  { value: 'date_asc', label: t('gamedays.sort.date_asc') },
  { value: 'date_desc', label: t('gamedays.sort.date_desc') },
  { value: 'name', label: t('gamedays.sort.name') },
  { value: 'number', label: t('gamedays.sort.number') },
  { value: 'level', label: t('gamedays.sort.level') },
])

// ─── Officials summary ───
const getOfficialsSummary = (g: Gameday): string => {
  const parts: string[] = []
  if (g.responsableInsc) parts.push(`RC: ${g.responsableInsc}`)
  if (g.responsableR1) parts.push(`R1: ${g.responsableR1}`)
  if (g.delegue) parts.push(`Del: ${g.delegue}`)
  if (g.chefArbitre) parts.push(`Ref: ${g.chefArbitre}`)
  return parts.length > 0 ? parts.join(', ') : '-'
}

// ─── Bulk Officials Copy (Feature 1) ───
const bulkOfficialsSource = computed(() => {
  if (!bulkOfficialsSourceId.value) return null
  return gamedays.value.find(g => g.id === bulkOfficialsSourceId.value) ?? null
})

const openBulkOfficialsModal = () => {
  bulkOfficialsSourceId.value = null
  bulkOfficialsModalOpen.value = true
}

const submitBulkOfficials = async () => {
  if (!bulkOfficialsSourceId.value) return
  formSaving.value = true
  try {
    const targetIds = selectedIds.value.filter(id => id !== bulkOfficialsSourceId.value)
    const response = await api.patch<{ updated: number }>('/admin/gamedays/bulk/officials', {
      sourceId: bulkOfficialsSourceId.value,
      ids: targetIds,
    })
    toast.add({
      title: t('common.success'),
      description: t('gamedays.bulk_officials_updated', { count: response.updated }),
      color: 'success',
    })
    bulkOfficialsModalOpen.value = false
    selectedIds.value = []
    await loadGamedays()
  } catch {
    // Error already shown by useApi
  } finally {
    formSaving.value = false
  }
}

// ─── Officials Modal (Feature 2) ───
const openOfficialsModal = (g: Gameday) => {
  officialsGameday.value = g
  officialsFormData.value = {
    responsableInsc: g.responsableInsc || '',
    responsableR1: g.responsableR1 || '',
    delegue: g.delegue || '',
    chefArbitre: g.chefArbitre || '',
    repAthletes: g.repAthletes || '',
    arbNj1: g.arbNj1 || '',
    arbNj2: g.arbNj2 || '',
    arbNj3: g.arbNj3 || '',
    arbNj4: g.arbNj4 || '',
    arbNj5: g.arbNj5 || '',
  }
  officialsModalOpen.value = true
}

const saveOfficials = async () => {
  if (!officialsGameday.value) return
  formSaving.value = true
  try {
    await api.put(`/admin/gamedays/${officialsGameday.value.id}`, officialsFormData.value)
    // Update local data
    const g = gamedays.value.find(gd => gd.id === officialsGameday.value!.id)
    if (g) {
      Object.assign(g, {
        responsableInsc: officialsFormData.value.responsableInsc || null,
        responsableR1: officialsFormData.value.responsableR1 || null,
        delegue: officialsFormData.value.delegue || null,
        chefArbitre: officialsFormData.value.chefArbitre || null,
        repAthletes: officialsFormData.value.repAthletes || null,
        arbNj1: officialsFormData.value.arbNj1 || null,
        arbNj2: officialsFormData.value.arbNj2 || null,
        arbNj3: officialsFormData.value.arbNj3 || null,
        arbNj4: officialsFormData.value.arbNj4 || null,
        arbNj5: officialsFormData.value.arbNj5 || null,
      })
    }
    toast.add({ title: t('common.success'), description: t('gamedays.updated'), color: 'success' })
    officialsModalOpen.value = false
  } catch {
    // Error already shown
  } finally {
    formSaving.value = false
  }
}

const printJurySheet = (gamedayId: number) => {
  window.open(`${legacyBaseUrl.value}/admin/FeuilleInstances.php?idJournee=${gamedayId}`, '_blank')
}

</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader
      :title="t('gamedays.title')"
      :show-all-option="true"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
      @event-group-change="() => { page = 1 }"
      @competition-change="() => { page = 1 }"
    >
      <template #filters>
        <!-- Month filter -->
        <div class="min-w-36">
          <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.date_debut') }}</label>
          <select
            v-model="selectedMonth"
            class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">{{ t('gamedays.all_months') }}</option>
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <!-- Sort -->
        <div class="min-w-48">
          <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.sort.date_asc') }}</label>
          <select
            v-model="selectedSort"
            class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
      </template>
    </AdminPageHeader>

    <!-- Toolbar (search + add) -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('gamedays.search_placeholder')"
      :add-label="t('gamedays.add')"
      :show-add="canEdit"
      :selected-count="selectedIds.length"
      @add="openAddModal"
    >
      <template #left>
        <!-- Bulk actions dropdown -->
        <div v-if="canSelect && selectedIds.length > 0" ref="bulkActionsRef" class="relative">
          <button
            class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-primary-700 bg-primary-50 border border-primary-200 rounded-lg hover:bg-primary-100"
            @click="bulkActionsOpen = !bulkActionsOpen"
          >
            <UIcon name="heroicons:bolt" class="w-6 h-6" />
            {{ t('gamedays.bulk.actions') }}
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
              {{ selectedIds.length }}
            </span>
            <UIcon name="heroicons:chevron-down" class="w-6 h-6 transition-transform" :class="{ 'rotate-180': bulkActionsOpen }" />
          </button>
          <div v-show="bulkActionsOpen" class="absolute z-20 mt-1 w-72 bg-white border border-header-200 rounded-lg shadow-lg py-1 left-0">
            <!-- ── Toggle section ── -->
            <div class="px-3 py-1 text-[10px] font-semibold text-header-400 uppercase tracking-wider">{{ t('gamedays.bulk.toggle_section') }}</div>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-header-700 hover:bg-header-50"
              @click="bulkPublishConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:eye" class="w-5 h-5 text-success-500" />
              {{ t('gamedays.field.publication') }}
            </button>

            <!-- ── Edit section ── -->
            <div class="border-t border-header-100 my-1" />
            <div class="px-3 py-1 text-[10px] font-semibold text-header-400 uppercase tracking-wider">{{ t('gamedays.bulk.edit_section') }}</div>
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-header-700 hover:bg-header-50"
              @click="openBulkCalendarModal(); bulkActionsOpen = false"
            >
              <UIcon name="heroicons:calendar-days" class="w-5 h-5 text-primary-600" />
              {{ t('gamedays.calendar_public') }}
            </button>
            <button
              v-if="showCPColumns"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-header-700 hover:bg-header-50"
              @click="openBulkOfficialsModal(); bulkActionsOpen = false"
            >
              <UIcon name="heroicons:user-group" class="w-5 h-5 text-amber-600" />
              {{ t('gamedays.bulk_officials_title') }}
            </button>

            <!-- ── Event association ── -->
            <template v-if="canAssociateEvents && selectedEventId">
              <div class="border-t border-header-100 my-1" />
              <button
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-header-700 hover:bg-header-50"
                @click="openEventAssociation(); bulkActionsOpen = false"
              >
                <UIcon name="heroicons:link" class="w-5 h-5 text-purple-600" />
                {{ t('gamedays.manage_event_association') }}
              </button>
            </template>

            <!-- ── Danger section ── -->
            <div class="border-t border-header-100 my-1" />
            <button
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger-600 hover:bg-danger-50"
              @click="bulkDeleteConfirmOpen = true; bulkActionsOpen = false"
            >
              <UIcon name="heroicons:trash" class="w-5 h-5" />
              {{ t('common.delete_selected') }}
            </button>
          </div>
        </div>
      </template>
    </AdminToolbar>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-header-200">
          <thead class="bg-header-50">
            <tr>
              <!-- Checkbox -->
              <th v-if="canSelect" class="w-10 px-2 py-3">
                <input
                  type="checkbox"
                  class="rounded border-header-300"
                  :checked="selectedIds.length === gamedays.length && gamedays.length > 0"
                  :indeterminate="selectedIds.length > 0 && selectedIds.length < gamedays.length"
                  @change="toggleSelectAll"
                >
              </th>
              <!-- Publication -->
              <th class="w-10 px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">
                <UIcon name="heroicons:eye" class="w-6 h-6" />
              </th>
              <!-- Id -->
              <th class="px-2 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.id') }}</th>
              <!-- Actions -->
              <th v-if="canEdit" class="w-20 px-2 py-3" />
              <!-- Competition / Phase -->
              <th class="px-2 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.competition') }} / {{ t('gamedays.field.phase') }}</th>
              <!-- CP columns -->
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.niveau') }}</th>
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.etape') }}</th>
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.nb_equipes') }}</th>
              <!-- Type -->
              <th class="w-10 px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.type') }}</th>
              <!-- Calendar public columns (green headers) -->
              <th class="px-2 py-3 text-left text-xs font-medium text-success-700 uppercase bg-success-100">{{ t('gamedays.field.nom') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-success-700 uppercase bg-success-100">{{ t('gamedays.field.date_debut') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-success-700 uppercase bg-success-100">{{ t('gamedays.field.date_fin') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-success-700 uppercase bg-success-100">{{ t('gamedays.field.lieu') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-success-700 uppercase bg-success-100">{{ t('gamedays.field.departement') }}</th>
              <!-- Matches -->
              <th class="px-2 py-3 text-center text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.matches') }}</th>
              <!-- Officials -->
              <th class="px-2 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('gamedays.field.officiels') }}</th>
              <!-- Delete -->
              <th v-if="canEdit" class="w-10 px-2 py-3" />
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-header-200">
            <!-- Loading -->
            <tr v-if="loading && gamedays.length === 0">
              <td :colspan="canSelect ? 17 : 16" class="px-4 py-8 text-center text-header-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>
            <!-- Empty -->
            <tr v-else-if="gamedays.length === 0">
              <td :colspan="canSelect ? 17 : 16" class="px-4 py-8 text-center text-header-500">
                {{ t('gamedays.no_results') }}
              </td>
            </tr>
            <!-- Rows -->
            <tr
              v-for="g in gamedays"
              :key="g.id"
              class="hover:bg-header-50"
              :class="{ 'bg-primary-50': selectedIds.includes(g.id) }"
            >
              <!-- Checkbox -->
              <td v-if="canSelect" class="px-2 py-2" @click.stop>
                <input
                  type="checkbox"
                  class="rounded border-header-300"
                  :checked="selectedIds.includes(g.id)"
                  @change="toggleSelect(g.id)"
                >
              </td>
              <!-- Publication toggle -->
              <td class="px-2 py-2 text-center">
                <AdminToggleButton
                  :active="g.publication"
                  active-icon="heroicons:eye-solid"
                  inactive-icon="heroicons:eye-slash"
                  active-color="success"
                  :active-title="t('gamedays.published')"
                  :inactive-title="t('gamedays.unpublished')"
                  :disabled="!canEdit"
                  @toggle="togglePublication(g)"
                />
              </td>
              <!-- Id -->
              <td class="px-2 py-2 text-sm text-header-500 font-mono">{{ g.id }}</td>
              <!-- Actions -->
              <td v-if="canEdit" class="px-2 py-2" @click.stop>
                <div class="flex items-center gap-0.5">
                  <button :title="t('common.edit')" class="p-1 text-primary-600 hover:text-primary-800" @click="openEditModal(g)">
                    <UIcon name="heroicons:pencil" class="w-6 h-6" />
                  </button>
                  <button :title="t('gamedays.duplicate')" class="p-1 text-header-500 hover:text-header-700" @click="openDuplicateConfirm(g)">
                    <UIcon name="heroicons:document-duplicate" class="w-6 h-6" />
                  </button>
                  <button :title="t('schema.title')" class="p-1 text-header-500 hover:text-header-700" @click="goToSchema(g.codeCompetition)">
                    <UIcon name="heroicons:rectangle-group" class="w-6 h-6" />
                  </button>
                </div>
              </td>
              <!-- Competition / Phase -->
              <td class="px-2 py-2 text-sm">
                <span class="font-medium text-header-900 me-2">{{ g.codeCompetition }}</span>
                <!-- Inline editable Phase -->
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Phase'">
                  <input
                    :id="`inline-${g.id}-Phase`"
                    v-model="editingValue"
                    type="text"
                    maxlength="30"
                    class="w-full px-1 py-0.5 text-sm border border-primary-400 rounded focus:ring-1 focus:ring-primary-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="text-header-600"
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Phase')"
                  >{{ g.phase || '-' }}</span>
                </template>
              </td>
              <!-- CP columns (inline editable) -->
              <td v-if="showCPColumns" class="px-2 py-2 text-sm text-center">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Niveau'">
                  <input
                    :id="`inline-${g.id}-Niveau`"
                    v-model="editingValue"
                    type="number"
                    min="1"
                    class="w-12 px-1 py-0.5 text-sm text-center border border-primary-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Niveau')"
                  >{{ g.niveau ?? '-' }}</span>
                </template>
              </td>
              <td v-if="showCPColumns" class="px-2 py-2 text-sm text-center">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Etape'">
                  <input
                    :id="`inline-${g.id}-Etape`"
                    v-model="editingValue"
                    type="number"
                    min="1"
                    class="w-12 px-1 py-0.5 text-sm text-center border border-primary-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Etape')"
                  >{{ g.etape }}</span>
                </template>
              </td>
              <td v-if="showCPColumns" class="px-2 py-2 text-sm text-center">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Nbequipes'">
                  <input
                    :id="`inline-${g.id}-Nbequipes`"
                    v-model="editingValue"
                    type="number"
                    min="1"
                    class="w-12 px-1 py-0.5 text-sm text-center border border-primary-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Nbequipes')"
                  >{{ g.nbEquipes }}</span>
                </template>
              </td>
              <!-- Type toggle -->
              <td class="px-2 py-2 text-center">
                <button
                  :title="g.type === 'C' ? t('gamedays.field.type_c') : t('gamedays.field.type_e')"
                  class="p-1 rounded"
                  :class="canEdit ? 'hover:bg-header-100 cursor-pointer' : 'opacity-40 cursor-not-allowed'"
                  :disabled="!canEdit"
                  @click="canEdit && toggleType(g)"
                >
                  <UIcon
                    :name="g.type === 'C' ? 'heroicons:bars-3' : 'heroicons:arrows-right-left'"
                    class="w-6 h-6"
                    :class="g.type === 'C' ? 'text-primary-600' : 'text-orange-600'"
                  />
                </button>
              </td>
              <!-- Nom (calendar public - green bg, inline editable) -->
              <td class="px-2 py-2 text-sm bg-success-50">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Nom'">
                  <input
                    :id="`inline-${g.id}-Nom`"
                    v-model="editingValue"
                    type="text"
                    maxlength="80"
                    class="w-full px-1 py-0.5 text-sm border border-primary-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="font-medium text-header-900"
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Nom')"
                  >{{ g.nom || '-' }}</span>
                </template>
              </td>
              <!-- Date début (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-success-50 whitespace-nowrap">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Date_debut'">
                  <input
                    :id="`inline-${g.id}-Date_debut`"
                    v-model="editingValue"
                    type="date"
                    class="px-1 py-0.5 text-sm border border-primary-400 rounded focus:ring-1 focus:ring-primary-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="text-header-700"
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Date_debut')"
                  >{{ formatDate(g.dateDebut) }}</span>
                </template>
              </td>
              <!-- Date fin (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-success-50 whitespace-nowrap">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Date_fin'">
                  <input
                    :id="`inline-${g.id}-Date_fin`"
                    v-model="editingValue"
                    type="date"
                    class="px-1 py-0.5 text-sm border border-primary-400 rounded focus:ring-1 focus:ring-primary-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="text-header-700"
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Date_fin')"
                  >{{ formatDate(g.dateFin) }}</span>
                </template>
              </td>
              <!-- Lieu (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-success-50">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Lieu'">
                  <input
                    :id="`inline-${g.id}-Lieu`"
                    v-model="editingValue"
                    type="text"
                    maxlength="40"
                    class="w-full px-1 py-0.5 text-sm border border-primary-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Lieu')"
                  >{{ g.lieu || '-' }}</span>
                </template>
              </td>
              <!-- Departement (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-success-50">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Departement'">
                  <input
                    :id="`inline-${g.id}-Departement`"
                    v-model="editingValue"
                    type="text"
                    maxlength="3"
                    class="w-12 px-1 py-0.5 text-sm border border-primary-400 rounded uppercase"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'editable-cell' : ''"
                    @click="startInlineEdit(g, 'Departement')"
                  >{{ g.departement || '-' }}</span>
                </template>
              </td>
              <!-- Match count -->
              <td class="px-2 py-2 text-sm text-center">
                <NuxtLink
                  v-if="g.matchCount > 0"
                  :to="`/games?phase=${g.id}`"
                  class="link-value"
                >
                  {{ g.matchCount }}
                </NuxtLink>
                <span v-else class="text-header-400">0</span>
              </td>
              <!-- Officials -->
              <td class="px-2 py-2 text-xs text-header-600 max-w-48" @click.stop>
                <button
                  class="text-left hover:text-primary-600 truncate max-w-full"
                  :title="getOfficialsSummary(g)"
                  @click="openOfficialsModal(g)"
                >
                  {{ getOfficialsSummary(g) }}
                </button>
              </td>
              <!-- Delete -->
              <td v-if="canEdit && g.matchCount === 0" class="px-2 py-2" @click.stop>
                <button
                  class="p-1 text-danger-500 hover:text-danger-700"
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
        :showing-text="t('gamedays.total', { count: total })"
      />
    </div>

    <!-- Mobile Cards -->
    <AdminCardList class="lg:hidden" :loading="loading && gamedays.length === 0" :empty="gamedays.length === 0" :empty-text="t('gamedays.no_results')">
      <AdminCard
        v-for="g in gamedays"
        :key="g.id"
        :selected="selectedIds.includes(g.id)"
        :show-checkbox="canSelect"
        :checked="selectedIds.includes(g.id)"
        @toggle-select="toggleSelect(g.id)"
      >
        <template #header>
          <div>
            <div class="font-bold text-header-900">{{ g.codeCompetition }} - {{ g.phase || '?' }}</div>
            <div class="text-sm text-header-500">#{{ g.id }}</div>
          </div>
        </template>
        <template #header-right>
          <AdminToggleButton
            :active="g.publication"
            active-icon="heroicons:eye-solid"
            inactive-icon="heroicons:eye-slash"
            active-color="success"
            @toggle="canEdit && togglePublication(g)"
          />
        </template>

        <div class="space-y-1 text-sm">
          <div v-if="g.nom">
            <span class="text-success-700 font-medium">{{ g.nom }}</span>
          </div>
          <div v-if="g.dateDebut" class="flex items-center gap-1">
            <UIcon name="heroicons:calendar" class="w-6 h-6 text-header-400" />
            <span>{{ formatDateRange(g.dateDebut, g.dateFin) }}</span>
          </div>
          <div v-if="g.lieu" class="flex items-center gap-1">
            <UIcon name="heroicons:map-pin" class="w-6 h-6 text-header-400" />
            <span>{{ g.lieu }} <span v-if="g.departement">({{ g.departement }})</span></span>
          </div>
          <div class="flex items-center gap-3 text-xs text-header-500">
            <span>{{ g.type === 'C' ? t('gamedays.field.type_c') : t('gamedays.field.type_e') }}</span>
            <span v-if="g.matchCount > 0">{{ g.matchCount }} {{ t('gamedays.field.matches').toLowerCase() }}</span>
          </div>
          <button v-if="g.responsableInsc" class="text-xs text-header-500 hover:text-primary-600" @click.stop="openOfficialsModal(g)">
            RC: {{ g.responsableInsc }}
          </button>
        </div>

        <template #footer-right>
          <AdminActionButton v-if="canEdit" icon="heroicons:pencil" @click="openEditModal(g)">
            {{ t('common.edit') }}
          </AdminActionButton>
          <AdminActionButton v-if="canEdit" icon="heroicons:document-duplicate" @click="openDuplicateConfirm(g)">
            {{ t('gamedays.duplicated') }}
          </AdminActionButton>
          <AdminActionButton icon="heroicons:rectangle-group" @click="goToSchema(g.codeCompetition)">
            {{ t('schema.title') }}
          </AdminActionButton>
          <AdminActionButton v-if="canEdit" variant="danger" icon="heroicons:trash" @click="openDeleteConfirm(g)">
            {{ t('common.delete') }}
          </AdminActionButton>
        </template>
      </AdminCard>

      <!-- Mobile Pagination -->
      <AdminPagination
        v-if="gamedays.length > 0"
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('gamedays.total', { count: total })"
        class="mt-4 rounded-lg shadow"
      />
    </AdminCardList>

    <!-- ═══════ ADD/EDIT MODAL ═══════ -->
    <AdminModal
      :open="formModalOpen"
      :title="editingGameday ? t('gamedays.edit') : t('gamedays.add')"
      max-width="xl"
      @close="formModalOpen = false"
    >
      <form class="space-y-4" @submit.prevent="submitForm">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-800 text-sm">
          <UIcon name="heroicons:exclamation-triangle" class="w-6 h-6 inline mr-1" />
          {{ formError }}
        </div>

        <!-- Season + Competition -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.season') }} *</label>
            <input
              v-model="formData.codeSaison"
              type="text"
              :readonly="authStore.profile > 2"
              class="w-full px-3 py-2 border border-header-300 rounded-lg"
              :class="authStore.profile > 2 ? 'bg-header-100' : ''"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.competition') }} *</label>
            <AdminCompetitionGroupedSelect
              v-model="formData.codeCompetition"
              :disabled="authStore.profile > 2 && !!editingGameday"
            />
          </div>
        </div>

        <!-- Phase + Type -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.phase') }} *</label>
            <input
              v-model="formData.phase"
              type="text"
              maxlength="30"
              required
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              list="phase-suggestions"
            >
            <datalist id="phase-suggestions">
              <option v-for="letter in 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')" :key="letter" :value="locale === 'fr' ? `Poule ${letter}` : `Group ${letter}`" />
              <option :value="locale === 'fr' ? '1/8 Finale' : 'Round of 16'" />
              <option :value="locale === 'fr' ? '1/4 Finale' : 'Quarter-final'" />
              <option :value="locale === 'fr' ? '1/2 Finale' : 'Semi-final'" />
              <option :value="locale === 'fr' ? 'Finale' : 'Final'" />
              <option :value="locale === 'fr' ? 'Barrage' : 'Playoff'" />
            </datalist>
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.type') }}</label>
            <div class="flex items-center gap-4 mt-2">
              <label class="flex items-center gap-2">
                <input v-model="formData.type" type="radio" value="C" class="text-primary-600">
                <span class="text-sm">{{ t('gamedays.field.type_c') }}</span>
              </label>
              <label class="flex items-center gap-2">
                <input v-model="formData.type" type="radio" value="E" class="text-orange-600">
                <span class="text-sm">{{ t('gamedays.field.type_e') }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Niveau / Etape / NbEquipes -->
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.niveau') }}</label>
            <select v-model.number="formData.niveau" class="w-full px-3 py-2 border border-header-300 rounded-lg">
              <option v-for="n in 29" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.etape') }}</label>
            <select v-model.number="formData.etape" class="w-full px-3 py-2 border border-header-300 rounded-lg">
              <option v-for="n in 19" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.nb_equipes') }}</label>
            <select v-model.number="formData.nbEquipes" class="w-full px-3 py-2 border border-header-300 rounded-lg">
              <option v-for="n in 19" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.date_debut') }}</label>
            <input v-model="formData.dateDebut" type="date" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.date_fin') }}</label>
            <input v-model="formData.dateFin" type="date" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
        </div>

        <!-- Calendar public fields (highlighted) -->
        <div class="p-4 bg-success-50 rounded-lg border border-success-200 space-y-4">
          <h3 class="text-sm font-semibold text-success-800">{{ t('gamedays.calendar_public') }}</h3>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.nom') }}</label>
            <input v-model="formData.nom" type="text" maxlength="80" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.lieu') }}</label>
              <AdminTextAutocomplete
                :model-value="formData.lieu"
                api-url="/admin/gamedays/autocomplete/communes"
                label-field="label"
                detail-field="detail"
                :placeholder="t('gamedays.field.lieu')"
                :maxlength="40"
                @update:model-value="formData.lieu = $event"
                @select="(item: any) => { if (item.departement) formData.departement = item.departement }"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.departement') }}</label>
              <input v-model="formData.departement" type="text" maxlength="3" class="w-24 px-3 py-2 border border-header-300 rounded-lg uppercase">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.plan_eau') }}</label>
            <input v-model="formData.planEau" type="text" maxlength="80" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
        </div>

        <!-- Organisateur -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.organisateur') }}</label>
          <AdminTextAutocomplete
            :model-value="formData.organisateur"
            api-url="/admin/clubs/search-all"
            label-field="libelle"
            detail-field="code"
            :placeholder="t('gamedays.field.organisateur')"
            :maxlength="40"
            @update:model-value="formData.organisateur = $event"
          />
        </div>

        <!-- Officials (always visible) -->
        <div class="border border-header-200 rounded-lg">
          <div class="px-4 py-3 text-sm font-medium text-header-700 bg-header-50 rounded-t-lg">
            {{ t('gamedays.field.officiels') }}
          </div>
          <div class="p-4 border-t border-header-200 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.responsable_insc') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="formData.responsableInsc"
                  :placeholder="t('gamedays.field.responsable_insc')"
                  @update:model-value="formData.responsableInsc = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.responsable_r1') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="formData.responsableR1"
                  :placeholder="t('gamedays.field.responsable_r1')"
                  @update:model-value="formData.responsableR1 = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.delegue') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="formData.delegue"
                  :placeholder="t('gamedays.field.delegue')"
                  @update:model-value="formData.delegue = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.chef_arbitre') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="formData.chefArbitre"
                  :placeholder="t('gamedays.field.chef_arbitre')"
                  @update:model-value="formData.chefArbitre = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.rep_athletes') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="formData.repAthletes"
                  :placeholder="t('gamedays.field.rep_athletes')"
                  @update:model-value="formData.repAthletes = $event"
                />
              </div>
            </div>
            <div class="border-t border-header-100 pt-3">
              <label class="block text-xs font-medium text-header-500 mb-2">{{ t('gamedays.field.arb_nj') }}</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <AdminAthleteAutocomplete
                  :model-value="formData.arbNj1"
                  placeholder="1"
                  @update:model-value="formData.arbNj1 = $event"
                />
                <AdminAthleteAutocomplete
                  :model-value="formData.arbNj2"
                  placeholder="2"
                  @update:model-value="formData.arbNj2 = $event"
                />
                <AdminAthleteAutocomplete
                  :model-value="formData.arbNj3"
                  placeholder="3"
                  @update:model-value="formData.arbNj3 = $event"
                />
                <AdminAthleteAutocomplete
                  :model-value="formData.arbNj4"
                  placeholder="4"
                  @update:model-value="formData.arbNj4 = $event"
                />
                <AdminAthleteAutocomplete
                  :model-value="formData.arbNj5"
                  placeholder="5"
                  @update:model-value="formData.arbNj5 = $event"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="formModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
            :disabled="formSaving"
          >
            {{ formSaving ? t('common.loading') : t('common.save') }}
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- ═══════ DUPLICATE CONFIRM ═══════ -->
    <AdminModal
      :open="duplicateConfirmOpen"
      :title="t('gamedays.duplicate_confirm', { id: duplicateGameday?.id })"
      max-width="sm"
      @close="duplicateConfirmOpen = false"
    >
      <div class="space-y-4">
        <label class="flex items-center gap-2">
          <input v-model="duplicateIncludeMatches" type="checkbox" class="rounded border-header-300 text-primary-600">
          <span class="text-sm">{{ t('gamedays.include_matches') }}</span>
        </label>
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="duplicateConfirmOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
            :disabled="formSaving"
            @click="confirmDuplicate"
          >
            {{ t('common.confirm') }}
          </button>
        </div>
      </div>
    </AdminModal>

    <!-- ═══════ DELETE CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="deleteConfirmOpen"
      :title="t('gamedays.delete_confirm_title')"
      :message="t('gamedays.delete_confirm_message', { id: gamedayToDelete?.id, phase: gamedayToDelete?.phase || '?' })"
      :loading="formSaving"
      danger
      @close="deleteConfirmOpen = false"
      @confirm="confirmDelete"
    />

    <!-- ═══════ BULK PUBLISH CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkPublishConfirmOpen"
      :title="t('gamedays.field.publication')"
      :message="t('gamedays.publish_confirm')"
      :loading="formSaving"
      @close="bulkPublishConfirmOpen = false"
      @confirm="confirmBulkPublish"
    />

    <!-- ═══════ BULK DELETE CONFIRM ═══════ -->
    <AdminConfirmModal
      :open="bulkDeleteConfirmOpen"
      :title="t('common.delete_selected')"
      :message="t('gamedays.bulk_delete_confirm', { count: selectedIds.length })"
      :loading="formSaving"
      danger
      @close="bulkDeleteConfirmOpen = false"
      @confirm="confirmBulkDelete"
    />

    <!-- ═══════ BULK CALENDAR MODAL ═══════ -->
    <AdminModal
      :open="bulkCalendarModalOpen"
      :title="t('gamedays.bulk_calendar_title')"
      max-width="md"
      @close="bulkCalendarModalOpen = false"
    >
      <form class="space-y-4" @submit.prevent="submitBulkCalendar">
        <p class="text-sm text-header-600">
          {{ t('gamedays.bulk_calendar_hint', { count: selectedIds.length }) }}
        </p>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.nom') }}</label>
          <input v-model="bulkCalendarData.nom" type="text" maxlength="80" class="w-full px-3 py-2 border border-header-300 rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.date_debut') }}</label>
            <input v-model="bulkCalendarData.dateDebut" type="date" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.date_fin') }}</label>
            <input v-model="bulkCalendarData.dateFin" type="date" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.lieu') }}</label>
            <input v-model="bulkCalendarData.lieu" type="text" maxlength="40" class="w-full px-3 py-2 border border-header-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.field.departement') }}</label>
            <input v-model="bulkCalendarData.departement" type="text" maxlength="3" class="w-24 px-3 py-2 border border-header-300 rounded-lg uppercase">
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="bulkCalendarModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
            :disabled="formSaving"
          >
            {{ t('common.save') }}
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- ═══════ EVENT ASSOCIATION MODAL ═══════ -->
    <AdminModal
      :open="eventAssociationOpen"
      :title="t('gamedays.event_association_title')"
      max-width="lg"
      @close="eventAssociationOpen = false"
    >
      <div class="space-y-4">
        <p class="text-sm text-header-600">
          {{ t('gamedays.event_association_hint') }}
        </p>
        <div v-if="eventAssociationLoading" class="text-center py-4">
          <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto" />
        </div>
        <div v-else class="max-h-96 overflow-y-auto border rounded-lg divide-y">
          <label
            v-for="g in gamedays"
            :key="g.id"
            class="flex items-center gap-3 px-4 py-2 hover:bg-header-50 cursor-pointer"
          >
            <input
              type="checkbox"
              class="rounded border-header-300 text-purple-600"
              :checked="eventAssociations.has(g.id)"
              @change="toggleEventAssociation(g.id)"
            >
            <div class="text-sm">
              <span class="font-medium">#{{ g.id }}</span>
              <span class="text-header-600 ml-1">{{ g.codeCompetition }} - {{ g.phase }}</span>
              <span v-if="g.nom" class="text-header-500 ml-1">| {{ g.nom }}</span>
            </div>
          </label>
        </div>
      </div>
    </AdminModal>

    <!-- ═══════ BULK OFFICIALS COPY MODAL (Feature 1) ═══════ -->
    <AdminModal
      :open="bulkOfficialsModalOpen"
      :title="t('gamedays.bulk_officials_title')"
      max-width="lg"
      @close="bulkOfficialsModalOpen = false"
    >
      <div class="space-y-4">
        <p class="text-sm text-header-600">
          {{ t('gamedays.bulk_officials_hint', { count: selectedIds.length }) }}
        </p>

        <!-- Source phase selector -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('gamedays.bulk_officials_source') }}</label>
          <select
            v-model.number="bulkOfficialsSourceId"
            class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option :value="null">{{ t('gamedays.bulk_officials_select_source') }}</option>
            <option v-for="g in gamedays" :key="g.id" :value="g.id">
              #{{ g.id }} - {{ g.phase || '?' }} ({{ g.nom || '-' }})
            </option>
          </select>
        </div>

        <!-- Preview of source data -->
        <div v-if="bulkOfficialsSource" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm space-y-1">
          <p class="font-medium text-amber-800 mb-2">{{ t('gamedays.bulk_officials_preview') }}</p>
          <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-header-700">
            <div class="text-success-700 font-medium col-span-2 mt-1">{{ t('gamedays.calendar_public') }}</div>
            <div>{{ t('gamedays.field.nom') }}: <span class="font-medium">{{ bulkOfficialsSource.nom || '-' }}</span></div>
            <div>{{ t('gamedays.field.lieu') }}: <span class="font-medium">{{ bulkOfficialsSource.lieu || '-' }} {{ bulkOfficialsSource.departement ? `(${bulkOfficialsSource.departement})` : '' }}</span></div>
            <div>{{ t('gamedays.field.date_debut') }}: <span class="font-medium">{{ formatDate(bulkOfficialsSource.dateDebut) }}</span></div>
            <div>{{ t('gamedays.field.date_fin') }}: <span class="font-medium">{{ formatDate(bulkOfficialsSource.dateFin) }}</span></div>
            <div class="text-amber-800 font-medium col-span-2 mt-1">{{ t('gamedays.field.officiels') }}</div>
            <div v-if="bulkOfficialsSource.responsableInsc">{{ t('gamedays.field.responsable_insc') }}: {{ bulkOfficialsSource.responsableInsc }}</div>
            <div v-if="bulkOfficialsSource.responsableR1">{{ t('gamedays.field.responsable_r1') }}: {{ bulkOfficialsSource.responsableR1 }}</div>
            <div v-if="bulkOfficialsSource.delegue">{{ t('gamedays.field.delegue') }}: {{ bulkOfficialsSource.delegue }}</div>
            <div v-if="bulkOfficialsSource.chefArbitre">{{ t('gamedays.field.chef_arbitre') }}: {{ bulkOfficialsSource.chefArbitre }}</div>
            <div v-if="bulkOfficialsSource.repAthletes">{{ t('gamedays.field.rep_athletes') }}: {{ bulkOfficialsSource.repAthletes }}</div>
            <div v-if="bulkOfficialsSource.arbNj1">{{ t('gamedays.field.arb_nj') }} 1: {{ bulkOfficialsSource.arbNj1 }}</div>
            <div v-if="bulkOfficialsSource.arbNj2">{{ t('gamedays.field.arb_nj') }} 2: {{ bulkOfficialsSource.arbNj2 }}</div>
            <div v-if="bulkOfficialsSource.arbNj3">{{ t('gamedays.field.arb_nj') }} 3: {{ bulkOfficialsSource.arbNj3 }}</div>
            <div v-if="bulkOfficialsSource.arbNj4">{{ t('gamedays.field.arb_nj') }} 4: {{ bulkOfficialsSource.arbNj4 }}</div>
            <div v-if="bulkOfficialsSource.arbNj5">{{ t('gamedays.field.arb_nj') }} 5: {{ bulkOfficialsSource.arbNj5 }}</div>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="bulkOfficialsModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50"
            :disabled="formSaving || !bulkOfficialsSourceId"
            @click="submitBulkOfficials"
          >
            {{ formSaving ? t('common.loading') : t('common.confirm') }}
          </button>
        </div>
      </div>
    </AdminModal>

    <!-- ═══════ OFFICIALS MODAL (Feature 2) ═══════ -->
    <AdminModal
      :open="officialsModalOpen"
      :title="t('gamedays.officials_title')"
      max-width="xl"
      @close="officialsModalOpen = false"
    >
      <div v-if="officialsGameday" class="space-y-4">
        <!-- Header info -->
        <div class="text-sm text-header-600">
          <span class="font-medium text-header-900">{{ officialsGameday.codeCompetition }} - {{ officialsGameday.phase }}</span>
          <span v-if="officialsGameday.nom" class="ml-2">| {{ officialsGameday.nom }}</span>
          <span v-if="officialsGameday.lieu" class="ml-2">| {{ officialsGameday.lieu }}</span>
        </div>

        <!-- Competition Committee -->
        <div class="border border-header-200 rounded-lg">
          <div class="px-4 py-2 text-sm font-medium text-header-700 bg-header-50 rounded-t-lg">
            {{ t('gamedays.officials_comite') }}
          </div>
          <div class="p-4 border-t border-header-200 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.responsable_insc') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="officialsFormData.responsableInsc"
                  :placeholder="t('gamedays.field.responsable_insc')"
                  @update:model-value="officialsFormData.responsableInsc = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.responsable_r1') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="officialsFormData.responsableR1"
                  :placeholder="t('gamedays.field.responsable_r1')"
                  @update:model-value="officialsFormData.responsableR1 = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.delegue') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="officialsFormData.delegue"
                  :placeholder="t('gamedays.field.delegue')"
                  @update:model-value="officialsFormData.delegue = $event"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.chef_arbitre') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="officialsFormData.chefArbitre"
                  :placeholder="t('gamedays.field.chef_arbitre')"
                  @update:model-value="officialsFormData.chefArbitre = $event"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Appeal Jury -->
        <div class="border border-header-200 rounded-lg">
          <div class="px-4 py-2 text-sm font-medium text-header-700 bg-header-50 rounded-t-lg">
            {{ t('gamedays.officials_jury') }}
          </div>
          <div class="p-4 border-t border-header-200 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.delegue') }} ({{ t('gamedays.field.delegue') }})</label>
                <div class="px-3 py-2 text-sm bg-header-50 border border-header-200 rounded-lg text-header-600">
                  {{ officialsFormData.delegue || '-' }}
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.responsable_r1') }}</label>
                <div class="px-3 py-2 text-sm bg-header-50 border border-header-200 rounded-lg text-header-600">
                  {{ officialsFormData.responsableR1 || '-' }}
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-header-500 mb-1">{{ t('gamedays.field.rep_athletes') }}</label>
                <AdminAthleteAutocomplete
                  :model-value="officialsFormData.repAthletes"
                  :placeholder="t('gamedays.field.rep_athletes')"
                  @update:model-value="officialsFormData.repAthletes = $event"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Non-player referees -->
        <div class="border border-header-200 rounded-lg">
          <div class="px-4 py-2 text-sm font-medium text-header-700 bg-header-50 rounded-t-lg">
            {{ t('gamedays.field.arb_nj') }}
          </div>
          <div class="p-4 border-t border-header-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
              <AdminAthleteAutocomplete
                :model-value="officialsFormData.arbNj1"
                placeholder="1"
                @update:model-value="officialsFormData.arbNj1 = $event"
              />
              <AdminAthleteAutocomplete
                :model-value="officialsFormData.arbNj2"
                placeholder="2"
                @update:model-value="officialsFormData.arbNj2 = $event"
              />
              <AdminAthleteAutocomplete
                :model-value="officialsFormData.arbNj3"
                placeholder="3"
                @update:model-value="officialsFormData.arbNj3 = $event"
              />
              <AdminAthleteAutocomplete
                :model-value="officialsFormData.arbNj4"
                placeholder="4"
                @update:model-value="officialsFormData.arbNj4 = $event"
              />
              <AdminAthleteAutocomplete
                :model-value="officialsFormData.arbNj5"
                placeholder="5"
                @update:model-value="officialsFormData.arbNj5 = $event"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-danger-700 bg-white border border-danger-300 rounded-lg hover:bg-danger-50"
            @click="printJurySheet(officialsGameday.id)"
          >
            <UIcon name="heroicons:document-text" class="w-5 h-5 inline mr-1" />
            {{ t('gamedays.officials_print') }}
          </button>
          <div class="flex gap-2">
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
              @click="officialsModalOpen = false"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              v-if="canEdit"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
              :disabled="formSaving"
              @click="saveOfficials"
            >
              {{ formSaving ? t('common.loading') : t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </AdminModal>

    <!-- Scroll to top -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
