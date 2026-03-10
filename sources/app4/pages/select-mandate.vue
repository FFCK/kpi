<script setup lang="ts">
definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

const { t } = useI18n()
const { switchMandate } = useAuth()
const authStore = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref<string | null>(null)

async function selectBase() {
  loading.value = true
  error.value = null
  try {
    await switchMandate(null)
    router.push('/')
  } catch {
    error.value = t('users.select_mandate.error')
  } finally {
    loading.value = false
  }
}

async function selectMandate(id: number) {
  loading.value = true
  error.value = null
  try {
    await switchMandate(id)
    router.push('/')
  } catch {
    error.value = t('users.select_mandate.error')
  } finally {
    loading.value = false
  }
}

const profileLabel = (niveau: number) => {
  return t(`users.profiles.${niveau}`)
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-lg">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-primary-600">KPI Admin</h1>
        <p class="mt-2 text-header-600">{{ t('users.select_mandate.title') }}</p>
        <p class="mt-1 text-sm text-header-500">
          {{ t('users.select_mandate.subtitle', { name: authStore.user?.name }) }}
        </p>
      </div>

      <!-- Error -->
      <div
        v-if="error"
        class="mb-4 p-3 bg-danger-50 border border-danger-200 rounded-lg text-danger-700 text-sm"
      >
        {{ error }}
      </div>

      <div class="space-y-3">
        <!-- Base profile option -->
        <button
          :disabled="loading"
          class="w-full text-left p-4 bg-white rounded-lg shadow hover:shadow-md border-2 border-transparent hover:border-primary-500 transition-all disabled:opacity-50"
          @click="selectBase"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold text-header-900">
                {{ t('users.select_mandate.base_profile') }}
              </div>
              <div class="text-sm text-header-500 mt-0.5">
                {{ profileLabel(authStore.user?.profile ?? 99) }}
              </div>
            </div>
            <UIcon name="i-heroicons-user-circle" class="w-8 h-8 text-header-400" />
          </div>
        </button>

        <!-- Mandate options -->
        <button
          v-for="mandate in authStore.mandates"
          :key="mandate.id"
          :disabled="loading"
          class="w-full text-left p-4 bg-white rounded-lg shadow hover:shadow-md border-2 border-transparent hover:border-primary-500 transition-all disabled:opacity-50"
          @click="selectMandate(mandate.id)"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="font-semibold text-header-900">{{ mandate.libelle }}</div>
              <div class="text-sm text-header-500 mt-0.5">
                {{ profileLabel(mandate.niveau) }}
              </div>
              <div v-if="mandate.filters.seasons?.length || mandate.filters.competitions?.length" class="text-xs text-header-400 mt-1">
                <span v-if="mandate.filters.seasons?.length">
                  {{ t('users.select_mandate.seasons') }}: {{ mandate.filters.seasons.join(', ') }}
                </span>
                <span v-if="mandate.filters.seasons?.length && mandate.filters.competitions?.length"> · </span>
                <span v-if="mandate.filters.competitions?.length">
                  {{ t('users.select_mandate.competitions') }}: {{ mandate.filters.competitions.join(', ') }}
                </span>
              </div>
            </div>
            <UIcon name="i-heroicons-identification" class="w-8 h-8 text-primary-400" />
          </div>
        </button>
      </div>

      <!-- Loading indicator -->
      <div v-if="loading" class="mt-4 text-center text-sm text-header-500">
        {{ t('users.select_mandate.loading') }}
      </div>
    </div>
  </div>
</template>
