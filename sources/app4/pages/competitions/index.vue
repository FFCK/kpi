<script setup lang="ts">
import type {
  AdminCompetition,
  CompetitionFormData,
  CompetitionGroup,
  CompetitionLevel,
  CompetitionStatus,
  CompetitionSectionForMulti,
  CompetitionImportMode,
  CompetitionSearchResult
} from '~/types/competitions'
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

interface CompetitionsBySection {
  section: number
  sectionLabel: string
  competitions: AdminCompetition[]
}

const { t } = useI18n()
const api = useApi()
const competitionsApi = useCompetitionsApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()

// State
const loading = ref(false)
const competitions = ref<AdminCompetition[]>([])
const search = ref('')

// Accordion state: collapsed sections (all expanded by default)
const collapsedSections = ref<Set<number>>(new Set())

// Filters - only groups for multi type (level/type removed - use context)
const groups = ref<CompetitionGroup[]>([])
const competitionsForMulti = ref<CompetitionSectionForMulti[]>([])

// Selection state
const selectedCodes = ref<string[]>([])
const selectAll = ref(false)

// Modal state
const isModalOpen = ref(false)
const isDeleting = ref(false)
const editingCompetition = ref<AdminCompetition | null>(null)
const formData = ref<CompetitionFormData>(getDefaultFormData())
const formError = ref('')

// Import mode state (for creating from previous season)
const importMode = ref<CompetitionImportMode>({
  mode: 'import',
  selectedCompetition: null
})
const isCodeEditable = ref(false) // For profiles 1-2 when importing
const importedFromSeason = ref<string | null>(null)
const autocompleteRef = ref<{ focusInput: () => void } | null>(null)

// Delete confirmation modal
const deleteModalOpen = ref(false)
const competitionToDelete = ref<AdminCompetition | null>(null)
const bulkDeleteModalOpen = ref(false)

// Toast notifications
const toast = useToast()

// Default form data
function getDefaultFormData(): CompetitionFormData {
  return {
    code: '',
    codeNiveau: 'NAT',
    libelle: '',
    soustitre: '',
    soustitre2: '',
    codeRef: 'AUTRES',
    groupOrder: null,
    codeTypeclt: 'CHPT',
    codeTour: 1,
    qualifies: 3,
    elimines: 0,
    points: '4-2-1-0',
    goalaverage: 'gen',
    statut: 'ATT',
    web: '',
    enActif: true,
    titreActif: true,
    bandeauActif: true,
    logoActif: true,
    sponsorActif: true,
    kpiFfckActif: true,
    pointsGrid: null,
    multiCompetitions: [],
    rankingStructureType: 'team',
    commentairesCompet: ''
  }
}

// Load groups
const loadGroups = async () => {
  try {
    const response = await api.get<CompetitionGroup[]>('/admin/competitions-groups')
    groups.value = response
  } catch (error) {
    console.error('Error loading groups:', error)
  }
}

// Load competitions for MULTI select
const loadCompetitionsForMulti = async () => {
  if (!workContext.season) return
  try {
    const response = await api.get<CompetitionSectionForMulti[]>('/admin/competitions-for-multi', {
      season: workContext.season
    })
    competitionsForMulti.value = response
  } catch (error) {
    console.error('Error loading competitions for multi:', error)
  }
}

// Accordion helpers
const toggleSection = (sectionId: number) => {
  const newSet = new Set(collapsedSections.value)
  if (newSet.has(sectionId)) {
    newSet.delete(sectionId)
  } else {
    newSet.add(sectionId)
  }
  collapsedSections.value = newSet
}

const isSectionCollapsed = (sectionId: number) => collapsedSections.value.has(sectionId)

const expandAll = () => {
  collapsedSections.value = new Set()
}

const collapseAll = () => {
  collapsedSections.value = new Set(competitionsBySection.value.map(s => s.section))
}

// Competitions grouped by section (with search filtering)
const competitionsBySection = computed<CompetitionsBySection[]>(() => {
  const filtered = competitions.value.filter(c => {
    if (search.value) {
      const s = search.value.toLowerCase()
      return c.code.toLowerCase().includes(s) ||
             c.libelle.toLowerCase().includes(s) ||
             (c.soustitre && c.soustitre.toLowerCase().includes(s)) ||
             (c.codeRef && c.codeRef.toLowerCase().includes(s))
    }
    return true
  })

  const bySection = new Map<number, CompetitionsBySection>()
  for (const c of filtered) {
    if (!bySection.has(c.section)) {
      bySection.set(c.section, {
        section: c.section,
        sectionLabel: t(`groups.sections.${c.section}`),
        competitions: []
      })
    }
    bySection.get(c.section)!.competitions.push(c)
  }

  return Array.from(bySection.values()).sort((a, b) => a.section - b.section)
})

const totalCompetitions = computed(() => competitions.value.length)

