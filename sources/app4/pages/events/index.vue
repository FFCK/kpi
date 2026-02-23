<script setup lang="ts">
import type { Event, EventFormData, PaginatedResponse } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t, locale } = useI18n()
const api = useApi()
const authStore = useAuthStore()

// State
const loading = ref(false)
const events = ref<Event[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(20)
const totalPages = ref(0)
const search = ref('')
const sortBy = ref('Date_debut')
const sortOrder = ref<'ASC' | 'DESC'>('DESC')

// Selection state
const selectedIds = ref<number[]>([])
const selectAll = ref(false)

// Modal state
const isModalOpen = ref(false)
const isDeleting = ref(false)
const editingEvent = ref<Event | null>(null)
const formData = ref<EventFormData>({
  libelle: '',
  lieu: '',
  dateDebut: '',
  dateFin: ''
})
const formError = ref('')

// Delete confirmation modal
const deleteModalOpen = ref(false)
const eventToDelete = ref<Event | null>(null)
const bulkDeleteModalOpen = ref(false)

// Toast notifications
const toast = useToast()

// Load events
const loadEvents = async () => {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      limit: limit.value,
      sortBy: sortBy.value,
      sortOrder: sortOrder.value
    }
    if (search.value) {
      params.search = search.value
    }

    const response = await api.get<PaginatedResponse<Event>>('/admin/events', params)
    events.value = response.items
    total.value = response.total
    totalPages.value = response.totalPages

    // Clear selection on page change
    selectedIds.value = []
    selectAll.value = false
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('events.error_load')
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
  loadEvents()
})

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(search, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadEvents()
  }, 300)
})

// Load on mount
onMounted(() => {
  loadEvents()
})

// Selection handlers
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedIds.value = events.value.map(e => e.id)
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
  selectAll.value = selectedIds.value.length === events.value.length
}

// Modal handlers
const openAddModal = () => {
  editingEvent.value = null
  formData.value = {
    libelle: '',
    lieu: '',
    dateDebut: '',
    dateFin: ''
  }
  formError.value = ''
  isModalOpen.value = true
}

const openEditModal = (event: Event) => {
  editingEvent.value = event
  formData.value = {
    libelle: event.libelle,
    lieu: event.lieu || '',
    dateDebut: event.dateDebut || '',
    dateFin: event.dateFin || ''
  }
  formError.value = ''
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingEvent.value = null
  formError.value = ''
}

// Save event (create or update)
const saveEvent = async () => {
  formError.value = ''

  // Validate
  if (!formData.value.libelle.trim()) {
    formError.value = 'Le libellé est obligatoire'
    return
  }

  if (formData.value.libelle.length > 40) {
    formError.value = 'Le libellé doit faire 40 caractères maximum'
    return
  }

  if (formData.value.lieu.length > 40) {
    formError.value = 'Le lieu doit faire 40 caractères maximum'
    return
  }

  loading.value = true
  try {
    if (editingEvent.value) {
      // Update
      await api.put(`/admin/events/${editingEvent.value.id}`, formData.value)
      toast.add({
        title: t('common.success'),
        description: t('events.success_updated'),
        color: 'success',
        duration: 3000
      })
    } else {
      // Create
      await api.post('/admin/events', formData.value)
      toast.add({
        title: t('common.success'),
        description: t('events.success_created'),
        color: 'success',
        duration: 3000
      })
    }
    closeModal()
    loadEvents()
  } catch (error: unknown) {
    formError.value = (error as { message?: string })?.message || t('events.error_save')
  } finally {
    loading.value = false
  }
}

// Toggle publication
const togglePublication = async (event: Event) => {
  try {
    const response = await api.patch<{ publication: boolean }>(`/admin/events/${event.id}/publish`)
    event.publication = response.publication
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || 'Erreur',
      color: 'error',
      duration: 3000
    })
  }
}

// Toggle app
const toggleApp = async (event: Event) => {
  try {
    const response = await api.patch<{ app: boolean }>(`/admin/events/${event.id}/app`)
    event.app = response.app
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
const openDeleteModal = (event: Event) => {
  eventToDelete.value = event
  deleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!eventToDelete.value) return

  isDeleting.value = true
  try {
    await api.del(`/admin/events/${eventToDelete.value.id}`)
    toast.add({
      title: t('common.success'),
      description: t('events.success_deleted'),
      color: 'success',
      duration: 3000
    })
    deleteModalOpen.value = false
    eventToDelete.value = null
    loadEvents()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('events.error_delete'),
      color: 'error',
      duration: 3000
    })
  } finally {
    isDeleting.value = false
  }
}

// Bulk delete
const openBulkDeleteModal = () => {
  if (selectedIds.value.length === 0) return
  bulkDeleteModalOpen.value = true
}

