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

const { t } = useI18n()
const api = useApi()
const toast = useToast()
const imageVersionStore = useImageVersionStore()

// State
const loading = ref(false)
const selectedImageType = ref<ImageType>('logo_competition')
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const imageTypesConfig = ref<Record<string, ImageTypeConfig>>({})

// Form fields based on image type
const codeCompetition = ref('')
const saison = ref('')
const codeNation = ref('')

// Club autocomplete state
interface ClubResult { numero: string; nom: string; label: string }
const clubSearch = ref('')
const clubSearchResults = ref<ClubResult[]>([])
const clubSearchLoading = ref(false)
const clubSearchOpen = ref(false)
const selectedClubNumero = ref('')
const clubSearchRef = ref<HTMLElement | null>(null)
let clubSearchTimer: ReturnType<typeof setTimeout> | null = null

// Overwrite confirmation state
const confirmOverwriteModal = ref(false)
const overwriteFilename = ref('')
const overwriteArchiveName = ref('')

// Rename state
const currentName = ref('')
const newName = ref('')
const confirmRenameModal = ref(false)

// Fallback image type options (used before API loads)
const imageTypesFallback: { value: ImageType; label: string; accept: string; formatHint: string }[] = [
  { value: 'logo_competition', label: 'Logo compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 1000x1000px' },
  { value: 'bandeau_competition', label: 'Bandeau compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 2480x250px' },
  { value: 'sponsor_competition', label: 'Sponsor compétition', accept: 'image/jpeg,image/png', formatHint: 'JPG ou PNG, max 2480x250px' },
  { value: 'logo_club', label: 'Logo club', accept: 'image/png', formatHint: 'PNG uniquement, max 200x200px' },
  { value: 'logo_nation', label: 'Logo nation', accept: 'image/png', formatHint: 'PNG uniquement, max 200x200px' }
]

// Fetch image types config on mount
onMounted(async () => {
  try {
    const config = await api.get<Record<string, ImageTypeConfig>>('/admin/operations/images/types')
    imageTypesConfig.value = config
  } catch {
    console.warn('Failed to load image types config, using fallback')
  }
})

// Computed
const imageTypes = computed(() => {
  return imageTypesFallback.map(fallback => {
    const apiConfig = imageTypesConfig.value[fallback.value]
    return {
      value: fallback.value,
      label: apiConfig?.label || fallback.label,
      accept: apiConfig?.accept || fallback.accept,
      formatHint: apiConfig?.formatHint || fallback.formatHint
    }
  })
})

const currentConfig = computed(() => {
  return imageTypes.value.find(t => t.value === selectedImageType.value)
})

const currentAccept = computed(() => {
  return currentConfig.value?.accept || 'image/jpeg'
})

const currentFormatHint = computed(() => {
  return currentConfig.value?.formatHint || ''
})

const needsCompetitionFields = computed(() => {
  return ['logo_competition', 'bandeau_competition', 'sponsor_competition'].includes(selectedImageType.value)
})

const needsClubField = computed(() => selectedImageType.value === 'logo_club')
const needsNationField = computed(() => selectedImageType.value === 'logo_nation')

const canUpload = computed(() => {
  if (!selectedFile.value) return false
  if (needsCompetitionFields.value && (!codeCompetition.value.trim() || !saison.value.trim())) return false
  if (needsClubField.value && !selectedClubNumero.value) return false
  if (needsNationField.value && !codeNation.value.trim()) return false
  return true
})

const canRename = computed(() => {
  return currentName.value.trim() && newName.value.trim() && currentName.value.trim() !== newName.value.trim()
})

// Methods
const onFileSelected = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    if (file) {
      selectedFile.value = file
    }
  }
}

const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const clearClubSearch = () => {
  clubSearch.value = ''
  clubSearchResults.value = []
  clubSearchOpen.value = false
  selectedClubNumero.value = ''
}

const resetForm = () => {
  clearFile()
  codeCompetition.value = ''
  saison.value = ''
  clearClubSearch()
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

const handleGlobalClick = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (clubSearchRef.value && !clubSearchRef.value.contains(target)) {
    clubSearchOpen.value = false
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
  }
  return formData
}

