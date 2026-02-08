<script setup lang="ts">
import type {
  AdminCompetition,
  CompetitionFormData,
  CompetitionGroup,
  CompetitionLevel,
  CompetitionStatus,
  CompetitionSectionForMulti
} from '~/types/competitions'
import type { PaginatedResponse } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()

// State
const loading = ref(false)
const competitions = ref<AdminCompetition[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(50)
const totalPages = ref(0)
const search = ref('')
const sortBy = ref('section')
const sortOrder = ref<'ASC' | 'DESC'>('ASC')

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

// Load competitions
const loadCompetitions = async () => {
  // Wait for context to be initialized
  if (!workContext.initialized || !workContext.season) return

  loading.value = true
  try {
    const params: Record<string, string | number | string[]> = {
      season: workContext.season,
      page: page.value,
      limit: limit.value,
      sortBy: sortBy.value,
      sortOrder: sortOrder.value
    }
    if (search.value) {
      params.search = search.value
    }
    // Filter by competitions from context if available
    if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.codes = workContext.competitionCodes.join(',')
    }

    const response = await api.get<PaginatedResponse<AdminCompetition> & { season: string }>('/admin/competitions', params)
    competitions.value = response.items
    total.value = response.total
    totalPages.value = response.totalPages

    // Clear selection on page change
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

// Watch for changes that require reload
watch([page, limit, sortBy, sortOrder], () => {
  loadCompetitions()
})

// Watch for context changes
watch(
  () => [workContext.initialized, workContext.season, workContext.competitionCodes],
  () => {
    if (workContext.initialized) {
      page.value = 1
      loadCompetitions()
      loadCompetitionsForMulti()
    }
  },
  { deep: true }
)

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(search, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadCompetitions()
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
  isModalOpen.value = true
}

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
      await api.post('/admin/competitions', { ...formData.value, season: workContext.season })
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

// Sorting
const handleSort = (column: string) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'ASC' ? 'DESC' : 'ASC'
  } else {
    sortBy.value = column
    sortOrder.value = 'ASC'
  }
}

