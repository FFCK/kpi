<script setup lang="ts">
import type {
  SchemaSearchResult,
  CompetitionCopyDetail,
  CompetitionOptionGroup,
  CompetitionOption
} from '~/types/competition-copy'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t } = useI18n()
const toast = useToast()
const router = useRouter()
const authStore = useAuthStore()
const workContext = useWorkContextStore()
const copyApi = useCompetitionCopyApi()

// --- Search state ---
const searchNbEquipes = ref<number | null>(null)
const searchType = ref<'' | 'CHPT' | 'CP'>('')
const searchTri = ref<'saison' | 'matchs'>('saison')
const searchLoading = ref(false)
const schemas = ref<SchemaSearchResult[]>([])
const hasSearched = ref(false)

// --- Comment edit state ---
const commentModalOpen = ref(false)
const commentSchema = ref<SchemaSearchResult | null>(null)
const commentText = ref('')
const commentSaving = ref(false)

// --- Copy modal state ---
const copyModalOpen = ref(false)
const copyOrigin = ref<CompetitionCopyDetail | null>(null)
const copyOriginLoading = ref(false)
const copyDestSeason = ref('')
const copyDestCompetition = ref('')
const copyDestOptions = ref<CompetitionOptionGroup[]>([])
const copyDestOptionsLoading = ref(false)
const copySaving = ref(false)

// Copy form fields
const copyDateDebut = ref('')
const copyDateFin = ref('')
const copyNom = ref('')
const copyLibelle = ref('')
const copyLieu = ref('')
const copyPlanEau = ref('')
const copyDepartement = ref('')
const copyRespInsc = ref('')
const copyRespR1 = ref('')
const copyOrganisateur = ref('')
const copyDelegue = ref('')
const copyInitPremierTour = ref(false)

// Confirm modal state
const confirmSwitchOpen = ref(false)
const switchTarget = ref<SchemaSearchResult | null>(null)
const confirmCopyOpen = ref(false)

// --- Computed ---
const canAccess = computed(() => authStore.hasProfile(3))

const selectedDestCompetition = computed<CompetitionOption | null>(() => {
  if (!copyDestCompetition.value) return null
  for (const group of copyDestOptions.value) {
    const found = group.options.find(o => o.code === copyDestCompetition.value)
    if (found) return found
  }
  return null
})

// --- Search ---
const doSearch = async () => {
  if (!searchNbEquipes.value || searchNbEquipes.value <= 0) return

  searchLoading.value = true
  hasSearched.value = true
  try {
    schemas.value = await copyApi.searchSchemas(
      searchNbEquipes.value,
      searchType.value,
      searchTri.value
    )
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('competitionCopy.search.search'),
      color: 'error',
      duration: 3000,
    })
  } finally {
    searchLoading.value = false
  }
}

// --- Display helpers ---
const getDisplayLabel = (s: SchemaSearchResult) => {
  const lines: string[] = []
  if (s.titreActif) {
    lines.push(s.libelle)
  } else if (s.soustitre) {
    lines.push(s.soustitre)
  } else {
    lines.push(s.libelle)
  }
  if (s.soustitre2) lines.push(s.soustitre2)
  return lines
}

const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-primary-100 text-primary-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-header-100 text-header-800'
  }
}

// --- Switch to competition ---
const askSwitch = (schema: SchemaSearchResult) => {
  switchTarget.value = schema
  confirmSwitchOpen.value = true
}

const doSwitch = () => {
  if (!switchTarget.value) return
  confirmSwitchOpen.value = false

  // Change work context and navigate
  workContext.setSeason(switchTarget.value.season)
  workContext.selectCompetition(switchTarget.value.code)
  router.push('/competitions')
}

// --- Comment edit ---
const openCommentModal = (schema: SchemaSearchResult) => {
  commentSchema.value = schema
  commentText.value = schema.commentaires || ''
  commentModalOpen.value = true
}

