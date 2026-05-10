<script setup lang="ts">
import type { MandateForm } from '~/types/users'

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

interface Props {
  seasons: { code: string; active: boolean }[]
  events: EventItem[]
  profileOptions: { value: number; label: string }[]
  adminNiveau: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'save', data: MandateForm, id?: number): void
}>()

interface ClubResult {
  code: string
  libelle: string
}

const { t } = useI18n()
const api = useApi()

const expanded = ref(false)
const form = reactive({
  libelle: '',
  niveau: 7 as number,
  filtreSaison: [] as string[],
  filtreCompetition: [] as string[],
  filtreJournee: '',
  idEvenement: [] as number[],
})
const allCompetitions = ref(false)

// Club autocomplete (tags)
const selectedClubs = ref<ClubResult[]>([])
const clubQuery = ref('')
const clubResults = ref<ClubResult[]>([])
const clubLoading = ref(false)
const showClubDropdown = ref(false)
const clubDropdownRef = ref<HTMLElement>()

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

function handleGlobalClick(e: MouseEvent) {
  if (clubDropdownRef.value && !clubDropdownRef.value.contains(e.target as Node)) {
    showClubDropdown.value = false
  }
}

onMounted(() => document.addEventListener('click', handleGlobalClick))
onBeforeUnmount(() => document.removeEventListener('click', handleGlobalClick))

// Competitions loaded dynamically based on selected seasons
const competitions = ref<Competition[]>([])
const competitionsLoading = ref(false)

async function loadCompetitions() {
  competitionsLoading.value = true
  try {
    const params: Record<string, string> = form.filtreSaison.length > 0
      ? { seasons: form.filtreSaison.join('|') }
      : { allSeasons: '1' }
    const data = await api.get<{ groups: { section: number; sectionLabel: string; competitions: { code: string; libelle: string }[] }[] }>('/admin/filters/competitions', params)
    const flat: Competition[] = []
    for (const group of data.groups || []) {
      for (const c of group.competitions) {
        flat.push({ code: c.code, libelle: c.libelle, section: group.section, sectionLabel: group.sectionLabel })
      }
    }
    competitions.value = flat
    // Remove selected competitions no longer available
    const validCodes = new Set(flat.map(c => c.code))
    form.filtreCompetition = form.filtreCompetition.filter(code => validCodes.has(code))
  } catch { /* ignore */ }
  finally { competitionsLoading.value = false }
}

function profileLabelWithoutNumber(label: string): string {
  return label.replace(/^\d+\s*-\s*/, '')
}

watch(() => form.niveau, (newVal) => {
  const opt = props.profileOptions.find(o => o.value === newVal)
  if (opt) form.libelle = profileLabelWithoutNumber(opt.label)
  // Profile >= 3 cannot use "all competitions" — reset if active
  if (newVal >= 3 && allCompetitions.value) {
    allCompetitions.value = false
  }
})

// Reload competitions when selected seasons change
watch(() => form.filtreSaison, () => {
  if (expanded.value) loadCompetitions()
}, { deep: true })

function resetForm() {
  form.niveau = 7
  const opt = props.profileOptions.find(o => o.value === 7)
  form.libelle = opt ? profileLabelWithoutNumber(opt.label) : ''
  form.filtreSaison = []
  form.filtreCompetition = []
  form.filtreJournee = ''
  form.idEvenement = []
  allCompetitions.value = false
  competitions.value = []
  selectedClubs.value = []
  clubQuery.value = ''
  clubResults.value = []
  showClubDropdown.value = false
}

function toggleExpanded() {
  expanded.value = !expanded.value
  if (expanded.value) {
    resetForm()
    loadCompetitions()
  }
}

function toggleSeason(code: string) {
  const idx = form.filtreSaison.indexOf(code)
  if (idx >= 0) form.filtreSaison.splice(idx, 1)
  else form.filtreSaison.push(code)
}

function toggleCompetition(code: string) {
  const idx = form.filtreCompetition.indexOf(code)
  if (idx >= 0) form.filtreCompetition.splice(idx, 1)
  else form.filtreCompetition.push(code)
  allCompetitions.value = false
}

