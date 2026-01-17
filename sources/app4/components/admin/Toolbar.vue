<script setup lang="ts">
interface Props {
  searchPlaceholder?: string
  addLabel?: string
  showAdd?: boolean
  showBulkDelete?: boolean
  bulkDeleteLabel?: string
  selectedCount?: number
}

withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Rechercher',
  addLabel: 'Ajouter',
  showAdd: true,
  showBulkDelete: false,
  bulkDeleteLabel: 'Supprimer la sélection',
  selectedCount: 0
})

const search = defineModel<string>('search', { default: '' })

const emit = defineEmits<{
  (e: 'add'): void
  (e: 'bulk-delete'): void
}>()
</script>

<template>
  <div class="mb-4 flex flex-col sm:flex-row gap-4 justify-between">
    <!-- Left side: bulk actions -->
    <div class="flex items-center gap-2">
      <button
        v-if="showBulkDelete && selectedCount > 0"
        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-lg font-medium text-sm hover:bg-red-100 transition-colors"
        @click="emit('bulk-delete')"
      >
        <UIcon name="heroicons:trash" class="w-5 h-5" />
        {{ bulkDeleteLabel }} ({{ selectedCount }})
      </button>
      <slot name="left" />
    </div>

    <!-- Right side: search and add -->
    <div class="flex items-center gap-3">
      <slot name="before-search" />

      <div class="relative">
        <UIcon
          name="heroicons:magnifying-glass"
          class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
        />
        <input
          v-model="search"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <slot name="after-search" />

      <button
        v-if="showAdd"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition-colors"
        @click="emit('add')"
      >
        <UIcon name="heroicons:plus" class="w-5 h-5" />
        {{ addLabel }}
      </button>

      <slot name="right" />
    </div>
  </div>
</template>
