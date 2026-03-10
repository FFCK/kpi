<script setup lang="ts">
import type { InitialRankingTeam, InitialRankingResponse } from '~/types/rankings'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()
const route = useRoute()

// Query params
const competition = computed(() => (route.query.competition as string) || '')
const season = computed(() => (route.query.season as string) || '')

// State
const loading = ref(false)
const teams = ref<InitialRankingTeam[]>([])
const competitionCode = ref('')
const seasonCode = ref('')

// Inline editing
const editingCell = ref<{ id: number; field: string } | null>(null)
const editingValue = ref('')
const editingOriginalValue = ref('')

// Reset state
const resetModalOpen = ref(false)
const resetting = ref(false)

// Permission
const canEdit = computed(() => authStore.profile <= 3)

// Editable fields
const editableFields = ['Clt', 'Pts', 'J', 'G', 'N', 'P', 'F', 'Plus', 'Moins', 'Diff'] as const

// Load initial ranking
const loadData = async () => {
  if (!competition.value || !season.value) return

  loading.value = true
  try {
    const response = await api.get<InitialRankingResponse>('/admin/rankings/initial', {
      season: season.value,
      competition: competition.value
    })
    teams.value = response.teams
    competitionCode.value = response.competition
    seasonCode.value = response.season
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('rankings.error_load')
    toast.add({ title: t('common.error'), description: message, color: 'error', duration: 3000 })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})

