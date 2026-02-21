<script setup lang="ts">
import type { UserListItem, UsersResponse } from '~/types/users'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()
const router = useRouter()

// Profile guard: only profiles <= 4 can access
if (authStore.profile > 4) {
  navigateTo('/')
}

// Permissions
const canEdit = computed(() => authStore.hasProfile(3))
const canDelete = computed(() => authStore.hasProfile(2))

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
    loadUsers()
  }, 300)
})

watch([filterProfile, filterSeason], () => {
  page.value = 1
  loadUsers()
})

watch([page, limit], () => {
  loadUsers()
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

// Profile options for the filter dropdown
const profileOptions = computed(() => {
  const options = []
  for (let i = 1; i <= 10; i++) {
    options.push({ value: String(i), label: t(`users.profiles.${i}`) })
  }
  return options
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-gray-900">{{ t('users.title') }}</h1>
      <NuxtLink
        v-if="canDelete"
        to="/activity-log"
        class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
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
      :show-add="canEdit"
      :show-bulk-delete="canDelete"
      :bulk-delete-label="t('common.delete_selected')"
      :selected-count="selectedCodes.length"
      @add="openAddModal"
      @bulk-delete="confirmBulkDelete"
    >
      <template #before-search>
        <!-- Profile filter -->
        <select
          v-model="filterProfile"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">{{ t('users.filter_profile_all') }}</option>
          <option v-for="opt in profileOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>

        <!-- Season filter -->
        <select
          v-model="filterSeason"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">{{ t('users.filter_season_all') }}</option>
          <option v-for="s in seasons" :key="s.code" :value="s.code">
            {{ s.code }}{{ s.active ? ' ★' : '' }}
          </option>
        </select>
      </template>
    </AdminToolbar>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
    </div>

    <!-- Desktop Table -->
    <div v-else class="hidden lg:block overflow-x-auto">
      <table v-if="users.length > 0" class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow-sm">
        <thead class="bg-gray-50">
          <tr>
            <th v-if="canDelete" class="w-10 px-3 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
              >
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.identity') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.function') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.profile') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.seasons') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.competitions') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.events_gamedays') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.clubs') }}</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('users.table.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.code" class="hover:bg-gray-50">
            <td v-if="canDelete" class="w-10 px-3 py-2" @click.stop>
              <input
                type="checkbox"
                :checked="selectedCodes.includes(user.code)"
                @change="toggleSelect(user.code)"
              >
            </td>
            <td class="px-3 py-2">
              <div class="text-sm font-medium text-gray-900">{{ user.identite }}</div>
              <div class="text-xs text-gray-500">({{ user.code }})</div>
            </td>
            <td class="px-3 py-2 text-sm text-gray-600">{{ user.fonction }}</td>
            <td class="px-3 py-2">
              <span class="text-sm font-medium">{{ user.niveau }}</span>
              <div v-if="user.mandateCount > 0" class="text-xs text-blue-600">
                {{ t('users.mandates.table_mandates', { count: user.mandateCount }) }}
              </div>
            </td>
            <td class="px-3 py-2 text-sm text-gray-600">
              {{ formatFilter(user.filtreSaison, t('users.table.seasons_all')) }}
            </td>
            <td class="px-3 py-2 text-sm text-gray-600 max-w-50 truncate" :title="formatFilter(user.filtreCompetition, t('users.table.competitions_all'))">
              {{ formatFilter(user.filtreCompetition, t('users.table.competitions_all')) }}
            </td>
            <td class="px-3 py-2 text-sm text-gray-600">
              {{ formatEventsGamedays(user) }}
            </td>
            <td class="px-3 py-2 text-sm text-gray-600">
              {{ user.limitClubs || '—' }}
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-1">
                <button
                  v-if="canEdit"
                  class="p-1.5 text-gray-500 hover:text-blue-600 rounded"
                  :title="t('common.edit')"
                  @click="openEditModal(user)"
                >
                  <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
                </button>
                <button
                  v-if="canDelete"
                  class="p-1.5 text-gray-500 hover:text-red-600 rounded"
                  :title="t('common.delete')"
                  @click="confirmDeleteUser(user)"
                >
                  <UIcon name="i-heroicons-trash" class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-center py-12 text-gray-500">
        {{ t('common.no_results') }}
      </div>
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
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
            <span class="text-xs text-gray-500 ml-1">({{ user.code }})</span>
          </div>
        </template>
        <template #header-right>
          <span class="text-sm font-medium">{{ t('users.table.profile') }} {{ user.niveau }}</span>
          <span v-if="user.mandateCount > 0" class="text-xs text-blue-600 ml-1">
            {{ t('users.mandates.table_mandates', { count: user.mandateCount }) }}
          </span>
        </template>
        <div class="space-y-1 text-sm text-gray-600">
          <div v-if="user.fonction">{{ user.fonction }}</div>
          <div>{{ t('users.table.seasons') }}: {{ formatFilter(user.filtreSaison, t('users.table.seasons_all')) }}</div>
          <div>{{ t('users.table.competitions') }}: {{ formatFilter(user.filtreCompetition, t('users.table.competitions_all')) }}</div>
          <div v-if="user.limitClubs">{{ t('users.table.clubs') }}: {{ user.limitClubs }}</div>
        </div>
        <template #footer-right>
          <div class="flex items-center gap-2">
            <button
              v-if="canEdit"
              class="px-3 py-1 text-xs text-blue-600 border border-blue-300 rounded hover:bg-blue-50"
              @click="openEditModal(user)"
            >
              {{ t('common.edit') }}
            </button>
            <button
              v-if="canDelete"
              class="px-3 py-1 text-xs text-red-600 border border-red-300 rounded hover:bg-red-50"
              @click="confirmDeleteUser(user)"
            >
              {{ t('common.delete') }}
            </button>
          </div>
        </template>
      </AdminCard>
    </AdminCardList>

    <!-- Pagination -->
    <AdminPagination
      v-if="total > 0"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      :limit="limit"
      :showing-text="t('users.pagination.showing')"
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

    <AdminScrollToTop />
  </div>
</template>
