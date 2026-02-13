<script setup lang="ts">
import type { Player, AddPlayerFormData, CopyCompositionFormData, AvailableComposition } from '~/types/presence'
import type { PlayerAutocomplete } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const api = useApi()
const toast = useToast()
const config = useRuntimeConfig()
const authStore = useAuthStore()
const workContext = useWorkContextStore()
const presenceStore = usePresenceStore()

// Extract teamId from route
const teamId = computed(() => parseInt(route.params.teamId as string))

// Permissions
const { canEdit, canDelete, canCopy, canCreatePlayer, canSearchLicense } = usePresencePermissions(
  'team',
  computed(() => presenceStore.isLocked)
)

// Player validation
const { isPlayerValid, requiresSurclassement, getValidationErrors } = usePlayerValidation()

// Selection state
const selectedPlayerIds = ref<number[]>([])
const selectAll = ref(false)

// Inline editing state
const editingCell = ref<{ matric: number; field: 'numero' | 'capitaine' } | null>(null)
const editingValue = ref('')

// Search filter
const search = ref('')

// Add player modal
const addModalOpen = ref(false)
const addMode = ref<'existing' | 'create'>('existing')
const addFormData = ref<AddPlayerFormData>({ mode: 'existing', capitaine: '-' })
const addFormError = ref('')
const addFormSaving = ref(false)

// Player search (existing players)
const selectedPlayer = ref<PlayerAutocomplete | null>(null)

// Copy composition modal
const copyModalOpen = ref(false)
const copyFormData = ref<CopyCompositionFormData>({ sourceCompetition: '', sourceSeason: '' })
const availableCompositions = ref<AvailableComposition[]>([])
const loadingCompositions = ref(false)

// Delete modal
const bulkDeleteModalOpen = ref(false)
const isDeleting = ref(false)

// Sibling teams (same competition/season) for quick navigation
const siblingTeams = ref<{ id: number; libelle: string }[]>([])

// Load sibling teams for dropdown
const loadSiblingTeams = async () => {
  if (!presenceStore.competition || !presenceStore.team) return
  try {
    const response = await api.get<{ teams: { id: number; libelle: string }[] }>('/admin/competition-teams', {
      season: presenceStore.team.codeSaison,
      competition: presenceStore.competition.code
    })
    siblingTeams.value = (response.teams || []).map(t => ({ id: t.id, libelle: t.libelle }))
  } catch (error) {
    console.error('Failed to load sibling teams:', error)
  }
}

// Navigate to another team
const navigateToTeam = (newTeamId: number) => {
  if (newTeamId === teamId.value) return
  router.push(`/presence/team/${newTeamId}`)
}

// Watch route changes to reload when navigating between teams
watch(teamId, async (newId) => {
  if (newId && presenceStore.initialized) {
    await presenceStore.initTeamMode(newId, api)
    await loadSiblingTeams()
  }
})

// Initialize on mount
onMounted(async () => {
  await workContext.initContext()
  await presenceStore.initTeamMode(teamId.value, api)
  await loadSiblingTeams()
})

// Computed
const filteredPlayers = computed(() => {
  if (!search.value) return presenceStore.players
  const query = search.value.toLowerCase()
  return presenceStore.players.filter(p =>
    p.nom.toLowerCase().includes(query) ||
    p.prenom.toLowerCase().includes(query) ||
    p.matric.toString().includes(query) ||
    (p.icf && p.icf.toString().includes(query))
  )
})

const activePlayers = computed(() => filteredPlayers.value.filter(p => ['-', 'C'].includes(p.capitaine)))
const coaches = computed(() => filteredPlayers.value.filter(p => p.capitaine === 'E'))
const referees = computed(() => filteredPlayers.value.filter(p => p.capitaine === 'A'))
const inactivePlayers = computed(() => filteredPlayers.value.filter(p => p.capitaine === 'X'))

// Season options for copy modal (current + 5 previous)
const seasonOptions = computed(() => {
  const current = parseInt(presenceStore.team?.codeSaison || new Date().getFullYear().toString())
  return Array.from({ length: 6 }, (_, i) => (current - i).toString())
})

// Actions
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedPlayerIds.value = filteredPlayers.value.map(p => p.matric)
  } else {
    selectedPlayerIds.value = []
  }
}

const toggleSelect = (matric: number) => {
  const index = selectedPlayerIds.value.indexOf(matric)
  if (index > -1) {
    selectedPlayerIds.value.splice(index, 1)
  } else {
    selectedPlayerIds.value.push(matric)
  }
}

