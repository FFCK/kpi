<script setup lang="ts">
interface Props {
  active: boolean
  activeIcon?: string
  inactiveIcon?: string
  activeColor?: 'success' | 'primary' | 'danger' | 'warning'
  activeTitle?: string
  inactiveTitle?: string
  size?: 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), {
  active: false,
  activeIcon: 'heroicons:check-circle-solid',
  inactiveIcon: 'heroicons:x-circle-solid',
  activeColor: 'success',
  activeTitle: '',
  inactiveTitle: '',
  size: 'md'
})

const emit = defineEmits<{
  (e: 'toggle'): void
}>()

const activeColorClasses = {
  success: 'text-success-500 hover:text-success-600',
  primary: 'text-primary-500 hover:text-primary-600',
  danger: 'text-danger-500 hover:text-danger-600',
  warning: 'text-warning-500 hover:text-warning-600'
}

const sizeClasses = {
  sm: 'w-5 h-5',
  md: 'w-6 h-6',
  lg: 'w-8 h-8'
}
</script>

<template>
  <button
    :class="[
      'p-1 transition-colors',
      active ? activeColorClasses[activeColor] : 'text-header-400 hover:text-header-500'
    ]"
    :title="active ? activeTitle : inactiveTitle"
    @click="emit('toggle')"
  >
    <UIcon
      :name="active ? activeIcon : inactiveIcon"
      :class="sizeClasses[size]"
    />
  </button>
</template>
