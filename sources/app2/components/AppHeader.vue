<template>
  <header class="bg-gray-900 text-white shadow-md">
    <div class="flex items-center justify-between px-4 py-2">
      <NuxtLink to="/" class="flex items-center space-x-2">
        <img src="/img/logo_kp.png" width="30" height="30" alt="logo" class="inline-block align-middle" />
        <span class="font-bold text-lg">KPI App</span>
        <span class="ml-2 text-green-400"><UIcon name="i-heroicons-wifi" /></span>
      </NuxtLink>
      <nav class="hidden md:flex space-x-4">
        <NuxtLink to="/" class="hover:text-green-400 flex items-center space-x-1">
          <UIcon name="i-heroicons-home" />
          <span>{{ t('nav.Home') }}</span>
        </NuxtLink>
        <NuxtLink to="/games" class="hover:text-green-400 flex items-center space-x-1">
          <UIcon name="i-heroicons-list-bullet" />
          <span>{{ t('nav.Games') }}</span>
        </NuxtLink>
        <NuxtLink to="/charts" class="hover:text-green-400 flex items-center space-x-1">
          <UIcon name="i-heroicons-chart-bar" />
          <span>{{ t('nav.Chart') }}</span>
        </NuxtLink>
        <div class="relative group">
          <button class="flex items-center space-x-1 focus:outline-none">
            <UIcon name="i-heroicons-user-group" />
            <span>{{ t('nav.Staff') }}</span>
            <UIcon name="i-heroicons-chevron-down" />
          </button>
          <div class="absolute left-0 mt-2 w-40 bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <NuxtLink to="/login" class="block px-4 py-2 hover:bg-gray-700 flex items-center space-x-1">
              <UIcon name="i-heroicons-user-circle" />
              <span>{{ t('nav.Login') }}</span>
            </NuxtLink>
            <NuxtLink v-if="isAuthenticated" to="/scrutineering" class="block px-4 py-2 hover:bg-gray-700 flex items-center space-x-1">
              <UIcon name="i-heroicons-clipboard-document-check" />
              <span>{{ t('nav.Scrutineering') }}</span>
            </NuxtLink>
          </div>
        </div>
        <NuxtLink to="/about" class="hover:text-green-400 flex items-center space-x-1">
          <UIcon name="i-heroicons-information-circle" />
          <span>{{ t('nav.About') }}</span>
        </NuxtLink>
      </nav>
      <div class="ml-4">
        <LanguageSwitcher />
      </div>
      <!-- Mobile menu button -->
      <button class="md:hidden text-white focus:outline-none" @click="showMenu = !showMenu">
        <UIcon name="i-heroicons-bars-3" />
      </button>
    </div>
    <!-- Mobile menu -->
    <div v-if="showMenu" class="md:hidden px-4 pb-2">
      <NuxtLink to="/" class="block py-2 hover:text-green-400">{{ t('nav.Home') }}</NuxtLink>
      <NuxtLink to="/games" class="block py-2 hover:text-green-400">{{ t('nav.Games') }}</NuxtLink>
      <NuxtLink to="/charts" class="block py-2 hover:text-green-400">{{ t('nav.Chart') }}</NuxtLink>
      <NuxtLink to="/login" class="block py-2 hover:text-green-400">{{ t('nav.Login') }}</NuxtLink>
      <NuxtLink to="/about" class="block py-2 hover:text-green-400">{{ t('nav.About') }}</NuxtLink>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

const showMenu = ref(false)
const { t } = useI18n()
const preferenceStore = usePreferenceStore()

// Check if user is authenticated
const isAuthenticated = computed(() => {
  return preferenceStore.preferences.user !== undefined && preferenceStore.preferences.user !== null
})

onMounted(async () => {
  await preferenceStore.fetchItems()
})
</script>