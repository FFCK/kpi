<script setup lang="ts">
const modelValue = defineModel<number>({ required: true })

const numbers = Array.from({ length: 22 }, (_, i) => i)
</script>

<template>
  <div class="flex flex-col gap-2">
    <div class="grid grid-cols-11 gap-1">
      <button
        v-for="n in numbers"
        :key="n"
        type="button"
        class="px-2 py-1 text-xs font-medium rounded border transition-colors"
        :class="modelValue === n
          ? 'bg-primary-600 text-white border-primary-600'
          : 'bg-white text-header-700 border-header-300 hover:bg-header-100'"
        @click="modelValue = n"
      >
        {{ n }}
      </button>
    </div>
  </div>
</template>
