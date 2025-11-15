<template>
  <span class="inline-flex items-center justify-center w-6 h-6 rounded">
    <!-- Goal -->
    <span v-if="type === 'B'" class="text-xl" title="Goal">⚽</span>

    <!-- Green Card -->
    <span
      v-else-if="type === 'V'"
      class="w-4 h-6 bg-green-500 rounded-sm"
      title="Green Card"
    ></span>

    <!-- Yellow Card -->
    <span
      v-else-if="type === 'J'"
      class="w-4 h-6 bg-yellow-500 rounded-sm"
      title="Yellow Card"
    ></span>

    <!-- Red Card -->
    <span
      v-else-if="type === 'R'"
      class="w-4 h-6 bg-red-500 rounded-sm"
      title="Red Card"
    ></span>

    <!-- Definitive Red Card -->
    <span
      v-else-if="type === 'D'"
      class="w-4 h-6 bg-red-800 rounded-sm border border-red-600"
      title="Definitive Red Card"
    ></span>
  </span>
</template>

<script setup lang="ts">
defineProps<{
  type: 'B' | 'V' | 'J' | 'R' | 'D'
}>()
</script>
