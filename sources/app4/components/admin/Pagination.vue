<script setup lang="ts">
interface Props {
  page: number
  totalPages: number
  total: number
  limit: number
  showingText?: string
  itemsPerPageText?: string
  limitOptions?: number[]
}

const props = withDefaults(defineProps<Props>(), {
  showingText: 'Affichage de {from} à {to} sur {total}',
  itemsPerPageText: 'Éléments par page',
  limitOptions: () => [10, 20, 50, 100]
})

const emit = defineEmits<{
  (e: 'update:page', value: number): void
  (e: 'update:limit', value: number): void
}>()

const paginationFrom = computed(() => ((props.page - 1) * props.limit) + 1)
const paginationTo = computed(() => Math.min(props.page * props.limit, props.total))

const showingTextFormatted = computed(() => {
  return props.showingText
    .replace('{from}', String(paginationFrom.value))
    .replace('{to}', String(paginationTo.value))
    .replace('{total}', String(props.total))
})

const localLimit = computed({
  get: () => props.limit,
  set: (value: number) => emit('update:limit', value)
})
</script>

<template>
  <div class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
    <div class="text-sm text-gray-500">
      <span v-if="total > 0">
        {{ showingTextFormatted }}
      </span>
    </div>

    <div class="flex items-center gap-4">
      <!-- Items per page -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">{{ itemsPerPageText }}</span>
        <select
          v-model.number="localLimit"
          class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
        >
          <option v-for="opt in limitOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
      </div>

      <!-- Page navigation -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page <= 1"
          @click="emit('update:page', page - 1)"
        >
          <UIcon name="heroicons:chevron-left" class="w-5 h-5" />
        </button>
        <span class="text-sm text-gray-700 px-2 min-w-[4rem] text-center">
          {{ page }} / {{ totalPages || 1 }}
        </span>
        <button
          type="button"
          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page >= totalPages"
          @click="emit('update:page', page + 1)"
        >
          <UIcon name="heroicons:chevron-right" class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>
