<script setup lang="ts">
import type { ImageType } from '~/types/operations'
import type { useImageVersionStore } from '~/stores/imageVersionStore'

type BumpableImageType = Parameters<ReturnType<typeof useImageVersionStore>['bump']>[0]

interface ImageTypeConfig {
  label: string
  formatHint: string
  accept: string
  maxWidth: number
  maxHeight: number
  requiredFields: string[]
}

interface ImageEntry {
  filename: string
  size: number
  modified: number
}

const { t } = useI18n()
const api = useApi()
const toast = useToast()
const imageVersionStore = useImageVersionStore()
const config = useRuntimeConfig()

const legacyBaseUrl = config.public.legacyBaseUrl as string

// Static image directory paths matching ImageOperationsService.$imageConfig
const imageDestinations: Record<string, string> = {
  logo_competition: '/img/logo/',
  bandeau_competition: '/img/logo/',
  sponsor_competition: '/img/logo/',
  logo_club: '/img/KIP/logo/',
  logo_nation: '/img/Nations/',
  photo_equipe: '/img/KIP/teams/',
}

// ── State ──────────────────────────────────────────────────────────────────

const loading = ref(false)
const selectedImageType = ref<ImageType>('logo_competition')
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const imageTypesConfig = ref<Record<string, ImageTypeConfig>>({})

// Form fields
const codeCompetition = ref('')
const saison = ref('')
const saisonEquipe = ref('')
const codeNation = ref('')

// Club autocomplete
interface ClubResult { numero: string; nom: string; label: string }
const clubSearch = ref('')
const clubSearchResults = ref<ClubResult[]>([])
const clubSearchLoading = ref(false)
const clubSearchOpen = ref(false)
const selectedClubNumero = ref('')
const clubSearchRef = ref<HTMLElement | null>(null)
let clubSearchTimer: ReturnType<typeof setTimeout> | null = null

// Team autocomplete
interface TeamResult { numero: number; libelle: string; club: string; label: string }
const teamSearch = ref('')
const teamSearchResults = ref<TeamResult[]>([])
const teamSearchLoading = ref(false)
const teamSearchOpen = ref(false)
const selectedTeamNumero = ref('')
const teamSearchRef = ref<HTMLElement | null>(null)
let teamSearchTimer: ReturnType<typeof setTimeout> | null = null

// Gallery
const gallerySearch = ref('')
const galleryImages = ref<ImageEntry[]>([])
const galleryTotal = ref(0)
const galleryPage = ref(1)
const galleryPageSize = 50
const galleryLoading = ref(false)
let gallerySearchTimer: ReturnType<typeof setTimeout> | null = null

// Inline rename
const renamingFilename = ref('')
const renameValue = ref('')

// Modals
const confirmOverwriteModal = ref(false)
const overwriteFilename = ref('')
const overwriteArchiveName = ref('')

const confirmDeleteModal = ref(false)
const deleteTarget = ref<ImageEntry | null>(null)
const deleteUsedBy = ref<string[]>([])

// Import URL
const importUrl = ref('')
const importUrlLoading = ref(false)
const importUrlCompetition = ref('')
const importUrlSaison = ref('')
const importUrlClub = ref('')
const importUrlNation = ref('')
const importUrlTeam = ref('')
const importUrlSaisonEquipe = ref('')

// Fallback image type options (used before API loads)
const imageTypesFallback: { value: ImageType; label: string; accept: string; formatHint: string }[] = [
  { value: 'logo_competition', label: 'Logo compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 1000x1000px' },
  { value: 'bandeau_competition', label: 'Bandeau compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 2480x250px' },
  { value: 'sponsor_competition', label: 'Sponsor compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 2480x250px' },
  { value: 'logo_club', label: 'Logo club', accept: 'image/png', formatHint: 'PNG uniquement, max 200x200px' },
  { value: 'logo_nation', label: 'Logo nation', accept: 'image/png', formatHint: 'PNG uniquement, max 200x200px' },
  { value: 'photo_equipe', label: 'Photo équipe', accept: 'image/jpeg', formatHint: 'JPG uniquement, max 1920x1080px' },
]

// ── Init ───────────────────────────────────────────────────────────────────