const saveComment = async () => {
  if (!commentSchema.value) return
  commentSaving.value = true
  try {
    await copyApi.updateComments(
      commentSchema.value.season,
      commentSchema.value.code,
      commentText.value
    )
    // Update local data
    commentSchema.value.commentaires = commentText.value || null
    commentModalOpen.value = false
    toast.add({
      title: t('competitionCopy.comments.success'),
      color: 'success',
      duration: 3000,
    })
  } catch {
    toast.add({
      title: t('competitionCopy.comments.error'),
      color: 'error',
      duration: 3000,
    })
  } finally {
    commentSaving.value = false
  }
}

// --- Copy modal ---
const openCopyModal = async (schema: SchemaSearchResult) => {
  copyModalOpen.value = true
  copyOriginLoading.value = true
  copyOrigin.value = null

  // Reset form
  copyDestSeason.value = workContext.season || ''
  copyDestCompetition.value = ''
  copyInitPremierTour.value = false

  try {
    copyOrigin.value = await copyApi.getCopyDetail(schema.season, schema.code)

    // Prefill form fields
    const p = copyOrigin.value.prefill
    copyDateDebut.value = p.dateDebut || ''
    copyDateFin.value = p.dateFin || ''
    copyNom.value = p.nom || ''
    copyLibelle.value = p.libelle || ''
    copyLieu.value = p.lieu || ''
    copyPlanEau.value = p.planEau || ''
    copyDepartement.value = p.departement || ''
    copyRespInsc.value = p.responsableInsc || ''
    copyRespR1.value = p.responsableR1 || ''
    copyOrganisateur.value = p.organisateur || ''
    copyDelegue.value = p.delegue || ''

    // Load destination options
    await loadDestOptions()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || '',
      color: 'error',
      duration: 3000,
    })
    copyModalOpen.value = false
  } finally {
    copyOriginLoading.value = false
  }
}

const loadDestOptions = async () => {
  if (!copyDestSeason.value) return
  copyDestOptionsLoading.value = true
  try {
    copyDestOptions.value = await copyApi.getCompetitionOptions(copyDestSeason.value)
  } catch {
    copyDestOptions.value = []
  } finally {
    copyDestOptionsLoading.value = false
  }
}

// Watch destination season changes
watch(copyDestSeason, async () => {
  copyDestCompetition.value = ''
  await loadDestOptions()
})

const askCopy = () => {
  if (!copyOrigin.value || !copyDestCompetition.value) return
  confirmCopyOpen.value = true
}

const doCopy = async () => {
  if (!copyOrigin.value || !copyDestCompetition.value) return
  confirmCopyOpen.value = false
  copySaving.value = true

  try {
    const result = await copyApi.copyCompetition({
      originSeason: copyOrigin.value.season,
      originCompetition: copyOrigin.value.code,
      destinationSeason: copyDestSeason.value,
      destinationCompetition: copyDestCompetition.value,
      dateDebut: copyDateDebut.value || null,
      dateFin: copyDateFin.value || null,
      nom: copyNom.value || null,
      libelle: copyLibelle.value || null,
      lieu: copyLieu.value || null,
      planEau: copyPlanEau.value || null,
      departement: copyDepartement.value || null,
      responsableInsc: copyRespInsc.value || null,
      responsableR1: copyRespR1.value || null,
      organisateur: copyOrganisateur.value || null,
      delegue: copyDelegue.value || null,
      initPremierTour: copyInitPremierTour.value,
    })

    toast.add({
      title: t('competitionCopy.copy.success', {
        journees: result.journeesCreated,
        matchs: result.matchsCreated,
      }),
      color: 'success',
      duration: 5000,
    })
    copyModalOpen.value = false
  } catch (error: unknown) {
    toast.add({
      title: t('competitionCopy.copy.error'),
      description: (error as { message?: string })?.message || '',
      color: 'error',
      duration: 5000,
    })
  } finally {
    copySaving.value = false
  }
}

