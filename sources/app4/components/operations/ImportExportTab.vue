<script setup lang="ts">
import type { OperationsEvent } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)
const pceLoading = ref(false)
const locksLoading = ref(false)
const confirmPceModal = ref(false)
const confirmLocksModal = ref(false)
const events = ref<OperationsEvent[]>([])
const searchEvent = ref('')
const showEventDropdown = ref(false)

// Export state
const selectedExportEvent = ref<OperationsEvent | null>(null)

// Import state
const importEventId = ref<number | null>(null)
const importFile = ref<File | null>(null)
const importFileInput = ref<HTMLInputElement | null>(null)
const confirmImportModal = ref(false)

// Debounce timeout
let searchTimeout: ReturnType<typeof setTimeout> | null = null

// Search events
const searchEvents = async (query: string) => {
  if (query.length < 2) {
    events.value = []
    return
  }

  try {
    const response = await api.get<{ items: OperationsEvent[] }>('/admin/events', {
      search: query,
      limit: 10
    })
    events.value = response.items || []
    showEventDropdown.value = true
  } catch {
    events.value = []
  }
}

// Watch search input with debounce
watch(searchEvent, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => searchEvents(value), 300)
})

// Select event for export
const selectExportEvent = (event: OperationsEvent) => {
  selectedExportEvent.value = event
  searchEvent.value = event.libelle
  showEventDropdown.value = false
}