function toggleAllCompetitions() {
  allCompetitions.value = !allCompetitions.value
  if (allCompetitions.value) form.filtreCompetition = []
}

function toggleEvent(id: number) {
  const idx = form.idEvenement.indexOf(id)
  if (idx >= 0) form.idEvenement.splice(idx, 1)
  else form.idEvenement.push(id)
}

const groupedCompetitions = computed(() => {
  const groups: Record<string, { label: string; items: Competition[] }> = {}
  for (const c of competitions.value) {
    const key = String(c.section)
    if (!groups[key]) groups[key] = { label: c.sectionLabel, items: [] }
    groups[key].items.push(c)
  }
  return groups
})

const saveError = ref('')

function handleSave() {
  saveError.value = ''

  if (!form.libelle.trim()) return

  // Mandates always require at least one season
  if (form.filtreSaison.length === 0) {
    saveError.value = t('users.validation_season_required')
    return
  }

  // Profile >= 3: at least one competition required
  if (form.niveau >= 3 && (allCompetitions.value || form.filtreCompetition.length === 0)) {
    saveError.value = t('users.validation_competition_required')
    return
  }

  // Profile 5 or 6: at least one gameday required
  if ((form.niveau === 5 || form.niveau === 6) && !form.filtreJournee.trim()) {
    saveError.value = t('users.validation_gameday_required')
    return
  }

  // Profile 7: at least one club required
  if (form.niveau === 7 && selectedClubs.value.length === 0) {
    saveError.value = t('users.validation_club_required')
    return
  }

  const data: MandateForm = {
    libelle: form.libelle.trim(),
    niveau: form.niveau,
    filtreSaison: form.filtreSaison.length === 0 ? '' : '|' + form.filtreSaison.join('|') + '|',
    filtreCompetition: allCompetitions.value || form.filtreCompetition.length === 0 ? '' : '|' + form.filtreCompetition.join('|') + '|',
    limitClubs: selectedClubs.value.map(c => c.code).join(','),
    filtreJournee: form.filtreJournee.trim(),
    idEvenement: form.idEvenement.length === 0 ? '' : '|' + form.idEvenement.join('|') + '|',
  }

  emit('save', data)
  expanded.value = false
  resetForm()
}
</script>