// --- Init ---
onMounted(async () => {
  await workContext.initContext()
})
</script>

<template>
  <div v-if="canAccess" class="p-4">
    <!-- Page Header -->
    <AdminPageHeader :title="t('competitionCopy.title')" :show-filters="false" />

    <!-- Search Filters -->
    <div class="bg-white border border-header-200 rounded-lg p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-end">
        <!-- Nb Equipes -->
        <div class="w-32">
          <label class="block text-xs font-medium text-header-500 mb-1">
            {{ t('competitionCopy.search.nbEquipes') }}
          </label>
          <input
            v-model.number="searchNbEquipes"
            type="number"
            min="1"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @keydown.enter="doSearch"
          >
        </div>

        <!-- Type filter -->
        <div class="w-40">
          <label class="block text-xs font-medium text-header-500 mb-1">
            {{ t('competitionCopy.search.type') }}
          </label>
          <select
            v-model="searchType"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">{{ t('competitionCopy.search.typeAll') }}</option>
            <option value="CHPT">CHPT</option>
            <option value="CP">CP</option>
          </select>
        </div>

        <!-- Sort -->
        <div class="w-40">
          <label class="block text-xs font-medium text-header-500 mb-1">
            {{ t('competitionCopy.search.sortBy') }}
          </label>
          <select
            v-model="searchTri"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="saison">{{ t('competitionCopy.search.sortSeason') }}</option>
            <option value="matchs">{{ t('competitionCopy.search.sortMatches') }}</option>
          </select>
        </div>

        <!-- Search button -->
        <button
          class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors disabled:opacity-50"
          :disabled="!searchNbEquipes || searchNbEquipes <= 0 || searchLoading"
          @click="doSearch"
        >
          <span v-if="searchLoading" class="flex items-center gap-2">
            <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
            {{ t('competitionCopy.search.search') }}
          </span>
          <span v-else>{{ t('competitionCopy.search.search') }}</span>
        </button>
      </div>
    </div>

    <!-- Results -->
    <div v-if="hasSearched" class="bg-white border border-header-200 rounded-lg overflow-hidden">
      <!-- Disclaimer -->
      <div class="px-4 py-2 bg-amber-50 border-b border-amber-200 text-sm text-amber-700 flex items-center gap-2">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
        {{ t('competitionCopy.search.disclaimer') }}
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-header-50 border-b border-header-200">
            <tr>
              <th class="px-3 py-2 text-left font-medium text-header-600">{{ t('competitionCopy.table.season') }}</th>
              <th class="px-3 py-2 text-left font-medium text-header-600">{{ t('competitionCopy.table.code') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.type') }}</th>
              <th class="px-3 py-2 text-left font-medium text-header-600">{{ t('competitionCopy.table.level') }}</th>
              <th class="px-3 py-2 text-left font-medium text-header-600">{{ t('competitionCopy.table.label') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.teams') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.pitches') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.rounds') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.phases') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.matches') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.encoded') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.info') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.viewSchema') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.switchTo') }}</th>
              <th class="px-3 py-2 text-center font-medium text-header-600">{{ t('competitionCopy.table.copyTo') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="schema in schemas"
              :key="`${schema.code}-${schema.season}`"
              class="border-b border-header-100 hover:bg-header-50"
            >
              <td class="px-3 py-2 text-header-900 font-medium">{{ schema.season }}</td>
              <td class="px-3 py-2 text-header-900 font-mono text-xs">{{ schema.code }}</td>
              <td class="px-3 py-2 text-center">
                <span class="px-1.5 py-0.5 text-xs font-medium rounded bg-header-100 text-header-700">
                  {{ schema.codeTypeclt }}
                </span>
              </td>
              <td class="px-3 py-2">
                <span class="px-1.5 py-0.5 text-xs font-medium rounded" :class="getLevelColor(schema.codeNiveau)">
                  {{ schema.codeNiveau }}
                </span>
              </td>
              <td class="px-3 py-2">
                <div v-for="(line, idx) in getDisplayLabel(schema)" :key="idx" class="text-header-900" :class="idx > 0 ? 'text-header-500 text-xs' : ''">
                  {{ line }}
                </div>
                <div v-if="schema.codeTour === '10'" class="text-xs text-header-500 italic">
                  {{ t('competitionCopy.table.final') }}
                </div>
              </td>
              <td class="px-3 py-2 text-center text-header-700">{{ schema.nbEquipes }}</td>
              <td class="px-3 py-2 text-center text-header-700">{{ schema.nbTerrains }}</td>
              <td class="px-3 py-2 text-center text-header-700">{{ schema.nbTours }}</td>
              <td class="px-3 py-2 text-center text-header-700">{{ schema.nbPhases }}</td>
              <td class="px-3 py-2 text-center text-header-700 font-medium">{{ schema.nbMatchs }}</td>
              <td class="px-3 py-2 text-center">
                <UIcon
                  :name="schema.matchsEncodes ? 'i-heroicons-check-circle-solid' : 'i-heroicons-x-circle-solid'"
                  class="w-6 h-6"
                  :class="schema.matchsEncodes ? 'text-success-500' : 'text-header-300'"
                />
              </td>
              <td class="px-3 py-2 text-center">
                <button
                  class="p-1 rounded hover:bg-header-200 transition-colors"
                  :class="schema.commentaires ? 'text-primary-600' : 'text-header-400'"
                  :title="schema.commentaires || t('competitionCopy.table.info')"
                  @click="openCommentModal(schema)"
                >
                  <UIcon name="i-heroicons-chat-bubble-left-ellipsis" class="w-6 h-6" />
                </button>
              </td>
              <!-- View schema (new tab) -->
              <td class="px-3 py-2 text-center">
                <NuxtLink
                  :to="`/gamedays/schema?competition=${schema.code}&season=${schema.season}`"
                  target="_blank"
                  class="p-1.5 rounded text-primary-500 hover:text-primary-600 hover:bg-primary-50 transition-colors inline-block"
                  :title="t('competitionCopy.table.viewSchema')"
                >
                  <UIcon name="i-heroicons-rectangle-group" class="w-6 h-6" />
                </NuxtLink>
              </td>
              <!-- Switch to -->
              <td class="px-3 py-2 text-center">
                <button
                  class="p-1.5 rounded text-warning-500 hover:text-warning-600 hover:bg-warning-50 transition-colors"
                  :title="t('competitionCopy.table.switchTo')"
                  @click="askSwitch(schema)"
                >
                  <UIcon name="i-heroicons-arrow-right-end-on-rectangle" class="w-6 h-6" />
                </button>
              </td>
              <!-- Copy to -->
              <td class="px-3 py-2 text-center">
                <button
                  class="p-1.5 rounded text-primary-500 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                  :title="t('competitionCopy.table.copyTo')"
                  @click="openCopyModal(schema)"
                >
                  <UIcon name="i-heroicons-document-duplicate" class="w-6 h-6" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- No results -->
        <div v-if="schemas.length === 0 && !searchLoading" class="px-4 py-8 text-center text-header-500">
          {{ t('competitionCopy.table.noResults') }}
        </div>

        <!-- Loading -->
        <div v-if="searchLoading" class="px-4 py-8 text-center text-header-500">
          <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin mx-auto" />
        </div>
      </div>
    </div>

    <!-- ===== COMMENT EDIT MODAL ===== -->
    <AdminModal
      :open="commentModalOpen"
      :title="commentSchema ? t('competitionCopy.comments.title', { code: commentSchema.code, season: commentSchema.season }) : ''"
      max-width="md"
      @close="commentModalOpen = false"
    >
      <div class="space-y-4">
        <textarea
          v-model="commentText"
          rows="5"
          class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
        <div class="flex justify-end gap-3">
          <button
            class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200 transition-colors"
            @click="commentModalOpen = false"
          >
            {{ t('competitionCopy.comments.cancel') }}
          </button>
          <button
            class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50"
            :disabled="commentSaving"
            @click="saveComment"
          >
            {{ t('competitionCopy.comments.save') }}
          </button>
        </div>
      </div>
    </AdminModal>

    <!-- ===== COPY MODAL ===== -->
    <AdminModal
      :open="copyModalOpen"
      :title="t('competitionCopy.copy.title')"
      max-width="xl"
      @close="copyModalOpen = false"
    >
      <div v-if="copyOriginLoading" class="py-8 text-center text-header-500">
        <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin mx-auto" />
      </div>

      <div v-else-if="copyOrigin" class="space-y-6">
        <!-- Origin summary -->
        <div class="bg-header-50 border border-header-200 rounded-lg p-4">
          <h3 class="text-sm font-semibold text-header-700 mb-2">{{ t('competitionCopy.copy.origin') }}</h3>
          <div class="flex flex-wrap gap-3 text-sm text-header-600">
            <span class="font-medium text-header-900">{{ copyOrigin.season }}</span>
            <span class="font-mono">{{ copyOrigin.code }}</span>
            <span class="px-1.5 py-0.5 text-xs font-medium rounded bg-header-200">{{ copyOrigin.codeTypeclt }}</span>
            <span>{{ copyOrigin.nbEquipes }} {{ t('competitionCopy.copy.teams') }}</span>
            <span>{{ copyOrigin.nbMatchs }} {{ t('competitionCopy.copy.matches') }}</span>
          </div>
          <!-- Phases -->
          <div v-if="copyOrigin.journees.length > 0" class="mt-2">
            <span class="text-xs font-medium text-header-500">{{ t('competitionCopy.copy.phases') }}:</span>
            <span class="text-xs text-header-600 ml-1">
              {{ copyOrigin.journees.map(j => j.phase).join(' > ') }}
            </span>
          </div>
        </div>

        <!-- Destination -->
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
          <h3 class="text-sm font-semibold text-header-700 mb-3">{{ t('competitionCopy.copy.destination') }}</h3>
          <div class="flex flex-wrap gap-4 items-end">
            <!-- Season -->
            <div class="w-32">
              <label class="block text-xs font-medium text-header-500 mb-1">{{ t('competitionCopy.copy.season') }}</label>
              <select
                v-model="copyDestSeason"
                class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
              >
                <option v-for="s in workContext.seasons" :key="s.code" :value="s.code">
                  {{ s.code }}
                </option>
              </select>
            </div>
            <!-- Competition -->
            <div class="flex-1 min-w-48">
              <label class="block text-xs font-medium text-header-500 mb-1">{{ t('competitionCopy.copy.competition') }}</label>
              <select
                v-model="copyDestCompetition"
                class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                :disabled="copyDestOptionsLoading"
              >
                <option value="">--</option>
                <optgroup v-for="group in copyDestOptions" :key="group.label" :label="group.label">
                  <option v-for="opt in group.options" :key="opt.code" :value="opt.code">
                    {{ opt.code }} - {{ opt.libelle }}
                  </option>
                </optgroup>
              </select>
            </div>
          </div>
          <!-- Destination info -->
          <div v-if="selectedDestCompetition" class="mt-2 flex flex-wrap gap-3 text-xs text-header-600">
            <span>{{ t('competitionCopy.copy.type') }}: <strong>{{ selectedDestCompetition.codeTypeclt }}</strong></span>
            <span>{{ selectedDestCompetition.nbEquipes }} {{ t('competitionCopy.copy.teams') }}</span>
            <span>{{ t('competitionCopy.copy.qualified') }}: {{ selectedDestCompetition.qualifies }}</span>
            <span>{{ t('competitionCopy.copy.eliminated') }}: {{ selectedDestCompetition.elimines }}</span>
          </div>
        </div>

        <!-- Common values -->
        <div class="border border-header-200 rounded-lg p-4">
          <h3 class="text-sm font-semibold text-header-700 mb-1">{{ t('competitionCopy.copy.commonValues') }}</h3>
          <p class="text-xs text-header-500 mb-4 flex items-center gap-1">
            <UIcon name="i-heroicons-information-circle" class="w-4 h-4 shrink-0" />
            {{ t('competitionCopy.copy.commonValuesHelp') }}
          </p>

          <!-- Public params -->
          <p class="text-xs font-medium text-header-500 mb-2">{{ t('competitionCopy.copy.publicParams') }}</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.dateDebut') }}</label>
              <input v-model="copyDateDebut" type="date" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.dateFin') }}</label>
              <input v-model="copyDateFin" type="date" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.lieu') }}</label>
              <input v-model="copyLieu" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.departement') }}</label>
              <input v-model="copyDepartement" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.nom') }}</label>
              <input v-model="copyNom" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.planEau') }}</label>
              <input v-model="copyPlanEau" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
          </div>

          <!-- Responsables -->
          <p class="text-xs font-medium text-header-500 mb-2">{{ t('competitionCopy.copy.responsables') }}</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.organisateur') }}</label>
              <input v-model="copyOrganisateur" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.responsableR1') }}</label>
              <input v-model="copyRespR1" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.responsableInsc') }}</label>
              <input v-model="copyRespInsc" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
              <label class="block text-xs text-header-500 mb-1">{{ t('competitionCopy.copy.delegue') }}</label>
              <input v-model="copyDelegue" type="text" class="w-full px-3 py-1.5 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
          </div>
        </div>

        <!-- Init premier tour -->
        <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
          <input
            id="initPremierTour"
            v-model="copyInitPremierTour"
            type="checkbox"
            class="mt-0.5 rounded border-header-300 text-primary-600 focus:ring-primary-500"
          >
          <div>
            <label for="initPremierTour" class="text-sm text-header-700 cursor-pointer">
              {{ t('competitionCopy.copy.initFirstRound') }}
            </label>
            <p class="text-xs text-amber-600 mt-0.5">
              {{ t('competitionCopy.copy.initFirstRoundWarning') }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-2 border-t border-header-200">
          <button
            class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200 transition-colors"
            @click="copyModalOpen = false"
          >
            {{ t('competitionCopy.copy.cancel') }}
          </button>
          <button
            class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50"
            :disabled="!copyDestCompetition || copySaving"
            @click="askCopy"
          >
            <span v-if="copySaving" class="flex items-center gap-2">
              <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('competitionCopy.copy.submit') }}
            </span>
            <span v-else>{{ t('competitionCopy.copy.submit') }}</span>
          </button>
        </div>
      </div>
    </AdminModal>

    <!-- ===== CONFIRM SWITCH MODAL ===== -->
    <AdminConfirmModal
      :open="confirmSwitchOpen"
      :title="t('competitionCopy.table.switchTo')"
      :message="switchTarget ? t('competitionCopy.confirm.switchTo', { code: switchTarget.code, season: switchTarget.season }) : ''"
      variant="info"
      @close="confirmSwitchOpen = false"
      @confirm="doSwitch"
    />

    <!-- ===== CONFIRM COPY MODAL ===== -->
    <AdminConfirmModal
      :open="confirmCopyOpen"
      :title="t('competitionCopy.copy.title')"
      :message="copyOrigin && copyDestCompetition ? t('competitionCopy.copy.confirmCopy', { origin: `${copyOrigin.code} (${copyOrigin.season})`, destination: `${copyDestCompetition} (${copyDestSeason})` }) : ''"
      :loading="copySaving"
      variant="warning"
      @close="confirmCopyOpen = false"
      @confirm="doCopy"
    />
  </div>
</template>
