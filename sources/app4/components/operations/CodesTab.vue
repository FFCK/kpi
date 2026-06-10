<script setup lang="ts">
import type { OperationsSeason, OperationsCompetition, CopyRcResult, CopyCompetitionsResult } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// Internal tab navigation
const activeSubTab = ref('change_code')

// State
const loading = ref(false)

// Change code state
const sourceCode = ref('')
const targetCode = ref('')
const allSeasons = ref(false)
const targetExists = ref(false)
const confirmModal = ref(false)

// Copy RC / Copy competitions: seasons list
const seasons = ref<OperationsSeason[]>([])
const seasonsLoaded = ref(false)

const loadSeasons = async () => {
  if (seasonsLoaded.value) return
  try {
    seasons.value = await api.get<OperationsSeason[]>('/admin/operations/seasons')
    seasonsLoaded.value = true
  } catch {
    seasons.value = []
  }
}

watch(activeSubTab, (tab) => {
  if (tab === 'copy_rc' || tab === 'copy_competitions') {
    loadSeasons()
  }
})

// Copy RC state
const copyRcSource = ref('')
const copyRcTarget = ref('')

// Copy competitions state
const competitions = ref<OperationsCompetition[]>([])
const copyCompSource = ref('')
const copyCompTarget = ref('')
const selectedCompetitions = ref<string[]>([])
const copyMatches = ref(false)

const loadCompetitions = async () => {
  if (!copyCompSource.value) {
    competitions.value = []
    return
  }
  try {
    competitions.value = await api.get<OperationsCompetition[]>(
      `/admin/operations/seasons/${copyCompSource.value}/competitions`
    )
  } catch {
    competitions.value = []
  }
}

watch(copyCompSource, () => {
  selectedCompetitions.value = []
  loadCompetitions()
})

const selectAllCompetitions = () => {
  selectedCompetitions.value = competitions.value.map(c => c.code)
}

const deselectAllCompetitions = () => {
  selectedCompetitions.value = []
}

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

