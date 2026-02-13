<script setup lang="ts">
import type { Gameday, GamedayFormData, GamedayBulkCalendarData, GamedayEvent } from '~/types/gamedays'
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
const selectedCompetitions = ref<string[]>([])
const selectedEvent = ref('-1')
const selectedMonth = ref('')
const selectedSort = ref('date_asc')
const filterOpen = ref(false)

// Events for filter dropdown
const events = ref<GamedayEvent[]>([])

// Selection
const selectedIds = ref<number[]>([])

// Modals
const formModalOpen = ref(false)
const deleteConfirmOpen = ref(false)
const duplicateConfirmOpen = ref(false)
const bulkDeleteConfirmOpen = ref(false)
const bulkPublishConfirmOpen = ref(false)
const bulkCalendarModalOpen = ref(false)
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
  ids: [],
  nom: '',
  dateDebut: '',
  dateFin: '',
  lieu: '',
  departement: '',
})

// Inline editing
const editingCell = ref<{ id: number; field: string } | null>(null)
const editingValue = ref('')

// Event associations (loaded when panel is open)
const eventAssociations = ref<Set<number>>(new Set())
const eventAssociationLoading = ref(false)

// ─── Permissions ───
const canEdit = computed(() => authStore.profile <= 4)
const canSelect = computed(() => authStore.profile <= 3)
const canAssociateEvents = computed(() => authStore.profile <= 3)

