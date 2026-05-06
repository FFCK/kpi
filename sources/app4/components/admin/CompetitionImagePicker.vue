<script setup lang="ts">
type ImageKind = 'bandeau_competition' | 'logo_competition' | 'sponsor_competition'
type PickerMode = 'existing' | 'upload' | 'url'

interface ExistingImage {
  filename: string
  size: number
  modified: number
}

interface Props {
  modelValue: string       // current filename stored in DB
  imageKind: ImageKind
  competitionCode: string  // needed to build normalized filename on upload/import
  saison: string           // needed to build normalized filename
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), { disabled: false })
const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const { t } = useI18n()
const api = useApi()
const toast = useToast()

// Prefixes & format hints per kind
const kindMeta: Record<ImageKind, { prefix: string; accept: string; formatHint: string }> = {
  logo_competition:    { prefix: 'L-', accept: 'image/jpeg,image/png', formatHint: 'JPG/PNG, max 1000x1000px' },
  bandeau_competition: { prefix: 'B-', accept: 'image/jpeg,image/png', formatHint: 'JPG/PNG, max 2480x250px' },
  sponsor_competition: { prefix: 'S-', accept: 'image/jpeg,image/png', formatHint: 'JPG/PNG, max 2480x250px' },
}

const meta = computed(() => kindMeta[props.imageKind])

// Mode
const mode = ref<PickerMode>('existing')

// Existing images list
const existingImages = ref<ExistingImage[]>([])
const searchQuery = ref('')
const listLoading = ref(false)

// Upload
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const uploading = ref(false)

// URL import
const externalUrl = ref('')
const importing = ref(false)

const canUpload = computed(() =>
  !!selectedFile.value && !!props.competitionCode.trim() && !!props.saison.trim()
)

const canImportUrl = computed(() =>
  !!externalUrl.value.trim() && !!props.competitionCode.trim() && !!props.saison.trim()
)

// Preview URL (image served from PHP backend root /img/logo/)
const legacyBaseUrl = (useRuntimeConfig().public.legacyBaseUrl as string) || ''

const previewUrl = computed(() => {
  if (!props.modelValue) return null
  return `${legacyBaseUrl}/img/logo/${props.modelValue}`
})

const loadImages = async () => {
  if (searchQuery.value.length < 2) {
    existingImages.value = []
    return
  }
  listLoading.value = true
  try {
    const result = await api.get<ExistingImage[]>('/admin/operations/images/list', {
      imageType: props.imageKind,
      q: searchQuery.value
    })
    existingImages.value = result.slice(0, 5)
  } catch {
    toast.add({
      title: t('common.error'),
      description: t('competitions.images.error_list'),
      color: 'error',
      duration: 3000
    })
  } finally {
    listLoading.value = false
  }
}

watch(mode, (val) => {
  if (val === 'existing') {
    existingImages.value = []
    searchQuery.value = ''
  }
})

watch(searchQuery, () => {
  if (mode.value === 'existing') loadImages()
})

onMounted(() => {
  // no initial load — user must type to search
})

const selectExisting = (filename: string) => {
  emit('update:modelValue', filename)
}

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  selectedFile.value = target.files?.[0] ?? null
}

