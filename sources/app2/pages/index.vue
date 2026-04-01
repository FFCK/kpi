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

      <EventQrCode />

      <!-- Tutorial screenshots -->
      <div v-if="hasEventSelected" class="mt-10 pt-8 border-t border-gray-200">
        <!-- Titre (4) -->
        <p class="text-center text-lg font-medium text-gray-700 mb-4">
          <UIcon name="i-heroicons-information-circle" class="h-6 w-6 inline-block mr-1" />
          {{ t('Event.TutoTitle') }}
        </p>

        <!-- Desktop: tuto_md.png avec descriptions gauche/droite -->
        <div class="hidden md:grid md:grid-cols-[1fr_3fr_1fr] items-center gap-6">
          <p class="text-right text-md text-gray-700">
            <UIcon name="i-heroicons-users" class="h-5 w-5 inline-block mb-0.5 mr-1" />
            {{ t('Event.TutoTeam') }}
          </p>
          <!-- Image avec opacité (2) + badge overlay (1) -->
          <div class="relative shadow-xl/30">
            <img src="/img/tuto_md.png" alt="Tutorial" class="w-full rounded opacity-70" />
            <span class="absolute top-2 right-2 bg-gray-800/70 text-white text-xs font-medium px-2 py-0.5 rounded italic">
              {{ t('Event.TutoExample') }}
            </span>
          </div>
          <p class="text-left text-md text-gray-700">
            <UIcon name="i-heroicons-document-text" class="h-5 w-5 inline-block mb-0.5 mr-1" />
            {{ t('Event.TutoMatchSheet') }}
          </p>
        </div>

        <!-- Mobile: tuto_xs.png avec descriptions au-dessus/dessous -->
        <div class="md:hidden flex flex-col items-center gap-3">
          <p class="text-center text-sm text-gray-600 px-4">
            <UIcon name="i-heroicons-users" class="h-4 w-4 inline-block mb-0.5 mr-1" />
            {{ t('Event.TutoTeam') }}
          </p>
          <!-- Image avec opacité (2) + badge overlay (1) -->
          <div class="relative w-full max-w-xs">
            <img src="/img/tuto_xs.png" alt="Tutorial" class="w-full rounded shadow-sm opacity-60" />
            <span class="absolute top-2 right-2 bg-gray-800/70 text-white text-xs font-medium px-2 py-0.5 rounded italic">
              {{ t('Event.TutoExample') }}
            </span>
          </div>
          <p class="text-center text-sm text-gray-600 px-4">
            <UIcon name="i-heroicons-document-text" class="h-4 w-4 inline-block mb-0.5 mr-1" />
            {{ t('Event.TutoMatchSheet') }}
          </p>
        </div>
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
