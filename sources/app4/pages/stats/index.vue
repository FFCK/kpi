<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()

// Types
interface StatType {
  value: string
  label: string
  restricted: boolean
}

interface CompetitionGroup {
  label: string
  options: { code: string; libelle: string }[]
}

interface FiltersResponse {
  seasons: string[]
  activeSeason: string
  competitions: CompetitionGroup[]
  statTypes: StatType[]
}

interface StatsResponse {
  type: string
  columns: string[]
  data: Record<string, unknown>[]
  meta: {
    season: string
    competitions: string[]
    limit: number
    count: number
  }
}

// Toast notifications
const toast = useToast()

// State
const loading = ref(false)
const loadingFilters = ref(true)
const showFiltersModal = ref(false)

// Filter state
const seasons = ref<string[]>([])
const selectedSeason = ref('')
const competitionGroups = ref<CompetitionGroup[]>([])
const selectedCompetitions = ref<string[]>([])
const statTypes = ref<StatType[]>([])
const selectedStatType = ref('Buteurs')
const limit = ref(30)

// Temporary state for modal (to allow cancel)
const tempSeason = ref('')
const tempStatType = ref('')
const tempCompetitions = ref<string[]>([])
const tempLimit = ref(30)

// Data state
const columns = ref<string[]>([])
const data = ref<Record<string, unknown>[]>([])
const count = ref(0)

// Load filters on mount
const loadFilters = async () => {
  loadingFilters.value = true
  try {
    const response = await api.get<FiltersResponse>('/admin/stats/filters')
    seasons.value = response.seasons
    selectedSeason.value = response.activeSeason
    competitionGroups.value = response.competitions

    // Filter stat types based on user profile
    statTypes.value = response.statTypes.filter(st => {
      if (st.restricted && authStore.profile > 6) return false
      return true
    })

    // No default competition selection - user must configure parameters
    selectedCompetitions.value = []
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('stats.error_load_filters')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'red'
    })
  } finally {
    loadingFilters.value = false
  }
}

// Load stats data
const loadStats = async () => {
  if (selectedCompetitions.value.length === 0) {
    data.value = []
    columns.value = []
    count.value = 0
    return
  }

  loading.value = true
  try {
    const params: Record<string, unknown> = {
      season: selectedSeason.value,
      type: selectedStatType.value,
      limit: limit.value,
      competitions: selectedCompetitions.value
    }

    const response = await api.get<StatsResponse>('/admin/stats/data', params as Record<string, string | number>)
    columns.value = response.columns
    data.value = response.data
    count.value = response.meta.count
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('stats.error_load_data')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'red'
    })
    data.value = []
    columns.value = []
    count.value = 0
  } finally {
    loading.value = false
  }
}

// Open modal with current values
const openFiltersModal = () => {
  tempSeason.value = selectedSeason.value
  tempStatType.value = selectedStatType.value
  tempCompetitions.value = [...selectedCompetitions.value]
  tempLimit.value = limit.value
  showFiltersModal.value = true
}

// Apply filters from modal
const applyFilters = async () => {
  selectedSeason.value = tempSeason.value
  selectedStatType.value = tempStatType.value
  selectedCompetitions.value = [...tempCompetitions.value]
  limit.value = tempLimit.value
  showFiltersModal.value = false
  await loadStats()
}

// Reload competitions when temp season changes in modal
const onTempSeasonChange = async () => {
  try {
    const response = await api.get<FiltersResponse>('/admin/stats/filters', { season: tempSeason.value })
    competitionGroups.value = response.competitions
    // Reset temp competition selection when season changes
    tempCompetitions.value = []
  } catch {
    // Ignore
  }
}

// Load on mount
onMounted(async () => {
  await loadFilters()
  if (selectedCompetitions.value.length > 0) {
    loadStats()
  }
})

