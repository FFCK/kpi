<script setup lang="ts">
import type { Rc, RcFormData, RcCopyFormData } from '~/types/rc'
import type { PlayerAutocomplete } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t } = useI18n()
const route = useRoute()
const api = useApi()
const toast = useToast()
const workContext = useWorkContextStore()
const authStore = useAuthStore()

// State
const rcList = ref<Rc[]>([])
const loading = ref(false)
const searchQuery = ref('')
const selectedCompetitions = computed({
  get: () => workContext.pageCompetitionCodes,
  set: (val: string[]) => workContext.setPageCompetitions(val),
})
const selectedIds = ref<number[]>([])
const filterOpen = ref(false)

// Modals
const addModalOpen = ref(false)
const editModalOpen = ref(false)
const copyModalOpen = ref(false)
const deleteConfirmOpen = ref(false)

// Form data
const formData = ref<RcFormData>({
  season: '',
  competitionCode: null,
  matric: 0,
  ordre: 1,
})
const editingRc = ref<Rc | null>(null)
const formError = ref('')
const formSaving = ref(false)

// Player search
const selectedPlayer = ref<PlayerAutocomplete | null>(null)

// Copy RC
const copyFormData = ref<RcCopyFormData>({
  sourceCode: '',
  targetCode: '',
})
const availableSeasons = ref<string[]>([])

// Computed
const canEdit = computed(() => authStore.profile <= 2)
const canDelete = computed(() => authStore.profile <= 1)
const canCopy = computed(() => authStore.profile <= 2)

const filteredRc = computed(() => {
  let filtered = rcList.value

  // Filter by selected competitions
  if (selectedCompetitions.value.length > 0) {
    filtered = filtered.filter(rc =>
      selectedCompetitions.value.includes(rc.competitionCode || '')
    )
  }

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(rc =>
      rc.nom.toLowerCase().includes(query) ||
      rc.prenom.toLowerCase().includes(query) ||
      rc.matric.toString().includes(query)
    )
  }

  return filtered
})

// Load RC list
const loadRc = async () => {
  if (!workContext.season) return

  loading.value = true
  try {
    const params: Record<string, string> = {
      season: workContext.season,
    }

    if (selectedCompetitions.value.length > 0) {
      params.competitions = selectedCompetitions.value.join(',')
    }

    const data = await api.get<{ items: Rc[]; total: number }>('/admin/rc', params)
    rcList.value = data.items || []
  } catch (error) {
    console.error('Error loading RC:', error)
  } finally {
    loading.value = false
  }
}

// Handle player selection from autocomplete
const onPlayerSelected = (player: PlayerAutocomplete | null) => {
  selectedPlayer.value = player
  if (player) {
    formData.value.matric = player.matric
  }
}

// Open add modal
const openAddModal = () => {
  formData.value = {
    season: workContext.season || '',
    competitionCode: null,
    matric: 0,
    ordre: 1,
  }
  selectedPlayer.value = null
  formError.value = ''
  addModalOpen.value = true
}

// Open edit modal
const openEditModal = (rc: Rc) => {
  editingRc.value = rc
  formData.value = {
    season: rc.season,
    competitionCode: rc.competitionCode,
    matric: rc.matric,
    ordre: rc.ordre,
  }
  selectedPlayer.value = {
    matric: rc.matric,
    nom: rc.nom,
    prenom: rc.prenom,
    naissance: null,
    numeroClub: rc.club,
    club: null,
    label: `${rc.matric} - ${rc.nom} ${rc.prenom}`,
  }
  formError.value = ''
  editModalOpen.value = true
}

// Submit add/edit form
const submitForm = async () => {
  formError.value = ''

  if (!selectedPlayer.value) {
    formError.value = t('rc.error_no_player')
    return
  }

  formSaving.value = true
  try {
    if (editingRc.value) {
      await api.put(`/admin/rc/${editingRc.value.id}`, formData.value)
    } else {
      await api.post('/admin/rc', formData.value)
    }

    toast.add({
      title: t('common.success'),
      description: editingRc.value ? t('rc.updated') : t('rc.added'),
      color: 'success',
    })

    addModalOpen.value = false
    editModalOpen.value = false
    await loadRc()
  } catch (error: any) {
    formError.value = error.message || t('common.error')
  } finally {
    formSaving.value = false
  }
}

// Confirm delete
const confirmDelete = () => {
  if (selectedIds.value.length === 0) return
  deleteConfirmOpen.value = true
}

// Delete RC
const deleteRc = async () => {
  formSaving.value = true
  try {
    const data = await api.post<{ deleted: number; message: string }>('/admin/rc/bulk-delete', {
      ids: selectedIds.value,
    })

    toast.add({
      title: t('common.success'),
      description: t('rc.deleted', { count: data.deleted }),
      color: 'success',
    })

    selectedIds.value = []
    deleteConfirmOpen.value = false
    await loadRc()
  } catch (error) {
    console.error('Error deleting RC:', error)
  } finally {
    formSaving.value = false
  }
}

