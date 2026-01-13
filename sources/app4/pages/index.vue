<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const authStore = useAuthStore()
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

    <!-- Quick stats / navigation cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Events card -->
      <NuxtLink
        v-if="authStore.hasProfile(2)"
        to="/events"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-100 rounded-lg">
            <UIcon name="i-heroicons-calendar-days" class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.events') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.manage_events') }}</p>
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

      <!-- Statistics card -->
      <NuxtLink
        v-if="authStore.hasProfile(9)"
        to="/stats"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-purple-100 rounded-lg">
            <UIcon name="i-heroicons-chart-bar" class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.statistics') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.view_statistics') }}</p>
          </div>
        </div>
      </NuxtLink>

      <!-- Operations card -->
      <NuxtLink
        v-if="authStore.isSuperAdmin"
        to="/operations"
        class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 bg-orange-100 rounded-lg">
            <UIcon name="i-heroicons-cog-6-tooth" class="w-6 h-6 text-orange-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">{{ t('menu.operations') }}</h3>
            <p class="text-sm text-gray-500">{{ t('dashboard.system_operations') }}</p>
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
