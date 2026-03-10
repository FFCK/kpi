<script setup lang="ts">
import type { ClubMapItem, ClubDetail, RegionalCommittee, DepartmentalCommittee, ClubSearchResult, ClubTeam } from '~/types/clubs'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()

const canEdit = computed(() => authStore.hasProfile(2))

// ── Map state ──
const mapRef = ref<InstanceType<any> | null>(null)
const mapClubs = ref<ClubMapItem[]>([])
const mapLoading = ref(false)
const geocodeAddress = ref('')
const geocoding = ref(false)

// ── Club search / update state ──
const clubSearch = ref('')
const clubSearchResults = ref<ClubSearchResult[]>([])
const clubSearchLoading = ref(false)
const clubSearchOpen = ref(false)
let clubSearchTimer: ReturnType<typeof setTimeout> | null = null

const selectedClub = ref<ClubDetail | null>(null)
const selectedClubCode = ref<string | null>(null)
const updateForm = reactive({
  postal: '',
  www: '',
  email: '',
  coord: ''
})
const updating = ref(false)

// ── Club teams ──
const clubTeams = ref<ClubTeam[]>([])
const clubTeamsLoading = ref(false)

// ── Add CD Modal ──
const cdModalOpen = ref(false)
const cdForm = reactive({
  codeComiteReg: '',
  crLibelle: '',
  code: '',
  libelle: ''
})
const crList = ref<RegionalCommittee[]>([])
const crSearch = ref('')
const crSearchOpen = ref(false)
const crSearchLoading = ref(false)
const cdSubmitting = ref(false)
const cdError = ref('')

// ── Add Club Modal ──
const clubModalOpen = ref(false)
const newClubForm = reactive({
  codeComiteDep: '',
  cdLibelle: '',
  code: '',
  libelle: '',
  postal: '',
  www: '',
  email: '',
  coord: '',
  equipe: ''
})
const cdList = ref<DepartmentalCommittee[]>([])
const cdSearch = ref('')
const cdSearchOpen = ref(false)
const cdSearchLoading = ref(false)
const clubSubmitting = ref(false)
const clubError = ref('')

// ── Load map clubs ──
async function loadMapClubs() {
  mapLoading.value = true
  try {
    const response = await api.get<{ clubs: ClubMapItem[] }>('/admin/clubs/map')
    mapClubs.value = response.clubs
  } catch {
    // useApi already shows toast
  } finally {
    mapLoading.value = false
  }
}

// ── Geocode address ──
async function handleGeocode() {
  if (!geocodeAddress.value.trim() || !mapRef.value) return
  geocoding.value = true
  try {
    const result = await mapRef.value.geocode(geocodeAddress.value.trim())
    if (!result) {
      toast.add({
        title: t('clubs.map.geocode_failed'),
        color: 'error',
        duration: 3000
      })
    }
  } catch {
    toast.add({
      title: t('clubs.map.geocode_failed'),
      color: 'error',
      duration: 3000
    })
  } finally {
    geocoding.value = false
  }
}

// ── Club autocomplete search ──
function onClubSearchInput() {
  if (clubSearchTimer) clearTimeout(clubSearchTimer)
  const q = clubSearch.value.trim()
  if (q.length < 2) {
    clubSearchResults.value = []
    clubSearchOpen.value = false
    return
  }
  clubSearchTimer = setTimeout(async () => {
    clubSearchLoading.value = true
    try {
      const results = await api.get<ClubSearchResult[]>('/admin/clubs/search-all', { q, limit: 20 })
      clubSearchResults.value = results
      clubSearchOpen.value = results.length > 0
    } catch {
      clubSearchResults.value = []
    } finally {
      clubSearchLoading.value = false
    }
  }, 300)
}

function clearClubSearch() {
  clubSearch.value = ''
  clubSearchResults.value = []
  clubSearchOpen.value = false
  selectedClub.value = null
  selectedClubCode.value = null
  clubTeams.value = []
}

