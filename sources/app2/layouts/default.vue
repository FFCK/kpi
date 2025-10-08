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
