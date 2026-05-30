<script setup lang="ts">
import type { PlayerAutocomplete } from '~/types'

const props = defineProps<{
  modelValue: PlayerAutocomplete | null
  placeholder?: string
  disabled?: boolean
  filterClub?: string
  filterSexe?: string
  filterArbitre?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: PlayerAutocomplete | null): void
  (e: 'player-selected', value: PlayerAutocomplete): void
}>()

defineExpose({
  focus: () => inputRef.value?.focus(),
  getSearchQuery: () => searchQuery.value
})

const { t } = useI18n()
const api = useApi()

// Local state
const searchQuery = ref('')
const results = ref<PlayerAutocomplete[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement>()
const inputRef = ref<HTMLInputElement>()

// Debounce timeout
let debounceTimeout: ReturnType<typeof setTimeout> | null = null
let skipNextSearch = false

// Search function
async function performSearch() {
  if (searchQuery.value.length < 2) {
    results.value = []
    isOpen.value = false
    return
  }

  isLoading.value = true
  try {
    const params: Record<string, string> = { q: searchQuery.value }
    if (props.filterClub) params.club = props.filterClub
    if (props.filterSexe) params.sexe = props.filterSexe
    if (props.filterArbitre) params.arbitre = props.filterArbitre
    const data = await api.get<PlayerAutocomplete[]>(
      '/admin/operations/autocomplete/players',
      params
    )
    results.value = data || []
    isOpen.value = true
  } catch (error) {
    console.error('Player search error:', error)
    results.value = []
  } finally {
    isLoading.value = false
  }
}

// Debounced search
function debouncedSearch() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => performSearch(), 300)
}

// Watch search query
watch(searchQuery, () => {
  if (skipNextSearch) {
    skipNextSearch = false
    return
  }
  debouncedSearch()
})

// Re-search when filters change
watch(
  () => [props.filterClub, props.filterSexe, props.filterArbitre],
  () => { if (searchQuery.value.length >= 2) debouncedSearch() }
)

// Handle selection
function selectPlayer(player: PlayerAutocomplete) {
  emit('update:modelValue', player)
  emit('player-selected', player)
  skipNextSearch = true
  searchQuery.value = `${formatNom(player.nom)} ${formatPrenom(player.prenom)}`
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

// Handle click outside
function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

// Watch external clear
watch(
  () => props.modelValue,
  (newValue) => {
    if (!newValue) {
      searchQuery.value = ''
      results.value = []
    }
  }
)

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (debounceTimeout) clearTimeout(debounceTimeout)
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
        :placeholder="placeholder || t('common.search_player_placeholder')"
        class="w-full px-3 py-2 pr-10 border border-header-300 rounded-lg bg-white text-header-900 placeholder-header-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-header-100 disabled:cursor-not-allowed"
        @focus="isOpen = results.length > 0"
      >
      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <div
          v-if="isLoading"
          class="animate-spin h-4 w-4 border-2 border-primary-500 border-t-transparent rounded-full"
        />
        <button
          v-else-if="searchQuery && !disabled"
          type="button"
          class="text-header-400 hover:text-header-600"
          @click="clearSelection"
        >
          <UIcon name="i-heroicons-x-mark" class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Dropdown results -->
    <div
      v-if="isOpen && !disabled"
      class="absolute z-50 w-full mt-1 bg-white border border-header-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <div
        v-if="!isLoading && results.length === 0 && searchQuery.length >= 2"
        class="px-4 py-3 text-sm text-header-500 text-center"
      >
        {{ t('common.no_results') }}
      </div>

      <button
        v-for="player in results"
        :key="player.matric"
        type="button"
        class="w-full px-3 py-2 text-left hover:bg-primary-50 border-b border-header-100 last:border-b-0 transition-colors"
        @click="selectPlayer(player)"
      >
        <div class="font-medium text-sm">{{ formatNom(player.nom) }} {{ formatPrenom(player.prenom) }}</div>
        <div class="text-xs text-header-500 font-mono flex items-center gap-2">
          <span>{{ player.icf ? `ICF-${player.icf}` : player.matric }}</span>
          <span v-if="player.club">&mdash; {{ player.club }}</span>
          <span v-if="player.arbitre" class="px-1 rounded bg-primary-100 text-primary-700 not-italic">{{ player.arbitre }}</span>
        </div>
      </button>
    </div>

    <!-- Selected player display -->
    <div v-if="modelValue" class="mt-2 p-3 bg-primary-50 border border-primary-200 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <span class="font-semibold text-header-900">{{ formatNom(modelValue.nom) }} {{ formatPrenom(modelValue.prenom) }}</span>
          <span class="text-sm text-header-500 ml-2">{{ modelValue.matric }}</span>
        </div>
        <button
          v-if="!disabled"
          type="button"
          class="text-header-400 hover:text-header-600"
          @click="clearSelection"
        >
          <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
        </button>
      </div>
      <div class="text-xs text-header-600 mt-1">
        {{ modelValue.club }}
      </div>
    </div>
  </div>
</template>