const getSortIcon = (column: string) => {
  if (sortBy.value !== column) return 'heroicons:arrows-up-down'
  return sortOrder.value === 'ASC' ? 'heroicons:arrow-up' : 'heroicons:arrow-down'
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

// Legacy links
const getDocumentsUrl = (competition: AdminCompetition) => {
  return `/admin/GestionDocuments.php?competition=${competition.code}`
}

const getRcUrl = (competition: AdminCompetition) => {
  return `/admin/GestionRC.php?competition=${competition.code}`
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
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('competitions.title') }}
      </h1>
    </div>

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
    />

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Checkbox column -->
              <th v-if="canDelete" class="px-3 py-3 w-10">
                <input
                  v-model="selectAll"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  @change="toggleSelectAll"
                />
              </th>

              <!-- Code -->
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Code')">
                <div class="flex items-center gap-1">
                  {{ t('competitions.columns.code') }}
                  <UIcon :name="getSortIcon('Code')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Level -->
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Code_niveau')">
                <div class="flex items-center gap-1">
                  {{ t('competitions.columns.niveau') }}
                  <UIcon :name="getSortIcon('Code_niveau')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Libelle -->
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Libelle')">
                <div class="flex items-center gap-1">
                  {{ t('competitions.columns.libelle') }}
                  <UIcon :name="getSortIcon('Libelle')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Groupe -->
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.groupe') }}
              </th>

              <!-- Type -->
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Code_typeclt')">
                <div class="flex items-center gap-1">
                  {{ t('competitions.columns.type') }}
                  <UIcon :name="getSortIcon('Code_typeclt')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Status -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Statut')">
                <div class="flex items-center justify-center gap-1">
                  {{ t('competitions.columns.statut') }}
                  <UIcon :name="getSortIcon('Statut')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Publication -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.publication') }}
              </th>

              <!-- Verrou -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.verrou') }}
              </th>

              <!-- Teams -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.equipes') }}
              </th>

              <!-- Journées/Phases -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.journees') }}
              </th>

              <!-- Matches -->
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.matchs') }}
              </th>

              <!-- Actions -->
              <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('competitions.columns.actions') }}
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading state -->
            <tr v-if="loading && competitions.length === 0">
              <td :colspan="canDelete ? 13 : 12" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-else-if="competitions.length === 0">
              <td :colspan="canDelete ? 12 : 11" class="px-4 py-8 text-center text-gray-500">
                {{ t('competitions.empty') }}
              </td>
            </tr>

            <!-- Competition rows -->
            <tr
              v-for="competition in competitions"
              :key="competition.code"
              class="hover:bg-gray-50"
              :class="{ 'bg-blue-50': isSelected(competition.code) }"
            >
              <!-- Checkbox -->
              <td v-if="canDelete" class="px-3 py-3">
                <input
                  :checked="isSelected(competition.code)"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  @change="toggleSelect(competition.code)"
                />
              </td>

              <!-- Code with link to documents -->
              <td class="px-3 py-3 text-sm">
                <a
                  :href="getDocumentsUrl(competition)"
                  class="text-blue-600 hover:text-blue-800 hover:underline font-medium"
                  :title="t('competitions.documents')"
                >
                  {{ competition.code }}
                </a>
              </td>

              <!-- Level badge -->
              <td class="px-3 py-3 text-sm">
                <span
                  class="px-2 py-1 text-xs font-medium rounded"
                  :class="getLevelColor(competition.codeNiveau)"
                >
                  {{ competition.codeNiveau }}
                </span>
              </td>

              <!-- Libelle -->
              <td class="px-3 py-3 text-sm text-gray-900">
                <div class="font-medium">{{ competition.libelle }}</div>
                <div v-if="competition.soustitre" class="text-xs text-gray-500">{{ competition.soustitre }}</div>
              </td>

              <!-- Groupe -->
              <td class="px-3 py-3 text-sm text-gray-500">
                {{ competition.codeRef || '-' }}
              </td>

              <!-- Type -->
              <td class="px-3 py-3 text-sm text-gray-500">
                {{ competition.codeTypeclt }}
              </td>

              <!-- Status (clickable to cycle ATT -> ON -> END -> ATT) -->
              <td class="px-3 py-3 text-center">
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

              <!-- Publication toggle -->
              <td class="px-3 py-3 text-center">
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

              <!-- Lock toggle -->
              <td class="px-3 py-3 text-center">
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

              <!-- Teams count -->
              <td class="px-3 py-3 text-sm text-center text-gray-500">
                {{ competition.nbEquipes }}
              </td>

              <!-- Journées/Phases count -->
              <td class="px-3 py-3 text-sm text-center text-gray-500">
                {{ competition.nbJournees }}
              </td>

              <!-- Matches count -->
              <td class="px-3 py-3 text-sm text-center text-gray-500">
                {{ competition.nbMatchs }}
              </td>

              <!-- Actions -->
              <td class="px-3 py-3">
                <div class="flex items-center justify-end gap-1">
                  <!-- RC link -->
                  <a
                    v-if="competition.hasRc"
                    :href="getRcUrl(competition)"
                    class="p-1.5 text-purple-600"
                    :title="t('competitions.rc')"
                  >
                    <UIcon name="heroicons:users-solid" class="w-6 h-6" />
                  </a>
                  <!-- Edit -->
                  <button
                    v-if="canEdit"
                    class="p-1.5 text-blue-600"
                    :title="t('common.edit')"
                    @click="openEditModal(competition)"
                  >
                    <UIcon name="heroicons:pencil-solid" class="w-6 h-6" />
                  </button>
                  <!-- Delete -->
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

      <!-- Desktop Pagination -->
      <AdminPagination
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('competitions.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
        :items-per-page-text="t('competitions.pagination.items_per_page')"
      />
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      :loading="loading && competitions.length === 0"
      :empty="competitions.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('competitions.empty')"
    >
      <AdminCard
        v-for="competition in competitions"
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
            <a
              :href="getDocumentsUrl(competition)"
              class="font-semibold text-blue-600 hover:underline"
            >
              {{ competition.code }}
            </a>
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

      <!-- Mobile Pagination -->
      <AdminPagination
        v-if="competitions.length > 0"
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('competitions.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
        :items-per-page-text="t('competitions.pagination.items_per_page')"
        class="mt-4 rounded-lg shadow"
      />
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="isModalOpen"
      :title="editingCompetition ? t('competitions.form.edit_title') : t('competitions.form.add_title')"
      max-width="xl"
      @close="closeModal"
    >
      <form @submit.prevent="saveCompetition">
        <div class="space-y-4 max-h-[70vh] overflow-y-auto px-1">
          <!-- Error message -->
          <div
            v-if="formError"
            class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <span class="text-sm">{{ formError }}</span>
          </div>

          <!-- Code (only for create) -->
          <div v-if="!editingCompetition">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('competitions.form.code') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.code"
              type="text"
              :placeholder="t('competitions.form.code_placeholder')"
              maxlength="12"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
            />
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
            />
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
            />
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
            />
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
              />
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
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('competitions.form.elimines') }}</label>
              <input
                v-model.number="formData.elimines"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
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
                    />
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
            />
          </div>

          <!-- Display options -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('competitions.form.options') }}</label>
            <div class="grid grid-cols-2 gap-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.enActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm">{{ t('competitions.form.en_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.titreActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm">{{ t('competitions.form.titre_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.bandeauActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm">{{ t('competitions.form.bandeau_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.logoActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm">{{ t('competitions.form.logo_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.sponsorActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm">{{ t('competitions.form.sponsor_actif') }}</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="formData.kpiFfckActif" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
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
