<template>
  <header class="bg-gray-900 text-white shadow-md">
    <div class="flex items-center justify-between px-4 py-2">
      <NuxtLink to="/" class="flex items-center space-x-2">
        <img src="/img/logo_kp.png" width="30" height="30" alt="logo" class="inline-block align-middle" />
        <span class="font-bold text-lg">KPI App</span>
        <ClientOnly>
          <span :class="isOnline ? 'ml-2 text-green-400' : 'ml-2 text-red-500'">
            <UIcon name="i-heroicons-wifi" />
          </span>
          <template #fallback>
            <span class="ml-2 text-green-400">
              <UIcon name="i-heroicons-wifi" />
            </span>
          </template>
        </ClientOnly>
      </NuxtLink>
      <nav class="hidden md:flex space-x-4">
        <NuxtLink to="/" :class="isActive('/') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-1">
          <UIcon name="i-heroicons-home" />
          <span>{{ t('nav.Home') }}</span>
        </NuxtLink>
        <NuxtLink v-if="hasEventSelected" to="/games" :class="isActive('/games') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-1">
          <UIcon name="i-heroicons-list-bullet" />
          <span>{{ t('nav.Games') }}</span>
        </NuxtLink>
        <NuxtLink v-if="hasEventSelected" to="/charts" :class="isActive('/charts') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-1">
          <UIcon name="i-heroicons-rectangle-group" />
          <span>{{ t('nav.Charts') }}</span>
        </NuxtLink>
        <NuxtLink v-if="hasEventSelected" to="/team" :class="isActive('/team') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-1">
          <UIcon name="i-heroicons-users" />
          <span>{{ t('Teams.Team') }}</span>
        </NuxtLink>
        <div v-if="hasEventSelected" class="relative group">
          <button :class="[isActive('/login') || isActive('/scrutineering') ? 'text-green-400 font-bold' : '', 'flex items-center space-x-1 focus:outline-none']">
            <UIcon name="i-heroicons-user-group" />
            <span>{{ t('nav.Staff') }}</span>
            <UIcon name="i-heroicons-chevron-down" />
          </button>
          <div class="absolute left-0 mt-2 w-40 bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <NuxtLink to="/login" :class="isActive('/login') ? 'bg-gray-700 text-green-400' : ''" class="flex px-4 py-2 hover:bg-gray-700 items-center space-x-1">
              <UIcon name="i-heroicons-user-circle" />
              <span>{{ t('nav.Login') }}</span>
            </NuxtLink>
            <NuxtLink v-if="isAuthenticated" to="/scrutineering" :class="isActive('/scrutineering') ? 'bg-gray-700 text-green-400' : ''" class="flex px-4 py-2 hover:bg-gray-700 items-center space-x-1">
              <UIcon name="i-heroicons-clipboard-document-check" />
              <span>{{ t('nav.Scrutineering') }}</span>
            </NuxtLink>
          </div>
        </div>
        <NuxtLink to="/about" :class="isActive('/about') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-1">
          <UIcon name="i-heroicons-information-circle" />
          <span>{{ t('nav.About') }}</span>
        </NuxtLink>
      </nav>
      <div class="flex items-center ml-2 md:ml-4">
        <AppLanguageSwitcher />
      </div>
      <!-- Mobile menu button -->
      <button class="md:hidden text-white focus:outline-none text-3xl ml-2" @click="showMenu = !showMenu">
        <UIcon name="i-heroicons-bars-3" class="h-8 w-8" />
      </button>
    </div>
    <!-- Mobile menu -->
    <div v-if="showMenu" class="md:hidden px-4 pb-2">
      <NuxtLink to="/" :class="isActive('/') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-home" />
        <span>{{ t('nav.Home') }}</span>
      </NuxtLink>
      <NuxtLink v-if="hasEventSelected" to="/games" :class="isActive('/games') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-list-bullet" />
        <span>{{ t('nav.Games') }}</span>
      </NuxtLink>
      <NuxtLink v-if="hasEventSelected" to="/charts" :class="isActive('/charts') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-rectangle-group" />
        <span>{{ t('nav.Charts') }}</span>
      </NuxtLink>
      <NuxtLink v-if="hasEventSelected" to="/team" :class="isActive('/team') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-users" />
        <span>{{ t('Teams.Team') }}</span>
      </NuxtLink>
      <NuxtLink v-if="hasEventSelected" to="/login" :class="isActive('/login') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-user-circle" />
        <span>{{ t('nav.Login') }}</span>
      </NuxtLink>
      <NuxtLink v-if="isAuthenticated && hasEventSelected" to="/scrutineering" :class="isActive('/scrutineering') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-clipboard-document-check" />
        <span>{{ t('nav.Scrutineering') }}</span>
      </NuxtLink>
      <NuxtLink to="/about" :class="isActive('/about') ? 'text-green-400 font-bold' : 'hover:text-green-400'" class="flex items-center space-x-2 py-2">
        <UIcon name="i-heroicons-information-circle" />
        <span>{{ t('nav.About') }}</span>
      </NuxtLink>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { usePwa } from '~/composables/usePwa'
import { useEventGuard } from '~/composables/useEventGuard'

const showMenu = ref(false)
const { t } = useI18n()
const route = useRoute()
const preferenceStore = usePreferenceStore()
const { isOnline } = usePwa()
const { hasEventSelected } = useEventGuard()

// Check if user is authenticated
const isAuthenticated = computed(() => {
  return preferenceStore.preferences.user !== undefined && preferenceStore.preferences.user !== null
})

// Check if a link is active
const isActive = (path) => {
  // Handle /team route with parameters
  if (path === '/team' && route.path.startsWith('/team')) {
    return true
  }
  return route.path === path
}

// Close mobile menu when route changes
watch(() => route.path, () => {
  showMenu.value = false
})

onMounted(async () => {
  await preferenceStore.fetchItems()
})
</script>