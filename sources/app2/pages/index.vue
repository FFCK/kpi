<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #center>
        <h1 class="text-2xl font-bold text-gray-800">KPI Application</h1>
      </template>
      <template #right></template>
    </AppSecondaryNav>

    <div class="px-4">
      <div class="pb-8">
        <EventSelector />
      </div>

      <div v-if="hasEventSelected" class="grid grid-cols-1 md:grid-cols-3 gap-4 my-5">
        <NuxtLink to="/games" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg transition-colors text-center flex items-center justify-center gap-2">
          <UIcon name="i-heroicons-list-bullet" class="h-6 w-6" />
          <span>{{ t('nav.Games') }}</span>
        </NuxtLink>
        <NuxtLink to="/charts" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg transition-colors text-center flex items-center justify-center gap-2">
          <UIcon name="i-heroicons-rectangle-group" class="h-6 w-6" />
          <span>{{ t('nav.Charts') }}</span>
        </NuxtLink>
        <NuxtLink to="/team" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg transition-colors text-center flex items-center justify-center gap-2">
          <UIcon name="i-heroicons-users" class="h-6 w-6" />
          <span>{{ t('Teams.Team') }}</span>
        </NuxtLink>
      </div>

      <div class="text-center mt-8">
        <img class="w-30 mx-auto" src="/img/logo_kp.png" alt="kayak-polo" />
      </div>
    </div>

    <AppFooter />
  </div>
</template>

<script setup>
import { useEventGuard } from '~/composables/useEventGuard'

const { t } = useI18n()
const { hasEventSelected } = useEventGuard()

// Page-specific SEO
useSeoMeta({
  title: 'Home - KPI Application',
  description: 'Welcome to KPI Application - Your kayak polo information hub. Access live scores, rankings, team statistics, and tournament information.',
  ogTitle: 'KPI Application - Kayak Polo Information',
  ogDescription: 'Your kayak polo information hub - Live scores, rankings, and statistics'
})
</script>
