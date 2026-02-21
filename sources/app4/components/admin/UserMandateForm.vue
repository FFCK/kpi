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
  competitions: Competition[]
  events: EventItem[]
  profileOptions: { value: number; label: string }[]
  adminNiveau: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'save', data: MandateForm, id?: number): void
}>()

const { t } = useI18n()

const expanded = ref(false)
const form = reactive({
  libelle: '',
  niveau: 7,
  filtreSaison: [] as string[],
  filtreCompetition: [] as string[],
  limitClubs: '',
  filtreJournee: '',
  idEvenement: [] as number[],
})
const allSeasons = ref(false)
const allCompetitions = ref(false)

function resetForm() {
  form.libelle = ''
  form.niveau = 7
  form.filtreSaison = []
  form.filtreCompetition = []
  form.limitClubs = ''
  form.filtreJournee = ''
  form.idEvenement = []
  allSeasons.value = false
  allCompetitions.value = false
}

function toggleExpanded() {
  expanded.value = !expanded.value
  if (expanded.value) {
    resetForm()
  }
}

function toggleSeason(code: string) {
  const idx = form.filtreSaison.indexOf(code)
  if (idx >= 0) form.filtreSaison.splice(idx, 1)
  else form.filtreSaison.push(code)
  allSeasons.value = false
}

function toggleAllSeasons() {
  allSeasons.value = !allSeasons.value
  if (allSeasons.value) form.filtreSaison = []
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
  for (const c of props.competitions) {
    const key = String(c.section)
    if (!groups[key]) groups[key] = { label: c.sectionLabel, items: [] }
    groups[key].items.push(c)
  }
  return groups
})

function handleSave() {
  if (!form.libelle.trim()) return

  const data: MandateForm = {
    libelle: form.libelle.trim(),
    niveau: form.niveau,
    filtreSaison: allSeasons.value || form.filtreSaison.length === 0 ? '' : '|' + form.filtreSaison.join('|') + '|',
    filtreCompetition: allCompetitions.value || form.filtreCompetition.length === 0 ? '' : '|' + form.filtreCompetition.join('|') + '|',
    limitClubs: form.limitClubs.trim(),
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
      class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
      @click="toggleExpanded"
    >
      <UIcon :name="expanded ? 'i-heroicons-minus' : 'i-heroicons-plus'" class="w-4 h-4" />
      {{ t('users.modal.mandate_add') }}
    </button>

    <div v-if="expanded" class="mt-3 p-3 border border-blue-200 bg-blue-50/50 rounded-lg space-y-3">
      <!-- Label + Profile -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">
            {{ t('users.modal.mandate_label') }} <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.libelle"
            type="text"
            maxlength="100"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">
            {{ t('users.modal.mandate_profile') }} <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.niveau"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option v-for="opt in profileOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Seasons + Competitions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('users.modal.filter_seasons') }}</label>
          <div class="border border-gray-300 rounded max-h-28 overflow-y-auto p-1.5 bg-white">
            <label class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer">
              <input type="checkbox" :checked="allSeasons" @change="toggleAllSeasons">
              <span class="font-medium">{{ t('users.modal.filter_seasons_all') }}</span>
            </label>
            <label
              v-for="s in seasons"
              :key="s.code"
              class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="form.filtreSaison.includes(s.code)"
                :disabled="allSeasons"
                @change="toggleSeason(s.code)"
              >
              {{ s.code }}
            </label>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('users.modal.filter_competitions') }}</label>
          <div class="border border-gray-300 rounded max-h-28 overflow-y-auto p-1.5 bg-white">
            <label class="flex items-center gap-1.5 text-xs mb-0.5 cursor-pointer">
              <input type="checkbox" :checked="allCompetitions" @change="toggleAllCompetitions">
              <span class="font-medium">{{ t('users.modal.filter_competitions_all') }}</span>
            </label>
            <template v-for="(group, key) in groupedCompetitions" :key="key">
              <div class="text-[10px] font-semibold text-gray-500 mt-1 mb-0.5">— {{ group.label }} —</div>
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
          </div>
        </div>
      </div>

      <!-- Clubs + Journées -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('users.modal.filter_clubs') }}</label>
          <input
            v-model="form.limitClubs"
            type="text"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="7603,4404"
          >
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('users.modal.filter_gamedays') }}</label>
          <input
            v-model="form.filtreJournee"
            type="text"
            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            :placeholder="t('users.modal.filter_gamedays_placeholder')"
          >
        </div>
      </div>

      <!-- Events (profile <= 2 only) -->
      <div v-if="adminNiveau <= 2 && events.length > 0">
        <label class="block text-xs font-medium text-gray-700 mb-1">{{ t('users.modal.filter_events') }}</label>
        <div class="border border-gray-300 rounded max-h-24 overflow-y-auto p-1.5 bg-white">
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
      <div class="flex justify-end gap-2 pt-1">
        <button
          class="px-3 py-1.5 text-xs text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
          @click="expanded = false"
        >
          {{ t('users.modal.mandate_cancel') }}
        </button>
        <button
          class="px-3 py-1.5 text-xs text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
          :disabled="!form.libelle.trim()"
          @click="handleSave"
        >
          {{ t('users.modal.mandate_validate') }}
        </button>
      </div>
    </div>
  </div>
</template>