// Export event
const exportEvent = async () => {
  if (!selectedExportEvent.value) return

  loading.value = true
  try {
    // Use getBlob from API composable for authenticated file download
    const arrayBuffer = await api.getBlob(`/admin/operations/events/${selectedExportEvent.value.id}/export`)
    const blob = new Blob([arrayBuffer], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `kp_evenement_${selectedExportEvent.value.id}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    toast.add({
      title: t('common.success'),
      description: t('operations.import_export.success_export'),
      color: 'success',
      duration: 3000
    })
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.import_export.error_export'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Import file selection
const onImportFileSelected = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    importFile.value = target.files[0]
  }
}

const clearImportFile = () => {
  importFile.value = null
  if (importFileInput.value) {
    importFileInput.value.value = ''
  }
}

// Open import modal
const openImportModal = () => {
  if (!importEventId.value || !importFile.value) return
  confirmImportModal.value = true
}

// Confirm import
const confirmImport = async () => {
  if (!importEventId.value || !importFile.value) return

  loading.value = true
  try {
    const formData = new FormData()
    formData.append('jsonFile', importFile.value)

    await api.upload(`/admin/operations/events/${importEventId.value}/import`, formData)

    toast.add({
      title: t('common.success'),
      description: t('operations.import_export.success_import'),
      color: 'success',
      duration: 3000
    })
    confirmImportModal.value = false
    clearImportFile()
    importEventId.value = null
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.import_export.error_import'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// PCE import
const confirmPceImport = async () => {
  pceLoading.value = true
  try {
    const result = await api.post<{
      nbLicencies: number
      nbArbitres: number
      nbSurclassements: number
      totalTime: number
    }>('/admin/operations/licenses/import-pce')

    toast.add({
      title: t('common.success'),
      description: t('operations.import_export.pce_success', {
        licencies: result.nbLicencies,
        arbitres: result.nbArbitres,
        surclassements: result.nbSurclassements,
        time: result.totalTime
      }),
      color: 'success',
      duration: 8000
    })
    confirmPceModal.value = false
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.import_export.pce_error'),
      color: 'error',
      duration: 5000
    })
  } finally {
    pceLoading.value = false
  }
}

// Competition locks
const confirmLocksUpdate = async () => {
  locksLoading.value = true
  try {
    const result = await api.post<{
      locked: string[]
      unlocked: string[]
    }>('/admin/operations/competitions/update-locks')

    const messages: string[] = []
    if (result.locked.length > 0) {
      messages.push(t('operations.import_export.locks_locked', { list: result.locked.join(', ') }))
    }
    if (result.unlocked.length > 0) {
      messages.push(t('operations.import_export.locks_unlocked', { list: result.unlocked.join(', ') }))
    }
    if (messages.length === 0) {
      messages.push(t('operations.import_export.locks_no_change'))
    }

    toast.add({
      title: t('operations.import_export.locks_success'),
      description: messages.join(' | '),
      color: 'success',
      duration: 5000
    })
    confirmLocksModal.value = false
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.import_export.locks_error'),
      color: 'error',
      duration: 5000
    })
  } finally {
    locksLoading.value = false
  }
}

// Close dropdown on blur
const onBlur = () => {
  setTimeout(() => {
    showEventDropdown.value = false
  }, 200)
}

// Computed
const canExport = computed(() => selectedExportEvent.value !== null)
const canImport = computed(() => importEventId.value !== null && importEventId.value > 0 && importFile.value !== null)
</script>

<template>
  <div class="space-y-8">
    <!-- Export section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-4">
        {{ t('operations.import_export.export_title') }}
      </h2>

      <div class="max-w-xl space-y-4">
        <div class="relative">
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.import_export.select_event') }}
          </label>
          <input
            v-model="searchEvent"
            type="text"
            :placeholder="t('operations.import_export.search_event')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            @focus="showEventDropdown = events.length > 0"
            @blur="onBlur"
          >
          <!-- Dropdown -->
          <div
            v-if="showEventDropdown && events.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-header-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="event in events"
              :key="event.id"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-header-50 text-sm"
              @click="selectExportEvent(event)"
            >
              <div class="font-medium">{{ event.libelle }}</div>
              <div class="text-xs text-header-500">
                ID: {{ event.id }} - {{ event.lieu || 'Sans lieu' }}
              </div>
            </button>
          </div>
          <!-- Selected indicator -->
          <div v-if="selectedExportEvent" class="mt-2 p-2 bg-primary-50 border border-primary-200 rounded-lg">
            <div class="flex items-center gap-2">
              <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-primary-600" />
              <span class="text-sm font-medium">{{ selectedExportEvent.libelle }}</span>
              <span class="text-xs text-header-500">(ID: {{ selectedExportEvent.id }})</span>
            </div>
          </div>
        </div>

        <button
          :disabled="!canExport || loading"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="exportEvent"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          <UIcon v-else name="i-heroicons-arrow-down-tray" class="w-4 h-4" />
          {{ t('operations.import_export.export_button') }}
        </button>
      </div>
    </section>

    <!-- Import section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-4">
        {{ t('operations.import_export.import_title') }}
      </h2>

      <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-4">
        <div class="flex items-start gap-3">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-warning-600 mt-0.5 flex-shrink-0" />
          <p class="text-sm text-warning-800">{{ t('operations.import_export.import_warning') }}</p>
        </div>
      </div>

      <div class="max-w-xl space-y-4">
        <!-- Event ID input -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.import_export.event_id') }}
          </label>
          <input
            v-model.number="importEventId"
            type="number"
            min="1"
            :placeholder="t('operations.import_export.event_id_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
        </div>

        <!-- File input -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.import_export.json_file') }}
          </label>
          <div class="flex items-center gap-3">
            <input
              ref="importFileInput"
              type="file"
              accept="application/json"
              class="flex-1 px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
              @change="onImportFileSelected"
            >
            <button
              v-if="importFile"
              class="px-3 py-2 text-header-600 hover:text-header-900"
              @click="clearImportFile"
            >
              <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
            </button>
          </div>
          <p v-if="importFile" class="mt-1 text-sm text-header-500">
            {{ importFile.name }} ({{ Math.round(importFile.size / 1024) }} Ko)
          </p>
        </div>

        <button
          :disabled="!canImport || loading"
          class="px-4 py-2 bg-danger-600 text-white rounded-lg hover:bg-danger-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="openImportModal"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          <UIcon v-else name="i-heroicons-arrow-up-tray" class="w-4 h-4" />
          {{ t('operations.import_export.import_button') }}
        </button>
      </div>
    </section>

    <!-- PCE Import section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-2">
        {{ t('operations.import_export.pce_title') }}
      </h2>
      <p class="text-sm text-header-600 mb-4">
        {{ t('operations.import_export.pce_description') }}
      </p>

      <button
        :disabled="pceLoading"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        @click="confirmPceModal = true"
      >
        <UIcon v-if="pceLoading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        <UIcon v-else name="i-heroicons-arrow-down-tray" class="w-4 h-4" />
        {{ t('operations.import_export.pce_button') }}
      </button>
    </section>

    <!-- Competition Locks section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-2">
        {{ t('operations.import_export.locks_title') }}
      </h2>
      <p class="text-sm text-header-600 mb-4">
        {{ t('operations.import_export.locks_description') }}
      </p>

      <button
        :disabled="locksLoading"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        @click="confirmLocksModal = true"
      >
        <UIcon v-if="locksLoading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        <UIcon v-else name="i-heroicons-lock-closed" class="w-4 h-4" />
        {{ t('operations.import_export.locks_button') }}
      </button>
    </section>

    <!-- Confirm import modal -->
    <AdminConfirmModal
      :open="confirmImportModal"
      :title="t('operations.import_export.confirm_import')"
      :message="t('operations.import_export.confirm_import_message')"
      :item-name="`${importFile?.name} => Événement ${importEventId}`"
      :confirm-text="t('operations.import_export.import_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmImportModal = false"
      @confirm="confirmImport"
    />

    <!-- Confirm PCE import modal -->
    <AdminConfirmModal
      :open="confirmPceModal"
      :title="t('operations.import_export.pce_confirm')"
      :message="t('operations.import_export.pce_confirm_message')"
      :confirm-text="t('operations.import_export.pce_button')"
      :cancel-text="t('common.cancel')"
      :loading="pceLoading"
      @close="confirmPceModal = false"
      @confirm="confirmPceImport"
    />

    <!-- Confirm locks update modal -->
    <AdminConfirmModal
      :open="confirmLocksModal"
      :title="t('operations.import_export.locks_confirm')"
      :message="t('operations.import_export.locks_confirm_message')"
      :confirm-text="t('operations.import_export.locks_button')"
      :cancel-text="t('common.cancel')"
      :loading="locksLoading"
      @close="confirmLocksModal = false"
      @confirm="confirmLocksUpdate"
    />
  </div>
</template>
