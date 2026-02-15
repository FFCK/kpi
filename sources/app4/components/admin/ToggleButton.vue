<script setup lang="ts">
interface Props {
  active: boolean
  activeIcon?: string
  inactiveIcon?: string
  activeColor?: 'green' | 'blue' | 'red' | 'yellow'
  activeTitle?: string
  inactiveTitle?: string
  size?: 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), {
  active: false,
  activeIcon: 'heroicons:check-circle-solid',
  inactiveIcon: 'heroicons:x-circle-solid',
  activeColor: 'green',
  activeTitle: '',
  inactiveTitle: '',
  size: 'md'
})

const emit = defineEmits<{
  (e: 'toggle'): void
}>()

const activeColorClasses = {
  green: 'text-green-600 hover:text-green-700',
  blue: 'text-blue-600 hover:text-blue-700',
  red: 'text-red-600 hover:text-red-700',
  yellow: 'text-yellow-600 hover:text-yellow-700'
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
      active ? activeColorClasses[activeColor] : 'text-gray-400 hover:text-gray-500'
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
