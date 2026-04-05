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
const selectedIds = ref<number[]>([])

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
const canDelete = computed(() => authStore.profile <= 2)
const canCopy = computed(() => authStore.profile <= 2)

const filteredRc = computed(() => {
  let filtered = rcList.value

  // Search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(rc =>
      rc.nom.toLowerCase().includes(query)
      || rc.prenom.toLowerCase().includes(query)
      || rc.matric.toString().includes(query),
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

    // Competition filter
    if (workContext.pageCompetitionCodeAll) {
      params.competitions = workContext.pageCompetitionCodeAll
    }
    else if (workContext.pageEventGroupType === 'group') {
      const group = workContext.uniqueGroups.find(g => g.code === workContext.pageEventGroupValue)
      if (group) {
        const contextCodes = new Set(workContext.competitionCodes)
        const groupCodes = group.competitions.filter(c => contextCodes.has(c))
        if (groupCodes.length > 0) params.competitions = groupCodes.join(',')
      }
    }
    else if (workContext.pageEventGroupType === 'event') {
      params.event = workContext.pageEventGroupValue
    }
    else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
      params.competitions = workContext.competitionCodes.join(',')
    }

    const data = await api.get<{ items: Rc[]; total: number }>('/admin/rc', params)
    rcList.value = data.items || []
  }
  catch (error) {
    console.error('Error loading RC:', error)
  }
  finally {
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
    }
    else {
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
  }
  catch (error: any) {
    formError.value = error.message || t('common.error')
  }
  finally {
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
  }
  catch (error) {
    console.error('Error deleting RC:', error)
  }
  finally {
    formSaving.value = false
  }
}

// Copy RC
const openCopyModal = () => {
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
      copyFormData.value,
    )

    toast.add({
      title: t('common.success'),
      description: t('rc.copy_success', { copied: data.copied, skipped: data.skipped }),
      color: 'success',
    })

    copyModalOpen.value = false
    await loadRc()
  }
  catch (error) {
    console.error('Error copying RC:', error)
  }
  finally {
    formSaving.value = false
  }
}

// Init
onMounted(async () => {
  await workContext.initContext()

  const competitionParam = route.query.competition as string
  if (competitionParam) {
    workContext.setPageCompetitionAll(competitionParam)
  }
})

// Watch context changes
watch(() => [workContext.initialized, workContext.season], () => {
  if (workContext.initialized && workContext.season) {
    loadRc()
  }
}, { immediate: true })

// Watch competition/event-group changes
watch([() => workContext.pageCompetitionCodeAll, () => workContext.pageEventGroupSelection], () => {
  loadRc()
})
</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader
      :title="t('rc.title')"
      :show-all-option="true"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
    />

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
      <template #after-search>
        <!-- Copy RC button -->
        <button
          v-if="canCopy"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50 transition-colors"
          @click="openCopyModal"
        >
          <UIcon name="heroicons:document-duplicate" class="w-4 h-4" />
          {{ t('rc.copy_button') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- RC Table (Desktop) -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-header-200">
        <thead class="bg-header-50">
          <tr>
            <th v-if="canDelete" class="w-10 px-3 py-3">
              <input
                type="checkbox"
                class="rounded border-header-300"
                :checked="selectedIds.length === filteredRc.length && filteredRc.length > 0"
                @change="selectedIds = selectedIds.length === filteredRc.length ? [] : filteredRc.map(r => r.id)"
              >
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('rc.field.competition') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('rc.field.ordre') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('common.last_name') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('common.first_name') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('rc.field.licence') }}
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">
              {{ t('rc.field.email') }}
            </th>
            <th v-if="canEdit" class="w-16 px-3 py-3" />
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-header-200">
          <tr
            v-for="rc in filteredRc"
            :key="rc.id"
            class="hover:bg-header-50 cursor-pointer"
            @click="canEdit && openEditModal(rc)"
          >
            <td v-if="canDelete" class="px-3 py-4" @click.stop>
              <input
                v-model="selectedIds"
                type="checkbox"
                :value="rc.id"
                class="rounded border-header-300"
              >
            </td>
            <td class="px-3 py-4 text-sm text-header-900">
              {{ rc.competitionLabel }}
            </td>
            <td class="px-3 py-4 text-sm text-header-900">
              {{ rc.ordre }}
            </td>
            <td class="px-3 py-4 text-sm font-medium text-header-900">
              {{ rc.nom }}
            </td>
            <td class="px-3 py-4 text-sm text-header-900">
              {{ rc.prenom }}
            </td>
            <td class="px-3 py-4 text-sm text-header-500 font-mono">
              {{ rc.matric }}
            </td>
            <td class="px-3 py-4 text-sm text-header-500">
              {{ rc.email || '-' }}
            </td>
            <td v-if="canEdit" class="px-3 py-4" @click.stop>
              <UIcon
                name="i-heroicons-pencil"
                class="w-6 h-6 text-primary-600 hover:text-primary-800 cursor-pointer"
                @click="openEditModal(rc)"
              />
            </td>
          </tr>
          <tr v-if="filteredRc.length === 0">
            <td :colspan="canDelete ? 8 : 7" class="px-3 py-8 text-center text-sm text-header-500">
              {{ loading ? t('common.loading') : t('rc.no_results') }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Footer -->
      <div class="px-4 py-3 bg-header-50 border-t border-header-200 text-sm text-header-600">
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
            <div class="text-sm text-header-500">{{ rc.competitionLabel }}</div>
          </div>
        </template>

        <div class="space-y-1 text-sm">
          <div><span class="text-header-500">{{ t('rc.field.ordre') }}:</span> {{ rc.ordre }}</div>
          <div><span class="text-header-500">{{ t('rc.field.licence') }}:</span> {{ rc.matric }}</div>
          <div v-if="rc.email"><span class="text-header-500">{{ t('rc.field.email') }}:</span> {{ rc.email }}</div>
        </div>
      </AdminCard>
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="addModalOpen || editModalOpen"
      :title="editingRc ? t('rc.edit') : t('rc.add_title')"
      max-width="lg"
      @close="addModalOpen = false; editModalOpen = false"
    >
      <form class="space-y-4" @submit.prevent="submitForm">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-800 text-sm">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 inline mr-2" />
          {{ formError }}
        </div>

        <!-- Player search -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
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
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('rc.field.season') }}
          </label>
          <input
            v-model="formData.season"
            type="text"
            readonly
            class="w-full px-3 py-2 border border-header-300 rounded-lg bg-header-100"
          >
        </div>

        <!-- Competition -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('rc.field.competition') }}
          </label>
          <AdminCompetitionGroupedSelect
            v-model="formData.competitionCode"
            :show-national-option="true"
          />
        </div>

        <!-- Ordre -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('rc.field.ordre') }} *
          </label>
          <input
            v-model.number="formData.ordre"
            type="number"
            min="1"
            max="99"
            required
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="addModalOpen = false; editModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700"
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
      <form class="space-y-4" @submit.prevent="copyRc">
        <!-- Error -->
        <div v-if="formError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-800 text-sm">
          {{ formError }}
        </div>

        <!-- Source season -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('rc.copy_source') }}
          </label>
          <select
            v-model="copyFormData.sourceCode"
            required
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">{{ t('common.select') }}</option>
            <option v-for="season in availableSeasons" :key="season" :value="season">
              {{ season }}
            </option>
          </select>
        </div>

        <!-- Target season -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('rc.copy_target') }}
          </label>
          <select
            v-model="copyFormData.targetCode"
            required
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
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
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="copyModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700"
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
