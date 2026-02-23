<script setup lang="ts">
import type { ImageType } from '~/types/operations'

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

// State
const loading = ref(false)
const selectedImageType = ref<ImageType>('logo_competition')
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const imageTypesConfig = ref<Record<string, ImageTypeConfig>>({})

// Form fields based on image type
const codeCompetition = ref('')
const saison = ref('')
const numeroClub = ref('')
const codeNation = ref('')

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
    // Use fallback if API fails
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
  if (needsClubField.value && !numeroClub.value.trim()) return false
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

const resetForm = () => {
  clearFile()
  codeCompetition.value = ''
  saison.value = ''
  numeroClub.value = ''
  codeNation.value = ''
}

const uploadImage = async () => {
  if (!canUpload.value || !selectedFile.value) return

  loading.value = true
  try {
    const formData = new FormData()
    formData.append('imageType', selectedImageType.value)
    formData.append('imageFile', selectedFile.value)

    if (needsCompetitionFields.value) {
      formData.append('codeCompetition', codeCompetition.value.trim().toUpperCase())
      formData.append('saison', saison.value.trim())
    } else if (needsClubField.value) {
      formData.append('numeroClub', numeroClub.value.trim())
    } else if (needsNationField.value) {
      formData.append('codeNation', codeNation.value.trim().toUpperCase())
    }

    const result = await api.upload<{ message: string; filename: string; resized?: boolean }>('/admin/operations/images/upload', formData)

    const description = result.resized
      ? t('operations.images.success_upload_resized', { filename: result.filename })
      : t('operations.images.success_upload', { filename: result.filename })

    toast.add({
      title: t('common.success'),
      description,
      color: 'success',
      duration: 5000
    })
    resetForm()
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('operations.images.error_upload'),
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
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
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('operations.images.type') }}
      </label>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="type in imageTypes"
          :key="type.value"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            selectedImageType === type.value
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
          @click="selectedImageType = type.value as ImageType; resetForm()"
        >
          {{ type.label }}
        </button>
      </div>
      <!-- Format hint for selected type -->
      <p v-if="currentFormatHint" class="mt-2 text-sm text-blue-600 flex items-center gap-1">
        <UIcon name="i-heroicons-information-circle" class="w-4 h-4" />
        {{ currentFormatHint }}
      </p>
    </div>

    <!-- Upload section -->
    <section>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.images.upload') }}
      </h2>

      <div class="max-w-xl space-y-4">
        <!-- Dynamic fields based on image type -->
        <template v-if="needsCompetitionFields">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('operations.images.code_competition') }}
              </label>
              <input
                v-model="codeCompetition"
                type="text"
                placeholder="ex: N1H"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('operations.images.season') }}
              </label>
              <input
                v-model="saison"
                type="text"
                placeholder="ex: 2024-2025"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>
        </template>

        <template v-else-if="needsClubField">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.images.club_number') }}
            </label>
            <input
              v-model="numeroClub"
              type="text"
              placeholder="ex: 0750001"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
          </div>
        </template>

        <template v-else-if="needsNationField">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.images.nation_code') }}
            </label>
            <input
              v-model="codeNation"
              type="text"
              placeholder="ex: FRA"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
            >
          </div>
        </template>

        <!-- File input -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ t('operations.images.file') }}
          </label>
          <div class="flex items-center gap-3">
            <input
              ref="fileInput"
              type="file"
              :accept="currentAccept"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              @change="onFileSelected"
            >
            <button
              v-if="selectedFile"
              class="px-3 py-2 text-gray-600 hover:text-gray-900"
              @click="clearFile"
            >
              <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
            </button>
          </div>
          <p v-if="selectedFile" class="mt-1 text-sm text-gray-500">
            {{ selectedFile.name }} ({{ Math.round(selectedFile.size / 1024) }} Ko)
          </p>
        </div>

        <!-- Upload button -->
        <button
          :disabled="!canUpload || loading"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
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
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ t('operations.images.rename') }}
      </h2>

      <div class="max-w-xl space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.images.current_name') }}
            </label>
            <input
              v-model="currentName"
              type="text"
              placeholder="ex: L-N1H-2024-2025.jpg"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('operations.images.new_name') }}
            </label>
            <input
              v-model="newName"
              type="text"
              placeholder="ex: L-N1M-2024-2025.jpg"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
          </div>
        </div>

        <button
          :disabled="!canRename || loading"
          class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          @click="openRenameModal"
        >
          <UIcon v-if="loading" name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin" />
          {{ t('operations.images.rename_button') }}
        </button>
      </div>
    </section>

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
