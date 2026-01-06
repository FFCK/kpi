<template>
  <div>
    <AppHeader />

    <div class="pb-16">
      <NuxtPage />
    </div>

    <AppFooter />
    <AppInstallPrompt />
    <ClientOnly>
      <UpdatePrompt />
      <NetworkMonitor />
    </ClientOnly>
  </div>
</template>

<script setup>
// Network monitoring is initialized via NetworkMonitor component (client-only)
</script>
