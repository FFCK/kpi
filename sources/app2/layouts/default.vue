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
    </ClientOnly>
  </div>
</template>

<script setup>
// Network monitoring is handled by useOnlineStatus composable (used in AppHeader and other components)
</script>