const confirmBulkDelete = async () => {
  if (selectedIds.value.length === 0) return

  isDeleting.value = true
  try {
    await api.post('/admin/events/bulk-delete', { ids: selectedIds.value })
    toast.add({
      title: t('common.success'),
      description: `${selectedIds.value.length} événement(s) supprimé(s)`,
      color: 'success',
      duration: 3000
    })
    bulkDeleteModalOpen.value = false
    selectedIds.value = []
    selectAll.value = false
    loadEvents()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('events.error_delete'),
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
    sortOrder.value = 'DESC'
  }
}

const getSortIcon = (column: string) => {
  if (sortBy.value !== column) return 'heroicons:arrows-up-down'
  return sortOrder.value === 'ASC' ? 'heroicons:arrow-up' : 'heroicons:arrow-down'
}

// Format date for display based on current locale
const formatDate = (date: string | null) => {
  if (!date) return '-'
  const d = new Date(date)
  if (locale.value === 'fr') {
    // French: DD/MM/YYYY
    return d.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } else {
    // English: YYYY-MM-DD (ISO format)
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
}
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('events.title') }}
      </h1>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('common.search')"
      :add-label="t('events.add')"
      :show-bulk-delete="authStore.isSuperAdmin"
      :bulk-delete-label="t('events.delete_selected')"
      :selected-count="selectedIds.length"
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
              <th v-if="authStore.isSuperAdmin" class="px-4 py-3 w-10">
                <input
                  v-model="selectAll"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  @change="toggleSelectAll"
                >
              </th>

              <!-- ID -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Id')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.id') }}
                  <UIcon :name="getSortIcon('Id')" />
                </div>
              </th>

              <!-- Libelle -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Libelle')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.libelle') }}
                  <UIcon :name="getSortIcon('Libelle')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Lieu -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Lieu')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.lieu') }}
                  <UIcon :name="getSortIcon('Lieu')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Date debut -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Date_debut')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.date_debut') }}
                  <UIcon :name="getSortIcon('Date_debut')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Date fin -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Date_fin')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.date_fin') }}
                  <UIcon :name="getSortIcon('Date_fin')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Publication -->
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Publication')">
                <div class="flex items-center justify-center gap-1">
                  {{ t('events.columns.publication') }}
                  <UIcon :name="getSortIcon('Publication')" class="w-4 h-4" />
                </div>
              </th>

              <!-- App -->
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('app')">
                <div class="flex items-center justify-center gap-1">
                  {{ t('events.columns.app') }}
                  <UIcon :name="getSortIcon('app')" class="w-4 h-4" />
                </div>
              </th>

              <!-- Actions -->
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ t('events.columns.actions') }}
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading state -->
            <tr v-if="loading && events.length === 0">
              <td :colspan="authStore.isSuperAdmin ? 9 : 8" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-else-if="events.length === 0">
              <td :colspan="authStore.isSuperAdmin ? 9 : 8" class="px-4 py-8 text-center text-gray-500">
                {{ t('events.empty') }}
              </td>
            </tr>

            <!-- Event rows -->
            <tr
              v-for="event in events"
              :key="event.id"
              class="hover:bg-gray-50"
              :class="{ 'bg-blue-50': isSelected(event.id) }"
            >
              <!-- Checkbox -->
              <td v-if="authStore.isSuperAdmin" class="px-4 py-3">
                <input
                  :checked="isSelected(event.id)"
                  type="checkbox"
                  class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 cursor-pointer"
                  @change="toggleSelect(event.id)"
                >
              </td>

              <!-- ID -->
              <td class="px-4 py-3 text-sm text-gray-900">
                {{ event.id }}
              </td>

              <!-- Libelle -->
              <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                {{ event.libelle }}
              </td>

              <!-- Lieu -->
              <td class="px-4 py-3 text-sm text-gray-500">
                {{ event.lieu || '-' }}
              </td>

              <!-- Date debut -->
              <td class="px-4 py-3 text-sm text-gray-500">
                {{ formatDate(event.dateDebut) }}
              </td>

              <!-- Date fin -->
              <td class="px-4 py-3 text-sm text-gray-500">
                {{ formatDate(event.dateFin) }}
              </td>

              <!-- Publication toggle -->
              <td class="px-4 py-3 text-center">
                <AdminToggleButton
                  :active="event.publication"
                  active-icon="heroicons:eye-solid"
                  inactive-icon="heroicons:eye-slash"
                  active-color="green"
                  :active-title="t('events.published')"
                  :inactive-title="t('events.unpublished')"
                  size="md"
                  @toggle="togglePublication(event)"
                />
              </td>

              <!-- App toggle -->
              <td class="px-4 py-3 text-center">
                <AdminToggleButton
                  :active="event.app"
                  active-icon="heroicons:device-phone-mobile-solid"
                  inactive-icon="heroicons:device-phone-mobile-solid"
                  active-color="blue"
                  :active-title="t('events.app_enabled')"
                  :inactive-title="t('events.app_disabled')"
                  size="md"
                  @toggle="toggleApp(event)"
                />
              </td>

              <!-- Actions -->
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-1.5 text-blue-600"
                    :title="t('common.edit')"
                    @click="openEditModal(event)"
                  >
                    <UIcon name="heroicons:pencil-solid" class="w-6 h-6" />
                  </button>
                  <button
                    v-if="authStore.isSuperAdmin"
                    class="p-1.5 text-red-600"
                    :title="t('common.delete')"
                    @click="openDeleteModal(event)"
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
        :showing-text="t('events.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
        :items-per-page-text="t('events.pagination.items_per_page')"
      />
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      :loading="loading && events.length === 0"
      :empty="events.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('events.empty')"
    >
      <AdminCard
        v-for="event in events"
        :key="event.id"
        :selected="isSelected(event.id)"
        :show-checkbox="authStore.isSuperAdmin"
        :checked="isSelected(event.id)"
        @toggle-select="toggleSelect(event.id)"
      >
        <!-- Header -->
        <template #header>
          <h3 class="font-semibold text-gray-900 truncate">
            {{ event.libelle }}
          </h3>
        </template>
        <template #header-right>
          <span class="text-sm text-gray-600 flex-shrink-0 ml-2">ID: {{ event.id }}</span>
        </template>

        <!-- Content -->
        <div v-if="event.lieu" class="flex items-start gap-2 text-sm">
          <UIcon name="heroicons:map-pin-solid" class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" />
          <span class="text-gray-700">{{ event.lieu }}</span>
        </div>

        <div class="flex flex-col gap-2 text-sm">
          <div v-if="event.dateDebut" class="flex items-center gap-2">
            <UIcon name="heroicons:calendar-solid" class="w-5 h-5 text-gray-400 flex-shrink-0" />
            <div>
              <span class="text-gray-900 ml-1">{{ formatDate(event.dateDebut) }}</span>
              -
              <span class="text-gray-900 ml-1">{{ formatDate(event.dateFin) }}</span>
            </div>
          </div>
        </div>

        <!-- Footer left: toggles -->
        <template #footer-left>
          <AdminToggleButton
            :active="event.publication"
            active-icon="heroicons:eye-solid"
            inactive-icon="heroicons:x-circle-solid"
            active-color="green"
            :active-title="t('events.published')"
            :inactive-title="t('events.unpublished')"
            @toggle="togglePublication(event)"
          />
          <AdminToggleButton
            :active="event.app"
            active-icon="heroicons:device-phone-mobile-solid"
            inactive-icon="heroicons:device-phone-mobile-solid"
            active-color="blue"
            :active-title="t('events.app_enabled')"
            :inactive-title="t('events.app_disabled')"
            @toggle="toggleApp(event)"
          />
        </template>

        <!-- Footer right: actions -->
        <template #footer-right>
          <AdminActionButton
            icon="heroicons:pencil-solid"
            @click="openEditModal(event)"
          >
            {{ t('common.edit') }}
          </AdminActionButton>
          <AdminActionButton
            v-if="authStore.isSuperAdmin"
            variant="danger"
            icon="heroicons:trash-solid"
            @click="openDeleteModal(event)"
          >
            {{ t('common.delete') }}
          </AdminActionButton>
        </template>
      </AdminCard>

      <!-- Mobile Pagination -->
      <AdminPagination
        v-if="events.length > 0"
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('events.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
        :items-per-page-text="t('events.pagination.items_per_page')"
        class="mt-4 rounded-lg shadow"
      />
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="isModalOpen"
      :title="editingEvent ? t('events.form.edit_title') : t('events.form.add_title')"
      @close="closeModal"
    >
      <form @submit.prevent="saveEvent">
        <div class="space-y-4">
          <!-- Error message -->
          <div
            v-if="formError"
            class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <span class="text-sm">{{ formError }}</span>
          </div>

          <!-- Libelle -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('events.form.libelle') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.libelle"
              type="text"
              :placeholder="t('events.form.libelle_placeholder')"
              maxlength="40"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Lieu -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('events.form.lieu') }}
            </label>
            <input
              v-model="formData.lieu"
              type="text"
              :placeholder="t('events.form.lieu_placeholder')"
              maxlength="40"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Date debut -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('events.form.date_debut') }}
            </label>
            <input
              v-model="formData.dateDebut"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Date fin -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('events.form.date_fin') }}
            </label>
            <input
              v-model="formData.dateFin"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="closeModal"
          >
            {{ t('events.form.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('events.form.save') }}
            </span>
            <span v-else>{{ t('events.form.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Delete confirmation modal -->
    <AdminConfirmModal
      :open="deleteModalOpen"
      :title="t('events.delete')"
      :message="t('events.confirm_delete')"
      :item-name="eventToDelete?.libelle"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      @close="deleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <!-- Bulk delete confirmation modal -->
    <AdminConfirmModal
      :open="bulkDeleteModalOpen"
      :title="t('events.delete_selected')"
      :message="t('events.confirm_delete_multiple', { count: selectedIds.length })"
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