const doUpload = async (overwrite = false) => {
  loading.value = true
  try {
    const result = await api.upload<{ message: string; filename: string; resized?: boolean }>('/admin/operations/images/upload', buildFormData(overwrite), [409])

    const description = result.resized
      ? t('operations.images.success_upload_resized', { filename: result.filename })
      : t('operations.images.success_upload', { filename: result.filename })

    toast.add({ title: t('common.success'), description, color: 'success', duration: 5000 })
    imageVersionStore.bump(selectedImageType.value as BumpableImageType)
    resetForm()
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
      toast.add({
        title: t('common.error'),
        description: t('operations.images.error_upload'),
        color: 'error',
        duration: 3000
      })
    }
  }
}

const confirmOverwrite = async () => {
  confirmOverwriteModal.value = false
  try {
    await doUpload(true)
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.images.error_upload'),
      color: 'error',
      duration: 3000
    })
  }
}

const openRenameModal = () => {
  if (!canRename.value) return
  confirmRenameModal.value = true
}

const confirmRename = async () => {
  if (!canRename.value) return

  loading.value = true
  try {
    await api.post('/admin/operations/images/rename', {
      imageType: selectedImageType.value,
      currentName: currentName.value.trim(),
      newName: newName.value.trim()
    })
    toast.add({
      title: t('common.success'),
      description: t('operations.images.success_rename'),
      color: 'success',
      duration: 3000
    })
    confirmRenameModal.value = false
    currentName.value = ''
    newName.value = ''
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.images.error_rename'),
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
    <!-- Image type selector -->
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
              : 'bg-header-100 text-header-700 hover:bg-header-200'
          ]"
          @click="selectedImageType = type.value as ImageType; resetForm()"
        >
          {{ type.label }}
        </button>
      </div>
      <!-- Format hint for selected type -->
      <p v-if="currentFormatHint" class="mt-2 text-sm text-primary-600 flex items-center gap-1">
        <UIcon name="i-heroicons-information-circle" class="w-4 h-4" />
        {{ currentFormatHint }}
      </p>
    </div>

    <!-- Upload section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-4">
        {{ t('operations.images.upload') }}
      </h2>

      <div class="max-w-xl space-y-4">
        <!-- Dynamic fields based on image type -->
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
                  <span class="font-mono text-xs text-header-500 bg-header-100 px-1.5 py-0.5 rounded">{{ club.numero }}</span>
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
            <button
              v-if="selectedFile"
              class="px-3 py-2 text-header-600 hover:text-header-900"
              @click="clearFile"
            >
              <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
            </button>
          </div>
          <p v-if="selectedFile" class="mt-1 text-sm text-header-500">
            {{ selectedFile.name }} ({{ Math.round(selectedFile.size / 1024) }} Ko)
          </p>
        </div>

        <!-- Upload button -->
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

    <!-- Rename section -->
    <section>
      <h2 class="text-lg font-semibold text-header-900 mb-4">
        {{ t('operations.images.rename') }}
      </h2>

      <div class="max-w-xl space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.current_name') }}
            </label>
            <input
              v-model="currentName"
              type="text"
              placeholder="ex: L-N1H-2024-2025.jpg"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('operations.images.new_name') }}
            </label>
            <input
              v-model="newName"
              type="text"
              placeholder="ex: L-N1M-2024-2025.jpg"
              class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </div>

        <button
          :disabled="!canRename || loading"
          class="px-4 py-2 bg-warning-600 text-white rounded-lg hover:bg-warning-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="openRenameModal"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('operations.images.rename_button') }}
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

    <!-- Confirm rename modal -->
    <AdminConfirmModal
      :open="confirmRenameModal"
      :title="t('operations.images.confirm_rename')"
      :message="t('operations.images.confirm_rename_message')"
      :item-name="`${currentName} => ${newName}`"
      :confirm-text="t('operations.images.rename_button')"
      :cancel-text="t('common.cancel')"
      :loading="loading"
      variant="warning"
      @close="confirmRenameModal = false"
      @confirm="confirmRename"
    />
  </div>
</template>