// Load competitions
const loadCompetitions = async () => {
  // Wait for context to be initialized
  if (!workContext.initialized || !workContext.season) return

  loading.value = true
  try {
    const params: Record<string, string | number> = {
      season: workContext.season,
      limit: 500,
      sortBy: 'section',
      sortOrder: 'ASC'
    }
    // Filter by competitions from context if available
    if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.codes = workContext.competitionCodes.join(',')
    }

    const response = await api.get<{ items: AdminCompetition[], total: number }>('/admin/competitions', params)
    competitions.value = response.items

    // Clear selection
    selectedCodes.value = []
    selectAll.value = false
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('competitions.error_load')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Watch for context changes
watch(
  () => [workContext.initialized, workContext.season, workContext.competitionCodes],
  () => {
    if (workContext.initialized) {
      loadCompetitions()
      loadCompetitionsForMulti()
    }
  },
  { deep: true }
)

// Debounced search (client-side filtering, no reload needed)
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(search, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Clear selection when search changes
    selectedCodes.value = []
    selectAll.value = false
  }, 300)
})

// Load on mount
onMounted(async () => {
  // Initialize work context if not already done
  await workContext.initContext()
  await loadGroups()
  await loadCompetitions()
  await loadCompetitionsForMulti()
})

// Selection handlers
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedCodes.value = competitions.value.map(c => c.code)
  } else {
    selectedCodes.value = []
  }
}

const isSelected = (code: string) => selectedCodes.value.includes(code)

const toggleSelect = (code: string) => {
  const index = selectedCodes.value.indexOf(code)
  if (index > -1) {
    selectedCodes.value.splice(index, 1)
  } else {
    selectedCodes.value.push(code)
  }
  selectAll.value = selectedCodes.value.length === competitions.value.length
}

// Permission checks
const canEdit = computed(() => authStore.profile <= 3)
const canDelete = computed(() => authStore.profile <= 2)
const canTogglePublish = computed(() => authStore.profile <= 4)
const canToggleLock = computed(() => authStore.profile <= 3)

// Modal handlers
const openAddModal = () => {
  editingCompetition.value = null
  formData.value = getDefaultFormData()
  formError.value = ''
  importMode.value = { mode: 'import', selectedCompetition: null }
  isCodeEditable.value = false
  importedFromSeason.value = null
  isModalOpen.value = true

  // Focus autocomplete input after modal opens
  nextTick(() => {
    autocompleteRef.value?.focusInput()
  })
}

// Handle competition selection from autocomplete
const onCompetitionSelected = async (competition: CompetitionSearchResult) => {
  loading.value = true
  try {
    // Fetch complete competition data from previous season
    const fullCompetition = await competitionsApi.getCompetitionFromPreviousSeason(
      competition.code,
      competition.latestSeasonCode
    )

    // Pre-fill all form fields
    formData.value = {
      code: fullCompetition.code,
      codeNiveau: fullCompetition.niveau,
      codeTypeclt: fullCompetition.type,
      libelle: fullCompetition.libelle,
      soustitre: fullCompetition.soustitre || '',
      soustitre2: fullCompetition.soustitre2 || '',
      codeRef: fullCompetition.groupe || 'AUTRES',
      groupOrder: fullCompetition.groupOrder,
      codeTour: fullCompetition.tour,
      statut: 'ATT', // Always set to ATT (pending) for new competitions
      qualifies: fullCompetition.qualifies,
      elimines: fullCompetition.elimines,
      points: fullCompetition.points,
      goalaverage: fullCompetition.goalaverage,
      web: fullCompetition.lienWeb || '',
      enActif: fullCompetition.enActif,
      titreActif: fullCompetition.titreActif,
      bandeauActif: fullCompetition.bandeauActif,
      logoActif: fullCompetition.logoActif,
      sponsorActif: fullCompetition.sponsorActif,
      kpiFfckActif: fullCompetition.kpiFfckActif,
      pointsGrid: fullCompetition.pointsGrid,
      multiCompetitions: fullCompetition.multiCompetitions || [],
      rankingStructureType: fullCompetition.rankingStructureType || 'team',
      commentairesCompet: fullCompetition.commentaires || ''
    }

    // Store imported season for display
    importedFromSeason.value = fullCompetition.importedFromSeason

    // Code is initially not editable (only for profiles 1-2 with explicit action)
    isCodeEditable.value = false

    toast.add({
      title: t('common.success'),
      description: t('competitions.success_imported', { season: competition.latestSeasonCode }),
      color: 'success',
      duration: 3000
    })
  } catch (error) {
    console.error('Error importing competition:', error)
    formError.value = (error as { message?: string })?.message || t('competitions.error_import')
  } finally {
    loading.value = false
  }
}

// Toggle code editability (for profiles 1-2 only)
const toggleCodeEdit = () => {
  isCodeEditable.value = !isCodeEditable.value
}

// Check if code is editable
const canEditCode = computed(() => {
  // If no imported competition, always editable (manual input)
  if (!importedFromSeason.value) return true

  // If imported and user toggled edit (profile 1-2 only), allow it
  return isCodeEditable.value
})

// Check if user can change code (profile <= 2)
const canChangeImportedCode = computed(() => authStore.profile <= 2)

