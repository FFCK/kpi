<script setup lang="ts">
import type {
  PoolGroup,
  PoolReferee,
  PoolListResponse,
  LicenceSearchResult,
  ArbitrationCode,
  PoolStatus,
  AddRefereePayload,
  UpdateRefereePayload,
} from '~/types/referees-pool'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t } = useI18n()
const api = useApi()
const toast = useToast()
const authStore = useAuthStore()
const config = useRuntimeConfig()

const legacyBase = computed(() => config.public.legacyBaseUrl)

function groupLogoUrl(group: PoolGroup): string | null {
  if (group.logo) return `${legacyBase.value}/img/${group.logo}`
  // Otherwise the group label is the nation code (FRA, GER...) → its flag.
  // (Code_club is 'ICF' for international groups and has no flag of its own.)
  if (group.libelle) {
    return `${legacyBase.value}/img/Nations/${group.libelle}.png`
  }
  return null
}

// Pool management is admin-only (profile <= 2).
const canManage = computed(() => authStore.profile <= 2)

const ARBITRATION_CODES: ArbitrationCode[] = ['REG', 'IR', 'NAT', 'INT', 'OTM', 'JO']

// ── State ──
const loading = ref(false)
const groups = ref<PoolGroup[]>([])
const expandedIds = ref<Set<number>>(new Set())

const allExpanded = computed(() => groups.value.length > 0 && groups.value.every(g => expandedIds.value.has(g.id)))

async function loadPool() {
  loading.value = true
  try {
    const data = await api.get<PoolListResponse>('/admin/referees-pool')
    groups.value = data.groups || []
  } catch {
    toast.add({ title: t('common.error'), description: t('common.load_error'), color: 'error' })
  } finally {
    loading.value = false
  }
}

function toggleGroup(id: number) {
  if (expandedIds.value.has(id)) expandedIds.value.delete(id)
  else expandedIds.value.add(id)
  // Trigger reactivity on the Set.
  expandedIds.value = new Set(expandedIds.value)
}

function toggleAll() {
  if (allExpanded.value) {
    expandedIds.value = new Set()
  } else {
    expandedIds.value = new Set(groups.value.map(g => g.id))
  }
}

// ── Group create / rename modal ──
const groupModalOpen = ref(false)
const groupModalMode = ref<'create' | 'rename'>('create')
const groupForm = ref<{ id: number | null; libelle: string; codeClub: string }>({ id: null, libelle: '', codeClub: '' })
const groupSaving = ref(false)

function openCreateGroup() {
  groupModalMode.value = 'create'
  groupForm.value = { id: null, libelle: '', codeClub: '' }
  groupModalOpen.value = true
}

function openRenameGroup(group: PoolGroup) {
  groupModalMode.value = 'rename'
  groupForm.value = { id: group.id, libelle: group.libelle, codeClub: group.codeClub }
  groupModalOpen.value = true
}

async function submitGroup() {
  const libelle = groupForm.value.libelle.trim()
  if (!libelle) return
  groupSaving.value = true
  try {
    if (groupModalMode.value === 'create') {
      await api.post('/admin/referees-pool/groups', { libelle, codeClub: groupForm.value.codeClub.trim() })
      toast.add({ title: t('common.success'), description: t('referees_pool_page.success_group_created'), color: 'success' })
    } else {
      await api.patch(`/admin/referees-pool/groups/${groupForm.value.id}`, { libelle })
      toast.add({ title: t('common.success'), description: t('referees_pool_page.success_group_updated'), color: 'success' })
    }
    groupModalOpen.value = false
    await loadPool()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('referees_pool_page.error_save'), color: 'error' })
  } finally {
    groupSaving.value = false
  }
}

// ── Group delete ──
const deleteGroupModalOpen = ref(false)
const groupToDelete = ref<PoolGroup | null>(null)
const groupDeleting = ref(false)

function openDeleteGroup(group: PoolGroup) {
  groupToDelete.value = group
  deleteGroupModalOpen.value = true
}

