<script setup lang="ts">
import { useAuthStore } from '~/stores/authStore'
import { version } from '~/package.json'

const { t } = useI18n()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

// Close mobile menu on route change
const route = useRoute()
watch(() => route.path, () => {
  mobileMenuOpen.value = false
})
</script>

<template>
  <div class="flex flex-col min-h-screen bg-gray-50">
    <!-- Mobile menu backdrop -->
    <div
      v-if="mobileMenuOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="mobileMenuOpen = false"
    />

    <!-- Header with horizontal menu -->
    <AdminHeader
      :user="authStore.user"
      :mobile-menu-open="mobileMenuOpen"
      @toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen"
    />

    <!-- Page content -->
    <main class="flex-1 p-4 md:p-6 overflow-auto">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 border-t border-gray-200 py-3 px-4 text-center text-sm text-gray-500">
      {{ t('app.title') }} - {{ t('footer.version') }} {{ version }}
    </footer>
  </div>
</template>
