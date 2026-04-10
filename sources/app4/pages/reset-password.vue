<script setup lang="ts">
definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

const { t } = useI18n()
const config = useRuntimeConfig()
const route = useRoute()

const token = computed(() => (route.query.token as string) || '')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref(false)

const rules = computed(() => [
  { key: 'length', label: t('users.reset_password.rule_length'), valid: password.value.length >= 10 },
  { key: 'uppercase', label: t('users.reset_password.rule_uppercase'), valid: /[A-Z]/.test(password.value) },
  { key: 'lowercase', label: t('users.reset_password.rule_lowercase'), valid: /[a-z]/.test(password.value) },
  { key: 'digit', label: t('users.reset_password.rule_digit'), valid: /\d/.test(password.value) },
  { key: 'special', label: t('users.reset_password.rule_special'), valid: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password.value) }
])

const allRulesValid = computed(() => rules.value.every(r => r.valid))
const passwordsMatch = computed(() => password.value === confirmPassword.value && confirmPassword.value.length > 0)
const canSubmit = computed(() => allRulesValid.value && passwordsMatch.value && !loading.value && token.value)

async function handleSubmit() {
  if (!canSubmit.value) return

  error.value = null
  loading.value = true

  try {
    const response = await fetch(`${config.public.api2BaseUrl}/auth/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: token.value, password: password.value })
    })

    if (!response.ok) {
      const data = await response.json().catch(() => null)
      if (response.status === 400 && data?.code === 'INVALID_TOKEN') {
        error.value = t('users.reset_password.error_token_invalid')
      } else if (data?.message) {
        error.value = data.message
      } else {
        error.value = t('users.reset_password.error_token_invalid')
      }
      return
    }

    success.value = true
  } catch {
    error.value = t('users.reset_password.error_token_invalid')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-primary-600">KPI Admin</h1>
      </div>

      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-xl font-semibold text-header-900 mb-6">
          {{ t('users.reset_password.title') }}
        </h2>

        <!-- No token -->
        <div v-if="!token" class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-700 text-sm">
          {{ t('users.reset_password.error_token_invalid') }}
        </div>

        <!-- Success -->
        <div v-else-if="success" class="space-y-4">
          <div class="p-3 bg-success-50 border border-success-200 rounded-lg text-success-700 text-sm">
            {{ t('users.reset_password.success') }}
          </div>
          <NuxtLink
            to="/login"
            class="block text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium"
          >
            {{ t('login.title') }}
          </NuxtLink>
        </div>

        <!-- Form -->
        <form v-else class="space-y-4" @submit.prevent="handleSubmit">
          <!-- Error -->
          <div
            v-if="error"
            class="p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-700 text-sm"
          >
            {{ error }}
          </div>

          <!-- New password -->
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('users.reset_password.new_password') }}
            </label>
            <UInput
              v-model="password"
              type="password"
              required
            />
          </div>

          <!-- Rules checklist -->
          <div class="text-xs text-header-600 space-y-0.5">
            <div class="font-medium mb-1">{{ t('users.reset_password.rules') }}</div>
            <div
              v-for="rule in rules"
              :key="rule.key"
              class="flex items-center gap-1.5"
            >
              <UIcon
                :name="rule.valid ? 'i-heroicons-check-circle' : 'i-heroicons-x-circle'"
                :class="rule.valid ? 'text-success-500' : 'text-header-400'"
                class="w-4 h-4"
              />
              <span :class="rule.valid ? 'text-success-700' : 'text-header-500'">{{ rule.label }}</span>
            </div>
          </div>

          <!-- Confirm password -->
          <div>
            <label class="block text-sm font-medium text-header-700 mb-1">
              {{ t('users.reset_password.confirm_password') }}
            </label>
            <UInput
              v-model="confirmPassword"
              type="password"
              required
            />
            <p
              v-if="confirmPassword && !passwordsMatch"
              class="mt-1 text-xs text-danger-500"
            >
              {{ t('users.reset_password.error_mismatch') }}
            </p>
          </div>

          <!-- Submit -->
          <UButton
            type="submit"
            color="primary"
            block
            :loading="loading"
            :disabled="!canSubmit"
          >
            {{ t('users.reset_password.submit') }}
          </UButton>
        </form>
      </div>
    </div>
  </div>
</template>
