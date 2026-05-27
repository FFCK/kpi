<script setup lang="ts">
import type {
  AthleteSearchResult,
  AthleteSearchFilters,
  AthleteDetail,
  AthleteParticipations,
} from '~/types/athletes'
import type { RegionalCommittee, DepartmentalCommittee, ClubSearchResult } from '~/types/clubs'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t, locale } = useI18n()
const route = useRoute()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()

const canEdit = computed(() => authStore.hasProfile(2))

// ── Search state ──
const search = ref('')
const searchResults = ref<AthleteSearchResult[]>([])
const searchLoading = ref(false)
const searchOpen = ref(false)
const searchRef = ref<HTMLElement | null>(null)
let searchTimer: ReturnType<typeof setTimeout> | null = null

// ── Filters ──
const filtersOpen = ref(false)
const filters = ref<AthleteSearchFilters>({
  regionCode: '',
  deptCode: '',
  clubCode: '',
  sexe: '',
  arbNiveau: '',
})

const regions = ref<RegionalCommittee[]>([])
const depts = ref<DepartmentalCommittee[]>([])
const allDepts = ref<DepartmentalCommittee[]>([])

// Club filter autocomplete
const clubSearch = ref('')
const clubResults = ref<ClubSearchResult[]>([])
const clubLoading = ref(false)
const clubOpen = ref(false)
const clubFilterRef = ref<HTMLElement | null>(null)
let clubTimer: ReturnType<typeof setTimeout> | null = null
const selectedClubLabel = ref('')

const ARB_NIVEAUX = ['Reg', 'IR', 'Nat', 'Int', 'OTM', 'JO']

const hasActiveFilters = computed(() =>
  filters.value.regionCode !== ''
  || filters.value.deptCode !== ''
  || filters.value.clubCode !== ''
  || filters.value.sexe !== ''
  || filters.value.arbNiveau !== ''
)

// Filtered depts based on selected region (cascade)
const availableDepts = computed(() =>
  filters.value.regionCode
    ? allDepts.value.filter(d => d.codeComiteReg === filters.value.regionCode)
    : allDepts.value
)

function onRegionChange() {
  // Reset dept and club when region changes
  filters.value.deptCode = ''
  filters.value.clubCode = ''
  clubSearch.value = ''
  selectedClubLabel.value = ''
  triggerSearch()
}

function onDeptChange() {
  // Reset club when dept changes
  filters.value.clubCode = ''
  clubSearch.value = ''
  selectedClubLabel.value = ''
  triggerSearch()
}

function onFilterChange() {
  triggerSearch()
}

function resetFilters() {
  filters.value = { regionCode: '', deptCode: '', clubCode: '', sexe: '', arbNiveau: '' }
  clubSearch.value = ''
  selectedClubLabel.value = ''
  triggerSearch()
}

// Club autocomplete in filter panel
function onClubSearchInput() {
  if (clubTimer) clearTimeout(clubTimer)
  const q = clubSearch.value.trim()
  if (q.length < 2 && !filters.value.deptCode && !filters.value.regionCode) {
    clubResults.value = []
    clubOpen.value = false
    return
  }
  clubTimer = setTimeout(async () => {
    clubLoading.value = true
    try {
      const params: Record<string, string> = { limit: '30' }
      if (q.length >= 2) params.q = q
      if (filters.value.deptCode) params.dept_code = filters.value.deptCode
      else if (filters.value.regionCode) params.region_code = filters.value.regionCode
      const results = await api.get<ClubSearchResult[]>('/admin/clubs/search-all', params)
      clubResults.value = results
      clubOpen.value = results.length > 0
    } catch {
      clubResults.value = []
    } finally {
      clubLoading.value = false
    }
  }, 300)
}

function selectClub(club: ClubSearchResult) {
  filters.value.clubCode = club.code
  selectedClubLabel.value = `${club.code} ${club.libelle}`
  clubSearch.value = selectedClubLabel.value
  clubOpen.value = false
  triggerSearch()
}