onMounted(async () => {
  try {
    const cfg = await api.get<Record<string, ImageTypeConfig>>('/admin/operations/images/types')
    imageTypesConfig.value = cfg
  } catch {
    console.warn('Failed to load image types config, using fallback')
  }
  loadGallery()
})

// ── Computed ───────────────────────────────────────────────────────────────

const imageTypes = computed(() =>
  imageTypesFallback.map(fallback => {
    const apiConfig = imageTypesConfig.value[fallback.value]
    return {
      value: fallback.value,
      label: apiConfig?.label || fallback.label,
      accept: apiConfig?.accept || fallback.accept,
      formatHint: apiConfig?.formatHint || fallback.formatHint,
    }
  })
)

const currentConfig = computed(() => imageTypes.value.find(t => t.value === selectedImageType.value))
const currentAccept = computed(() => currentConfig.value?.accept || 'image/jpeg')
const currentFormatHint = computed(() => currentConfig.value?.formatHint || '')

const needsCompetitionFields = computed(() =>
  ['logo_competition', 'bandeau_competition', 'sponsor_competition'].includes(selectedImageType.value)
)
const needsClubField = computed(() => selectedImageType.value === 'logo_club')
const needsNationField = computed(() => selectedImageType.value === 'logo_nation')
const needsTeamField = computed(() => selectedImageType.value === 'photo_equipe')

const canUpload = computed(() => {
  if (!selectedFile.value) return false
  if (needsCompetitionFields.value && (!codeCompetition.value.trim() || !saison.value.trim())) return false
  if (needsClubField.value && !selectedClubNumero.value) return false
  if (needsNationField.value && !codeNation.value.trim()) return false
  if (needsTeamField.value && (!selectedTeamNumero.value || !saisonEquipe.value.trim())) return false
  return true
})

const canImportUrl = computed(() => {
  if (!importUrl.value.trim()) return false
  if (needsCompetitionFields.value && (!importUrlCompetition.value.trim() || !importUrlSaison.value.trim())) return false
  if (needsClubField.value && !importUrlClub.value.trim()) return false
  if (needsNationField.value && !importUrlNation.value.trim()) return false
  if (needsTeamField.value && (!importUrlTeam.value.trim() || !importUrlSaisonEquipe.value.trim())) return false
  return true
})

// ── Gallery ────────────────────────────────────────────────────────────────

const galleryPageCount = computed(() => Math.max(1, Math.ceil(galleryTotal.value / galleryPageSize)))

const loadGallery = async () => {
  galleryLoading.value = true
  try {
    const result = await api.get<{ total: number; items: ImageEntry[] }>('/admin/operations/images/list', {
      imageType: selectedImageType.value,
      q: gallerySearch.value,
      page: galleryPage.value,
      limit: galleryPageSize,
    })
    galleryImages.value = result.items
    galleryTotal.value = result.total
  } catch {
    galleryImages.value = []
    galleryTotal.value = 0
  } finally {
    galleryLoading.value = false
  }
}

const onGallerySearchInput = () => {
  if (gallerySearchTimer) clearTimeout(gallerySearchTimer)
  galleryPage.value = 1
  gallerySearchTimer = setTimeout(loadGallery, 300)
}

const galleryGoToPage = (page: number) => {
  galleryPage.value = page
  loadGallery()
}

const imageUrl = (filename: string) => {
  const dest = imageDestinations[selectedImageType.value] ?? '/img/logo/'
  return `${legacyBaseUrl}${dest}${encodeURIComponent(filename)}`
}

const formatBytes = (bytes: number) => {
  if (bytes < 1024) return `${bytes} o`
  if (bytes < 1048576) return `${Math.round(bytes / 1024)} Ko`
  return `${(bytes / 1048576).toFixed(1)} Mo`
}

