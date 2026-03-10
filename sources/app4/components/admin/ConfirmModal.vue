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
  if (isDanger.value) return 'text-danger-600'
  if (isWarning.value) return 'text-amber-600'
  return 'text-header-900'
})

const buttonClass = computed(() => {
  if (isDanger.value) return 'bg-danger-600 hover:bg-danger-700'
  if (isWarning.value) return 'bg-amber-600 hover:bg-amber-700'
  return 'bg-primary-600 hover:bg-primary-700'
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
        <div class="p-6 border-b border-header-200">
          <h3 :class="['text-lg font-semibold', titleClass]">
            {{ title }}
          </h3>
        </div>

        <!-- Body -->
        <div class="p-6">
          <p class="text-header-600">
            {{ message }}
          </p>
          <p v-if="itemName" class="mt-2 font-medium text-header-900">
            {{ itemName }}
          </p>
          <slot />
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 p-6 pt-4 border-t border-header-200 bg-header-50">
          <button
            type="button"
            class="px-4 py-2 text-header-700 border border-header-300 bg-white hover:bg-header-100 rounded-lg transition-colors"
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
