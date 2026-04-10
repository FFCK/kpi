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
const editingOriginalValue = ref('')

// Inline focus directive
const vInlineFocus = {
  mounted(el: HTMLElement) {
    el.focus()
    if (el instanceof HTMLInputElement && el.type !== 'date' && el.type !== 'time') {
      el.select()
    }
    if (el instanceof HTMLSelectElement) {
      try { el.showPicker() } catch { /* ignore */ }
    }
  },
}

// Search filter
const search = ref('')

// Add player modal
const addModalOpen = ref(false)
const addFormData = ref<MatchAddPlayerFormData>({ matric: 0, capitaine: '-' })
const addFormError = ref('')
const addFormSaving = ref(false)

// Available team players (for adding to match - loaded lazily)
const availableTeamPlayers = ref<Player[]>([])
const availableTeamPlayersLoaded = ref(false)

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

const activePlayers = computed(() => filteredPlayers.value.filter(p => ['-', 'C'].includes(p.capitaine)))
const coaches = computed(() => filteredPlayers.value.filter(p => p.capitaine === 'E'))

// License display
const getLicenseDisplay = (player: Player): string => {
  return player.icf ? `ICF-${player.icf}` : player.matric.toString()
}

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
  const val = field === 'numero' ? (player.numero || '').toString() : player.capitaine
  editingValue.value = val
  editingOriginalValue.value = val
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return

  const { matric, field } = editingCell.value
  const value = field === 'numero' ? parseInt(editingValue.value) || 0 : editingValue.value

  editingCell.value = null

  if (editingValue.value === editingOriginalValue.value) return

  try {
    await presenceStore.updatePlayerInline(matric, field, value, api)
    toast.add({ title: t('common.saved'), color: 'success' })
  } catch (error: any) {
    toast.add({ title: t('common.error'), description: error.message, color: 'error' })
  }
}

const cancelInlineEdit = () => {
  editingCell.value = null
}

const handleInlineKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') saveInlineEdit()
  else if (e.key === 'Escape') cancelInlineEdit()
}

// Load available team players (excluding E/A/X and already added) - called lazily on modal open
const loadAvailableTeamPlayers = async () => {
  if (!presenceStore.team) return

  try {
    const response = await api.get<{ players: Player[] }>(
      `/admin/teams/${presenceStore.team.id}/players`
    )

    // Filter out E/A/X and already added players
    const currentMatrics = presenceStore.players.map(p => p.matric)
    availableTeamPlayers.value = response.players.filter(
      p => !['E', 'A', 'X'].includes(p.capitaine) && !currentMatrics.includes(p.matric)
    )
    availableTeamPlayersLoaded.value = true
  } catch (error) {
    console.error('Failed to load team players:', error)
  }
}

const openAddModal = async () => {
  addModalOpen.value = true
  if (!availableTeamPlayersLoaded.value) {
    await loadAvailableTeamPlayers()
  }
}

