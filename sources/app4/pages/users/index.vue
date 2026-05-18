<script setup lang="ts">
import type { UserListItem, UsersResponse, MandateScope, MandateScopesResponse } from '~/types/users'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()
const config = useRuntimeConfig()
const competitionsSearchUrl = computed(() => `${config.public.api2BaseUrl}/admin/filters/competitions-search`)


// Profile guard: only profiles <= 4 can access
if (authStore.profile > 4) {
  navigateTo('/')
}

// Permissions
const canEdit = computed(() => authStore.hasProfile(3))
const canManageMandates = computed(() => authStore.hasProfile(4))
const canDelete = computed(() => authStore.hasProfile(2))

// Per-row permission: an admin can only edit/delete users with a strictly higher profile number (lower privilege)
// Exception: profile 1 can edit/delete anyone
function canEditUser(userNiveau: number): boolean {
  if (!canEdit.value) return false
  return authStore.profile === 1 || userNiveau > authStore.profile
}
function canOpenMandatesModal(userNiveau: number): boolean {
  if (!canManageMandates.value) return false
  if (authStore.profile === 1) return true
  // Profiles 3 and 4 can open the mandates modal for users with niveau >= 3
  const minVisible = (authStore.profile === 3 || authStore.profile === 4) ? 3 : authStore.profile
  return userNiveau >= minVisible
}
function canDeleteUser(userNiveau: number): boolean {
  if (!canDelete.value) return false
  return authStore.profile === 1 || userNiveau > authStore.profile
}

// State
const loading = ref(false)
const users = ref<UserListItem[]>([])
const page = ref(1)
const limit = ref(20)
const total = ref(0)
const totalPages = ref(0)
const searchQuery = ref('')
const filterProfile = ref<string>('')
const filterSeason = ref<string>('')
const filterCompetitions = ref<string[]>([])

// Seasons for the filter dropdown
const seasons = ref<{ code: string; active: boolean }[]>([])

// Selection
const selectedCodes = ref<string[]>([])
const allSelected = computed(() => users.value.length > 0 && selectedCodes.value.length === users.value.length)

// Modal state
const editModalOpen = ref(false)
const editingUser = ref<UserListItem | null>(null)

// Confirm modal
const confirmOpen = ref(false)
const confirmMessage = ref('')
const confirmAction = ref<(() => void) | null>(null)
const deleting = ref(false)

// Load seasons for the filter
async function loadSeasons() {
  try {
    const data = await api.get<{ seasons: { code: string; active: boolean }[] }>('/admin/filters/seasons')
    seasons.value = data.seasons || []
  } catch { /* useApi handles toast */ }
}

// Load users
async function loadUsers() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      limit: limit.value,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterProfile.value) params.profile = filterProfile.value
    if (filterSeason.value) params.season = filterSeason.value
    if (filterCompetitions.value.length > 0) params.competition = filterCompetitions.value.join(',')

    const data = await api.get<UsersResponse>('/admin/users', params)
    users.value = data.items
    total.value = data.total
    totalPages.value = data.totalPages
    // Clear selection on reload
    selectedCodes.value = []
  } catch { /* useApi handles toast */ }
  finally { loading.value = false }
}

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    scopesPage.value = 1
    if (viewMode.value === 'mandates') loadScopes()
    else loadUsers()
  }, 300)
})

watch([filterProfile, filterSeason, filterCompetitions], () => {
  page.value = 1
  scopesPage.value = 1
  if (viewMode.value === 'mandates') loadScopes()
  else loadUsers()
})

watch([page, limit], () => {
  if (viewMode.value === 'users') loadUsers()
})

// Selection
function toggleSelect(code: string) {
  const idx = selectedCodes.value.indexOf(code)
  if (idx >= 0) {
    selectedCodes.value.splice(idx, 1)
  } else {
    selectedCodes.value.push(code)
  }
}

function toggleSelectAll() {
  if (allSelected.value) {
    selectedCodes.value = []
  } else {
    selectedCodes.value = users.value.map(u => u.code)
  }
}

// Actions
function openAddModal() {
  editingUser.value = null
  editModalOpen.value = true
}

function openEditModal(user: UserListItem) {
  editingUser.value = user
  editModalOpen.value = true
}