const formatDate = (ts: number) => {
  return new Date(ts * 1000).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// ── Inline rename ──────────────────────────────────────────────────────────

const startRename = (entry: ImageEntry) => {
  renamingFilename.value = entry.filename
  renameValue.value = entry.filename
}

const cancelRename = () => {
  renamingFilename.value = ''
  renameValue.value = ''
}

const canConfirmRename = computed(() =>
  renameValue.value.trim() && renameValue.value.trim() !== renamingFilename.value
)

const confirmRename = async () => {
  if (!canConfirmRename.value) return
  loading.value = true
  try {
    await api.post('/admin/operations/images/rename', {
      imageType: selectedImageType.value,
      currentName: renamingFilename.value,
      newName: renameValue.value.trim(),
    })
    toast.add({ title: t('common.success'), description: t('operations.images.success_rename'), color: 'success', duration: 3000 })
    cancelRename()
    await loadGallery()
  } catch {
    toast.add({ title: t('common.error'), description: t('operations.images.error_rename'), color: 'error', duration: 3000 })
  } finally {
    loading.value = false
  }
}

// ── Delete ─────────────────────────────────────────────────────────────────

const openDeleteModal = (entry: ImageEntry) => {
  deleteTarget.value = entry
  deleteUsedBy.value = []
  confirmDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  loading.value = true
  try {
    await api.del('/admin/operations/images/delete', {
      imageType: selectedImageType.value,
      filename: deleteTarget.value.filename,
    })
    toast.add({ title: t('common.success'), description: t('operations.images.success_delete'), color: 'success', duration: 3000 })
    confirmDeleteModal.value = false
    deleteTarget.value = null
    await loadGallery()
  } catch (err: unknown) {
    const e = err as { status?: number; data?: { code?: string; usedBy?: string[] } }
    if (e?.status === 409 && e?.data?.code === 'FILE_IN_USE') {
      deleteUsedBy.value = e.data?.usedBy ?? []
    } else {
      toast.add({ title: t('common.error'), description: t('operations.images.error_delete'), color: 'error', duration: 3000 })
      confirmDeleteModal.value = false
    }
  } finally {
    loading.value = false
  }
}

// ── Upload ─────────────────────────────────────────────────────────────────

const onFileSelected = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files?.[0]) selectedFile.value = target.files[0]
}

const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const clearClubSearch = () => {
  clubSearch.value = ''
  clubSearchResults.value = []
  clubSearchOpen.value = false
  selectedClubNumero.value = ''
}

const clearTeamSearch = () => {
  teamSearch.value = ''
  teamSearchResults.value = []
  teamSearchOpen.value = false
  selectedTeamNumero.value = ''
}

const resetUploadForm = () => {
  clearFile()
  codeCompetition.value = ''
  saison.value = ''
  saisonEquipe.value = ''
  clearClubSearch()
  clearTeamSearch()
  codeNation.value = ''
}

const onClubSearchInput = () => {
  selectedClubNumero.value = ''
  if (clubSearchTimer) clearTimeout(clubSearchTimer)
  const q = clubSearch.value.trim()
  if (q.length < 2) {
    clubSearchResults.value = []
    clubSearchOpen.value = false
    return
  }
  clubSearchTimer = setTimeout(async () => {
    clubSearchLoading.value = true
    try {
      const results = await api.get<ClubResult[]>('/admin/operations/autocomplete/clubs', { q, limit: 20 })
      clubSearchResults.value = results
      clubSearchOpen.value = results.length > 0
    } catch {
      clubSearchResults.value = []
    } finally {
      clubSearchLoading.value = false
    }
  }, 300)
}

const selectClub = (club: ClubResult) => {
  selectedClubNumero.value = club.numero
  clubSearch.value = club.label
  clubSearchOpen.value = false
}

const onTeamSearchInput = () => {
  selectedTeamNumero.value = ''
  if (teamSearchTimer) clearTimeout(teamSearchTimer)
  const q = teamSearch.value.trim()
  if (q.length < 2) {
    teamSearchResults.value = []
    teamSearchOpen.value = false
    return
  }
  teamSearchTimer = setTimeout(async () => {
    teamSearchLoading.value = true
    try {
      const results = await api.get<TeamResult[]>('/admin/operations/autocomplete/teams', { q, limit: 20 })
      teamSearchResults.value = results
      teamSearchOpen.value = results.length > 0
    } catch {
      teamSearchResults.value = []
    } finally {
      teamSearchLoading.value = false
    }
  }, 300)
}