<template>
  <div>
    <button
      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-primary-700 bg-primary-50 border border-primary-300 rounded-lg hover:bg-primary-100 transition-colors"
      @click="toggleExpanded"
    >
      <UIcon :name="expanded ? 'i-heroicons-minus' : 'i-heroicons-plus'" class="w-4 h-4" />
      {{ t('users.modal.mandate_add') }}
    </button>

    <div v-if="expanded" class="mt-3 p-3 border border-primary-200 bg-primary-50/50 rounded-lg space-y-3">
      <!-- Error message -->
      <div v-if="saveError" class="p-2 bg-danger-50 border border-danger-200 rounded text-xs text-danger-700">
        {{ saveError }}
      </div>

      <!-- Profile + Label -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.mandate_profile') }} <span class="text-danger-500">*</span>
          </label>
          <select
            v-model="form.niveau"
            class="w-full px-2 py-1.5 border border-header-300 rounded text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="opt in profileOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.mandate_label') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="form.libelle"
            type="text"
            maxlength="100"
            class="w-full px-2 py-1.5 border border-header-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>

      <!-- Seasons + Competitions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.filter_seasons') }} <span class="text-danger-500">*</span>
          </label>
          <div class="border border-header-300 rounded max-h-38 overflow-y-auto p-1.5 bg-white">
            <label
              v-for="s in seasons"
              :key="s.code"
              class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="form.filtreSaison.includes(s.code)"
                @change="toggleSeason(s.code)"
              >
              {{ s.code }}
            </label>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.filter_competitions') }}
            <span v-if="form.niveau >= 3" class="text-danger-500">*</span>
          </label>
          <div class="border border-header-300 rounded max-h-38 overflow-y-auto p-1.5 bg-white relative">
            <div v-if="competitionsLoading" class="absolute inset-0 flex items-center justify-center bg-white/70">
              <span class="text-[10px] text-header-400">{{ t('common.loading') }}</span>
            </div>
            <label v-if="form.niveau < 3" class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer">
              <input type="checkbox" :checked="allCompetitions" @change="toggleAllCompetitions">
              <span class="font-medium">{{ t('users.modal.filter_competitions_all') }}</span>
            </label>
            <template v-if="!competitionsLoading">
              <template v-for="(group, key) in groupedCompetitions" :key="key">
                <div class="text-[10px] font-semibold text-header-500 mt-1 mb-0.5">— {{ group.label }} —</div>
                <label
                  v-for="c in group.items"
                  :key="c.code"
                  class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :checked="form.filtreCompetition.includes(c.code)"
                    :disabled="allCompetitions"
                    @change="toggleCompetition(c.code)"
                  >
                  {{ c.code }}
                </label>
              </template>
              <p v-if="competitions.length === 0 && form.filtreSaison.length > 0" class="text-[10px] text-header-400 italic">
                {{ t('users.modal.no_competitions_for_seasons') }}
              </p>
            </template>
          </div>
        </div>
      </div>

      <!-- Clubs + Journées -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.filter_clubs') }}
            <span v-if="form.niveau === 7" class="text-danger-500">*</span>
          </label>
          <div ref="clubDropdownRef" class="relative">
            <input
              v-model="clubQuery"
              type="text"
              class="w-full px-2 py-1.5 border border-header-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :placeholder="t('users.modal.filter_clubs_placeholder')"
            >
            <UIcon v-if="clubLoading" name="i-heroicons-arrow-path" class="absolute right-2 top-2 w-3.5 h-3.5 animate-spin text-header-400" />
            <div v-if="showClubDropdown" class="absolute z-30 mt-1 w-full bg-white border border-header-200 rounded shadow-lg max-h-36 overflow-y-auto">
              <button
                v-for="club in clubResults"
                :key="club.code"
                class="w-full px-2 py-1.5 text-left text-xs hover:bg-primary-50"
                @click="selectClub(club)"
              >
                {{ club.code }} - {{ club.libelle }}
              </button>
            </div>
          </div>
          <div v-if="selectedClubs.length > 0" class="flex flex-wrap gap-1 mt-1.5">
            <span
              v-for="club in selectedClubs"
              :key="club.code"
              class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-primary-100 text-primary-800 text-[10px] rounded-full"
            >
              {{ club.code }} - {{ club.libelle }}
              <button class="text-primary-600 hover:text-primary-800 leading-none" @click="removeClub(club.code)">×</button>
            </span>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-header-700 mb-1">
            {{ t('users.modal.filter_gamedays') }}
            <span v-if="form.niveau === 5 || form.niveau === 6" class="text-danger-500">*</span>
          </label>
          <input
            v-model="form.filtreJournee"
            type="text"
            class="w-full px-2 py-1.5 border border-header-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            :placeholder="t('users.modal.filter_gamedays_placeholder')"
          >
        </div>
      </div>

      <!-- Events (profile <= 2 only) -->
      <div v-if="adminNiveau <= 2 && events.length > 0">
        <label class="block text-xs font-medium text-header-700 mb-1">{{ t('users.modal.filter_events') }}</label>
        <div class="border border-header-300 rounded max-h-38 overflow-y-auto p-1.5 bg-white">
          <label
            v-for="evt in events"
            :key="evt.id"
            class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer"
          >
            <input
              type="checkbox"
              :checked="form.idEvenement.includes(evt.id)"
              @change="toggleEvent(evt.id)"
            >
            {{ evt.id }} - {{ evt.libelle }}
          </label>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-2">
        <button
          class="px-4 py-2 text-sm font-medium text-header-700 bg-header-100 border border-header-300 rounded-lg hover:bg-header-200 transition-colors"
          @click="expanded = false"
        >
          {{ t('users.modal.mandate_cancel') }}
        </button>
        <button
          class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-primary-700 rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          :disabled="!form.libelle.trim()"
          @click="handleSave"
        >
          {{ t('users.modal.mandate_validate') }}
        </button>
      </div>
    </div>
  </div>
</template>