function confirmDeleteUser(user: UserListItem) {
  confirmMessage.value = t('users.confirm_delete')
  confirmAction.value = () => deleteUser(user.code)
  confirmOpen.value = true
}

function confirmBulkDelete() {
  confirmMessage.value = t('users.confirm_bulk_delete', { count: selectedCodes.value.length })
  confirmAction.value = () => bulkDelete()
  confirmOpen.value = true
}

async function deleteUser(code: string) {
  deleting.value = true
  try {
    await api.del(`/admin/users/${code}`)
    toast.add({ title: t('users.success_deleted'), color: 'success', duration: 3000 })
    confirmOpen.value = false
    loadUsers()
  } catch { /* useApi handles toast */ }
  finally { deleting.value = false }
}

async function bulkDelete() {
  deleting.value = true
  try {
    const data = await api.post<{ deleted: number }>('/admin/users/bulk-delete', { codes: selectedCodes.value })
    toast.add({ title: t('users.success_bulk_deleted', { count: data.deleted }), color: 'success', duration: 3000 })
    confirmOpen.value = false
    selectedCodes.value = []
    loadUsers()
  } catch { /* useApi handles toast */ }
  finally { deleting.value = false }
}

function onUserSaved() {
  loadUsers()
}

// Helpers
function formatFilter(value: string, allLabel: string): string {
  if (!value || value.trim() === '') return allLabel
  // Pipe-delimited: |A|B|C| → A, B, C
  if (value.includes('|')) {
    const items = value.split('|').filter(v => v.trim() !== '')
    return items.join(', ')
  }
  // Comma-delimited
  return value
}

function formatEventsGamedays(user: UserListItem): string {
  const parts: string[] = []
  if (user.idEvenement && user.idEvenement.trim()) {
    const evts = user.idEvenement.split('|').filter(v => v.trim() !== '')
    parts.push(`E:${evts.length}`)
  }
  if (user.filtreJournee && user.filtreJournee.trim()) {
    const jours = user.filtreJournee.split(',').filter(v => v.trim() !== '')
    parts.push(`J:${jours.length}`)
  }
  return parts.join(' ') || '—'
}

onMounted(() => {
  loadSeasons()
  loadUsers()
})

// Mandate tooltip
const tooltipUser = ref<string | null>(null)
const tooltipRef = ref<HTMLElement | null>(null)
const tooltipStyle = ref<Record<string, string>>({ top: '0px', left: '0px' })
let _tooltipAnchorRect: DOMRect | null = null

function showTooltip(code: string, event: MouseEvent) {
  tooltipUser.value = code
  _tooltipAnchorRect = (event.currentTarget as HTMLElement).getBoundingClientRect()
  tooltipStyle.value = { top: '-9999px', left: '-9999px' }
  nextTick(() => positionTooltip())
}

function positionTooltip() {
  const rect = _tooltipAnchorRect
  if (!rect) return
  const tip = tooltipRef.value
  const tipHeight = tip ? tip.offsetHeight : 0
  const tipWidth = tip ? tip.offsetWidth : 0
  const gap = 6
  const viewportH = window.innerHeight
  const viewportW = window.innerWidth

  let top: number
  if (rect.bottom + gap + tipHeight > viewportH && rect.top - gap - tipHeight >= 0) {
    top = rect.top - gap - tipHeight
  } else {
    top = rect.bottom + gap
  }

  let left = rect.left
  if (left + tipWidth > viewportW) left = viewportW - tipWidth - 8

  tooltipStyle.value = { top: `${top}px`, left: `${left}px` }
}

function hideTooltip() {
  tooltipUser.value = null
  _tooltipAnchorRect = null
}

// Mobile mandate expand
const expandedMandates = ref<Set<string>>(new Set())

function toggleMandates(code: string) {
  if (expandedMandates.value.has(code)) {
    expandedMandates.value.delete(code)
  } else {
    expandedMandates.value.add(code)
  }
  expandedMandates.value = new Set(expandedMandates.value)
}

function formatMandateFilters(mandate: import('~/types/users').Mandate): string {
  const parts: string[] = []
  if (mandate.filtreSaison) {
    parts.push(mandate.filtreSaison.split('|').filter(v => v).join(', '))
  }
  if (mandate.filtreCompetition) {
    const comps = mandate.filtreCompetition.split('|').filter(v => v)
    parts.push(comps.join(', '))
  }
  if (mandate.limitClubs) parts.push(`clubs: ${mandate.limitClubs}`)
  return parts.join(' · ') || t('users.mandates.tooltip_all')
}

