<script setup lang="ts">
interface Props {
  open: boolean
  title: string
  message: string
  itemName?: string
  confirmText?: string
  cancelText?: string
  loading?: boolean
  danger?: boolean
}

withDefaults(defineProps<Props>(), {
  open: false,
  itemName: '',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  loading: false,
  danger: true
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'confirm'): void
}>()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')" />

      <!-- Modal content -->
      <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
          <h3 :class="['text-lg font-semibold', danger ? 'text-red-600' : 'text-gray-900']">
            {{ title }}
          </h3>
        </div>

        <!-- Body -->
        <div class="p-6">
          <p class="text-gray-600">
            {{ message }}
          </p>
          <p v-if="itemName" class="mt-2 font-medium text-gray-900">
            {{ itemName }}
          </p>
          <slot />
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 p-6 pt-4 border-t border-gray-200 bg-gray-50">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 rounded-lg transition-colors"
            @click="emit('close')"
          >
            {{ cancelText }}
          </button>
          <button
            type="button"
            :class="[
              'px-4 py-2 text-white rounded-lg transition-colors disabled:opacity-50',
              danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'
            ]"
            :disabled="loading"
            @click="emit('confirm')"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
              {{ confirmText }}
            </span>
            <span v-else>{{ confirmText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