// Copy RC
const openCopyModal = () => {
  // Generate list of seasons (current + 2 previous)
  const currentSeason = parseInt(workContext.season || new Date().getFullYear().toString())
  availableSeasons.value = [
    currentSeason.toString(),
    (currentSeason - 1).toString(),
    (currentSeason - 2).toString(),
  ]

  copyFormData.value = {
    sourceCode: '',
    targetCode: workContext.season || '',
  }

  copyModalOpen.value = true
}

const copyRc = async () => {
  if (!copyFormData.value.sourceCode || !copyFormData.value.targetCode) {
    formError.value = t('rc.error_copy_seasons')
    return
  }

  if (copyFormData.value.sourceCode === copyFormData.value.targetCode) {
    formError.value = t('rc.error_copy_same_season')
    return
  }

  formSaving.value = true
  try {
    const data = await api.post<{ copied: number; skipped: number }>(
      '/admin/operations/seasons/copy-rc',
      copyFormData.value
    )

    toast.add({
      title: t('common.success'),
      description: t('rc.copy_success', { copied: data.copied, skipped: data.skipped }),
      color: 'success',
    })

    copyModalOpen.value = false
    await loadRc()
  } catch (error) {
    console.error('Error copying RC:', error)
  } finally {
    formSaving.value = false
  }
}

// Click outside to close filter dropdown
const competitionFilterRef = ref<HTMLElement | null>(null)
const onClickOutsideFilter = (e: MouseEvent) => {
  if (filterOpen.value && competitionFilterRef.value && !competitionFilterRef.value.contains(e.target as Node)) {
    filterOpen.value = false
  }
}

// Initialize from URL parameter
onMounted(() => {
  document.addEventListener('click', onClickOutsideFilter)
  const competitionParam = route.query.competition as string
  if (competitionParam) {
    selectedCompetitions.value = [competitionParam]
  }
})

onUnmounted(() => {
  document.removeEventListener('click', onClickOutsideFilter)
})

// Watch context changes
watch(() => [workContext.initialized, workContext.season], () => {
  if (workContext.initialized && workContext.season) {
    loadRc()
  }
}, { immediate: true })

// Watch competition filter
watch(selectedCompetitions, () => {
  loadRc()
})
</script>

