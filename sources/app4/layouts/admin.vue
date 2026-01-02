<script setup lang="ts">
import { useAuthStore } from '~/stores/authStore'

const authStore = useAuthStore()
const sidebarCollapsed = ref(false)
const sidebarOpen = ref(false)

const toggleSidebar = () => {
  // On mobile, toggle open/close
  // On desktop, toggle collapsed/expanded
  if (window.innerWidth < 1024) {
    sidebarOpen.value = !sidebarOpen.value
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }
}

// Close mobile sidebar on route change
const route = useRoute()
watch(() => route.path, () => {
  sidebarOpen.value = false
})
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Mobile sidebar backdrop -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <AdminSidebar
      :collapsed="sidebarCollapsed"
      :mobile-open="sidebarOpen"
      @close="sidebarOpen = false"
    />

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Header -->
      <AdminHeader
        :user="authStore.user"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page content -->
      <main class="flex-1 p-4 md:p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
