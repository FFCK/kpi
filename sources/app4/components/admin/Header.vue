<script setup lang="ts">
import type { User } from '~/types'
import { useAuthStore } from '~/stores/authStore'

interface MenuItem {
  to?: string
  icon: string
  label: string
  children?: MenuItem[]
}

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
const { isOnline } = useOnlineStatus()

const languages = [
  { code: 'fr', label: 'FR', flag: '🇫🇷' },
  { code: 'en', label: 'EN', flag: '🇬🇧' }
]

// Menu items based on user profile
const menuItems = computed<MenuItem[]>(() => {
  const profile = authStore.user?.profile ?? 99
  const items: MenuItem[] = []

  // Compétition (dropdown) - profile <= 10
  if (profile <= 10) {
    const children: MenuItem[] = []
    if (profile <= 9) {
      children.push({
        to: '/documents',
        icon: 'heroicons:document-text',
        label: t('menu.documents')
      })
    }
    if (profile <= 2) {
      children.push({
        to: '/events',
        icon: 'heroicons:calendar-days',
        label: t('menu.events')
      })
    }
    items.push({
      to: '/competitions',
      icon: 'heroicons:trophy',
      label: t('menu.competition'),
      ...(children.length > 0 ? { children } : {})
    })
  }

  // Équipes - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/teams',
      icon: 'heroicons:user-group',
      label: t('menu.teams')
    })
  }

  // Journées/Phases - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/gamedays',
      icon: 'heroicons:calendar',
      label: t('menu.gamedays')
    })
  }

  // Matchs - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/matches',
      icon: 'heroicons:play-circle',
      label: t('menu.matches')
    })
  }

  // Classements (dropdown) - profile <= 9
  if (profile <= 9) {
    items.push({
      to: '/rankings',
      icon: 'heroicons:chart-bar',
      label: t('menu.rankings'),
      children: [
        {
          to: '/rankings/initial',
          icon: 'heroicons:list-bullet',
          label: t('menu.initial_ranking')
        }
      ]
    })
  }

  // Stats (dropdown) - profile <= 9
  if (profile <= 9) {
    const children: MenuItem[] = []
    if (profile <= 8) {
      children.push({
        to: '/athletes',
        icon: 'heroicons:user',
        label: t('menu.athletes')
      })
    }
    items.push({
      to: '/stats',
      icon: 'heroicons:chart-pie',
      label: t('menu.statistics'),
      ...(children.length > 0 ? { children } : {})
    })
  }

  // Gestion (dropdown) - visible if any child is accessible
  {
    const children: MenuItem[] = []
    if (profile <= 9) {
      children.push({
        to: '/clubs',
        icon: 'heroicons:building-office-2',
        label: t('menu.clubs')
      })
    }
    if (profile <= 3) {
      children.push({
        to: '/users',
        icon: 'heroicons:users',
        label: t('menu.users')
      })
    }
    if (profile === 1) {
      children.push({
        to: '/operations',
        icon: 'heroicons:wrench-screwdriver',
        label: t('menu.operations')
      })
    }
    if (children.length > 0) {
      items.push({
        icon: 'heroicons:cog-6-tooth',
        label: t('menu.management'),
        children
      })
    }
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

const isActive = (path: string) => route.path.startsWith(path)

// User dropdown
const userMenuOpen = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

// Nav dropdowns
const openDropdown = ref<string | null>(null)
const navRef = ref<HTMLElement | null>(null)

const toggleDropdown = (label: string) => {
  openDropdown.value = openDropdown.value === label ? null : label
}

// Mobile accordion
const mobileExpanded = ref<string | null>(null)
const toggleMobileExpanded = (label: string) => {
  mobileExpanded.value = mobileExpanded.value === label ? null : label
}

// Close dropdowns when clicking outside
onMounted(() => {
  const handleClickOutside = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
      userMenuOpen.value = false
    }
    if (navRef.value && !navRef.value.contains(event.target as Node)) {
      openDropdown.value = null
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
          <!-- Online/Offline indicator -->
          <ClientOnly>
            <UTooltip :text="isOnline ? t('status.online') : t('status.offline')">
              <span :class="isOnline ? 'text-green-400' : 'text-red-500 animate-pulse'">
                <UIcon :name="isOnline ? 'i-heroicons-wifi' : 'i-heroicons-signal-slash'" class="h-5 w-5" />
              </span>
            </UTooltip>
            <template #fallback>
              <span class="text-green-400">
                <UIcon name="i-heroicons-wifi" class="h-5 w-5" />
              </span>
            </template>
          </ClientOnly>
        </div>

        <!-- Center: Horizontal menu (desktop only) -->
        <nav ref="navRef" class="hidden lg:flex items-center space-x-1">
          <template v-for="item in menuItems" :key="item.label">
            <!-- Item with dropdown -->
            <div v-if="item.children" class="relative">
              <button
                :class="[
                  'flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors',
                  openDropdown === item.label || (item.to && isActive(item.to))
                    ? 'text-blue-400 bg-gray-800'
                    : 'text-gray-300 hover:text-white hover:bg-gray-800'
                ]"
                @click="toggleDropdown(item.label)"
              >
                <UIcon :name="item.icon" class="w-4 h-4" />
                <span>{{ item.label }}</span>
                <UIcon
                  name="heroicons:chevron-down"
                  class="w-3 h-3 transition-transform"
                  :class="{ 'rotate-180': openDropdown === item.label }"
                />
              </button>

              <!-- Dropdown panel -->
              <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="openDropdown === item.label"
                  class="absolute left-0 mt-1 w-52 bg-gray-800 rounded-lg shadow-lg border border-gray-700 py-1 z-50"
                >
                  <!-- Parent link if it has a route -->
                  <NuxtLink
                    v-if="item.to"
                    :to="item.to"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors"
                    @click="openDropdown = null"
                  >
                    <UIcon :name="item.icon" class="w-4 h-4" />
                    <span>{{ item.label }}</span>
                  </NuxtLink>
                  <div v-if="item.to" class="border-t border-gray-700 my-1" />
                  <!-- Children -->
                  <NuxtLink
                    v-for="child in item.children"
                    :key="child.to"
                    :to="child.to!"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors"
                    @click="openDropdown = null"
                  >
                    <UIcon :name="child.icon" class="w-4 h-4" />
                    <span>{{ child.label }}</span>
                  </NuxtLink>
                </div>
              </Transition>
            </div>

            <!-- Simple link (no dropdown) -->
            <NuxtLink
              v-else
              :to="item.to!"
              :class="[
                'flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isActive(item.to!)
                  ? 'text-blue-400 bg-gray-800'
                  : 'text-gray-300 hover:text-white hover:bg-gray-800'
              ]"
            >
              <UIcon :name="item.icon" class="w-4 h-4" />
              <span>{{ item.label }}</span>
            </NuxtLink>
          </template>
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
        <div class="px-4 pb-2 space-y-1">
          <template v-for="item in menuItems" :key="item.label">
            <!-- Item with dropdown (accordion on mobile) -->
            <div v-if="item.children">
              <button
                :class="[
                  'w-full flex items-center justify-between py-2 text-sm font-medium transition-colors',
                  mobileExpanded === item.label ? 'text-blue-400' : 'text-gray-200 hover:text-white'
                ]"
                @click="toggleMobileExpanded(item.label)"
              >
                <span class="flex items-center gap-2">
                  <UIcon :name="item.icon" class="w-5 h-5" />
                  {{ item.label }}
                </span>
                <UIcon
                  name="heroicons:chevron-down"
                  class="w-4 h-4 transition-transform"
                  :class="{ 'rotate-180': mobileExpanded === item.label }"
                />
              </button>
              <div v-if="mobileExpanded === item.label" class="ml-6 space-y-1 border-l border-gray-700 pl-3">
                <NuxtLink
                  v-if="item.to"
                  :to="item.to"
                  class="flex items-center gap-2 py-1.5 text-sm text-gray-300 hover:text-white transition-colors"
                >
                  <UIcon :name="item.icon" class="w-4 h-4" />
                  {{ item.label }}
                </NuxtLink>
                <NuxtLink
                  v-for="child in item.children"
                  :key="child.to"
                  :to="child.to!"
                  class="flex items-center gap-2 py-1.5 text-sm text-gray-300 hover:text-white transition-colors"
                >
                  <UIcon :name="child.icon" class="w-4 h-4" />
                  {{ child.label }}
                </NuxtLink>
              </div>
            </div>

            <!-- Simple link -->
            <NuxtLink
              v-else
              :to="item.to!"
              :class="[
                'flex items-center gap-2 py-2 text-sm font-medium transition-colors',
                isActive(item.to!)
                  ? 'text-blue-400'
                  : 'text-gray-200 hover:text-white'
              ]"
            >
              <UIcon :name="item.icon" class="w-5 h-5" />
              <span>{{ item.label }}</span>
            </NuxtLink>
          </template>
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