const openEditModal = (competition: AdminCompetition) => {
  editingCompetition.value = competition
  formData.value = {
    code: competition.code,
    codeNiveau: competition.codeNiveau,
    libelle: competition.libelle,
    soustitre: competition.soustitre || '',
    soustitre2: competition.soustitre2 || '',
    codeRef: competition.codeRef || 'AUTRES',
    groupOrder: competition.groupOrder,
    codeTypeclt: competition.codeTypeclt,
    codeTour: competition.codeTour,
    qualifies: competition.qualifies,
    elimines: competition.elimines,
    points: competition.points,
    goalaverage: competition.goalaverage,
    statut: competition.statut,
    web: competition.web || '',
    enActif: competition.enActif,
    titreActif: competition.titreActif,
    bandeauActif: competition.bandeauActif,
    logoActif: competition.logoActif,
    sponsorActif: competition.sponsorActif,
    kpiFfckActif: competition.kpiFfckActif,
    pointsGrid: competition.pointsGrid,
    multiCompetitions: competition.multiCompetitions || [],
    rankingStructureType: competition.rankingStructureType || 'team',
    commentairesCompet: competition.commentairesCompet || ''
  }
  formError.value = ''
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingCompetition.value = null
  formError.value = ''
}

// Save competition (create or update)
const saveCompetition = async () => {
  formError.value = ''

  // Validate
  if (!formData.value.code.trim()) {
    formError.value = 'Le code est obligatoire'
    return
  }

  if (formData.value.code.length > 12) {
    formError.value = 'Le code doit faire 12 caractères maximum'
    return
  }

  if (!formData.value.libelle.trim()) {
    formError.value = 'Le libellé est obligatoire'
    return
  }

  if (formData.value.libelle.length > 80) {
    formError.value = 'Le libellé doit faire 80 caractères maximum'
    return
  }

  loading.value = true
  try {
    if (editingCompetition.value) {
      // Update
      await api.put(`/admin/competitions/${editingCompetition.value.code}?season=${workContext.season}`, formData.value)
      toast.add({
        title: t('common.success'),
        description: t('competitions.success_updated'),
        color: 'success',
        duration: 3000
      })
    } else {
      // Create
      const createdCode = formData.value.code
      await api.post('/admin/competitions', { ...formData.value, season: workContext.season })

      // Reload work context to include new competition
      await workContext.loadSeasonData(api)

      // If in selection mode, add the new competition to selection
      if (workContext.selectionType === 'selection' && !workContext.selectedCompetitionCodes.includes(createdCode)) {
        workContext.selectedCompetitionCodes.push(createdCode)
        workContext.computeCompetitionCodes()
        workContext.saveToStorage()
      }

      toast.add({
        title: t('common.success'),
        description: t('competitions.success_created'),
        color: 'success',
        duration: 3000
      })
    }
    closeModal()
    loadCompetitions()
  } catch (error: unknown) {
    formError.value = (error as { message?: string })?.message || t('competitions.error_save')
  } finally {
    loading.value = false
  }
}

// Toggle publication
const togglePublication = async (competition: AdminCompetition) => {
  try {
    const response = await api.patch<{ publication: boolean }>(`/admin/competitions/${competition.code}/publish?season=${workContext.season}`)
    competition.publication = response.publication
    toast.add({
      title: t('common.success'),
      description: t('competitions.success_published'),
      color: 'success',
      duration: 2000
    })
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || 'Erreur',
      color: 'error',
      duration: 3000
    })
  }
}

// Toggle lock
const toggleLock = async (competition: AdminCompetition) => {
  try {
    const response = await api.patch<{ verrou: boolean }>(`/admin/competitions/${competition.code}/lock?season=${workContext.season}`)
    competition.verrou = response.verrou
    toast.add({
      title: t('common.success'),
      description: t('competitions.success_locked'),
      color: 'success',
      duration: 2000
    })
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || 'Erreur',
      color: 'error',
      duration: 3000
    })
  }
}

// Cycle status: ATT -> ON -> END -> ATT
const cycleStatus = async (competition: AdminCompetition) => {
  if (!canToggleLock.value) return

  const statusMap: Record<CompetitionStatus, CompetitionStatus> = {
    'ATT': 'ON',
    'ON': 'END',
    'END': 'ATT'
  }
  const nextStatus = statusMap[competition.statut]

  try {
    await api.patch(`/admin/competitions/${competition.code}/status?season=${workContext.season}`, { statut: nextStatus })
    competition.statut = nextStatus
    toast.add({
      title: t('common.success'),
      description: t('competitions.success_status'),
      color: 'success',
      duration: 2000
    })
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || 'Erreur',
      color: 'error',
      duration: 3000
    })
  }
}

// Delete handlers
const openDeleteModal = (competition: AdminCompetition) => {
  competitionToDelete.value = competition
  deleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!competitionToDelete.value) return

  isDeleting.value = true
  try {
    await api.del(`/admin/competitions/${competitionToDelete.value.code}?season=${workContext.season}`)
    toast.add({
      title: t('common.success'),
      description: t('competitions.success_deleted'),
      color: 'success',
      duration: 3000
    })
    deleteModalOpen.value = false
    competitionToDelete.value = null
    loadCompetitions()
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('competitions.error_delete')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'error',
      duration: 3000
    })
  } finally {
    isDeleting.value = false
  }
}

