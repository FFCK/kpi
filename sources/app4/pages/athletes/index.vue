<script setup lang="ts">
import type {
  AthleteSearchResult,
  AthleteDetail,
  AthleteParticipations,
} from '~/types/athletes'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t, locale } = useI18n()
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
function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  const q = search.value.trim()
  if (q.length < 2) {
    searchResults.value = []
    searchOpen.value = false
    return
  }
  searchTimer = setTimeout(async () => {
    searchLoading.value = true
    try {
      const results = await api.get<AthleteSearchResult[]>('/admin/athletes/search', { q, limit: 20 })
      searchResults.value = results
      searchOpen.value = results.length > 0
    } catch {
      searchResults.value = []
    } finally {
      searchLoading.value = false
    }
  }, 300)
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

// ── Close dropdown on outside click ──
function handleGlobalClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (searchRef.value && !searchRef.value.contains(target)) {
    searchOpen.value = false
  }
}

onMounted(async () => {
  await workContext.initContext()
  participationsSeason.value = workContext.season || ''
  document.addEventListener('click', handleGlobalClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick)
})
</script>

<template>
  <div>
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-gray-900 mb-6">
      {{ t('athletes.title') }}
    </h1>

    <!-- ═══ Search Bar ═══ -->
    <div ref="searchRef" class="mb-6 relative">
      <div class="relative">
        <input
          v-model="search"
          type="text"
          :placeholder="t('athletes.search_placeholder')"
          class="w-full px-4 py-3 pl-10 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          @input="onSearchInput"
          @focus="onSearchInput"
        >
        <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <UIcon v-if="searchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 animate-spin" />
        <button
          v-else-if="search"
          type="button"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          @click="clearSearch"
        >
          <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
        </button>
      </div>

      <!-- Search results dropdown -->
      <div
        v-if="searchOpen && searchResults.length > 0"
        class="absolute z-20 mt-1 w-full max-h-72 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg"
      >
        <button
          v-for="result in searchResults"
          :key="result.matric"
          class="w-full px-4 py-2.5 text-left text-sm text-gray-900 hover:bg-blue-50 focus:bg-blue-100 focus:outline-none flex items-center gap-3"
          @click="selectAthlete(result)"
        >
          <span class="font-mono text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded shrink-0">{{ result.matric }}</span>
          <span class="font-medium">{{ result.nom }} {{ result.prenom }}</span>
          <span v-if="result.club" class="text-gray-600 text-xs truncate">— {{ result.club }}</span>
        </button>
      </div>
    </div>

    <!-- ═══ Loading skeleton ═══ -->
    <div v-if="athleteLoading" class="space-y-4">
      <div class="bg-white border border-gray-200 rounded-lg p-6 animate-pulse">
        <div class="h-6 bg-gray-200 rounded w-64 mb-4" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="i in 4" :key="i" class="h-24 bg-gray-100 rounded" />
        </div>
      </div>
    </div>

    <!-- ═══ No athlete selected ═══ -->
    <div
      v-else-if="!athlete"
      class="bg-white border border-gray-200 rounded-lg p-12 text-center"
    >
      <UIcon name="i-heroicons-user-group" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
      <p class="text-gray-500 text-sm">{{ t('athletes.no_athlete_selected') }}</p>
    </div>

    <!-- ═══ Athlete Profile ═══ -->
    <template v-else>
      <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <!-- Identity header -->
        <div class="mb-5">
          <h2 class="text-lg font-bold text-gray-900">
            {{ t('athletes.licence') }}{{ athlete.matric }}
            <span class="ml-3">{{ athlete.nom }} {{ athlete.prenom }}</span>
            <span class="text-gray-500 font-normal ml-2">({{ athlete.sexe }})</span>
            <span v-if="athlete.naissance" class="text-gray-500 font-normal ml-2">
              {{ formatDate(athlete.naissance) }}
            </span>
          </h2>
          <div v-if="athlete.icf || athlete.surclassement" class="flex flex-wrap gap-4 mt-1 text-sm text-gray-600">
            <span v-if="athlete.icf">
              {{ t('athletes.icf_number') }}{{ athlete.icf }}
            </span>
            <span v-if="athlete.surclassement">
              {{ t('athletes.surclasse') }} {{ formatDate(athlete.surclassement) }}
            </span>
          </div>
        </div>

        <!-- Info cards grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
          <!-- Club -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('athletes.club.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div v-if="athlete.club.code" class="font-medium">
                {{ athlete.club.code }} {{ athlete.club.libelle }}
              </div>
              <div v-if="athlete.comiteDep.code" class="text-gray-500 text-xs">
                {{ athlete.comiteDep.code }} {{ athlete.comiteDep.libelle }}
              </div>
              <div v-if="athlete.comiteReg.code" class="text-gray-500 text-xs">
                {{ athlete.comiteReg.code }} {{ athlete.comiteReg.libelle }}
              </div>
              <div class="text-gray-500 text-xs mt-2">
                {{ t('athletes.club.last_season') }} : {{ athlete.origine || '-' }}
              </div>
            </div>
          </div>

          <!-- Pagaie -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('athletes.pagaie.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div>
                <span class="text-gray-500">{{ t('athletes.pagaie.eau_vive') }} :</span>
                {{ athlete.pagaie.eauVive || '-' }}
              </div>
              <div>
                <span class="text-gray-500">{{ t('athletes.pagaie.mer') }} :</span>
                {{ athlete.pagaie.mer || '-' }}
              </div>
              <div>
                <span class="text-gray-500">{{ t('athletes.pagaie.eau_calme') }} :</span>
                {{ athlete.pagaie.eauCalme || '-' }}
              </div>
            </div>
          </div>

          <!-- Certificats -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('athletes.certificats.title') }}</h3>
            <div class="space-y-1 text-sm">
              <div>
                <span class="text-gray-500">{{ t('athletes.certificats.aps') }} :</span>
                <span :class="athlete.certificats.aps === 'OUI' ? 'text-green-600 font-medium' : 'text-red-500'">
                  {{ athlete.certificats.aps || '-' }}
                </span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('athletes.certificats.ck') }} :</span>
                <span :class="athlete.certificats.ck === 'OUI' ? 'text-green-600 font-medium' : 'text-red-500'">
                  {{ athlete.certificats.ck || '-' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Arbitrage -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('athletes.arbitrage.title') }}</h3>
            <div v-if="athlete.arbitrage.qualification" class="space-y-1 text-sm">
              <div>
                <span class="text-gray-500">{{ t('athletes.arbitrage.niveau') }} :</span>
                {{ t(`athletes.arbitrage.qualification.${athlete.arbitrage.qualification}`, athlete.arbitrage.qualification) }}
                {{ athlete.arbitrage.niveau || '' }}
              </div>
              <div v-if="athlete.arbitrage.saison">
                <span class="text-gray-500">{{ t('athletes.arbitrage.saison') }} :</span>
                {{ athlete.arbitrage.saison }}
              </div>
              <div v-if="athlete.arbitrage.livret">
                <span class="text-gray-500">{{ t('athletes.arbitrage.livret') }} :</span>
                {{ athlete.arbitrage.livret }}
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">-</p>
          </div>
        </div>

        <!-- Edit button -->
        <div v-if="canEdit && athlete.editable" class="flex justify-end">
          <button
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 flex items-center gap-2"
            @click="openEditModal"
          >
            <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
            {{ t('athletes.edit.submit') }}
          </button>
        </div>
      </div>

      <!-- ═══ Participations ═══ -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <!-- Season selector -->
        <div class="flex flex-wrap items-center gap-4 mb-4">
          <label class="text-sm font-medium text-gray-700">{{ t('athletes.participations.season') }} :</label>
          <select
            v-model="participationsSeason"
            class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option v-for="s in workContext.seasons" :key="s.code" :value="s.code">
              {{ s.code }}
            </option>
          </select>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4">
          <nav class="flex gap-4 -mb-px">
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'presence'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="activeTab = 'presence'"
            >
              {{ t('athletes.participations.presence.title') }}
              <span v-if="participations?.presences.length" class="ml-1 text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">
                {{ participations.presences.length }}
              </span>
            </button>
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'officiels'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="activeTab = 'officiels'"
            >
              {{ t('athletes.participations.officiels.title') }}
              <span v-if="participations?.officiels.length" class="ml-1 text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">
                {{ participations.officiels.length }}
              </span>
            </button>
            <button
              class="py-2 px-1 text-sm font-medium border-b-2 transition-colors"
              :class="activeTab === 'matchs'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="activeTab = 'matchs'"
            >
              {{ t('athletes.participations.matchs.title') }}
              <span v-if="participations?.matchs.length" class="ml-1 text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">
                {{ participations.matchs.length }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Loading -->
        <div v-if="participationsLoading" class="flex items-center justify-center py-8">
          <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 text-gray-400 animate-spin" />
        </div>

        <!-- ── Tab: Presence ── -->
        <div v-else-if="activeTab === 'presence'">
          <div v-if="!participations?.presences.length" class="text-center py-8 text-sm text-gray-400 italic">
            {{ t('athletes.participations.presence.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-left">
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.presence.competition') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.presence.equipe') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.presence.numero') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.presence.role') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.presence.categorie') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(p, i) in participations.presences" :key="i" class="hover:bg-gray-50">
                  <td class="px-3 py-2 font-mono text-xs">{{ p.competition }}</td>
                  <td class="px-3 py-2">{{ p.equipe }}</td>
                  <td class="px-3 py-2 text-center">{{ p.numero ?? '' }}</td>
                  <td class="px-3 py-2 text-center">{{ t(`athletes.roles.${p.capitaine}`, p.capitaine) }}</td>
                  <td class="px-3 py-2 text-gray-500">{{ p.categorie }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── Tab: Officiels ── -->
        <div v-else-if="activeTab === 'officiels'">
          <div v-if="!participations?.officiels.length" class="text-center py-8 text-sm text-gray-400 italic">
            {{ t('athletes.participations.officiels.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-left">
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.officiels.date') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.officiels.heure') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600">{{ t('athletes.participations.officiels.competition') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.match') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.arb_principal') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.arb_secondaire') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.secretaire') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.chronometreur') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.timekeeper') }}</th>
                  <th class="px-3 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.officiels.ligne') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="o in participations.officiels"
                  :key="o.matchId"
                  class="hover:bg-gray-50"
                  :class="{ 'italic text-gray-400': !o.scoreValide }"
                >
                  <td class="px-3 py-2">{{ formatDateShort(o.date) }}</td>
                  <td class="px-3 py-2">{{ o.heure }}</td>
                  <td class="px-3 py-2 font-mono text-xs">{{ o.competition }}</td>
                  <td class="px-3 py-2 text-center">{{ o.matchNumero }}</td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.arbitrePrincipal" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-green-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.arbitreSecondaire" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-green-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.secretaire" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-blue-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.chronometreur" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-blue-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.timekeeper" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-blue-600 inline-block" />
                  </td>
                  <td class="px-3 py-2 text-center">
                    <UIcon v-if="o.ligne" name="i-heroicons-check-circle-solid" class="w-4 h-4 text-blue-600 inline-block" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── Tab: Matchs ── -->
        <div v-else-if="activeTab === 'matchs'">
          <div v-if="!participations?.matchs.length" class="text-center py-8 text-sm text-gray-400 italic">
            {{ t('athletes.participations.matchs.empty') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-left">
                  <th class="px-2 py-2 font-medium text-gray-600">{{ t('athletes.participations.matchs.date') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600">{{ t('athletes.participations.matchs.competition') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.matchs.match') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600">{{ t('athletes.participations.matchs.equipes') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.matchs.score') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.matchs.numero') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center">{{ t('athletes.participations.matchs.role') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-gray-100">{{ t('athletes.participations.matchs.buts') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-green-50">{{ t('athletes.participations.matchs.vert') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-yellow-50">{{ t('athletes.participations.matchs.jaune') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-red-50">{{ t('athletes.participations.matchs.rouge') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-red-50">{{ t('athletes.participations.matchs.rouge_def') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-gray-100">{{ t('athletes.participations.matchs.tir') }}</th>
                  <th class="px-2 py-2 font-medium text-gray-600 text-center bg-gray-100">{{ t('athletes.participations.matchs.arret') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="m in participations.matchs"
                  :key="m.matchId"
                  class="hover:bg-gray-50"
                  :class="{ 'italic text-gray-400': !m.scoreValide }"
                >
                  <td class="px-2 py-2 whitespace-nowrap">{{ formatDateShort(m.date) }}</td>
                  <td class="px-2 py-2 font-mono text-xs">{{ m.competition }}</td>
                  <td class="px-2 py-2 text-center">{{ m.matchNumero }}</td>
                  <td class="px-2 py-2 whitespace-nowrap">
                    <span :class="{ 'font-bold': m.equipe === 'A' }">{{ m.equipeA }}</span>
                    <span class="text-gray-400 mx-1">-</span>
                    <span :class="{ 'font-bold': m.equipe === 'B' }">{{ m.equipeB }}</span>
                  </td>
                  <td class="px-2 py-2 text-center whitespace-nowrap">
                    <template v-if="m.scoreValide">
                      (<span :class="{ 'font-bold': m.equipe === 'A' }">{{ m.scoreA }}</span>-<span :class="{ 'font-bold': m.equipe === 'B' }">{{ m.scoreB }}</span>)
                    </template>
                  </td>
                  <td class="px-2 py-2 text-center">{{ m.numero ?? '' }}</td>
                  <td class="px-2 py-2 text-center">{{ t(`athletes.roles.${m.capitaine}`, m.capitaine) }}</td>
                  <td class="px-2 py-2 text-center bg-gray-50" :class="{ 'font-bold': m.buts > 0 }">
                    {{ m.buts || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.verts > 0 ? 'bg-green-100 font-medium' : ''">
                    {{ m.verts || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.jaunes > 0 ? 'bg-yellow-100 font-medium' : ''">
                    {{ m.jaunes || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.rouges > 0 ? 'bg-red-100 font-medium text-red-700' : ''">
                    {{ m.rouges || '' }}
                  </td>
                  <td class="px-2 py-2 text-center" :class="m.rougesDefinitifs > 0 ? 'bg-red-200 font-bold text-red-800' : ''">
                    {{ m.rougesDefinitifs || '' }}
                  </td>
                  <td class="px-2 py-2 text-center bg-gray-50">
                    {{ m.tirs || '' }}
                  </td>
                  <td class="px-2 py-2 text-center bg-gray-50">
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
