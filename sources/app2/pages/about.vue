<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #center>
        <h1 class="text-2xl font-bold text-gray-800">{{ t("nav.About") }}</h1>
      </template>
      <template #right></template>
    </AppSecondaryNav>

    <div class="max-w-3xl mx-auto px-4 py-6">
      <!-- Introduction Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
          <img src="/img/logo_kp.png" alt="KPI Logo" class="h-16 w-16" />
          <div>
            <h2 class="text-xl font-bold text-gray-800">KPI Application</h2>
            <p class="text-sm text-gray-600">Kayak-Polo.Info</p>
          </div>
        </div>
        <p class="text-gray-700 leading-relaxed">
          {{ t("About.DoYouLike") }}
        </p>
        <p class="text-gray-700 leading-relaxed mt-2">
          {{ t("About.IDevelopIt") }}
        </p>
      </div>

      <!-- Rating Card -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 text-center">{{ t("About.Rating") }}</h3>
        <Rating
          :thanks="thanks"
          :grade="stars"
          @rated="rated"
          :key="key"
          class="my-4"
        />
        <div v-if="currentRating" class="text-center">
          <p class="text-sm text-gray-700">
            <span class="font-semibold">{{ t("About.Average") }}</span>
            <span class="text-lg font-bold text-blue-600 mx-1">{{ currentRating }}/5</span>
          </p>
          <p class="text-xs text-gray-600 mt-1">
            {{ currentVoters }} {{ t("About.voters") }}
          </p>
        </div>
      </div>

      <!-- Support Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">{{ t("About.SupportMeOnKofi") }}</h3>
        <div class="flex justify-center">
          <a
            href="https://ko-fi.com/kayakpoloinfo"
            target="_blank"
            class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105"
          >
            <img alt="logo Ko-fi" :src="logoKofi" class="h-8" />
            <span class="font-semibold">Support on Ko-fi</span>
          </a>
        </div>
      </div>

      <!-- Contact Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ t("About.OwnCompetition") }}</h3>
        <p class="text-gray-700 mb-3">
          {{ t("About.ContactMe") }}
        </p>
        <div class="flex items-center gap-2">
          <UIcon name="i-heroicons-envelope" class="h-5 w-5 text-blue-600" />
          <a href="mailto:contact@kayak-polo.info" class="text-blue-600 hover:underline font-medium">
            contact@kayak-polo.info
          </a>
        </div>
      </div>

      <!-- Signature -->
      <div class="text-right">
        <p class="text-gray-600 font-serif italic text-lg">Laurent.</p>
      </div>
    </div>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import logoKofi from '~/public/img/kofi/logo-kofi.png'
import { usePreferenceStore } from '~/stores/preferenceStore'
import db from '~/utils/db'

// Composables & Stores
const { t } = useI18n()
const preferenceStore = usePreferenceStore()
const { getApi, postApi } = useApi()
const runtimeConfig = useRuntimeConfig()
const apiBaseUrl = runtimeConfig.public.apiBaseUrl

// State
const stars = ref(0)
const thanks = ref(false)
const key = ref(0) // To force re-render of the rating component
const currentRating = ref(null)
const currentVoters = ref(null)

// Methods
const getCurrentRating = async (force = false) => {
  try {
    // Vérifier si on doit charger depuis l'API
    const now = Date.now()
    const lastApiLoad = preferenceStore.preferences.stars_last_api_load || 0
    const fiveMinutes = 5 * 60 * 1000
    const shouldLoadFromApi = force || (now - lastApiLoad > fiveMinutes)

    let cachedStars = null

    // Charger depuis IndexedDB
    const starsData = await db.stars.get('rating')
    if (starsData && !force) {
      cachedStars = starsData
      currentRating.value = parseFloat(cachedStars.average).toFixed(2)
      currentVoters.value = cachedStars.count
    }

    // Charger depuis l'API uniquement si nécessaire
    if (shouldLoadFromApi || !cachedStars) {
      try {
        const result = await getApi(`/stars`)
        const data = await result.json()

        // Sauvegarder dans IndexedDB
        await db.stars.put({
          id: 'rating',
          average: data.average,
          count: data.count,
          timestamp: now
        })

        // Sauvegarder la date de chargement API
        await preferenceStore.putItem('stars_last_api_load', now)

        currentRating.value = parseFloat(data.average).toFixed(2)
        currentVoters.value = data.count
      } catch (apiError) {
        console.error('Failed to get current rating from API, using cached data:', apiError)
        if (!cachedStars) {
          throw apiError
        }
      }
    }
  } catch (error) {
    console.error('Failed to get current rating:', error)
  }
}

const rated = async (newStars) => {
  thanks.value = true
  stars.value = newStars
  key.value++

  try {
    const uid = preferenceStore.preferences.uid
    if (!uid) {
      console.error('User ID (uid) not found in preferences after init.')
      return
    }

    // Assuming a postApi composable or similar for POST requests
    const result = await postApi(`${apiBaseUrl}/rating`, { uid, stars: newStars })

    if (result.ok) {
      await preferenceStore.putItem('stars', newStars)
      // Recharger les stats depuis l'API après un vote
      await getCurrentRating(true)
    }
  } catch (error) {
    console.error('Failed to post rating:', error)
  }
}
// Lifecycle Hooks
onMounted(async () => {
  await preferenceStore.fetchItems()
  await preferenceStore.initUid()
  
  // Init stars from preferences
  stars.value = preferenceStore.preferences.stars || 0
  key.value++

  getCurrentRating()
})
</script>
