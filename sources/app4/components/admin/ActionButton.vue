<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  size?: 'sm' | 'md'
  icon?: string
  loading?: boolean
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  variant: 'secondary',
  size: 'md',
  icon: '',
  loading: false,
  disabled: false
})

const variantClasses = {
  primary: 'text-white bg-blue-600 border-blue-600 hover:bg-blue-700',
  secondary: 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50',
  danger: 'text-red-700 bg-white border-red-300 hover:bg-red-50',
  ghost: 'text-gray-600 bg-transparent border-transparent hover:bg-gray-100'
}

const sizeClasses = {
  sm: 'px-2 py-1 text-xs gap-1',
  md: 'px-3 py-2 text-sm gap-2'
}

const iconSizeClasses = {
  sm: 'w-3.5 h-3.5',
  md: 'w-4 h-4'
}
</script>

<template>
  <button
    :class="[
      'inline-flex items-center font-medium border rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
      variantClasses[variant],
      sizeClasses[size]
    ]"
    :disabled="disabled || loading"
  >
    <UIcon
      v-if="loading"
      name="heroicons:arrow-path"
      :class="['animate-spin', iconSizeClasses[size]]"
    />
    <UIcon
      v-else-if="icon"
      :name="icon"
      :class="iconSizeClasses[size]"
    />
    <slot />
  </button>
</template>