// View mode: 'users' | 'mandates'
const viewMode = ref<'users' | 'mandates'>('users')

// Mandate scopes (mode mandats)
const scopes = ref<MandateScope[]>([])
const scopesPage = ref(1)
const scopesTotal = ref(0)
const scopesTotalPages = ref(0)
const selectedScopes = ref<{ scopeType: 'base' | 'mandate'; userCode: string; mandateId: number | null }[]>([])
const scopesAllSelected = computed(() =>
  scopes.value.length > 0 && selectedScopes.value.length === scopes.value.length
)

async function loadScopes() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: scopesPage.value,
      limit: limit.value,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterProfile.value) params.profile = filterProfile.value
    if (filterSeason.value) params.season = filterSeason.value
    if (filterCompetitions.value.length > 0) params.competition = filterCompetitions.value.join(',')
    const data = await api.get<MandateScopesResponse>('/admin/users/mandate-scopes', params)
    scopes.value = data.items
    scopesTotal.value = data.total
    scopesTotalPages.value = data.totalPages
    selectedScopes.value = []
  } catch { /* useApi handles toast */ }
  finally { loading.value = false }
}

function toggleScopeSelect(scope: MandateScope) {
  const idx = selectedScopes.value.findIndex(
    s => s.userCode === scope.userCode && s.scopeType === scope.scopeType && s.mandateId === scope.mandateId
  )
  if (idx >= 0) selectedScopes.value.splice(idx, 1)
  else selectedScopes.value.push({ scopeType: scope.scopeType, userCode: scope.userCode, mandateId: scope.mandateId })
}

function isScopeSelected(scope: MandateScope): boolean {
  return selectedScopes.value.some(
    s => s.userCode === scope.userCode && s.scopeType === scope.scopeType && s.mandateId === scope.mandateId
  )
}

function toggleSelectAllScopes() {
  if (scopesAllSelected.value) {
    selectedScopes.value = []
  } else {
    selectedScopes.value = scopes.value.map(s => ({
      scopeType: s.scopeType,
      userCode: s.userCode,
      mandateId: s.mandateId,
    }))
  }
}

function switchViewMode(mode: 'users' | 'mandates') {
  viewMode.value = mode
  selectedScopes.value = []
  selectedCodes.value = []
  if (mode === 'mandates') {
    scopesPage.value = 1
    loadScopes()
  } else {
    page.value = 1
    loadUsers()
  }
}

watch([scopesPage], () => { if (viewMode.value === 'mandates') loadScopes() })

// Open edit modal focused on a mandate
function openEditModalOnMandate(scope: MandateScope) {
  const user = { code: scope.userCode, identite: scope.identite } as UserListItem
  editingUser.value = user
  editModalOpen.value = true
}

// Bulk add season modal
const bulkAddSeasonOpen = ref(false)
const bulkAddSeasonValue = ref('')
const bulkAddSeasonLoading = ref(false)
const bulkAddSeasonResult = ref<{ updated: number; alreadyPresent: number; restricted: number; season: string } | null>(null)

const restrictedCount = computed(() =>
  selectedScopes.value.filter(s => {
    const scope = scopes.value.find(sc => sc.userCode === s.userCode && sc.scopeType === s.scopeType && sc.mandateId === s.mandateId)
    return scope && scope.filtreSaison === ''
  }).length
)

const selectedBaseCount = computed(() => selectedScopes.value.filter(s => s.scopeType === 'base').length)
const selectedMandateCount = computed(() => selectedScopes.value.filter(s => s.scopeType === 'mandate').length)

function openBulkAddSeason() {
  bulkAddSeasonValue.value = ''
  bulkAddSeasonResult.value = null
  bulkAddSeasonOpen.value = true
}

async function confirmBulkAddSeason() {
  if (!bulkAddSeasonValue.value) return
  bulkAddSeasonLoading.value = true
  try {
    const result = await api.post<{ season: string; updated: number; alreadyPresent: number; restricted: number }>(
      '/admin/users/bulk-add-season',
      {
        season: bulkAddSeasonValue.value,
        scopes: selectedScopes.value.map(s => ({
          type: s.scopeType,
          userCode: s.userCode,
          ...(s.mandateId !== null ? { mandateId: s.mandateId } : {}),
        })),
      }
    )
    bulkAddSeasonResult.value = result
    selectedScopes.value = []
    loadScopes()
  } catch { /* useApi handles toast */ }
  finally { bulkAddSeasonLoading.value = false }
}

