<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t, locale } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const statsStore = useStatsStore()
const workContext = useWorkContextStore()

// Types
interface StatType {
  value: string
  labelKey: string
  restricted: boolean
}

interface StatTypeCategory {
  category: string
  categoryLabelKey: string
  types: StatType[]
}

interface CompetitionGroup {
  labelKey: string
  options: { code: string; libelle: string }[]
}

interface FiltersResponse {
  seasons: string[]
  activeSeason: string
  competitions: CompetitionGroup[]
  statTypes: StatTypeCategory[]
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
const statTypeCategories = ref<StatTypeCategory[]>([])
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

// Filter competition groups by work context (keep only competitions in context)
// Rebuilds optgroups using workContext sections for correct labels
const filterCompetitionsByContext = (groups: CompetitionGroup[]): CompetitionGroup[] => {
  if (!workContext.hasValidContext) return groups
  const contextCodes = new Set(workContext.competitionCodes)

  // Build lookup: code → option from stats API
  const optionsByCode = new Map<string, { code: string; libelle: string }>()
  for (const group of groups) {
    for (const opt of group.options) {
      optionsByCode.set(opt.code, opt)
    }
  }

  // Rebuild groups using workContext sections for correct labels
  const sectionGroups = new Map<number, CompetitionGroup>()
  for (const wcGroup of workContext.groups) {
    for (const comp of wcGroup.competitions) {
      if (contextCodes.has(comp.code) && optionsByCode.has(comp.code)) {
        if (!sectionGroups.has(wcGroup.section)) {
          sectionGroups.set(wcGroup.section, {
            labelKey: `context.sections.${wcGroup.section}`,
            options: []
          })
        }
        sectionGroups.get(wcGroup.section)!.options.push(optionsByCode.get(comp.code)!)
      }
    }
  }

  return Array.from(sectionGroups.values())
}

// Load filters on mount
const loadFilters = async () => {
  loadingFilters.value = true
  try {
    // Use stored season from statsStore if available, otherwise use workContext season
    const seasonParam = (statsStore.initialized && statsStore.season)
      ? statsStore.season
      : workContext.season || undefined
    const response = await api.get<FiltersResponse>('/admin/stats/filters', seasonParam ? { season: seasonParam } : undefined)
    seasons.value = response.seasons
    competitionGroups.value = filterCompetitionsByContext(response.competitions)

    // Filter stat type categories based on user profile - remove restricted types if profile > 6
    statTypeCategories.value = response.statTypes
      .map(category => ({
        ...category,
        types: category.types.filter(st => {
          if (st.restricted && authStore.profile > 6) return false
          return true
        })
      }))
      .filter(category => category.types.length > 0) // Remove empty categories

    // Restore from store if initialized, otherwise use defaults
    if (statsStore.initialized) {
      selectedSeason.value = statsStore.season || response.activeSeason
      selectedStatType.value = statsStore.statType
      selectedCompetitions.value = [...statsStore.competitions]
      limit.value = statsStore.limit
    } else {
      selectedSeason.value = workContext.season || response.activeSeason
      selectedCompetitions.value = []
    }
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('stats.error_load_filters')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'error',
      duration: 3000
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
      color: 'error',
      duration: 3000
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

  // Save to store for persistence
  statsStore.setParams({
    season: tempSeason.value,
    statType: tempStatType.value,
    competitions: tempCompetitions.value,
    limit: tempLimit.value
  })

  await loadStats()
}

// Reload competitions when temp season changes in modal
const onTempSeasonChange = async () => {
  try {
    const response = await api.get<FiltersResponse>('/admin/stats/filters', { season: tempSeason.value })
    competitionGroups.value = filterCompetitionsByContext(response.competitions)
    // Reset temp competition selection when season changes
    tempCompetitions.value = []
  } catch {
    // Ignore
  }
}

// Load on mount
onMounted(async () => {
  await workContext.initContext()
  await loadFilters()
  if (selectedCompetitions.value.length > 0) {
    loadStats()
  }
})

// Column labels - computed once and cached
const columnLabels = computed<Record<string, string>>(() => ({
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
  departement: t('stats.columns.departement'),
  responsableInsc: t('stats.columns.responsable_insc'),
  responsableR1: t('stats.columns.responsable_r1'),
  delegue: t('stats.columns.delegue'),
  chefArbitre: t('stats.columns.chef_arbitre'),
  dateMatch: t('stats.columns.date_match'),
  heureMatch: t('stats.columns.heure_match'),
  equipeA: t('stats.columns.equipe_a'),
  equipeB: t('stats.columns.equipe_b'),
  arbitrePrincipal: t('stats.columns.arbitre_principal'),
  arbitreSecondaire: t('stats.columns.arbitre_secondaire'),
  numeroOrdre: t('stats.columns.numero_ordre'),
  ligne1: t('stats.columns.ligne1'),
  ligne2: t('stats.columns.ligne2'),
  secretaire: t('stats.columns.secretaire'),
  chronometre: t('stats.columns.chronometre'),
  timeshoot: t('stats.columns.timeshoot'),
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
}))

// Numeric columns set for O(1) lookup
const numericColumnsSet = new Set([
  'buts', 'vert', 'jaune', 'rouge', 'rougeDefinitif', 'fairplay',
  'principal', 'secondaire', 'total', 'nbMatchs', 'matchs',
  'hommesU16', 'hommesU18', 'hommesU23', 'hommesU35', 'hommesPlus35', 'hommesTotal',
  'femmesU16', 'femmesU18', 'femmesU23', 'femmesU35', 'femmesPlus35', 'femmesTotal',
  'totalActivite', 'numero', 'numeroOrdre', 'id'
])

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

// Check if column is numeric - use Set for O(1) lookup
const isNumericColumn = (column: string): boolean => numericColumnsSet.has(column)

// Get column label - use cached computed
const getColumnLabel = (column: string): string => columnLabels.value[column] || column

// Pre-compute columns for mobile view (exclude nom/prenom)
const mobileColumns = computed(() => columns.value.filter(c => c !== 'nom' && c !== 'prenom'))

// Get stat type label
const getStatTypeLabel = computed(() => {
  for (const category of statTypeCategories.value) {
    const st = category.types.find(s => s.value === selectedStatType.value)
    if (st) return t(st.labelKey)
  }
  return selectedStatType.value
})

// Get stat type description
const getStatTypeDescription = computed(() => {
  return t(`stats.descriptions.${selectedStatType.value}`) || ''
})

// Get temp stat type description (for modal)
const getTempStatTypeDescription = computed(() => {
  return t(`stats.descriptions.${tempStatType.value}`) || ''
})

// Get summary of selected competitions
const selectedCompetitionsSummary = computed(() => {
  const count = selectedCompetitions.value.length
  if (count === 0) return t('stats.params.no_competition')
  if (count <= 3) return selectedCompetitions.value.join(', ')
  return t('stats.params.competitions_count', { count })
})

// Get tooltip text for competitions when more than 3 are selected
const selectedCompetitionsTooltip = computed(() => {
  const count = selectedCompetitions.value.length
  return count > 3 ? selectedCompetitions.value.join(', ') : ''
})

// Check if current stat type should show ranking column
const showRankingColumn = computed(() => {
  const rankedStatTypes = ['Buteurs', 'Cartons', 'Fairplay', 'Arbitrage']
  return rankedStatTypes.includes(selectedStatType.value)
})

// Export functions
const exportingXlsx = ref(false)
const exportingPdf = ref(false)

const getExportParams = (): Record<string, string> => {
  const params: Record<string, string> = {
    season: selectedSeason.value,
    type: selectedStatType.value,
    limit: String(limit.value),
    labels: JSON.stringify(columnLabels.value),
    title: getStatTypeLabel.value,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    locale: locale.value
  }
  return params
}

const getExportUrl = (format: 'xlsx' | 'pdf'): string => {
  const params = new URLSearchParams()
  const exportParams = getExportParams()
  Object.entries(exportParams).forEach(([key, value]) => {
    params.set(key, value)
  })
  selectedCompetitions.value.forEach(c => params.append('competitions[]', c))
  return `/admin/stats/export/${format}?${params.toString()}`
}

const exportXlsx = async () => {
  if (selectedCompetitions.value.length === 0) return
  exportingXlsx.value = true
  try {
    const url = getExportUrl('xlsx')
    const response = await api.getBlob(url)
    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `stats_${selectedStatType.value}_${new Date().toISOString().slice(0, 10)}.xlsx`
    link.click()
    URL.revokeObjectURL(link.href)
  } catch (error) {
    toast.add({
      title: t('common.error'),
      description: t('stats.error_export'),
      color: 'error'
    })
  } finally {
    exportingXlsx.value = false
  }
}

const exportPdf = async () => {
  if (selectedCompetitions.value.length === 0) return
  exportingPdf.value = true
  try {
    const url = getExportUrl('pdf')
    const response = await api.getBlob(url)
    const blob = new Blob([response], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `stats_${selectedStatType.value}_${new Date().toISOString().slice(0, 10)}.pdf`
    link.click()
    URL.revokeObjectURL(link.href)
  } catch (error) {
    toast.add({
      title: t('common.error'),
      description: t('stats.error_export'),
      color: 'error'
    })
  } finally {
    exportingPdf.value = false
  }
}
</script>

<template>
  <div>
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('stats.title') }}
      </h1>
    </div>

