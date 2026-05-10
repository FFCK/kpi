<script setup lang="ts">
interface CompetitionOption {
  code: string
  libelle: string
  season: string
}

const props = defineProps<{
  modelValue: string[]
  season?: string
  placeholder?: string
  fetchUrl: string
  authToken?: string
  mandateId?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void
}>()

const query = ref('')
const results = ref<CompetitionOption[]>([])
const isLoading = ref(false)
const isOpen = ref(false)
const containerRef = ref<HTMLElement>()
const inputRef = ref<HTMLInputElement>()

let debounceTimeout: ReturnType<typeof setTimeout> | null = null

function handleClickOutside(event: MouseEvent) {
  if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => { document.addEventListener('click', handleClickOutside) })
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (debounceTimeout) clearTimeout(debounceTimeout)
})

async function performSearch() {
  if (query.value.length < 2) {
    results.value = []
    isOpen.value = false
    return
  }

  isLoading.value = true
  try {
    const params = new URLSearchParams({ query: query.value })
    if (props.season) params.set('season', props.season)

    const headers: Record<string, string> = {}
    if (props.authToken) headers['Authorization'] = `Bearer ${props.authToken}`
    if (props.mandateId) headers['X-Active-Mandate'] = String(props.mandateId)

    const res = await fetch(`${props.fetchUrl}?${params}`, { headers })
    if (!res.ok) throw new Error('fetch failed')
    const data: CompetitionOption[] = await res.json()
    results.value = data.filter(r => !props.modelValue.includes(r.code))
    isOpen.value = results.value.length > 0
  } catch {
    results.value = []
    isOpen.value = false
  } finally {
    isLoading.value = false
  }
}

function onInput() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(performSearch, 300)
}

function select(option: CompetitionOption) {
  emit('update:modelValue', [...props.modelValue, option.code])
  query.value = ''
  results.value = []
  isOpen.value = false
  inputRef.value?.focus()
}

function remove(code: string) {
  emit('update:modelValue', props.modelValue.filter(c => c !== code))
}

function clear() {
  emit('update:modelValue', [])
  query.value = ''
  results.value = []
  isOpen.value = false
}

watch(() => props.season, () => {
  if (query.value.length >= 2) performSearch()
})
</script>

<template>
  <div ref="containerRef" class="relative">
    <div class="flex flex-wrap items-center gap-1 px-2 py-1.5 min-w-45 max-w-[320px] min-h-9.5 text-sm border border-header-300 rounded-lg bg-white focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500">
      <!-- Selected badges -->
      <span
        v-for="code in modelValue"
        :key="code"
        class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-primary-100 text-primary-800 text-xs rounded-full"
      >
        {{ code }}
        <button type="button" class="hover:text-primary-600" @click="remove(code)">
          <UIcon name="i-heroicons-x-mark" class="w-3 h-3" />
        </button>
      </span>

      <!-- Input -->
      <input
        ref="inputRef"
        v-model="query"
        type="text"
        class="flex-1 min-w-20 outline-none bg-transparent text-header-900 placeholder-header-400 text-sm"
        :placeholder="modelValue.length === 0 ? (placeholder ?? '') : ''"
        @input="onInput"
        @keydown.escape="isOpen = false"
      >

      <!-- Clear all -->
      <button
        v-if="modelValue.length > 0"
        type="button"
        class="ml-auto text-header-400 hover:text-header-600"
        @click="clear"
      >
        <UIcon name="i-heroicons-x-circle" class="w-4 h-4" />
      </button>
    </div>

    <!-- Dropdown -->
    <div
      v-if="isOpen || isLoading"
      class="absolute z-50 mt-1 w-full min-w-55 max-w-90 bg-white border border-header-200 rounded-lg shadow-lg overflow-hidden"
    >
      <div v-if="isLoading" class="flex justify-center py-3">
        <UIcon name="i-heroicons-arrow-path" class="w-4 h-4 animate-spin text-header-400" />
      </div>
      <div v-else-if="results.length === 0 && query.length >= 2" class="px-3 py-2 text-sm text-header-500">
        {{ $t('common.no_results') }}
      </div>
      <ul v-else class="max-h-48 overflow-y-auto divide-y divide-header-100">
        <li
          v-for="option in results"
          :key="option.code"
          class="px-3 py-2 text-sm cursor-pointer hover:bg-primary-50"
          @mousedown.prevent="select(option)"
        >
          <span class="font-medium text-header-800">{{ option.code }}</span>
          <span class="text-header-500 ml-1">– {{ option.libelle }}</span>
          <span v-if="!season" class="text-header-400 text-xs ml-1">({{ option.season }})</span>
        </li>
      </ul>
    </div>
  </div>
</template>