async function confirmDeleteGroup() {
  if (!groupToDelete.value) return
  groupDeleting.value = true
  try {
    await api.del(`/admin/referees-pool/groups/${groupToDelete.value.id}`)
    toast.add({ title: t('common.success'), description: t('referees_pool_page.success_group_deleted'), color: 'success' })
    deleteGroupModalOpen.value = false
    groupToDelete.value = null
    await loadPool()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('referees_pool_page.error_save'), color: 'error' })
  } finally {
    groupDeleting.value = false
  }
}

// ── Add / edit referee modal ──
const refereeModalOpen = ref(false)
const refereeModalMode = ref<'add' | 'edit'>('add')
const addMode = ref<'licence' | 'manual'>('licence')
const refereeGroup = ref<PoolGroup | null>(null)
const refereeSaving = ref(false)
const refereeForm = ref<{
  matric: number | null
  nom: string
  prenom: string
  sexe: string
  naissance: string
  arbitre: ArbitrationCode
  niveau: string
}>({ matric: null, nom: '', prenom: '', sexe: '', naissance: '', arbitre: '', niveau: '' })

// Licence search
const licenceQuery = ref('')
const licenceResults = ref<LicenceSearchResult[]>([])
const licenceSearching = ref(false)
const selectedLicence = ref<LicenceSearchResult | null>(null)
let licenceDebounce: ReturnType<typeof setTimeout> | null = null

function openAddReferee(group: PoolGroup) {
  refereeModalMode.value = 'add'
  addMode.value = 'licence'
  refereeGroup.value = group
  refereeForm.value = { matric: null, nom: '', prenom: '', sexe: '', naissance: '', arbitre: '', niveau: '' }
  licenceQuery.value = ''
  licenceResults.value = []
  selectedLicence.value = null
  refereeModalOpen.value = true
}

function openEditReferee(group: PoolGroup, ref_: PoolReferee) {
  refereeModalMode.value = 'edit'
  refereeGroup.value = group
  refereeForm.value = {
    matric: ref_.matric,
    nom: ref_.nom,
    prenom: ref_.prenom,
    sexe: ref_.sexe,
    naissance: '',
    arbitre: ref_.arbitre,
    niveau: ref_.niveau,
  }
  refereeModalOpen.value = true
}

watch(licenceQuery, (q) => {
  selectedLicence.value = null
  if (licenceDebounce) clearTimeout(licenceDebounce)
  if (q.trim().length < 2) {
    licenceResults.value = []
    return
  }
  licenceDebounce = setTimeout(async () => {
    licenceSearching.value = true
    try {
      licenceResults.value = await api.get<LicenceSearchResult[]>('/admin/referees-pool/search-licence', { q: q.trim() })
    } catch {
      licenceResults.value = []
    } finally {
      licenceSearching.value = false
    }
  }, 300)
})

function selectLicence(item: LicenceSearchResult) {
  selectedLicence.value = item
  licenceResults.value = []
  licenceQuery.value = `${item.nom} ${item.prenom}`
}

const canSubmitReferee = computed(() => {
  if (refereeModalMode.value === 'edit') return refereeForm.value.nom.trim().length > 0
  if (addMode.value === 'licence') return !!selectedLicence.value
  return refereeForm.value.nom.trim().length > 0
})

async function submitReferee() {
  if (!refereeGroup.value || !canSubmitReferee.value) return
  refereeSaving.value = true
  try {
    if (refereeModalMode.value === 'edit') {
      const payload: UpdateRefereePayload = {
        nom: refereeForm.value.nom.trim(),
        prenom: refereeForm.value.prenom.trim(),
        sexe: refereeForm.value.sexe,
        arbitre: refereeForm.value.arbitre,
        niveau: refereeForm.value.niveau.trim(),
      }
      await api.patch(`/admin/referees-pool/groups/${refereeGroup.value.id}/referees/${refereeForm.value.matric}`, payload)
      toast.add({ title: t('common.success'), description: t('referees_pool_page.success_referee_updated'), color: 'success' })
    } else {
      let payload: AddRefereePayload
      if (addMode.value === 'licence') {
        payload = { mode: 'licence', matric: selectedLicence.value!.matric }
      } else {
        payload = {
          mode: 'manual',
          nom: refereeForm.value.nom.trim(),
          prenom: refereeForm.value.prenom.trim(),
          sexe: refereeForm.value.sexe,
          naissance: refereeForm.value.naissance,
          arbitre: refereeForm.value.arbitre,
          niveau: refereeForm.value.niveau.trim(),
        }
      }
      await api.post(`/admin/referees-pool/groups/${refereeGroup.value.id}/referees`, payload)
      toast.add({ title: t('common.success'), description: t('referees_pool_page.success_referee_added'), color: 'success' })
    }
    refereeModalOpen.value = false
    await loadPool()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('referees_pool_page.error_save'), color: 'error' })
  } finally {
    refereeSaving.value = false
  }
}