    <!-- Current parameters summary -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4 text-sm">
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.stat_type') }}:</span>
          <span class="font-semibold text-gray-900">{{ getStatTypeLabel }}</span>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.season') }}:</span>
          <span class="font-semibold text-gray-900">{{ selectedSeason }}</span>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.competitions') }}:</span>
          <UTooltip :text="selectedCompetitionsTooltip">
            <span class="font-semibold text-gray-900">{{ selectedCompetitionsSummary }}</span>
          </UTooltip>
        </div>
        <div class="w-px h-4 bg-gray-300" />
        <div class="flex items-center gap-2">
          <span class="text-gray-500">{{ t('stats.params.limit') }}:</span>
          <span class="font-semibold text-gray-900">{{ limit }}</span>
        </div>
        <div class="ml-auto flex items-center gap-2">
          <!-- Export buttons -->
          <button
            type="button"
            class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedCompetitions.length === 0 || exportingXlsx"
            @click="exportXlsx"
          >
            <UIcon v-if="exportingXlsx" name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
            <UIcon v-else name="heroicons:table-cells" class="w-4 h-4" />
            {{ t('stats.params.export_xlsx') }}
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedCompetitions.length === 0 || exportingPdf"
            @click="exportPdf"
          >
            <UIcon v-if="exportingPdf" name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
            <UIcon v-else name="heroicons:document-text" class="w-4 h-4" />
            {{ t('stats.params.export_pdf') }}
          </button>
          <!-- Parameters button -->
          <button
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm"
            @click="openFiltersModal"
          >
            <UIcon name="heroicons:adjustments-horizontal" class="w-4 h-4" />
            {{ t('stats.params.change') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Description and results count -->
    <div class="mb-4 flex items-center justify-between text-sm">
      <span class="text-gray-600 italic">
        {{ getStatTypeDescription }}
      </span>
      <span v-if="loading" class="text-gray-500">
        <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin inline mr-1" />
        {{ t('common.loading') }}
      </span>
      <span v-else class="font-semibold text-gray-700">
        {{ t('stats.results_count', { count }) }}
      </span>
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
        <template #header-right>Toto</template>

        <!-- Content: show all columns -->
        <div class="space-y-1 text-sm">
          <div
            v-for="column in mobileColumns"
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
        <!-- Stat Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('stats.params.stat_type') }}
          </label>
          <select
            v-model="tempStatType"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <optgroup
              v-for="category in statTypeCategories"
              :key="category.category"
              :label="t(category.categoryLabelKey)"
            >
              <option v-for="st in category.types" :key="st.value" :value="st.value">
                {{ t(st.labelKey) }}
              </option>
            </optgroup>
          </select>
          <p v-if="getTempStatTypeDescription" class="mt-1 text-xs text-gray-500 italic">
            {{ getTempStatTypeDescription }}
          </p>
        </div>

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

          <!-- Limit with +/- buttons -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('stats.params.limit') }}
            </label>
            <div class="flex items-center">
              <button
                type="button"
                class="px-3 py-2 border border-gray-300 rounded-l-lg bg-gray-50 text-gray-700 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                @click="tempLimit = Math.max(1, tempLimit - 1)"
              >
                <UIcon name="heroicons:minus" class="w-4 h-4" />
              </button>
              <input
                v-model.number="tempLimit"
                type="tel"
                min="1"
                max="500"
                class="w-full px-3 py-2 border-y border-gray-300 bg-white text-gray-900 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                type="button"
                class="px-3 py-2 border border-gray-300 rounded-r-lg bg-gray-50 text-gray-700 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                @click="tempLimit = Math.min(500, tempLimit + 1)"
              >
                <UIcon name="heroicons:plus" class="w-4 h-4" />
              </button>
            </div>
          </div>
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
              :key="group.labelKey"
              :label="t(group.labelKey)"
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