<template>
  <div>
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Title -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('rc.title') }}
      </h1>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('rc.search')"
      :add-label="t('rc.add')"
      :show-add="canEdit"
      :show-bulk-delete="canDelete"
      :bulk-delete-label="t('common.delete_selected')"
      :selected-count="selectedIds.length"
      @add="openAddModal"
      @bulk-delete="confirmDelete"
    >
      <template #before-search>
        <!-- Competition Filter (inline dropdown) -->
        <div ref="competitionFilterRef" class="relative">
          <button
            class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors bg-white"
            @click="filterOpen = !filterOpen"
          >
            <UIcon name="heroicons:funnel" class="w-4 h-4 text-gray-500" />
            <span class="text-gray-700">{{ t('rc.filter_competitions') }}</span>
            <span v-if="selectedCompetitions.length > 0" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {{ selectedCompetitions.length }}
            </span>
            <UIcon
              name="heroicons:chevron-down"
              class="w-4 h-4 text-gray-400 transition-transform"
              :class="{ 'rotate-180': filterOpen }"
            />
          </button>
          <div v-show="filterOpen" class="absolute z-20 mt-1 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-3">
            <AdminCompetitionMultiSelect
              v-model="selectedCompetitions"
              :competitions="workContext.competitions || []"
            />
          </div>
        </div>
      </template>
      <template #after-search>
        <!-- Copy RC button -->
        <button
          v-if="canCopy"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          @click="openCopyModal"
        >
          <UIcon name="heroicons:document-duplicate" class="w-4 h-4" />
          {{ t('rc.copy_button') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- RC Table (Desktop) -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th v-if="canDelete" class="w-10 px-3 py-3">
              <input
                type="checkbox"
                class="rounded border-gray-300"
                :checked="selectedIds.length === filteredRc.length && filteredRc.length > 0"
                @change="selectedIds = selectedIds.length === filteredRc.length ? [] : filteredRc.map(r => r.id)"
              >
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('rc.field.competition') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('rc.field.ordre') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('common.last_name') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('common.first_name') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('rc.field.licence') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              {{ t('rc.field.email') }}
            </th>
            <th v-if="canEdit" class="w-16 px-3 py-3" />
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="rc in filteredRc"
            :key="rc.id"
            class="hover:bg-gray-50 cursor-pointer"
            @click="canEdit && openEditModal(rc)"
          >
            <td v-if="canDelete" class="px-3 py-4" @click.stop>
              <input
                v-model="selectedIds"
                type="checkbox"
                :value="rc.id"
                class="rounded border-gray-300"
              >
            </td>
            <td class="px-3 py-4 text-sm text-gray-900">
              {{ rc.competitionLabel }}
            </td>
            <td class="px-3 py-4 text-sm text-gray-900">
              {{ rc.ordre }}
            </td>
            <td class="px-3 py-4 text-sm font-medium text-gray-900">
              {{ rc.nom }}
            </td>
            <td class="px-3 py-4 text-sm text-gray-900">
              {{ rc.prenom }}
            </td>
            <td class="px-3 py-4 text-sm text-gray-500 font-mono">
              {{ rc.matric }}
            </td>
            <td class="px-3 py-4 text-sm text-gray-500">
              {{ rc.email || '-' }}
            </td>
            <td v-if="canEdit" class="px-3 py-4" @click.stop>
              <UIcon
                name="i-heroicons-pencil"
                class="w-5 h-5 text-blue-600 hover:text-blue-800 cursor-pointer"
                @click="openEditModal(rc)"
              />
            </td>
          </tr>
          <tr v-if="filteredRc.length === 0">
            <td :colspan="canDelete ? 8 : 7" class="px-3 py-8 text-center text-sm text-gray-500">
              {{ loading ? t('common.loading') : t('rc.no_results') }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Footer -->
      <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
        {{ t('rc.total', { count: filteredRc.length }) }}
      </div>
    </div>

    <!-- RC Cards (Mobile) -->
    <AdminCardList class="lg:hidden" :loading="loading" :empty="filteredRc.length === 0">
      <AdminCard
        v-for="rc in filteredRc"
        :key="rc.id"
        :selected="selectedIds.includes(rc.id)"
        :show-checkbox="canDelete"
        @toggle-select="selectedIds.includes(rc.id) ? selectedIds = selectedIds.filter(id => id !== rc.id) : selectedIds.push(rc.id)"
        @click="canEdit && openEditModal(rc)"
      >
        <template #header>
          <div>
            <div class="font-bold">{{ rc.nom }} {{ rc.prenom }}</div>
            <div class="text-sm text-gray-500">{{ rc.competitionLabel }}</div>
          </div>
        </template>

        <div class="space-y-1 text-sm">
          <div><span class="text-gray-500">{{ t('rc.field.ordre') }}:</span> {{ rc.ordre }}</div>
          <div><span class="text-gray-500">{{ t('rc.field.licence') }}:</span> {{ rc.matric }}</div>
          <div v-if="rc.email"><span class="text-gray-500">{{ t('rc.field.email') }}:</span> {{ rc.email }}</div>
        </div>
      </AdminCard>
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="addModalOpen || editModalOpen"
      :title="editingRc ? t('rc.edit') : t('rc.add')"
      max-width="lg"
      @close="addModalOpen = false; editModalOpen = false"
    >
      <form @submit.prevent="submitForm" class="space-y-4">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 inline mr-2" />
          {{ formError }}
        </div>

        <!-- Player search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.field.search_person') }} *
          </label>
          <AdminPlayerAutocomplete
            :model-value="selectedPlayer"
            :placeholder="t('rc.field.search_placeholder')"
            @update:model-value="onPlayerSelected"
          />
        </div>

        <!-- Season (readonly) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.field.season') }}
          </label>
          <input
            v-model="formData.season"
            type="text"
            readonly
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
          >
        </div>

        <!-- Competition -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.field.competition') }}
          </label>
          <AdminCompetitionGroupedSelect
            v-model="formData.competitionCode"
            :show-national-option="true"
          />
        </div>

        <!-- Ordre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.field.ordre') }} *
          </label>
          <input
            v-model.number="formData.ordre"
            type="number"
            min="1"
            max="99"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="addModalOpen = false; editModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            :disabled="!selectedPlayer || formSaving"
          >
            {{ editingRc ? t('common.save') : t('common.add') }}
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Copy RC Modal -->
    <AdminModal
      :open="copyModalOpen"
      :title="t('rc.copy_title')"
      max-width="md"
      @close="copyModalOpen = false"
    >
      <form @submit.prevent="copyRc" class="space-y-4">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
          {{ formError }}
        </div>

        <!-- Source season -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.copy_source') }}
          </label>
          <select
            v-model="copyFormData.sourceCode"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">{{ t('common.select') }}</option>
            <option v-for="season in availableSeasons" :key="season" :value="season">
              {{ season }}
            </option>
          </select>
        </div>

        <!-- Target season -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('rc.copy_target') }}
          </label>
          <select
            v-model="copyFormData.targetCode"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">{{ t('common.select') }}</option>
            <option
              v-for="season in availableSeasons"
              :key="season"
              :value="season"
              :disabled="season === copyFormData.sourceCode"
            >
              {{ season }}
            </option>
          </select>
        </div>

        <!-- Warning -->
        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 inline mr-1" />
          {{ t('rc.copy_help') }}
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="copyModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            :disabled="!copyFormData.sourceCode || !copyFormData.targetCode || formSaving"
          >
            {{ t('common.copy') }}
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Delete Confirm Modal -->
    <AdminConfirmModal
      :open="deleteConfirmOpen"
      :title="t('rc.delete_confirm_title')"
      :message="t('rc.delete_confirm_message', { count: selectedIds.length })"
      :loading="formSaving"
      @confirm="deleteRc"
      @close="deleteConfirmOpen = false"
    />
  </div>
</template>
