<script setup lang="ts">
import type { Event, EventFormData, PaginatedResponse } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
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
      color: 'red'
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
        color: 'green'
      })
    } else {
      // Create
      await api.post('/admin/events', formData.value)
      toast.add({
        title: t('common.success'),
        description: t('events.success_created'),
        color: 'green'
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
      color: 'red'
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
      color: 'red'
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
      color: 'green'
    })
    deleteModalOpen.value = false
    eventToDelete.value = null
    loadEvents()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('events.error_delete'),
      color: 'red'
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
      color: 'green'
    })
    bulkDeleteModalOpen.value = false
    selectedIds.value = []
    selectAll.value = false
    loadEvents()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('events.error_delete'),
      color: 'red'
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

// Format date for display
const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

// Pagination info
const paginationFrom = computed(() => ((page.value - 1) * limit.value) + 1)
const paginationTo = computed(() => Math.min(page.value * limit.value, total.value))
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
    <div class="mb-4 flex flex-col sm:flex-row gap-4 justify-between">
      <!-- Left side: bulk actions -->
      <div class="flex items-center gap-2">
        <button
          v-if="authStore.isSuperAdmin && selectedIds.length > 0"
          class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-lg font-medium text-sm hover:bg-red-100 transition-colors"
          @click="openBulkDeleteModal"
        >
          <UIcon name="heroicons:trash" class="w-5 h-5" />
          {{ t('events.delete_selected') }} ({{ selectedIds.length }})
        </button>
      </div>

      <!-- Right side: search and add -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <UIcon
            name="heroicons:magnifying-glass"
            class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
          />
          <input
            v-model="search"
            type="text"
            :placeholder="t('common.search')"
            class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <button
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition-colors"
          @click="openAddModal"
        >
          <UIcon name="heroicons:plus" class="w-5 h-5" />
          {{ t('events.add') }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                />
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
                />
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
                <button
                  :class="[
                    'p-1.5 transition-colors',
                    event.publication
                      ? 'text-green-600 hover:text-green-700'
                      : 'text-gray-300 hover:text-gray-500'
                  ]"
                  :title="event.publication ? t('events.published') : t('events.unpublished')"
                  @click="togglePublication(event)"
                >
                  <UIcon
                    :name="event.publication ? 'heroicons-solid:check-circle' : 'heroicons-solid:x-circle'"
                    class="w-8 h-8"
                  />
                </button>
              </td>

              <!-- App toggle -->
              <td class="px-4 py-3 text-center">
                <button
                  :class="[
                    'p-1.5 transition-colors',
                    event.app
                      ? 'text-blue-600 hover:text-blue-700'
                      : 'text-gray-300 hover:text-gray-500'
                  ]"
                  :title="event.app ? t('events.app_enabled') : t('events.app_disabled')"
                  @click="toggleApp(event)"
                >
                  <UIcon
                    name="heroicons-solid:device-phone-mobile"
                    class="w-8 h-8"
                  />
                </button>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors"
                    :title="t('common.edit')"
                    @click="openEditModal(event)"
                  >
                    <UIcon name="heroicons-solid:pencil" class="w-8 h-8" />
                  </button>
                  <button
                    v-if="authStore.isSuperAdmin"
                    class="p-1.5 text-gray-400 hover:text-red-600 transition-colors"
                    :title="t('common.delete')"
                    @click="openDeleteModal(event)"
                  >
                    <UIcon name="heroicons-solid:trash" class="w-8 h-8" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500">
          <span v-if="total > 0">
            {{ t('events.pagination.showing', { from: paginationFrom, to: paginationTo, total }) }}
          </span>
        </div>

        <div class="flex items-center gap-4">
          <!-- Items per page -->
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">{{ t('events.pagination.items_per_page') }}</span>
            <select
              v-model.number="limit"
              class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
            >
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <!-- Page navigation -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
              :disabled="page <= 1"
              @click="page--"
            >
              <UIcon name="heroicons:chevron-left" class="w-5 h-5" />
            </button>
            <span class="text-sm text-gray-700 px-2 min-w-[4rem] text-center">
              {{ page }} / {{ totalPages || 1 }}
            </span>
            <button
              type="button"
              class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
              :disabled="page >= totalPages"
              @click="page++"
            >
              <UIcon name="heroicons:chevron-right" class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        @click.self="closeModal"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ editingEvent ? t('events.form.edit_title') : t('events.form.add_title') }}
            </h3>
            <button
              type="button"
              class="text-gray-400 hover:text-gray-600 p-1"
              @click="closeModal"
            >
              <UIcon name="heroicons:x-mark" class="w-6 h-6" />
            </button>
          </div>

          <!-- Body -->
          <form @submit.prevent="saveEvent" class="p-6">
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
                />
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
                />
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
                />
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
        </div>
      </div>
    </Teleport>

    <!-- Delete confirmation modal -->
    <Teleport to="body">
      <div
        v-if="deleteModalOpen"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        @click.self="deleteModalOpen = false"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModalOpen = false" />

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
          <!-- Header -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-red-600">
              {{ t('events.delete') }}
            </h3>
          </div>

          <!-- Body -->
          <div class="p-6">
            <p class="text-gray-600">
              {{ t('events.confirm_delete') }}
            </p>
            <p v-if="eventToDelete" class="mt-2 font-medium text-gray-900">
              {{ eventToDelete.libelle }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 p-6 pt-4 border-t border-gray-200 bg-gray-50">
            <button
              type="button"
              class="px-4 py-2 text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 rounded-lg transition-colors"
              @click="deleteModalOpen = false"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              type="button"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
              :disabled="isDeleting"
              @click="confirmDelete"
            >
              <span v-if="isDeleting" class="flex items-center gap-2">
                <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
                {{ t('common.delete') }}
              </span>
              <span v-else>{{ t('common.delete') }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Bulk delete confirmation modal -->
    <Teleport to="body">
      <div
        v-if="bulkDeleteModalOpen"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        @click.self="bulkDeleteModalOpen = false"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="bulkDeleteModalOpen = false" />

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
          <!-- Header -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-red-600">
              {{ t('events.delete_selected') }}
            </h3>
          </div>

          <!-- Body -->
          <div class="p-6">
            <p class="text-gray-600">
              {{ t('events.confirm_delete_multiple', { count: selectedIds.length }) }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 p-6 pt-4 border-t border-gray-200 bg-gray-50">
            <button
              type="button"
              class="px-4 py-2 text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 rounded-lg transition-colors"
              @click="bulkDeleteModalOpen = false"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              type="button"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
              :disabled="isDeleting"
              @click="confirmBulkDelete"
            >
              <span v-if="isDeleting" class="flex items-center gap-2">
                <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
                {{ t('common.delete') }}
              </span>
              <span v-else>{{ t('common.delete') }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
