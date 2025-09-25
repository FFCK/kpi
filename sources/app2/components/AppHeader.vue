<template>
  <header class="bg-gray-900 text-white shadow-md">
    <div class="flex items-center justify-between px-4 py-2">
      <NuxtLink to="/" class="flex items-center space-x-2">
        <img src="/img/logo_kp.png" width="30" height="30" alt="logo" class="inline-block align-middle" />
        <span class="font-bold text-lg">KPI App</span>
        <span class="ml-2 text-green-400"><i class="bi bi-wifi"></i></span>
      </NuxtLink>
      <nav class="hidden md:flex space-x-4">
        <NuxtLink to="/" class="hover:text-green-400 flex items-center space-x-1">
          <i class="bi bi-house"></i>
          <span>{{ t('nav.Home') }}</span>
        </NuxtLink>
        <NuxtLink to="/games" class="hover:text-green-400 flex items-center space-x-1">
          <i class="bi bi-list-ol"></i>
          <span>{{ t('nav.Games') }}</span>
        </NuxtLink>
        <NuxtLink to="/charts" class="hover:text-green-400 flex items-center space-x-1">
          <i class="bi bi-diagram-2"></i>
          <span>{{ t('nav.Chart') }}</span>
        </NuxtLink>
        <div class="relative group">
          <button class="flex items-center space-x-1 focus:outline-none">
            <i class="el-icon-service"></i>
            <span>{{ t('nav.Staff') }}</span>
            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          <div class="absolute left-0 mt-2 w-40 bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <NuxtLink to="/login" class="block px-4 py-2 hover:bg-gray-700 flex items-center space-x-1">
              <i class="bi bi-person-circle"></i>
              <span>{{ t('nav.Login') }}</span>
            </NuxtLink>
          </div>
        </div>
        <NuxtLink to="/about" class="hover:text-green-400 flex items-center space-x-1">
          <i class="el-icon-info"></i>
          <span>{{ t('nav.About') }}</span>
        </NuxtLink>
      </nav>
      <div class="ml-4">
        <LanguageSwitcher />
      </div>
      <!-- Mobile menu button -->
      <button class="md:hidden text-white focus:outline-none" @click="showMenu = !showMenu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
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
import { ref } from 'vue'
const showMenu = ref(false)
const { t } = useI18n()
</script>