<script setup lang="ts">
import type { CompetitionSearchResult } from '~/types/competitions'

const props = defineProps<{
  modelValue: CompetitionSearchResult | null
  currentSeasonCode: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: CompetitionSearchResult | null): void
  (e: 'selected', value: CompetitionSearchResult): void
}>()

const { t } = useI18n()
const competitionsApi = useCompetitionsApi()

// Local state
const searchQuery = ref('')
const results = ref<CompetitionSearchResult[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement>()
const inputRef = ref<HTMLInputElement>()

// Debounce timeout
let debounceTimeout: ReturnType<typeof setTimeout> | null = null

// Expose focusInput method for parent component
const focusInput = () => {
  inputRef.value?.focus()
}

defineExpose({
  focusInput
})

// Format competition label for display
function formatLabel(comp: CompetitionSearchResult): string {
  const parts = [comp.code, comp.libelle]
  if (comp.soustitre) {
    parts.push(`(${comp.soustitre})`)
  }
  return parts.join(' - ')
}

// Search function
async function performSearch() {
  if (searchQuery.value.length < 2) {
    results.value = []
    isOpen.value = false
    return
  }

  isLoading.value = true
  try {
    results.value = await competitionsApi.searchPreviousSeasons(
      searchQuery.value,
      props.currentSeasonCode,
      20
    )
    isOpen.value = true
  } catch (error) {
    console.error('Autocomplete search error:', error)
    results.value = []
  } finally {
    isLoading.value = false
  }
}

// Debounced search
function debouncedSearch() {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
  debounceTimeout = setTimeout(() => {
    performSearch()
  }, 300)
}

// Watch search query and trigger debounced search
watch(searchQuery, () => {
  debouncedSearch()
})

// Handle selection
function selectCompetition(comp: CompetitionSearchResult) {
  emit('update:modelValue', comp)
  emit('selected', comp)
  // Clear search query to avoid triggering new searches
  searchQuery.value = ''
  results.value = []
  isOpen.value = false
}

// Clear selection
function clearSelection() {
  emit('update:modelValue', null)
  searchQuery.value = ''
  results.value = []
  isOpen.value = false
}

// Handle click outside to close dropdown
function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

// Clear search query when modelValue is cleared externally
watch(
  () => props.modelValue,
  (newValue) => {
    if (!newValue) {
      searchQuery.value = ''
      results.value = []
    }
  }
)

// Setup click outside listener
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
})
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <!-- Search input -->
    <div class="relative">
      <input
        ref="inputRef"
        v-model="searchQuery"
        type="text"
        :disabled="disabled"
        :placeholder="t('competitions.search_previous_placeholder')"
        class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
        @focus="isOpen = results.length > 0"
      >

      <!-- Loading spinner or clear button -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <div
          v-if="isLoading"
          class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"
        />
        <button
          v-else-if="searchQuery && !disabled"
          type="button"
          class="text-gray-400 hover:text-gray-600"
          @click="clearSelection"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Dropdown results -->
    <div
      v-if="isOpen && !disabled"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <!-- Min 2 chars message -->
      <div
        v-if="searchQuery.length < 2"
        class="px-4 py-3 text-sm text-gray-500 text-center"
      >
        {{ t('competitions.min_2_chars') }}
      </div>

      <!-- No results -->
      <div
        v-else-if="!isLoading && results.length === 0"
        class="px-4 py-3 text-sm text-gray-500 text-center"
      >
        {{ t('competitions.no_results') }}
      </div>

      <!-- Results list -->
      <div v-else-if="results.length > 0">
        <button
          v-for="comp in results"
          :key="`${comp.code}-${comp.latestSeasonCode}`"
          type="button"
          class="w-full px-4 py-3 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none border-b border-gray-100 last:border-b-0 transition-colors"
          @click="selectCompetition(comp)"
        >
          <div class="text-sm font-medium text-gray-900">
            {{ formatLabel(comp) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ t('competitions.latest_season') }}: {{ comp.latestSeasonCode }}
          </div>
        </button>
      </div>
    </div>
  </div>
</template>