const clearFile = () => {
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const uploadFile = async () => {
  if (!canUpload.value || !selectedFile.value) return
  uploading.value = true
  try {
    const form = new FormData()
    form.append('imageType', props.imageKind)
    form.append('imageFile', selectedFile.value)
    form.append('codeCompetition', props.competitionCode.trim().toUpperCase())
    form.append('saison', props.saison.trim())

    const result = await api.upload<{ filename: string; resized?: boolean }>(
      '/admin/operations/images/upload',
      form
    )

    const desc = result.resized
      ? t('competitions.images.success_upload_resized', { filename: result.filename })
      : t('competitions.images.success_upload', { filename: result.filename })

    toast.add({ title: t('common.success'), description: desc, color: 'success', duration: 4000 })
    emit('update:modelValue', result.filename)
    clearFile()
    loadImages()
  } catch (err) {
    toast.add({
      title: t('common.error'),
      description: (err as { message?: string })?.message || t('competitions.images.error_upload'),
      color: 'error',
      duration: 4000
    })
  } finally {
    uploading.value = false
  }
}

const importFromUrl = async () => {
  if (!canImportUrl.value) return
  importing.value = true
  try {
    const result = await api.post<{ filename: string; resized?: boolean }>(
      '/admin/operations/images/import-url',
      {
        imageType: props.imageKind,
        url: externalUrl.value.trim(),
        codeCompetition: props.competitionCode.trim().toUpperCase(),
        saison: props.saison.trim()
      }
    )

    const desc = result.resized
      ? t('competitions.images.success_import_resized', { filename: result.filename })
      : t('competitions.images.success_import', { filename: result.filename })

    toast.add({ title: t('common.success'), description: desc, color: 'success', duration: 4000 })
    emit('update:modelValue', result.filename)
    externalUrl.value = ''
    loadImages()
  } catch (err) {
    toast.add({
      title: t('common.error'),
      description: (err as { message?: string })?.message || t('competitions.images.error_import'),
      color: 'error',
      duration: 4000
    })
  } finally {
    importing.value = false
  }
}

const removeImage = () => {
  emit('update:modelValue', '')
}


</script>

<template>
  <div class="border border-header-200 rounded-lg p-3 bg-white space-y-3">
    <!-- Current image preview -->
    <div v-if="modelValue" class="space-y-1">
      <img
        :src="previewUrl!"
        :alt="modelValue"
        class="max-h-16 w-full object-contain border border-header-200 rounded bg-header-50 p-1"
        @error="($event.target as HTMLImageElement).style.display='none'"
      >
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-header-600 font-mono truncate">{{ modelValue }}</span>
        <button
          v-if="!disabled"
          type="button"
          class="text-xs text-danger-600 hover:text-danger-800 shrink-0"
          @click="removeImage"
        >
          {{ t('competitions.images.remove') }}
        </button>
      </div>
    </div>

    <div v-if="!disabled">
      <!-- Mode tabs -->
      <div class="flex gap-1 border-b border-header-200 mb-3">
        <button
          v-for="m in (['existing', 'upload', 'url'] as PickerMode[])"
          :key="m"
          type="button"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded-t transition-colors',
            mode === m
              ? 'bg-white border border-b-white border-header-200 -mb-px text-primary-700'
              : 'text-header-500 hover:text-header-700'
          ]"
          @click="mode = m"
        >
          {{
            m === 'existing' ? t('competitions.images.mode_existing') :
            m === 'upload'   ? t('competitions.images.mode_upload') :
                               t('competitions.images.mode_url')
          }}
        </button>
      </div>

      <!-- Existing images -->
      <template v-if="mode === 'existing'">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('competitions.images.search_placeholder')"
          class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-1 focus:ring-primary-500 mb-2"
        >
        <div class="border border-header-200 rounded-lg overflow-hidden">
          <div v-if="searchQuery.length < 2" class="p-3 text-xs text-header-500 text-center italic">
            {{ t('competitions.images.search_min_chars') }}
          </div>
          <div v-else-if="listLoading" class="p-3 text-xs text-header-500 text-center">
            {{ t('competitions.images.loading') }}
          </div>
          <div v-else-if="existingImages.length === 0" class="p-3 text-xs text-header-500 text-center">
            {{ t('competitions.images.no_results') }}
          </div>
          <button
            v-for="img in existingImages"
            :key="img.filename"
            type="button"
            :class="[
              'w-full flex items-center gap-2 px-3 py-1.5 text-xs text-left hover:bg-header-50 transition-colors border-b border-header-100 last:border-b-0',
              modelValue === img.filename ? 'bg-primary-50 text-primary-700 font-medium' : 'text-header-700'
            ]"
            @click="selectExisting(img.filename)"
          >
            <UIcon
              v-if="modelValue === img.filename"
              name="heroicons:check-circle-solid"
              class="w-3.5 h-3.5 text-primary-600 shrink-0"
            />
            <UIcon
              v-else
              name="heroicons:document-solid"
              class="w-3.5 h-3.5 text-header-400 shrink-0"
            />
            <span class="font-mono truncate">{{ img.filename }}</span>
          </button>
        </div>
      </template>

      <!-- Upload -->
      <template v-else-if="mode === 'upload'">
        <p class="text-xs text-primary-600 mb-2">
          <UIcon name="i-heroicons-information-circle" class="w-3.5 h-3.5 inline" />
          {{ meta.formatHint }}
        </p>
        <div class="flex items-center gap-2">
          <input
            ref="fileInput"
            type="file"
            :accept="meta.accept"
            class="flex-1 text-xs file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
            @change="onFileChange"
          >
          <button
            v-if="selectedFile"
            type="button"
            class="text-header-500 hover:text-header-900"
            @click="clearFile"
          >
            <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
          </button>
        </div>
        <p v-if="selectedFile" class="text-xs text-header-500 mt-1">
          {{ selectedFile.name }} ({{ Math.round(selectedFile.size / 1024) }} Ko)
        </p>
        <button
          type="button"
          :disabled="!canUpload || uploading"
          class="mt-2 px-3 py-1.5 text-xs bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
          @click="uploadFile"
        >
          <UIcon v-if="uploading" name="i-heroicons-arrow-path" class="w-3.5 h-3.5 animate-spin" />
          <UIcon v-else name="i-heroicons-arrow-up-tray" class="w-3.5 h-3.5" />
          {{ t('competitions.images.mode_upload') }}
        </button>
        <p v-if="!props.competitionCode.trim() || !props.saison.trim()" class="text-xs text-warning-600 mt-1">
          {{ t('competitions.form.images_hint_create') }}
        </p>
      </template>

      <!-- URL import -->
      <template v-else>
        <div class="flex gap-2">
          <input
            v-model="externalUrl"
            type="url"
            :placeholder="t('competitions.images.url_placeholder')"
            class="flex-1 px-2 py-1.5 text-xs border border-header-300 rounded-lg focus:ring-1 focus:ring-primary-500"
          >
          <button
            type="button"
            :disabled="!canImportUrl || importing"
            class="px-3 py-1.5 text-xs bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 shrink-0"
            @click="importFromUrl"
          >
            <UIcon v-if="importing" name="i-heroicons-arrow-path" class="w-3.5 h-3.5 animate-spin" />
            {{ importing ? t('competitions.images.url_importing') : t('competitions.images.url_import') }}
          </button>
        </div>
        <p v-if="!props.competitionCode.trim() || !props.saison.trim()" class="text-xs text-warning-600 mt-1">
          {{ t('competitions.form.images_hint_create') }}
        </p>
      </template>
    </div>
  </div>
</template>
