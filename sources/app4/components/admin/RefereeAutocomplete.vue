<script setup lang="ts">
interface RefereeResult {
  type: string
  matric?: string
  nom?: string
  prenom?: string
  libelle?: string
  arbitre?: string
  label: string
  value?: string
}

const props = defineProps<{
  modelValue: string
  matric: number
  journeeId: number | null
  placeholder?: string
  disabled?: boolean
  compact?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'update:matric', value: number): void
  (e: 'confirm'): void
  (e: 'cancel'): void
}>()

const { t, locale } = useI18n()
const api = useApi()

const searchQuery = ref('')
const results = ref<RefereeResult[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement>()
const inputRef = ref<HTMLInputElement>()

let debounceTimeout: ReturnType<typeof setTimeout> | null = null
let skipNextSearch = false
// Track if a selection was just made (to prevent blur from cancelling)
let justSelected = false

// Initialize search query from modelValue
watch(
  () => props.modelValue,
  (newVal) => {
    if (!isOpen.value) {
      searchQuery.value = newVal || ''
    }
  },
  { immediate: true },
)

async function performSearch() {
  if (searchQuery.value.length < 2 || !props.journeeId) {
    results.value = []
    isOpen.value = false
    return
  }

  isLoading.value = true
  try {
    const data = await api.get<RefereeResult[]>(
      '/admin/games/autocomplete/referees',
      { q: searchQuery.value, journeeId: props.journeeId, lang: locale.value },
    )
    results.value = data || []
    isOpen.value = true
  }
  catch {
    results.value = []
  }
  finally {
    isLoading.value = false
  }
}

function debouncedSearch() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => performSearch(), 300)
}

watch(searchQuery, () => {
  if (skipNextSearch) {
    skipNextSearch = false
    return
  }
  // When user types, clear matric (free text = non-nominative)
  emit('update:matric', 0)
  debouncedSearch()
})

function selectItem(item: RefereeResult) {
  justSelected = true
  skipNextSearch = true
  const value = item.value || item.label
  searchQuery.value = value
  emit('update:modelValue', value)
  emit('update:matric', item.matric ? parseInt(String(item.matric)) || 0 : 0)
  results.value = []
  isOpen.value = false
  emit('confirm')
}

function clearSelection() {
  justSelected = true
  searchQuery.value = ''
  emit('update:modelValue', '')
  emit('update:matric', 0)
  results.value = []
  isOpen.value = false
  emit('confirm')
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    e.preventDefault()
    isOpen.value = false
    results.value = []
    searchQuery.value = props.modelValue || ''
    inputRef.value?.blur()
    // In compact (inline) mode, cancel the edit
    if (props.compact) {
      emit('cancel')
    }
  }
  else if (e.key === 'Enter') {
    e.preventDefault()
    isOpen.value = false
    results.value = []
    // In compact mode: Enter commits free text
    if (props.compact) {
      justSelected = true
      const current = searchQuery.value.trim()
      if (current !== props.modelValue) {
        emit('update:modelValue', current)
        emit('update:matric', 0)
      }
      emit('confirm')
      inputRef.value?.blur()
    }
    else {
      // Form mode: commit free text
      const current = searchQuery.value.trim()
      if (current !== props.modelValue) {
        emit('update:modelValue', current)
        emit('update:matric', 0)
      }
    }
  }
}

function handleBlur() {
  // Delay to allow click on dropdown item
  setTimeout(() => {
    if (justSelected) {
      justSelected = false
      return
    }
    isOpen.value = false
    results.value = []
    if (props.compact) {
      // In inline mode: blur = cancel (restore original value)
      searchQuery.value = props.modelValue || ''
      emit('cancel')
    }
    else {
      // In form mode: blur = commit free text
      const current = searchQuery.value.trim()
      if (current !== props.modelValue) {
        emit('update:modelValue', current)
        emit('update:matric', 0)
      }
    }
  }, 200)
}

// Auto-focus and select on mount (for inline mode)
onMounted(() => {
  if (props.compact && inputRef.value) {
    inputRef.value.focus()
    inputRef.value.select()
  }
})

onUnmounted(() => {
  if (debounceTimeout) clearTimeout(debounceTimeout)
})
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <div class="relative">
      <input
        ref="inputRef"
        v-model="searchQuery"
        type="text"
        maxlength="60"
        :disabled="disabled"
        :placeholder="placeholder || t('games.referee_placeholder')"
        :class="[
          compact
            ? 'w-full px-1 py-0 text-xs border border-primary-400 rounded bg-white'
            : 'w-full px-3 py-2 text-sm border border-header-300 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
          disabled ? 'bg-header-100 cursor-not-allowed' : '',
        ]"
        @focus="isOpen = results.length > 0"
        @keydown="handleKeydown"
        @blur="handleBlur"
      >
      <div v-if="!compact" class="absolute inset-y-0 right-0 flex items-center pr-2">
        <div
          v-if="isLoading"
          class="animate-spin h-4 w-4 border-2 border-primary-500 border-t-transparent rounded-full"
        />
        <button
          v-else-if="searchQuery && !disabled"
          type="button"
          class="text-header-400 hover:text-header-600"
          @mousedown.prevent="clearSelection"
        >
          <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
        </button>
      </div>
      <!-- Compact mode: clear button inline -->
      <button
        v-if="compact && searchQuery && !disabled"
        type="button"
        class="absolute right-0.5 top-1/2 -translate-y-1/2 text-header-400 hover:text-header-600"
        @mousedown.prevent="clearSelection"
      >
        <UIcon name="i-heroicons-x-mark" class="w-3 h-3" />
      </button>
    </div>

    <!-- Dropdown results -->
    <div
      v-if="isOpen && !disabled && results.length > 0"
      class="absolute z-50 w-64 mt-1 bg-white border border-header-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <template v-for="(item, idx) in results" :key="idx">
        <!-- Separator -->
        <div
          v-if="item.type === 'separator'"
          class="px-3 py-1 text-[10px] font-semibold text-header-400 bg-header-50 uppercase tracking-wider"
        >
          {{ item.label }}
        </div>
        <!-- Error -->
        <div
          v-else-if="item.type === 'error'"
          class="px-3 py-2 text-xs text-danger-500 text-center"
        >
          {{ item.label }}
        </div>
        <!-- Selectable item -->
        <button
          v-else
          type="button"
          class="w-full px-3 py-1.5 text-left hover:bg-primary-50 border-b border-header-50 transition-colors text-xs"
          @mousedown.prevent="selectItem(item)"
        >
          <span class="font-medium">{{ item.label }}</span>
        </button>
      </template>
    </div>
  </div>
</template>