// Bulk delete
const openBulkDeleteModal = () => {
  if (selectedCodes.value.length === 0) return
  bulkDeleteModalOpen.value = true
}

const confirmBulkDelete = async () => {
  if (selectedCodes.value.length === 0) return

  isDeleting.value = true
  try {
    await api.post('/admin/competitions/bulk-delete', { codes: selectedCodes.value, season: workContext.season })
    toast.add({
      title: t('common.success'),
      description: `${selectedCodes.value.length} compétition(s) supprimée(s)`,
      color: 'success',
      duration: 3000
    })
    bulkDeleteModalOpen.value = false
    selectedCodes.value = []
    selectAll.value = false
    loadCompetitions()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('competitions.error_delete'),
      color: 'error',
      duration: 3000
    })
  } finally {
    isDeleting.value = false
  }
}

// Status badge color
const getStatusColor = (status: CompetitionStatus) => {
  // Colors matching legacy GestionStyle.css (.statutCompetATT, .statutCompetON, .statutCompetEND)
  switch (status) {
    case 'ATT': return 'bg-[#888888] text-[#CCEEDD] italic'
    case 'ON': return 'bg-[#008800] text-[#CCEEDD] italic'
    case 'END': return 'bg-[#334F64] text-[#CCEEDD]'
    default: return 'bg-[#888888] text-[#CCEEDD]'
  }
}

// Level badge color
const getLevelColor = (level: CompetitionLevel) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-blue-100 text-blue-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

// Generic navigation function
const navigateToPage = (target: string, eventGroup: string = '', competition: string = '') => {
  // Reset event/group filter if competition is not in current filter
  const filteredCodes = workContext.pageFilteredCompetitionCodes
  if (competition && filteredCodes && !filteredCodes.includes(competition)) {
    workContext.setPageEventGroupSelection('')
  }
  
  if (competition) {
    workContext.setPageCompetition(competition)
  }
  if (eventGroup) {
    workContext.setPageEventGroupSelection(eventGroup)
  }
  
  navigateTo(`/${target}`)
}

// Convenience wrapper for documents navigation
const navigateToDocuments = (competition: AdminCompetition) => {
  navigateToPage('documents', '', competition.code)
}


// Tour options
const tourOptions = [
  { value: 1, label: t('competitions.tour_options.1') },
  { value: 2, label: t('competitions.tour_options.2') },
  { value: 3, label: t('competitions.tour_options.3') },
  { value: 4, label: t('competitions.tour_options.4') },
  { value: 5, label: t('competitions.tour_options.5') },
  { value: 6, label: t('competitions.tour_options.6') },
  { value: 10, label: t('competitions.tour_options.10') }
]

