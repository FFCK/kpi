<script setup lang="ts">
import type { PlayerAutocomplete, AutoMergeResult } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)
const searchSource = ref('')
const searchTarget = ref('')
const sourceResults = ref<PlayerAutocomplete[]>([])
const targetResults = ref<PlayerAutocomplete[]>([])
const selectedSource = ref<PlayerAutocomplete | null>(null)
const selectedTarget = ref<PlayerAutocomplete | null>(null)
const showSourceDropdown = ref(false)
const showTargetDropdown = ref(false)

// Modal state
const confirmMergeModal = ref(false)
const confirmAutoMergeModal = ref(false)

// Debounce timeout
let sourceTimeout: ReturnType<typeof setTimeout> | null = null
let targetTimeout: ReturnType<typeof setTimeout> | null = null

// Search players
const searchPlayers = async (query: string, isSource: boolean) => {
  if (query.length < 2) {
    if (isSource) sourceResults.value = []
    else targetResults.value = []
    return
  }

  try {
    const results = await api.get<PlayerAutocomplete[]>('/admin/operations/autocomplete/players', {
      q: query,
      limit: 10
    })
    if (isSource) {
      sourceResults.value = results
      showSourceDropdown.value = true
    } else {
      targetResults.value = results
      showTargetDropdown.value = true
    }
  } catch {
    if (isSource) sourceResults.value = []
    else targetResults.value = []
  }
}

// Watch search inputs with debounce
watch(searchSource, (value) => {
  if (sourceTimeout) clearTimeout(sourceTimeout)
  if (selectedSource.value && selectedSource.value.label !== value) {
    selectedSource.value = null
  }
  sourceTimeout = setTimeout(() => searchPlayers(value, true), 300)
})

watch(searchTarget, (value) => {
  if (targetTimeout) clearTimeout(targetTimeout)
  if (selectedTarget.value && selectedTarget.value.label !== value) {
    selectedTarget.value = null
  }
  targetTimeout = setTimeout(() => searchPlayers(value, false), 300)
})

// Select player
const selectSource = (player: PlayerAutocomplete) => {
  selectedSource.value = player
  searchSource.value = player.label
  showSourceDropdown.value = false
}

const selectTarget = (player: PlayerAutocomplete) => {
  selectedTarget.value = player
  searchTarget.value = player.label
  showTargetDropdown.value = false
}

// Merge players
const openMergeModal = () => {
  if (!selectedSource.value || !selectedTarget.value) return
  if (selectedSource.value.matric === selectedTarget.value.matric) {
    toast.add({
      title: t('common.error'),
      description: 'Source et cible ne peuvent pas être identiques',
      color: 'error',
      duration: 3000
    })
    return
  }
  confirmMergeModal.value = true
}

