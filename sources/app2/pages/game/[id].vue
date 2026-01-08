<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav hide-left hide-right>
      <template #left>
        <button
          @click="goBack"
          class="flex items-center gap-2 px-3 py-1 text-sm border-2 border-gray-300 rounded-md hover:bg-gray-100 transition-colors cursor-pointer"
        >
          <UIcon name="i-heroicons-arrow-left" class="h-4 w-4" />
          {{ t('MatchSheet.Back') }}
        </button>
      </template>
      <template #right>
        <div class="flex items-center gap-2">
          <button
            v-if="refreshButtonVisible"
            @click="handleRefresh"
            class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100 cursor-pointer"
            :title="t('MatchSheet.Refresh')"
          >
            <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
            <span class="hidden sm:inline text-sm">{{ t('MatchSheet.Refresh') }}</span>
          </button>
          <a
            v-if="matchData"
            :href="getPdfUrl()"
            target="_blank"
            class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100 cursor-pointer"
            :title="t('MatchSheet.DownloadPdf')"
          >
            <UIcon name="i-heroicons-document-arrow-down" class="h-6 w-6" />
            <span class="hidden sm:inline text-sm">{{ t('MatchSheet.DownloadPdf') }}</span>
          </a>
        </div>
      </template>
    </AppSecondaryNav>

    <div class="p-4">
      <GameSheet
        :match-data="matchData"
        :loading="loading"
        :error="error"
        :game-id="gameId"
        @refresh="handleRefresh"
      />
    </div>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '~/composables/useApi'
import GameSheet from '~/components/GameSheet.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { getApi } = useApi()

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl

const gameId = computed(() => route.params.id)
const matchData = ref(null)
const loading = ref(true)
const error = ref(null)
const refreshButtonVisible = ref(true)

// Page-specific SEO
const pageTitle = computed(() =>
  matchData.value
    ? `${matchData.value.team_a.label} vs ${matchData.value.team_b.label} - ${t('MatchSheet.Title')}`
    : t('MatchSheet.Title')
)

useSeoMeta({
  title: pageTitle,
  description: 'View match sheet details including team compositions, events timeline, and player statistics.',
  ogTitle: pageTitle,
  ogDescription: 'Match sheet with complete game details'
})

const getPdfUrl = () => {
  return `${baseUrl}/PdfMatchMulti.php?listMatch=${gameId.value}`
}

const goBack = () => {
  // Use browser history if available, otherwise go to games page
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/games')
  }
}

const loadMatchSheet = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await getApi(`/game-sheet/${gameId.value}`)

    if (response.ok) {
      matchData.value = await response.json()
    } else if (response.status === 404) {
      error.value = t('MatchSheet.NotFound')
    } else {
      error.value = t('MatchSheet.NotFound')
    }
  } catch (e) {
    console.error('Error loading game sheet:', e)
    error.value = t('MatchSheet.NotFound')
  } finally {
    loading.value = false
  }
}

const handleRefresh = () => {
  refreshButtonVisible.value = false
  loadMatchSheet()
  setTimeout(() => {
    refreshButtonVisible.value = true
  }, 5000)
}

onMounted(() => {
  loadMatchSheet()
})
</script>
