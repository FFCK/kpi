<script setup lang="ts">
import type { OperationsSeason, OperationsCompetition, CopyRcResult, CopyCompetitionsResult } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)
const seasons = ref<OperationsSeason[]>([])
const competitions = ref<OperationsCompetition[]>([])

// Form state - Add season
const newSeasonCode = ref('')
const newSeasonNatDebut = ref('')
const newSeasonNatFin = ref('')
const newSeasonInterDebut = ref('')
const newSeasonInterFin = ref('')

// Form state - Copy RC
const copyRcSource = ref('')
const copyRcTarget = ref('')

// Form state - Copy competitions
const copyCompSource = ref('')
const copyCompTarget = ref('')
const selectedCompetitions = ref<string[]>([])
const copyMatches = ref(false)

// Modal state
const confirmActivateModal = ref(false)
const seasonToActivate = ref<OperationsSeason | null>(null)

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

// Load competitions for source season
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

// Watch for source season change
watch(copyCompSource, () => {
  selectedCompetitions.value = []
  loadCompetitions()
})

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

// Select/deselect all competitions
const selectAllCompetitions = () => {
  selectedCompetitions.value = competitions.value.map(c => c.code)
}

const deselectAllCompetitions = () => {
  selectedCompetitions.value = []
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
  <div class="space-y-8">
    <!-- Season list -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.seasons.list') }}
      </h2>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.code') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.status') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.nat_start') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.nat_end') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.inter_start') }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                {{ t('operations.seasons.inter_end') }}
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                {{ t('common.actions') }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading && seasons.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>
            <tr v-for="season in seasons" :key="season.code" :class="{ 'bg-green-200': season.active }">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">
                {{ season.code }}
              </td>
              <td class="px-4 py-3 text-sm">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    season.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ season.active ? t('operations.seasons.active') : t('operations.seasons.inactive') }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(season.natDebut) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(season.natFin) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(season.interDebut) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(season.interFin) }}</td>
              <td class="px-4 py-3 text-right">
                <button
                  v-if="!season.active"
                  class="text-sm text-blue-600 hover:text-blue-800"
                  @click="openActivateModal(season)"
                >
                  {{ t('operations.seasons.activate') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Add season -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.seasons.add') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.code') }} *
          </label>
          <input
            v-model="newSeasonCode"
            type="text"
            placeholder="2025"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.nat_start') }}
          </label>
          <input
            v-model="newSeasonNatDebut"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.nat_end') }}
          </label>
          <input
            v-model="newSeasonNatFin"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.inter_start') }}
          </label>
          <input
            v-model="newSeasonInterDebut"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.inter_end') }}
          </label>
          <input
            v-model="newSeasonInterFin"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="flex items-end">
          <button
            :disabled="!newSeasonCode || loading"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="addSeason"
          >
            {{ t('operations.seasons.add_button') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Copy RC -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.seasons.copy_rc') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.source_season') }}
          </label>
          <select
            v-model="copyRcSource"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">--</option>
            <option v-for="season in seasons" :key="season.code" :value="season.code">
              {{ season.code }} {{ season.active ? '(active)' : '' }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.seasons.target_season') }}
          </label>
          <select
            v-model="copyRcTarget"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
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
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="copyRc"
          >
            {{ t('operations.seasons.copy_rc_button') }}
          </button>
        </div>
      </div>
    </section>

    <!-- Copy competitions -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.seasons.copy_competitions') }}
      </h2>

      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.seasons.source_season') }}
            </label>
            <select
              v-model="copyCompSource"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">--</option>
              <option v-for="season in seasons" :key="season.code" :value="season.code">
                {{ season.code }} {{ season.active ? '(active)' : '' }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.seasons.target_season') }}
            </label>
            <select
              v-model="copyCompTarget"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
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
            <label class="block text-sm font-medium text-gray-700">
              {{ t('operations.seasons.select_competitions') }}
            </label>
            <div class="flex gap-2">
              <button class="text-sm text-blue-600 hover:underline" @click="selectAllCompetitions">
                {{ t('stats.params.select_all') }}
              </button>
              <button class="text-sm text-gray-600 hover:underline" @click="deselectAllCompetitions">
                {{ t('stats.params.deselect_all') }}
              </button>
            </div>
          </div>
          <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
            <label
              v-for="comp in competitions"
              :key="comp.code"
              class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer"
            >
              <input
                v-model="selectedCompetitions"
                type="checkbox"
                :value="comp.code"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
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
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700">{{ t('operations.seasons.copy_matches') }}</span>
          </label>
        </div>

        <button
          :disabled="!copyCompSource || !copyCompTarget || selectedCompetitions.length === 0 || loading"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          @click="copyCompetitions"
        >
          {{ t('operations.seasons.copy_competitions_button') }}
          <span v-if="selectedCompetitions.length > 0" class="ml-1">
            ({{ selectedCompetitions.length }})
          </span>
        </button>
      </div>
    </section>

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
  </div>
</template>
