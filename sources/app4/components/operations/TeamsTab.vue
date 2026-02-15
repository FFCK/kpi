<script setup lang="ts">
import type { TeamAutocomplete, ClubAutocomplete } from '~/types/operations'

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// State
const loading = ref(false)

// Rename state
const renameSearch = ref('')
const renameResults = ref<TeamAutocomplete[]>([])
const selectedRenameTeam = ref<TeamAutocomplete | null>(null)
const showRenameDropdown = ref(false)
const newTeamName = ref('')

// Merge state
const mergeSourceSearch = ref('')
const mergeTargetSearch = ref('')
const mergeSourceResults = ref<TeamAutocomplete[]>([])
const mergeTargetResults = ref<TeamAutocomplete[]>([])
const selectedMergeSource = ref<TeamAutocomplete | null>(null)
const selectedMergeTarget = ref<TeamAutocomplete | null>(null)
const showMergeSourceDropdown = ref(false)
const showMergeTargetDropdown = ref(false)

// Move state
const moveTeamSearch = ref('')
const moveClubSearch = ref('')
const moveTeamResults = ref<TeamAutocomplete[]>([])
const moveClubResults = ref<ClubAutocomplete[]>([])
const selectedMoveTeam = ref<TeamAutocomplete | null>(null)
const selectedMoveClub = ref<ClubAutocomplete | null>(null)
const showMoveTeamDropdown = ref(false)
const showMoveClubDropdown = ref(false)

// Modal state
const confirmRenameModal = ref(false)
const confirmMergeModal = ref(false)
const confirmMoveModal = ref(false)

// Debounce timeouts
let renameTimeout: ReturnType<typeof setTimeout> | null = null
let mergeSourceTimeout: ReturnType<typeof setTimeout> | null = null
let mergeTargetTimeout: ReturnType<typeof setTimeout> | null = null
let moveTeamTimeout: ReturnType<typeof setTimeout> | null = null
let moveClubTimeout: ReturnType<typeof setTimeout> | null = null

// Search teams
const searchTeams = async (query: string): Promise<TeamAutocomplete[]> => {
  if (query.length < 2) return []
  try {
    return await api.get<TeamAutocomplete[]>('/admin/operations/autocomplete/teams', { q: query, limit: 10 })
  } catch {
    return []
  }
}

// Search clubs
const searchClubs = async (query: string): Promise<ClubAutocomplete[]> => {
  if (query.length < 2) return []
  try {
    return await api.get<ClubAutocomplete[]>('/admin/operations/autocomplete/clubs', { q: query, limit: 10 })
  } catch {
    return []
  }
}

// Watch rename search
watch(renameSearch, (value) => {
  if (renameTimeout) clearTimeout(renameTimeout)
  if (selectedRenameTeam.value && selectedRenameTeam.value.label !== value) {
    selectedRenameTeam.value = null
  }
  renameTimeout = setTimeout(async () => {
    renameResults.value = await searchTeams(value)
    showRenameDropdown.value = renameResults.value.length > 0
  }, 300)
})

// Watch merge source search
watch(mergeSourceSearch, (value) => {
  if (mergeSourceTimeout) clearTimeout(mergeSourceTimeout)
  if (selectedMergeSource.value && selectedMergeSource.value.label !== value) {
    selectedMergeSource.value = null
  }
  mergeSourceTimeout = setTimeout(async () => {
    mergeSourceResults.value = await searchTeams(value)
    showMergeSourceDropdown.value = mergeSourceResults.value.length > 0
  }, 300)
})

// Watch merge target search
watch(mergeTargetSearch, (value) => {
  if (mergeTargetTimeout) clearTimeout(mergeTargetTimeout)
  if (selectedMergeTarget.value && selectedMergeTarget.value.label !== value) {
    selectedMergeTarget.value = null
  }
  mergeTargetTimeout = setTimeout(async () => {
    mergeTargetResults.value = await searchTeams(value)
    showMergeTargetDropdown.value = mergeTargetResults.value.length > 0
  }, 300)
})

// Watch move team search
watch(moveTeamSearch, (value) => {
  if (moveTeamTimeout) clearTimeout(moveTeamTimeout)
  if (selectedMoveTeam.value && selectedMoveTeam.value.label !== value) {
    selectedMoveTeam.value = null
  }
  moveTeamTimeout = setTimeout(async () => {
    moveTeamResults.value = await searchTeams(value)
    showMoveTeamDropdown.value = moveTeamResults.value.length > 0
  }, 300)
})

