<script setup lang="ts">
interface JournalEntry {
  id: number
  date: string
  userCode: string
  userIdentite: string
  userFonction: string
  action: string
  journal: string | null
  saison: string | null
  competition: string | null
  journee: number | null
  match: string | null
}

interface JournalResponse {
  items: JournalEntry[]
  total: number
  page: number
  limit: number
  totalPages: number
}

interface JournalUser {
  code: string
  identite: string
  fonction: string
}

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()

// Profile guard: only profiles <= 2
if (authStore.profile > 2) {
  navigateTo('/')
}

// State
const loading = ref(false)
const entries = ref<JournalEntry[]>([])
const page = ref(1)
const limit = ref(50)
const total = ref(0)
const totalPages = ref(0)
const searchQuery = ref('')

// Filters
const filterUser = ref('')
const filterAction = ref('')
const filterActionMode = ref<'prefix' | 'exact'>('prefix')
const filterSeason = ref('')
const filterCompetition = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

// Filter data
const journalUsers = ref<JournalUser[]>([])
const journalActions = ref<string[]>([])
const seasons = ref<{ code: string; active: boolean }[]>([])

// Action groups for "Ensemble" optgroup
const actionGroups = [
  { value: 'Connexion', labelKey: 'journal.filters.action_connexion' },
  { value: 'Ajout', labelKey: 'journal.filters.action_ajout' },
  { value: 'Modif', labelKey: 'journal.filters.action_modif' },
  { value: 'Supp', labelKey: 'journal.filters.action_supp' },
  { value: 'Calcul', labelKey: 'journal.filters.action_calcul' },
]

// Load filter data
async function loadFilters() {
  try {
    const [usersData, actionsData, seasonsData] = await Promise.all([
      api.get<JournalUser[]>('/admin/journal/users'),
      api.get<string[]>('/admin/journal/actions'),
      api.get<{ seasons: { code: string; active: boolean }[] }>('/admin/filters/seasons'),
    ])
    journalUsers.value = usersData
    journalActions.value = actionsData
    seasons.value = seasonsData.seasons || []
  } catch { /* useApi handles toast */ }
}

// Load journal entries
async function loadJournal() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      limit: limit.value,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterUser.value) params.user = filterUser.value
    if (filterAction.value) {
      params.action = filterAction.value
      params.actionMode = filterActionMode.value
    }
    if (filterSeason.value) params.season = filterSeason.value
    if (filterCompetition.value) params.competition = filterCompetition.value
    if (filterDateFrom.value) params.dateFrom = filterDateFrom.value
    if (filterDateTo.value) params.dateTo = filterDateTo.value

    const data = await api.get<JournalResponse>('/admin/journal', params)
    entries.value = data.items
    total.value = data.total
    totalPages.value = data.totalPages
  } catch { /* useApi handles toast */ }
  finally { loading.value = false }
}

// Handle action filter change (detect group vs detail)
function onActionFilterChange(event: Event) {
  const select = event.target as HTMLSelectElement
  const selectedOption = select.options[select.selectedIndex]
  const mode = selectedOption?.dataset.mode as 'prefix' | 'exact' | undefined
  filterActionMode.value = mode || 'prefix'
  filterAction.value = select.value
}

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadJournal()
  }, 300)
})

// Debounced competition filter
let competitionTimeout: ReturnType<typeof setTimeout> | null = null
watch(filterCompetition, () => {
  if (competitionTimeout) clearTimeout(competitionTimeout)
  competitionTimeout = setTimeout(() => {
    page.value = 1
    loadJournal()
  }, 300)
})

watch([filterUser, filterAction, filterSeason, filterDateFrom, filterDateTo], () => {
  page.value = 1
  loadJournal()
})

watch([page, limit], () => {
  loadJournal()
})

// Format date for display
function formatDate(dateStr: string): string {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = String(d.getFullYear()).slice(-2)
  const hours = String(d.getHours()).padStart(2, '0')
  const minutes = String(d.getMinutes()).padStart(2, '0')
  return `${day}/${month}/${year} ${hours}:${minutes}`
}

// Format competition + season column
function formatCompSaison(entry: JournalEntry): string {
  const parts: string[] = []
  if (entry.competition) parts.push(entry.competition)
  if (entry.saison) parts.push(entry.saison)
  return parts.join(' - ')
}

// Check if any filter is active
const hasActiveFilters = computed(() => {
  return filterUser.value || filterAction.value || filterSeason.value ||
    filterCompetition.value || filterDateFrom.value || filterDateTo.value ||
    searchQuery.value
})