const selectTeam = (team: TeamResult) => {
  selectedTeamNumero.value = String(team.numero)
  teamSearch.value = `${team.libelle} (${team.club})`
  teamSearchOpen.value = false
}

const handleGlobalClick = (e: MouseEvent) => {
  if (clubSearchRef.value && !clubSearchRef.value.contains(e.target as HTMLElement)) {
    clubSearchOpen.value = false
  }
  if (teamSearchRef.value && !teamSearchRef.value.contains(e.target as HTMLElement)) {
    teamSearchOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleGlobalClick))
onBeforeUnmount(() => document.removeEventListener('click', handleGlobalClick))

const buildFormData = (overwrite = false) => {
  const formData = new FormData()
  formData.append('imageType', selectedImageType.value)
  formData.append('imageFile', selectedFile.value!)
  if (overwrite) formData.append('overwrite', 'true')
  if (needsCompetitionFields.value) {
    formData.append('codeCompetition', codeCompetition.value.trim().toUpperCase())
    formData.append('saison', saison.value.trim())
  } else if (needsClubField.value) {
    formData.append('numeroClub', selectedClubNumero.value)
  } else if (needsNationField.value) {
    formData.append('codeNation', codeNation.value.trim().toUpperCase())
  } else if (needsTeamField.value) {
    formData.append('numeroEquipe', selectedTeamNumero.value)
    formData.append('saison', saisonEquipe.value.trim())
  }
  return formData
}

const doUpload = async (overwrite = false) => {
  loading.value = true
  try {
    const result = await api.upload<{ message: string; filename: string; resized?: boolean }>(
      '/admin/operations/images/upload', buildFormData(overwrite), [409]
    )
    const description = result.resized
      ? t('operations.images.success_upload_resized', { filename: result.filename })
      : t('operations.images.success_upload', { filename: result.filename })
    toast.add({ title: t('common.success'), description, color: 'success', duration: 5000 })
    imageVersionStore.bump(selectedImageType.value as BumpableImageType)
    resetUploadForm()
    await loadGallery()
  } finally {
    loading.value = false
  }
}

const uploadImage = async () => {
  if (!canUpload.value || !selectedFile.value) return
  try {
    await doUpload(false)
  } catch (err: unknown) {
    const e = err as { status?: number; data?: { code?: string; filename?: string; archiveName?: string } }
    if (e?.status === 409 && e?.data?.code === 'FILE_EXISTS') {
      overwriteFilename.value = e.data!.filename!
      overwriteArchiveName.value = e.data!.archiveName!
      confirmOverwriteModal.value = true
    } else {
      toast.add({ title: t('common.error'), description: t('operations.images.error_upload'), color: 'error', duration: 3000 })
    }
  }
}

const confirmOverwrite = async () => {
  confirmOverwriteModal.value = false
  try {
    await doUpload(true)
  } catch {
    toast.add({ title: t('common.error'), description: t('operations.images.error_upload'), color: 'error', duration: 3000 })
  }
}

// ── Import URL ─────────────────────────────────────────────────────────────

const doImportUrl = async () => {
  if (!canImportUrl.value) return
  importUrlLoading.value = true
  try {
    const params: Record<string, string> = { imageType: selectedImageType.value, url: importUrl.value.trim() }
    if (needsCompetitionFields.value) {
      params.codeCompetition = importUrlCompetition.value.trim().toUpperCase()
      params.saison = importUrlSaison.value.trim()
    } else if (needsClubField.value) {
      params.numeroClub = importUrlClub.value.trim()
    } else if (needsNationField.value) {
      params.codeNation = importUrlNation.value.trim().toUpperCase()
    } else if (needsTeamField.value) {
      params.numeroEquipe = importUrlTeam.value.trim()
      params.saison = importUrlSaisonEquipe.value.trim()
    }
    const result = await api.post<{ filename: string; resized?: boolean }>('/admin/operations/images/import-url', params)
    const description = result.resized
      ? t('operations.images.success_upload_resized', { filename: result.filename })
      : t('operations.images.success_upload', { filename: result.filename })
    toast.add({ title: t('common.success'), description, color: 'success', duration: 5000 })
    imageVersionStore.bump(selectedImageType.value as BumpableImageType)
    importUrl.value = ''
    importUrlCompetition.value = ''
    importUrlSaison.value = ''
    importUrlClub.value = ''
    importUrlNation.value = ''
    importUrlTeam.value = ''
    importUrlSaisonEquipe.value = ''
    await loadGallery()
  } catch {
    toast.add({ title: t('common.error'), description: t('operations.images.error_import_url'), color: 'error', duration: 3000 })
  } finally {
    importUrlLoading.value = false
  }
}