// Watch move club search
watch(moveClubSearch, (value) => {
  if (moveClubTimeout) clearTimeout(moveClubTimeout)
  if (selectedMoveClub.value && selectedMoveClub.value.label !== value) {
    selectedMoveClub.value = null
  }
  moveClubTimeout = setTimeout(async () => {
    moveClubResults.value = await searchClubs(value)
    showMoveClubDropdown.value = moveClubResults.value.length > 0
  }, 300)
})

// Select handlers
const selectRenameTeam = (team: TeamAutocomplete) => {
  selectedRenameTeam.value = team
  renameSearch.value = team.label
  newTeamName.value = team.libelle
  showRenameDropdown.value = false
}

const selectMergeSource = (team: TeamAutocomplete) => {
  selectedMergeSource.value = team
  mergeSourceSearch.value = team.label
  showMergeSourceDropdown.value = false
}

const selectMergeTarget = (team: TeamAutocomplete) => {
  selectedMergeTarget.value = team
  mergeTargetSearch.value = team.label
  showMergeTargetDropdown.value = false
}

const selectMoveTeam = (team: TeamAutocomplete) => {
  selectedMoveTeam.value = team
  moveTeamSearch.value = team.label
  showMoveTeamDropdown.value = false
}

const selectMoveClub = (club: ClubAutocomplete) => {
  selectedMoveClub.value = club
  moveClubSearch.value = club.label
  showMoveClubDropdown.value = false
}

// Operations
const openRenameModal = () => {
  if (!selectedRenameTeam.value || !newTeamName.value.trim()) return
  confirmRenameModal.value = true
}

