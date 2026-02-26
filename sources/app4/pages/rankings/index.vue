<script setup lang="ts">
import type {
  RankingCompetitionInfo,
  RankingTypeOption,
  RankingTeam,
  RankingPhase,
  RankingPhaseTeam,
  RankingResponse,
  TransferResult,
  TransferCompetition
} from '~/types/rankings'
import type { CompetitionStatus, CompetitionType } from '~/types/competitions'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()
const config = useRuntimeConfig()
const toast = useToast()

// State
const loading = ref(false)
const competitionInfo = ref<RankingCompetitionInfo | null>(null)
const rankingTypes = ref<RankingTypeOption[]>([])
const ranking = ref<RankingTeam[]>([])
const phases = ref<RankingPhase[]>([])

// Active tab: 'computed' or 'published'
const activeTab = ref<'computed' | 'published'>('computed')

// Selected ranking type (profil ≤ 3 can change it)
const selectedType = ref<CompetitionType | ''>('')

// Compute options
const includeUnlocked = ref(
  typeof localStorage !== 'undefined'
    ? localStorage.getItem('kpi_admin_ranking_include_unlocked') !== 'false'
    : true
)
watch(includeUnlocked, (v) => {
  localStorage.setItem('kpi_admin_ranking_include_unlocked', String(v))
})

// Action loading states
const computing = ref(false)
const publishing = ref(false)
const unpublishing = ref(false)

// Selection state (for transfer)
const selectedIds = ref<number[]>([])
const selectAll = ref(false)

// Inline editing state
const editingCell = ref<{ id: number; field: string; journeeId?: number } | null>(null)
const editingValue = ref('')
const editingOriginalValue = ref('')

// Transfer state
const transferSeason = ref('')
const transferCompetitions = ref<TransferCompetition[]>([])
const transferCompetition = ref('')
const transferring = ref(false)
const transferCompetitionsLoading = ref(false)

// Confirm modal state
const confirmModal = ref<{ open: boolean; title: string; message: string; action: () => void }>({
  open: false,
  title: '',
  message: '',
  action: () => {}
})

// Transfer modal state
const transferModalOpen = ref(false)

// PDF dropdown state
const pdfDropdownOpen = ref(false)
const pdfDropdownStyle = ref<Record<string, string>>({})
const pdfDropdownMode = ref<'admin' | 'public'>('admin')

// Permission checks
const canCompute = computed(() => authStore.profile <= 6)
const canEditInline = computed(() => authStore.profile <= 4 && competitionInfo.value?.statut === 'ON')
const canPublish = computed(() => authStore.profile <= 4 && competitionInfo.value?.statut === 'ON')
const canUnpublish = computed(() => authStore.profile <= 3 && competitionInfo.value?.statut === 'ON')
const canConsolidate = computed(() => authStore.profile <= 4 && competitionInfo.value?.statut === 'ON')
const canTransfer = computed(() => authStore.profile <= 4)
const canChangeType = computed(() => authStore.profile <= 3)
const canChangeStatus = computed(() => authStore.profile <= 3)
const canAccessInitial = computed(() => authStore.profile <= 3)
const isStatusOn = computed(() => competitionInfo.value?.statut === 'ON')

// Current effective type
const effectiveType = computed<CompetitionType>(() => {
  if (selectedType.value) return selectedType.value
  return competitionInfo.value?.codeTypeclt || 'CHPT'
})

// Is international competition?
const isInternational = computed(() => competitionInfo.value?.codeNiveau === 'INT')

// Is ranking different from published?
const isRankingDifferent = computed(() => {
  if (!competitionInfo.value?.dateCalcul || !competitionInfo.value?.datePublication) return false
  return competitionInfo.value.dateCalcul !== competitionInfo.value.datePublicationCalcul
    || competitionInfo.value.modeCalcul !== competitionInfo.value.modePublicationCalcul
})

// Phases sorted by niveau DESC (highest level first)
const sortedPhases = computed(() => [...phases.value].sort((a, b) => b.niveau - a.niveau))

// Legacy base URL
const legacyBase = computed(() => config.public.legacyBaseUrl)

// Status badge colors (same as teams page)
const getStatusColor = (status: string) => {
  switch (status) {
    case 'ATT': return 'bg-[#888888] text-[#CCEEDD] italic'
    case 'ON': return 'bg-[#008800] text-[#CCEEDD] italic'
    case 'END': return 'bg-[#334F64] text-[#CCEEDD]'
    default: return 'bg-[#888888] text-[#CCEEDD]'
  }
}

const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-blue-100 text-blue-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

// Flag URL for international competitions
const getFlagUrl = (team: RankingTeam) => {
  if (!isInternational.value || !team.codeClub) return null
  return `${legacyBase.value}/img/Nations/${team.codeClub.substring(0, 3)}.png`
}

// Qualified/eliminated indicator
const getQualifiedStatus = (index: number, totalTeams: number) => {
  if (!competitionInfo.value) return null
  if (competitionInfo.value.qualifies > 0 && index < competitionInfo.value.qualifies) return 'qualified'
  if (competitionInfo.value.elimines > 0 && index >= totalTeams - competitionInfo.value.elimines) return 'eliminated'
  return null
}

// Format date
const formatDate = (dateStr: string | null) => {
  if (!dateStr) return null
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// Display points (÷ 100)
const displayPts = (pts: number) => {
  if (pts % 100 === 0) return String(pts / 100)
  return (pts / 100).toFixed(2).replace(/\.?0+$/, '')
}

// Load rankings
const loadRankings = async () => {
  if (!workContext.initialized || !workContext.season || !workContext.pageCompetitionCode) return

  loading.value = true
  try {
    const params: Record<string, string> = {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    }
    if (selectedType.value) {
      params.type = selectedType.value
    }
    const response = await api.get<RankingResponse>('/admin/rankings', params)
    competitionInfo.value = response.competition
    rankingTypes.value = response.types
    ranking.value = response.ranking
    phases.value = response.phases

    // Set selected type from response if not already set
    if (!selectedType.value) {
      const sel = response.types.find(t => t.selected)
      if (sel) selectedType.value = sel.code
    }

    // Reset selection
    selectedIds.value = []
    selectAll.value = false
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('rankings.error_load')
    toast.add({ title: t('common.error'), description: message, color: 'error', duration: 3000 })
  } finally {
    loading.value = false
  }
}

// Watch page competition changes
watch(
  () => workContext.pageCompetitionCode,
  (code) => {
    if (code) {
      selectedType.value = ''
      loadRankings()
    } else {
      competitionInfo.value = null
      ranking.value = []
      phases.value = []
    }
  }
)

// Load on mount
onMounted(async () => {
  await workContext.initContext()
  if (workContext.pageCompetitionCode) {
    await loadRankings()
  }
})

// Competition change handler
function onCompetitionChange() {
  // Watch above handles the reload
}

// Type change handler
function onTypeChange() {
  loadRankings()
}

// Selection handlers
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedIds.value = ranking.value.map(t => t.id)
  } else {
    selectedIds.value = []
  }
}

const isSelected = (id: number) => selectedIds.value.includes(id)

const toggleSelect = (id: number) => {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
  selectAll.value = selectedIds.value.length === ranking.value.length
}

// Inline editing
const startEdit = (teamId: number, field: string, currentValue: number | string, journeeId?: number) => {
  if (!canEditInline.value) return
  editingCell.value = { id: teamId, field, journeeId }
  const val = String(currentValue)
  editingValue.value = val
  editingOriginalValue.value = val
  nextTick(() => {
    const suffix = journeeId ? `-${journeeId}` : ''
    const el = document.getElementById(`inline-edit-${teamId}-${field}${suffix}`)
    if (el) {
      el.focus()
      if (el instanceof HTMLInputElement) el.select()
    }
  })
}