async function selectClub(club: ClubSearchResult) {
  clubSearchOpen.value = false
  clubSearch.value = `${club.code} - ${club.libelle}`
  selectedClubCode.value = club.code

  try {
    const detail = await api.get<ClubDetail>(`/admin/clubs/${club.code}`)
    selectedClub.value = detail
    updateForm.postal = detail.postal || ''
    updateForm.www = detail.www || ''
    updateForm.email = detail.email || ''
    updateForm.coord = detail.coord || ''

    // Center map on this club
    nextTick(() => {
      mapRef.value?.centerOnClub(club.code)
    })
  } catch {
    // useApi already shows toast
  }

  // Load club teams
  loadClubTeams(club.code)
}

async function loadClubTeams(code: string) {
  clubTeamsLoading.value = true
  clubTeams.value = []
  try {
    const response = await api.get<{ teams: ClubTeam[] }>(`/admin/clubs/${code}/teams`)
    clubTeams.value = response.teams
  } catch {
    // useApi already shows toast
  } finally {
    clubTeamsLoading.value = false
  }
}

// Map marker click → select club
function onMapSelectClub(code: string) {
  const club = mapClubs.value.find(c => c.code === code)
  if (club) {
    selectClub({ code: club.code, libelle: club.libelle, codeComiteDep: '' })
  }
}

// ── Update club ──
async function handleUpdate() {
  if (!selectedClub.value) return
  updating.value = true
  try {
    await api.patch(`/admin/clubs/${selectedClub.value.code}`, {
      postal: updateForm.postal,
      www: updateForm.www,
      email: updateForm.email,
      coord: updateForm.coord
    })
    toast.add({
      title: t('clubs.update.success'),
      color: 'success',
      duration: 3000
    })

    // Update marker on map if coord changed
    if (updateForm.coord !== selectedClub.value.coord) {
      mapRef.value?.updateMarkerPosition(selectedClub.value.code, updateForm.coord)
    }

    // Update local state
    selectedClub.value.postal = updateForm.postal
    selectedClub.value.www = updateForm.www
    selectedClub.value.email = updateForm.email
    selectedClub.value.coord = updateForm.coord
  } catch {
    toast.add({
      title: t('clubs.update.error'),
      color: 'error',
      duration: 3000
    })
  } finally {
    updating.value = false
  }
}

// ── CR autocomplete (for Add CD modal) ──
async function loadRegionalCommittees() {
  if (crList.value.length > 0) return
  crSearchLoading.value = true
  try {
    crList.value = await api.get<RegionalCommittee[]>('/admin/regional-committees')
  } catch {
    // handled by useApi
  } finally {
    crSearchLoading.value = false
  }
}

const filteredCR = computed(() => {
  const q = crSearch.value.toLowerCase().trim()
  if (!q) return crList.value
  return crList.value.filter(cr =>
    cr.libelle.toLowerCase().includes(q) || cr.code.toLowerCase().includes(q)
  )
})

function selectCR(cr: RegionalCommittee) {
  cdForm.codeComiteReg = cr.code
  cdForm.crLibelle = cr.libelle
  crSearch.value = `${cr.code} - ${cr.libelle}`
  crSearchOpen.value = false
}

// ── CD autocomplete (for Add Club modal) ──
async function loadDepartmentalCommittees() {
  if (cdList.value.length > 0) return
  cdSearchLoading.value = true
  try {
    cdList.value = await api.get<DepartmentalCommittee[]>('/admin/departmental-committees')
  } catch {
    // handled by useApi
  } finally {
    cdSearchLoading.value = false
  }
}

const filteredCD = computed(() => {
  const q = cdSearch.value.toLowerCase().trim()
  if (!q) return cdList.value
  return cdList.value.filter(cd =>
    cd.libelle.toLowerCase().includes(q) || cd.code.toLowerCase().includes(q)
  )
})

function selectCD(cd: DepartmentalCommittee) {
  newClubForm.codeComiteDep = cd.code
  newClubForm.cdLibelle = cd.libelle
  cdSearch.value = `${cd.code} - ${cd.libelle}`
  cdSearchOpen.value = false
}

