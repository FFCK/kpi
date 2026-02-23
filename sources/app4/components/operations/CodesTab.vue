<script setup lang="ts">
const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)
const sourceCode = ref('')
const targetCode = ref('')
const allSeasons = ref(false)
const targetExists = ref(false)

// Modal state
const confirmModal = ref(false)

// Change code
const openConfirmModal = () => {
  if (!sourceCode.value.trim() || !targetCode.value.trim()) return
  if (sourceCode.value.trim() === targetCode.value.trim()) {
    toast.add({
      title: t('common.error'),
      description: 'Les codes source et cible doivent être différents',
      color: 'error',
      duration: 3000
    })
    return
  }
  confirmModal.value = true
}

const confirmChange = async () => {
  loading.value = true
  try {
    await api.post('/admin/operations/codes/change', {
      sourceCode: sourceCode.value.trim(),
      targetCode: targetCode.value.trim().toUpperCase(),
      allSeasons: allSeasons.value,
      targetExists: targetExists.value
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.codes.success'),
      color: 'success',
      duration: 3000
    })
    confirmModal.value = false
    sourceCode.value = ''
    targetCode.value = ''
    allSeasons.value = false
    targetExists.value = false
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.codes.error'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-lg font-semibold text-gray-900">
      {{ t('operations.codes.title') }}
    </h2>

    <div class="max-w-xl space-y-4">
      <!-- Source code -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('operations.codes.source_code') }}
        </label>
        <input
          v-model="sourceCode"
          type="text"
          placeholder="ex: N1H"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
        >
      </div>

      <!-- Target code -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('operations.codes.target_code') }}
        </label>
        <input
          v-model="targetCode"
          type="text"
          placeholder="ex: N1M"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
        >
      </div>

      <!-- Options -->
      <div class="space-y-3 pt-2">
        <label class="flex items-center gap-3 cursor-pointer">
          <input
            v-model="allSeasons"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          >
          <span class="text-sm text-gray-700">{{ t('operations.codes.all_seasons') }}</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
          <input
            v-model="targetExists"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          >
          <span class="text-sm text-gray-700">{{ t('operations.codes.target_exists') }}</span>
        </label>
      </div>

      <!-- Info box -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
          <div class="text-sm text-blue-800">
            <p v-if="allSeasons">Modifiera le code dans <strong>toutes les saisons</strong>.</p>
            <p v-else>Modifiera le code uniquement pour la <strong>saison active</strong>.</p>
          </div>
        </div>
      </div>

      <!-- Submit button -->
      <button
        :disabled="!sourceCode.trim() || !targetCode.trim() || loading"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        @click="openConfirmModal"
      >
        <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        {{ t('operations.codes.change_button') }}
      </button>
    </div>

    <!-- Confirm modal -->
    <AdminConfirmModal
      :open="confirmModal"
      :title="t('operations.codes.confirm_change')"
      :message="t('operations.codes.confirm_change_message')"
      :item-name="`${sourceCode} => ${targetCode.toUpperCase()} (${allSeasons ? 'toutes saisons' : 'saison active'})`"
      :confirm-text="t('operations.codes.change_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmModal = false"
      @confirm="confirmChange"
    />
  </div>
</template>