// CSV export
const exporting = ref(false)
const canExport = computed(() => authStore.hasProfile(2))
const exportEnabled = computed(() => !!filterProfile.value || !!filterSeason.value || filterCompetitions.value.length > 0)

async function exportCsv() {
  if (!exportEnabled.value) return
  exporting.value = true
  try {
    const qs = new URLSearchParams()
    if (searchQuery.value) qs.set('search', searchQuery.value)
    if (filterProfile.value) qs.set('profile', filterProfile.value)
    if (filterSeason.value) qs.set('season', filterSeason.value)
    if (filterCompetitions.value.length > 0) qs.set('competition', filterCompetitions.value.join(','))

    const buffer = await api.getBlob(`/admin/users/export?${qs.toString()}`)
    const blob = new Blob([buffer], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `utilisateurs_${new Date().toISOString().slice(0, 10).replace(/-/g, '')}.csv`
    a.click()
    URL.revokeObjectURL(url)
  } catch { /* useApi handles toast */ }
  finally { exporting.value = false }
}

// Profile options for the filter dropdown — only profiles the current admin can see
const profileOptions = computed(() => {
  const minProfile = authStore.profile > 1 ? authStore.profile : 1
  const options = []
  for (let i = minProfile; i <= 10; i++) {
    options.push({ value: String(i), label: t(`users.profiles.${i}`) })
  }
  return options
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-header-900">{{ t('users.title') }}</h1>
      <NuxtLink
        v-if="canDelete"
        to="/journal"
        class="text-sm text-primary-600 hover:text-primary-800 flex items-center gap-1"
      >
        {{ t('users.activity_log') }}
        <UIcon name="i-heroicons-arrow-top-right-on-square" class="w-3.5 h-3.5" />
      </NuxtLink>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="searchQuery"
      :search-placeholder="t('users.search_placeholder')"
      :add-label="t('users.add')"
      :show-add="canEdit && viewMode === 'users'"
      :show-bulk-delete="canDelete && viewMode === 'users'"
      :bulk-delete-label="t('common.delete_selected')"
      :selected-count="viewMode === 'users' ? selectedCodes.length : selectedScopes.length"
      @add="openAddModal"
      @bulk-delete="confirmBulkDelete"
    >
      <template #left>
        <!-- Mode mandats: bulk-add-season -->
        <button
          v-if="viewMode === 'mandates' && canDelete && selectedScopes.length > 0"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-primary-300 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors"
          @click="openBulkAddSeason"
        >
          <UIcon name="i-heroicons-calendar-days" class="w-4 h-4" />
          {{ t('users.bulk_add_season') }}
        </button>
      </template>
      <template #right>
        <!-- CSV export -->
        <button
          v-if="canExport"
          :disabled="!exportEnabled || exporting"
          :title="exportEnabled ? t('users.export_csv') : t('users.export_csv_disabled_tooltip')"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-header-300 rounded-lg bg-white hover:bg-header-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          @click="exportCsv"
        >
          <UIcon
            :name="exporting ? 'i-heroicons-arrow-path' : 'i-heroicons-arrow-down-tray'"
            :class="['w-4 h-4', exporting && 'animate-spin']"
          />
          {{ t('users.export_csv') }}
        </button>
        <!-- View mode toggle -->
        <div class="inline-flex rounded-lg border border-header-300 overflow-hidden text-sm">
          <button
            :class="['px-3 py-2 transition-colors', viewMode === 'users' ? 'bg-primary-600 text-white' : 'bg-white text-header-600 hover:bg-header-50']"
            @click="switchViewMode('users')"
          >
            {{ t('users.view_mode_users') }}
          </button>
          <button
            :class="['px-3 py-2 transition-colors border-l border-header-300', viewMode === 'mandates' ? 'bg-primary-600 text-white' : 'bg-white text-header-600 hover:bg-header-50']"
            @click="switchViewMode('mandates')"
          >
            {{ t('users.view_mode_mandates') }}
          </button>
        </div>
      </template>
      <template #before-search>
        <!-- Profile filter -->
        <select
          v-model="filterProfile"
          class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
          <option value="">{{ t('users.filter_profile_all') }}</option>
          <option v-for="opt in profileOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>

        <!-- Season filter -->
        <select
          v-model="filterSeason"
          class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
          <option value="">{{ t('users.filter_season_all') }}</option>
          <option v-for="s in seasons" :key="s.code" :value="s.code">
            {{ s.code }}{{ s.active ? ' ★' : '' }}
          </option>
        </select>

        <!-- Competition filter -->
        <AdminUsersCompetitionFilter
          v-model="filterCompetitions"
          :season="filterSeason || undefined"
          :placeholder="t('users.filter_competition_placeholder')"
          :fetch-url="competitionsSearchUrl"
          :auth-token="authStore.token ?? undefined"
          :mandate-id="authStore.activeMandate?.id"
        />
      </template>
    </AdminToolbar>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-header-400" />
    </div>

    <!-- Desktop Table — Mode Mandats -->
    <div v-if="!loading && viewMode === 'mandates'" class="hidden lg:block overflow-x-auto">
      <div v-if="!filterProfile && !filterSeason && filterCompetitions.length === 0" class="text-center py-12 text-header-500">
        <UIcon name="i-heroicons-funnel" class="w-8 h-8 mx-auto mb-2 opacity-40" />
        {{ t('users.mandate_scopes_no_filter') }}
      </div>
      <table v-else-if="scopes.length > 0" class="min-w-full divide-y divide-header-200 bg-white rounded-lg shadow-sm">
        <thead class="bg-header-50">
          <tr>
            <th v-if="canDelete" class="w-10 px-3 py-3">
              <input type="checkbox" :checked="scopesAllSelected" @change="toggleSelectAllScopes">
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.identity') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.profile') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.seasons') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.competitions') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.clubs') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.view_mode_mandates') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-header-200">
          <tr
            v-for="(scope, idx) in scopes"
            :key="`${scope.userCode}-${scope.scopeType}-${scope.mandateId ?? 'base'}-${idx}`"
            :class="['hover:bg-header-50', scope.scopeType === 'mandate' ? 'bg-primary-50/30' : '']"
          >
            <td v-if="canDelete" class="w-10 px-3 py-2" @click.stop>
              <input type="checkbox" :checked="isScopeSelected(scope)" @change="toggleScopeSelect(scope)">
            </td>
            <td class="px-3 py-2">
              <div class="text-sm font-medium text-header-900">{{ scope.identite }}</div>
              <div class="text-xs text-header-500">({{ scope.userCode }})</div>
            </td>
            <td class="px-3 py-2 text-sm font-medium text-header-800">{{ scope.niveau }}</td>
            <td class="px-3 py-2 text-sm text-header-600">{{ formatFilter(scope.filtreSaison, t('users.table.seasons_all')) }}</td>
            <td class="px-3 py-2 text-sm text-header-600 max-w-50 truncate" :title="formatFilter(scope.filtreCompetition, t('users.table.competitions_all'))">
              {{ formatFilter(scope.filtreCompetition, t('users.table.competitions_all')) }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600">{{ scope.limitClubs || '—' }}</td>
            <td class="px-3 py-2">
              <span
                v-if="scope.scopeType === 'base'"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-header-100 text-header-600 text-xs font-medium"
                :title="t('users.mandate_scope_base_aria')"
              >
                <UIcon name="i-heroicons-user" class="w-3 h-3" />
                {{ t('users.mandate_scope_base_badge') }}
              </span>
              <span v-else class="text-sm text-primary-700">{{ scope.mandateLabel }}</span>
            </td>
            <td class="px-3 py-2">
              <button
                v-if="scope.scopeType === 'base' && canEdit"
                class="p-1.5 text-header-500 hover:text-primary-600 rounded"
                :title="t('common.edit')"
                @click="openEditModalOnMandate(scope)"
              >
                <UIcon name="i-heroicons-pencil-square" class="w-5 h-5" />
              </button>
              <button
                v-else-if="scope.scopeType === 'mandate' && canEdit"
                class="p-1.5 text-primary-500 hover:text-primary-700 rounded"
                :title="t('users.mandate_edit')"
                @click="openEditModalOnMandate(scope)"
              >
                <UIcon name="i-heroicons-identification" class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-center py-12 text-header-500">{{ t('users.mandate_scopes_empty') }}</div>
    </div>

    <!-- Pagination mode mandats -->
    <AdminPagination
      v-if="viewMode === 'mandates' && scopesTotal > 0"
      :page="scopesPage"
      :total-pages="scopesTotalPages"
      :total="scopesTotal"
      :limit="limit"
      :showing-text="t('users.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
      :items-per-page-text="t('users.pagination.items_per_page')"
      :show-all="true"
      @update:page="scopesPage = $event"
      @update:limit="limit = $event; scopesPage = 1; loadScopes()"
    />

    <!-- Desktop Table — Mode Utilisateurs -->
    <div v-if="!loading && viewMode === 'users'" class="hidden lg:block overflow-x-auto">
      <table v-if="users.length > 0" class="min-w-full divide-y divide-header-200 bg-white rounded-lg shadow-sm">
        <thead class="bg-header-50">
          <tr>
            <th v-if="canDelete" class="w-10 px-3 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
              >
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.identity') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.function') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.profile') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.seasons') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.competitions') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.events_gamedays') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.clubs') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-header-500 uppercase">{{ t('users.table.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-header-200">
          <tr v-for="user in users" :key="user.code" class="hover:bg-header-50">
            <td v-if="canDelete" class="w-10 px-3 py-2" @click.stop>
              <input
                type="checkbox"
                :checked="selectedCodes.includes(user.code)"
                @change="toggleSelect(user.code)"
              >
            </td>
            <td class="px-3 py-2">
              <div class="text-sm font-medium text-header-900">{{ user.identite }}</div>
              <div class="text-xs text-header-500">({{ user.code }})</div>
            </td>
            <td class="px-3 py-2 text-sm text-header-600">{{ user.fonction }}</td>
            <td class="px-3 py-2">
              <span class="text-sm font-medium">{{ user.niveau }}</span>
              <div v-if="user.mandateCount > 0" class="relative inline-block ml-1">
                <button
                  class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full border border-primary-300 hover:bg-primary-200 transition-colors"
                  @mouseenter="showTooltip(user.code, $event)"
                  @mouseleave="hideTooltip"
                  @focus="showTooltip(user.code, $event)"
                  @blur="hideTooltip"
                >
                  <UIcon name="i-heroicons-identification" class="w-3 h-3" />
                  {{ t('users.mandates.table_mandates', user.mandateCount) }}
                </button>
              </div>
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ formatFilter(user.filtreSaison, t('users.table.seasons_all')) }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600 max-w-50 truncate" :title="formatFilter(user.filtreCompetition, t('users.table.competitions_all'))">
              {{ formatFilter(user.filtreCompetition, t('users.table.competitions_all')) }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ formatEventsGamedays(user) }}
            </td>
            <td class="px-3 py-2 text-sm text-header-600">
              {{ user.limitClubs || '—' }}
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-1">
                <button
                  v-if="canEditUser(user.niveau)"
                  class="p-1.5 text-header-500 hover:text-primary-600 rounded"
                  :title="t('common.edit')"
                  @click="openEditModal(user)"
                >
                  <UIcon name="i-heroicons-pencil-square" class="w-5 h-5" />
                </button>
                <template v-else>
                  <button
                    v-if="canOpenMandatesModal(user.niveau)"
                    class="p-1.5 text-primary-500 hover:text-primary-700 rounded"
                    :title="t('users.mandate_edit')"
                    @click="openEditModal(user)"
                  >
                    <UIcon name="i-heroicons-identification" class="w-5 h-5" />
                  </button>
                  <span
                    v-else-if="canEdit"
                    class="p-1.5 text-header-300 cursor-help"
                    :title="t('users.table.edit_not_allowed')"
                  >
                    <UIcon name="i-heroicons-information-circle" class="w-5 h-5" />
                  </span>
                </template>
                <button
                  v-if="canDeleteUser(user.niveau)"
                  class="p-1.5 text-header-500 hover:text-danger-600 rounded"
                  :title="t('common.delete')"
                  @click="confirmDeleteUser(user)"
                >
                  <UIcon name="i-heroicons-trash" class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-center py-12 text-header-500">
        {{ t('common.no_results') }}
      </div>
    </div>

    <!-- Mobile Cards (mode utilisateurs uniquement) -->
    <AdminCardList
      v-if="viewMode === 'users'"
      :loading="loading"
      :empty="users.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('common.no_results')"
    >
      <AdminCard
        v-for="user in users"
        :key="user.code"
        :show-checkbox="canDelete"
        :checked="selectedCodes.includes(user.code)"
        @toggle-select="toggleSelect(user.code)"
      >
        <template #header>
          <div>
            <span class="font-medium">{{ user.identite }}</span>
            <span class="text-xs text-header-500 ml-1">({{ user.code }})</span>
          </div>
        </template>
        <template #header-right>
          <span class="text-sm font-medium">{{ t('users.table.profile') }} {{ user.niveau }}</span>
          <button
            v-if="user.mandateCount > 0"
            class="inline-flex items-center gap-0.5 ml-1 px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full border border-primary-300 active:bg-primary-200"
            @click.stop="toggleMandates(user.code)"
          >
            <UIcon name="i-heroicons-identification" class="w-3 h-3" />
            {{ t('users.mandates.table_mandates', user.mandateCount) }}
            <UIcon
              :name="expandedMandates.has(user.code) ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
              class="w-3 h-3"
            />
          </button>
        </template>
        <div class="space-y-1 text-sm text-header-600">
          <div v-if="user.fonction">{{ user.fonction }}</div>
          <div>{{ t('users.table.seasons') }}: {{ formatFilter(user.filtreSaison, t('users.table.seasons_all')) }}</div>
          <div>{{ t('users.table.competitions') }}: {{ formatFilter(user.filtreCompetition, t('users.table.competitions_all')) }}</div>
          <div v-if="user.limitClubs">{{ t('users.table.clubs') }}: {{ user.limitClubs }}</div>
        </div>
        <!-- Mandates expand (mobile) -->
        <div v-if="expandedMandates.has(user.code) && user.mandates.length > 0" class="mt-2 border-t border-primary-100 pt-2 space-y-2">
          <div
            v-for="mandate in user.mandates"
            :key="mandate.id"
            class="text-xs bg-primary-50 rounded-lg px-2 py-1.5"
          >
            <div class="font-semibold text-primary-800">{{ mandate.libelle }}</div>
            <div class="text-primary-600 mt-0.5">
              <span class="font-medium">P{{ mandate.niveau }}</span>
              <span class="mx-1">·</span>
              <span>{{ formatMandateFilters(mandate) }}</span>
            </div>
          </div>
        </div>
        <template #footer-right>
          <div class="flex items-center gap-2">
            <button
              v-if="canEditUser(user.niveau)"
              class="px-3 py-1 text-xs text-primary-600 border border-primary-300 rounded hover:bg-primary-50"
              @click="openEditModal(user)"
            >
              {{ t('common.edit') }}
            </button>
            <button
              v-else-if="canOpenMandatesModal(user.niveau)"
              class="px-3 py-1 text-xs text-primary-600 border border-primary-300 rounded hover:bg-primary-50"
              @click="openEditModal(user)"
            >
              {{ t('users.view_mode_mandates') }}
            </button>
            <button
              v-if="canDeleteUser(user.niveau)"
              class="px-3 py-1 text-xs text-danger-600 border border-danger-300 rounded hover:bg-danger-50"
              @click="confirmDeleteUser(user)"
            >
              {{ t('common.delete') }}
            </button>
          </div>
        </template>
      </AdminCard>
    </AdminCardList>

    <!-- Pagination mode utilisateurs -->
    <AdminPagination
      v-if="viewMode === 'users' && total > 0"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      :limit="limit"
      :showing-text="t('users.pagination.showing', { from: '{from}', to: '{to}', total: '{total}' })"
      :items-per-page-text="t('users.pagination.items_per_page')"
      :show-all="true"
      @update:page="page = $event"
      @update:limit="limit = $event; page = 1"
    />

    <!-- Edit Modal -->
    <AdminUserEditModal
      :open="editModalOpen"
      :user="editingUser"
      :seasons="seasons"
      @close="editModalOpen = false"
      @saved="onUserSaved"
    />

    <!-- Confirm Modal -->
    <AdminConfirmModal
      :open="confirmOpen"
      :title="t('common.delete')"
      :message="confirmMessage"
      :loading="deleting"
      variant="danger"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      @close="confirmOpen = false"
      @confirm="confirmAction?.()"
    />

    <!-- Bulk add season modal -->
    <Teleport to="body">
      <div
        v-if="bulkAddSeasonOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="bulkAddSeasonOpen = false"
      >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-semibold text-header-900 mb-4">{{ t('users.bulk_add_season_modal_title') }}</h3>

          <template v-if="!bulkAddSeasonResult">
            <!-- Summary -->
            <p class="text-sm text-header-600 mb-4">
              {{ t('users.bulk_add_season_summary', { base: selectedBaseCount, mandates: selectedMandateCount }) }}
            </p>

            <!-- Season select -->
            <label class="block text-sm font-medium text-header-700 mb-1">{{ t('users.bulk_add_season_select_label') }}</label>
            <select
              v-model="bulkAddSeasonValue"
              class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-4"
            >
              <option value="">—</option>
              <option v-for="s in seasons" :key="s.code" :value="s.code">
                {{ s.code }}{{ s.active ? ' ★' : '' }}
              </option>
            </select>

            <!-- Warning for empty filtre_saison scopes -->
            <div v-if="restrictedCount > 0 && bulkAddSeasonValue" class="flex gap-2 p-3 mb-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
              <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
              <span>{{ t('users.bulk_add_season_warning_restricted', { count: restrictedCount, season: bulkAddSeasonValue }) }}</span>
            </div>

            <div class="flex justify-end gap-2">
              <button
                class="px-4 py-2 text-sm text-header-600 hover:text-header-800"
                @click="bulkAddSeasonOpen = false"
              >
                {{ t('common.cancel') }}
              </button>
              <button
                :disabled="!bulkAddSeasonValue || bulkAddSeasonLoading"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed"
                @click="confirmBulkAddSeason"
              >
                <UIcon v-if="bulkAddSeasonLoading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
                {{ t('users.bulk_add_season_confirm') }}
              </button>
            </div>
          </template>

          <!-- Result -->
          <template v-else>
            <div class="space-y-2 text-sm mb-6">
              <div class="flex items-center gap-2 text-green-700">
                <UIcon name="i-heroicons-check-circle" class="w-4 h-4 shrink-0" />
                {{ t('users.bulk_add_season_result_updated', { count: bulkAddSeasonResult.updated, season: bulkAddSeasonResult.season }) }}
              </div>
              <div v-if="bulkAddSeasonResult.alreadyPresent > 0" class="flex items-center gap-2 text-header-500">
                <UIcon name="i-heroicons-minus-circle" class="w-4 h-4 shrink-0" />
                {{ t('users.bulk_add_season_result_already_present', { count: bulkAddSeasonResult.alreadyPresent }) }}
              </div>
              <div v-if="bulkAddSeasonResult.restricted > 0" class="flex items-center gap-2 text-amber-700">
                <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
                {{ t('users.bulk_add_season_result_restricted', { count: bulkAddSeasonResult.restricted, season: bulkAddSeasonResult.season }) }}
              </div>
            </div>
            <div class="flex justify-end">
              <button
                class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                @click="bulkAddSeasonOpen = false; bulkAddSeasonResult = null"
              >
                {{ t('common.close') }}
              </button>
            </div>
          </template>
        </div>
      </div>
    </Teleport>

    <AdminScrollToTop />

    <!-- Mandate tooltip (desktop) -->
    <Teleport to="body">
      <div
        v-if="tooltipUser"
        ref="tooltipRef"
        class="fixed z-50 max-w-xs bg-white border border-header-200 rounded-lg shadow-lg p-3 text-xs pointer-events-none"
        :style="tooltipStyle"
      >
        <template v-for="mandate in users.find(u => u.code === tooltipUser)?.mandates ?? []" :key="mandate.id">
          <div class="mb-2 last:mb-0">
            <div class="font-semibold text-header-800">{{ mandate.libelle }}</div>
            <div class="text-header-500 mt-0.5">
              <span class="font-medium text-header-600">P{{ mandate.niveau }}</span>
              <span class="mx-1">·</span>
              <span>{{ formatMandateFilters(mandate) }}</span>
            </div>
          </div>
        </template>
      </div>
    </Teleport>
  </div>
</template>