const saveInlineEdit = async () => {
  if (!editingCell.value) return

  const { id, field, journeeId } = editingCell.value
  editingCell.value = null

  if (editingValue.value === editingOriginalValue.value) return

  let value = parseInt(editingValue.value) || 0
  // Pts field: multiply by 100 for storage
  if (field === 'Pts') {
    value = Math.round(parseFloat(editingValue.value || '0') * 100)
  }

  try {
    await api.patch(`/admin/rankings/${id}/inline`, {
      field,
      value,
      journeeId: journeeId || null
    })
    // Reload to get updated data
    await loadRankings()
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

// Compute ranking
const computeRanking = async () => {
  if (!competitionInfo.value || !workContext.season) return

  computing.value = true
  try {
    const response = await api.post<RankingResponse>('/admin/rankings/compute', {
      season: workContext.season,
      competition: competitionInfo.value.code,
      includeUnlocked: includeUnlocked.value
    })
    competitionInfo.value = response.competition
    ranking.value = response.ranking
    phases.value = response.phases
    toast.add({ title: t('common.success'), description: t('rankings.success_computed'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_compute'), color: 'error', duration: 3000 })
  } finally {
    computing.value = false
  }
}

// Publish ranking
const publishRanking = async () => {
  if (!competitionInfo.value || !workContext.season) return

  publishing.value = true
  try {
    await api.post('/admin/rankings/publish', {
      season: workContext.season,
      competition: competitionInfo.value.code
    })
    await loadRankings()
    toast.add({ title: t('common.success'), description: t('rankings.success_published'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_publish'), color: 'error', duration: 3000 })
  } finally {
    publishing.value = false
  }
}

// Unpublish ranking
const unpublishRanking = async () => {
  if (!competitionInfo.value || !workContext.season) return

  unpublishing.value = true
  try {
    await api.delete('/admin/rankings/publish', {
      season: workContext.season,
      competition: competitionInfo.value.code
    })
    await loadRankings()
    toast.add({ title: t('common.success'), description: t('rankings.success_unpublished'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_publish'), color: 'error', duration: 3000 })
  } finally {
    unpublishing.value = false
  }
}

// Consolidation toggle
const toggleConsolidation = async (phase: RankingPhase) => {
  if (!canConsolidate.value) return

  const newValue = !phase.consolidation
  try {
    await api.patch(`/admin/rankings/consolidation/${phase.idJournee}`, {
      consolidation: newValue
    })
    phase.consolidation = newValue
    const msg = newValue ? t('rankings.success_consolidated') : t('rankings.success_unconsolidated')
    toast.add({ title: t('common.success'), description: msg, color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_save'), color: 'error', duration: 3000 })
  }
}

// Remove team from phase
const removePhaseTeam = async (phase: RankingPhase, team: RankingPhaseTeam) => {
  if (!canEditInline.value) return

  try {
    await api.delete(`/admin/rankings/phase-team/${phase.idJournee}/${team.id}`)
    phase.teams = phase.teams.filter(t => t.id !== team.id)
    toast.add({ title: t('common.success'), description: t('rankings.success_team_removed'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || t('rankings.error_save'), color: 'error', duration: 3000 })
  }
}

// Status cycle (ATT → ON → END → ATT)
const cycleStatus = async () => {
  if (!canChangeStatus.value || !competitionInfo.value || !workContext.season) return

  const statusMap: Record<CompetitionStatus, CompetitionStatus> = { 'ATT': 'ON', 'ON': 'END', 'END': 'ATT' }
  const nextStatus = statusMap[competitionInfo.value.statut]

  try {
    await api.patch(`/admin/competitions/${competitionInfo.value.code}/status?season=${workContext.season}`, { statut: nextStatus })
    competitionInfo.value.statut = nextStatus
    toast.add({ title: t('common.success'), description: t('competitions.success_status'), color: 'success', duration: 2000 })
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || '', color: 'error', duration: 3000 })
  }
}

// Transfer: load competitions for selected season
const loadTransferCompetitions = async () => {
  if (!transferSeason.value) {
    transferCompetitions.value = []
    return
  }
  transferCompetitionsLoading.value = true
  try {
    transferCompetitions.value = await api.get<TransferCompetition[]>('/admin/rankings/transfer-competitions', {
      season: transferSeason.value
    })
  } catch {
    transferCompetitions.value = []
  } finally {
    transferCompetitionsLoading.value = false
  }
}

watch(transferSeason, () => {
  transferCompetition.value = ''
  loadTransferCompetitions()
})

// Transfer teams
const doTransfer = async () => {
  if (selectedIds.value.length === 0) {
    toast.add({ title: t('common.error'), description: t('rankings.transfer.nothing_selected'), color: 'error', duration: 3000 })
    return
  }
  if (!transferSeason.value) {
    toast.add({ title: t('common.error'), description: t('rankings.transfer.select_season'), color: 'error', duration: 3000 })
    return
  }
  if (!transferCompetition.value) {
    toast.add({ title: t('common.error'), description: t('rankings.transfer.select_competition'), color: 'error', duration: 3000 })
    return
  }

  transferring.value = true
  try {
    const result = await api.post<TransferResult>('/admin/rankings/transfer', {
      teamIds: selectedIds.value,
      targetSeason: transferSeason.value,
      targetCompetition: transferCompetition.value
    })
    let msg = t('rankings.transfer.success', { count: result.transferred })
    if (result.skipped > 0) {
      msg += ' - ' + t('rankings.transfer.skipped', { count: result.skipped })
    }
    toast.add({ title: t('common.success'), description: msg, color: 'success', duration: 4000 })
    selectedIds.value = []
    selectAll.value = false
  } catch (error: unknown) {
    toast.add({ title: t('common.error'), description: (error as { message?: string })?.message || '', color: 'error', duration: 3000 })
  } finally {
    transferring.value = false
  }
}

// Confirm modal helpers
const showConfirm = (title: string, message: string, action: () => void) => {
  confirmModal.value = { open: true, title, message, action }
}

const executeConfirm = () => {
  confirmModal.value.action()
  confirmModal.value.open = false
}

// PDF dropdown
const togglePdfDropdown = (e: MouseEvent, mode: 'admin' | 'public') => {
  if (pdfDropdownOpen.value && pdfDropdownMode.value === mode) {
    pdfDropdownOpen.value = false
    return
  }
  const btn = (e.currentTarget as HTMLElement)
  const rect = btn.getBoundingClientRect()
  pdfDropdownStyle.value = {
    position: 'fixed',
    top: `${rect.bottom + 4}px`,
    right: `${window.innerWidth - rect.right}px`
  }
  pdfDropdownMode.value = mode
  pdfDropdownOpen.value = true
}

const handleClickOutsideDropdown = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (pdfDropdownOpen.value) {
    if (!target.closest('.pdf-dropdown-trigger') && !target.closest('.pdf-dropdown-menu')) {
      pdfDropdownOpen.value = false
    }
  }
}

onMounted(() => document.addEventListener('click', handleClickOutsideDropdown))
onUnmounted(() => document.removeEventListener('click', handleClickOutsideDropdown))

// PDF URLs
const pdfUrls = computed(() => {
  if (!competitionInfo.value) return null
  const code = competitionInfo.value.code
  const base = legacyBase.value
  const type = effectiveType.value

  const urls: Record<string, { admin?: string; public?: string }> = {}

  if (type === 'CHPT') {
    urls.general = { admin: `${base}/admin/FeuilleCltChpt.php`, public: `${base}/admin/PdfCltChpt.php` }
    urls.detail = { admin: `${base}/admin/FeuilleCltChptDetail.php`, public: `${base}/admin/PdfCltChptDetail.php` }
    urls.matches = { admin: `${base}/admin/FeuilleListeMatchs.php?Compet=${code}`, public: `${base}/admin/PdfListeMatchs.php?Compet=${code}` }
  } else if (type === 'CP') {
    urls.general = { admin: `${base}/admin/FeuilleCltNiveau.php`, public: `${base}/admin/PdfCltNiveau.php` }
    urls.progress = { admin: `${base}/admin/FeuilleCltNiveauPhase.php`, public: `${base}/admin/PdfCltNiveauPhase.php` }
    urls.detail = { admin: `${base}/admin/FeuilleCltNiveauDetail.php`, public: `${base}/admin/PdfCltNiveauDetail.php` }
    urls.matches = { admin: `${base}/admin/FeuilleListeMatchs.php?Compet=${code}`, public: `${base}/admin/PdfListeMatchs.php?Compet=${code}` }
  } else if (type === 'MULTI') {
    urls.general = { admin: `${base}/admin/FeuilleCltMulti.php`, public: `${base}/admin/PdfCltMulti.php` }
  }

  return urls
})

// Get column label for MULTI structure type
const structureLabel = computed(() => {
  if (!competitionInfo.value?.rankingStructureType) return t('rankings.table.team')
  const key = competitionInfo.value.rankingStructureType
  const labels: Record<string, string> = {
    team: t('rankings.table.team'),
    club: t('rankings.table.club'),
    cd: t('rankings.table.cd'),
    cr: t('rankings.table.cr'),
    nation: t('rankings.table.nation')
  }
  return labels[key] || t('rankings.table.team')
})

// Map field name to team property
const fieldToTeamProp = (field: string, team: RankingTeam, published: boolean): number => {
  const suffix = published ? 'Publi' : ''
  const map: Record<string, string> = {
    'Clt': published ? 'cltPubli' : 'clt',
    'Pts': published ? 'ptsPubli' : 'pts',
    'J': published ? 'jPubli' : 'j',
    'G': published ? 'gPubli' : 'g',
    'N': published ? 'nPubli' : 'n',
    'P': published ? 'pPubli' : 'p',
    'F': published ? 'fPubli' : 'f',
    'Plus': published ? 'plusPubli' : 'plus',
    'Moins': published ? 'moinsPubli' : 'moins',
    'Diff': published ? 'diffPubli' : 'diff',
    'CltNiveau': published ? 'cltNiveauPubli' : 'cltNiveau',
    'PtsNiveau': published ? 'ptsNiveauPubli' : 'ptsNiveau'
  }
  const prop = map[field] || field
  return (team as Record<string, unknown>)[prop] as number
}

// Display value for a field (handles Pts ÷ 100)
const displayFieldValue = (field: string, value: number): string => {
  if (field === 'Pts') return displayPts(value)
  return String(value)
}

// Edit value for a field (handles Pts ÷ 100 for input)
const editValueForField = (field: string, value: number): string => {
  if (field === 'Pts') return displayPts(value)
  return String(value)
}
</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader
      :title="t('rankings.title')"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
      @competition-change="onCompetitionChange"
    >
      <template #filters>
        <!-- Type selector (profil ≤ 3) -->
        <div v-if="canChangeType && rankingTypes.length > 0">
          <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('rankings.type.label') }}</label>
          <select
            v-model="selectedType"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            @change="onTypeChange"
          >
            <option v-for="rt in rankingTypes" :key="rt.code" :value="rt.code">
              {{ t(`rankings.type.${rt.code}`) }}
            </option>
          </select>
        </div>
      </template>
      <template #badges>
        <div v-if="competitionInfo" class="flex items-center gap-2 flex-wrap">
          <!-- Level badge -->
          <span
            class="px-2 py-1 text-xs font-medium rounded uppercase"
            :class="getLevelColor(competitionInfo.codeNiveau)"
          >
            {{ competitionInfo.codeNiveau }}
          </span>
          <!-- Type badge -->
          <span class="px-2 py-1 text-xs font-medium rounded uppercase bg-gray-100 text-gray-800">
            {{ competitionInfo.codeTypeclt }}
          </span>
          <!-- Status badge (clickable for profil ≤ 3) -->
          <span
            class="px-2 py-1 text-xs font-medium rounded uppercase"
            :class="[getStatusColor(competitionInfo.statut), canChangeStatus ? 'cursor-pointer' : '']"
            @click="canChangeStatus && cycleStatus()"
          >
            {{ t(`competitions.status.${competitionInfo.statut}`) }}
          </span>
          <!-- Goal-average info -->
          <span class="text-xs text-gray-500">
            {{ t('rankings.goalaverage.label') }} : {{ t(`rankings.goalaverage.${competitionInfo.goalaverage || 'gen'}`) }}
          </span>
        </div>
      </template>
      <template #notices>
        <div
          v-if="competitionInfo && competitionInfo.statut !== 'ON'"
          class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800"
        >
          <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 shrink-0" />
          {{ t('rankings.status_restriction') }}
        </div>
      </template>
    </AdminPageHeader>

    <!-- No competition selected -->
    <div v-if="!workContext.pageCompetitionCode" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ t('teams_page.no_competition') }}
    </div>

    <template v-if="workContext.pageCompetitionCode && competitionInfo">
      <!-- Tabs: blue for computed, green for published -->
      <div class="mb-4 bg-white rounded-lg shadow">
        <div class="flex border-b border-gray-200">
          <button
            class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors"
            :class="activeTab === 'computed' ? 'text-white bg-blue-700 border-b-2 border-blue-700' : 'text-blue-600 bg-blue-100 hover:bg-blue-200'"
            @click="activeTab = 'computed'"
          >
            {{ t('rankings.tabs.computed') }}
          </button>
          <button
            class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors"
            :class="activeTab === 'published' ? 'text-white bg-green-700 border-b-2 border-green-700' : 'text-green-600 bg-green-100 hover:bg-green-200'"
            @click="activeTab = 'published'"
          >
            {{ t('rankings.tabs.published') }}
          </button>
        </div>

        <!-- ═══ COMPUTED TAB ═══ -->
        <div v-if="activeTab === 'computed'" class="p-4">
          <!-- Compute info -->
          <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div v-if="competitionInfo.dateCalcul" class="text-sm text-gray-700">
              <span class="font-medium">{{ t('rankings.compute.date') }}</span> :
              {{ formatDate(competitionInfo.dateCalcul) }}
              ({{ t('rankings.compute.by') }} {{ competitionInfo.userNameCalcul }})
              <span v-if="competitionInfo.modeCalcul" class="ml-2 text-xs text-gray-500">
                — {{ competitionInfo.modeCalcul === 'tous' ? t('rankings.compute.mode_all') : t('rankings.compute.mode_locked') }}
              </span>
            </div>
            <div v-else class="text-sm text-gray-500 italic">
              {{ t('rankings.compute.not_computed') }}
            </div>
          </div>

          <!-- ── Toolbar (above ranking) ── -->
          <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="flex-1" />

            <!-- PDF dropdown -->
            <div v-if="pdfUrls" class="relative">
              <button
                class="pdf-dropdown-trigger px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm flex items-center gap-1"
                @click="togglePdfDropdown($event, 'admin')"
              >
                <UIcon name="heroicons:document-text" class="w-4 h-4" />
                {{ t('rankings.pdf.title') }}
                <UIcon name="heroicons:chevron-down" class="w-3 h-3" />
              </button>
            </div>

            <!-- RIGHT: Action buttons -->

            <NuxtLink
              v-if="canAccessInitial && effectiveType === 'CHPT'"
              :to="`/rankings/initial?competition=${competitionInfo.code}&season=${workContext.season}`"
              class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
            >
              {{ t('rankings.initial.button') }}
            </NuxtLink>
            <template v-if="canCompute && isStatusOn">
              <label class="flex items-center gap-2 text-sm">
                <input
                  v-model="includeUnlocked"
                  type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                >
                {{ t('rankings.compute.include_unlocked') }}
              </label>
              <button
                class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="computing"
                @click="showConfirm(t('rankings.compute.title'), t('rankings.confirm_compute'), computeRanking)"
              >
                <template v-if="computing">{{ t('rankings.compute.computing') }}</template>
                <template v-else>{{ t('rankings.compute.button') }}</template>
              </button>
            </template>

            <button
              v-if="canPublish"
              class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="publishing"
              @click="showConfirm(t('rankings.publish.title'), t('rankings.confirm_publish'), publishRanking)"
            >
              <template v-if="publishing">{{ t('rankings.publish.publishing') }}</template>
              <template v-else>{{ t('rankings.publish.button') }}</template>
            </button>
          </div>

          <!-- Loading -->
          <div v-if="loading && ranking.length === 0" class="p-8 text-center text-gray-500">
            <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
            {{ t('common.loading') }}
          </div>

          <!-- Empty -->
          <div v-else-if="ranking.length === 0" class="p-8 text-center text-gray-500">
            {{ t('rankings.no_teams') }}
          </div>

          <template v-else>
            <div class="lg:flex lg:gap-4 lg:items-start">
            <!-- ── General Ranking Table ── -->
            <div class="mb-4" :class="effectiveType === 'CP' && sortedPhases.length > 0 ? 'lg:flex-1 lg:min-w-0' : 'w-full'">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('rankings.pdf.general') }}</h3>

              <!-- Desktop table -->
              <div class="hidden lg:block overflow-x-auto rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 bg-blue-100">
                  <thead>
                    <tr class="bg-blue-200">
                      <th v-if="isInternational" class="px-2 py-2" />
                      <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.rank') }}</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                        {{ effectiveType === 'MULTI' ? structureLabel : t('rankings.table.team') }}
                      </th>
                      <!-- CHPT columns -->
                      <template v-if="effectiveType === 'CHPT'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.g') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.n') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.p') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.f') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.plus') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.minus') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.diff') }}</th>
                      </template>
                      <!-- CP columns: only J -->
                      <template v-else-if="effectiveType === 'CP'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                      </template>
                      <!-- MULTI columns -->
                      <template v-else-if="effectiveType === 'MULTI'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr
                      v-for="(team, idx) in ranking"
                      :key="team.id"
                      class="hover:bg-gray-50"
                    >
                      <!-- Flag -->
                      <td v-if="isInternational" class="px-2 py-1.5">
                        <img
                          v-if="getFlagUrl(team)"
                          :src="getFlagUrl(team)!"
                          :alt="team.codeClub"
                          class="w-6 h-6 object-contain"
                          @error="($event.target as HTMLImageElement).style.display = 'none'"
                        >
                      </td>
                      <!-- Qualified/Eliminated indicator + Rank -->
                      <td class="px-2 py-1.5 text-center text-sm">
                        <div class="flex items-center justify-center gap-1">
                          <span
                            v-if="getQualifiedStatus(idx, ranking.length) === 'qualified'"
                            class="text-green-600 text-xs"
                            :title="t('rankings.qualified')"
                          >▲</span>
                          <span
                            v-else-if="getQualifiedStatus(idx, ranking.length) === 'eliminated'"
                            class="text-red-600 text-xs"
                            :title="t('rankings.eliminated')"
                          >▼</span>
                          <!-- Rank value -->
                          <template v-if="editingCell?.id === team.id && editingCell.field === (effectiveType === 'CP' ? 'CltNiveau' : 'Clt') && !editingCell.journeeId">
                            <input
                              :id="`inline-edit-${team.id}-${effectiveType === 'CP' ? 'CltNiveau' : 'Clt'}`"
                              v-model="editingValue"
                              type="tel"
                              maxlength="3"
                              class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                              @keydown="handleInlineKeydown"
                              @blur="saveInlineEdit"
                            >
                          </template>
                          <span
                            v-else
                            :class="canEditInline ? 'editable-cell' : ''"
                            @click="canEditInline && startEdit(team.id, effectiveType === 'CP' ? 'CltNiveau' : 'Clt', effectiveType === 'CP' ? team.cltNiveau : team.clt)"
                          >
                            {{ effectiveType === 'CP' ? team.cltNiveau : team.clt }}
                          </span>
                        </div>
                      </td>
                      <!-- Team name -->
                      <td class="px-2 py-1.5 text-sm font-medium text-gray-900">
                        {{ team.libelle }}
                      </td>
                      <!-- CHPT specific columns -->
                      <template v-if="effectiveType === 'CHPT'">
                        <td v-for="field in ['Pts', 'J', 'G', 'N', 'P', 'F', 'Plus', 'Moins', 'Diff']" :key="field" class="px-2 py-1.5 text-center text-sm">
                          <template v-if="editingCell?.id === team.id && editingCell.field === field && !editingCell.journeeId">
                            <input
                              :id="`inline-edit-${team.id}-${field}`"
                              v-model="editingValue"
                              type="tel"
                              maxlength="5"
                              class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                              @keydown="handleInlineKeydown"
                              @blur="saveInlineEdit"
                            >
                          </template>
                          <span
                            v-else
                            :class="canEditInline ? 'editable-cell' : ''"
                            @click="canEditInline && startEdit(team.id, field, editValueForField(field, fieldToTeamProp(field, team, false)))"
                          >
                            {{ displayFieldValue(field, fieldToTeamProp(field, team, false)) }}
                          </span>
                        </td>
                      </template>
                      <!-- CP: only J -->
                      <template v-else-if="effectiveType === 'CP'">
                        <td class="px-2 py-1.5 text-center text-sm">
                          <template v-if="editingCell?.id === team.id && editingCell.field === 'J' && !editingCell.journeeId">
                            <input
                              :id="`inline-edit-${team.id}-J`"
                              v-model="editingValue"
                              type="tel"
                              maxlength="3"
                              class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                              @keydown="handleInlineKeydown"
                              @blur="saveInlineEdit"
                            >
                          </template>
                          <span
                            v-else
                            :class="canEditInline ? 'editable-cell' : ''"
                            @click="canEditInline && startEdit(team.id, 'J', team.j)"
                          >
                            {{ team.j }}
                          </span>
                        </td>
                      </template>
                      <!-- MULTI: Pts + J -->
                      <template v-else-if="effectiveType === 'MULTI'">
                        <td class="px-2 py-1.5 text-center text-sm">
                          <template v-if="editingCell?.id === team.id && editingCell.field === 'Pts' && !editingCell.journeeId">
                            <input
                              :id="`inline-edit-${team.id}-Pts`"
                              v-model="editingValue"
                              type="tel"
                              maxlength="5"
                              class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                              @keydown="handleInlineKeydown"
                              @blur="saveInlineEdit"
                            >
                          </template>
                          <span
                            v-else
                            :class="canEditInline ? 'editable-cell' : ''"
                            @click="canEditInline && startEdit(team.id, 'Pts', editValueForField('Pts', team.pts))"
                          >
                            {{ displayPts(team.pts) }}
                          </span>
                        </td>
                        <td class="px-2 py-1.5 text-center text-sm">
                          <template v-if="editingCell?.id === team.id && editingCell.field === 'J' && !editingCell.journeeId">
                            <input
                              :id="`inline-edit-${team.id}-J`"
                              v-model="editingValue"
                              type="tel"
                              maxlength="3"
                              class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                              @keydown="handleInlineKeydown"
                              @blur="saveInlineEdit"
                            >
                          </template>
                          <span
                            v-else
                            :class="canEditInline ? 'editable-cell' : ''"
                            @click="canEditInline && startEdit(team.id, 'J', team.j)"
                          >
                            {{ team.j }}
                          </span>
                        </td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile cards -->
              <div class="lg:hidden divide-y divide-gray-200">
                <div
                  v-for="(team, idx) in ranking"
                  :key="team.id"
                  class="p-3"
                >
                  <div class="flex items-start gap-2">
                    <img
                      v-if="isInternational && getFlagUrl(team)"
                      :src="getFlagUrl(team)!"
                      :alt="team.codeClub"
                      class="w-6 h-6 object-contain mt-0.5"
                      @error="($event.target as HTMLImageElement).style.display = 'none'"
                    >
                    <span
                      v-if="getQualifiedStatus(idx, ranking.length) === 'qualified'"
                      class="text-green-600 text-xs mt-0.5"
                    >▲</span>
                    <span
                      v-else-if="getQualifiedStatus(idx, ranking.length) === 'eliminated'"
                      class="text-red-600 text-xs mt-0.5"
                    >▼</span>
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-gray-900">{{ team.libelle }}</div>
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                        <span>{{ t('rankings.table.rank') }}: {{ effectiveType === 'CP' ? team.cltNiveau : team.clt }}</span>
                        <template v-if="effectiveType === 'CHPT'">
                          <span>{{ t('rankings.table.pts') }}: {{ displayPts(team.pts) }}</span>
                          <span>{{ t('rankings.table.j') }}: {{ team.j }}</span>
                          <span>{{ t('rankings.table.g') }}: {{ team.g }}</span>
                          <span>{{ t('rankings.table.n') }}: {{ team.n }}</span>
                          <span>{{ t('rankings.table.p') }}: {{ team.p }}</span>
                          <span>{{ t('rankings.table.diff') }}: {{ team.diff }}</span>
                        </template>
                        <template v-else-if="effectiveType === 'CP'">
                          <span>{{ t('rankings.table.j') }}: {{ team.j }}</span>
                        </template>
                        <template v-else-if="effectiveType === 'MULTI'">
                          <span>{{ t('rankings.table.pts') }}: {{ displayPts(team.pts) }}</span>
                          <span>{{ t('rankings.table.j') }}: {{ team.j }}</span>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ── Phase Progression (CP only, sorted by niveau DESC) ── -->
            <div v-if="effectiveType === 'CP' && sortedPhases.length > 0" class="mb-4 lg:flex-1 lg:min-w-0">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('rankings.phases.title') }}</h3>

              <div v-for="phase in sortedPhases" :key="phase.idJournee" class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                <!-- Phase header: name on left, consolidation/elimination on right -->
                <div class="px-4 py-2 bg-blue-300 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                  <span class="font-medium text-sm text-gray-800">
                    {{ phase.phase }}
                    <span v-if="phase.lieu" class="text-gray-500">({{ phase.lieu }})</span>
                  </span>
                  <!-- Consolidation checkbox (type C only) - on the right -->
                  <label v-if="phase.type === 'C'" class="flex items-center gap-2 text-sm">
                    <input
                      :checked="phase.consolidation"
                      type="checkbox"
                      class="w-4 h-4 rounded border-gray-300 text-blue-600"
                      :disabled="!canConsolidate"
                      @change="toggleConsolidation(phase)"
                    >
                    <span :class="phase.consolidation ? 'font-medium text-blue-700' : 'text-gray-600'">
                      {{ phase.consolidation ? t('rankings.phases.consolidated') : t('rankings.phases.consolidate') }}
                    </span>
                  </label>
                  <span v-if="phase.type === 'E'" class="text-sm text-gray-600 italic">
                    {{ t('rankings.phases.elimination') }}
                  </span>
                </div>

                <!-- Phase type C: classification table -->
                <template v-if="phase.type === 'C'">
                  <!-- Desktop table -->
                  <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 bg-blue-100">
                      <thead class="bg-gray-50">
                        <tr class="bg-blue-200">
                          <th v-if="canEditInline && !phase.consolidation" class="px-2 py-2" />
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.rank') }}</th>
                          <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.team') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.g') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.n') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.p') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.f') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.plus') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.minus') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.diff') }}</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                        <tr v-for="pTeam in phase.teams" :key="pTeam.id" class="hover:bg-gray-50">
                          <!-- Delete button (if J=0 and editable) -->
                          <td v-if="canEditInline && !phase.consolidation" class="px-2 py-1.5">
                            <button
                              v-if="pTeam.j === 0"
                              class="p-0.5 text-red-400 hover:text-red-600 rounded"
                              :title="t('common.delete')"
                              @click="showConfirm(t('common.delete'), t('rankings.confirm_delete_phase_team'), () => removePhaseTeam(phase, pTeam))"
                            >
                              <UIcon name="heroicons:trash-solid" class="w-4 h-4" />
                            </button>
                          </td>
                          <!-- Clt -->
                          <td class="px-2 py-1.5 text-center text-sm">
                            <template v-if="editingCell?.id === pTeam.id && editingCell.field === 'Clt' && editingCell.journeeId === phase.idJournee">
                              <input
                                :id="`inline-edit-${pTeam.id}-Clt-${phase.idJournee}`"
                                v-model="editingValue"
                                type="tel"
                                maxlength="3"
                                class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                                @keydown="handleInlineKeydown"
                                @blur="saveInlineEdit"
                              >
                            </template>
                            <span
                              v-else
                              :class="canEditInline && !phase.consolidation ? 'editable-cell' : ''"
                              @click="canEditInline && !phase.consolidation && startEdit(pTeam.id, 'Clt', pTeam.clt, phase.idJournee)"
                            >
                              {{ pTeam.clt }}
                            </span>
                          </td>
                          <!-- Team -->
                          <td class="px-2 py-1.5 text-sm font-medium text-gray-900">{{ pTeam.libelle }}</td>
                          <!-- Pts (editable if not consolidated) -->
                          <td class="px-2 py-1.5 text-center text-sm">
                            <template v-if="editingCell?.id === pTeam.id && editingCell.field === 'Pts' && editingCell.journeeId === phase.idJournee">
                              <input
                                :id="`inline-edit-${pTeam.id}-Pts-${phase.idJournee}`"
                                v-model="editingValue"
                                type="tel"
                                maxlength="5"
                                class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                                @keydown="handleInlineKeydown"
                                @blur="saveInlineEdit"
                              >
                            </template>
                            <span
                              v-else
                              :class="canEditInline && !phase.consolidation ? 'editable-cell' : ''"
                              @click="canEditInline && !phase.consolidation && startEdit(pTeam.id, 'Pts', displayPts(pTeam.pts), phase.idJournee)"
                            >
                              {{ displayPts(pTeam.pts) }}
                            </span>
                          </td>
                          <!-- J, G, N, P, F (read-only in phases) -->
                          <td class="px-2 py-1.5 text-center text-sm text-gray-600">{{ pTeam.j }}</td>
                          <td class="px-2 py-1.5 text-center text-sm text-gray-600">{{ pTeam.g }}</td>
                          <td class="px-2 py-1.5 text-center text-sm text-gray-600">{{ pTeam.n }}</td>
                          <td class="px-2 py-1.5 text-center text-sm text-gray-600">{{ pTeam.p }}</td>
                          <td class="px-2 py-1.5 text-center text-sm text-gray-600">{{ pTeam.f }}</td>
                          <!-- Plus (editable if not consolidated) -->
                          <td class="px-2 py-1.5 text-center text-sm">
                            <template v-if="editingCell?.id === pTeam.id && editingCell.field === 'Plus' && editingCell.journeeId === phase.idJournee">
                              <input
                                :id="`inline-edit-${pTeam.id}-Plus-${phase.idJournee}`"
                                v-model="editingValue"
                                type="tel"
                                maxlength="3"
                                class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                                @keydown="handleInlineKeydown"
                                @blur="saveInlineEdit"
                              >
                            </template>
                            <span
                              v-else
                              :class="canEditInline && !phase.consolidation ? 'editable-cell' : ''"
                              @click="canEditInline && !phase.consolidation && startEdit(pTeam.id, 'Plus', pTeam.plus, phase.idJournee)"
                            >
                              {{ pTeam.plus }}
                            </span>
                          </td>
                          <!-- Moins (editable if not consolidated) -->
                          <td class="px-2 py-1.5 text-center text-sm">
                            <template v-if="editingCell?.id === pTeam.id && editingCell.field === 'Moins' && editingCell.journeeId === phase.idJournee">
                              <input
                                :id="`inline-edit-${pTeam.id}-Moins-${phase.idJournee}`"
                                v-model="editingValue"
                                type="tel"
                                maxlength="3"
                                class="w-10 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                                @keydown="handleInlineKeydown"
                                @blur="saveInlineEdit"
                              >
                            </template>
                            <span
                              v-else
                              :class="canEditInline && !phase.consolidation ? 'editable-cell' : ''"
                              @click="canEditInline && !phase.consolidation && startEdit(pTeam.id, 'Moins', pTeam.moins, phase.idJournee)"
                            >
                              {{ pTeam.moins }}
                            </span>
                          </td>
                          <!-- Diff (editable if not consolidated) -->
                          <td class="px-2 py-1.5 text-center text-sm">
                            <template v-if="editingCell?.id === pTeam.id && editingCell.field === 'Diff' && editingCell.journeeId === phase.idJournee">
                              <input
                                :id="`inline-edit-${pTeam.id}-Diff-${phase.idJournee}`"
                                v-model="editingValue"
                                type="tel"
                                maxlength="4"
                                class="w-12 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
                                @keydown="handleInlineKeydown"
                                @blur="saveInlineEdit"
                              >
                            </template>
                            <span
                              v-else
                              :class="canEditInline && !phase.consolidation ? 'editable-cell' : ''"
                              @click="canEditInline && !phase.consolidation && startEdit(pTeam.id, 'Diff', pTeam.diff, phase.idJournee)"
                            >
                              {{ pTeam.diff }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Mobile cards for phase type C -->
                  <div class="lg:hidden divide-y divide-gray-200">
                    <div v-for="pTeam in phase.teams" :key="pTeam.id" class="p-3">
                      <div class="font-medium text-gray-900 text-sm">{{ pTeam.libelle }}</div>
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                        <span>{{ t('rankings.table.rank') }}: {{ pTeam.clt }}</span>
                        <span>{{ t('rankings.table.pts') }}: {{ displayPts(pTeam.pts) }}</span>
                        <span>{{ t('rankings.table.j') }}: {{ pTeam.j }}</span>
                        <span>{{ t('rankings.table.g') }}: {{ pTeam.g }}</span>
                        <span>{{ t('rankings.table.n') }}: {{ pTeam.n }}</span>
                        <span>{{ t('rankings.table.p') }}: {{ pTeam.p }}</span>
                        <span>+{{ pTeam.plus }} / -{{ pTeam.moins }} = {{ pTeam.diff }}</span>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Phase type E: elimination matches -->
                <template v-if="phase.type === 'E'">
                  <div class="p-3 bg-blue-100 space-y-2">
                    <template v-if="phase.matches && phase.matches.length > 0">
                      <div v-for="match in phase.matches" :key="match.id" class="flex items-center gap-1 py-1">
                        <span
                          class="flex-1 text-sm text-right truncate"
                          :class="match.scoreA !== null && match.scoreA > match.scoreB! ? 'font-bold text-gray-900' : 'text-gray-600'"
                        >{{ match.equipeA }}</span>
                        <span class="w-16 text-center text-sm font-mono font-semibold text-gray-700">
                          <template v-if="match.scoreA !== null">{{ match.scoreA }} - {{ match.scoreB }}</template>
                          <template v-else>—</template>
                        </span>
                        <span
                          class="flex-1 text-sm truncate"
                          :class="match.scoreB !== null && match.scoreB > match.scoreA! ? 'font-bold text-gray-900' : 'text-gray-600'"
                        >{{ match.equipeB }}</span>
                      </div>
                    </template>
                    <!-- Fallback: teams without match data -->
                    <template v-else>
                      <div v-for="pTeam in phase.teams" :key="pTeam.id" class="flex items-center gap-2 py-1">
                        <template v-if="pTeam.g > 0">
                          <span class="text-xs font-bold text-green-700 w-20">{{ t('rankings.winner') }}</span>
                          <span class="font-bold text-sm text-gray-900">{{ pTeam.libelle }}</span>
                        </template>
                        <template v-else-if="pTeam.p > 0">
                          <span class="text-xs italic text-red-600 w-20">{{ t('rankings.loser') }}</span>
                          <span class="italic text-sm text-gray-600">{{ pTeam.libelle }}</span>
                        </template>
                        <template v-else>
                          <span class="text-xs text-gray-400 w-20">—</span>
                          <span class="text-sm text-gray-600">{{ pTeam.libelle }}</span>
                          <button
                            v-if="canEditInline && !phase.consolidation && pTeam.j === 0"
                            class="p-0.5 text-red-400 hover:text-red-600 rounded ml-auto"
                            @click="showConfirm(t('common.delete'), t('rankings.confirm_delete_phase_team'), () => removePhaseTeam(phase, pTeam))"
                          >
                            <UIcon name="heroicons:trash-solid" class="w-4 h-4" />
                          </button>
                        </template>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
            </div>

            </div><!-- /lg:flex -->
          </template>
        </div>

        <!-- ═══ PUBLISHED TAB ═══ -->
        <div v-if="activeTab === 'published'" class="p-4">
          <!-- Publication info -->
          <div class="mb-1 p-3 bg-gray-50 rounded-lg">
            <template v-if="competitionInfo.datePublication">
              <div class="text-sm text-gray-700">
                <span class="font-medium">{{ t('rankings.publish.date_compute') }}</span> :
                {{ formatDate(competitionInfo.datePublicationCalcul) }}
                <span v-if="competitionInfo.modePublicationCalcul" class="ml-2 text-xs text-gray-500">
                  — {{ competitionInfo.modePublicationCalcul === 'tous' ? t('rankings.compute.mode_all') : t('rankings.compute.mode_locked') }}
                </span>
              </div>
              <div class="text-sm text-gray-700 mt-1">
                <span class="font-medium">{{ t('rankings.publish.date_publish') }}</span> :
                {{ formatDate(competitionInfo.datePublication) }}
                ({{ t('rankings.compute.by') }} {{ competitionInfo.userNamePublication }})
              </div>
              <!-- Alert if different -->
              <div
                v-if="isRankingDifferent"
                class="mt-2 flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800"
              >
                <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 shrink-0" />
                {{ t('rankings.publish.different') }}
              </div>
            </template>
            <div v-else class="text-sm text-gray-500 italic">
              {{ t('rankings.publish.not_published') }}
            </div>
          </div>

          <!-- Published ranking content -->
          <template v-if="competitionInfo.datePublication && ranking.length > 0">
            <!-- ── Toolbar (published tab) ── -->
            <div class="mb-4 flex flex-wrap items-center gap-3">
              <!-- LEFT: Selection actions -->
              <button
                v-if="canTransfer && selectedIds.length > 0"
                class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm flex items-center gap-1"
                @click="transferModalOpen = true"
              >
                <UIcon name="heroicons:arrow-right-circle" class="w-4 h-4" />
                {{ t('rankings.transfer.button') }} ({{ selectedIds.length }})
              </button>

              <div class="flex-1" />

              <!-- PDF dropdown (public) -->
              <div v-if="pdfUrls" class="relative">
                <button
                  class="pdf-dropdown-trigger px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm flex items-center gap-1"
                  @click="togglePdfDropdown($event, 'public')"
                >
                  <UIcon name="heroicons:document-text" class="w-4 h-4" />
                  {{ t('rankings.pdf.title') }}
                  <UIcon name="heroicons:chevron-down" class="w-3 h-3" />
                </button>
              </div>

              <!-- RIGHT: Actions -->
              <button
                v-if="canUnpublish"
                class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="unpublishing"
                @click="showConfirm(t('rankings.publish.title'), t('rankings.confirm_unpublish'), unpublishRanking)"
              >
                <template v-if="unpublishing">{{ t('rankings.publish.unpublishing') }}</template>
                <template v-else>{{ t('rankings.publish.unpublish') }}</template>
              </button>
            </div>

            <div class="lg:flex lg:gap-4 lg:items-start">
            <!-- Published general ranking table -->
            <div class="mb-4" :class="effectiveType === 'CP' && sortedPhases.length > 0 ? 'lg:flex-1 lg:min-w-0' : 'w-full'">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('rankings.pdf.general') }}</h3>

              <!-- Desktop table -->
              <div class="hidden lg:block overflow-x-auto rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 bg-green-100">
                  <thead>
                    <tr class="bg-green-200">
                      <th v-if="canTransfer" class="px-2 py-2">
                        <input
                          v-model="selectAll"
                          type="checkbox"
                          class="w-4 h-4 rounded border-gray-300 text-blue-600"
                          @change="toggleSelectAll()"
                        >
                      </th>
                      <th v-if="isInternational" class="px-2 py-2" />
                      <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.rank') }}</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                        {{ effectiveType === 'MULTI' ? structureLabel : t('rankings.table.team') }}
                      </th>
                      <template v-if="effectiveType === 'CHPT'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.g') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.n') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.p') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.f') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.plus') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.minus') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.diff') }}</th>
                      </template>
                      <template v-else-if="effectiveType === 'CP'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                      </template>
                      <template v-else-if="effectiveType === 'MULTI'">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr
                      v-for="(team, idx) in ranking"
                      :key="team.id"
                      class="hover:bg-gray-50"
                      :class="{ 'bg-blue-50': isSelected(team.id) }"
                    >
                      <td v-if="canTransfer" class="px-2 py-1.5">
                        <input
                          :checked="isSelected(team.id)"
                          type="checkbox"
                          class="w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer"
                          @change="toggleSelect(team.id)"
                        >
                      </td>
                      <td v-if="isInternational" class="px-2 py-1.5">
                        <img
                          v-if="getFlagUrl(team)"
                          :src="getFlagUrl(team)!"
                          :alt="team.codeClub"
                          class="w-6 h-6 object-contain"
                          @error="($event.target as HTMLImageElement).style.display = 'none'"
                        >
                      </td>
                      <td class="px-2 py-1.5 text-center text-sm">
                        <div class="flex items-center justify-center gap-1">
                          <span
                            v-if="getQualifiedStatus(idx, ranking.length) === 'qualified'"
                            class="text-green-600 text-xs"
                          >▲</span>
                          <span
                            v-else-if="getQualifiedStatus(idx, ranking.length) === 'eliminated'"
                            class="text-red-600 text-xs"
                          >▼</span>
                          {{ effectiveType === 'CP' ? team.cltNiveauPubli : team.cltPubli }}
                        </div>
                      </td>
                      <td class="px-2 py-1.5 text-sm font-medium text-gray-900">{{ team.libelle }}</td>
                      <template v-if="effectiveType === 'CHPT'">
                        <td class="px-2 py-1.5 text-center text-sm">{{ displayPts(team.ptsPubli) }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.jPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.gPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.nPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.pPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.fPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.plusPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.moinsPubli }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.diffPubli }}</td>
                      </template>
                      <template v-else-if="effectiveType === 'CP'">
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.jPubli }}</td>
                      </template>
                      <template v-else-if="effectiveType === 'MULTI'">
                        <td class="px-2 py-1.5 text-center text-sm">{{ displayPts(team.ptsPubli) }}</td>
                        <td class="px-2 py-1.5 text-center text-sm">{{ team.jPubli }}</td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile cards (published) -->
              <div class="lg:hidden divide-y divide-gray-200">
                <div
                  v-for="(team, idx) in ranking"
                  :key="team.id"
                  class="p-3"
                  :class="{ 'bg-blue-50': isSelected(team.id) }"
                >
                  <div class="flex items-start gap-2">
                    <input
                      v-if="canTransfer"
                      :checked="isSelected(team.id)"
                      type="checkbox"
                      class="w-4 h-4 rounded border-gray-300 text-blue-600 mt-0.5 cursor-pointer"
                      @change="toggleSelect(team.id)"
                    >
                    <img
                      v-if="isInternational && getFlagUrl(team)"
                      :src="getFlagUrl(team)!"
                      :alt="team.codeClub"
                      class="w-6 h-6 object-contain mt-0.5"
                      @error="($event.target as HTMLImageElement).style.display = 'none'"
                    >
                    <span
                      v-if="getQualifiedStatus(idx, ranking.length) === 'qualified'"
                      class="text-green-600 text-xs mt-0.5"
                    >▲</span>
                    <span
                      v-else-if="getQualifiedStatus(idx, ranking.length) === 'eliminated'"
                      class="text-red-600 text-xs mt-0.5"
                    >▼</span>
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-gray-900">{{ team.libelle }}</div>
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                        <span>{{ t('rankings.table.rank') }}: {{ effectiveType === 'CP' ? team.cltNiveauPubli : team.cltPubli }}</span>
                        <template v-if="effectiveType === 'CHPT'">
                          <span>{{ t('rankings.table.pts') }}: {{ displayPts(team.ptsPubli) }}</span>
                          <span>{{ t('rankings.table.j') }}: {{ team.jPubli }}</span>
                          <span>{{ t('rankings.table.diff') }}: {{ team.diffPubli }}</span>
                        </template>
                        <template v-else-if="effectiveType === 'CP'">
                          <span>{{ t('rankings.table.j') }}: {{ team.jPubli }}</span>
                        </template>
                        <template v-else-if="effectiveType === 'MULTI'">
                          <span>{{ t('rankings.table.pts') }}: {{ displayPts(team.ptsPubli) }}</span>
                          <span>{{ t('rankings.table.j') }}: {{ team.jPubli }}</span>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Published phases (CP only, read-only, sorted by niveau DESC) -->
            <div v-if="effectiveType === 'CP' && sortedPhases.length > 0" class="mb-4 lg:flex-1 lg:min-w-0">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ t('rankings.phases.title') }}</h3>

              <div v-for="phase in sortedPhases" :key="phase.idJournee" class="mb-4 border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-2 bg-green-300 border-b border-gray-200 flex items-center justify-between gap-3">
                  <span class="font-medium text-sm text-gray-800">
                    {{ phase.phase }}
                    <span v-if="phase.lieu" class="text-gray-500">({{ phase.lieu }})</span>
                  </span>
                  <span v-if="phase.type === 'E'" class="text-sm text-gray-600 italic">{{ t('rankings.phases.elimination') }}</span>
                </div>

                <!-- Phase type C (published, read-only) -->
                <template v-if="phase.type === 'C'">
                  <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 bg-green-100">
                      <thead class="bg-gray-50">
                        <tr class="bg-green-200">
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.rank') }}</th>
                          <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.team') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.pts') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.j') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.g') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.n') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.p') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.f') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.plus') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.minus') }}</th>
                          <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">{{ t('rankings.table.diff') }}</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                        <tr v-for="pTeam in phase.teams" :key="pTeam.id" class="hover:bg-gray-50">
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.cltPubli }}</td>
                          <td class="px-2 py-1.5 text-sm font-medium text-gray-900">{{ pTeam.libelle }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ displayPts(pTeam.ptsPubli) }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.jPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.gPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.nPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.pPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.fPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.plusPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.moinsPubli }}</td>
                          <td class="px-2 py-1.5 text-center text-sm">{{ pTeam.diffPubli }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Mobile cards -->
                  <div class="lg:hidden divide-y divide-gray-200">
                    <div v-for="pTeam in phase.teams" :key="pTeam.id" class="p-3">
                      <div class="font-medium text-gray-900 text-sm">{{ pTeam.libelle }}</div>
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                        <span>{{ t('rankings.table.rank') }}: {{ pTeam.cltPubli }}</span>
                        <span>{{ t('rankings.table.pts') }}: {{ displayPts(pTeam.ptsPubli) }}</span>
                        <span>{{ t('rankings.table.j') }}: {{ pTeam.jPubli }}</span>
                        <span>+{{ pTeam.plusPubli }} / -{{ pTeam.moinsPubli }} = {{ pTeam.diffPubli }}</span>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Phase type E (published, read-only) -->
                <template v-if="phase.type === 'E'">
                  <div class="p-3 space-y-2">
                    <template v-if="phase.matches && phase.matches.length > 0">
                      <div v-for="match in phase.matches" :key="match.id" class="flex items-center gap-1 py-1">
                        <span
                          class="flex-1 text-sm text-right truncate"
                          :class="match.scoreA !== null && match.scoreA > match.scoreB! ? 'font-bold text-gray-900' : 'text-gray-600'"
                        >{{ match.equipeA }}</span>
                        <span class="w-16 text-center text-sm font-mono font-semibold text-gray-700">
                          <template v-if="match.scoreA !== null">{{ match.scoreA }} - {{ match.scoreB }}</template>
                          <template v-else>—</template>
                        </span>
                        <span
                          class="flex-1 text-sm truncate"
                          :class="match.scoreB !== null && match.scoreB > match.scoreA! ? 'font-bold text-gray-900' : 'text-gray-600'"
                        >{{ match.equipeB }}</span>
                      </div>
                    </template>
                    <template v-else>
                      <div v-for="pTeam in phase.teams" :key="pTeam.id" class="flex items-center gap-2 py-1">
                        <template v-if="pTeam.gPubli > 0">
                          <span class="text-xs font-bold text-green-700 w-20">{{ t('rankings.winner') }}</span>
                          <span class="font-bold text-sm text-gray-900">{{ pTeam.libelle }}</span>
                        </template>
                        <template v-else-if="pTeam.pPubli > 0">
                          <span class="text-xs italic text-red-600 w-20">{{ t('rankings.loser') }}</span>
                          <span class="italic text-sm text-gray-600">{{ pTeam.libelle }}</span>
                        </template>
                        <template v-else>
                          <span class="text-xs text-gray-400 w-20">—</span>
                          <span class="text-sm text-gray-600">{{ pTeam.libelle }}</span>
                        </template>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
            </div>

            </div><!-- /lg:flex -->
          </template>

          <!-- No published ranking -->
          <div v-else-if="!competitionInfo?.datePublication" class="p-8 text-center text-gray-500">
            {{ t('rankings.publish.not_published') }}
          </div>
        </div>
      </div>

    </template>

    <!-- Transfer modal -->
    <AdminModal
      :open="transferModalOpen"
      :title="t('rankings.transfer.title')"
      max-width="lg"
      @close="transferModalOpen = false"
    >
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          {{ t('rankings.transfer.button') }} : <strong>{{ selectedIds.length }}</strong> {{ selectedIds.length > 1 ? 'équipes' : 'équipe' }}
        </p>

        <!-- Season selector -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('rankings.transfer.target_season') }}</label>
          <select
            v-model="transferSeason"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
          >
            <option value="">—</option>
            <option v-for="s in workContext.seasons" :key="s.code" :value="s.code">
              {{ s.libelle }}
            </option>
          </select>
        </div>

        <!-- Competition selector -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('rankings.transfer.target_competition') }}</label>
          <select
            v-model="transferCompetition"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            :disabled="!transferSeason || transferCompetitionsLoading"
          >
            <option value="">—</option>
            <option v-for="c in transferCompetitions" :key="c.code" :value="c.code">
              {{ c.code }} - {{ c.libelle }}
            </option>
          </select>
        </div>
      </div>

      <template #footer>
        <button
          class="px-4 py-2 text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 rounded-lg transition-colors text-sm"
          @click="transferModalOpen = false"
        >
          {{ t('common.cancel') }}
        </button>
        <button
          class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="transferring || !transferSeason || !transferCompetition"
          @click="showConfirm(
            t('rankings.transfer.title'),
            t('rankings.transfer.confirm', { count: selectedIds.length, competition: transferCompetition, season: transferSeason }),
            () => { transferModalOpen = false; doTransfer() }
          )"
        >
          <template v-if="transferring">{{ t('rankings.transfer.transferring') }}</template>
          <template v-else>{{ t('rankings.transfer.button') }}</template>
        </button>
      </template>
    </AdminModal>

    <!-- Confirm modal -->
    <AdminConfirmModal
      :open="confirmModal.open"
      :title="confirmModal.title"
      :message="confirmModal.message"
      :danger="false"
      variant="info"
      @close="confirmModal.open = false"
      @confirm="executeConfirm"
    />

    <!-- PDF dropdown (teleported) -->
    <Teleport to="body">
      <div
        v-if="pdfDropdownOpen && pdfUrls"
        class="pdf-dropdown-menu z-9999 bg-white rounded-lg shadow-lg border border-gray-200 py-1 min-w-50"
        :style="pdfDropdownStyle"
      >
        <a
          v-if="pdfDropdownMode === 'admin' ? pdfUrls.general?.admin : pdfUrls.general?.public"
          :href="pdfDropdownMode === 'admin' ? pdfUrls.general!.admin : pdfUrls.general!.public"
          target="_blank"
          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
          @click="pdfDropdownOpen = false"
        >
          <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
          {{ t('rankings.pdf.general') }}
        </a>
        <a
          v-if="pdfDropdownMode === 'admin' ? pdfUrls.progress?.admin : pdfUrls.progress?.public"
          :href="pdfDropdownMode === 'admin' ? pdfUrls.progress!.admin : pdfUrls.progress!.public"
          target="_blank"
          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
          @click="pdfDropdownOpen = false"
        >
          <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
          {{ t('rankings.pdf.progress') }}
        </a>
        <a
          v-if="pdfDropdownMode === 'admin' ? pdfUrls.detail?.admin : pdfUrls.detail?.public"
          :href="pdfDropdownMode === 'admin' ? pdfUrls.detail!.admin : pdfUrls.detail!.public"
          target="_blank"
          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
          @click="pdfDropdownOpen = false"
        >
          <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
          {{ t('rankings.pdf.detail') }}
        </a>
        <a
          v-if="pdfDropdownMode === 'admin' ? pdfUrls.matches?.admin : pdfUrls.matches?.public"
          :href="pdfDropdownMode === 'admin' ? pdfUrls.matches!.admin : pdfUrls.matches!.public"
          target="_blank"
          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
          @click="pdfDropdownOpen = false"
        >
          <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
          {{ t('rankings.pdf.matches') }}
        </a>
      </div>
    </Teleport>
  </div>
</template>