// Get column label for display
const getColumnLabel = (column: string): string => {
  const labels: Record<string, string> = {
    competition: t('stats.columns.competition'),
    licence: t('stats.columns.licence'),
    matric: t('stats.columns.matric'),
    nom: t('stats.columns.nom'),
    prenom: t('stats.columns.prenom'),
    sexe: t('stats.columns.sexe'),
    numero: t('stats.columns.numero'),
    equipe: t('stats.columns.equipe'),
    buts: t('stats.columns.buts'),
    vert: t('stats.columns.vert'),
    jaune: t('stats.columns.jaune'),
    rouge: t('stats.columns.rouge'),
    rougeDefinitif: t('stats.columns.rouge_definitif'),
    fairplay: t('stats.columns.fairplay'),
    principal: t('stats.columns.principal'),
    secondaire: t('stats.columns.secondaire'),
    total: t('stats.columns.total'),
    nbMatchs: t('stats.columns.nb_matchs'),
    nomEquipe: t('stats.columns.nom_equipe'),
    numeroClub: t('stats.columns.numero_club'),
    nomClub: t('stats.columns.nom_club'),
    irregularite: t('stats.columns.irregularite'),
    matchs: t('stats.columns.matchs'),
    id: t('stats.columns.id'),
    libelle: t('stats.columns.libelle'),
    lieu: t('stats.columns.lieu'),
    dateDebut: t('stats.columns.date_debut'),
    dateFin: t('stats.columns.date_fin'),
    delegue: t('stats.columns.delegue'),
    chefArbitre: t('stats.columns.chef_arbitre'),
    dateMatch: t('stats.columns.date_match'),
    heureMatch: t('stats.columns.heure_match'),
    equipeA: t('stats.columns.equipe_a'),
    equipeB: t('stats.columns.equipe_b'),
    arbitrePrincipal: t('stats.columns.arbitre_principal'),
    arbitreSecondaire: t('stats.columns.arbitre_secondaire'),
    codeClub: t('stats.columns.code_club'),
    club: t('stats.columns.club'),
    arbitre: t('stats.columns.arbitre'),
    niveau: t('stats.columns.niveau'),
    saison: t('stats.columns.saison'),
    naissance: t('stats.columns.naissance'),
    clubActuel: t('stats.columns.club_actuel'),
    categorie: t('stats.columns.categorie'),
    cd: t('stats.columns.cd'),
    cr: t('stats.columns.cr'),
    clubActuelJoueurs: t('stats.columns.club_actuel_joueurs'),
    hommesU16: t('stats.columns.hommes_u16'),
    hommesU18: t('stats.columns.hommes_u18'),
    hommesU23: t('stats.columns.hommes_u23'),
    hommesU35: t('stats.columns.hommes_u35'),
    hommesPlus35: t('stats.columns.hommes_plus35'),
    hommesTotal: t('stats.columns.hommes_total'),
    femmesU16: t('stats.columns.femmes_u16'),
    femmesU18: t('stats.columns.femmes_u18'),
    femmesU23: t('stats.columns.femmes_u23'),
    femmesU35: t('stats.columns.femmes_u35'),
    femmesPlus35: t('stats.columns.femmes_plus35'),
    femmesTotal: t('stats.columns.femmes_total'),
    totalActivite: t('stats.columns.total_activite'),
    type: t('stats.columns.type'),
    date: t('stats.columns.date'),
    details: t('stats.columns.details')
  }
  return labels[column] || column
}

// Format cell value for display
const formatCellValue = (value: unknown, column: string): string => {
  if (value === null || value === undefined) return '-'

  // Date formatting
  if (column.toLowerCase().includes('date') && typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}/)) {
    const d = new Date(value)
    return d.toLocaleDateString('fr-FR')
  }

  // Boolean formatting
  if (typeof value === 'boolean') {
    return value ? 'Oui' : 'Non'
  }

  return String(value)
}

// Check if column is numeric
const isNumericColumn = (column: string): boolean => {
  const numericColumns = [
    'buts', 'vert', 'jaune', 'rouge', 'rougeDefinitif', 'fairplay',
    'principal', 'secondaire', 'total', 'nbMatchs', 'matchs',
    'hommesU16', 'hommesU18', 'hommesU23', 'hommesU35', 'hommesPlus35', 'hommesTotal',
    'femmesU16', 'femmesU18', 'femmesU23', 'femmesU35', 'femmesPlus35', 'femmesTotal',
    'totalActivite', 'numero', 'numeroOrdre', 'id'
  ]
  return numericColumns.includes(column)
}

// Get stat type label
const getStatTypeLabel = computed(() => {
  const st = statTypes.value.find(s => s.value === selectedStatType.value)
  return st?.label || selectedStatType.value
})

// Get summary of selected competitions
const selectedCompetitionsSummary = computed(() => {
  const count = selectedCompetitions.value.length
  if (count === 0) return t('stats.params.no_competition')
  if (count <= 3) return selectedCompetitions.value.join(', ')
  return t('stats.params.competitions_count', { count })
})

// Check if current stat type should show ranking column
const showRankingColumn = computed(() => {
  const rankedStatTypes = ['Buteurs', 'Cartons', 'Fairplay', 'Arbitres']
  return rankedStatTypes.includes(selectedStatType.value)
})
</script>

