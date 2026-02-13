<script setup lang="ts">
import type { Player, MatchAddPlayerFormData, CopyToMatchesFormData, CopyableMatch } from '~/types/presence'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const route = useRoute()
const { t } = useI18n()
const api = useApi()
const toast = useToast()
const presenceStore = usePresenceStore()

// Extract params from route
const matchId = computed(() => parseInt(route.params.matchId as string))
const teamCode = computed(() => route.params.teamCode as 'A' | 'B')

// Permissions
const {
  canEdit,
  canDelete,
  canCopy,
  canCopyToCompetition,
  canInitializeFromTeam,
  canClearAll
} = usePresencePermissions('match', computed(() => presenceStore.isLocked))

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
const addFormData = ref<MatchAddPlayerFormData>({ matric: 0, capitaine: '-' })
const addFormError = ref('')
const addFormSaving = ref(false)

// Available team players (for adding to match)
const availableTeamPlayers = ref<Player[]>([])

// Copy to matches modal
const copyToMatchesModalOpen = ref(false)
const copyScope = ref<'day' | 'competition'>('day')
const copyableMatches = ref<CopyableMatch[]>([])
const selectedMatchIds = ref<number[]>([])

// Delete modal
const bulkDeleteModalOpen = ref(false)
const clearAllModalOpen = ref(false)
const isDeleting = ref(false)

// Initialize on mount
onMounted(async () => {
  await presenceStore.initMatchMode(matchId.value, teamCode.value, api)
  await loadAvailableTeamPlayers()
})