function clearClubFilter() {
  filters.value.clubCode = ''
  clubSearch.value = ''
  selectedClubLabel.value = ''
  clubResults.value = []
  triggerSearch()
}

// ── Athlete detail ──
const athlete = ref<AthleteDetail | null>(null)
const athleteLoading = ref(false)

// ── Participations ──
const participations = ref<AthleteParticipations | null>(null)
const participationsLoading = ref(false)
const participationsSeason = ref('')
const activeTab = ref<'presence' | 'officiels' | 'matchs'>('presence')

// ── Edit modal ──
const editModalOpen = ref(false)

// ── Date formatter ──
function formatDate(dateStr: string | null): string {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

function formatDateShort(dateStr: string | null): string {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
      day: '2-digit',
      month: '2-digit',
    })
  } catch {
    return dateStr
  }
}

// ── Search autocomplete ──
function buildSearchParams(): Record<string, string | number> {
  const q = search.value.trim()
  const params: Record<string, string | number> = { limit: 20 }
  if (q.length >= 2) params.q = q
  if (filters.value.regionCode) params.region_code = filters.value.regionCode
  if (filters.value.deptCode) params.dept_code = filters.value.deptCode
  if (filters.value.clubCode) params.club_code = filters.value.clubCode
  if (filters.value.sexe) params.sexe = filters.value.sexe
  if (filters.value.arbNiveau) params.arb_niveau = filters.value.arbNiveau
  return params
}

function triggerSearch() {
  if (searchTimer) clearTimeout(searchTimer)
  const q = search.value.trim()
  if (q.length < 2 && !hasActiveFilters.value) {
    searchResults.value = []
    searchOpen.value = false
    return
  }
  searchTimer = setTimeout(async () => {
    searchLoading.value = true
    try {
      const results = await api.get<AthleteSearchResult[]>('/admin/athletes/search', buildSearchParams())
      searchResults.value = results
      searchOpen.value = results.length > 0
    } catch {
      searchResults.value = []
    } finally {
      searchLoading.value = false
    }
  }, 300)
}

function onSearchInput() {
  triggerSearch()
}

function clearSearch() {
  search.value = ''
  searchResults.value = []
  searchOpen.value = false
}

async function selectAthlete(result: AthleteSearchResult) {
  searchOpen.value = false
  search.value = result.label
  await loadAthlete(result.matric)
}

// ── Load athlete profile ──
async function loadAthlete(matric: number) {
  athleteLoading.value = true
  athlete.value = null
  participations.value = null
  try {
    athlete.value = await api.get<AthleteDetail>(`/admin/athletes/${matric}`)

    // Default season from work context, fallback to athlete's origin
    participationsSeason.value = workContext.season || athlete.value.origine || ''

    if (participationsSeason.value) {
      await loadParticipations()
    }
  } catch {
    // useApi shows toast
  } finally {
    athleteLoading.value = false
  }
}

// ── Load participations ──
async function loadParticipations() {
  if (!athlete.value || !participationsSeason.value) return
  participationsLoading.value = true
  try {
    participations.value = await api.get<AthleteParticipations>(
      `/admin/athletes/${athlete.value.matric}/participations`,
      { season: participationsSeason.value }
    )
  } catch {
    // useApi shows toast
  } finally {
    participationsLoading.value = false
  }
}

// Watch season change to reload participations
watch(participationsSeason, () => {
  if (athlete.value && participationsSeason.value) {
    loadParticipations()
  }
})

// ── Edit modal handlers ──
function openEditModal() {
  if (!athlete.value?.editable || !canEdit.value) return
  editModalOpen.value = true
}

async function onAthleteSaved() {
  if (athlete.value) {
    await loadAthlete(athlete.value.matric)
  }
}

// ── Close dropdowns on outside click ──
function handleGlobalClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (searchRef.value && !searchRef.value.contains(target)) {
    searchOpen.value = false
  }
  if (clubFilterRef.value && !clubFilterRef.value.contains(target)) {
    clubOpen.value = false
  }
}

