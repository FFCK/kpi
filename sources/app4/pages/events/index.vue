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
  if (sortBy.value !== column) return 'i-heroicons-arrows-up-down'
  return sortOrder.value === 'ASC' ? 'i-heroicons-arrow-up' : 'i-heroicons-arrow-down'
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
        <UButton
          v-if="authStore.isSuperAdmin && selectedIds.length > 0"
          color="red"
          variant="soft"
          icon="i-heroicons-trash"
          @click="openBulkDeleteModal"
        >
          {{ t('events.delete_selected') }} ({{ selectedIds.length }})
        </UButton>
      </div>

      <!-- Right side: search and add -->
      <div class="flex items-center gap-2">
        <UInput
          v-model="search"
          icon="i-heroicons-magnifying-glass"
          :placeholder="t('common.search')"
          class="w-64"
        />
        <UButton
          color="primary"
          icon="i-heroicons-plus"
          @click="openAddModal"
        >
          {{ t('events.add') }}
        </UButton>
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
                <UCheckbox
                  v-model="selectAll"
                  @change="toggleSelectAll"
                />
              </th>

              <!-- ID -->
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="handleSort('Id')">
                <div class="flex items-center gap-1">
                  {{ t('events.columns.id') }}
                  <UIcon :name="getSortIcon('Id')" class="w-4 h-4" />
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
                <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
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
                <UCheckbox
                  :model-value="isSelected(event.id)"
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
                <UButton
                  :icon="event.publication ? 'i-heroicons-check-circle' : 'i-heroicons-x-circle'"
                  :color="event.publication ? 'green' : 'gray'"
                  variant="ghost"
                  size="sm"
                  @click="togglePublication(event)"
                />
              </td>

              <!-- App toggle -->
              <td class="px-4 py-3 text-center">
                <UButton
                  :icon="event.app ? 'i-heroicons-device-phone-mobile' : 'i-heroicons-device-phone-mobile'"
                  :color="event.app ? 'blue' : 'gray'"
                  variant="ghost"
                  size="sm"
                  @click="toggleApp(event)"
                />
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <UButton
                    icon="i-heroicons-pencil"
                    color="gray"
                    variant="ghost"
                    size="sm"
                    @click="openEditModal(event)"
                  />
                  <UButton
                    v-if="authStore.isSuperAdmin"
                    icon="i-heroicons-trash"
                    color="red"
                    variant="ghost"
                    size="sm"
                    @click="openDeleteModal(event)"
                  />
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
            <span class="text-sm text-gray-500">{{ t('events.pagination.items_per_page') }}</span>
            <USelect
              v-model="limit"
              :options="[10, 20, 50, 100]"
              size="sm"
              class="w-20"
            />
          </div>

          <!-- Page navigation -->
          <div class="flex items-center gap-1">
            <UButton
              icon="i-heroicons-chevron-left"
              color="gray"
              variant="ghost"
              size="sm"
              :disabled="page <= 1"
              @click="page--"
            />
            <span class="text-sm text-gray-700 px-2">
              {{ page }} / {{ totalPages || 1 }}
            </span>
            <UButton
              icon="i-heroicons-chevron-right"
              color="gray"
              variant="ghost"
              size="sm"
              :disabled="page >= totalPages"
              @click="page++"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <UModal v-model="isModalOpen">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">
              {{ editingEvent ? t('events.form.edit_title') : t('events.form.add_title') }}
            </h3>
            <UButton
              icon="i-heroicons-x-mark"
              color="gray"
              variant="ghost"
              @click="closeModal"
            />
          </div>
        </template>

        <form @submit.prevent="saveEvent">
          <div class="space-y-4">
            <!-- Error message -->
            <UAlert
              v-if="formError"
              color="red"
              icon="i-heroicons-exclamation-triangle"
              :title="formError"
            />

            <!-- Libelle -->
            <UFormGroup :label="t('events.form.libelle')" required>
              <UInput
                v-model="formData.libelle"
                :placeholder="t('events.form.libelle_placeholder')"
                maxlength="40"
              />
            </UFormGroup>

            <!-- Lieu -->
            <UFormGroup :label="t('events.form.lieu')">
              <UInput
                v-model="formData.lieu"
                :placeholder="t('events.form.lieu_placeholder')"
                maxlength="40"
              />
            </UFormGroup>

            <!-- Date debut -->
            <UFormGroup :label="t('events.form.date_debut')">
              <UInput
                v-model="formData.dateDebut"
                type="date"
              />
            </UFormGroup>

            <!-- Date fin -->
            <UFormGroup :label="t('events.form.date_fin')">
              <UInput
                v-model="formData.dateFin"
                type="date"
              />
            </UFormGroup>
          </div>

          <div class="flex justify-end gap-2 mt-6">
            <UButton
              color="gray"
              variant="ghost"
              @click="closeModal"
            >
              {{ t('events.form.cancel') }}
            </UButton>
            <UButton
              type="submit"
              color="primary"
              :loading="loading"
            >
              {{ t('events.form.save') }}
            </UButton>
          </div>
        </form>
      </UCard>
    </UModal>

    <!-- Delete confirmation modal -->
    <UModal v-model="deleteModalOpen">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-red-600">
            {{ t('events.delete') }}
          </h3>
        </template>

        <p class="text-gray-600">
          {{ t('events.confirm_delete') }}
        </p>
        <p v-if="eventToDelete" class="mt-2 font-medium">
          {{ eventToDelete.libelle }}
        </p>

        <template #footer>
          <div class="flex justify-end gap-2">
            <UButton
              color="gray"
              variant="ghost"
              @click="deleteModalOpen = false"
            >
              {{ t('common.cancel') }}
            </UButton>
            <UButton
              color="red"
              :loading="isDeleting"
              @click="confirmDelete"
            >
              {{ t('common.delete') }}
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>

    <!-- Bulk delete confirmation modal -->
    <UModal v-model="bulkDeleteModalOpen">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-red-600">
            {{ t('events.delete_selected') }}
          </h3>
        </template>

        <p class="text-gray-600">
          {{ t('events.confirm_delete_multiple', { count: selectedIds.length }) }}
        </p>

        <template #footer>
          <div class="flex justify-end gap-2">
            <UButton
              color="gray"
              variant="ghost"
              @click="bulkDeleteModalOpen = false"
            >
              {{ t('common.cancel') }}
            </UButton>
            <UButton
              color="red"
              :loading="isDeleting"
              @click="confirmBulkDelete"
            >
              {{ t('common.delete') }}
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </div>
</template>