// ─── Computed ───
// Check if a single CP competition is selected (to show Niveau/Tour/Equipes columns)
const showCPColumns = computed(() => {
  if (selectedCompetitions.value.length !== 1) return false
  const comp = gamedays.value.find(g => g.codeCompetition === selectedCompetitions.value[0])
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

    if (selectedCompetitions.value.length > 0) {
      params.competitions = selectedCompetitions.value.join(',')
    } else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }

    if (selectedEvent.value && selectedEvent.value !== '-1') {
      params.event = selectedEvent.value
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

const loadEvents = async () => {
  try {
    const data = await api.get<{ items: GamedayEvent[] }>('/admin/gamedays/events')
    events.value = data.items || []
  } catch {
    // Silently fail
  }
}

// ─── Watchers ───
watch(() => [workContext.initialized, workContext.season], () => {
  if (workContext.initialized && workContext.season) {
    loadGamedays()
    loadEvents()
  }
}, { immediate: true })

watch([page, limit, selectedSort], () => {
  loadGamedays()
})

watch([selectedCompetitions, selectedEvent, selectedMonth], () => {
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
  if (!date) return '-'
  const d = new Date(date)
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
const startInlineEdit = (gameday: Gameday, field: string) => {
  if (!canEdit.value) return
  editingCell.value = { id: gameday.id, field }
  const fieldMap: Record<string, keyof Gameday> = {
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
  const prop = fieldMap[field]
  editingValue.value = prop ? String(gameday[prop] ?? '') : ''
  nextTick(() => {
    const input = document.getElementById(`inline-${gameday.id}-${field}`)
    input?.focus()
    if (input instanceof HTMLInputElement) input.select()
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return
  const { id, field } = editingCell.value
  try {
    await api.patch(`/admin/gamedays/${id}/inline`, { field, value: editingValue.value })
    // Refresh the row locally
    const gameday = gamedays.value.find(g => g.id === id)
    if (gameday) {
      const fieldMap: Record<string, keyof Gameday> = {
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
      const prop = fieldMap[field]
      if (prop) {
        const numericFields = ['niveau', 'etape', 'nbEquipes']
        if (numericFields.includes(prop)) {
          ;(gameday as any)[prop] = editingValue.value ? parseInt(editingValue.value) : null
        } else {
          ;(gameday as any)[prop] = editingValue.value || null
        }
      }
    }
  } catch {
    // Error already shown by useApi
  }
  editingCell.value = null
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
  // Pre-select competition if only one in context
  if (workContext.competitionCodes.length === 1) {
    formData.value.codeCompetition = workContext.competitionCodes[0]
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
  bulkCalendarData.value = { ids: [], nom: '', dateDebut: '', dateFin: '', lieu: '', departement: '' }
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
const openEventAssociation = async () => {
  if (!selectedEvent.value || selectedEvent.value === '-1') return
  eventAssociationOpen.value = true
  eventAssociationLoading.value = true

  // Load current associations for this event
  try {
    // Fetch all gameday IDs for this event from the main list (re-query without event filter to get all)
    const params: Record<string, string | number> = {
      season: workContext.season || '',
      event: selectedEvent.value,
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
  const eventId = parseInt(selectedEvent.value)
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
  return parts.length > 0 ? parts.join(', ') : '-'
}
</script>

<template>
  <div>
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Title -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('gamedays.title') }}
      </h1>
    </div>

    <!-- Filters Row -->
    <div class="flex flex-wrap gap-3 items-end">
      <!-- Event filter -->
      <div class="min-w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.event') }}</label>
        <select
          v-model="selectedEvent"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="-1">{{ t('gamedays.all_events') }}</option>
          <option v-for="evt in events" :key="evt.id" :value="String(evt.id)">
            {{ evt.id }} - {{ evt.libelle }}
          </option>
        </select>
      </div>

      <!-- Competition Filter (collapsible) -->
      <div class="bg-white rounded-lg shadow">
        <button
          class="w-full flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors"
          @click="filterOpen = !filterOpen"
        >
          <div class="flex items-center gap-2">
            <UIcon name="heroicons:funnel" class="w-4 h-4 text-gray-500" />
            <span class="text-sm font-medium text-gray-700">{{ t('rc.filter_competitions') }}</span>
            <span v-if="selectedCompetitions.length > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {{ selectedCompetitions.length }}
            </span>
          </div>
          <UIcon
            name="heroicons:chevron-down"
            class="w-4 h-4 text-gray-400 transition-transform"
            :class="{ 'rotate-180': filterOpen }"
          />
        </button>
        <div v-show="filterOpen" class="px-4 pb-4 border-t border-gray-100">
          <AdminCompetitionMultiSelect
            v-model="selectedCompetitions"
            :competitions="workContext.competitions || []"
          />
        </div>
      </div>

      <!-- Month filter -->
      <div class="min-w-36">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.date_debut') }}</label>
        <select
          v-model="selectedMonth"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="">{{ t('gamedays.all_months') }}</option>
          <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
      </div>

      <!-- Sort -->
      <div class="min-w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.sort.date_asc') }}</label>
        <select
          v-model="selectedSort"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>

    <!-- Toolbar (search + add + bulk) -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('gamedays.search_placeholder')"
      :add-label="t('gamedays.add')"
      :show-add="canEdit"
      :show-bulk-delete="canSelect"
      :bulk-delete-label="t('common.delete_selected')"
      :selected-count="selectedIds.length"
      @add="openAddModal"
      @bulk-delete="bulkDeleteConfirmOpen = true"
    >
      <template #after-search>
        <!-- Bulk publish -->
        <button
          v-if="canEdit && selectedIds.length > 0"
          class="px-3 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100"
          @click="bulkPublishConfirmOpen = true"
        >
          <UIcon name="heroicons:eye" class="w-4 h-4 inline mr-1" />
          {{ t('gamedays.field.publication') }}
        </button>
        <!-- Bulk calendar edit -->
        <button
          v-if="canEdit && selectedIds.length > 0"
          class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100"
          @click="openBulkCalendarModal"
        >
          <UIcon name="heroicons:calendar-days" class="w-4 h-4 inline mr-1" />
          {{ t('gamedays.calendar_public') }}
        </button>
        <!-- Event association -->
        <button
          v-if="canAssociateEvents && selectedEvent !== '-1'"
          class="px-3 py-2 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100"
          @click="openEventAssociation"
        >
          <UIcon name="heroicons:link" class="w-4 h-4 inline mr-1" />
          {{ t('gamedays.manage_event_association') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Checkbox -->
              <th v-if="canSelect" class="w-10 px-2 py-3">
                <input
                  type="checkbox"
                  class="rounded border-gray-300"
                  :checked="selectedIds.length === gamedays.length && gamedays.length > 0"
                  :indeterminate="selectedIds.length > 0 && selectedIds.length < gamedays.length"
                  @change="toggleSelectAll"
                >
              </th>
              <!-- Publication -->
              <th class="w-10 px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                <UIcon name="heroicons:eye" class="w-4 h-4" />
              </th>
              <!-- Id -->
              <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.id') }}</th>
              <!-- Actions -->
              <th v-if="canEdit" class="w-20 px-2 py-3" />
              <!-- Competition / Phase -->
              <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.competition') }} / {{ t('gamedays.field.phase') }}</th>
              <!-- CP columns -->
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.niveau') }}</th>
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.etape') }}</th>
              <th v-if="showCPColumns" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.nb_equipes') }}</th>
              <!-- Type -->
              <th class="w-10 px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.type') }}</th>
              <!-- Calendar public columns (green headers) -->
              <th class="px-2 py-3 text-left text-xs font-medium text-green-700 uppercase bg-green-50">{{ t('gamedays.field.nom') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-green-700 uppercase bg-green-50">{{ t('gamedays.field.date_debut') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-green-700 uppercase bg-green-50">{{ t('gamedays.field.lieu') }}</th>
              <th class="px-2 py-3 text-left text-xs font-medium text-green-700 uppercase bg-green-50">{{ t('gamedays.field.departement') }}</th>
              <!-- Matches -->
              <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.matches') }}</th>
              <!-- Officials -->
              <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('gamedays.field.officiels') }}</th>
              <!-- Delete -->
              <th v-if="canEdit" class="w-10 px-2 py-3" />
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading -->
            <tr v-if="loading && gamedays.length === 0">
              <td :colspan="canSelect ? 16 : 15" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>
            <!-- Empty -->
            <tr v-else-if="gamedays.length === 0">
              <td :colspan="canSelect ? 16 : 15" class="px-4 py-8 text-center text-gray-500">
                {{ t('gamedays.no_results') }}
              </td>
            </tr>
            <!-- Rows -->
            <tr
              v-for="g in gamedays"
              :key="g.id"
              class="hover:bg-gray-50"
              :class="{ 'bg-blue-50': selectedIds.includes(g.id) }"
            >
              <!-- Checkbox -->
              <td v-if="canSelect" class="px-2 py-2" @click.stop>
                <input
                  type="checkbox"
                  class="rounded border-gray-300"
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
                  active-color="green"
                  :active-title="t('gamedays.published')"
                  :inactive-title="t('gamedays.unpublished')"
                  @toggle="canEdit && togglePublication(g)"
                />
              </td>
              <!-- Id -->
              <td class="px-2 py-2 text-sm text-gray-500 font-mono">{{ g.id }}</td>
              <!-- Actions -->
              <td v-if="canEdit" class="px-2 py-2" @click.stop>
                <div class="flex items-center gap-0.5">
                  <button :title="t('common.edit')" class="p-1 text-blue-600 hover:text-blue-800" @click="openEditModal(g)">
                    <UIcon name="heroicons:pencil" class="w-4 h-4" />
                  </button>
                  <button :title="t('gamedays.duplicate')" class="p-1 text-gray-500 hover:text-gray-700" @click="openDuplicateConfirm(g)">
                    <UIcon name="heroicons:document-duplicate" class="w-4 h-4" />
                  </button>
                </div>
              </td>
              <!-- Competition / Phase -->
              <td class="px-2 py-2 text-sm">
                <div class="font-medium text-gray-900">{{ g.codeCompetition }}</div>
                <!-- Inline editable Phase -->
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Phase'">
                  <input
                    :id="`inline-${g.id}-Phase`"
                    v-model="editingValue"
                    type="text"
                    maxlength="30"
                    class="w-full px-1 py-0.5 text-sm border border-blue-400 rounded focus:ring-1 focus:ring-blue-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="text-gray-600"
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 hover:outline-1 hover:outline-yellow-300 px-1 rounded' : ''"
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
                    class="w-12 px-1 py-0.5 text-sm text-center border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
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
                    class="w-12 px-1 py-0.5 text-sm text-center border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
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
                    class="w-12 px-1 py-0.5 text-sm text-center border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
                    @click="startInlineEdit(g, 'Nbequipes')"
                  >{{ g.nbEquipes }}</span>
                </template>
              </td>
              <!-- Type toggle -->
              <td class="px-2 py-2 text-center">
                <button
                  :title="g.type === 'C' ? t('gamedays.field.type_c') : t('gamedays.field.type_e')"
                  class="p-1 rounded"
                  :class="canEdit ? 'hover:bg-gray-100 cursor-pointer' : 'cursor-default'"
                  @click="canEdit && toggleType(g)"
                >
                  <UIcon
                    :name="g.type === 'C' ? 'heroicons:bars-3' : 'heroicons:arrows-right-left'"
                    class="w-4 h-4"
                    :class="g.type === 'C' ? 'text-blue-600' : 'text-orange-600'"
                  />
                </button>
              </td>
              <!-- Nom (calendar public - green bg, inline editable) -->
              <td class="px-2 py-2 text-sm bg-green-50/30">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Nom'">
                  <input
                    :id="`inline-${g.id}-Nom`"
                    v-model="editingValue"
                    type="text"
                    maxlength="80"
                    class="w-full px-1 py-0.5 text-sm border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    class="font-medium text-gray-900"
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
                    @click="startInlineEdit(g, 'Nom')"
                  >{{ g.nom || '-' }}</span>
                </template>
              </td>
              <!-- Dates (calendar public) -->
              <td class="px-2 py-2 text-sm text-gray-700 bg-green-50/30 whitespace-nowrap">
                {{ formatDateRange(g.dateDebut, g.dateFin) }}
              </td>
              <!-- Lieu (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-green-50/30">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Lieu'">
                  <input
                    :id="`inline-${g.id}-Lieu`"
                    v-model="editingValue"
                    type="text"
                    maxlength="40"
                    class="w-full px-1 py-0.5 text-sm border border-blue-400 rounded"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
                    @click="startInlineEdit(g, 'Lieu')"
                  >{{ g.lieu || '-' }}</span>
                </template>
              </td>
              <!-- Departement (calendar public, inline editable) -->
              <td class="px-2 py-2 text-sm bg-green-50/30">
                <template v-if="editingCell?.id === g.id && editingCell.field === 'Departement'">
                  <input
                    :id="`inline-${g.id}-Departement`"
                    v-model="editingValue"
                    type="text"
                    maxlength="3"
                    class="w-12 px-1 py-0.5 text-sm border border-blue-400 rounded uppercase"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <template v-else>
                  <span
                    :class="canEdit ? 'cursor-pointer hover:bg-yellow-50 px-1 rounded' : ''"
                    @click="startInlineEdit(g, 'Departement')"
                  >{{ g.departement || '-' }}</span>
                </template>
              </td>
              <!-- Match count -->
              <td class="px-2 py-2 text-sm text-center">
                <NuxtLink
                  v-if="g.matchCount > 0"
                  :to="`/games?journee=${g.id}`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  {{ g.matchCount }}
                </NuxtLink>
                <span v-else class="text-gray-400">0</span>
              </td>
              <!-- Officials -->
              <td class="px-2 py-2 text-xs text-gray-600 max-w-48 truncate" :title="getOfficialsSummary(g)">
                {{ getOfficialsSummary(g) }}
              </td>
              <!-- Delete -->
              <td v-if="canEdit" class="px-2 py-2" @click.stop>
                <button
                  class="p-1 text-red-500 hover:text-red-700"
                  :title="t('common.delete')"
                  @click="openDeleteConfirm(g)"
                >
                  <UIcon name="heroicons:trash" class="w-4 h-4" />
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
            <div class="font-bold text-gray-900">{{ g.codeCompetition }} - {{ g.phase || '?' }}</div>
            <div class="text-sm text-gray-500">#{{ g.id }}</div>
          </div>
        </template>
        <template #header-right>
          <AdminToggleButton
            :active="g.publication"
            active-icon="heroicons:eye-solid"
            inactive-icon="heroicons:eye-slash"
            active-color="green"
            @toggle="canEdit && togglePublication(g)"
          />
        </template>

        <div class="space-y-1 text-sm">
          <div v-if="g.nom">
            <span class="text-green-700 font-medium">{{ g.nom }}</span>
          </div>
          <div v-if="g.dateDebut" class="flex items-center gap-1">
            <UIcon name="heroicons:calendar" class="w-4 h-4 text-gray-400" />
            <span>{{ formatDateRange(g.dateDebut, g.dateFin) }}</span>
          </div>
          <div v-if="g.lieu" class="flex items-center gap-1">
            <UIcon name="heroicons:map-pin" class="w-4 h-4 text-gray-400" />
            <span>{{ g.lieu }} <span v-if="g.departement">({{ g.departement }})</span></span>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-500">
            <span>{{ g.type === 'C' ? t('gamedays.field.type_c') : t('gamedays.field.type_e') }}</span>
            <span v-if="g.matchCount > 0">{{ g.matchCount }} {{ t('gamedays.field.matches').toLowerCase() }}</span>
          </div>
          <div v-if="g.responsableInsc" class="text-xs text-gray-500">
            RC: {{ g.responsableInsc }}
          </div>
        </div>

        <template #footer-right>
          <AdminActionButton v-if="canEdit" icon="heroicons:pencil" @click="openEditModal(g)">
            {{ t('common.edit') }}
          </AdminActionButton>
          <AdminActionButton v-if="canEdit" icon="heroicons:document-duplicate" @click="openDuplicateConfirm(g)">
            {{ t('gamedays.duplicated') }}
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
      <form @submit.prevent="submitForm" class="space-y-4">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
          <UIcon name="heroicons:exclamation-triangle" class="w-4 h-4 inline mr-1" />
          {{ formError }}
        </div>

        <!-- Season + Competition -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.season') }} *</label>
            <input
              v-model="formData.codeSaison"
              type="text"
              :readonly="authStore.profile > 2"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              :class="authStore.profile > 2 ? 'bg-gray-100' : ''"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.competition') }} *</label>
            <AdminCompetitionGroupedSelect
              v-model="formData.codeCompetition"
              :disabled="authStore.profile > 2 && !!editingGameday"
            />
          </div>
        </div>

        <!-- Phase + Type -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.phase') }} *</label>
            <input
              v-model="formData.phase"
              type="text"
              maxlength="30"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.type') }}</label>
            <div class="flex items-center gap-4 mt-2">
              <label class="flex items-center gap-2">
                <input v-model="formData.type" type="radio" value="C" class="text-blue-600">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.niveau') }}</label>
            <select v-model.number="formData.niveau" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option v-for="n in 29" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.etape') }}</label>
            <select v-model.number="formData.etape" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option v-for="n in 19" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.nb_equipes') }}</label>
            <select v-model.number="formData.nbEquipes" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option v-for="n in 19" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.date_debut') }}</label>
            <input v-model="formData.dateDebut" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.date_fin') }}</label>
            <input v-model="formData.dateFin" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>

        <!-- Calendar public fields (highlighted) -->
        <div class="p-4 bg-green-50 rounded-lg border border-green-200 space-y-4">
          <h3 class="text-sm font-semibold text-green-800">{{ t('gamedays.calendar_public') }}</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.nom') }}</label>
            <input v-model="formData.nom" type="text" maxlength="80" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.lieu') }}</label>
              <input v-model="formData.lieu" type="text" maxlength="40" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.departement') }}</label>
              <input v-model="formData.departement" type="text" maxlength="3" class="w-24 px-3 py-2 border border-gray-300 rounded-lg uppercase">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.plan_eau') }}</label>
            <input v-model="formData.planEau" type="text" maxlength="80" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>

        <!-- Organisateur -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.organisateur') }}</label>
          <input v-model="formData.organisateur" type="text" maxlength="40" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <!-- Officials (collapsible) -->
        <details class="border border-gray-200 rounded-lg">
          <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ t('gamedays.field.officiels') }}
          </summary>
          <div class="p-4 border-t border-gray-200 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.responsable_insc') }}</label>
                <input v-model="formData.responsableInsc" type="text" maxlength="80" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.responsable_r1') }}</label>
                <input v-model="formData.responsableR1" type="text" maxlength="80" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.delegue') }}</label>
                <input v-model="formData.delegue" type="text" maxlength="80" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.chef_arbitre') }}</label>
                <input v-model="formData.chefArbitre" type="text" maxlength="80" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('gamedays.field.rep_athletes') }}</label>
                <input v-model="formData.repAthletes" type="text" maxlength="80" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
            </div>
            <div class="border-t border-gray-100 pt-3">
              <label class="block text-xs font-medium text-gray-500 mb-2">{{ t('gamedays.field.arb_nj') }}</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <input v-model="formData.arbNj1" type="text" maxlength="80" placeholder="1" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <input v-model="formData.arbNj2" type="text" maxlength="80" placeholder="2" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <input v-model="formData.arbNj3" type="text" maxlength="80" placeholder="3" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <input v-model="formData.arbNj4" type="text" maxlength="80" placeholder="4" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <input v-model="formData.arbNj5" type="text" maxlength="80" placeholder="5" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
              </div>
            </div>
          </div>
        </details>

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

    <!-- ═══════ DUPLICATE CONFIRM ═══════ -->
    <AdminModal
      :open="duplicateConfirmOpen"
      :title="t('gamedays.duplicate_confirm', { id: duplicateGameday?.id })"
      max-width="sm"
      @close="duplicateConfirmOpen = false"
    >
      <div class="space-y-4">
        <label class="flex items-center gap-2">
          <input v-model="duplicateIncludeMatches" type="checkbox" class="rounded border-gray-300 text-blue-600">
          <span class="text-sm">{{ t('gamedays.include_matches') }}</span>
        </label>
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="duplicateConfirmOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
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
      <form @submit.prevent="submitBulkCalendar" class="space-y-4">
        <p class="text-sm text-gray-600">
          {{ t('gamedays.bulk_calendar_hint', { count: selectedIds.length }) }}
        </p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.nom') }}</label>
          <input v-model="bulkCalendarData.nom" type="text" maxlength="80" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.date_debut') }}</label>
            <input v-model="bulkCalendarData.dateDebut" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.date_fin') }}</label>
            <input v-model="bulkCalendarData.dateFin" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.lieu') }}</label>
            <input v-model="bulkCalendarData.lieu" type="text" maxlength="40" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gamedays.field.departement') }}</label>
            <input v-model="bulkCalendarData.departement" type="text" maxlength="3" class="w-24 px-3 py-2 border border-gray-300 rounded-lg uppercase">
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="bulkCalendarModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
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
        <p class="text-sm text-gray-600">
          {{ t('gamedays.event_association_hint') }}
        </p>
        <div v-if="eventAssociationLoading" class="text-center py-4">
          <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto" />
        </div>
        <div v-else class="max-h-96 overflow-y-auto border rounded-lg divide-y">
          <label
            v-for="g in gamedays"
            :key="g.id"
            class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 cursor-pointer"
          >
            <input
              type="checkbox"
              class="rounded border-gray-300 text-purple-600"
              :checked="eventAssociations.has(g.id)"
              @change="toggleEventAssociation(g.id)"
            >
            <div class="text-sm">
              <span class="font-medium">#{{ g.id }}</span>
              <span class="text-gray-600 ml-1">{{ g.codeCompetition }} - {{ g.phase }}</span>
              <span v-if="g.nom" class="text-gray-500 ml-1">| {{ g.nom }}</span>
            </div>
          </label>
        </div>
      </div>
    </AdminModal>

    <!-- Scroll to top -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
