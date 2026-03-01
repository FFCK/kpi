<script setup lang="ts">
interface AthleteResult {
  matric: number
  nom: string
  prenom: string
  club: string
  label: string
}

const props = defineProps<{
  modelValue: string
  placeholder?: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const api = useApi()

const searchQuery = ref('')
const results = ref<AthleteResult[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement>()
const inputRef = ref<HTMLInputElement>()

let debounceTimeout: ReturnType<typeof setTimeout> | null = null
let skipNextSearch = false
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
  if (searchQuery.value.length < 2) {
    results.value = []
    isOpen.value = false
    return
  }

  isLoading.value = true
  try {
    const data = await api.get<AthleteResult[]>(
      '/admin/athletes/search',
      { q: searchQuery.value },
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
  debouncedSearch()
})

function selectItem(item: AthleteResult) {
  justSelected = true
  skipNextSearch = true
  const prenom = item.prenom.charAt(0).toUpperCase() + item.prenom.slice(1).toLowerCase()
  const nom = item.nom.toUpperCase()
  const value = `${prenom} ${nom} (${item.matric})`
  searchQuery.value = value
  emit('update:modelValue', value)
  results.value = []
  isOpen.value = false
}

function clearSelection() {
  justSelected = true
  searchQuery.value = ''
  emit('update:modelValue', '')
  results.value = []
  isOpen.value = false
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    e.preventDefault()
    isOpen.value = false
    results.value = []
    searchQuery.value = props.modelValue || ''
    inputRef.value?.blur()
  }
  else if (e.key === 'Enter') {
    e.preventDefault()
    isOpen.value = false
    results.value = []
    // Commit free text
    const current = searchQuery.value.trim()
    if (current !== props.modelValue) {
      emit('update:modelValue', current)
    }
  }
}

function handleBlur() {
  setTimeout(() => {
    if (justSelected) {
      justSelected = false
      return
    }
    isOpen.value = false
    results.value = []
    // Commit free text on blur
    const current = searchQuery.value.trim()
    if (current !== props.modelValue) {
      emit('update:modelValue', current)
    }
  }, 200)
}

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
        maxlength="80"
        :disabled="disabled"
        :placeholder="placeholder"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        :class="disabled ? 'bg-gray-100 cursor-not-allowed' : ''"
        @focus="isOpen = results.length > 0"
        @keydown="handleKeydown"
        @blur="handleBlur"
      >
      <div class="absolute inset-y-0 right-0 flex items-center pr-2">
        <div
          v-if="isLoading"
          class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"
        />
        <button
          v-else-if="searchQuery && !disabled"
          type="button"
          class="text-gray-400 hover:text-gray-600"
          @mousedown.prevent="clearSelection"
        >
          <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Dropdown results -->
    <div
      v-if="isOpen && !disabled && results.length > 0"
      class="absolute z-50 w-72 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <button
        v-for="item in results"
        :key="item.matric"
        type="button"
        class="w-full px-3 py-1.5 text-left hover:bg-blue-50 border-b border-gray-50 transition-colors"
        @mousedown.prevent="selectItem(item)"
      >
        <div class="text-sm font-medium">{{ item.nom }} {{ item.prenom }}</div>
        <div class="text-xs text-gray-500">{{ item.matric }} - {{ item.club }}</div>
      </button>
    </div>
  </div>
</template>
