<script setup lang="ts">
interface Props {
  loading?: boolean
  empty?: boolean
  loadingText?: string
  emptyText?: string
}

withDefaults(defineProps<Props>(), {
  loading: false,
  empty: false,
  loadingText: 'Chargement...',
  emptyText: 'Aucun élément trouvé'
})
</script>

<template>
  <div class="lg:hidden">
    <!-- Loading state -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
      {{ loadingText }}
    </div>

    <!-- Empty state -->
    <div v-else-if="empty" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ emptyText }}
    </div>

    <!-- Cards -->
    <div v-else class="space-y-3">
      <slot />
    </div>
  </div>
</template>
