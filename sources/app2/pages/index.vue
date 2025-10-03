<template>
  <div class="container-fluid">
    <AppSecondaryNav>
      <template #center>
        <h1 class="text-2xl font-bold text-gray-800">KPI Application</h1>
      </template>
      <template #right></template>
    </AppSecondaryNav>

    <div class="px-4">
      <EventSelector />

      <div class="flex justify-around my-5">
        <NuxtLink to="/games" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">{{ t('nav.Games') }}</NuxtLink>
        <NuxtLink to="/charts" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">{{ t('nav.Chart') }}</NuxtLink>
      </div>

      <div class="text-center mt-8">
        <img class="w-30 mx-auto" src="/img/logo_kp.png" alt="kayak-polo" />
      </div>
    </div>

    <AppFooter />
  </div>
</template>

<script setup>
const { t } = useI18n()
</script>
