<script setup lang="ts">
import type { UserListItem, UserDetail, Mandate, MandateForm } from '~/types/users'

interface Competition {
  code: string
  libelle: string
  section: number
  sectionLabel: string
}

interface EventItem {
  id: number
  libelle: string
  dateDebut: string
}

interface ClubResult {
  code: string
  libelle: string
}

interface AthleteResult {
  matric: number
  nom: string
  prenom: string
  codeClub: string
  club: string
  label: string
}

interface Props {
  open: boolean
  user: UserListItem | null
  seasons: { code: string; active: boolean }[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()

const isEditing = computed(() => !!props.user)
const adminNiveau = computed(() => authStore.profile)

// Form state
const form = reactive({
  code: '',
  identite: '',
  mail: '',
  tel: '',
  fonction: '',
  niveau: 7,
  filtreSaison: '' as string,
  filtreCompetition: '' as string,
  idEvenement: '' as string,
  filtreJournee: '',
  limitClubs: '',
  sendResetEmail: true,
  includeDocLink: true,
  complementaryMessage: '',
})

const submitting = ref(false)
const formError = ref('')

// Autocomplete state
const licenceQuery = ref('')
const licenceResults = ref<AthleteResult[]>([])
const licenceLoading = ref(false)
const showLicenceDropdown = ref(false)
const licenceDropdownRef = ref<HTMLElement>()

const clubQuery = ref('')
const clubResults = ref<ClubResult[]>([])
const clubLoading = ref(false)
const showClubDropdown = ref(false)
const clubDropdownRef = ref<HTMLElement>()

// Selected clubs (tags)
const selectedClubs = ref<ClubResult[]>([])

// Competitions and events for multi-selects
const competitions = ref<Competition[]>([])
const events = ref<EventItem[]>([])

// Parsed selected values for multi-selects
const selectedSeasons = ref<string[]>([])
const allSeasons = ref(false)
const selectedCompetitions = ref<string[]>([])
const allCompetitions = ref(false)
const selectedEvents = ref<number[]>([])

// Mandates
const mandates = ref<Mandate[]>([])
const mandatesLoading = ref(false)

// Standard message template
const standardMessage = "Bonjour,\n\nVous avez reçu un accès à l'espace d'administration KPI (kayak-polo.info).\n\nVos identifiants vous permettent de gérer les feuilles de présence de votre équipe. Merci de vérifier la composition avant chaque journée de compétition.\n\nCordialement"

// Watch for open changes to load data
watch(() => props.open, async (isOpen) => {
  if (!isOpen) return

  formError.value = ''
  selectedClubs.value = []
  mandates.value = []

  // Load competitions for the active season
  await loadCompetitions()
  if (adminNiveau.value <= 2) {
    await loadEvents()
  }

  if (props.user) {
    // Edit mode: load user detail
    await loadUserDetail(props.user.code)
  } else {
    // Create mode: defaults
    resetForm()
  }
})

function resetForm() {
  form.code = ''
  form.identite = ''
  form.mail = ''
  form.tel = ''
  form.fonction = ''
  form.niveau = 7
  form.filtreSaison = ''
  form.filtreCompetition = ''
  form.idEvenement = ''
  form.filtreJournee = ''
  form.limitClubs = ''
  form.sendResetEmail = true
  form.includeDocLink = true
  form.complementaryMessage = ''

  // Default: active season selected
  const activeSeason = props.seasons.find(s => s.active)
  if (activeSeason) {
    selectedSeasons.value = [activeSeason.code]
    allSeasons.value = false
  } else {
    selectedSeasons.value = []
    allSeasons.value = true
  }
  selectedCompetitions.value = []
  allCompetitions.value = true
  selectedEvents.value = []
  selectedClubs.value = []
  mandates.value = []
}

async function loadUserDetail(code: string) {
  try {
    const detail = await api.get<UserDetail>(`/admin/users/${code}`)
    form.code = detail.code
    form.identite = detail.identite
    form.mail = detail.mail
    form.tel = detail.tel
    form.fonction = detail.fonction
    form.niveau = detail.niveau
    form.filtreSaison = detail.filtreSaison
    form.filtreCompetition = detail.filtreCompetition
    form.idEvenement = detail.idEvenement
    form.filtreJournee = detail.filtreJournee
    form.limitClubs = detail.limitClubs
    form.sendResetEmail = false
    form.includeDocLink = false
    form.complementaryMessage = ''

    // Parse seasons
    if (!detail.filtreSaison || detail.filtreSaison.trim() === '') {
      allSeasons.value = true
      selectedSeasons.value = []
    } else {
      allSeasons.value = false
      selectedSeasons.value = detail.filtreSaison.split('|').filter(v => v.trim() !== '')
    }

    // Parse competitions
    if (!detail.filtreCompetition || detail.filtreCompetition.trim() === '') {
      allCompetitions.value = true
      selectedCompetitions.value = []
    } else {
      allCompetitions.value = false
      selectedCompetitions.value = detail.filtreCompetition.split('|').filter(v => v.trim() !== '')
    }

    // Parse events
    if (detail.idEvenement && detail.idEvenement.trim()) {
      selectedEvents.value = detail.idEvenement.split('|').filter(v => v.trim() !== '').map(Number)
    } else {
      selectedEvents.value = []
    }

    // Parse clubs
    if (detail.limitClubs && detail.limitClubs.trim()) {
      const codes = detail.limitClubs.split(',').filter(v => v.trim() !== '')
      selectedClubs.value = codes.map(c => ({ code: c.trim(), libelle: c.trim() }))
      // Try to resolve club names
      for (const club of selectedClubs.value) {
        try {
          const results = await api.get<ClubResult[]>('/admin/clubs/search', { q: club.code, limit: 1 })
          if (results.length > 0 && results[0].code === club.code) {
            club.libelle = results[0].libelle
          }
        } catch { /* ignore */ }
      }
    } else {
      selectedClubs.value = []
    }

    // Load mandates if profile allows
    if (adminNiveau.value <= 3) {
      await loadMandates(code)
    }
  } catch { /* useApi handles toast */ }
}

async function loadCompetitions() {
  try {
    const data = await api.get<{ competitions: Competition[] }>('/admin/filters/competitions')
    competitions.value = data.competitions || []
  } catch { /* ignore */ }
}

async function loadEvents() {
  try {
    const data = await api.get<{ events: EventItem[] }>('/admin/filters/events')
    events.value = data.events || []
  } catch { /* ignore */ }
}

async function loadMandates(code: string) {
  mandatesLoading.value = true
  try {
    const data = await api.get<{ mandats: Mandate[] }>(`/admin/users/${code}/mandats`)
    mandates.value = data.mandats || []
  } catch { /* ignore */ }
  finally { mandatesLoading.value = false }
}

// Licence autocomplete
let licenceTimeout: ReturnType<typeof setTimeout> | null = null
watch(licenceQuery, (q) => {
  if (licenceTimeout) clearTimeout(licenceTimeout)
  if (q.length < 2) {
    licenceResults.value = []
    showLicenceDropdown.value = false
    return
  }
  licenceTimeout = setTimeout(async () => {
    licenceLoading.value = true
    try {
      licenceResults.value = await api.get<AthleteResult[]>('/admin/athletes/search', { q, limit: 10 })
      showLicenceDropdown.value = licenceResults.value.length > 0
    } catch { /* ignore */ }
    finally { licenceLoading.value = false }
  }, 300)
})

function selectLicence(athlete: AthleteResult) {
  form.code = String(athlete.matric)
  form.identite = `${athlete.nom} ${athlete.prenom}`
  licenceQuery.value = ''
  showLicenceDropdown.value = false

  // Pre-fill club if available
  if (athlete.codeClub) {
    const existing = selectedClubs.value.find(c => c.code === athlete.codeClub)
    if (!existing) {
      selectedClubs.value.push({ code: athlete.codeClub, libelle: athlete.club || athlete.codeClub })
    }
  }
}

// Club autocomplete
let clubTimeout: ReturnType<typeof setTimeout> | null = null
watch(clubQuery, (q) => {
  if (clubTimeout) clearTimeout(clubTimeout)
  if (q.length < 2) {
    clubResults.value = []
    showClubDropdown.value = false
    return
  }
  clubTimeout = setTimeout(async () => {
    clubLoading.value = true
    try {
      clubResults.value = await api.get<ClubResult[]>('/admin/clubs/search', { q, limit: 10 })
      showClubDropdown.value = clubResults.value.length > 0
    } catch { /* ignore */ }
    finally { clubLoading.value = false }
  }, 300)
})

function selectClub(club: ClubResult) {
  if (!selectedClubs.value.find(c => c.code === club.code)) {
    selectedClubs.value.push(club)
  }
  clubQuery.value = ''
  showClubDropdown.value = false
}

function removeClub(code: string) {
  selectedClubs.value = selectedClubs.value.filter(c => c.code !== code)
}

// Build filter strings from selected values
function buildFiltreSaison(): string {
  if (allSeasons.value || selectedSeasons.value.length === 0) return ''
  return '|' + selectedSeasons.value.join('|') + '|'
}

function buildFiltreCompetition(): string {
  if (allCompetitions.value || selectedCompetitions.value.length === 0) return ''
  return '|' + selectedCompetitions.value.join('|') + '|'
}

function buildIdEvenement(): string {
  if (selectedEvents.value.length === 0) return ''
  return '|' + selectedEvents.value.join('|') + '|'
}

function buildLimitClubs(): string {
  return selectedClubs.value.map(c => c.code).join(',')
}

// Season toggle
function toggleSeason(code: string) {
  const idx = selectedSeasons.value.indexOf(code)
  if (idx >= 0) {
    selectedSeasons.value.splice(idx, 1)
  } else {
    selectedSeasons.value.push(code)
  }
  allSeasons.value = false
}

function toggleAllSeasons() {
  allSeasons.value = !allSeasons.value
  if (allSeasons.value) {
    selectedSeasons.value = []
  }
}

// Competition toggle
function toggleCompetition(code: string) {
  const idx = selectedCompetitions.value.indexOf(code)
  if (idx >= 0) {
    selectedCompetitions.value.splice(idx, 1)
  } else {
    selectedCompetitions.value.push(code)
  }
  allCompetitions.value = false
}

function toggleAllCompetitions() {
  allCompetitions.value = !allCompetitions.value
  if (allCompetitions.value) {
    selectedCompetitions.value = []
  }
}

// Event toggle
function toggleEvent(id: number) {
  const idx = selectedEvents.value.indexOf(id)
  if (idx >= 0) {
    selectedEvents.value.splice(idx, 1)
  } else {
    selectedEvents.value.push(id)
  }
}

// Profile options
const profileOptions = computed(() => {
  const options = []
  for (let i = 1; i <= 10; i++) {
    // Apply restrictions: profils 3-4 can only assign >= 5, profil 2 can assign >= 3
    if (adminNiveau.value <= 1) {
      options.push({ value: i, label: t(`users.profiles.${i}`) })
    } else if (adminNiveau.value <= 2 && i >= 3) {
      options.push({ value: i, label: t(`users.profiles.${i}`) })
    } else if (adminNiveau.value <= 4 && i >= 5) {
      options.push({ value: i, label: t(`users.profiles.${i}`) })
    }
  }
  return options
})

// Grouped competitions for display
const groupedCompetitions = computed(() => {
  const groups: Record<string, { label: string; items: Competition[] }> = {}
  for (const c of competitions.value) {
    const key = String(c.section)
    if (!groups[key]) {
      groups[key] = { label: c.sectionLabel, items: [] }
    }
    groups[key].items.push(c)
  }
  return groups
})

// Submit
async function handleSubmit() {
  formError.value = ''

  // Validation
  if (!form.code.trim()) {
    formError.value = t('users.modal.licence') + ' is required'
    return
  }
  if (!form.mail.trim()) {
    formError.value = t('users.modal.email') + ' is required'
    return
  }

  // Club validation for profiles 7-8
  if ((form.niveau === 7 || form.niveau === 8) && selectedClubs.value.length === 0) {
    formError.value = t('users.validation_club_required')
    return
  }

  submitting.value = true

  const payload = {
    code: form.code.trim(),
    identite: form.identite.trim(),
    mail: form.mail.trim(),
    tel: form.tel.trim(),
    fonction: form.fonction.trim(),
    niveau: form.niveau,
    filtreSaison: buildFiltreSaison(),
    filtreCompetition: buildFiltreCompetition(),
    idEvenement: buildIdEvenement(),
    filtreJournee: form.filtreJournee.trim(),
    limitClubs: buildLimitClubs(),
    sendResetEmail: form.sendResetEmail,
    includeDocLink: form.includeDocLink,
    complementaryMessage: form.complementaryMessage,
  }

  try {
    if (isEditing.value) {
      await api.put(`/admin/users/${form.code}`, payload)
      toast.add({ title: t('users.success_updated'), color: 'success', duration: 3000 })
    } else {
      await api.post('/admin/users', payload)
      toast.add({ title: t('users.success_created'), color: 'success', duration: 3000 })
    }
    emit('saved')
    emit('close')
  } catch (error: unknown) {
    const err = error as { status?: number; data?: { code?: string; message?: string } }
    if (err?.data?.code === 'CODE_EXISTS') {
      formError.value = t('users.error_code_exists')
    } else if (err?.data?.code === 'PROFILE_RESTRICTED') {
      formError.value = t('users.error_profile_restricted')
    } else if (err?.data?.message) {
      formError.value = err.data.message
    }
  } finally {
    submitting.value = false
  }
}

// Mandate CRUD
async function onMandateSaved(mandateData: MandateForm, mandateId?: number) {
  if (!props.user) return
  try {
    if (mandateId) {
      await api.put(`/admin/users/${props.user.code}/mandats/${mandateId}`, mandateData)
      toast.add({ title: t('users.mandates.success_updated'), color: 'success', duration: 3000 })
    } else {
      await api.post(`/admin/users/${props.user.code}/mandats`, mandateData)
      toast.add({ title: t('users.mandates.success_created'), color: 'success', duration: 3000 })
    }
    await loadMandates(props.user.code)
  } catch { /* useApi handles toast */ }
}

async function deleteMandate(mandateId: number) {
  if (!props.user) return
  if (!confirm(t('users.modal.mandate_confirm_delete'))) return
  try {
    await api.del(`/admin/users/${props.user.code}/mandats/${mandateId}`)
    toast.add({ title: t('users.mandates.success_deleted'), color: 'success', duration: 3000 })
    await loadMandates(props.user.code)
  } catch { /* useApi handles toast */ }
}

// Close autocomplete dropdowns on outside click
function handleGlobalClick(e: MouseEvent) {
  if (licenceDropdownRef.value && !licenceDropdownRef.value.contains(e.target as Node)) {
    showLicenceDropdown.value = false
  }
  if (clubDropdownRef.value && !clubDropdownRef.value.contains(e.target as Node)) {
    showClubDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleGlobalClick)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick)
})
</script>