onMounted(async () => {
  await workContext.initContext()
  participationsSeason.value = workContext.season || ''
  document.addEventListener('click', handleGlobalClick)

  // Load regions and depts for filter panel
  try {
    const [r, d] = await Promise.all([
      api.get<RegionalCommittee[]>('/admin/regional-committees'),
      api.get<DepartmentalCommittee[]>('/admin/departmental-committees'),
    ])
    regions.value = r
    allDepts.value = d
    depts.value = d
  } catch { /* non-blocking */ }

  // Auto-load athlete from query param ?matric=
  const matric = Number(route.query.matric)
  if (matric) {
    await loadAthlete(matric)
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick)
})
</script>

<template>
  <div>
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-header-900 mb-6">
      {{ t('athletes.title') }}
    </h1>

    <!-- ═══ Filter Panel ═══ -->
    <div class="mb-4">
      <button
        type="button"
        class="flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-lg border transition-colors"
        :class="hasActiveFilters
          ? 'border-primary-400 bg-primary-50 text-primary-700'
          : 'border-header-300 text-header-600 hover:bg-header-50'"
        @click="filtersOpen = !filtersOpen"
      >
        <UIcon name="i-heroicons-funnel" class="w-4 h-4" />
        {{ t('athletes.filters.toggle') }}
        <span v-if="hasActiveFilters" class="ml-1 w-2 h-2 rounded-full bg-primary-500" />
        <UIcon :name="filtersOpen ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'" class="w-3 h-3 ml-1" />
      </button>

      <div v-if="filtersOpen" class="mt-3 bg-white border border-header-200 rounded-lg p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">

          <!-- Région -->
          <div>
            <label class="block text-xs font-medium text-header-600 mb-1">{{ t('athletes.filters.region') }}</label>
            <select
              v-model="filters.regionCode"
              class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="onRegionChange"
            >
              <option value="">{{ t('athletes.filters.region_placeholder') }}</option>
              <option v-for="r in regions" :key="r.code" :value="r.code">{{ r.code }} {{ r.libelle }}</option>
            </select>
          </div>

          <!-- Comité dép. (cascade région) -->
          <div>
            <label class="block text-xs font-medium text-header-600 mb-1">{{ t('athletes.filters.dept') }}</label>
            <select
              v-model="filters.deptCode"
              class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="onDeptChange"
            >
              <option value="">{{ t('athletes.filters.dept_placeholder') }}</option>
              <option v-for="d in availableDepts" :key="d.code" :value="d.code">{{ d.code }} {{ d.libelle }}</option>
            </select>
          </div>

          <!-- Club (cascade dept/région) -->
          <div ref="clubFilterRef" class="relative">
            <label class="block text-xs font-medium text-header-600 mb-1">{{ t('athletes.filters.club') }}</label>
            <div class="relative">
              <input
                v-model="clubSearch"
                type="text"
                :placeholder="t('athletes.filters.club_placeholder')"
                class="w-full px-2 py-1.5 pr-7 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="filters.clubCode ? 'border-primary-400 bg-primary-50' : ''"
                @input="onClubSearchInput"
                @focus="onClubSearchInput"
              >
              <UIcon v-if="clubLoading" name="i-heroicons-arrow-path" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400 animate-spin" />
              <button
                v-else-if="filters.clubCode"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
                @click="clearClubFilter"
              >
                <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
              </button>
            </div>
            <div
              v-if="clubOpen && clubResults.length > 0"
              class="absolute z-30 mt-1 w-full max-h-48 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
            >
              <button
                v-for="club in clubResults"
                :key="club.code"
                class="w-full px-3 py-2 text-left text-xs text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none"
                @click="selectClub(club)"
              >
                <span class="font-mono text-header-500">{{ club.code }}</span>
                <span class="ml-2">{{ club.libelle }}</span>
              </button>
            </div>
          </div>

          <!-- Sexe -->
          <div>
            <label class="block text-xs font-medium text-header-600 mb-1">{{ t('athletes.filters.sexe') }}</label>
            <select
              v-model="filters.sexe"
              class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="onFilterChange"
            >
              <option value="">{{ t('athletes.filters.sexe_all') }}</option>
              <option value="M">{{ t('athletes.filters.sexe_m') }}</option>
              <option value="F">{{ t('athletes.filters.sexe_f') }}</option>
            </select>
          </div>

          <!-- Niveau arbitrage -->
          <div>
            <label class="block text-xs font-medium text-header-600 mb-1">{{ t('athletes.filters.arb_niveau') }}</label>
            <select
              v-model="filters.arbNiveau"
              class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="onFilterChange"
            >
              <option value="">{{ t('athletes.filters.arb_all') }}</option>
              <option v-for="n in ARB_NIVEAUX" :key="n" :value="n">
                {{ t(`athletes.arbitrage.qualification.${n}`, n) }}
              </option>
            </select>
          </div>

          <!-- Reset -->
          <div class="flex items-end">
            <button
              v-if="hasActiveFilters"
              type="button"
              class="w-full px-3 py-1.5 text-sm font-medium text-danger-600 border border-danger-200 rounded-lg hover:bg-danger-50 transition-colors"
              @click="resetFilters"
            >
              {{ t('athletes.filters.reset') }}
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- ═══ Search Bar ═══ -->
    <div ref="searchRef" class="mb-6 relative">
      <div class="relative">
        <input
          v-model="search"
          type="text"
          :placeholder="t('athletes.search_placeholder')"
          class="w-full px-4 py-3 pl-10 pr-10 border border-header-300 text-primary-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          @input="onSearchInput"
          @focus="onSearchInput"
        >
        <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-header-400" />
        <UIcon v-if="searchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-header-400 animate-spin" />
        <button
          v-else-if="search"
          type="button"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
          @click="clearSearch"
        >
          <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
        </button>
      </div>

      <!-- Search results dropdown -->
      <div
        v-if="searchOpen && searchResults.length > 0"
        class="absolute z-20 mt-1 w-full max-h-72 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
      >
        <button
          v-for="result in searchResults"
          :key="result.matric"
          class="w-full px-4 py-2.5 text-left text-sm text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none flex items-center gap-3"
          @click="selectAthlete(result)"
        >
          <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded shrink-0">{{ result.matric }}</span>
          <span class="font-medium">{{ formatNom(result.nom) }} {{ formatPrenom(result.prenom) }}</span>
          <span v-if="result.club" class="text-header-600 text-xs truncate">— {{ result.club }}</span>
        </button>
      </div>
    </div>

    <!-- ═══ Loading skeleton ═══ -->
    <div v-if="athleteLoading" class="space-y-4">
      <div class="bg-white border border-header-200 rounded-lg p-6 animate-pulse">
        <div class="h-6 bg-header-200 rounded w-64 mb-4" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="i in 4" :key="i" class="h-24 bg-header-100 rounded" />
        </div>
      </div>
    </div>

    <!-- ═══ No athlete selected ═══ -->
    <div
      v-else-if="!athlete"
      class="bg-white border border-header-200 rounded-lg p-12 text-center"
    >
      <UIcon name="i-heroicons-user-group" class="w-12 h-12 text-header-300 mx-auto mb-4" />
      <p class="text-header-500 text-sm">{{ t('athletes.no_athlete_selected') }}</p>
    </div>

    <!-- ═══ Athlete Profile ═══ -->
    <template v-else>
      <div class="bg-white border border-header-200 rounded-lg p-3 mb-3">
        <!-- Identity header -->
        <div class="mb-5">
          <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-header-900">
              {{ t('athletes.licence') }}{{ athlete.matric }}
              <span class="ml-3">{{ formatNom(athlete.nom) }} {{ formatPrenom(athlete.prenom) }}</span>
              <span class="text-header-500 font-normal ml-2">({{ athlete.sexe }})</span>
              <span v-if="athlete.naissance" class="text-header-500 font-normal ml-2">
                {{ formatDate(athlete.naissance) }}
              </span>
            </h2>
            <button
              v-if="canEdit && athlete.editable"
              class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-2 shrink-0"
              @click="openEditModal"
            >
              <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
              {{ t('athletes.edit.submit') }}
            </button>
          </div>
        </div>

        <!-- Info cards grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
          <!-- Club -->
          <div class="bg-header-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('athletes.club.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div v-if="athlete.club.code" class="font-medium">
                {{ athlete.club.code }} {{ athlete.club.libelle }}
              </div>
              <div v-if="athlete.comiteDep.code" class="text-header-500 text-xs">
                {{ athlete.comiteDep.code }} {{ athlete.comiteDep.libelle }}
              </div>
              <div v-if="athlete.comiteReg.code" class="text-header-500 text-xs">
                {{ athlete.comiteReg.code }} {{ athlete.comiteReg.libelle }}
              </div>
            </div>
          </div>

          <!-- Pagaie -->
          <div class="bg-header-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('athletes.pagaie.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div>
                <span class="text-header-500">{{ t('athletes.pagaie.eau_vive') }} :</span>
                {{ athlete.pagaie.eauVive || '-' }}
              </div>
              <div>
                <span class="text-header-500">{{ t('athletes.pagaie.mer') }} :</span>
                {{ athlete.pagaie.mer || '-' }}
              </div>
              <div>
                <span class="text-header-500">{{ t('athletes.pagaie.eau_calme') }} :</span>
                {{ athlete.pagaie.eauCalme || '-' }}
              </div>
            </div>
          </div>

          <!-- Certificats -->
          <div class="bg-header-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('athletes.certificats.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div>
                <span class="text-header-500">{{ t('athletes.certificats.aps') }} :</span>
                <span :class="athlete.certificats.aps === 'OUI' ? 'text-success-500 font-medium' : 'text-danger-500'">
                  {{ athlete.certificats.aps || '-' }}
                </span>
              </div>
              <div>
                <span class="text-header-500">{{ t('athletes.certificats.ck') }} :</span>
                <span :class="athlete.certificats.ck === 'OUI' ? 'text-success-500 font-medium' : 'text-danger-500'">
                  {{ athlete.certificats.ck || '-' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Licence -->
          <div class="bg-header-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('athletes.licence_bloc.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div v-if="athlete.icf" class="text-header-500 text-xs">
                {{ t('athletes.icf_number') }}{{ athlete.icf }}
              </div>
              <div v-if="athlete.typeLicence">
                {{ athlete.typeLicence }}
              </div>
              <div class="text-header-800 font-semibold">
                {{ t('athletes.club.last_season') }} : {{ athlete.origine || '-' }}
              </div>
              <div v-if="athlete.categorieAge">
                <span class="text-header-500">{{ t('athletes.licence_bloc.categorie') }} :</span>
                {{ athlete.categorieAge.code }}
                <span class="text-header-400 text-xs">({{ athlete.categorieAge.libelle }})</span>
              </div>
              <div v-if="athlete.surclassement" class="mt-1 pt-1 border-t border-header-200">
                <span class="text-header-500">{{ t('athletes.licence_bloc.surclassement') }} :</span>
                <span class="font-medium">{{ athlete.surclassement.cat }}</span>
                <span class="text-header-400 text-xs ml-1">({{ formatDate(athlete.surclassement.date) }})</span>
              </div>
            </div>
          </div>

          <!-- Arbitrage -->
          <div class="bg-header-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('athletes.arbitrage.title') }}</h3>
            <div v-if="athlete.arbitrage.qualification" class="space-y-1 text-sm">
              <div>
                <span class="text-header-500">{{ t('athletes.arbitrage.niveau') }} :</span>
                {{ t(`athletes.arbitrage.qualification.${athlete.arbitrage.qualification}`, athlete.arbitrage.qualification) }}
                {{ athlete.arbitrage.niveau || '' }}
              </div>
              <div v-if="athlete.arbitrage.saison">
                <span class="text-header-500">{{ t('athletes.arbitrage.saison') }} :</span>
                {{ athlete.arbitrage.saison }}
              </div>
              <div v-if="athlete.arbitrage.livret">
                <span class="text-header-500">{{ t('athletes.arbitrage.livret') }} :</span>
                {{ athlete.arbitrage.livret }}
              </div>
            </div>
            <p v-else class="text-sm text-header-400">-</p>
          </div>
        </div>

      </div>

      <!-- ═══ Participations ═══ -->
      <div class="bg-white border border-header-200 rounded-lg p-6">
        <!-- Season selector -->
        <div class="flex flex-wrap items-center gap-4 mb-4">
          <label class="text-sm font-medium text-header-700">{{ t('athletes.participations.season') }} :</label>
          <select
            v-model="participationsSeason"
            class="px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="s in workContext.seasons" :key="s.code" :value="s.code">
              {{ s.code }}
            </option>
          </select>
        </div>

        <!-- Tabs -->
        <div class="border-b border-header-200 mb-4">
          <nav class="flex gap-4 -mb-px">
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'presence'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300'"
              @click="activeTab = 'presence'"
            >
              {{ t('athletes.participations.presence.title') }}
              <span v-if="participations?.presences.length" class="ml-1 text-xs bg-header-100 text-header-600 px-1.5 py-0.5 rounded-full">
                {{ participations.presences.length }}
              </span>
            </button>
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'officiels'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300'"
              @click="activeTab = 'officiels'"
            >
              {{ t('athletes.participations.officiels.title') }}
              <span v-if="participations?.officiels.length" class="ml-1 text-xs bg-header-100 text-header-600 px-1.5 py-0.5 rounded-full">
                {{ participations.officiels.length }}
              </span>
            </button>
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'matchs'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300'"
              @click="activeTab = 'matchs'"
            >
              {{ t('athletes.participations.matchs.title') }}
              <span v-if="participations?.matchs.length" class="ml-1 text-xs bg-header-100 text-header-600 px-1.5 py-0.5 rounded-full">
                {{ participations.matchs.length }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Loading -->
        <div v-if="participationsLoading" class="flex items-center justify-center py-8">
          <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 text-header-400 animate-spin" />
        </div>

        <!-- ── Tab: Presence ── -->
        <div v-else-if="activeTab === 'presence'">
          <div v-if="!participations?.presences.length" class="text-center py-8 text-sm text-header-400 italic">
            {{ t('athletes.participations.presence.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-header-50 text-left">
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.presence.competition') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.presence.equipe') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.presence.numero') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.presence.role') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.presence.categorie') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-header-100">
                <tr v-for="(p, i) in participations.presences" :key="i" class="hover:bg-header-50">
                  <td class="px-3 py-2 font-mono text-xs">{{ p.competition }}</td>
                  <td class="px-3 py-2">{{ p.equipe }}</td>
                  <td class="px-3 py-2 text-center">{{ p.numero ?? '' }}</td>
                  <td class="px-3 py-2 text-center">{{ t(`athletes.roles.${p.capitaine}`, p.capitaine) }}</td>
                  <td class="px-3 py-2 text-header-500">{{ p.categorie }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── Tab: Officiels ── -->
        <div v-else-if="activeTab === 'officiels'">
          <div v-if="!participations?.officiels.length" class="text-center py-8 text-sm text-header-400 italic">
            {{ t('athletes.participations.officiels.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-header-50 text-left">
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.officiels.date') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.officiels.heure') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600">{{ t('athletes.participations.officiels.competition') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.match') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.arb_principal') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.arb_secondaire') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.secretaire') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.chronometreur') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.timekeeper') }}</th>
                  <th class="px-3 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.officiels.ligne') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-header-100">
                <tr
                  v-for="o in participations.officiels"
                  :key="o.matchId"
                  class="hover:bg-header-50"
                  :class="{ 'italic text-header-400': !o.scoreValide }"
                >
                  <td class="px-3 py-2">{{ formatDateShort(o.date) }}</td>
                  <td class="px-3 py-2">{{ o.heure }}</td>
                  <td class="px-3 py-2 font-mono text-xs">{{ o.competition }}</td>
                  <td class="px-3 py-2 text-center">{{ o.matchNumero }}</td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.arbitrePrincipal" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-success-500 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.arbitreSecondaire" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-success-500 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.secretaire" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-primary-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.chronometreur" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-primary-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.timekeeper" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-primary-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.ligne" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-primary-600 inline-block" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── Tab: Matchs ── -->
        <div v-else-if="activeTab === 'matchs'">
          <div v-if="!participations?.matchs.length" class="text-center py-8 text-sm text-header-400 italic">
            {{ t('athletes.participations.matchs.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-header-50 text-left">
                  <th class="px-2 py-2 font-medium text-header-600">{{ t('athletes.participations.matchs.date') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600">{{ t('athletes.participations.matchs.competition') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.matchs.match') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600">{{ t('athletes.participations.matchs.equipes') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.matchs.score') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.matchs.numero') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center">{{ t('athletes.participations.matchs.role') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-header-100">{{ t('athletes.participations.matchs.buts') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-success-50">{{ t('athletes.participations.matchs.vert') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-warning-50">{{ t('athletes.participations.matchs.jaune') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-danger-50">{{ t('athletes.participations.matchs.rouge') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-danger-50">{{ t('athletes.participations.matchs.rouge_def') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-header-100">{{ t('athletes.participations.matchs.tir') }}</th>
                  <th class="px-2 py-2 font-medium text-header-600 text-center bg-header-100">{{ t('athletes.participations.matchs.arret') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-header-100">
                <tr
                  v-for="m in participations.matchs"
                  :key="m.matchId"
                  class="hover:bg-header-50"
                  :class="{ 'italic text-header-400': !m.scoreValide }"
                >
                  <td class="px-2 py-2 whitespace-nowrap">{{ formatDateShort(m.date) }}</td>
                  <td class="px-2 py-2 font-mono text-xs">{{ m.competition }}</td>
                  <td class="px-2 py-2 text-center">{{ m.matchNumero }}</td>
                  <td class="px-2 py-2 whitespace-nowrap">
                    <span :class="{ 'font-bold': m.equipe === 'A' }">{{ m.equipeA }}</span>
                    <span class="text-header-400 mx-1">-</span>
                    <span :class="{ 'font-bold': m.equipe === 'B' }">{{ m.equipeB }}</span>
                  </td>
                  <td class="px-2 py-2 text-center whitespace-nowrap">
                    <template v-if="m.scoreValide">
                      (<span :class="{ 'font-bold': m.equipe === 'A' }">{{ m.scoreA }}</span>-<span :class="{ 'font-bold': m.equipe === 'B' }">{{ m.scoreB }}</span>)
                    </template>
                  </td>
                  <td class="px-2 py-2 text-center">{{ m.numero ?? '' }}</td>
                  <td class="px-2 py-2 text-center">{{ t(`athletes.roles.${m.capitaine}`, m.capitaine) }}</td>
                  <td class="px-2 py-2 text-center bg-header-50" :class="{ 'font-bold': m.buts > 0 }">
                    {{ m.buts || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.verts > 0 ? 'bg-success-100 font-medium' : ''">
                    {{ m.verts || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.jaunes > 0 ? 'bg-warning-100 font-medium' : ''">
                    {{ m.jaunes || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.rouges > 0 ? 'bg-danger-100 font-medium text-danger-700' : ''">
                    {{ m.rouges || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.rougesDefinitifs > 0 ? 'bg-danger-200 font-bold text-danger-800' : ''">
                    {{ m.rougesDefinitifs || '' }}
                  </td>
                  <td class="px-2 py-2 text-center bg-header-50">
                    {{ m.tirs || '' }}
                  </td>
                  <td class="px-2 py-2 text-center bg-header-50">
                    {{ m.arrets || '' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <!-- ═══ Edit Modal ═══ -->
    <AdminAthleteEditModal
      :open="editModalOpen"
      :athlete="athlete"
      @close="editModalOpen = false"
      @saved="onAthleteSaved"
    />

    <AdminScrollToTop />
  </div>
</template>