// Inline editing
const startEdit = (player: Player, field: 'numero' | 'capitaine') => {
  if (!canEdit.value) return
  editingCell.value = { matric: player.matric, field }
  editingValue.value = field === 'numero' ? (player.numero || '').toString() : player.capitaine
  nextTick(() => {
    const input = document.getElementById(`inline-edit-${player.matric}-${field}`)
    if (input) input.focus()
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return

  const { matric, field } = editingCell.value
  const value = field === 'numero' ? parseInt(editingValue.value) || 0 : editingValue.value

  try {
    await presenceStore.updatePlayerInline(matric, field, value, api)
    editingCell.value = null
    toast.add({ title: t('common.saved'), color: 'success', timeout: 2000 })
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

const handleInlineKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') {
    saveInlineEdit()
  } else if (e.key === 'Escape') {
    editingCell.value = null
  }
}

// Add player - handle selection from autocomplete
const onPlayerSelected = (player: PlayerAutocomplete | null) => {
  selectedPlayer.value = player
  if (player) {
    addFormData.value.matric = player.matric
  }
}

// Add player - submit
const addExistingPlayer = async () => {
  if (!selectedPlayer.value) return

  addFormSaving.value = true
  addFormError.value = ''

  try {
    await presenceStore.addPlayer({
      mode: 'existing',
      matric: selectedPlayer.value.matric,
      numero: addFormData.value.numero,
      capitaine: addFormData.value.capitaine
    }, api)

    toast.add({ title: t('presence.player_added'), color: 'success' })
    addModalOpen.value = false
    resetAddForm()
  } catch (error: any) {
    addFormError.value = error.message || t('presence.add_player_failed')
  } finally {
    addFormSaving.value = false
  }
}

const createNewPlayer = async () => {
  if (!addFormData.value.nom || !addFormData.value.prenom || !addFormData.value.sexe) {
    addFormError.value = t('presence.required_fields')
    return
  }

  addFormSaving.value = true
  addFormError.value = ''

  try {
    await presenceStore.addPlayer({
      mode: 'create',
      ...addFormData.value
    }, api)

    toast.add({ title: t('presence.player_created'), color: 'success' })
    addModalOpen.value = false
    resetAddForm()
  } catch (error: any) {
    addFormError.value = error.message || t('presence.create_player_failed')
  } finally {
    addFormSaving.value = false
  }
}

const resetAddForm = () => {
  addFormData.value = { mode: 'existing', capitaine: '-', sexe: undefined, arbitre: '', niveau: '' }
  selectedPlayer.value = null
  addFormError.value = ''
}

// Copy composition
const openCopyModal = async () => {
  copyModalOpen.value = true
  copyFormData.value.sourceSeason = presenceStore.team?.codeSaison || ''
  await loadSourceCompetitions()
}

const loadSourceCompetitions = async () => {
  loadingCompositions.value = true
  try {
    availableCompositions.value = await presenceStore.getAvailableCompositions(
      copyFormData.value.sourceSeason, api
    )
  } catch (error) {
    console.error('Failed to load compositions:', error)
  } finally {
    loadingCompositions.value = false
  }
}

const copyComposition = async () => {
  if (!copyFormData.value.sourceCompetition) return

  try {
    await presenceStore.copyComposition(copyFormData.value, api)
    toast.add({ title: t('presence.composition_copied'), color: 'success' })
    copyModalOpen.value = false
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

// Delete
const confirmBulkDelete = () => {
  if (selectedPlayerIds.value.length === 0) return
  bulkDeleteModalOpen.value = true
}

const bulkDelete = async () => {
  isDeleting.value = true
  try {
    await presenceStore.deletePlayers(selectedPlayerIds.value, api)
    toast.add({
      title: t('presence.players_deleted', { count: selectedPlayerIds.value.length }),
      color: 'success'
    })
    selectedPlayerIds.value = []
    selectAll.value = false
    bulkDeleteModalOpen.value = false
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  } finally {
    isDeleting.value = false
  }
}

const deletePlayer = async (matric: number) => {
  try {
    await presenceStore.deletePlayers([matric], api)
    toast.add({ title: t('presence.player_deleted'), color: 'success' })
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

// License display: show ICF (Reserve) if available, otherwise Matric
// Show season in parentheses if older than working season
const getLicenseDisplay = (player: Player): string => {
  const licenseNumber = player.icf ? `ICF-${player.icf}` : player.matric.toString()
  const workingSeason = presenceStore.team?.codeSaison || ''
  if (player.origine && workingSeason && player.origine < workingSeason) {
    return `${licenseNumber} (${player.origine})`
  }
  return licenseNumber
}

// PDF Links
const pdfLinks = computed(() => {
  if (!presenceStore.team || !presenceStore.competition) return {}

  const params = new URLSearchParams({
    team: teamId.value.toString(),
    compet: presenceStore.competition.code,
    season: presenceStore.team.codeSaison.toString()
  })

  return {
    fr: `${config.public.legacyBaseUrl}/admin/FeuillePresence.php?${params}`,
    en: `${config.public.legacyBaseUrl}/admin/FeuillePresenceEN.php?${params}`,
    photo: `${config.public.legacyBaseUrl}/admin/FeuillePresencePhoto.php?${params}`,
    visa: `${config.public.legacyBaseUrl}/admin/FeuillePresenceVisa.php?${params}`
  }
})
</script>

<template>
  <div>
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Page Header -->
    <div class="mb-4 bg-white rounded-lg shadow p-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <!-- Title + Team selector -->
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <UIcon name="i-heroicons-clipboard-document-list" class="w-6 h-6 text-blue-600 shrink-0" />
            <h1 class="text-xl font-bold text-gray-900">
              {{ t('presence.title_team') }}
            </h1>
          </div>

          <div v-if="presenceStore.team" class="flex flex-wrap items-center gap-2 text-sm">
            <span class="text-gray-500">{{ presenceStore.competition?.code }} - {{ presenceStore.team.codeSaison }}</span>
            <span class="text-gray-400">•</span>
            <!-- Team dropdown selector -->
            <select
              v-if="siblingTeams.length >= 2"
              :value="teamId"
              class="font-semibold text-gray-900 border border-gray-300 rounded-md px-2 py-1 text-sm bg-white hover:border-blue-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 cursor-pointer max-w-xs"
              @change="navigateToTeam(Number(($event.target as HTMLSelectElement).value))"
            >
              <option
                v-for="st in siblingTeams"
                :key="st.id"
                :value="st.id"
              >
                {{ st.libelle }}
              </option>
            </select>
            <span v-else class="font-semibold text-gray-900">{{ presenceStore.team.libelle }}</span>
          </div>
        </div>

        <!-- Competition badges and lock indicator -->
        <div v-if="presenceStore.competition" class="flex items-center gap-2 flex-wrap">
          <span
            v-if="presenceStore.competition.codeNiveau"
            class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800"
          >
            {{ presenceStore.competition.codeNiveau }}
          </span>

          <!-- National competition badge -->
          <span
            v-if="presenceStore.isNationalCompetition"
            class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800 flex items-center gap-1"
            :title="t('presence.national_validation_required')"
          >
            <UIcon name="i-heroicons-shield-check" class="w-3 h-3" />
            {{ t('common.national') }}
          </span>

          <!-- Lock indicator (read-only) -->
          <div
            v-if="presenceStore.isLocked"
            class="flex items-center gap-1 px-2 py-1 rounded bg-red-50 text-red-700"
            :title="t('presence.competition_locked')"
          >
            <UIcon name="i-heroicons-lock-closed-solid" class="w-4 h-4" />
            <span class="text-xs font-medium">{{ t('common.locked') }}</span>
          </div>
          <UIcon
            v-else
            name="i-heroicons-lock-open-solid"
            class="w-5 h-5 text-gray-400"
            :title="t('common.unlocked')"
          />
        </div>
      </div>

      <!-- Lock notice -->
      <div
        v-if="presenceStore.isLocked"
        class="mt-3 flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800"
      >
        <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
        {{ t('presence.competition_locked_notice') }}
      </div>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('presence.search_player')"
      :add-label="t('presence.add_player')"
      :show-add="canEdit"
      :show-bulk-delete="canEdit"
      :bulk-delete-label="t('common.delete_selected')"
      :selected-count="selectedPlayerIds.length"
      @add="addModalOpen = true"
      @bulk-delete="confirmBulkDelete"
    >
      <template #left>
        <button
          v-if="canCopy"
          class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
          @click="openCopyModal"
        >
          <UIcon name="i-heroicons-document-duplicate" class="w-4 h-4 inline mr-1" />
          {{ t('presence.copy_from') }}
        </button>

        <a
          v-for="(link, key) in pdfLinks"
          :key="key"
          :href="link"
          target="_blank"
          class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors"
        >
          <UIcon name="i-heroicons-document-text" class="w-4 h-4 inline mr-1" />
          {{ t(`presence.pdf_${key}`) }}
        </a>
      </template>
    </AdminToolbar>

    <!-- Loading state -->
    <div v-if="presenceStore.loading" class="text-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin mx-auto text-blue-600" />
      <p class="mt-2 text-sm text-gray-500">{{ t('common.loading') }}</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredPlayers.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
      <UIcon name="i-heroicons-user-group" class="w-12 h-12 mx-auto text-gray-400" />
      <p class="mt-2 text-sm text-gray-500">{{ t('presence.no_players') }}</p>
    </div>

    <!-- Desktop Table -->
    <div v-else class="hidden lg:block bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th v-if="canEdit" class="w-10 px-3 py-1">
              <input
                v-model="selectAll"
                type="checkbox"
                class="rounded border-gray-300"
                @change="toggleSelectAll"
              />
            </th>
            <th class="w-16 px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">#</th>
            <th class="w-12 px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">Cap</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.last_name') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.first_name') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.license') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.category') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.paddle') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.certificate') }}</th>
            <th v-if="canEdit" class="w-16 px-3 py-1"></th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Active players (-, C) -->
          <tr
            v-for="player in activePlayers"
            :key="player.matric"
            class="hover:bg-gray-50"
            :class="{ 'bg-yellow-50': player.capitaine === 'C' }"
          >
            <td v-if="canEdit" class="px-3 py-1">
              <input
                v-model="selectedPlayerIds"
                type="checkbox"
                :value="player.matric"
                class="rounded border-gray-300"
              />
            </td>

            <!-- Numero (inline edit) -->
            <td class="px-3 py-1 text-sm text-gray-900">
              <span
                v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
                :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                @click="canEdit && startEdit(player, 'numero')"
              >
                {{ player.numero || '-' }}
              </span>
              <input
                v-else
                :id="`inline-edit-${player.matric}-numero`"
                v-model.number="editingValue"
                type="number"
                min="0"
                max="99"
                class="w-16 px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              />
            </td>

            <!-- Capitaine (inline edit) -->
            <td class="px-3 py-1 text-sm">
              <span
                v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
                :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                @click="canEdit && startEdit(player, 'capitaine')"
              >
                {{ player.capitaine }}
              </span>
              <select
                v-else
                :id="`inline-edit-${player.matric}-capitaine`"
                v-model="editingValue"
                class="px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                @change="saveInlineEdit"
                @blur="saveInlineEdit"
              >
                <option value="-">-</option>
                <option value="C">C</option>
                <option value="E">E</option>
                <option value="A">A</option>
                <option value="X">X</option>
              </select>
            </td>

            <td class="px-3 py-1 text-sm font-medium text-gray-900">{{ player.nom }}</td>
            <td class="px-3 py-1 text-sm text-gray-900">{{ player.prenom }}</td>
            <td class="px-3 py-1 text-sm text-gray-500 font-mono">
              {{ getLicenseDisplay(player) }}
            </td>
            <td class="px-3 py-1 text-sm text-gray-500">{{ player.categ }}-{{ player.sexe }}</td>

            <!-- Pagaie with validation -->
            <td class="px-3 py-1 text-sm">
              <span
                v-if="player.pagaieValide === 0"
                class="text-red-600"
                :title="t('presence.invalid_paddle')"
              >
                ({{ player.pagaieLabel }})
              </span>
              <span v-else class="text-gray-700">
                {{ player.pagaieLabel }}
              </span>
            </td>

            <!-- Certificate -->
            <td class="px-3 py-1 text-sm">
              <span
                v-if="player.certifCK === 'OUI'"
                class="text-green-600"
              >
                {{ t('common.yes') }}
              </span>
              <span v-else class="text-red-600">
                {{ t('common.no') }}
              </span>
            </td>

            <!-- Actions -->
            <td v-if="canEdit" class="px-3 py-1 text-right">
              <button
                class="text-red-600 hover:text-red-800"
                @click="deletePlayer(player.matric)"
              >
                <UIcon name="i-heroicons-trash" class="w-5 h-5" />
              </button>
            </td>
          </tr>

          <!-- Coaches (E) -->
          <template v-if="coaches.length > 0">
            <tr class="bg-gray-100">
              <td :colspan="canEdit ? 10 : 9" class="px-3 py-1 text-xs text-gray-500 text-center">
                {{ t('presence.section_coaches') }}
              </td>
            </tr>
            <tr
              v-for="player in coaches"
              :key="player.matric"
              class="hover:bg-gray-50 bg-orange-50/50"
            >
              <td v-if="canEdit" class="px-3 py-1">
                <input v-model="selectedPlayerIds" type="checkbox" :value="player.matric" class="rounded border-gray-300" />
              </td>
              <!-- Numero (inline edit) -->
              <td class="px-3 py-1 text-sm text-gray-900">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'numero')"
                >
                  {{ player.numero || '-' }}
                </span>
                <input
                  v-else
                  :id="`inline-edit-${player.matric}-numero`"
                  v-model.number="editingValue"
                  type="number" min="0" max="99"
                  class="w-16 px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @keydown="handleInlineKeydown"
                  @blur="saveInlineEdit"
                />
              </td>
              <!-- Capitaine (inline edit) -->
              <td class="px-3 py-1 text-sm">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'capitaine')"
                >
                  {{ player.capitaine }}
                </span>
                <select
                  v-else
                  :id="`inline-edit-${player.matric}-capitaine`"
                  v-model="editingValue"
                  class="px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @change="saveInlineEdit"
                  @blur="saveInlineEdit"
                >
                  <option value="-">-</option>
                  <option value="C">C</option>
                  <option value="E">E</option>
                  <option value="A">A</option>
                  <option value="X">X</option>
                </select>
              </td>
              <td class="px-3 py-1 text-sm font-medium text-gray-900">{{ player.nom }}</td>
              <td class="px-3 py-1 text-sm text-gray-900">{{ player.prenom }}</td>
              <td class="px-3 py-1 text-sm text-gray-500 font-mono">{{ getLicenseDisplay(player) }}</td>
              <td class="px-3 py-1 text-sm text-gray-500">{{ player.categ }}-{{ player.sexe }}</td>
              <td class="px-3 py-1 text-sm text-gray-700">{{ player.pagaieLabel }}</td>
              <td class="px-3 py-1 text-sm">
                <span v-if="player.certifCK === 'OUI'" class="text-green-600">{{ t('common.yes') }}</span>
                <span v-else class="text-red-600">{{ t('common.no') }}</span>
              </td>
              <td v-if="canEdit" class="px-3 py-1 text-right">
                <button class="text-red-600 hover:text-red-800" @click="deletePlayer(player.matric)">
                  <UIcon name="i-heroicons-trash" class="w-5 h-5" />
                </button>
              </td>
            </tr>
          </template>

          <!-- Referees (A) -->
          <template v-if="referees.length > 0">
            <tr class="bg-gray-100">
              <td :colspan="canEdit ? 10 : 9" class="px-3 py-1 text-xs text-gray-500 text-center">
                {{ t('presence.section_referees') }}
              </td>
            </tr>
            <tr
              v-for="player in referees"
              :key="player.matric"
              class="hover:bg-gray-50 bg-blue-50/50"
            >
              <td v-if="canEdit" class="px-3 py-1">
                <input v-model="selectedPlayerIds" type="checkbox" :value="player.matric" class="rounded border-gray-300" />
              </td>
              <!-- Numero (inline edit) -->
              <td class="px-3 py-1 text-sm text-gray-900">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'numero')"
                >
                  {{ player.numero || '-' }}
                </span>
                <input
                  v-else
                  :id="`inline-edit-${player.matric}-numero`"
                  v-model.number="editingValue"
                  type="number" min="0" max="99"
                  class="w-16 px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @keydown="handleInlineKeydown"
                  @blur="saveInlineEdit"
                />
              </td>
              <!-- Capitaine (inline edit) -->
              <td class="px-3 py-1 text-sm">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'capitaine')"
                >
                  {{ player.capitaine }}
                </span>
                <select
                  v-else
                  :id="`inline-edit-${player.matric}-capitaine`"
                  v-model="editingValue"
                  class="px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @change="saveInlineEdit"
                  @blur="saveInlineEdit"
                >
                  <option value="-">-</option>
                  <option value="C">C</option>
                  <option value="E">E</option>
                  <option value="A">A</option>
                  <option value="X">X</option>
                </select>
              </td>
              <td class="px-3 py-1 text-sm font-medium text-gray-900">{{ player.nom }}</td>
              <td class="px-3 py-1 text-sm text-gray-900">{{ player.prenom }}</td>
              <td class="px-3 py-1 text-sm text-gray-500 font-mono">{{ getLicenseDisplay(player) }}</td>
              <td class="px-3 py-1 text-sm text-gray-500">{{ player.categ }}-{{ player.sexe }}</td>
              <td class="px-3 py-1 text-sm text-gray-700">{{ player.pagaieLabel }}</td>
              <td class="px-3 py-1 text-sm">
                <span v-if="player.certifCK === 'OUI'" class="text-green-600">{{ t('common.yes') }}</span>
                <span v-else class="text-red-600">{{ t('common.no') }}</span>
              </td>
              <td v-if="canEdit" class="px-3 py-1 text-right">
                <button class="text-red-600 hover:text-red-800" @click="deletePlayer(player.matric)">
                  <UIcon name="i-heroicons-trash" class="w-5 h-5" />
                </button>
              </td>
            </tr>
          </template>

          <!-- Inactive players (X) -->
          <template v-if="inactivePlayers.length > 0">
            <tr class="bg-gray-100">
              <td :colspan="canEdit ? 10 : 9" class="px-3 py-1 text-xs text-gray-500 text-center">
                {{ t('presence.section_inactive') }}
              </td>
            </tr>
            <tr
              v-for="player in inactivePlayers"
              :key="player.matric"
              class="hover:bg-gray-50 opacity-60"
            >
              <td v-if="canEdit" class="px-3 py-1">
                <input v-model="selectedPlayerIds" type="checkbox" :value="player.matric" class="rounded border-gray-300" />
              </td>
              <!-- Numero (inline edit) -->
              <td class="px-3 py-1 text-sm text-gray-900">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'numero')"
                >
                  {{ player.numero || '-' }}
                </span>
                <input
                  v-else
                  :id="`inline-edit-${player.matric}-numero`"
                  v-model.number="editingValue"
                  type="number" min="0" max="99"
                  class="w-16 px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @keydown="handleInlineKeydown"
                  @blur="saveInlineEdit"
                />
              </td>
              <!-- Capitaine (inline edit) -->
              <td class="px-3 py-1 text-sm">
                <span
                  v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
                  :class="canEdit ? 'editable-cell cursor-pointer' : ''"
                  @click="canEdit && startEdit(player, 'capitaine')"
                >
                  {{ player.capitaine }}
                </span>
                <select
                  v-else
                  :id="`inline-edit-${player.matric}-capitaine`"
                  v-model="editingValue"
                  class="px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  @change="saveInlineEdit"
                  @blur="saveInlineEdit"
                >
                  <option value="-">-</option>
                  <option value="C">C</option>
                  <option value="E">E</option>
                  <option value="A">A</option>
                  <option value="X">X</option>
                </select>
              </td>
              <td class="px-3 py-1 text-sm font-medium text-gray-900">{{ player.nom }}</td>
              <td class="px-3 py-1 text-sm text-gray-900">{{ player.prenom }}</td>
              <td class="px-3 py-1 text-sm text-gray-500 font-mono">{{ getLicenseDisplay(player) }}</td>
              <td class="px-3 py-1 text-sm text-gray-500">{{ player.categ }}-{{ player.sexe }}</td>
              <td class="px-3 py-1 text-sm text-gray-700">{{ player.pagaieLabel }}</td>
              <td class="px-3 py-1 text-sm">
                <span v-if="player.certifCK === 'OUI'" class="text-green-600">{{ t('common.yes') }}</span>
                <span v-else class="text-red-600">{{ t('common.no') }}</span>
              </td>
              <td v-if="canEdit" class="px-3 py-1 text-right">
                <button class="text-red-600 hover:text-red-800" @click="deletePlayer(player.matric)">
                  <UIcon name="i-heroicons-trash" class="w-5 h-5" />
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Footer -->
      <div class="px-4 py-1 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
        <div class="flex items-center justify-between">
          <div>
            {{ t('presence.total_players', { count: presenceStore.players.length }) }}
            <span v-if="presenceStore.lastUpdate" class="ml-4 text-xs text-gray-500">
              {{ t('presence.last_update') }}: {{ presenceStore.lastUpdate.date }} - {{ presenceStore.lastUpdate.user }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Cards (visible only on mobile) -->
    <div v-if="!presenceStore.loading && filteredPlayers.length > 0" class="lg:hidden space-y-3">
      <div
        v-for="player in filteredPlayers"
        :key="player.matric"
        class="bg-white rounded-lg shadow p-4"
        :class="{ 'bg-yellow-50': player.capitaine === 'C', 'opacity-60': ['E', 'A', 'X'].includes(player.capitaine) }"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-2">
            <input
              v-if="canEdit"
              v-model="selectedPlayerIds"
              type="checkbox"
              :value="player.matric"
              class="rounded border-gray-300"
            />
            <div>
              <div class="font-bold text-gray-900">{{ player.nom }} {{ player.prenom }}</div>
              <div class="text-sm text-gray-500">{{ getLicenseDisplay(player) }}</div>
            </div>
          </div>
          <span class="px-2 py-1 text-xs font-medium rounded" :class="player.capitaine === 'C' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-100 text-gray-600'">
            {{ player.capitaine }}
          </span>
        </div>

        <div class="space-y-1 text-sm">
          <div class="flex items-center gap-2">
            <span class="text-gray-500">{{ t('common.number') }}:</span>
            <span class="font-medium">{{ player.numero || '-' }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-gray-500">{{ t('common.category') }}:</span>
            <span>{{ player.categ }}-{{ player.sexe }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-gray-500">{{ t('common.paddle') }}:</span>
            <span :class="player.pagaieValide === 0 ? 'text-red-600' : ''">{{ player.pagaieLabel }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-gray-500">{{ t('common.certificate') }}:</span>
            <span :class="player.certifCK === 'OUI' ? 'text-green-600' : 'text-red-600'">
              {{ player.certifCK === 'OUI' ? t('common.yes') : t('common.no') }}
            </span>
          </div>
        </div>

        <div v-if="canEdit" class="mt-3 pt-3 border-t border-gray-200 flex justify-end">
          <button class="text-red-600 hover:text-red-800 text-sm" @click="deletePlayer(player.matric)">
            <UIcon name="i-heroicons-trash" class="w-5 h-5 inline mr-1" />
            {{ t('common.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <AdminConfirmModal
      :open="bulkDeleteModalOpen"
      :title="t('common.delete_selected')"
      :message="t('presence.confirm_delete_players', { count: selectedPlayerIds.length })"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      danger
      @close="bulkDeleteModalOpen = false"
      @confirm="bulkDelete"
    />

    <!-- Add Player Modal -->
    <AdminModal
      :open="addModalOpen"
      :title="t('presence.add_player')"
      max-width="lg"
      @close="addModalOpen = false"
    >
      <form @submit.prevent="addMode === 'existing' ? addExistingPlayer() : createNewPlayer()">
        <div class="space-y-4">
          <!-- Error -->
          <div
            v-if="addFormError"
            class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm"
          >
            <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <span>{{ addFormError }}</span>
          </div>

          <!-- Tabs -->
          <div class="flex border-b border-gray-200">
            <button
              type="button"
              class="px-4 py-1 text-sm font-medium border-b-2 transition-colors"
              :class="addMode === 'existing' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              @click="addMode = 'existing'; addFormData.mode = 'existing'"
            >
              {{ t('presence.add_existing_player') }}
            </button>
            <button
              v-if="!presenceStore.isNationalCompetition"
              type="button"
              class="px-4 py-1 text-sm font-medium border-b-2 transition-colors"
              :class="addMode === 'create' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              @click="addMode = 'create'; addFormData.mode = 'create'"
            >
              {{ t('presence.create_new_player') }}
            </button>
          </div>

          <!-- Existing player search -->
          <template v-if="addMode === 'existing'">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.search_placeholder') }}</label>
              <AdminPlayerAutocomplete
                :model-value="selectedPlayer"
                :placeholder="t('presence.search_placeholder')"
                @update:model-value="onPlayerSelected"
              />
            </div>
          </template>

          <!-- Create new player form -->
          <template v-if="addMode === 'create'">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('common.last_name') }} *</label>
                <input
                  :value="addFormData.nom"
                  type="text"
                  required
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @input="addFormData.nom = ($event.target as HTMLInputElement).value.toUpperCase(); ($event.target as HTMLInputElement).value = addFormData.nom!"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('common.first_name') }} *</label>
                <input
                  :value="addFormData.prenom"
                  type="text"
                  required
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @input="addFormData.prenom = ($event.target as HTMLInputElement).value.toUpperCase(); ($event.target as HTMLInputElement).value = addFormData.prenom!"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.sex') }} *</label>
                <select
                  v-model="addFormData.sexe"
                  required
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="" disabled>-</option>
                  <option value="M">M</option>
                  <option value="F">F</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.birth_date') }}</label>
                <input
                  v-model="addFormData.naissance"
                  type="date"
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.referee_qualification') }}</label>
                <select
                  v-model="addFormData.arbitre"
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">-</option>
                  <option value="INT">INT - {{ t('presence.referee_int') }}</option>
                  <option value="NAT">NAT - {{ t('presence.referee_nat') }}</option>
                  <option value="REG">REG - {{ t('presence.referee_reg') }}</option>
                  <option value="OTM">OTM - {{ t('presence.referee_otm') }}</option>
                  <option value="JO">JO - {{ t('presence.referee_jo') }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.referee_level') }}</label>
                <select
                  v-model="addFormData.niveau"
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="" v-if="addFormData.arbitre">-</option>
                  <option value="A" v-if="addFormData.arbitre">A</option>
                  <option value="B" v-if="addFormData.arbitre">B</option>
                  <option value="C" v-if="addFormData.arbitre">C</option>
                  <option value="S" v-if="addFormData.arbitre">S - {{ t('presence.referee_level_trainee') }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.icf_number') }}</label>
                <input
                  :value="addFormData.numicf ?? ''"
                  type="text"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  :placeholder="t('presence.icf_number_placeholder')"
                  class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @input="(e: Event) => { const el = e.target as HTMLInputElement; const digits = el.value.replace(/\D/g, ''); el.value = digits; addFormData.numicf = digits ? parseInt(digits) : undefined }"
                />
              </div>
            </div>
          </template>

          <!-- Common fields -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">#</label>
              <input
                v-model.number="addFormData.numero"
                type="number"
                min="0"
                max="99"
                class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.status') }}</label>
              <select
                v-model="addFormData.capitaine"
                class="w-full px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="-">{{ t('presence.status_player') }} (-)</option>
                <option value="C">{{ t('presence.status_captain') }} (C)</option>
                <option value="E">{{ t('presence.status_coach') }} (E)</option>
                <option value="A">{{ t('presence.status_referee') }} (A)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-1 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="addModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="addFormSaving || (addMode === 'existing' && !selectedPlayer)"
          >
            <span v-if="addFormSaving" class="flex items-center gap-2">
              <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('common.save') }}
            </span>
            <span v-else>{{ t('common.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Copy Composition Modal -->
    <AdminModal
      :open="copyModalOpen"
      :title="t('presence.copy_from')"
      max-width="md"
      @close="copyModalOpen = false"
    >
      <div class="space-y-4">
        <!-- Warning -->
        <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
          <span>{{ t('presence.copy_warning') }}</span>
        </div>

        <!-- Season -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.source_season') }}</label>
          <select
            v-model="copyFormData.sourceSeason"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @change="loadSourceCompetitions"
          >
            <option v-for="s in seasonOptions" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>

        <!-- Available compositions -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('presence.source_competition') }}</label>
          <div v-if="loadingCompositions" class="text-sm text-gray-500 py-1">
            <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin inline mr-1" />
            {{ t('common.loading') }}
          </div>
          <div v-else-if="availableCompositions.length === 0" class="text-sm text-gray-500 py-1">
            {{ t('presence.no_compositions') }}
          </div>
          <div v-else class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
            <button
              v-for="comp in availableCompositions"
              :key="comp.competitionCode"
              type="button"
              class="w-full text-left px-3 py-1 text-sm border-b border-gray-100 last:border-b-0 transition-colors"
              :class="copyFormData.sourceCompetition === comp.competitionCode ? 'bg-blue-50 text-blue-800' : 'hover:bg-gray-50'"
              @click="copyFormData.sourceCompetition = comp.competitionCode"
            >
              <div class="flex items-center justify-between">
                <span class="font-medium">{{ comp.competitionCode }} - {{ comp.competitionLibelle }}</span>
                <span class="text-xs text-gray-500">{{ comp.playerCount }} {{ t('presence.players_count_short') }}</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
        <button
          type="button"
          class="px-4 py-1 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
          @click="copyModalOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          type="button"
          class="px-4 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
          :disabled="!copyFormData.sourceCompetition"
          @click="copyComposition"
        >
          {{ t('presence.copy_from') }}
        </button>
      </div>
    </AdminModal>
  </div>
</template>