// Initialize from team composition
const initializeFromTeam = async () => {
  try {
    await presenceStore.initializeFromTeam(api)
    toast.add({ title: t('presence.composition_initialized'), color: 'success' })
    availableTeamPlayersLoaded.value = false
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
    availableTeamPlayersLoaded.value = false
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
    availableTeamPlayersLoaded.value = false
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
    availableTeamPlayersLoaded.value = false
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
    availableTeamPlayersLoaded.value = false
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
  <div>
    <!-- Page Header -->
    <AdminPageHeader
      :title="t('presence.title_match')"
      :show-filters="false"
    >
      <template #badges>
        <div v-if="presenceStore.match" class="flex flex-wrap items-center gap-2 text-sm">
          <span class="text-header-500">{{ presenceStore.competition?.code }}</span>
          <span class="text-header-400">&bull;</span>
          <span class="text-header-500">{{ formatDate(presenceStore.match.dateMatch) }} {{ presenceStore.match.heureMatch }}</span>
          <span class="text-header-400">&bull;</span>
          <span class="text-header-500">{{ t('games.field.terrain') }} {{ presenceStore.match.terrain }}</span>
          <span class="text-header-400">&bull;</span>
          <span class="font-mono text-header-500">#{{ presenceStore.match.numeroOrdre }}</span>
          <span class="text-header-400">&bull;</span>
          <span v-if="presenceStore.team" class="font-semibold text-header-900">
            {{ teamCode === 'A' ? t('common.team_a') : t('common.team_b') }}: {{ presenceStore.team.libelle }}
          </span>
          <UIcon
            v-if="presenceStore.isLocked"
            name="heroicons:lock-closed-solid"
            class="w-5 h-5 text-primary-500"
            :title="t('presence.match_validated')"
          />
          <UIcon
            v-else
            name="heroicons:lock-open"
            class="w-5 h-5 text-header-400"
            :title="t('common.unlocked')"
          />
        </div>
      </template>

      <template #notices>
        <div
          v-if="presenceStore.isLocked"
          class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800"
        >
          <UIcon name="heroicons:exclamation-triangle" class="w-4 h-4 shrink-0" />
          {{ t('presence.match_validated') }}
        </div>
      </template>
    </AdminPageHeader>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('presence.search_player')"
      :add-label="t('presence.add_player')"
      :show-add="canEdit"
      :show-bulk-delete="canEdit"
      :selected-count="selectedPlayerIds.length"
      @add="openAddModal"
      @bulk-delete="confirmBulkDelete"
    >
      <template #right>
        <button
          v-if="canInitializeFromTeam"
          class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-white bg-success-500 hover:bg-success-700 rounded-lg transition-colors"
          @click="initializeFromTeam"
        >
          <UIcon name="heroicons:arrow-down-tray" class="w-4 h-4" />
          {{ t('presence.initialize_from_team') }}
        </button>

        <button
          v-if="canClearAll && presenceStore.players.length > 0"
          class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-danger-700 bg-danger-50 hover:bg-danger-100 rounded-lg transition-colors"
          @click="confirmClearAll"
        >
          <UIcon name="heroicons:trash" class="w-4 h-4" />
          {{ t('presence.clear_all') }}
        </button>

        <button
          v-if="canCopy && presenceStore.players.length > 0"
          class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors"
          @click="openCopyToMatchesModal"
        >
          <UIcon name="heroicons:document-duplicate" class="w-4 h-4" />
          {{ t('presence.copy_to_matches') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- Loading state -->
    <div v-if="presenceStore.loading" class="text-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin mx-auto text-success-500" />
      <p class="mt-2 text-sm text-header-500">{{ t('common.loading') }}</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredPlayers.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
      <UIcon name="i-heroicons-user-group" class="w-12 h-12 mx-auto text-header-400" />
      <p class="mt-2 text-sm text-header-500">{{ t('presence.no_players') }}</p>
      <button
        v-if="canInitializeFromTeam"
        class="mt-4 px-4 py-2 text-sm font-medium text-white bg-success-500 hover:bg-success-700 rounded-lg"
        @click="initializeFromTeam"
      >
        {{ t('presence.initialize_from_team') }}
      </button>
    </div>

    <!-- Desktop Table -->
    <div v-else class="hidden lg:block bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-header-200">
        <thead class="bg-header-50">
          <tr>
            <th v-if="canEdit" class="w-10 px-3 py-1">
              <input
                v-model="selectAll"
                type="checkbox"
                class="rounded border-header-300"
                @change="toggleSelectAll"
              />
            </th>
            <th class="w-16 px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">#</th>
            <th class="w-12 px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">Cap</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.last_name') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.first_name') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.license') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.club') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.category') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.paddle') }}</th>
            <th class="px-3 py-1 text-left text-xs font-medium text-header-500 uppercase">{{ t('common.certificate') }}</th>
            <th v-if="canEdit" class="w-16 px-3 py-1"></th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-header-200">
          <!-- Active players (-, C) -->
          <tr
            v-for="player in activePlayers"
            :key="player.matric"
            class="hover:bg-header-50"
            :class="{ 'bg-warning-100': player.capitaine === 'C' }"
          >
            <td v-if="canEdit" class="px-3 py-1">
              <input
                v-model="selectedPlayerIds"
                type="checkbox"
                :value="player.matric"
                class="rounded border-header-300"
              />
            </td>

            <!-- Numero (inline edit) -->
            <td class="px-3 py-1 text-sm text-header-900">
              <span
                v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
                :class="canEdit ? 'editable-cell' : ''"
                @click="canEdit && startEdit(player, 'numero')"
              >
                {{ player.numero || '-' }}
              </span>
              <input
                v-else
                v-model.number="editingValue"
                v-inline-focus
                type="number"
                min="0"
                max="99"
                class="w-16 px-2 py-1 border border-primary-400 rounded text-sm focus:ring-2 focus:ring-primary-500"
                @keydown="handleInlineKeydown"
                @blur="saveInlineEdit"
              />
            </td>

            <!-- Capitaine (inline edit) -->
            <td class="px-3 py-1 text-sm">
              <span
                v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
                :class="canEdit ? 'editable-cell' : ''"
                @click="canEdit && startEdit(player, 'capitaine')"
              >
                {{ player.capitaine }}
              </span>
              <select
                v-else
                v-model="editingValue"
                v-inline-focus
                class="px-2 py-1 border border-primary-400 rounded text-sm focus:ring-2 focus:ring-primary-500"
                @change="saveInlineEdit"
                @blur="cancelInlineEdit"
              >
                <option value="-">-</option>
                <option value="C">C</option>
                <option value="E">E</option>
              </select>
            </td>

            <td class="px-3 py-1 text-sm font-medium text-header-900">{{ player.nom }}</td>
            <td class="px-3 py-1 text-sm text-header-900">{{ player.prenom }}</td>
            <td class="px-3 py-1 text-sm text-header-500 font-mono">
                <NuxtLink
                  :to="`/athletes?matric=${player.matric}`"
                  class="link-value"
                >
                  {{ getLicenseDisplay(player) }}
                </NuxtLink>
              </td>
            <td class="px-3 py-1 text-sm text-header-500">
                <NuxtLink
                  :to="`/clubs?code=${player.numeroClub}`"
                  class="link-value"
                  :title="t('teams_page.columns.club')"
                >
                  {{ player.numeroClub }}
                </NuxtLink>
              </td>
            <td class="px-3 py-1 text-sm text-header-500">{{ player.categ }}-{{ player.sexe }}</td>

            <!-- Pagaie -->
            <td class="px-3 py-1 text-sm">
              <span :class="player.pagaieValide === 0 ? 'text-danger-600' : 'text-header-700'">
                {{ player.pagaieValide === 0 ? `(${player.pagaieLabel})` : player.pagaieLabel }}
              </span>
            </td>

            <!-- Certificate -->
            <td class="px-3 py-1 text-sm">
              <span :class="player.certifCK === 'OUI' ? 'text-success-500' : 'text-danger-600'">
                {{ player.certifCK === 'OUI' ? t('common.yes') : t('common.no') }}
              </span>
            </td>

            <!-- Actions -->
            <td v-if="canEdit" class="px-3 py-1 text-right">
              <button class="text-danger-600 hover:text-danger-800" @click="deletePlayer(player.matric)">
                <UIcon name="i-heroicons-trash" class="w-6 h-6" />
              </button>
            </td>
          </tr>

          <!-- Coaches (E) -->
          <template v-if="coaches.length > 0">
            <tr class="bg-header-100">
              <td :colspan="canEdit ? 11 : 10" class="px-3 py-1 text-xs text-header-500 text-center">
                {{ t('presence.section_coaches') }}
              </td>
            </tr>
            <tr
              v-for="player in coaches"
              :key="player.matric"
              class="hover:bg-header-50 bg-orange-100/50"
            >
              <td v-if="canEdit" class="px-3 py-1">
                <input v-model="selectedPlayerIds" type="checkbox" :value="player.matric" class="rounded border-header-300" />
              </td>
              <td class="px-3 py-1 text-sm text-header-900">{{ player.numero || '-' }}</td>
              <td class="px-3 py-1 text-sm">{{ player.capitaine }}</td>
              <td class="px-3 py-1 text-sm font-medium text-header-900">{{ player.nom }}</td>
              <td class="px-3 py-1 text-sm text-header-900">{{ player.prenom }}</td>
              <td class="px-3 py-1 text-sm text-header-500 font-mono">
                <NuxtLink
                  :to="`/athletes?matric=${player.matric}`"
                  class="link-value"
                >
                  {{ getLicenseDisplay(player) }}
                </NuxtLink>
              </td>
              <td class="px-3 py-1 text-sm text-header-500">{{ player.numeroClub }}</td>
              <td class="px-3 py-1 text-sm text-header-500">{{ player.categ }}-{{ player.sexe }}</td>
              <td class="px-3 py-1 text-sm text-header-700">{{ player.pagaieLabel }}</td>
              <td class="px-3 py-1 text-sm">
                <span :class="player.certifCK === 'OUI' ? 'text-success-500' : 'text-danger-600'">
                  {{ player.certifCK === 'OUI' ? t('common.yes') : t('common.no') }}
                </span>
              </td>
              <td v-if="canEdit" class="px-3 py-1 text-right">
                <button class="text-danger-600 hover:text-danger-800" @click="deletePlayer(player.matric)">
                  <UIcon name="i-heroicons-trash" class="w-6 h-6" />
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Footer -->
      <div class="px-4 py-1 bg-header-50 border-t border-header-200 text-sm text-header-600">
        {{ t('presence.total_players', { count: presenceStore.players.length }) }}
      </div>
    </div>

    <!-- Mobile Cards -->
    <div v-if="!presenceStore.loading && filteredPlayers.length > 0" class="lg:hidden space-y-3">
      <div
        v-for="player in filteredPlayers"
        :key="player.matric"
        class="bg-white rounded-lg shadow p-4"
        :class="{ 'bg-warning-50': player.capitaine === 'C', 'opacity-60': player.capitaine === 'E' }"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-2">
            <input
              v-if="canEdit"
              v-model="selectedPlayerIds"
              type="checkbox"
              :value="player.matric"
              class="rounded border-header-300"
            />
            <div>
              <div class="font-bold text-header-900">{{ player.nom }} {{ player.prenom }}</div>
              <NuxtLink
                :to="`/athletes?matric=${player.matric}`"
                class="link-value text-sm"
              >
                {{ getLicenseDisplay(player) }}
              </NuxtLink>
            </div>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="px-2 py-1 text-xs font-medium rounded bg-header-50 border border-dashed border-header-300">
              #{{ player.numero || '-' }}
            </span>
            <span
              class="px-2 py-1 text-xs font-medium rounded"
              :class="player.capitaine === 'C' ? 'bg-warning-200 text-warning-800' : 'bg-header-100 text-header-600'"
            >
              {{ player.capitaine }}
            </span>
          </div>
        </div>

        <div class="space-y-1 text-sm">
          <div class="flex items-center gap-2">
            <span class="text-header-500">{{ t('common.club') }}:</span>
            <span>{{ player.numeroClub }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-header-500">{{ t('common.category') }}:</span>
            <span>{{ player.categ }}-{{ player.sexe }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-header-500">{{ t('common.paddle') }}:</span>
            <span :class="player.pagaieValide === 0 ? 'text-danger-600' : ''">{{ player.pagaieLabel }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-header-500">{{ t('common.certificate') }}:</span>
            <span :class="player.certifCK === 'OUI' ? 'text-success-500' : 'text-danger-600'">
              {{ player.certifCK === 'OUI' ? t('common.yes') : t('common.no') }}
            </span>
          </div>
        </div>

        <div v-if="canEdit" class="mt-3 pt-3 border-t border-header-200 flex justify-end">
          <button class="text-danger-600 hover:text-danger-800 text-sm" @click="deletePlayer(player.matric)">
            <UIcon name="i-heroicons-trash" class="w-6 h-6 inline mr-1" />
            {{ t('common.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Link to team presence sheet -->
    <div v-if="presenceStore.team" class="mt-4">
      <NuxtLink
        :to="`/presence/team/${presenceStore.team.id}`"
        class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800"
      >
        <UIcon name="heroicons:user-group" class="w-4 h-4" />
        {{ t('presence.view_team_sheet', { team: presenceStore.team.libelle }) }}
      </NuxtLink>
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

    <!-- Clear All Confirmation Modal -->
    <AdminConfirmModal
      :open="clearAllModalOpen"
      :title="t('presence.clear_all')"
      :message="t('presence.confirm_clear_all')"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      danger
      @close="clearAllModalOpen = false"
      @confirm="clearAll"
    />

    <!-- Copy to Matches Modal -->
    <AdminModal
      :open="copyToMatchesModalOpen"
      :title="t('presence.copy_to_matches')"
      max-width="lg"
      @close="copyToMatchesModalOpen = false"
    >
      <div class="space-y-4">
        <!-- Notice -->
        <div class="flex items-center gap-2 p-2 bg-primary-50 border border-primary-200 rounded text-sm text-primary-800">
          <UIcon name="heroicons:information-circle" class="w-4 h-4 shrink-0" />
          {{ t('presence.only_unlocked_matches') }}
        </div>

        <!-- Scope selector -->
        <div class="flex gap-2">
          <button
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="copyScope === 'day' ? 'bg-primary-600 text-white' : 'bg-header-100 text-header-700 hover:bg-header-200'"
            @click="copyScope = 'day'"
          >
            {{ t('presence.scope_day') }}
          </button>
          <button
            v-if="canCopyToCompetition"
            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="copyScope === 'competition' ? 'bg-primary-600 text-white' : 'bg-header-100 text-header-700 hover:bg-header-200'"
            @click="copyScope = 'competition'"
          >
            {{ t('presence.scope_competition') }}
          </button>
        </div>

        <!-- Match list -->
        <div v-if="copyableMatches.length === 0" class="text-center py-4 text-sm text-header-500">
          {{ t('presence.no_copyable_matches') }}
        </div>
        <div v-else class="space-y-2 max-h-64 overflow-y-auto">
          <label
            v-for="match in copyableMatches"
            :key="match.id"
            class="flex items-center gap-3 p-3 border border-header-200 rounded-lg hover:bg-header-50 cursor-pointer"
          >
            <input
              v-model="selectedMatchIds"
              type="checkbox"
              :value="match.id"
              class="rounded border-header-300"
            />
            <div class="text-sm">
              <div class="font-medium text-header-900">
                {{ match.equipeA }} vs {{ match.equipeB }}
              </div>
              <div class="text-header-500">
                {{ formatDate(match.dateMatch) }} {{ match.heureMatch }} &bull; {{ t('games.field.terrain') }} {{ match.terrain }}
              </div>
            </div>
          </label>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
          @click="copyToMatchesModalOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          :disabled="selectedMatchIds.length === 0"
          class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg disabled:opacity-50"
          @click="copyToMatches"
        >
          {{ t('presence.copy_to_count', { count: selectedMatchIds.length }) }}
        </button>
      </div>
    </AdminModal>

    <!-- Add Player Modal -->
    <AdminModal
      :open="addModalOpen"
      :title="t('presence.add_player')"
      max-width="lg"
      @close="addModalOpen = false"
    >
      <form @submit.prevent="addPlayerToMatch">
        <div class="space-y-4">
          <div
            v-if="addFormError"
            class="flex items-start gap-3 p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-800 text-sm"
          >
            <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <span>{{ addFormError }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('presence.select_player') }}</label>
            <select
              v-model="addFormData.matric"
              class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
              @change="onPlayerSelect"
            >
              <option :value="0" disabled>{{ t('presence.select_player') }}</option>
              <option v-for="p in availableTeamPlayers" :key="p.matric" :value="p.matric">
                {{ p.nom }} {{ p.prenom }} ({{ p.matric }})
              </option>
            </select>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50"
            @click="addModalOpen = false"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            type="submit"
            :disabled="!addFormData.matric || addFormSaving"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg disabled:opacity-50"
          >
            {{ t('common.add') }}
          </button>
        </div>
      </form>
    </AdminModal>
  </div>
</template>
