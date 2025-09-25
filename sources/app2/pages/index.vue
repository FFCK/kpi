<template>
  <div>
    <AppTitle>KPI Application</AppTitle>

    <EventSelector />
    
    <div class="flex justify-around my-5">
      <NuxtLink to="/games" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">{{ t('nav.Games') }}</NuxtLink>
      <NuxtLink to="/charts" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">{{ t('nav.Chart') }}</NuxtLink>
    </div>

    <AppAlert>
      This is an auto-imported component.
      <br>

      


    </AppAlert>

    {{ locale }}
    <h1>{{ t('message') }}</h1>
    <p>{{ t('status.Offline') }}</p>

    <div class="text-center mt-8">
      <img class="w-30 mx-auto" src="/img/logo_kp.png" alt="kayak-polo" />
      <p class="text-xs text-gray-500 mt-2">v0.1.0</p>
    </div>
  </div>
</template>

<script setup>
const { t, locale } = useI18n()

watch(locale, (l) => console.log(`Current locale: ${l}`))
</script>
