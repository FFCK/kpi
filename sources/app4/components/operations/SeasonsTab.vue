<script setup lang="ts">
import type { OperationsSeason } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// Internal tab navigation
const activeSubTab = ref<'list' | 'add'>('list')

// State
const loading = ref(false)
const seasons = ref<OperationsSeason[]>([])

// Form state - Add season
const newSeasonCode = ref('')
const newSeasonNatDebut = ref('')
const newSeasonNatFin = ref('')
const newSeasonInterDebut = ref('')
const newSeasonInterFin = ref('')

// Modal state - Activate
const confirmActivateModal = ref(false)
const seasonToActivate = ref<OperationsSeason | null>(null)

// Modal state - Edit
const editModal = ref(false)
const seasonToEdit = ref<OperationsSeason | null>(null)
const editNatDebut = ref('')
const editNatFin = ref('')
const editInterDebut = ref('')
const editInterFin = ref('')

// Modal state - Delete
const confirmDeleteModal = ref(false)
const seasonToDelete = ref<OperationsSeason | null>(null)

// Load seasons
const loadSeasons = async () => {
  loading.value = true
  try {
    seasons.value = await api.get<OperationsSeason[]>('/admin/operations/seasons')
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_add'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Add season
const addSeason = async () => {
  if (!newSeasonCode.value) return

  loading.value = true
  try {
    await api.post('/admin/operations/seasons', {
      code: newSeasonCode.value,
      natDebut: newSeasonNatDebut.value || null,
      natFin: newSeasonNatFin.value || null,
      interDebut: newSeasonInterDebut.value || null,
      interFin: newSeasonInterFin.value || null
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_add'),
      color: 'success',
      duration: 3000
    })
    newSeasonCode.value = ''
    newSeasonNatDebut.value = ''
    newSeasonNatFin.value = ''
    newSeasonInterDebut.value = ''
    newSeasonInterFin.value = ''
    loadSeasons()
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_add'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Edit season
const openEditModal = (season: OperationsSeason) => {
  seasonToEdit.value = season
  editNatDebut.value = season.natDebut ? season.natDebut.substring(0, 10) : ''
  editNatFin.value = season.natFin ? season.natFin.substring(0, 10) : ''
  editInterDebut.value = season.interDebut ? season.interDebut.substring(0, 10) : ''
  editInterFin.value = season.interFin ? season.interFin.substring(0, 10) : ''
  editModal.value = true
}

const confirmEdit = async () => {
  if (!seasonToEdit.value) return

  loading.value = true
  try {
    await api.put(`/admin/operations/seasons/${seasonToEdit.value.code}`, {
      natDebut: editNatDebut.value || null,
      natFin: editNatFin.value || null,
      interDebut: editInterDebut.value || null,
      interFin: editInterFin.value || null
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_edit'),
      color: 'success',
      duration: 3000
    })
    editModal.value = false
    seasonToEdit.value = null
    loadSeasons()
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_edit'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Activate season
const openActivateModal = (season: OperationsSeason) => {
  seasonToActivate.value = season
  confirmActivateModal.value = true
}

const confirmActivate = async () => {
  if (!seasonToActivate.value) return

  loading.value = true
  try {
    await api.patch(`/admin/operations/seasons/${seasonToActivate.value.code}/activate`)
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_activate'),
      color: 'success',
      duration: 3000
    })
    confirmActivateModal.value = false
    seasonToActivate.value = null
    loadSeasons()
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_activate'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Delete season
const openDeleteModal = (season: OperationsSeason) => {
  seasonToDelete.value = season
  confirmDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!seasonToDelete.value) return

  loading.value = true
  try {
    await api.del(`/admin/operations/seasons/${seasonToDelete.value.code}`)
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_delete'),
      color: 'success',
      duration: 3000
    })
    confirmDeleteModal.value = false
    seasonToDelete.value = null
    loadSeasons()
  } catch (err: unknown) {
    const message = (err as { data?: { message?: string } })?.data?.message
    toast.add({
      title: t('common.error'),
      description: message || t('operations.seasons.error_delete'),
      color: 'error',
      duration: 5000
    })
  } finally {
    loading.value = false
  }
}

// Format date
const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

// Load on mount
onMounted(() => {
  loadSeasons()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Internal sub-tab navigation -->
    <div class="border-b border-header-200">
      <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Season tabs">
        <button
          v-for="tab in [
            { id: 'list', label: t('operations.seasons.list'), icon: 'i-heroicons-list-bullet' },
            { id: 'add', label: t('operations.seasons.add'), icon: 'i-heroicons-plus-circle' },
          ]"
          :key="tab.id"
          :class="[
            activeSubTab === tab.id
              ? 'border-primary-500 text-primary-600 bg-primary-50'
              : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300',
            'whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors rounded-t'
          ]"
          @click="activeSubTab = tab.id as 'list' | 'add'"
        >
          <UIcon :name="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Season list -->
    <section v-if="activeSubTab === 'list'">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-header-200">
          <thead class="bg-header-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.code') }}
              </th>
              <th class="px-4 py-3 text-center text-xs font-medium text-header-500 uppercase">
                {{ t('common.edit') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.status') }}
              </th>
              <th class="px-4 py-3 text-center text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.activate') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.nat_start') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.nat_end') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.inter_start') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-header-500 uppercase">
                {{ t('operations.seasons.inter_end') }}
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-header-500 uppercase">
                {{ t('common.actions') }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-header-200">
            <tr v-if="loading && seasons.length === 0">
              <td colspan="9" class="px-4 py-8 text-center text-header-500">
                <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>
            <tr v-for="season in seasons" :key="season.code" :class="{ 'bg-success-200': season.active }">
              <td class="px-4 py-3 text-sm font-medium text-header-900">
                {{ season.code }}
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  :title="t('common.edit')"
                  class="p-1 text-header-500 hover:text-header-900 rounded"
                  @click="openEditModal(season)"
                >
                  <UIcon name="i-heroicons-pencil-square" class="w-5 h-5" />
                </button>
              </td>
              <td class="px-4 py-3 text-sm">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    season.active ? 'bg-success-100 text-success-800' : 'bg-header-100 text-header-600'
                  ]"
                >
                  {{ season.active ? t('operations.seasons.active') : t('operations.seasons.inactive') }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  v-if="!season.active"
                  :title="t('operations.seasons.activate')"
                  class="p-1 text-primary-500 hover:text-primary-700 rounded"
                  @click="openActivateModal(season)"
                >
                  <UIcon name="i-heroicons-check-circle" class="w-5 h-5" />
                </button>
              </td>
              <td class="px-4 py-3 text-sm text-header-500">{{ formatDate(season.natDebut) }}</td>
              <td class="px-4 py-3 text-sm text-header-500">{{ formatDate(season.natFin) }}</td>
              <td class="px-4 py-3 text-sm text-header-500">{{ formatDate(season.interDebut) }}</td>
              <td class="px-4 py-3 text-sm text-header-500">{{ formatDate(season.interFin) }}</td>
              <td class="px-4 py-3 text-right">
                <button
                  v-if="!season.active"
                  :title="t('common.delete')"
                  class="p-1 text-error-500 hover:text-error-700 rounded"
                  @click="openDeleteModal(season)"
                >
                  <UIcon name="i-heroicons-trash" class="w-5 h-5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Add season -->
    <section v-if="activeSubTab === 'add'">

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.code') }} *
          </label>
          <input
            v-model="newSeasonCode"
            type="text"
            placeholder="2025"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.nat_start') }}
          </label>
          <input
            v-model="newSeasonNatDebut"
            type="date"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.nat_end') }}
          </label>
          <input
            v-model="newSeasonNatFin"
            type="date"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.inter_start') }}
          </label>
          <input
            v-model="newSeasonInterDebut"
            type="date"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.inter_end') }}
          </label>
          <input
            v-model="newSeasonInterFin"
            type="date"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div class="flex items-end">
          <button
            :disabled="!newSeasonCode || loading"
            class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="addSeason"
          >
            {{ t('operations.seasons.add_button') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Edit season modal -->
    <UModal v-model:open="editModal">
      <template #content>
        <div class="p-6 space-y-4">
          <h3 class="text-lg font-semibold text-header-900">
            {{ t('operations.seasons.edit') }} — {{ seasonToEdit?.code }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.seasons.nat_start') }}
              </label>
              <input
                v-model="editNatDebut"
                type="date"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.seasons.nat_end') }}
              </label>
              <input
                v-model="editNatFin"
                type="date"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.seasons.inter_start') }}
              </label>
              <input
                v-model="editInterDebut"
                type="date"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.seasons.inter_end') }}
              </label>
              <input
                v-model="editInterFin"
                type="date"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button
              class="px-4 py-2 text-sm border border-header-300 rounded-lg hover:bg-header-50"
              @click="editModal = false"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              :disabled="loading"
              class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
              @click="confirmEdit"
            >
              {{ t('common.save') }}
            </button>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Confirm activate modal -->
    <AdminConfirmModal
      :open="confirmActivateModal"
      :title="t('operations.seasons.confirm_activate')"
      :message="t('operations.seasons.confirm_activate_message')"
      :item-name="seasonToActivate?.code"
      :confirm-text="t('operations.seasons.activate_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmActivateModal = false"
      @confirm="confirmActivate"
    />

    <!-- Confirm delete modal -->
    <AdminConfirmModal
      :open="confirmDeleteModal"
      :title="t('operations.seasons.confirm_delete')"
      :message="t('operations.seasons.confirm_delete_message')"
      :item-name="seasonToDelete?.code"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      variant="danger"
      @close="confirmDeleteModal = false"
      @confirm="confirmDelete"
    />
  </div>
</template>