// Is MULTI type
const isMultiType = computed(() => formData.value.codeTypeclt === 'MULTI')
</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader :title="t('competitions.title')" :show-filters="false" />

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('common.search')"
      :add-label="t('competitions.add')"
      :show-add="canEdit"
      :show-bulk-delete="canDelete"
      :bulk-delete-label="t('competitions.delete_selected')"
      :selected-count="selectedCodes.length"
      @add="openAddModal"
      @bulk-delete="openBulkDeleteModal"
    >
      <template v-if="competitionsBySection.length > 1" #left>
        <button
          class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-default"
          :disabled="collapsedSections.size === competitionsBySection.length"
          @click="collapseAll"
        >
          <UIcon name="heroicons:chevron-double-up" class="w-3.5 h-3.5" />
          {{ t('common.collapse_all') }}
        </button>
        <button
          class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-default"
          :disabled="collapsedSections.size === 0"
          @click="expandAll"
        >
          <UIcon name="heroicons:chevron-double-down" class="w-3.5 h-3.5" />
          {{ t('common.expand_all') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <!-- Loading state -->
      <div v-if="loading && competitions.length === 0" class="px-4 py-8 text-center text-gray-500">
        <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
        {{ t('common.loading') }}
      </div>

      <!-- Empty state -->
      <div v-else-if="competitionsBySection.length === 0" class="px-4 py-8 text-center text-gray-500">
        {{ t('competitions.empty') }}
      </div>

      <!-- Competitions by section -->
      <div v-else>
        <div v-for="section in competitionsBySection" :key="section.section" class="border-b border-gray-200 last:border-b-0">
          <!-- Section header (accordion toggle) -->
          <button
            class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-2 flex items-center gap-2 transition-colors cursor-pointer"
            @click="toggleSection(section.section)"
          >
            <UIcon
              name="heroicons:chevron-right"
              class="w-4 h-4 text-gray-500 transition-transform"
              :class="{ 'rotate-90': !isSectionCollapsed(section.section) }"
            />
            <span class="text-sm font-semibold text-gray-700">{{ section.sectionLabel }}</span>
            <span class="text-xs text-gray-500">({{ section.competitions.length }})</span>
          </button>

          <!-- Table for this section -->
          <div v-show="!isSectionCollapsed(section.section)" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <!-- Checkbox column -->
                  <!-- <th v-if="canDelete" class="px-3 py-2 w-10">
                    <input
                      type="checkbox"
                      class="w-6 h-6 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                      :checked="section.competitions.every(c => isSelected(c.code))"
                      @change="section.competitions.forEach(c => { if (($event.target as HTMLInputElement).checked !== isSelected(c.code)) toggleSelect(c.code) })"
                    >
                  </th> -->
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.publication') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.code') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.edit') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.libelle') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.niveau') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.groupe') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.stage') }}
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.type') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.statut') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.equipes') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.verrou') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.journees') }}
                  </th>
                  <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.matchs') }}
                  </th>
                  <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('competitions.columns.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="competition in section.competitions"
                  :key="competition.code"
                  class="hover:bg-gray-50"
                  :class="{ 'bg-blue-50': isSelected(competition.code) }"
                >
                  <!-- Checkbox -->
                  <!-- <td v-if="canDelete" class="px-3 py-1">
                    <input
                      :checked="isSelected(competition.code)"
                      type="checkbox"
                      class="w-6 h-6 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                      @change="toggleSelect(competition.code)"
                    >
                  </td> -->

                  <!-- Publication toggle -->
                  <td class="px-3 py-1 text-center">
                    <AdminToggleButton
                      v-if="canTogglePublish"
                      :active="competition.publication"
                      active-icon="heroicons:eye-solid"
                      inactive-icon="heroicons:eye-slash-solid"
                      active-color="green"
                      :active-title="t('competitions.published')"
                      :inactive-title="t('competitions.unpublished')"
                      size="lg"
                      @toggle="togglePublication(competition)"
                    />
                    <UIcon
                      v-else
                      :name="competition.publication ? 'heroicons:eye-solid' : 'heroicons:eye-slash-solid'"
                      class="w-5 h-5"
                      :class="competition.publication ? 'text-green-600' : 'text-gray-400'"
                    />
                  </td>

                  <!-- Code with link to documents -->
                  <td class="px-3 py-1 text-sm">
                    <button
                      class="link-value"
                      :title="t('competitions.documents')"
                      @click="navigateToPage('documents', '', competition.code)"
                    >
                      {{ competition.code }}
                    </button>
                  </td>

                  <!-- Edit -->
                  <td class="px-3 py-1 text-sm">
                    <button
                      v-if="canEdit"
                      class="p-1.5 text-blue-600"
                      :title="t('common.edit')"
                      @click="openEditModal(competition)"
                    >
                      <UIcon name="heroicons:pencil-solid" class="w-6 h-6" />
                    </button>
                  </td>

                  <!-- Libelle -->
                  <td class="px-3 py-1 text-sm text-gray-900">
                    <div class="font-medium">{{ competition.libelle }}</div>
                    <div v-if="competition.soustitre" class="text-xs text-gray-500">{{ competition.soustitre }}</div>
                  </td>

                  <!-- Level badge -->
                  <td class="px-3 py-1 text-sm">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded"
                      :class="getLevelColor(competition.codeNiveau)"
                    >
                      {{ competition.codeNiveau }}
                    </span>
                  </td>

                  <!-- Group -->
                  <td class="px-3 py-1 text-sm text-gray-500">
                    <button
                      class="link-value"
                      :title="t('competitions.columns.groupe')"
                      @click="navigateToPage('gamedays', `group:${competition.codeRef}`, competition.code)"
                    >
                      {{ competition.codeRef || '-' }}
                    </button>
                  </td>

                  <!-- Tour -->
                  <td class="px-3 py-1 text-sm text-gray-500">
                    {{ competition.codeTour === 10 ? 'F' : competition.codeTour || '-' }}
                  </td>

                  <!-- Type -->
                  <td class="px-3 py-1 text-sm text-gray-500">
                    {{ competition.codeTypeclt }}
                  </td>

                  <!-- Status -->
                  <td class="px-3 py-1 text-center">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded uppercase"
                      :class="[
                        getStatusColor(competition.statut),
                        canToggleLock ? 'cursor-pointer' : ''
                      ]"
                      :title="canToggleLock ? t('competitions.click_to_change_status') : ''"
                      @click="cycleStatus(competition)"
                    >
                      {{ t(`competitions.status.${competition.statut}`) }}
                    </span>
                  </td>

                  <!-- Teams count -->
                  <td class="px-3 py-1 text-sm text-center text-gray-500">
                    <NuxtLink
                        :to="`/teams?competition=${competition.code}`"
                        class="link-value"
                        :title="t('competitions.columns.equipes')"
                      >
                        {{ competition.nbEquipes }}
                      </NuxtLink>
                    
                  </td>

                  <!-- Lock toggle -->
                  <td class="px-3 py-1 text-center">
                    <AdminToggleButton
                      v-if="canToggleLock"
                      :active="competition.verrou"
                      active-icon="heroicons:lock-closed-solid"
                      inactive-icon="heroicons:lock-open-solid"
                      active-color="red"
                      :active-title="t('competitions.locked')"
                      :inactive-title="t('competitions.unlocked')"
                      size="lg"
                      @toggle="toggleLock(competition)"
                    />
                    <UIcon
                      v-else
                      :name="competition.verrou ? 'heroicons:lock-closed-solid' : 'heroicons:lock-open-solid'"
                      class="w-5 h-5"
                      :class="competition.verrou ? 'text-red-600' : 'text-gray-400'"
                    />
                  </td>

                  <!-- Journées/Phases count -->
                  <td class="px-3 py-1 text-sm text-center text-gray-500">
                    <button
                      class="link-value"
                      :title="t('competitions.columns.journees')"
                      @click="navigateToPage('gamedays', '', competition.code)"
                    >
                      {{ competition.nbJournees }}
                    </button>
                  </td>

                  <!-- Matches count -->
                  <td class="px-3 py-1 text-sm text-center text-gray-500">
                    <button
                      class="link-value"
                      :title="t('competitions.columns.matchs')"
                      @click="navigateToPage('games', '', competition.code)"
                    >
                      {{ competition.nbMatchs }}
                    </button>
                  </td>

                  <!-- Actions -->
                  <td class="px-3 py-1">
                    <div class="flex items-center justify-end gap-1">
                      <NuxtLink
                        :to="`/rc?competition=${competition.code}`"
                        class="p-1.5 text-purple-600 hover:text-purple-800"
                        :title="t('competitions.rc')"
                      >
                        <UIcon name="heroicons:users-solid" class="w-6 h-6" />
                      </NuxtLink>
                      <button
                        v-if="canDelete && competition.nbEquipes === 0 && competition.nbJournees === 0 && competition.nbMatchs === 0"
                        class="p-1.5 text-red-600"
                        :title="t('common.delete')"
                        @click="openDeleteModal(competition)"
                      >
                        <UIcon name="heroicons:trash-solid" class="w-6 h-6" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Total -->
        <div class="px-4 py-1 bg-gray-50 text-sm text-gray-600">
          {{ t('competitions.total_competitions', { count: totalCompetitions }) }}
        </div>
      </div>
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      :loading="loading && competitions.length === 0"
      :empty="competitionsBySection.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('competitions.empty')"
    >
      <template v-for="section in competitionsBySection" :key="section.section">
        <!-- Section header mobile (accordion toggle) -->
        <button
          class="w-full flex items-center gap-2 px-1 py-2 mt-2 first:mt-0 cursor-pointer"
          @click="toggleSection(section.section)"
        >
          <UIcon
            name="heroicons:chevron-right"
            class="w-4 h-4 text-gray-500 transition-transform"
            :class="{ 'rotate-90': !isSectionCollapsed(section.section) }"
          />
          <span class="text-sm font-semibold text-gray-700">{{ section.sectionLabel }}</span>
          <span class="text-xs text-gray-500">({{ section.competitions.length }})</span>
        </button>

        <template v-if="!isSectionCollapsed(section.section)">
          <AdminCard
            v-for="competition in section.competitions"
            :key="competition.code"
            :selected="isSelected(competition.code)"
            :show-checkbox="canDelete"
            :checked="isSelected(competition.code)"
            @toggle-select="toggleSelect(competition.code)"
          >
            <!-- Header -->
            <template #header>
              <div class="flex items-center gap-2">
                <span
                  class="px-2 py-0.5 text-xs font-medium rounded"
                  :class="getLevelColor(competition.codeNiveau)"
                >
                  {{ competition.codeNiveau }}
                </span>
                <button
                  class="font-semibold text-blue-600 hover:underline"
                  @click="navigateToPage(competition)"
                >
                  {{ competition.code }}
                </button>
              </div>
            </template>
            <template #header-right>
              <span
                class="px-2 py-0.5 text-xs font-medium rounded uppercase"
                :class="[
                  getStatusColor(competition.statut),
                  canToggleLock ? 'cursor-pointer' : ''
                ]"
                :title="canToggleLock ? t('competitions.click_to_change_status') : ''"
                @click="cycleStatus(competition)"
              >
                {{ t(`competitions.status.${competition.statut}`) }}
              </span>
            </template>

            <!-- Content -->
            <div class="space-y-2">
              <div class="font-medium text-gray-900">{{ competition.libelle }}</div>
              <div v-if="competition.soustitre" class="text-sm text-gray-500">{{ competition.soustitre }}</div>
              <div class="flex flex-wrap gap-2 text-sm text-gray-500">
                <span>{{ competition.codeTypeclt }}</span>
                <span v-if="competition.codeRef">| {{ competition.codeRef }}</span>
                <span>| {{ competition.nbEquipes }} {{ t('competitions.columns.equipes') }}</span>
                <span>| {{ competition.nbJournees }} {{ competition.codeTypeclt === 'CP' ? t('competitions.columns.phases') : t('competitions.columns.journees') }}</span>
                <span>| {{ competition.nbMatchs }} {{ t('competitions.columns.matchs') }}</span>
              </div>
            </div>

            <!-- Footer left: toggles -->
            <template #footer-left>
              <AdminToggleButton
                v-if="canTogglePublish"
                :active="competition.publication"
                active-icon="heroicons:eye-solid"
                inactive-icon="heroicons:eye-slash-solid"
                active-color="green"
                :active-title="t('competitions.published')"
                :inactive-title="t('competitions.unpublished')"
                @toggle="togglePublication(competition)"
              />
              <AdminToggleButton
                v-if="canToggleLock"
                :active="competition.verrou"
                active-icon="heroicons:lock-closed-solid"
                inactive-icon="heroicons:lock-open-solid"
                active-color="red"
                :active-title="t('competitions.locked')"
                :inactive-title="t('competitions.unlocked')"
                @toggle="toggleLock(competition)"
              />
            </template>

            <!-- Footer right: actions -->
            <template #footer-right>
              <AdminActionButton
                v-if="canEdit"
                icon="heroicons:pencil-solid"
                @click="openEditModal(competition)"
              >
                {{ t('common.edit') }}
              </AdminActionButton>
              <AdminActionButton
                v-if="canDelete && competition.nbEquipes === 0 && competition.nbJournees === 0 && competition.nbMatchs === 0"
                variant="danger"
                icon="heroicons:trash-solid"
                @click="openDeleteModal(competition)"
              >
                {{ t('common.delete') }}
              </AdminActionButton>
            </template>
          </AdminCard>
        </template>
      </template>

      <!-- Total mobile -->
      <div v-if="competitionsBySection.length > 0" class="px-1 py-2 text-sm text-gray-600">
        {{ t('competitions.total_competitions', { count: totalCompetitions }) }}
      </div>
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="isModalOpen"
      max-width="xl"
      @close="closeModal"
    >
      <template #header>
        <div class="flex-1 pr-8">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingCompetition ? t('competitions.form.edit_title') : t('competitions.form.add_title') }}
          </h3>
          <!-- Error message in header -->
          <div
            v-if="formError"
            class="flex items-start gap-2 mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <span class="text-sm">{{ formError }}</span>
          </div>
        </div>
      </template>

      <form @submit.prevent="saveCompetition">
        <div class="space-y-4 max-h-[70vh] overflow-y-auto px-1">
          <!-- Autocomplete (only when creating) -->
          <div v-if="!editingCompetition" class="border-b border-gray-200 pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('competitions.form.search_competition') }}
            </label>
            <AdminCompetitionAutocomplete
              v-if="workContext.season"
              ref="autocompleteRef"
              v-model="importMode.selectedCompetition"
              :current-season-code="workContext.season"
              @selected="onCompetitionSelected"
            />
            <div v-else class="text-sm text-gray-500 italic">
              Chargement...
            </div>
          </div>

          <!-- Code (only for create) -->
          <div v-if="!editingCompetition">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('competitions.form.code') }} <span class="text-red-500">*</span>
            </label>

            <!-- Imported code indicator and edit button -->
            <div v-if="importedFromSeason" class="flex items-center gap-2 mb-2">
              <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium">
                {{ t('competitions.form.imported_from') }} {{ importedFromSeason }}
              </span>
              <button
                v-if="canChangeImportedCode"
                type="button"
                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                @click="toggleCodeEdit"
              >
                {{ isCodeEditable ? '🔓 ' : '🔒 ' }}{{ t('competitions.form.edit_code') }}
              </button>
            </div>

            <input
              v-model="formData.code"
              type="text"
              :placeholder="t('competitions.form.code_placeholder')"
              :disabled="!canEditCode"
              maxlength="12"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase disabled:bg-gray-100 disabled:cursor-not-allowed"
            >
            <p class="mt-1 text-xs text-gray-500">{{ t('competitions.form.code_hint') }}</p>
          </div>

          <!-- Code display (for edit) -->
          <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.code') }}</label>
            <div class="px-3 py-2 bg-gray-100 rounded-lg font-mono">{{ formData.code }}</div>
          </div>

          <!-- Row: Niveau + Type -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Niveau -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.niveau') }}</label>
              <select
                v-model="formData.codeNiveau"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="INT">{{ t('competitions.levels.INT') }}</option>
                <option value="NAT">{{ t('competitions.levels.NAT') }}</option>
                <option value="REG">{{ t('competitions.levels.REG') }}</option>
              </select>
            </div>

            <!-- Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.type') }}</label>
              <select
                v-model="formData.codeTypeclt"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="CHPT">{{ t('competitions.types_long.CHPT') }}</option>
                <option value="CP">{{ t('competitions.types_long.CP') }}</option>
                <option value="MULTI">{{ t('competitions.types_long.MULTI') }}</option>
              </select>
            </div>
          </div>

          <!-- Libelle -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('competitions.form.libelle') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.libelle"
              type="text"
              :placeholder="t('competitions.form.libelle_placeholder')"
              maxlength="80"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Soustitre -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.soustitre') }}</label>
            <input
              v-model="formData.soustitre"
              type="text"
              :placeholder="t('competitions.form.soustitre_placeholder')"
              maxlength="80"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Soustitre 2 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.soustitre2') }}</label>
            <input
              v-model="formData.soustitre2"
              type="text"
              :placeholder="t('competitions.form.soustitre2_placeholder')"
              maxlength="80"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Row: Groupe + Order -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Groupe -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.groupe') }}</label>
              <select
                v-model="formData.codeRef"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">{{ t('competitions.form.groupe_placeholder') }}</option>
                <option v-for="group in groups" :key="group.id" :value="group.groupe">
                  {{ group.groupe }} - {{ group.libelle }}
                </option>
              </select>
            </div>

            <!-- Group Order -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.group_order') }}</label>
              <input
                v-model.number="formData.groupOrder"
                type="number"
                min="0"
                max="99"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
          </div>

          <!-- Row: Tour + Statut -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Tour -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.tour') }}</label>
              <select
                v-model="formData.codeTour"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="opt in tourOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- Statut -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.statut') }}</label>
              <select
                v-model="formData.statut"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="ATT">{{ t('competitions.status.ATT') }}</option>
                <option value="ON">{{ t('competitions.status.ON') }}</option>
                <option value="END">{{ t('competitions.status.END') }}</option>
              </select>
            </div>
          </div>

          <!-- Row: Qualifies + Elimines (only for non-MULTI) -->
          <div v-if="!isMultiType" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.qualifies') }}</label>
              <input
                v-model.number="formData.qualifies"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.elimines') }}</label>
              <input
                v-model.number="formData.elimines"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
          </div>

          <!-- Row: Points + Goal average (only for non-MULTI) -->
          <div v-if="!isMultiType" class="grid grid-cols-2 gap-4">
            <!-- Points -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.points') }}</label>
              <select
                v-model="formData.points"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="4-2-1-0">{{ t('competitions.points_options.4-2-1-0') }}</option>
                <option value="3-1-0-0">{{ t('competitions.points_options.3-1-0-0') }}</option>
              </select>
            </div>

            <!-- Goal average -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.goalaverage') }}</label>
              <select
                v-model="formData.goalaverage"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="gen">{{ t('competitions.goalaverage_options.gen') }}</option>
                <option value="part">{{ t('competitions.goalaverage_options.part') }}</option>
              </select>
            </div>
          </div>

          <!-- MULTI type specific fields -->
          <div v-if="isMultiType" class="border border-blue-200 rounded-lg p-4 bg-blue-50">
            <h3 class="font-medium text-blue-800 mb-3">{{ t('competitions.multi.title') }}</h3>

            <!-- Ranking type -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.multi.ranking_type') }}</label>
              <select
                v-model="formData.rankingStructureType"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
              >
                <option value="team">{{ t('competitions.multi.ranking_types.team') }}</option>
                <option value="club">{{ t('competitions.multi.ranking_types.club') }}</option>
                <option value="cd">{{ t('competitions.multi.ranking_types.cd') }}</option>
                <option value="cr">{{ t('competitions.multi.ranking_types.cr') }}</option>
                <option value="nation">{{ t('competitions.multi.ranking_types.nation') }}</option>
              </select>
            </div>

            <!-- Points grid -->
            <div class="mb-4">
              <AdminPointsGridEditor v-model="formData.pointsGrid" />
            </div>

            <!-- Source competitions -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('competitions.multi.source_competitions') }}
              </label>
              <p class="text-xs text-gray-500 mb-2">{{ t('competitions.multi.source_competitions_hint') }}</p>
              <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-lg bg-white p-2">
                <div v-for="section in competitionsForMulti" :key="section.section" class="mb-2">
                  <div class="text-xs font-medium text-gray-500 uppercase mb-1">{{ section.sectionLabel }}</div>
                  <div v-for="comp in section.competitions" :key="comp.code" class="flex items-center gap-2 py-1">
                    <input
                      :id="`multi-${comp.code}`"
                      v-model="formData.multiCompetitions"
                      type="checkbox"
                      :value="comp.code"
                      class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label :for="`multi-${comp.code}`" class="text-sm text-gray-700 cursor-pointer">
                      {{ comp.code }} - {{ comp.libelle }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Web link -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.web') }}</label>
            <input
              v-model="formData.web"
              type="url"
              :placeholder="t('competitions.form.web_placeholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Display options -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('competitions.form.options') }}</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.enActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.en_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.titreActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.titre_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.bandeauActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.bandeau_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.logoActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.logo_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.sponsorActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.sponsor_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.kpiFfckActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" >
                <span class="text-sm">{{ t('competitions.form.kpi_ffck_actif') }}</span>
              </label>
            </div>
          </div>

          <!-- Comments -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.commentaires') }}</label>
            <textarea
              v-model="formData.commentairesCompet"
              rows="3"
              :placeholder="t('competitions.form.commentaires_placeholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="closeModal"
          >
            {{ t('competitions.form.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('competitions.form.save') }}
            </span>
            <span v-else>{{ t('competitions.form.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Delete confirmation modal -->
    <AdminConfirmModal
      :open="deleteModalOpen"
      :title="t('competitions.delete')"
      :message="t('competitions.confirm_delete')"
      :item-name="competitionToDelete?.libelle"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      @close="deleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <!-- Bulk delete confirmation modal -->
    <AdminConfirmModal
      :open="bulkDeleteModalOpen"
      :title="t('competitions.delete_selected')"
      :message="t('competitions.confirm_delete_multiple', { count: selectedCodes.length })"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      @close="bulkDeleteModalOpen = false"
      @confirm="confirmBulkDelete"
    />

    <!-- Scroll to top button -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
