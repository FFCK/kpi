<script setup lang="ts">
import type { AthleteDetail, AthleteUpdatePayload } from '~/types/athletes'
import type { ClubSearchResult } from '~/types/clubs'

interface Props {
  open: boolean
  athlete: AthleteDetail | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// ── Form state ──
const form = reactive({
  nom: '',
  prenom: '',
  sexe: 'M',
  naissance: '',
  origine: '',
  icf: '' as string | number,
  arbQualification: '',
  arbNiveau: '',
  codeClub: '',
})

const submitting = ref(false)
const formError = ref('')

// ── Club autocomplete ──
const clubSearch = ref('')
const clubSearchResults = ref<ClubSearchResult[]>([])
const clubSearchLoading = ref(false)
const clubSearchOpen = ref(false)
const clubSearchRef = ref<HTMLElement | null>(null)
let clubSearchTimer: ReturnType<typeof setTimeout> | null = null

const qualificationOptions = [
  { value: '', label: '-' },
  { value: 'Reg', label: 'Reg' },
  { value: 'IR', label: 'IR' },
  { value: 'Nat', label: 'Nat' },
  { value: 'Int', label: 'Int' },
  { value: 'OTM', label: 'OTM' },
  { value: 'JO', label: 'JO' },
]

const niveauOptions = [
  { value: '', label: '-' },
  { value: 'A', label: 'A' },
  { value: 'B', label: 'B' },
  { value: 'C', label: 'C' },
  { value: 'S', label: 'S' },
]

// Reset form when athlete changes
watch(() => props.athlete, (athlete) => {
  if (athlete) {
    form.nom = athlete.nom
    form.prenom = athlete.prenom
    form.sexe = athlete.sexe
    form.naissance = athlete.naissance || ''
    form.origine = athlete.origine || ''
    form.icf = athlete.icf ?? ''
    form.arbQualification = athlete.arbitrage.qualification || ''
    form.arbNiveau = athlete.arbitrage.niveau || ''
    form.codeClub = ''
    clubSearch.value = ''
    formError.value = ''
  }
}, { immediate: true })

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

function selectClub(club: ClubSearchResult) {
  clubSearchOpen.value = false
  clubSearch.value = `${club.code} - ${club.libelle}`
  form.codeClub = club.code
}

function handleGlobalClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (clubSearchRef.value && !clubSearchRef.value.contains(target)) {
    clubSearchOpen.value = false
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    document.addEventListener('click', handleGlobalClick)
  } else {
    document.removeEventListener('click', handleGlobalClick)
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick)
})

async function handleSubmit() {
  if (!props.athlete) return
  formError.value = ''

  // Validate
  if (!form.nom.trim() || !form.prenom.trim()) {
    formError.value = t('athletes.edit.error')
    return
  }

  if (!['M', 'F'].includes(form.sexe)) {
    formError.value = t('athletes.edit.error')
    return
  }

  if (!form.naissance) {
    formError.value = t('athletes.edit.error')
    return
  }

  submitting.value = true
  try {
    const payload: AthleteUpdatePayload = {
      nom: form.nom.trim().toUpperCase(),
      prenom: form.prenom.trim().toUpperCase(),
      sexe: form.sexe,
      naissance: form.naissance,
      origine: form.origine.trim(),
      icf: form.icf !== '' ? Number(form.icf) : null,
      arbitrage: {
        qualification: form.arbQualification,
        niveau: form.arbNiveau,
      },
    }

    if (form.codeClub) {
      payload.codeClub = form.codeClub
    }

    await api.put(`/admin/athletes/${props.athlete.matric}`, payload)
    toast.add({
      title: t('athletes.edit.success'),
      color: 'success',
      duration: 3000,
    })
    emit('saved')
    emit('close')
  } catch (error: unknown) {
    const err = error as { status?: number; data?: { code?: string } }
    if (err?.status === 403) {
      formError.value = t('athletes.edit.forbidden')
    } else {
      formError.value = t('athletes.edit.error')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AdminModal
    :open="open"
    :title="t('athletes.edit.title')"
    max-width="lg"
    @close="emit('close')"
  >
    <div class="space-y-4">
      <!-- Error message -->
      <div v-if="formError" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
        {{ formError }}
      </div>

      <!-- Nom -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.nom') }} <span class="text-danger-500">*</span>
        </label>
        <input
          v-model="form.nom"
          type="text"
          maxlength="30"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase"
        >
      </div>

      <!-- Prenom -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.prenom') }} <span class="text-danger-500">*</span>
        </label>
        <input
          v-model="form.prenom"
          type="text"
          maxlength="30"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 uppercase"
        >
      </div>

      <!-- Sexe -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.sexe') }} <span class="text-danger-500">*</span>
        </label>
        <select
          v-model="form.sexe"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
          <option value="M">M</option>
          <option value="F">F</option>
        </select>
      </div>

      <!-- Date de naissance -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.naissance') }} <span class="text-danger-500">*</span>
        </label>
        <input
          v-model="form.naissance"
          type="date"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
      </div>

      <!-- Derniere saison -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.derniere_saison') }}
        </label>
        <input
          v-model="form.origine"
          type="text"
          maxlength="4"
          class="w-32 px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
      </div>

      <!-- Numero ICF -->
      <div>
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.icf') }}
        </label>
        <input
          v-model="form.icf"
          type="number"
          class="w-48 px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
      </div>

      <!-- Arbitrage qualification -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('athletes.edit.arb_qualification') }}
          </label>
          <select
            v-model="form.arbQualification"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="opt in qualificationOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('athletes.edit.arb_niveau') }}
          </label>
          <select
            v-model="form.arbNiveau"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="opt in niveauOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Club change autocomplete -->
      <div ref="clubSearchRef" class="relative">
        <label class="block text-sm font-medium text-header-700 mb-1">
          {{ t('athletes.edit.new_club') }}
        </label>
        <div class="relative">
          <input
            v-model="clubSearch"
            type="text"
            :placeholder="t('athletes.edit.new_club_placeholder')"
            class="w-full px-3 py-2 pl-9 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @input="onClubSearchInput"
            @focus="onClubSearchInput"
          >
          <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
          <UIcon v-if="clubSearchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400 animate-spin" />
        </div>
        <!-- Dropdown results -->
        <div
          v-if="clubSearchOpen && clubSearchResults.length > 0"
          class="absolute z-20 mt-1 w-full max-h-48 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
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
    </div>

    <template #footer>
      <button
        class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200"
        @click="emit('close')"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
        :disabled="submitting"
        @click="handleSubmit"
      >
        <UIcon v-if="submitting" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
        {{ t('athletes.edit.submit') }}
      </button>
    </template>
  </AdminModal>
</template>