onMounted(() => {
  loadFilters()
  loadJournal()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-header-900">{{ t('journal.title') }}</h1>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('journal.search_placeholder')"
      :show-add="false"
      :show-bulk-delete="false"
    >
      <template #before-search>
        <!-- User filter -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.user') }}</label>
          <select
            v-model="filterUser"
            class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">{{ t('journal.filters.user_all') }}</option>
            <option v-for="u in journalUsers" :key="u.code" :value="u.code">
              {{ u.identite }}
            </option>
          </select>
        </div>

        <!-- Action filter with optgroups -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.action') }}</label>
          <select
            :value="filterAction"
            class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @change="onActionFilterChange"
          >
            <option value="">{{ t('journal.filters.action_all') }}</option>
            <optgroup :label="t('journal.filters.action_group_ensemble')">
              <option v-for="g in actionGroups" :key="g.value" :value="g.value" data-mode="prefix">
                {{ t(g.labelKey) }}
              </option>
            </optgroup>
            <optgroup :label="t('journal.filters.action_group_detail')">
              <option v-for="a in journalActions" :key="a" :value="a" data-mode="exact">
                {{ a }}
              </option>
            </optgroup>
          </select>
        </div>

        <!-- Season filter -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.season') }}</label>
          <select
            v-model="filterSeason"
            class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">{{ t('journal.filters.season_all') }}</option>
            <option v-for="s in seasons" :key="s.code" :value="s.code">
              {{ s.code }}{{ s.active ? ' ★' : '' }}
            </option>
          </select>
        </div>

        <!-- Competition filter (text input) -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.competition') }}</label>
          <input
            v-model="filterCompetition"
            type="text"
            :placeholder="t('journal.filters.competition_placeholder')"
            class="w-32 px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- Date from -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.date_from') }}</label>
          <input
            v-model="filterDateFrom"
            type="date"
            class="px-2 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- Date to -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-header-500">{{ t('journal.filters.date_to') }}</label>
          <input
            v-model="filterDateTo"
            type="date"
            class="px-2 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </template>
    </AdminToolbar>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-header-400" />
    </div>

    <!-- Desktop Table -->
    <div v-else class="hidden lg:block overflow-x-auto">
      <table v-if="entries.length > 0" class="min-w-full divide-y divide-header-200 bg-white rounded-lg shadow-sm">
        <thead class="bg-header-50">
          <tr>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.date') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.identite') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.action') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.detail') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.competition') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.gameday') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('journal.table.match') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-header-200">
          <tr v-for="entry in entries" :key="entry.id" class="hover:bg-header-50">
            <td class="px-3 py-2 text-sm text-header-600 whitespace-nowrap">
              {{ formatDate(entry.date) }}
            </td>
            <td class="px-3 py-2">
              <div class="text-sm font-medium text-header-900" :title="entry.userFonction">
                {{ entry.userIdentite }}
              </div>
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ entry.action }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600 max-w-md truncate" :title="entry.journal || ''">
              {{ entry.journal || '' }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600 whitespace-nowrap">
              <span v-if="entry.competition" class="font-semibold">{{ entry.competition }}</span>
              <span v-if="entry.competition && entry.saison"> - </span>
              <span v-if="entry.saison">{{ entry.saison }}</span>
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ entry.journee ?? '' }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ entry.match ?? '' }}
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-center py-12 text-header-500">
        {{ hasActiveFilters ? t('journal.empty_filtered') : t('journal.empty') }}
      </div>
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      :loading="loading"
      :empty="entries.length === 0"
      :loading-text="t('journal.loading')"
      :empty-text="hasActiveFilters ? t('journal.empty_filtered') : t('journal.empty')"
    >
      <AdminCard
        v-for="entry in entries"
        :key="entry.id"
      >
        <template #header>
          <span class="text-sm text-header-600">{{ formatDate(entry.date) }}</span>
        </template>
        <template #header-right>
          <span v-if="formatCompSaison(entry)" class="text-xs font-medium text-primary-700 bg-primary-50 px-2 py-0.5 rounded">
            {{ formatCompSaison(entry) }}
          </span>
        </template>
        <div class="space-y-1 text-sm">
          <div class="font-medium text-header-900" :title="entry.userFonction">
            {{ entry.userIdentite }}
          </div>
          <div class="text-header-700">{{ entry.action }}</div>
          <div v-if="entry.journal" class="text-header-500">{{ entry.journal }}</div>
          <div v-if="entry.journee || entry.match" class="text-xs text-header-400">
            <span v-if="entry.journee">{{ t('journal.table.gameday') }}: {{ entry.journee }}</span>
            <span v-if="entry.journee && entry.match"> · </span>
            <span v-if="entry.match">{{ t('journal.table.match') }}: {{ entry.match }}</span>
          </div>
        </div>
      </AdminCard>
    </AdminCardList>

    <!-- Pagination -->
    <AdminPagination
      v-if="total > 0"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      :limit="limit"
      :limit-options="[25, 50, 100, 200]"
      :showing-text="t('journal.pagination.showing')"
      :items-per-page-text="t('journal.pagination.items_per_page')"
      @update:page="page = $event"
      @update:limit="limit = $event; page = 1"
    />

    <AdminScrollToTop />
  </div>
</template>