// ── Submit Add CD ──
async function submitAddCD() {
  cdError.value = ''

  if (!cdForm.codeComiteReg) {
    cdError.value = t('clubs.add_cd.error_no_cr')
    return
  }
  if (!cdForm.code.trim() || !cdForm.libelle.trim()) {
    cdError.value = t('clubs.add_cd.error_empty')
    return
  }

  cdSubmitting.value = true
  try {
    await api.post('/admin/departmental-committees', {
      code: cdForm.code.trim(),
      libelle: cdForm.libelle.trim(),
      codeComiteReg: cdForm.codeComiteReg
    })
    toast.add({
      title: t('clubs.add_cd.success'),
      color: 'success',
      duration: 3000
    })
    cdModalOpen.value = false
    resetCdForm()
    // Refresh CD list for club modal
    cdList.value = []
  } catch {
    // useApi shows toast for HTTP errors
  } finally {
    cdSubmitting.value = false
  }
}

function resetCdForm() {
  cdForm.codeComiteReg = ''
  cdForm.crLibelle = ''
  cdForm.code = ''
  cdForm.libelle = ''
  crSearch.value = ''
  cdError.value = ''
}

// ── Submit Add Club ──
async function submitAddClub() {
  clubError.value = ''

  if (!newClubForm.codeComiteDep) {
    clubError.value = t('clubs.add_club.error_no_cd')
    return
  }
  if (!newClubForm.code.trim() || !newClubForm.libelle.trim()) {
    clubError.value = t('clubs.add_club.error_empty')
    return
  }

  clubSubmitting.value = true
  try {
    const body: Record<string, unknown> = {
      code: newClubForm.code.trim(),
      libelle: newClubForm.libelle.trim().toUpperCase(),
      codeComiteDep: newClubForm.codeComiteDep,
      postal: newClubForm.postal.trim(),
      www: newClubForm.www.trim(),
      email: newClubForm.email.trim(),
      coord: newClubForm.coord.trim()
    }
    if (newClubForm.equipe.trim()) {
      body.equipe = { libelle: newClubForm.equipe.trim() }
    }

    await api.post('/admin/clubs', body)
    toast.add({
      title: t('clubs.add_club.success'),
      color: 'success',
      duration: 3000
    })
    clubModalOpen.value = false
    resetClubForm()
    // Reload map to include new club
    loadMapClubs()
  } catch (error: unknown) {
    const err = error as { status?: number; data?: { code?: string } }
    if (err?.data?.code === 'DUPLICATE_CODE') {
      clubError.value = t('clubs.add_club.error_duplicate')
    }
    // Other errors handled by useApi
  } finally {
    clubSubmitting.value = false
  }
}

function resetClubForm() {
  newClubForm.codeComiteDep = ''
  newClubForm.cdLibelle = ''
  newClubForm.code = ''
  newClubForm.libelle = ''
  newClubForm.postal = ''
  newClubForm.www = ''
  newClubForm.email = ''
  newClubForm.coord = ''
  newClubForm.equipe = ''
  cdSearch.value = ''
  clubError.value = ''
}

// Open modals
function openAddCdModal() {
  resetCdForm()
  cdModalOpen.value = true
  loadRegionalCommittees()
}

function openAddClubModal() {
  resetClubForm()
  clubModalOpen.value = true
  loadDepartmentalCommittees()
}

// Close dropdowns on outside click
const clubSearchRef = ref<HTMLElement | null>(null)
const crSearchRef = ref<HTMLElement | null>(null)
const cdSearchRef = ref<HTMLElement | null>(null)

function handleGlobalClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (clubSearchRef.value && !clubSearchRef.value.contains(target)) {
    clubSearchOpen.value = false
  }
  if (crSearchRef.value && !crSearchRef.value.contains(target)) {
    crSearchOpen.value = false
  }
  if (cdSearchRef.value && !cdSearchRef.value.contains(target)) {
    cdSearchOpen.value = false
  }
}

onMounted(async () => {
  await loadMapClubs()

  // Auto-select club from query param ?code=XXXX
  const codeFromQuery = route.query.code as string
  if (codeFromQuery) {
    const club = mapClubs.value.find(c => c.code === codeFromQuery)
    // Select from map if geolocated, otherwise fetch directly by code
    selectClub({
      code: club?.code ?? codeFromQuery,
      libelle: club?.libelle ?? '',
      codeComiteDep: ''
    })
    // Clean up URL
    router.replace({ query: { ...route.query, code: undefined } })
  }

  document.addEventListener('click', handleGlobalClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick)
})
</script>

