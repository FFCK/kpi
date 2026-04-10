<script setup lang="ts">
interface Props {
  selected?: boolean
  showCheckbox?: boolean
  checked?: boolean
}

withDefaults(defineProps<Props>(), {
  selected: false,
  showCheckbox: false,
  checked: false
})

const emit = defineEmits<{
  (e: 'toggle-select'): void
}>()
</script>

<template>
  <div
    class="bg-white rounded-lg shadow overflow-hidden border border-header-200"
    :class="{ 'ring-2 ring-primary-500': selected }"
  >
    <!-- Card header -->
    <div class="flex items-center justify-between px-4 py-3 bg-primary-50 border-b border-primary-100">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <input
          v-if="showCheckbox"
          :checked="checked"
          type="checkbox"
          class="w-5 h-5 rounded border-header-300 text-primary-600 focus:ring-2 focus:ring-primary-500 cursor-pointer flex-shrink-0"
          @change="emit('toggle-select')"
        >
        <slot name="header" />
      </div>
      <slot name="header-right" />
    </div>

    <!-- Card content -->
    <div class="px-4 py-3 space-y-3">
      <slot />
    </div>

    <!-- Card footer -->
    <div
      v-if="$slots.footer || $slots['footer-left'] || $slots['footer-right']"
      class="px-4 py-3 bg-primary-50 border-t border-primary-100 flex items-center justify-between gap-3"
    >
      <div class="flex items-center gap-3">
        <slot name="footer-left" />
      </div>
      <div class="flex items-center gap-2">
        <slot name="footer-right" />
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>