// Copy RC
const copyRc = async () => {
  if (!copyRcSource.value || !copyRcTarget.value) return

  loading.value = true
  try {
    const result = await api.post<CopyRcResult>('/admin/operations/seasons/copy-rc', {
      sourceCode: copyRcSource.value,
      targetCode: copyRcTarget.value
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_copy_rc', { copied: result.copied, skipped: result.skipped }),
      color: 'success',
      duration: 3000
    })
    copyRcSource.value = ''
    copyRcTarget.value = ''
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_copy_rc'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Copy competitions
const copyCompetitions = async () => {
  if (!copyCompSource.value || !copyCompTarget.value || selectedCompetitions.value.length === 0) return

  loading.value = true
  try {
    const result = await api.post<CopyCompetitionsResult>('/admin/operations/seasons/copy-competitions', {
      sourceCode: copyCompSource.value,
      targetCode: copyCompTarget.value,
      competitionCodes: selectedCompetitions.value,
      copyMatches: copyMatches.value
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.seasons.success_copy_competitions', { copied: result.copied }),
      color: 'success',
      duration: 3000
    })
    copyCompSource.value = ''
    copyCompTarget.value = ''
    selectedCompetitions.value = []
    copyMatches.value = false
    competitions.value = []
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.seasons.error_copy_competitions'),
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
    <!-- Internal sub-tab navigation -->
    <div class="border-b border-header-200">
      <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Competitions tabs">
        <button
          v-for="tab in [
            { id: 'change_code', label: t('operations.codes.title'), icon: 'i-heroicons-code-bracket' },
            { id: 'copy_rc', label: t('operations.seasons.copy_rc'), icon: 'i-heroicons-document-duplicate' },
            { id: 'copy_competitions', label: t('operations.seasons.copy_competitions'), icon: 'i-heroicons-clipboard-document-list' },
          ]"
          :key="tab.id"
          :class="[
            activeSubTab === tab.id
              ? 'border-primary-500 text-primary-600 bg-primary-50'
              : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300',
            'whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors rounded-t'
          ]"
          @click="activeSubTab = tab.id"
        >
          <UIcon :name="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Change code -->
    <section v-if="activeSubTab === 'change_code'">
      <div class="max-w-xl space-y-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.codes.source_code') }}
          </label>
          <input
            v-model="sourceCode"
            type="text"
            placeholder="ex: N1H"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.codes.target_code') }}
          </label>
          <input
            v-model="targetCode"
            type="text"
            placeholder="ex: N1M"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
          >
        </div>

        <div class="space-y-3 pt-2">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="allSeasons"
              type="checkbox"
              class="w-4 h-4 rounded border-header-300 text-primary-600 focus:ring-primary-500"
            >
            <span class="text-sm text-header-700">{{ t('operations.codes.all_seasons') }}</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="targetExists"
              type="checkbox"
              class="w-4 h-4 rounded border-header-300 text-primary-600 focus:ring-primary-500"
            >
            <span class="text-sm text-header-700">{{ t('operations.codes.target_exists') }}</span>
          </label>
        </div>

        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-primary-600 mt-0.5 shrink-0" />
            <div class="text-sm text-primary-800">
              <p v-if="allSeasons">Modifiera le code dans <strong>toutes les saisons</strong>.</p>
              <p v-else>Modifiera le code uniquement pour la <strong>saison active</strong>.</p>
            </div>
          </div>
        </div>

        <button
          :disabled="!sourceCode.trim() || !targetCode.trim() || loading"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="openConfirmModal"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('operations.codes.change_button') }}
        </button>
      </div>
    </section>

    <!-- Copy RC -->
    <section v-if="activeSubTab === 'copy_rc'">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.source_season') }}
          </label>
          <select
            v-model="copyRcSource"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">--</option>
            <option v-for="season in seasons" :key="season.code" :value="season.code">
              {{ season.code }} {{ season.active ? '(active)' : '' }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.seasons.target_season') }}
          </label>
          <select
            v-model="copyRcTarget"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">--</option>
            <option
              v-for="season in seasons"
              :key="season.code"
              :value="season.code"
              :disabled="season.code === copyRcSource"
            >
              {{ season.code }} {{ season.active ? '(active)' : '' }}
            </option>
          </select>
        </div>
        <div>
          <button
            :disabled="!copyRcSource || !copyRcTarget || loading"
            class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="copyRc"
          >
            {{ t('operations.seasons.copy_rc_button') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Copy competitions -->
    <section v-if="activeSubTab === 'copy_competitions'">
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.seasons.source_season') }}
            </label>
            <select
              v-model="copyCompSource"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
              <option value="">--</option>
              <option v-for="season in seasons" :key="season.code" :value="season.code">
                {{ season.code }} {{ season.active ? '(active)' : '' }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.seasons.target_season') }}
            </label>
            <select
              v-model="copyCompTarget"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
              <option value="">--</option>
              <option
                v-for="season in seasons"
                :key="season.code"
                :value="season.code"
                :disabled="season.code === copyCompSource"
              >
                {{ season.code }} {{ season.active ? '(active)' : '' }}
              </option>
            </select>
          </div>
        </div>

        <!-- Competitions selection -->
        <div v-if="competitions.length > 0">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-header-700">
              {{ t('operations.seasons.select_competitions') }}
            </label>
            <div class="flex gap-2">
              <button class="text-sm text-primary-600 hover:underline" @click="selectAllCompetitions">
                {{ t('stats.params.select_all') }}
              </button>
              <button class="text-sm text-header-600 hover:underline" @click="deselectAllCompetitions">
                {{ t('stats.params.deselect_all') }}
              </button>
            </div>
          </div>
          <div class="max-h-48 overflow-y-auto border border-header-200 rounded-lg p-2 space-y-1">
            <label
              v-for="comp in competitions"
              :key="comp.code"
              class="flex items-center gap-2 p-2 hover:bg-header-50 rounded cursor-pointer"
            >
              <input
                v-model="selectedCompetitions"
                type="checkbox"
                :value="comp.code"
                class="w-4 h-4 rounded border-header-300 text-primary-600 focus:ring-primary-500"
              >
              <span class="text-sm">{{ comp.code }} - {{ comp.libelle }}</span>
            </label>
          </div>
        </div>

        <!-- Copy matches option -->
        <div v-if="selectedCompetitions.length > 0">
          <label class="flex items-center gap-2">
            <input
              v-model="copyMatches"
              type="checkbox"
              class="w-4 h-4 rounded border-header-300 text-primary-600 focus:ring-primary-500"
            >
            <span class="text-sm text-header-700">{{ t('operations.seasons.copy_matches') }}</span>
          </label>
        </div>

        <button
          :disabled="!copyCompSource || !copyCompTarget || selectedCompetitions.length === 0 || loading"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
          @click="copyCompetitions"
        >
          {{ t('operations.seasons.copy_competitions_button') }}
          <span v-if="selectedCompetitions.length > 0" class="ml-1">
            ({{ selectedCompetitions.length }})
          </span>
        </button>
      </div>
    </section>

    <!-- Confirm change code modal -->
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