<template>
  <AdminModal
    :open="open"
    :title="isEditing ? t('users.modal.title_edit') : t('users.modal.title_create')"
    max-width="3xl"
    @close="emit('close')"
  >
    <div class="space-y-5">
      <!-- Error banner -->
      <div v-if="formError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
        {{ formError }}
      </div>

      <!-- Licence autocomplete (create mode only) -->
      <div v-if="!isEditing" ref="licenceDropdownRef" class="relative">
        <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.search_licence') }}</label>
        <div class="relative">
          <input
            v-model="licenceQuery"
            type="text"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            :placeholder="t('users.modal.search_licence')"
          >
          <UIcon v-if="licenceLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-2.5 w-4 h-4 animate-spin text-header-400" />
        </div>
        <div v-if="showLicenceDropdown" class="absolute z-30 mt-1 w-full bg-white border border-header-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
          <button
            v-for="athlete in licenceResults"
            :key="athlete.matric"
            class="w-full px-3 py-2 text-left text-sm hover:bg-primary-50"
            @click="selectLicence(athlete)"
          >
            {{ athlete.label }}
          </button>
        </div>
      </div>

      <!-- Licence + Identity row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('users.modal.licence') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="form.code"
            type="text"
            maxlength="8"
            :readonly="isEditing"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            :class="{ 'bg-header-100': isEditing }"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('users.modal.identity') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="form.identite"
            type="text"
            maxlength="80"
            :readonly="isEditing && adminNiveau > 1"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            :class="{ 'bg-header-100': isEditing && adminNiveau > 1 }"
          >
        </div>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('users.modal.email') }} <span class="text-danger-500">*</span>
        </label>
        <input
          v-model="form.mail"
          type="email"
          maxlength="100"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
      </div>

      <!-- Phone + Function -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.phone') }}</label>
          <input
            v-model="form.tel"
            type="text"
            maxlength="15"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.function') }}</label>
          <input
            v-model="form.fonction"
            type="text"
            maxlength="100"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <!-- Profile -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('users.modal.profile') }} <span class="text-danger-500">*</span>
        </label>
        <select
          v-model="form.niveau"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
          <option v-for="opt in profileOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <!-- Access Filters section -->
      <div class="border-t pt-4">
        <h3 class="text-sm font-semibold text-header-800 mb-3">{{ t('users.modal.filters_title') }}</h3>

        <!-- Seasons + Competitions side by side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <!-- Seasons -->
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.filter_seasons') }}</label>
            <div class="border border-header-300 rounded-lg max-h-36 overflow-y-auto p-2">
              <label class="flex items-center gap-2 text-sm mb-1 cursor-pointer">
                <input type="checkbox" :checked="allSeasons" @change="toggleAllSeasons">
                <span class="font-medium">{{ t('users.modal.filter_seasons_all') }}</span>
              </label>
              <label
                v-for="s in seasons"
                :key="s.code"
                class="flex items-center gap-2 text-sm mb-1 cursor-pointer"
              >
                <input
                  type="checkbox"
                  :checked="selectedSeasons.includes(s.code)"
                  :disabled="allSeasons"
                  @change="toggleSeason(s.code)"
                >
                {{ s.code }}{{ s.active ? ' ★' : '' }}
              </label>
            </div>
          </div>

          <!-- Competitions -->
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.filter_competitions') }}</label>
            <div class="border border-header-300 rounded-lg max-h-36 overflow-y-auto p-2">
              <label class="flex items-center gap-2 text-sm mb-1 cursor-pointer">
                <input type="checkbox" :checked="allCompetitions" @change="toggleAllCompetitions">
                <span class="font-medium">{{ t('users.modal.filter_competitions_all') }}</span>
              </label>
              <template v-for="(group, key) in groupedCompetitions" :key="key">
                <div class="text-xs font-semibold text-header-500 mt-2 mb-1">— {{ group.label }} —</div>
                <label
                  v-for="c in group.items"
                  :key="c.code"
                  class="flex items-center gap-2 text-sm mb-0.5 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :checked="selectedCompetitions.includes(c.code)"
                    :disabled="allCompetitions"
                    @change="toggleCompetition(c.code)"
                  >
                  {{ c.code }} - {{ c.libelle }}
                </label>
              </template>
            </div>
          </div>
        </div>

        <!-- Events (profile <= 2 only) -->
        <div v-if="adminNiveau <= 2 && events.length > 0" class="mb-4">
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.filter_events') }}</label>
          <div class="border border-header-300 rounded-lg max-h-36 overflow-y-auto p-2">
            <label
              v-for="evt in events"
              :key="evt.id"
              class="flex items-center gap-2 text-sm mb-0.5 cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="selectedEvents.includes(evt.id)"
                @change="toggleEvent(evt.id)"
              >
              {{ evt.id }} - {{ evt.libelle }}
            </label>
          </div>
        </div>

        <!-- Clubs autocomplete -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.filter_clubs') }}</label>
          <div ref="clubDropdownRef" class="relative">
            <input
              v-model="clubQuery"
              type="text"
              class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :placeholder="t('users.modal.filter_clubs_placeholder')"
            >
            <UIcon v-if="clubLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-2.5 w-4 h-4 animate-spin text-header-400" />
            <div v-if="showClubDropdown" class="absolute z-30 mt-1 w-full bg-white border border-header-200 rounded-lg shadow-lg max-h-36 overflow-y-auto">
              <button
                v-for="club in clubResults"
                :key="club.code"
                class="w-full px-3 py-2 text-left text-sm hover:bg-primary-50"
                @click="selectClub(club)"
              >
                {{ club.code }} - {{ club.libelle }}
              </button>
            </div>
          </div>
          <!-- Selected club tags -->
          <div v-if="selectedClubs.length > 0" class="flex flex-wrap gap-1.5 mt-2">
            <span
              v-for="club in selectedClubs"
              :key="club.code"
              class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-100 text-primary-800 text-xs rounded-full"
            >
              {{ club.code }} - {{ club.libelle }}
              <button class="text-primary-600 hover:text-primary-800" @click="removeClub(club.code)">×</button>
            </span>
          </div>
        </div>

        <!-- Journées -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.modal.filter_gamedays') }}</label>
          <input
            v-model="form.filtreJournee"
            type="text"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            :placeholder="t('users.modal.filter_gamedays_placeholder')"
          >
        </div>
      </div>

      <!-- Mandates section (profile <= 3 and edit mode only) -->
      <div v-if="adminNiveau <= 3 && isEditing" class="border-t pt-4">
        <h3 class="text-sm font-semibold text-header-800 mb-3">{{ t('users.modal.mandates_title') }}</h3>

        <div v-if="mandatesLoading" class="flex justify-center py-4">
          <UIcon name="i-heroicons-arrow-path" class="w-5 h-5 animate-spin text-header-400" />
        </div>

        <div v-else-if="mandates.length === 0" class="text-sm text-header-500 mb-3">
          {{ t('users.modal.mandates_empty') }}
        </div>

        <!-- Existing mandates list -->
        <div v-else class="space-y-2 mb-3">
          <div
            v-for="mandate in mandates"
            :key="mandate.id"
            class="flex items-center justify-between p-2 bg-header-50 rounded-lg text-sm"
          >
            <div>
              <span class="font-medium">{{ mandate.libelle }}</span>
              <span class="text-header-500 ml-2">P{{ mandate.niveau }}</span>
              <span v-if="mandate.filtreSaison" class="text-header-400 ml-2 text-xs">
                {{ mandate.filtreSaison.split('|').filter(v => v).join(', ') }}
              </span>
              <span v-if="mandate.filtreCompetition" class="text-header-400 ml-2 text-xs">
                {{ mandate.filtreCompetition.split('|').filter(v => v).join(', ') }}
              </span>
            </div>
            <button
              class="p-1 text-danger-500 hover:text-danger-700"
              @click="deleteMandate(mandate.id)"
            >
              <UIcon name="i-heroicons-trash" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Add mandate form -->
        <AdminUserMandateForm
          :seasons="seasons"
          :competitions="competitions"
          :events="events"
          :profile-options="profileOptions"
          :admin-niveau="adminNiveau"
          @save="onMandateSaved"
        />
      </div>

      <!-- Email section -->
      <div class="border-t pt-4">
        <h3 class="text-sm font-semibold text-header-800 mb-3">{{ t('users.modal.email_section') }}</h3>
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.sendResetEmail" type="checkbox">
            {{ t('users.modal.send_reset_email') }}
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.includeDocLink" type="checkbox">
            {{ t('users.modal.include_doc_link') }}
          </label>
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-sm font-medium text-header-700">{{ t('users.modal.complementary_message') }}</label>
              <button
                class="text-xs text-primary-600 hover:text-primary-800"
                @click="form.complementaryMessage = standardMessage"
              >
                {{ t('users.modal.standard_message') }}
              </button>
            </div>
            <textarea
              v-model="form.complementaryMessage"
              rows="3"
              maxlength="2000"
              class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200"
        @click="emit('close')"
      >
        {{ t('users.modal.cancel') }}
      </button>
      <button
        class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
        :disabled="submitting"
        @click="handleSubmit"
      >
        <UIcon v-if="submitting" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        {{ t('users.modal.save') }}
      </button>
    </template>
  </AdminModal>
</template>
