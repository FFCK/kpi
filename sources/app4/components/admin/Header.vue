<script setup lang="ts">
import type { User } from '~/types'
import { useAuthStore } from '~/stores/authStore'

defineProps<{
  user: User | null
  mobileMenuOpen: boolean
}>()

const emit = defineEmits<{
  'toggle-mobile-menu': []
}>()

const { t, locale, setLocale } = useI18n()
const { logout } = useAuth()
const router = useRouter()
const authStore = useAuthStore()
const route = useRoute()

const languages = [
  { code: 'fr', label: 'FR', flag: '🇫🇷' },
  { code: 'en', label: 'EN', flag: '🇬🇧' }
]

// Menu items based on user profile
const menuItems = computed(() => {
  const profile = authStore.user?.profile ?? 99
  const items = []

  // Events - profile <= 2
  if (profile <= 2) {
    items.push({
      to: '/events',
      icon: 'heroicons:calendar-days',
      label: t('menu.events')
    })
  }

  // Documents - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/documents',
      icon: 'heroicons:document-text',
      label: t('menu.documents')
    })
  }

  // Statistics - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/statistics',
      icon: 'heroicons:chart-bar',
      label: t('menu.statistics')
    })
  }

  // Operations - profile == 1 only
  if (profile === 1) {
    items.push({
      to: '/operations',
      icon: 'heroicons:cog-6-tooth',
      label: t('menu.operations')
    })
  }

  return items
})

const handleLogout = async () => {
  userMenuOpen.value = false
  await logout()
  router.push('/login')
}

const switchLanguage = (code: string) => {
  setLocale(code)
}

const isActive = (path: string) => route.path === path

// User dropdown
const userMenuOpen = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

// Close dropdown when clicking outside
onMounted(() => {
  const handleClickOutside = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
      userMenuOpen.value = false
    }
  }
  document.addEventListener('click', handleClickOutside)
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<template>
  <header class="sticky top-0 z-30 bg-gray-900 text-white border-b border-gray-800 shadow-lg">
    <!-- Top bar -->
    <div class="px-4 lg:px-6">
      <div class="flex items-center justify-between h-16">
        <!-- Left: Logo -->
        <div class="flex items-center gap-4">
          <NuxtLink to="/" class="flex items-center gap-2">
            <img src="/img/logo_kp.png" width="30" height="30" alt="logo" class="inline-block align-middle" />
            <span class="text-xl font-bold text-blue-400">KPI</span>
            <span class="text-sm text-gray-300">Admin</span>
          </NuxtLink>
        </div>

        <!-- Center: Horizontal menu (desktop only) -->
        <nav class="hidden lg:flex space-x-4">
          <NuxtLink
            v-for="item in menuItems"
            :key="item.to"
            :to="item.to"
            :class="[
              'flex items-center space-x-1 transition-colors',
              isActive(item.to)
                ? 'text-blue-400 font-bold' 
                : 'hover:text-blue-400'
            ]"
          >
            <UIcon :name="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </NuxtLink>
        </nav>

        <!-- Right: Language + User + Mobile toggle -->
        <div class="flex items-center gap-2">
          <!-- Language switcher with flags -->
          <div class="flex gap-2">
            <button
              v-for="lang in languages"
              :key="lang.code"
              :class="[
                'text-2xl transition-all duration-200 cursor-pointer',
                locale === lang.code
                  ? 'opacity-100 scale-110 drop-shadow-lg'
                  : 'opacity-50 hover:opacity-75 hover:scale-105'
              ]"
              :title="lang.label"
              @click="switchLanguage(lang.code)"
            >
              {{ lang.flag }}
            </button>
          </div>

          <!-- User menu (desktop) -->
          <div ref="userMenuRef" class="hidden lg:block relative">
            <button
              class="flex items-center gap-2 px-3 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors"
              @click="userMenuOpen = !userMenuOpen"
            >
              <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-medium">
                {{ user?.firstname?.[0] ?? 'U' }}{{ user?.name?.[0] ?? '' }}
              </div>
              <UIcon
                name="heroicons:chevron-down"
                class="w-4 h-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': userMenuOpen }"
              />
            </button>

            <!-- Dropdown menu -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
              >
                <!-- User info -->
                <div class="px-4 py-3 border-b border-gray-200">
                  <div class="text-sm font-medium text-gray-900">
                    {{ user?.name }} {{ user?.firstname }}
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ t('profile') }} {{ user?.profile }}
                  </div>
                </div>

                <!-- Menu items -->
                <button
                  class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  @click="handleLogout"
                >
                  <UIcon name="heroicons:arrow-right-on-rectangle" class="w-5 h-5" />
                  <span>{{ t('logout') }}</span>
                </button>
              </div>
            </Transition>
          </div>

          <!-- Mobile menu toggle -->
          <button
            class="lg:hidden p-2 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg"
            @click="emit('toggle-mobile-menu')"
          >
            <UIcon
              name="heroicons:bars-3"
              class="w-6 h-6"
            />
          </button>
        </div>
      </div>

      <!-- Mobile menu (vertical dropdown) -->
      <nav
        v-if="mobileMenuOpen"
        class="lg:hidden py-4 border-t border-gray-800"
      >
        <div class="md:hidden px-4 pb-2">
          <NuxtLink
            v-for="item in menuItems"
            :key="item.to"
            :to="item.to"
            :class="[
              'flex items-center space-x-2 py-2 transition-colors',
              isActive(item.to)
                ? 'text-blue-400 font-bold'
                : 'hover:text-blue-400 font-bold'
            ]"
          >
            <UIcon :name="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </NuxtLink>
        </div>

        <!-- User info mobile -->
        <div class="mt-4 pt-4 border-t border-gray-800 px-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-white">
                {{ user?.name }} {{ user?.firstname }}
              </div>
              <div class="text-xs text-gray-400">
                {{ t('profile') }} {{ user?.profile }}
              </div>
            </div>
            <UButton
              icon="heroicons:arrow-right-on-rectangle"
              color="error"
              variant="soft"
              size="sm"
              @click="handleLogout"
              class="pe-2"
            >
              {{ t('logout') }}
            </UButton>
          </div>
        </div>
      </nav>
    </div>
  </header>
</template>
