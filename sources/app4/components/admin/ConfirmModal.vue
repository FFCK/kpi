<script setup lang="ts">
interface Props {
  open: boolean
  title: string
  message?: string
  itemName?: string
  confirmText?: string
  cancelText?: string
  loading?: boolean
  danger?: boolean
  variant?: 'danger' | 'warning' | 'info'
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  message: '',
  itemName: '',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  loading: false,
  danger: true,
  variant: undefined
})

const isDanger = computed(() => props.variant === 'danger' || (props.variant === undefined && props.danger))
const isWarning = computed(() => props.variant === 'warning')

const titleClass = computed(() => {
  if (isDanger.value) return 'text-red-600'
  if (isWarning.value) return 'text-amber-600'
  return 'text-gray-900'
})

const buttonClass = computed(() => {
  if (isDanger.value) return 'bg-red-600 hover:bg-red-700'
  if (isWarning.value) return 'bg-amber-600 hover:bg-amber-700'
  return 'bg-blue-600 hover:bg-blue-700'
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
          <h3 :class="['text-lg font-semibold', titleClass]">
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
              buttonClass
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
