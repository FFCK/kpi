<script setup lang="ts">
import { useAuthStore } from '~/stores/authStore'

const props = defineProps<{
  collapsed: boolean
  mobileOpen: boolean
}>()

const emit = defineEmits<{
  close: []
}>()

const { t } = useI18n()
const authStore = useAuthStore()
const route = useRoute()

// Menu items based on user profile
const menuItems = computed(() => {
  const profile = authStore.user?.profile ?? 99
  const items = []

  // Events - profile <= 2
  if (profile <= 2) {
    items.push({
      to: '/events',
      icon: 'i-heroicons-calendar-days',
      label: t('menu.events')
    })
  }

  // Documents - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/documents',
      icon: 'i-heroicons-document-text',
      label: t('menu.documents')
    })
  }

  // Statistics - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/statistics',
      icon: 'i-heroicons-chart-bar',
      label: t('menu.statistics')
    })
  }

  // Operations - profile == 1 only
  if (profile === 1) {
    items.push({
      to: '/operations',
      icon: 'i-heroicons-cog-6-tooth',
      label: t('menu.operations')
    })
  }

  return items
})

const isActive = (path: string) => route.path === path
</script>

<template>
  <aside
    :class="[
      'fixed lg:static inset-y-0 left-0 z-50',
      'flex flex-col bg-gray-900 text-white transition-all duration-300',
      collapsed ? 'w-16' : 'w-64',
      mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800">
      <NuxtLink to="/" class="flex items-center gap-2">
        <span class="text-xl font-bold text-blue-400">KPI</span>
        <span v-if="!collapsed" class="text-sm text-gray-400">Admin</span>
      </NuxtLink>
      <button
        class="lg:hidden p-2 text-gray-400 hover:text-white"
        @click="emit('close')"
      >
        <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 overflow-y-auto">
      <ul class="space-y-1 px-2">
        <li v-for="item in menuItems" :key="item.to">
          <NuxtLink
            :to="item.to"
            :class="[
              'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors',
              isActive(item.to)
                ? 'bg-blue-600 text-white'
                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
          >
            <UIcon :name="item.icon" class="w-5 h-5 flex-shrink-0" />
            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
          </NuxtLink>
        </li>
      </ul>
    </nav>

    <!-- User info (bottom) -->
    <div class="p-4 border-t border-gray-800">
      <div v-if="!collapsed" class="text-sm text-gray-400">
        {{ authStore.user?.name }} {{ authStore.user?.firstname }}
      </div>
      <div v-if="!collapsed" class="text-xs text-gray-500">
        {{ t('profile') }} {{ authStore.user?.profile }}
      </div>
    </div>
  </aside>
</template>