const confirmMerge = async () => {
  if (!selectedSource.value || !selectedTarget.value) return

  loading.value = true
  try {
    await api.post('/admin/operations/players/merge', {
      sourceMatric: selectedSource.value.matric,
      targetMatric: selectedTarget.value.matric
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.players.success_merge'),
      color: 'success',
      duration: 3000
    })
    confirmMergeModal.value = false
    // Reset form
    searchSource.value = ''
    searchTarget.value = ''
    selectedSource.value = null
    selectedTarget.value = null
    sourceResults.value = []
    targetResults.value = []
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.players.error_merge'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Auto merge
const openAutoMergeModal = () => {
  confirmAutoMergeModal.value = true
}

const confirmAutoMerge = async () => {
  loading.value = true
  try {
    const result = await api.post<AutoMergeResult>('/admin/operations/players/auto-merge')
    toast.add({
      title: t('common.success'),
      description: t('operations.players.success_auto_merge', { count: result.count }),
      color: 'success',
      duration: 5000
    })
    confirmAutoMergeModal.value = false
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.players.error_merge'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Close dropdowns on click outside
const onClickOutside = () => {
  showSourceDropdown.value = false
  showTargetDropdown.value = false
}
</script>

<template>
  <div class="space-y-8" @click.self="onClickOutside">
    <!-- Manual merge -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.players.manual_merge') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Source player -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.players.source_player') }}
          </label>
          <input
            v-model="searchSource"
            type="text"
            :placeholder="t('operations.players.search_placeholder')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showSourceDropdown = sourceResults.length > 0"
            @blur="setTimeout(() => showSourceDropdown = false, 200)"
          >
          <!-- Dropdown -->
          <div
            v-if="showSourceDropdown && sourceResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="player in sourceResults"
              :key="player.matric"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm text-gray-900"
              @click="selectSource(player)"
            >
              <div class="font-medium">{{ player.nom }} {{ player.prenom }}</div>
              <div class="text-xs text-gray-500">
                {{ player.matric }} - {{ player.club || 'Sans club' }}
              </div>
            </button>
          </div>
          <!-- Selected indicator -->
          <div v-if="selectedSource" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2">
              <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-blue-600" />
              <span class="text-sm font-medium">{{ selectedSource.nom }} {{ selectedSource.prenom }}</span>
              <span class="text-xs text-gray-500">({{ selectedSource.matric }})</span>
            </div>
          </div>
        </div>

        <!-- Target player -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.players.target_player') }}
          </label>
          <input
            v-model="searchTarget"
            type="text"
            :placeholder="t('operations.players.search_placeholder')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showTargetDropdown = targetResults.length > 0"
            @blur="setTimeout(() => showTargetDropdown = false, 200)"
          >
          <!-- Dropdown -->
          <div
            v-if="showTargetDropdown && targetResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="player in targetResults"
              :key="player.matric"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm text-gray-900"
              @click="selectTarget(player)"
            >
              <div class="font-medium">{{ player.nom }} {{ player.prenom }}</div>
              <div class="text-xs text-gray-500">
                {{ player.matric }} - {{ player.club || 'Sans club' }}
              </div>
            </button>
          </div>
          <!-- Selected indicator -->
          <div v-if="selectedTarget" class="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
              <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-green-600" />
              <span class="text-sm font-medium">{{ selectedTarget.nom }} {{ selectedTarget.prenom }}</span>
              <span class="text-xs text-gray-500">({{ selectedTarget.matric }})</span>
            </div>
          </div>
        </div>
      </div>

      <button
        :disabled="!selectedSource || !selectedTarget || loading"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        @click="openMergeModal"
      >
        <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        {{ t('operations.players.merge_button') }}
      </button>
    </section>

    <!-- Auto merge -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.players.auto_merge') }}
      </h2>

      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" />
          <div>
            <p class="text-sm text-yellow-800">{{ t('operations.players.auto_merge_description') }}</p>
            <button
              :disabled="loading"
              class="mt-4 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              @click="openAutoMergeModal"
            >
              <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('operations.players.auto_merge_button') }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Confirm merge modal -->
    <AdminConfirmModal
      :open="confirmMergeModal"
      :title="t('operations.players.confirm_merge')"
      :message="t('operations.players.confirm_merge_message')"
      :item-name="`${selectedSource?.nom} ${selectedSource?.prenom} (${selectedSource?.matric}) => ${selectedTarget?.nom} ${selectedTarget?.prenom} (${selectedTarget?.matric})`"
      :confirm-text="t('operations.players.merge_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmMergeModal = false"
      @confirm="confirmMerge"
    />

    <!-- Confirm auto merge modal -->
    <AdminConfirmModal
      :open="confirmAutoMergeModal"
      :title="t('operations.players.confirm_auto_merge')"
      :message="t('operations.players.confirm_auto_merge_message')"
      :confirm-text="t('operations.players.auto_merge_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmAutoMergeModal = false"
      @confirm="confirmAutoMerge"
    />
  </div>
</template>
