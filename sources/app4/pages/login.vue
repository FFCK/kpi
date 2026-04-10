<script setup lang="ts">
definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

const { t, locale, setLocale } = useI18n()
const { login } = useAuth()

const languages = [
  { code: 'fr', label: 'FR', flag: '🇫🇷' },
  { code: 'en', label: 'EN', flag: '🇬🇧' }
]

const form = ref({
  username: '',
  password: ''
})

const loading = ref(false)
const error = ref<string | null>(null)

const handleSubmit = async () => {
  error.value = null
  loading.value = true

  try {
    const result = await login(form.value.username, form.value.password)
    if (result.hasMandates) {
      await navigateTo('/select-mandate')
    } else {
      await navigateTo('/')
    }
  } catch (e) {
    if (e instanceof Error) {
      if (e.message.includes('profile 1')) {
        error.value = t('login.error_profile_restricted')
      } else if (e.message.includes('Invalid credentials')) {
        error.value = t('login.error_invalid_credentials')
      } else {
        error.value = t('login.error_generic')
      }
    } else {
      error.value = t('login.error_generic')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <!-- Language switcher -->
    <div class="fixed top-4 right-4 flex gap-2">
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
        @click="setLocale(lang.code as 'en' | 'fr')"
      >
        {{ lang.flag }}
      </button>
    </div>

    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-primary-600">KPI Admin</h1>
        <p class="mt-2 text-header-600">{{ t('login.subtitle') }}</p>
      </div>

      <!-- Login form -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-xl font-semibold text-header-900 mb-6">
          {{ t('login.title') }}
        </h2>

        <!-- Error message -->
        <div
          v-if="error"
          class="mb-4 p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-700 text-sm"
        >
          {{ error }}
        </div>

        <form class="space-y-4" @submit.prevent="handleSubmit">
          <!-- Username -->
          <div>
            <label for="username" class="block text-sm font-medium text-header-700 mb-1">
              {{ t('login.username') }}
            </label>
            <UInput
              id="username"
              v-model="form.username"
              type="text"
              variant="outline"
              :placeholder="t('login.username_placeholder')"
              required
              autofocus
            />
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-header-700 mb-1">
              {{ t('login.password') }}
            </label>
            <UInput
              id="password"
              v-model="form.password"
              type="password"
              variant="outline"
              :placeholder="t('login.password_placeholder')"
              required
            />
          </div>

          <!-- Submit button -->
          <UButton
            type="submit"
            color="primary"
            block
            :loading="loading"
            :disabled="loading || !form.username || !form.password"
          >
            {{ t('login.submit') }}
          </UButton>
        </form>

        <!-- Beta notice -->
        <div class="mt-6 p-3 bg-warning-50 border border-warning-200 rounded text-sm text-warning-700">
          <strong>{{ t('login.beta_title') }}</strong>
          {{ t('login.beta_message') }}
        </div>
      </div>
    </div>
  </div>
</template>

<style>
#username,
#password {
  background-color: var(--color-primary-50) !important;
}
</style>