// Computed
const filteredPlayers = computed(() => {
  if (!search.value) return presenceStore.players
  const query = search.value.toLowerCase()
  return presenceStore.players.filter(p =>
    p.nom.toLowerCase().includes(query) ||
    p.prenom.toLowerCase().includes(query) ||
    p.matric.toString().includes(query)
  )
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

// Load available team players (excluding E/A/X and already added)
const loadAvailableTeamPlayers = async () => {
  if (!presenceStore.team) return

  try {
    const response = await api.get<{ players: Player[] }>(
      `/admin/teams/${presenceStore.team.id}/players`
    )

    // Filter out E/A/X and already added players
    const currentMatrics = presenceStore.players.map(p => p.matric)
    availableTeamPlayers.value = response.players.filter(
      p => ![' E', 'A', 'X'].includes(p.capitaine) && !currentMatrics.includes(p.matric)
    )
  } catch (error) {
    console.error('Failed to load team players:', error)
  }
}

// Initialize from team composition
const initializeFromTeam = async () => {
  try {
    await presenceStore.initializeFromTeam(api)
    toast.add({ title: t('presence.composition_initialized'), color: 'success' })
    await loadAvailableTeamPlayers()
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

// Add player
const onPlayerSelect = () => {
  const player = availableTeamPlayers.value.find(p => p.matric === addFormData.value.matric)
  if (player) {
    addFormData.value.numero = player.numero
    addFormData.value.capitaine = player.capitaine as '-' | 'C' | 'E'
  }
}

const addPlayerToMatch = async () => {
  if (!addFormData.value.matric) return

  addFormSaving.value = true
  addFormError.value = ''

  try {
    await presenceStore.addMatchPlayer(addFormData.value, api)
    toast.add({ title: t('presence.player_added'), color: 'success' })
    addModalOpen.value = false
    resetAddForm()
    await loadAvailableTeamPlayers()
  } catch (error: any) {
    addFormError.value = error.message || t('presence.add_player_failed')
  } finally {
    addFormSaving.value = false
  }
}

const resetAddForm = () => {
  addFormData.value = { matric: 0, capitaine: '-' }
  addFormError.value = ''
}

// Copy to matches
const openCopyToMatchesModal = async () => {
  copyToMatchesModalOpen.value = true
  await loadCopyableMatches()
}

const loadCopyableMatches = async () => {
  try {
    copyableMatches.value = await presenceStore.getCopyableMatches(copyScope.value, api)
  } catch (error) {
    console.error('Failed to load copyable matches:', error)
  }
}

watch(copyScope, () => {
  loadCopyableMatches()
  selectedMatchIds.value = []
})

const copyToMatches = async () => {
  if (selectedMatchIds.value.length === 0) return

  try {
    await presenceStore.copyToMatches(copyScope.value, selectedMatchIds.value, api)
    toast.add({
      title: t('presence.copied_to_matches', { count: selectedMatchIds.value.length }),
      color: 'success'
    })
    copyToMatchesModalOpen.value = false
    selectedMatchIds.value = []
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
    await loadAvailableTeamPlayers()
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
    await loadAvailableTeamPlayers()
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

// Clear all
const confirmClearAll = () => {
  clearAllModalOpen.value = true
}

const clearAll = async () => {
  isDeleting.value = true
  try {
    await presenceStore.clearMatchPlayers(api)
    toast.add({ title: t('presence.composition_cleared'), color: 'success' })
    clearAllModalOpen.value = false
    await loadAvailableTeamPlayers()
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  } finally {
    isDeleting.value = false
  }
}

// Format date helper
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR')
}
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-center gap-3 mb-2">
        <UIcon name="i-heroicons-clipboard-document-check" class="w-6 h-6 text-green-600 shrink-0" />
        <h1 class="text-xl font-bold text-gray-900">
          {{ t('presence.title_match') }}
        </h1>
      </div>

      <div class="text-sm text-gray-700">
        <div v-if="presenceStore.match" class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-1">
            <UIcon name="i-heroicons-calendar" class="w-4 h-4 shrink-0" />
            <span>{{ formatDate(presenceStore.match.dateMatch) }}</span>
          </div>
          <div class="flex items-center gap-1">
            <UIcon name="i-heroicons-clock" class="w-4 h-4 shrink-0" />
            <span>{{ presenceStore.match.heureMatch }}</span>
          </div>
          <div class="flex items-center gap-1">
            <UIcon name="i-heroicons-map-pin" class="w-4 h-4 shrink-0" />
            <span>{{ presenceStore.match.terrain }}</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="font-mono text-gray-500">#{{ presenceStore.match.numeroOrdre }}</span>
          </div>
        </div>

        <div v-if="presenceStore.team" class="flex items-center gap-2 mt-2">
          <span class="font-semibold">{{ teamCode === 'A' ? t('common.team_a') : t('common.team_b') }}:</span>
          <span>{{ presenceStore.team.libelle }}</span>
          <span class="text-gray-500">({{ presenceStore.competition?.code }})</span>
        </div>

        <div v-if="presenceStore.isLocked" class="flex items-center gap-2 mt-2 text-green-700">
          <UIcon name="i-heroicons-check-circle" class="w-4 h-4 shrink-0" />
          <span>{{ t('presence.match_validated') }}</span>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('presence.search_player')"
      :add-label="t('presence.add_player')"
      :show-add="canEdit"
      :show-bulk-delete="canEdit"
      :selected-count="selectedPlayerIds.length"
      @add="addModalOpen = true"
      @bulk-delete="confirmBulkDelete"
    >
      <template #left>
        <button
          v-if="canInitializeFromTeam && presenceStore.players.length === 0"
          class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
          @click="initializeFromTeam"
        >
          <UIcon name="i-heroicons-arrow-down-tray" class="w-4 h-4 inline mr-1" />
          {{ t('presence.initialize_from_team') }}
        </button>

        <button
          v-if="canClearAll"
          class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
          @click="confirmClearAll"
        >
          <UIcon name="i-heroicons-trash" class="w-4 h-4 inline mr-1" />
          {{ t('presence.clear_all') }}
        </button>

        <button
          v-if="canCopy"
          class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
          @click="openCopyToMatchesModal"
        >
          <UIcon name="i-heroicons-document-duplicate" class="w-4 h-4 inline mr-1" />
          {{ t('presence.copy_to_matches') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- Loading state -->
    <div v-if="presenceStore.loading" class="text-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin mx-auto text-green-600" />
      <p class="mt-2 text-sm text-gray-500">{{ t('common.loading') }}</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredPlayers.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
      <UIcon name="i-heroicons-user-group" class="w-12 h-12 mx-auto text-gray-400" />
      <p class="mt-2 text-sm text-gray-500">{{ t('presence.no_players') }}</p>
      <button
        v-if="canInitializeFromTeam"
        class="mt-4 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg"
        @click="initializeFromTeam"
      >
        {{ t('presence.initialize_from_team') }}
      </button>
    </div>

    <!-- Player count summary -->
    <div v-if="presenceStore.players.length > 0" class="mt-4 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
      <div class="flex items-center justify-between">
        <div>
          {{ t('presence.total_players', { count: presenceStore.players.length }) }}
        </div>
      </div>
    </div>

    <!-- Modals would be added here (AddMatchPlayerModal, CopyToMatchesModal, etc.) -->
  </div>
</template>