<template>
  <div>
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-header-900 mb-4">
      {{ t('clubs.title') }}
    </h1>

    <!-- ═══ Toolbar ═══ -->
    <div v-if="canEdit" class="p-3 mb-4 flex justify-end gap-3">
      <!-- Admin action buttons -->
      <div class="flex justify-end gap-2">
        <button
          class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-1.5"
          @click="openAddCdModal"
        >
          <UIcon name="i-heroicons-plus" class="w-4 h-4" />
          {{ t('clubs.add_cd.title') }}
        </button>
        <button
          class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-1.5"
          @click="openAddClubModal"
        >
          <UIcon name="i-heroicons-plus" class="w-4 h-4" />
          {{ t('clubs.add_club.title') }}
        </button>
      </div>
    </div>

    <!-- ═══ Main content: Map + Side panel ═══ -->
    <div class="flex flex-col lg:flex-row gap-4">
      <!-- Map (left) -->
      <div class="flex-1 min-w-0">
        <!-- Map loading skeleton -->
        <div v-if="mapLoading" class="w-full h-100 sm:h-125 lg:h-[calc(100vh-220px)] bg-header-100 rounded-lg animate-pulse flex items-center justify-center">
          <UIcon name="i-heroicons-map-pin" class="w-8 h-8 text-header-400" />
        </div>

        <!-- Leaflet Map -->
        <AdminClubMap
          v-show="!mapLoading"
          ref="mapRef"
          :clubs="mapClubs"
          :selected-club-code="selectedClubCode"
          class="lg:h-[calc(100vh-220px)]!"
          @select-club="onMapSelectClub"
        />

        <!-- Geocode search bar -->
        <div class="flex mt-3 gap-2">
          <input
            v-model="geocodeAddress"
            type="text"
            :placeholder="t('clubs.map.search_address')"
            class="flex-1 px-3 py-2 border border-header-300 text-primary-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @keydown.enter="handleGeocode"
          >
          <button
            class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-1.5"
            :disabled="geocoding || !geocodeAddress.trim()"
            @click="handleGeocode"
          >
            <UIcon v-if="geocoding" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
            <UIcon v-else name="i-heroicons-map-pin" class="w-4 h-4" />
            {{ t('clubs.map.locate') }}
          </button>
        </div>

        <p class="mt-2 text-xs text-header-500">
          {{ t('clubs.map.no_club_on_map') }}
        </p>
      </div>

      <!-- Side panel (right) -->
      <div class="w-full lg:w-80 xl:w-96 shrink-0">
        <div class="bg-white border border-header-200 rounded-lg p-4 lg:sticky lg:top-4">
          <!-- Club search autocomplete -->
          <div ref="clubSearchRef" class="relative mb-4">
            <div class="relative">
              <input
                v-model="clubSearch"
                type="text"
                :placeholder="t('clubs.search_placeholder')"
                class="w-full px-3 py-2 pl-9 pr-8 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                @input="onClubSearchInput"
                @focus="onClubSearchInput"
              >
              <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
              <button
                v-if="clubSearch && !clubSearchLoading"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
                @click="clearClubSearch"
              >
                <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
              </button>
              <UIcon v-if="clubSearchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400 animate-spin" />
            </div>
            <!-- Dropdown results -->
            <div
              v-if="clubSearchOpen && clubSearchResults.length > 0"
              class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
            >
              <button
                v-for="result in clubSearchResults"
                :key="result.code"
                class="w-full px-3 py-2 text-left text-sm text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none flex items-center gap-2"
                @click="selectClub(result)"
              >
                <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded">{{ result.code }}</span>
                <span>{{ result.libelle }}</span>
              </button>
            </div>
          </div>

          <!-- Club detail / edit form -->
          <template v-if="selectedClub">
            <div class="mb-3 text-sm">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded">{{ selectedClub.code }}</span>
                <span class="font-semibold text-header-900">{{ selectedClub.libelle }}</span>
              </div>
              <span v-if="selectedClub.libelleComiteDep" class="text-xs text-header-400">
                {{ selectedClub.libelleComiteDep }}
              </span>
            </div>

            <div v-if="canEdit" class="space-y-3">
              <!-- Postal -->
              <div>
                <label class="block text-xs font-medium text-header-700 mb-1">{{ t('clubs.update.postal') }}</label>
                <input
                  v-model="updateForm.postal"
                  type="text"
                  maxlength="100"
                  :placeholder="t('clubs.update.postal_hint')"
                  class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>

              <!-- Website -->
              <div>
                <label class="block text-xs font-medium text-header-700 mb-1">{{ t('clubs.update.www') }}</label>
                <input
                  v-model="updateForm.www"
                  type="text"
                  maxlength="60"
                  :placeholder="t('clubs.update.www_hint')"
                  class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>

              <!-- Email -->
              <div>
                <label class="block text-xs font-medium text-header-700 mb-1">{{ t('clubs.update.email') }}</label>
                <input
                  v-model="updateForm.email"
                  type="text"
                  maxlength="60"
                  :placeholder="t('clubs.update.email_hint')"
                  class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>

              <!-- GPS Coordinates -->
              <div>
                <label class="block text-xs font-medium text-header-700 mb-1">{{ t('clubs.update.coord') }}</label>
                <input
                  v-model="updateForm.coord"
                  type="text"
                  maxlength="50"
                  :placeholder="t('clubs.update.coord_hint')"
                  class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
              </div>

              <!-- Update button -->
              <div class="flex justify-end">
                <button
                  class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
                  :disabled="updating"
                  @click="handleUpdate"
                >
                  <UIcon v-if="updating" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
                  {{ t('clubs.update.submit') }}
                </button>
              </div>
            </div>

            <!-- Read-only for non-admins -->
            <div v-else class="space-y-2 text-sm text-header-600">
              <div v-if="selectedClub.postal"><span class="font-medium">{{ t('clubs.update.postal') }}:</span> {{ selectedClub.postal }}</div>
              <div v-if="selectedClub.www"><span class="font-medium">{{ t('clubs.update.www') }}:</span> {{ selectedClub.www }}</div>
              <div v-if="selectedClub.email"><span class="font-medium">{{ t('clubs.update.email') }}:</span> {{ selectedClub.email }}</div>
              <div v-if="selectedClub.coord"><span class="font-medium">{{ t('clubs.update.coord') }}:</span> {{ selectedClub.coord }}</div>
            </div>
          </template>

          <!-- No club selected -->
          <p v-else class="text-sm text-header-400 italic">
            {{ t('clubs.update.select_first') }}
          </p>

          <!-- ═══ Club teams ═══ -->
          <template v-if="selectedClub">
            <div class="mt-4 pt-4 border-t border-header-200">
              <h3 class="text-sm font-semibold text-header-900 mb-2">
                {{ t('clubs.teams.title') }}
              </h3>

              <!-- Loading -->
              <div v-if="clubTeamsLoading" class="flex items-center gap-2 text-sm text-header-400">
                <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
                <span>{{ t('common.loading') }}</span>
              </div>

              <!-- No teams -->
              <p v-else-if="clubTeams.length === 0" class="text-sm text-header-400 italic">
                {{ t('clubs.teams.no_teams') }}
              </p>

              <!-- Teams list -->
              <div v-else class="space-y-1.5">
                <NuxtLink
                  v-for="team in clubTeams"
                  :key="team.numero"
                  :to="`/clubs/team/${team.numero}`"
                  class="block px-3 py-2 rounded-lg hover:bg-primary-50 transition-colors group"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-header-900 group-hover:text-primary-700">{{ team.libelle }}</span>
                    <UIcon name="i-heroicons-chevron-right" class="w-4 h-4 text-header-300 group-hover:text-primary-500" />
                  </div>
                  <div class="flex items-center gap-3 text-xs text-header-500 mt-0.5">
                    <span v-if="team.derniereSaison">{{ t('clubs.teams.last_season') }} : {{ team.derniereSaison }}</span>
                    <span v-if="team.nbCompetitions > 0">{{ team.nbCompetitions }} {{ t('clubs.teams.competitions_count') }}</span>
                  </div>
                </NuxtLink>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ═══ Modal: Add CD ═══ -->
    <AdminModal
      :open="cdModalOpen"
      :title="t('clubs.add_cd.title')"
      max-width="lg"
      @close="cdModalOpen = false"
    >
      <div class="space-y-4">
        <!-- Error message -->
        <div v-if="cdError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
          {{ cdError }}
        </div>

        <!-- CR autocomplete -->
        <div ref="crSearchRef" class="relative">
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_cd.comite_reg') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="crSearch"
            type="text"
            :placeholder="t('clubs.add_cd.comite_reg_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @focus="crSearchOpen = true"
            @input="crSearchOpen = true"
          >
          <div
            v-if="crSearchOpen && filteredCR.length > 0"
            class="absolute z-20 mt-1 w-full max-h-48 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
          >
            <button
              v-for="cr in filteredCR"
              :key="cr.code"
              class="w-full px-3 py-2 text-left text-sm text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none flex items-center gap-2"
              @click="selectCR(cr)"
            >
              <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded">{{ cr.code }}</span>
              <span>{{ cr.libelle }}</span>
            </button>
          </div>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_cd.code') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="cdForm.code"
            type="text"
            maxlength="5"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- Libelle -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_cd.libelle') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="cdForm.libelle"
            type="text"
            maxlength="50"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <template #footer>
        <button
          class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200"
          @click="cdModalOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
          :disabled="cdSubmitting"
          @click="submitAddCD"
        >
          <UIcon v-if="cdSubmitting" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('clubs.add_cd.submit') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══ Modal: Add Club ═══ -->
    <AdminModal
      :open="clubModalOpen"
      :title="t('clubs.add_club.title')"
      max-width="xl"
      @close="clubModalOpen = false"
    >
      <div class="space-y-4">
        <!-- Error message -->
        <div v-if="clubError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
          {{ clubError }}
        </div>

        <!-- CD autocomplete -->
        <div ref="cdSearchRef" class="relative">
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_club.comite_dep') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="cdSearch"
            type="text"
            :placeholder="t('clubs.add_club.comite_dep_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @focus="cdSearchOpen = true"
            @input="cdSearchOpen = true"
          >
          <div
            v-if="cdSearchOpen && filteredCD.length > 0"
            class="absolute z-20 mt-1 w-full max-h-48 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
          >
            <button
              v-for="cd in filteredCD"
              :key="cd.code"
              class="w-full px-3 py-2 text-left text-sm text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none flex items-center gap-2"
              @click="selectCD(cd)"
            >
              <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded">{{ cd.code }}</span>
              <span>{{ cd.libelle }}</span>
            </button>
          </div>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_club.code') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="newClubForm.code"
            type="text"
            maxlength="5"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase"
          >
        </div>

        <!-- Libelle -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('clubs.add_club.libelle') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="newClubForm.libelle"
            type="text"
            maxlength="50"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase"
          >
        </div>

        <!-- Postal -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('clubs.add_club.postal') }}</label>
          <input
            v-model="newClubForm.postal"
            type="text"
            maxlength="100"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- Website -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('clubs.add_club.www') }}</label>
          <input
            v-model="newClubForm.www"
            type="text"
            maxlength="60"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('clubs.add_club.email') }}</label>
          <input
            v-model="newClubForm.email"
            type="text"
            maxlength="40"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- GPS -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('clubs.add_club.coord') }}</label>
          <input
            v-model="newClubForm.coord"
            type="text"
            maxlength="60"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>

        <!-- New team (optional) -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('clubs.add_club.equipe') }}</label>
          <input
            v-model="newClubForm.equipe"
            type="text"
            maxlength="40"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
          <p class="mt-1 text-xs text-header-500">
            {{ t('clubs.add_club.equipe_help') }}
          </p>
        </div>
      </div>

      <template #footer>
        <button
          class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200"
          @click="clubModalOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
          :disabled="clubSubmitting"
          @click="submitAddClub"
        >
          <UIcon v-if="clubSubmitting" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('clubs.add_club.submit') }}
        </button>
      </template>
    </AdminModal>

    <AdminScrollToTop />
  </div>
</template>
