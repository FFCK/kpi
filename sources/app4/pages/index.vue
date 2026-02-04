<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const authStore = useAuthStore()
// Note: workContext is initialized by WorkContextSelector component
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('dashboard.title') }}
      </h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ t('dashboard.welcome', { name: authStore.user?.firstname }) }}
      </p>
    </div>

    <!-- Work Context Selector -->
    <div class="mb-6">
      <AdminWorkContextSelector />
    </div>

    <!-- Quick stats / navigation cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Competitions card -->
      <NuxtLink
        v-if="authStore.hasProfile(10)"
        to="/competitions"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-100 rounded-lg">
            <UIcon name="i-heroicons-trophy" class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.competition') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_competitions') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Teams card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/teams"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-indigo-100 rounded-lg">
            <UIcon name="i-heroicons-user-group" class="w-6 h-6 text-indigo-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.teams') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_teams') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Gamedays/Phases card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/gamedays"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-cyan-100 rounded-lg">
            <UIcon name="i-heroicons-calendar-days" class="w-6 h-6 text-cyan-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.gamedays') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_gamedays') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Rankings card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/rankings"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-amber-100 rounded-lg">
            <UIcon name="i-heroicons-chart-bar-square" class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.rankings') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_rankings') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Documents card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/documents"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-green-100 rounded-lg">
            <UIcon name="i-heroicons-document-text" class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.documents') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_documents') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Games card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/games"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-purple-100 rounded-lg">
            <UIcon name="i-heroicons-play-circle" class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.matches') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_games') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Statistics card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/stats"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-orange-100 rounded-lg">
            <UIcon name="i-heroicons-chart-bar" class="w-6 h-6 text-orange-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.statistics') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.view_statistics') }}</p>
          </div>
        </div>
      </NuxtLink>
    </div>

    <!-- Beta notice -->
    <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <div class="flex items-start gap-3">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-yellow-600 mt-0.5" />
        <div>
          <h4 class="font-medium text-yellow-800">{{ t('dashboard.beta_notice_title') }}</h4>
          <p class="mt-1 text-sm text-yellow-700">{{ t('dashboard.beta_notice_message') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