// ── Remove referee ──
const removeRefModalOpen = ref(false)
const refToRemove = ref<{ group: PoolGroup; ref: PoolReferee } | null>(null)
const refRemoving = ref(false)

function openRemoveReferee(group: PoolGroup, ref_: PoolReferee) {
  refToRemove.value = { group, ref: ref_ }
  removeRefModalOpen.value = true
}

async function confirmRemoveReferee() {
  if (!refToRemove.value) return
  refRemoving.value = true
  try {
    await api.del(`/admin/referees-pool/groups/${refToRemove.value.group.id}/referees/${refToRemove.value.ref.matric}`)
    toast.add({ title: t('common.success'), description: t('referees_pool_page.success_referee_removed'), color: 'success' })
    removeRefModalOpen.value = false
    refToRemove.value = null
    await loadPool()
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('referees_pool_page.error_save'), color: 'error' })
  } finally {
    refRemoving.value = false
  }
}

const refToRemoveName = computed(() =>
  refToRemove.value ? `${refToRemove.value.ref.nom} ${refToRemove.value.ref.prenom}` : '',
)

// ── Inline status change (active 'A' / inactive 'X') ──
const statusSaving = ref<number | null>(null)

// Active referees first, inactive last, then alphabetical — mirrors the backend ordering.
function sortReferees(list: PoolReferee[]) {
  list.sort((a, b) => {
    if (a.status !== b.status) return a.status === 'X' ? 1 : -1
    return a.nom.localeCompare(b.nom) || a.prenom.localeCompare(b.prenom)
  })
}

async function changeStatus(group: PoolGroup, ref_: PoolReferee, status: PoolStatus) {
  if (ref_.status === status) return
  const previous = ref_.status
  ref_.status = status // optimistic
  sortReferees(group.referees) // move inactive rows to the bottom immediately
  statusSaving.value = ref_.matric
  try {
    await api.patch(`/admin/referees-pool/groups/${group.id}/referees/${ref_.matric}/status`, { status })
    toast.add({ title: t('common.saved'), color: 'success' })
  } catch (error: unknown) {
    ref_.status = previous // rollback
    sortReferees(group.referees)
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('referees_pool_page.error_save'), color: 'error' })
  } finally {
    statusSaving.value = null
  }
}