const confirmRename = async () => {
  if (!selectedRenameTeam.value || !newTeamName.value.trim()) return
  loading.value = true
  try {
    await api.post('/admin/operations/teams/rename', {
      teamId: selectedRenameTeam.value.numero,
      newName: newTeamName.value.trim()
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.teams.success_rename'),
      color: 'success',
      duration: 3000
    })
    confirmRenameModal.value = false
    renameSearch.value = ''
    selectedRenameTeam.value = null
    newTeamName.value = ''
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.teams.error_rename'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

const openMergeModal = () => {
  if (!selectedMergeSource.value || !selectedMergeTarget.value) return
  if (selectedMergeSource.value.numero === selectedMergeTarget.value.numero) {
    toast.add({
      title: t('common.error'),
      description: 'Source et cible ne peuvent pas être identiques',
      color: 'error',
      duration: 3000
    })
    return
  }
  confirmMergeModal.value = true
}

const confirmMerge = async () => {
  if (!selectedMergeSource.value || !selectedMergeTarget.value) return
  loading.value = true
  try {
    await api.post('/admin/operations/teams/merge', {
      sourceId: selectedMergeSource.value.numero,
      targetId: selectedMergeTarget.value.numero
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.teams.success_merge'),
      color: 'success',
      duration: 3000
    })
    confirmMergeModal.value = false
    mergeSourceSearch.value = ''
    mergeTargetSearch.value = ''
    selectedMergeSource.value = null
    selectedMergeTarget.value = null
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.teams.error_merge'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

const openMoveModal = () => {
  if (!selectedMoveTeam.value || !selectedMoveClub.value) return
  confirmMoveModal.value = true
}

const confirmMove = async () => {
  if (!selectedMoveTeam.value || !selectedMoveClub.value) return
  loading.value = true
  try {
    await api.post('/admin/operations/teams/move', {
      teamId: selectedMoveTeam.value.numero,
      clubCode: selectedMoveClub.value.numero
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.teams.success_move'),
      color: 'success',
      duration: 3000
    })
    confirmMoveModal.value = false
    moveTeamSearch.value = ''
    moveClubSearch.value = ''
    selectedMoveTeam.value = null
    selectedMoveClub.value = null
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.teams.error_move'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-8">
    <!-- Rename team -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.teams.rename') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Team search -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.team') }}
          </label>
          <input
            v-model="renameSearch"
            type="text"
            :placeholder="t('operations.teams.search_team')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showRenameDropdown = renameResults.length > 0"
            @blur="setTimeout(() => showRenameDropdown = false, 200)"
          />
          <div
            v-if="showRenameDropdown && renameResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="team in renameResults"
              :key="team.numero"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm"
              @click="selectRenameTeam(team)"
            >
              <div class="font-medium">{{ team.libelle }}</div>
              <div class="text-xs text-gray-500">{{ team.numero }} - {{ team.club }}</div>
            </button>
          </div>
        </div>

        <!-- New name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.new_name') }}
          </label>
          <input
            v-model="newTeamName"
            type="text"
            :disabled="!selectedRenameTeam"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
          />
        </div>
      </div>

      <button
        :disabled="!selectedRenameTeam || !newTeamName.trim() || loading"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="openRenameModal"
      >
        {{ t('operations.teams.rename_button') }}
      </button>
    </section>

    <!-- Merge teams -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.teams.merge') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Source team -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.source_team') }}
          </label>
          <input
            v-model="mergeSourceSearch"
            type="text"
            :placeholder="t('operations.teams.search_team')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showMergeSourceDropdown = mergeSourceResults.length > 0"
            @blur="setTimeout(() => showMergeSourceDropdown = false, 200)"
          />
          <div
            v-if="showMergeSourceDropdown && mergeSourceResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="team in mergeSourceResults"
              :key="team.numero"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm"
              @click="selectMergeSource(team)"
            >
              <div class="font-medium">{{ team.libelle }}</div>
              <div class="text-xs text-gray-500">{{ team.numero }} - {{ team.club }}</div>
            </button>
          </div>
        </div>

        <!-- Target team -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.target_team') }}
          </label>
          <input
            v-model="mergeTargetSearch"
            type="text"
            :placeholder="t('operations.teams.search_team')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showMergeTargetDropdown = mergeTargetResults.length > 0"
            @blur="setTimeout(() => showMergeTargetDropdown = false, 200)"
          />
          <div
            v-if="showMergeTargetDropdown && mergeTargetResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="team in mergeTargetResults"
              :key="team.numero"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm"
              @click="selectMergeTarget(team)"
            >
              <div class="font-medium">{{ team.libelle }}</div>
              <div class="text-xs text-gray-500">{{ team.numero }} - {{ team.club }}</div>
            </button>
          </div>
        </div>
      </div>

      <button
        :disabled="!selectedMergeSource || !selectedMergeTarget || loading"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="openMergeModal"
      >
        {{ t('operations.teams.merge_button') }}
      </button>
    </section>

    <!-- Move team -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.teams.move') }}
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Team search -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.team') }}
          </label>
          <input
            v-model="moveTeamSearch"
            type="text"
            :placeholder="t('operations.teams.search_team')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showMoveTeamDropdown = moveTeamResults.length > 0"
            @blur="setTimeout(() => showMoveTeamDropdown = false, 200)"
          />
          <div
            v-if="showMoveTeamDropdown && moveTeamResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="team in moveTeamResults"
              :key="team.numero"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm"
              @click="selectMoveTeam(team)"
            >
              <div class="font-medium">{{ team.libelle }}</div>
              <div class="text-xs text-gray-500">{{ team.numero }} - {{ team.club }}</div>
            </button>
          </div>
        </div>

        <!-- Club search -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.teams.target_club') }}
          </label>
          <input
            v-model="moveClubSearch"
            type="text"
            :placeholder="t('operations.teams.search_club')"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            @focus="showMoveClubDropdown = moveClubResults.length > 0"
            @blur="setTimeout(() => showMoveClubDropdown = false, 200)"
          />
          <div
            v-if="showMoveClubDropdown && moveClubResults.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="club in moveClubResults"
              :key="club.numero"
              type="button"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 text-sm"
              @click="selectMoveClub(club)"
            >
              <div class="font-medium">{{ club.nom }}</div>
              <div class="text-xs text-gray-500">{{ club.numero }} - {{ club.departement }}</div>
            </button>
          </div>
        </div>
      </div>

      <button
        :disabled="!selectedMoveTeam || !selectedMoveClub || loading"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="openMoveModal"
      >
        {{ t('operations.teams.move_button') }}
      </button>
    </section>

    <!-- Confirm modals -->
    <AdminConfirmModal
      :open="confirmRenameModal"
      :title="t('operations.teams.confirm_rename')"
      :item-name="`${selectedRenameTeam?.libelle} => ${newTeamName}`"
      :confirm-text="t('operations.teams.rename_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmRenameModal = false"
      @confirm="confirmRename"
    />

    <AdminConfirmModal
      :open="confirmMergeModal"
      :title="t('operations.teams.confirm_merge')"
      :item-name="`${selectedMergeSource?.libelle} => ${selectedMergeTarget?.libelle}`"
      :confirm-text="t('operations.teams.merge_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmMergeModal = false"
      @confirm="confirmMerge"
    />

    <AdminConfirmModal
      :open="confirmMoveModal"
      :title="t('operations.teams.confirm_move')"
      :item-name="`${selectedMoveTeam?.libelle} => ${selectedMoveClub?.nom}`"
      :confirm-text="t('operations.teams.move_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      @close="confirmMoveModal = false"
      @confirm="confirmMove"
    />
  </div>
</template>