// Inline editing
const startEdit = (teamId: number, field: string, currentValue: number) => {
  if (!canEdit.value) return
  editingCell.value = { id: teamId, field }
  const val = String(currentValue)
  editingValue.value = val
  editingOriginalValue.value = val
  nextTick(() => {
    const el = document.getElementById(`init-edit-${teamId}-${field}`)
    if (el) {
      el.focus()
      if (el instanceof HTMLInputElement) el.select()
    }
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return

  const { id, field } = editingCell.value
  editingCell.value = null

  if (editingValue.value === editingOriginalValue.value) return

  const value = parseInt(editingValue.value) || 0

  try {
    await api.patch(`/admin/rankings/initial/${id}`, { field, value })
    // Update local data
    const team = teams.value.find(t => t.id === id)
    if (team) {
      const propMap: Record<string, keyof InitialRankingTeam> = {
        Clt: 'clt', Pts: 'pts', J: 'j', G: 'g', N: 'n', P: 'p', F: 'f',
        Plus: 'plus', Moins: 'moins', Diff: 'diff'
      }
      const prop = propMap[field]
      if (prop) {
        (team as Record<string, unknown>)[prop] = value
      }
    }
    toast.add({ title: t('common.success'), description: t('rankings.success_saved'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_save'), color: 'error', duration: 3000 })
  }
}

const cancelInlineEdit = () => {
  editingCell.value = null
}

const handleInlineKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') saveInlineEdit()
  else if (e.key === 'Escape') cancelInlineEdit()
}

// Reset all values
const doReset = async () => {
  resetModalOpen.value = false
  resetting.value = true
  try {
    const response = await api.post<InitialRankingResponse>('/admin/rankings/initial/reset', {
      season: season.value,
      competition: competition.value
    })
    teams.value = response.teams
    toast.add({ title: t('common.success'), description: t('rankings.initial.reset_success'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_save'), color: 'error', duration: 3000 })
  } finally {
    resetting.value = false
  }
}

// Field display label
const fieldLabel = (field: string) => {
  const labels: Record<string, string> = {
    Clt: t('rankings.table.rank'),
    Pts: t('rankings.table.pts'),
    J: t('rankings.table.j'),
    G: t('rankings.table.g'),
    N: t('rankings.table.n'),
    P: t('rankings.table.p'),
    F: t('rankings.table.f'),
    Plus: t('rankings.table.plus'),
    Moins: t('rankings.table.minus'),
    Diff: t('rankings.table.diff')
  }
  return labels[field] || field
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-header-900">{{ t('rankings.initial.title') }}</h1>
        <p v-if="competitionCode" class="text-sm text-header-500 mt-1">
          {{ t('rankings.initial.subtitle', { competition: competitionCode }) }}
        </p>
      </div>
      <NuxtLink
        to="/rankings"
        class="px-3 py-2 border border-header-300 text-header-700 rounded-lg hover:bg-header-50 transition-colors text-sm flex items-center gap-1"
      >
        <UIcon name="heroicons:arrow-left" class="w-4 h-4" />
        {{ t('rankings.initial.back') }}
      </NuxtLink>
    </div>

    <!-- Toolbar -->
    <div v-if="canEdit" class="mb-4 bg-white rounded-lg shadow p-4 flex flex-wrap items-center gap-2">
      <button
        class="px-3 py-1.5 border border-header-300 text-header-700 rounded-lg hover:bg-header-50 transition-colors text-sm"
        :disabled="loading"
        @click="loadData"
      >
        {{ t('rankings.initial.reload') }}
      </button>
      <button
        class="px-3 py-1.5 bg-danger-600 text-white rounded-lg hover:bg-danger-700 transition-colors text-sm disabled:opacity-50"
        :disabled="resetting"
        @click="resetModalOpen = true"
      >
        {{ t('rankings.initial.reset') }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading && teams.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-header-500">
      <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
      {{ t('common.loading') }}
    </div>

    <!-- Table -->
    <div v-else-if="teams.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Desktop table -->
      <div class="hidden lg:block overflow-x-auto">
        <table class="min-w-full divide-y divide-header-200">
          <thead class="bg-header-50">
            <tr>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.rank') }}</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.team') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.pts') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.j') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.g') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.n') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.p') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.f') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.plus') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.minus') }}</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-header-500 uppercase">{{ t('rankings.table.diff') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-header-200">
            <tr v-for="team in teams" :key="team.id" class="hover:bg-header-50">
              <!-- Clt -->
              <td class="px-3 py-1.5 text-center text-sm">
                <template v-if="editingCell?.id === team.id && editingCell.field === 'Clt'">
                  <input
                    :id="`init-edit-${team.id}-Clt`"
                    v-model="editingValue"
                    type="tel"
                    maxlength="3"
                    class="w-10 px-1 py-0.5 border border-primary-400 rounded text-center text-sm focus:ring-2 focus:ring-primary-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <span
                  v-else
                  :class="canEdit ? 'editable-cell' : ''"
                  @click="canEdit && startEdit(team.id, 'Clt', team.clt)"
                >
                  {{ team.clt }}
                </span>
              </td>
              <!-- Team name -->
              <td class="px-3 py-1.5 text-sm font-medium text-header-900">{{ team.libelle }}</td>
              <!-- Pts, J, G, N, P, F, Plus, Moins, Diff -->
              <td v-for="field in (['Pts', 'J', 'G', 'N', 'P', 'F', 'Plus', 'Moins', 'Diff'] as const)" :key="field" class="px-3 py-1.5 text-center text-sm">
                <template v-if="editingCell?.id === team.id && editingCell.field === field">
                  <input
                    :id="`init-edit-${team.id}-${field}`"
                    v-model="editingValue"
                    type="tel"
                    maxlength="5"
                    class="w-12 px-1 py-0.5 border border-primary-400 rounded text-center text-sm focus:ring-2 focus:ring-primary-500"
                    @keydown="handleInlineKeydown"
                    @blur="saveInlineEdit"
                  >
                </template>
                <span
                  v-else
                  :class="canEdit ? 'editable-cell' : ''"
                  @click="canEdit && startEdit(team.id, field, (team as Record<string, any>)[{ Pts: 'pts', J: 'j', G: 'g', N: 'n', P: 'p', F: 'f', Plus: 'plus', Moins: 'moins', Diff: 'diff' }[field]])"
                >
                  {{ (team as Record<string, any>)[{ Pts: 'pts', J: 'j', G: 'g', N: 'n', P: 'p', F: 'f', Plus: 'plus', Moins: 'moins', Diff: 'diff' }[field]] }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="lg:hidden divide-y divide-header-200">
        <div v-for="team in teams" :key="team.id" class="p-3">
          <div class="font-medium text-header-900 text-sm mb-1">{{ team.libelle }}</div>
          <div class="grid grid-cols-5 gap-1 text-xs text-header-600">
            <div v-for="field in editableFields" :key="field" class="text-center">
              <div class="text-[10px] text-header-400 uppercase">{{ fieldLabel(field) }}</div>
              <span
                :class="canEdit ? 'editable-cell' : ''"
                @click="canEdit && startEdit(team.id, field, (team as Record<string, any>)[{ Clt: 'clt', Pts: 'pts', J: 'j', G: 'g', N: 'n', P: 'p', F: 'f', Plus: 'plus', Moins: 'moins', Diff: 'diff' }[field]])"
              >
                {{ (team as Record<string, any>)[{ Clt: 'clt', Pts: 'pts', J: 'j', G: 'g', N: 'n', P: 'p', F: 'f', Plus: 'plus', Moins: 'moins', Diff: 'diff' }[field]] }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="bg-white rounded-lg shadow p-8 text-center text-header-500">
      {{ t('rankings.no_teams') }}
    </div>

    <!-- Reset confirm modal -->
    <AdminConfirmModal
      :open="resetModalOpen"
      :title="t('rankings.initial.reset')"
      :message="t('rankings.initial.reset_confirm')"
      :loading="resetting"
      @close="resetModalOpen = false"
      @confirm="doReset"
    />
  </div>
</template>