onMounted(loadPool)
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="flex items-start justify-between mb-4 gap-4 flex-wrap">
      <div>
        <h1 class="text-2xl font-bold text-header-900">
          {{ t('referees_pool_page.title') }}
        </h1>
        <p class="text-sm text-header-500 mt-1">
          {{ t('referees_pool_page.subtitle') }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="groups.length > 0"
          class="px-3 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200 flex items-center gap-1.5"
          @click="toggleAll"
        >
          <UIcon :name="allExpanded ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'" class="w-4 h-4" />
          {{ allExpanded ? t('common.collapse_all') : t('common.expand_all') }}
        </button>
        <button
          v-if="canManage"
          class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-1.5"
          @click="openCreateGroup"
        >
          <UIcon name="i-heroicons-plus" class="w-4 h-4" />
          {{ t('referees_pool_page.add_group') }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-14 bg-header-100 rounded-lg animate-pulse" />
    </div>

    <!-- Empty -->
    <div v-else-if="groups.length === 0" class="text-center py-16 text-header-400">
      <UIcon name="i-heroicons-flag" class="w-12 h-12 mx-auto mb-3" />
      <p>{{ t('referees_pool_page.no_groups') }}</p>
    </div>

    <!-- Groups -->
    <div v-else class="space-y-3">
      <div v-for="group in groups" :key="group.id" class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Group header -->
        <div class="flex items-center justify-between px-4 py-3 bg-header-50 border-b border-header-200">
          <button class="flex items-center gap-3 flex-1 text-left" @click="toggleGroup(group.id)">
            <UIcon
              :name="expandedIds.has(group.id) ? 'i-heroicons-chevron-down' : 'i-heroicons-chevron-right'"
              class="w-5 h-5 text-header-400 shrink-0"
            />
            <img
              v-if="groupLogoUrl(group)"
              :src="groupLogoUrl(group)!"
              :alt="group.libelle"
              class="w-6 h-6 object-contain shrink-0"
              @error="($event.target as HTMLImageElement).style.display = 'none'"
            >
            <span class="font-semibold text-header-900">{{ group.libelle }}</span>
            <span class="text-xs text-header-500">
              {{ t('referees_pool_page.referee_count', { count: group.refereeCount }) }}
            </span>
          </button>
          <div v-if="canManage" class="flex items-center gap-1">
            <button
              class="p-1.5 text-header-500 hover:text-primary-600 hover:bg-primary-50 rounded"
              :title="t('referees_pool_page.add_referee')"
              @click="openAddReferee(group)"
            >
              <UIcon name="i-heroicons-user-plus" class="w-4 h-4" />
            </button>
            <button
              class="p-1.5 text-header-500 hover:text-primary-600 hover:bg-primary-50 rounded"
              :title="t('referees_pool_page.rename_group')"
              @click="openRenameGroup(group)"
            >
              <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
            </button>
            <button
              class="p-1.5 text-header-500 hover:text-danger-600 hover:bg-danger-50 rounded"
              :title="t('referees_pool_page.delete_group')"
              @click="openDeleteGroup(group)"
            >
              <UIcon name="i-heroicons-trash" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Referees -->
        <div v-if="expandedIds.has(group.id)">
          <div v-if="group.referees.length === 0" class="px-4 py-6 text-sm text-header-400 text-center">
            {{ t('referees_pool_page.no_referees') }}
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs text-header-500 uppercase border-b border-header-100">
                <th class="px-4 py-2 font-medium">{{ t('common.license') }}</th>
                <th class="px-4 py-2 font-medium">{{ t('common.last_name') }}</th>
                <th class="px-4 py-2 font-medium">{{ t('common.first_name') }}</th>
                <th class="px-2 py-2 font-medium">{{ t('referees_pool_page.sex') }}</th>
                <th class="px-2 py-2 font-medium">{{ t('referees_pool_page.arbitration') }}</th>
                <th class="px-2 py-2 font-medium">{{ t('referees_pool_page.status') }}</th>
                <th v-if="canManage" class="px-4 py-2 font-medium text-right">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="ref_ in group.referees"
                :key="ref_.matric"
                class="border-b border-header-50 hover:bg-header-50"
                :class="ref_.status === 'X' ? 'opacity-50' : ''"
              >
                <td class="px-4 py-2 font-mono text-xs text-header-600">
                  {{ ref_.matric }}
                  <UIcon
                    v-if="ref_.licensed"
                    name="i-heroicons-lock-closed"
                    class="w-3 h-3 inline text-header-400 ml-1"
                    :title="t('referees_pool_page.licensed_readonly')"
                  />
                </td>
                <td class="px-4 py-2 font-medium text-header-900">{{ ref_.nom }}</td>
                <td class="px-4 py-2 text-header-700">{{ ref_.prenom }}</td>
                <td class="px-2 py-2 text-header-600">{{ ref_.sexe }}</td>
                <td class="px-2 py-2">
                  <span v-if="ref_.arbitreLabel" class="inline-block px-2 py-0.5 text-xs bg-primary-50 text-primary-700 rounded">
                    {{ ref_.arbitreLabel }}
                  </span>
                </td>
                <td class="px-2 py-2">
                  <select
                    v-if="canManage"
                    :value="ref_.status"
                    :disabled="statusSaving === ref_.matric"
                    class="px-2 py-1 text-xs border border-header-300 rounded bg-white focus:ring-2 focus:ring-primary-500 disabled:opacity-50"
                    @change="changeStatus(group, ref_, ($event.target as HTMLSelectElement).value as PoolStatus)"
                  >
                    <option value="A">{{ t('referees_pool_page.status_active') }}</option>
                    <option value="X">{{ t('referees_pool_page.status_inactive') }}</option>
                  </select>
                  <span
                    v-else
                    class="inline-block px-2 py-0.5 text-xs rounded"
                    :class="ref_.status === 'X' ? 'bg-header-100 text-header-500' : 'bg-success-50 text-success-700'"
                  >
                    {{ ref_.status === 'X' ? t('referees_pool_page.status_inactive') : t('referees_pool_page.status_active') }}
                  </span>
                </td>
                <td v-if="canManage" class="px-4 py-2 text-right whitespace-nowrap">
                  <button
                    v-if="!ref_.licensed"
                    class="p-1.5 text-header-500 hover:text-primary-600 hover:bg-primary-50 rounded"
                    :title="t('referees_pool_page.edit_referee')"
                    @click="openEditReferee(group, ref_)"
                  >
                    <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
                  </button>
                  <button
                    class="p-1.5 text-header-500 hover:text-danger-600 hover:bg-danger-50 rounded"
                    :title="t('referees_pool_page.remove_referee')"
                    @click="openRemoveReferee(group, ref_)"
                  >
                    <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ═══ Group create/rename modal ═══ -->
    <AdminModal
      :open="groupModalOpen"
      :title="groupModalMode === 'create' ? t('referees_pool_page.add_group') : t('referees_pool_page.rename_group')"
      max-width="md"
      @close="groupModalOpen = false"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('referees_pool_page.group_name') }} <span class="text-danger-500">*</span>
          </label>
          <input
            v-model="groupForm.libelle"
            type="text"
            maxlength="30"
            :placeholder="t('referees_pool_page.group_name_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @keyup.enter="submitGroup"
          >
        </div>
        <div v-if="groupModalMode === 'create'">
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('referees_pool_page.club_code') }}
          </label>
          <input
            v-model="groupForm.codeClub"
            type="text"
            maxlength="6"
            :placeholder="t('referees_pool_page.club_code_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
        </div>
      </div>
      <template #footer>
        <button class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200" @click="groupModalOpen = false">
          {{ t('common.cancel') }}
        </button>
        <button
          class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
          :disabled="groupSaving || !groupForm.libelle.trim()"
          @click="submitGroup"
        >
          <UIcon v-if="groupSaving" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('common.save') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══ Add / edit referee modal ═══ -->
    <AdminModal
      :open="refereeModalOpen"
      :title="refereeModalMode === 'add' ? t('referees_pool_page.add_referee') : t('referees_pool_page.edit_referee')"
      max-width="lg"
      @close="refereeModalOpen = false"
    >
      <div class="space-y-4">
        <!-- Mode tabs (add only) -->
        <div v-if="refereeModalMode === 'add'" class="flex gap-2 border-b border-header-200">
          <button
            class="px-3 py-2 text-sm font-medium border-b-2 -mb-px"
            :class="addMode === 'licence' ? 'border-primary-600 text-primary-700' : 'border-transparent text-header-500 hover:text-header-700'"
            @click="addMode = 'licence'"
          >
            {{ t('referees_pool_page.add_mode_licence') }}
          </button>
          <button
            class="px-3 py-2 text-sm font-medium border-b-2 -mb-px"
            :class="addMode === 'manual' ? 'border-primary-600 text-primary-700' : 'border-transparent text-header-500 hover:text-header-700'"
            @click="addMode = 'manual'"
          >
            {{ t('referees_pool_page.add_mode_manual') }}
          </button>
        </div>

        <!-- Licence search -->
        <div v-if="refereeModalMode === 'add' && addMode === 'licence'" class="relative">
          <label class="block text-sm font-medium text-header-700 mb-1">{{ t('common.license') }}</label>
          <input
            v-model="licenceQuery"
            type="text"
            :placeholder="t('referees_pool_page.search_licence_placeholder')"
            class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
          <div v-if="licenceSearching" class="absolute right-3 top-9">
            <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin text-header-400" />
          </div>
          <div
            v-if="licenceResults.length > 0"
            class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
          >
            <button
              v-for="item in licenceResults"
              :key="item.matric"
              class="w-full px-3 py-2 text-left text-sm hover:bg-primary-50 flex items-center gap-2 border-b border-header-50"
              @click="selectLicence(item)"
            >
              <span class="font-mono text-xs text-header-500">{{ item.matric }}</span>
              <span class="font-medium">{{ item.nom }} {{ item.prenom }}</span>
              <span v-if="item.clubLibelle" class="text-xs text-header-400">({{ item.clubLibelle }})</span>
              <span v-if="item.arbitreLabel" class="ml-auto text-xs text-primary-600">{{ item.arbitreLabel }}</span>
            </button>
          </div>
          <p v-if="selectedLicence" class="mt-2 text-sm text-success-700 flex items-center gap-1">
            <UIcon name="i-heroicons-check-circle" class="w-4 h-4" />
            {{ selectedLicence.nom }} {{ selectedLicence.prenom }}
            <span class="text-header-400">— {{ t('referees_pool_page.licensed') }} ({{ selectedLicence.matric }})</span>
          </p>
        </div>

        <!-- Manual fields (manual add OR edit of a non-licensed referee) -->
        <template v-if="refereeModalMode === 'edit' || (refereeModalMode === 'add' && addMode === 'manual')">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('common.last_name') }} <span class="text-danger-500">*</span>
              </label>
              <input v-model="refereeForm.nom" type="text" maxlength="30" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('common.first_name') }}</label>
              <input v-model="refereeForm.prenom" type="text" maxlength="30" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('referees_pool_page.sex') }}</label>
              <select v-model="refereeForm.sexe" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="" />
                <option value="M">M</option>
                <option value="F">F</option>
              </select>
            </div>
            <div v-if="refereeModalMode === 'add'">
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('referees_pool_page.birthdate') }}</label>
              <input v-model="refereeForm.naissance" type="date" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" >
            </div>
          </div>

          <!-- Arbitration status -->
          <div class="grid grid-cols-2 gap-3 pt-2 border-t border-header-100">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('referees_pool_page.arbitration') }}</label>
              <select v-model="refereeForm.arbitre" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">{{ t('referees_pool_page.arbitration_none') }}</option>
                <option v-for="code in ARBITRATION_CODES" :key="code" :value="code">{{ code }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">{{ t('referees_pool_page.arbitration_level') }}</label>
              <input v-model="refereeForm.niveau" type="text" maxlength="1" class="w-full px-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" >
            </div>
          </div>
        </template>
      </div>

      <template #footer>
        <button class="px-4 py-2 text-sm text-header-700 bg-header-100 rounded-lg hover:bg-header-200" @click="refereeModalOpen = false">
          {{ t('common.cancel') }}
        </button>
        <button
          class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2"
          :disabled="refereeSaving || !canSubmitReferee"
          @click="submitReferee"
        >
          <UIcon v-if="refereeSaving" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ refereeModalMode === 'add' ? t('common.add') : t('common.save') }}
        </button>
      </template>
    </AdminModal>

    <!-- ═══ Delete group confirm ═══ -->
    <AdminConfirmModal
      :open="deleteGroupModalOpen"
      variant="danger"
      :title="t('referees_pool_page.delete_group')"
      :message="groupToDelete ? t('referees_pool_page.delete_group_confirm', { name: groupToDelete.libelle, count: groupToDelete.refereeCount }) : ''"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="groupDeleting"
      @close="deleteGroupModalOpen = false"
      @confirm="confirmDeleteGroup"
    />

    <!-- ═══ Remove referee confirm ═══ -->
    <AdminConfirmModal
      :open="removeRefModalOpen"
      variant="warning"
      :title="t('referees_pool_page.remove_referee')"
      :message="t('referees_pool_page.remove_referee_confirm', { name: refToRemoveName })"
      :confirm-text="t('referees_pool_page.remove_referee')"
      :cancel-text="t('common.cancel')"
      :loading="refRemoving"
      @close="removeRefModalOpen = false"
      @confirm="confirmRemoveReferee"
    />
  </div>
</template>
