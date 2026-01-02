<script setup lang="ts">
import type { User } from '~/types'

defineProps<{
  user: User | null
}>()

const emit = defineEmits<{
  'toggle-sidebar': []
}>()

const { t, locale, setLocale } = useI18n()
const { logout } = useAuth()
const router = useRouter()

const languages = [
  { code: 'fr', label: 'FR' },
  { code: 'en', label: 'EN' }
]

const handleLogout = async () => {
  await logout()
  router.push('/login')
}

const switchLanguage = (code: string) => {
  setLocale(code)
}
</script>

<template>
  <header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 shadow-sm">
    <!-- Left: Menu toggle -->
    <div class="flex items-center gap-4">
      <button
        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
        @click="emit('toggle-sidebar')"
      >
        <UIcon name="i-heroicons-bars-3" class="w-6 h-6" />
      </button>
      <h1 class="text-lg font-semibold text-gray-800 hidden sm:block">
        {{ t('app.title') }}
      </h1>
    </div>

    <!-- Right: Language + Logout -->
    <div class="flex items-center gap-2">
      <!-- Language switcher -->
      <div class="flex border border-gray-200 rounded-lg overflow-hidden">
        <button
          v-for="lang in languages"
          :key="lang.code"
          :class="[
            'px-3 py-1.5 text-sm font-medium transition-colors',
            locale === lang.code
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-600 hover:bg-gray-50'
          ]"
          @click="switchLanguage(lang.code)"
        >
          {{ lang.label }}
        </button>
      </div>

      <!-- User dropdown -->
      <UDropdown
        :items="[[
          { label: t('logout'), icon: 'i-heroicons-arrow-right-on-rectangle', click: handleLogout }
        ]]"
      >
        <UButton
          color="neutral"
          variant="ghost"
          :label="user?.name ?? ''"
          trailing-icon="i-heroicons-chevron-down"
        />
      </UDropdown>
    </div>
  </header>
</template>