<template>
  <div>
    <!-- Page header with filter button -->
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('stats.title') }}
      </h1>

      <button
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
        @click="openFiltersModal"
      >
        <UIcon name="heroicons:cog-6-tooth" class="w-5 h-5" />
        {{ t('stats.params.configure') }}
      </button>
    </div>

    <!-- Current parameters summary -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4 text-sm">
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.season') }}:</span>
          <span class="font-semibold text-gray-900">{{ selectedSeason }}</span>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.stat_type') }}:</span>
          <span class="font-semibold text-gray-900">{{ getStatTypeLabel }}</span>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.competitions') }}:</span>
          <span class="font-semibold text-gray-900">{{ selectedCompetitionsSummary }}</span>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.limit') }}:</span>
          <span class="font-semibold text-gray-900">{{ limit }}</span>
        </div>
        <div class="ml-auto">
          <span v-if="loading" class="text-gray-500">
            <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin inline mr-1" />
            {{ t('common.loading') }}
          </span>
          <span v-else class="font-semibold text-blue-600">
            {{ t('stats.results_count', { count }) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Ranking column -->
              <th
                v-if="showRankingColumn && data.length > 0"
                class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-center text-gray-500 w-16"
              >
                #
              </th>
              <th
                v-for="column in columns"
                :key="column"
                class="px-4 py-3 text-xs font-medium uppercase tracking-wider"
                :class="isNumericColumn(column)
                  ? 'text-right text-gray-500'
                  : 'text-left text-gray-500'"
              >
                {{ getColumnLabel(column) }}
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Loading state -->
            <tr v-if="loading && data.length === 0">
              <td :colspan="(showRankingColumn ? 1 : 0) + (columns.length || 1)" class="px-4 py-8 text-center text-gray-500">
                <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
                {{ t('common.loading') }}
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-else-if="data.length === 0">
              <td :colspan="(showRankingColumn ? 1 : 0) + (columns.length || 1)" class="px-4 py-8 text-center text-gray-500">
                {{ t('stats.empty') }}
              </td>
            </tr>

            <!-- Data rows -->
            <tr
              v-for="(row, index) in data"
              :key="index"
              class="hover:bg-gray-50"
            >
              <!-- Ranking cell -->
              <td
                v-if="showRankingColumn"
                class="px-4 py-3 text-sm whitespace-nowrap text-center font-semibold text-gray-500 w-16"
              >
                {{ index + 1 }}
              </td>
              <td
                v-for="column in columns"
                :key="column"
                class="px-4 py-3 text-sm whitespace-nowrap"
                :class="isNumericColumn(column)
                  ? 'text-right font-mono font-semibold text-gray-900 tabular-nums'
                  : 'text-gray-900'"
              >
                {{ formatCellValue(row[column], column) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      class="lg:hidden"
      :loading="loading && data.length === 0"
      :empty="data.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('stats.empty')"
    >
      <AdminCard
        v-for="(row, index) in data"
        :key="index"
      >
        <template #header>
          <h3 class="font-semibold text-gray-900 truncate">
            <span v-if="showRankingColumn" class="text-gray-500 mr-2">#{{ index + 1 }}</span>
            {{ row['nom'] }} {{ row['prenom'] }}
            <template v-if="!row['nom'] && row['equipe']">{{ row['equipe'] }}</template>
            <template v-if="!row['nom'] && !row['equipe'] && row['competition']">{{ row['competition'] }}</template>
          </h3>
        </template>

        <!-- Content: show all columns -->
        <div class="space-y-1 text-sm">
          <div
            v-for="column in columns.filter(c => !['nom', 'prenom'].includes(c))"
            :key="column"
            class="flex justify-between"
          >
            <span class="text-gray-500">{{ getColumnLabel(column) }}:</span>
            <span
              :class="isNumericColumn(column)
                ? 'font-mono font-semibold text-gray-900'
                : 'text-gray-700'"
            >
              {{ formatCellValue(row[column], column) }}
            </span>
          </div>
        </div>
      </AdminCard>
    </AdminCardList>

    <!-- Parameters Modal -->
    <AdminModal
      :open="showFiltersModal"
      :title="t('stats.params.title')"
      max-width="lg"
      @close="showFiltersModal = false"
    >
      <div class="space-y-4">
        <!-- Season + Limit on same row -->
        <div class="grid grid-cols-2 gap-4">
          <!-- Season -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('stats.params.season') }}
            </label>
            <select
              v-model="tempSeason"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              @change="onTempSeasonChange"
            >
              <option v-for="season in seasons" :key="season" :value="season">
                {{ season }}
              </option>
            </select>
          </div>

          <!-- Limit -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('stats.params.limit') }}
            </label>
            <input
              v-model.number="tempLimit"
              type="number"
              min="1"
              max="500"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Stat Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('stats.params.stat_type') }}
          </label>
          <select
            v-model="tempStatType"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option v-for="st in statTypes" :key="st.value" :value="st.value">
              {{ st.label }}
            </option>
          </select>
        </div>

        <!-- Competitions with optgroups -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('stats.params.competitions') }}
          </label>
          <select
            v-model="tempCompetitions"
            multiple
            size="10"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <optgroup
              v-for="group in competitionGroups"
              :key="group.label"
              :label="group.label"
            >
              <option
                v-for="comp in group.options"
                :key="comp.code"
                :value="comp.code"
              >
                {{ comp.code }} - {{ comp.libelle }}
              </option>
            </optgroup>
          </select>
          <p class="mt-1 text-xs text-gray-500">
            {{ t('stats.params.multi_select_hint') }}
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
        <button
          type="button"
          class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
          @click="showFiltersModal = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
          :disabled="tempCompetitions.length === 0"
          @click="applyFilters"
        >
          {{ t('stats.params.apply') }}
        </button>
      </div>
    </AdminModal>

    <!-- Scroll to top button -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