// Internal tab navigation
const activeSubTab = ref('gallery')

// ── Type change ────────────────────────────────────────────────────────────

const onTypeChange = (type: ImageType) => {
  selectedImageType.value = type
  resetUploadForm()
  importUrl.value = ''
  importUrlCompetition.value = ''
  importUrlSaison.value = ''
  importUrlClub.value = ''
  importUrlNation.value = ''
  importUrlTeam.value = ''
  importUrlSaisonEquipe.value = ''
  cancelRename()
  gallerySearch.value = ''
  galleryPage.value = 1
  loadGallery()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Image type selector (shared across all sub-tabs) -->
    <div>
      <label class="block text-sm font-medium text-header-700 mb-2">
        {{ t('operations.images.type') }}
      </label>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="type in imageTypes"
          :key="type.value"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            selectedImageType === type.value
              ? 'bg-primary-600 text-white'
              : 'bg-header-100 text-header-700 hover:bg-header-200',
          ]"
          @click="onTypeChange(type.value as ImageType)"
        >
          {{ type.label }}
        </button>
      </div>
      <p v-if="currentFormatHint" class="mt-2 text-sm text-primary-600 flex items-center gap-1">
        <UIcon name="i-heroicons-information-circle" class="w-4 h-4" />
        {{ currentFormatHint }}
      </p>
    </div>

    <!-- Internal sub-tab navigation -->
    <div class="border-b border-header-200">
      <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Images tabs">
        <button
          v-for="tab in [
            { id: 'gallery', label: t('operations.images.gallery'), icon: 'i-heroicons-photo' },
            { id: 'upload', label: t('operations.images.upload'), icon: 'i-heroicons-arrow-up-tray' },
            { id: 'import_url', label: t('operations.images.import_url'), icon: 'i-heroicons-link' },
          ]"
          :key="tab.id"
          :class="[
            activeSubTab === tab.id
              ? 'border-primary-500 text-primary-600 bg-primary-50'
              : 'border-transparent text-header-500 hover:text-header-700 hover:border-header-300',
            'whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors rounded-t'
          ]"
          @click="activeSubTab = tab.id"
        >
          <UIcon :name="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- ── Gallery ──────────────────────────────────────────────────────── -->
    <section v-if="activeSubTab === 'gallery'">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-header-900">
          {{ t('operations.images.gallery') }}
          <span class="ml-2 text-sm font-normal text-header-500">({{ galleryTotal }})</span>
        </h2>
        <div class="flex items-center gap-2">
          <div class="relative">
            <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
            <input
              v-model="gallerySearch"
              type="text"
              :placeholder="t('operations.images.gallery_search_placeholder')"
              class="pl-9 pr-3 py-2 border border-header-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 w-56"
              @input="onGallerySearchInput"
            >
          </div>
          <button
            class="p-2 text-header-500 hover:text-header-700"
            :title="t('operations.images.refresh')"
            @click="loadGallery"
          >
            <UIcon :class="['w-5 h-5', galleryLoading ? 'animate-spin' : '']" name="i-heroicons-arrow-path" />
          </button>
        </div>
      </div>

      <div v-if="galleryLoading && galleryImages.length === 0" class="text-center py-8 text-header-400">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin mx-auto mb-2" />
        {{ t('common.loading') }}
      </div>

      <div v-else-if="galleryImages.length === 0" class="text-center py-8 text-header-400">
        <UIcon name="i-heroicons-photo" class="w-8 h-8 mx-auto mb-2" />
        {{ t('operations.images.gallery_empty') }}
      </div>

      <div v-else class="border border-header-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-header-50 text-header-600 text-xs uppercase">
            <tr>
              <th class="px-3 py-2 text-left w-16">{{ t('operations.images.col_preview') }}</th>
              <th class="px-3 py-2 text-left">{{ t('operations.images.col_filename') }}</th>
              <th class="px-3 py-2 text-right w-24">{{ t('operations.images.col_size') }}</th>
              <th class="px-3 py-2 text-right w-28">{{ t('operations.images.col_date') }}</th>
              <th class="px-3 py-2 text-right w-28">{{ t('operations.images.col_actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-header-100">
            <template v-for="entry in galleryImages" :key="entry.filename">
              <!-- Normal row -->
              <tr v-if="renamingFilename !== entry.filename" class="hover:bg-header-50 group">
                <td class="px-3 py-1">
                  <a :href="imageUrl(entry.filename)" target="_blank" rel="noopener">
                    <img
                      :src="imageUrl(entry.filename)"
                      :alt="entry.filename"
                      class="h-10 w-16 object-contain bg-header-100 rounded border border-header-200"
                      loading="lazy"
                    >
                  </a>
                </td>
                <td class="px-3 py-2 font-mono text-xs text-header-800">
                  <a :href="imageUrl(entry.filename)" target="_blank" rel="noopener" class="hover:underline hover:text-primary-600">
                    {{ entry.filename }}
                  </a>
                </td>
                <td class="px-3 py-2 text-right text-header-500">{{ formatBytes(entry.size) }}</td>
                <td class="px-3 py-2 text-right text-header-500">{{ formatDate(entry.modified) }}</td>
                <td class="px-3 py-2 text-right">
                  <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                      class="p-1.5 rounded text-header-500 hover:text-primary-600 hover:bg-primary-50"
                      :title="t('operations.images.rename_button')"
                      @click="startRename(entry)"
                    >
                      <UIcon name="i-heroicons-pencil" class="w-4 h-4" />
                    </button>
                    <button
                      class="p-1.5 rounded text-header-500 hover:text-error-600 hover:bg-error-50"
                      :title="t('common.delete')"
                      @click="openDeleteModal(entry)"
                    >
                      <UIcon name="i-heroicons-trash" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Inline rename row -->
              <tr v-else class="bg-primary-50">
                <td class="px-3 py-1">
                  <img
                    :src="imageUrl(entry.filename)"
                    :alt="entry.filename"
                    class="h-10 w-16 object-contain bg-header-100 rounded border border-header-200"
                    loading="lazy"
                  >
                </td>
                <td class="px-3 py-2" colspan="3">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="renameValue"
                      type="text"
                      class="flex-1 px-2 py-1 text-xs font-mono border border-primary-400 rounded focus:ring-2 focus:ring-primary-500"
                      @keyup.enter="confirmRename"
                      @keyup.escape="cancelRename"
                    >
                    <button
                      :disabled="!canConfirmRename || loading"
                      class="px-3 py-1 text-xs bg-primary-600 text-white rounded hover:bg-primary-700 disabled:opacity-50"
                      @click="confirmRename"
                    >
                      <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-3.5 h-3.5 animate-spin" />
                      <span v-else>{{ t('common.confirm') }}</span>
                    </button>
                    <button
                      class="px-3 py-1 text-xs bg-header-200 text-header-700 rounded hover:bg-header-300"
                      @click="cancelRename"
                    >
                      {{ t('common.cancel') }}
                    </button>
                  </div>
                </td>
                <td />
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="galleryPageCount > 1" class="flex items-center justify-between mt-4">
        <p class="text-sm text-header-500">
          {{ (galleryPage - 1) * galleryPageSize + 1 }}–{{ Math.min(galleryPage * galleryPageSize, galleryTotal) }}
          / {{ galleryTotal }}
        </p>
        <div class="flex items-center gap-1">
          <button
            :disabled="galleryPage === 1"
            class="px-3 py-1.5 rounded text-sm border border-header-300 text-header-600 hover:bg-header-50 disabled:opacity-40 disabled:cursor-not-allowed"
            @click="galleryGoToPage(galleryPage - 1)"
          >
            ←
          </button>
          <template v-for="p in galleryPageCount" :key="p">
            <button
              v-if="p === 1 || p === galleryPageCount || Math.abs(p - galleryPage) <= 2"
              :class="[
                'px-3 py-1.5 rounded text-sm border',
                p === galleryPage
                  ? 'bg-primary-600 text-white border-primary-600'
                  : 'border-header-300 text-header-600 hover:bg-header-50'
              ]"
              @click="galleryGoToPage(p)"
            >
              {{ p }}
            </button>
            <span
              v-else-if="p === galleryPage - 3 || p === galleryPage + 3"
              class="px-1 text-header-400"
            >…</span>
          </template>
          <button
            :disabled="galleryPage === galleryPageCount"
            class="px-3 py-1.5 rounded text-sm border border-header-300 text-header-600 hover:bg-header-50 disabled:opacity-40 disabled:cursor-not-allowed"
            @click="galleryGoToPage(galleryPage + 1)"
          >
            →
          </button>
        </div>
      </div>
    </section>

    <!-- ── Upload ───────────────────────────────────────────────────────── -->
    <section v-if="activeSubTab === 'upload'">
      <div class="max-w-xl space-y-4">
        <!-- Dynamic fields -->
        <template v-if="needsCompetitionFields">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.code_competition') }}
              </label>
              <input
                v-model="codeCompetition"
                type="text"
                placeholder="ex: N1H"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.season') }}
              </label>
              <input
                v-model="saison"
                type="text"
                placeholder="ex: 2024-2025"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
          </div>
        </template>

        <template v-else-if="needsClubField">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.club_number') }}
            </label>
            <div ref="clubSearchRef" class="relative">
              <div class="relative">
                <input
                  v-model="clubSearch"
                  type="text"
                  :placeholder="t('operations.images.club_search_placeholder')"
                  class="w-full px-3 py-2 pl-9 pr-8 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm"
                  @input="onClubSearchInput"
                  @focus="onClubSearchInput"
                >
                <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
                <button
                  v-if="clubSearch && !clubSearchLoading"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
                  @click="clearClubSearch"
                >
                  <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
                </button>
                <UIcon v-if="clubSearchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400 animate-spin" />
              </div>
              <div
                v-if="clubSearchOpen && clubSearchResults.length > 0"
                class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
              >
                <button
                  v-for="club in clubSearchResults"
                  :key="club.numero"
                  class="w-full px-3 py-2 text-left text-sm text-header-900 hover:bg-primary-50 focus:bg-primary-100 focus:outline-none flex items-center gap-2"
                  @click="selectClub(club)"
                >
                  <span class="font-mono text-xs text-header-700 bg-header-100 px-1.5 py-0.5 rounded">{{ club.numero }}</span>
                  <span>{{ club.nom }}</span>
                </button>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="needsNationField">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.nation_code') }}
            </label>
            <input
              v-model="codeNation"
              type="text"
              placeholder="ex: FRA"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
            >
          </div>
        </template>

        <template v-else-if="needsTeamField">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.team_search') }}
              </label>
              <div ref="teamSearchRef" class="relative">
                <div class="relative">
                  <input
                    v-model="teamSearch"
                    type="text"
                    :placeholder="t('operations.images.team_search_placeholder')"
                    class="w-full px-3 py-2 pl-9 pr-8 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm"
                    @input="onTeamSearchInput"
                    @focus="onTeamSearchInput"
                  >
                  <UIcon name="i-heroicons-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
                  <button
                    v-if="teamSearch && !teamSearchLoading"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
                    @click="clearTeamSearch"
                  >
                    <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
                  </button>
                  <UIcon v-if="teamSearchLoading" name="i-heroicons-arrow-path" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400 animate-spin" />
                </div>
                <div
                  v-if="teamSearchOpen && teamSearchResults.length > 0"
                  class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-header-200 rounded-lg shadow-lg"
                >
                  <button
                    v-for="team in teamSearchResults"
                    :key="team.numero"
                    class="w-full px-3 py-2 text-left text-sm text-header-900 hover:bg-primary-50 focus:outline-none flex flex-col"
                    @click="selectTeam(team)"
                  >
                    <span class="font-medium">{{ team.libelle }}</span>
                    <span class="text-xs text-header-500">{{ team.numero }} — {{ team.club }}</span>
                  </button>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.season') }}
              </label>
              <input
                v-model="saisonEquipe"
                type="text"
                placeholder="ex: 2024"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
          </div>
        </template>

        <!-- File input -->
        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.images.file') }}
          </label>
          <div class="flex items-center gap-3">
            <input
              ref="fileInput"
              type="file"
              :accept="currentAccept"
              class="flex-1 px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
              @change="onFileSelected"
            >
            <button v-if="selectedFile" class="px-3 py-2 text-header-600 hover:text-header-900" @click="clearFile">
              <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
            </button>
          </div>
          <p v-if="selectedFile" class="mt-1 text-sm text-header-500">
            {{ selectedFile.name }} ({{ formatBytes(selectedFile.size) }})
          </p>
        </div>

        <button
          :disabled="!canUpload || loading"
          class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="uploadImage"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          <UIcon v-else name="i-heroicons-arrow-up-tray" class="w-4 h-4" />
          {{ t('operations.images.upload_button') }}
        </button>
      </div>
    </section>

    <!-- ── Import URL ────────────────────────────────────────────────────── -->
    <section v-if="activeSubTab === 'import_url'">
      <div class="max-w-xl space-y-4">
        <!-- Dynamic fields for import URL -->
        <template v-if="needsCompetitionFields">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.code_competition') }}
              </label>
              <input
                v-model="importUrlCompetition"
                type="text"
                placeholder="ex: N1H"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.season') }}
              </label>
              <input
                v-model="importUrlSaison"
                type="text"
                placeholder="ex: 2024-2025"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
          </div>
        </template>

        <template v-else-if="needsClubField">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.club_number') }}
            </label>
            <input
              v-model="importUrlClub"
              type="text"
              placeholder="ex: 12345"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </template>

        <template v-else-if="needsNationField">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.nation_code') }}
            </label>
            <input
              v-model="importUrlNation"
              type="text"
              placeholder="ex: FRA"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 uppercase"
            >
          </div>
        </template>

        <template v-else-if="needsTeamField">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.team_number') }}
              </label>
              <input
                v-model="importUrlTeam"
                type="text"
                placeholder="ex: 12345"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-header-700 mb-1">
                {{ t('operations.images.season') }}
              </label>
              <input
                v-model="importUrlSaisonEquipe"
                type="text"
                placeholder="ex: 2024"
                class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
            </div>
          </div>
        </template>

        <div>
          <label class="block text-sm font-medium text-header-700 mb-1">
            {{ t('operations.images.import_url_label') }}
          </label>
          <input
            v-model="importUrl"
            type="url"
            placeholder="https://example.com/image.png"
            class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm"
          >
        </div>

        <button
          :disabled="!canImportUrl || importUrlLoading"
          class="px-4 py-2 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="doImportUrl"
        >
          <UIcon v-if="importUrlLoading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          <UIcon v-else name="i-heroicons-link" class="w-4 h-4" />
          {{ t('operations.images.import_url_button') }}
        </button>
      </div>
    </section>

    <!-- Confirm overwrite modal -->
    <AdminConfirmModal
      :open="confirmOverwriteModal"
      :title="t('operations.images.confirm_overwrite')"
      :message="t('operations.images.confirm_overwrite_message', { filename: overwriteFilename, archiveName: overwriteArchiveName })"
      :item-name="overwriteFilename"
      :confirm-text="t('operations.images.overwrite_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      variant="warning"
      @close="confirmOverwriteModal = false"
      @confirm="confirmOverwrite"
    />

    <!-- Confirm delete modal -->
    <AdminConfirmModal
      :open="confirmDeleteModal"
      :title="t('operations.images.confirm_delete')"
      :message="deleteUsedBy.length > 0
        ? t('operations.images.confirm_delete_in_use', { list: deleteUsedBy.join(', ') })
        : t('operations.images.confirm_delete_message')"
      :item-name="deleteTarget?.filename ?? ''"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      :disabled-confirm="deleteUsedBy.length > 0"
      variant="danger"
      @close="confirmDeleteModal = false; deleteUsedBy = []"
      @confirm="confirmDelete"
    />
  </div>
</template>
