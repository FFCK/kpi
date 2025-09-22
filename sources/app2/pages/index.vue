<template>
  <div>
    <AppTitle>KPI Application</AppTitle>

    <EventSelector />
    
    <div class="flex justify-around my-5">
      <NuxtLink to="/games" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">Matchs</NuxtLink>
      <NuxtLink to="/charts" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">Progression</NuxtLink>
    </div>

    <AppAlert>
      This is an auto-imported component
    </AppAlert>
    
    <div class="text-center mt-8">
      <img class="w-30 mx-auto" src="/img/logo_kp.png" alt="kayak-polo" />
      <p class="text-xs text-gray-500 mt-2">v0.1.0</p>
    </div>
  </div>
</template>

<script setup>
// La logique de sélection de l'événement a été déplacée vers le composant EventSelector.
</script>
