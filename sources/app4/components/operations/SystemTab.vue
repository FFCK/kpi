<script setup lang="ts">
import type { CachePurgeResult } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)
const confirmPurgeModal = ref(false)

// Purge cache
const openPurgeModal = () => {
  confirmPurgeModal.value = true
}

const confirmPurge = async () => {
  loading.value = true
  try {
    const result = await api.post<CachePurgeResult>('/admin/operations/cache/purge')
    toast.add({
      title: t('common.success'),
      description: t('operations.system.success_purge', {
        matchFiles: result.matchFilesDeleted,
        eventFiles: result.eventFilesDeleted
      }),
      color: 'success',
      duration: 5000
    })
    confirmPurgeModal.value = false
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.system.error_purge'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-8">
    <!-- Cache management -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.system.cache') }}
      </h2>

      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-white rounded-lg shadow-sm">
            <UIcon name="i-heroicons-trash" class="w-6 h-6 text-gray-600" />
          </div>
          <div class="flex-1">
            <h3 class="font-medium text-gray-900">{{ t('operations.system.purge_cache') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ t('operations.system.purge_description') }}</p>
            <button
              :disabled="loading"
              class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              @click="openPurgeModal"
            >
              <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('operations.system.purge_button') }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Confirm purge modal -->
    <AdminConfirmModal
      :open="confirmPurgeModal"
      :title="t('operations.system.confirm_purge')"
      :message="t('operations.system.purge_description')"
      :confirm-text="t('operations.system.purge_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      danger
      @close="confirmPurgeModal = false"
      @confirm="confirmPurge"
    />
  </div>
</template>
