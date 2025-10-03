<template>
  <div class="container-fluid">
    <AppSecondaryNav>
      <template #left></template>
      <template #right></template>
    </AppSecondaryNav>

    <div class="px-4 py-2">

    <!-- Main Content -->
    <div class="text-sm">
      <p class="my-3">
        {{ t("About.DoYouLike") }}
        <br />
        {{ t("About.IDevelopIt") }}
      </p>

      <div class="mb-3">
        {{ t("About.Rating") }}
        <Rating
          :thanks="thanks"
          :grade="stars"
          @rated="rated"
          :key="key"
          class="text-center my-2"
        />
        <div v-if="currentRating" class="text-center italic text-xs text-gray-600">
          <i>{{ t("About.Average") }} {{ currentRating }}/5 ({{ currentVoters }} {{ t("About.voters") }})</i>
        </div>
      </div>

      <p class="my-3 flex items-center space-x-2">
        <span>{{ t("About.FeedbackOnTwitter") }}</span>
        <a
          class="inline-flex items-center space-x-1 px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
          href="https://twitter.com/kayakpolo_info"
          target="blank"
        >
          <UIcon name="i-bi-twitter" />
          <span>Twitter</span>
        </a>
      </p>
      <p class="my-3">
        <span>{{ t("About.SupportMeOnUtip") }}</span>
        <a
          href="https://utip.io/kayakpoloinfo"
          target="blank"
          class="inline-block align-middle ml-2"
        >
          <img alt="logo uTip" :src="logoUtip" class="h-12 inline-block" />
          <img alt="Dablicorne" :src="dablicorneUtip" class="h-12 inline-block" />
        </a>
      </p>
    </div>
    <hr class="my-3"/>
    <p class="text-sm">
      {{ t("About.OwnCompetition") }}
      <br />
      {{ t("About.HelpMe") }}
      <br />
      {{ t("About.ContactMe") }}
      <a href="mailto:contact@kayak-polo.info" class="text-blue-600 hover:underline">contact@kayak-polo.info</a>
    </p>
    <p class="text-right mt-4 mr-5 font-serif">
      Laurent.
    </p>
    </div>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import logoUtip from '~/public/img/utip/logo-utip.png'
import dablicorneUtip from '~/public/img/utip/dablicorne-utip.png'
import { usePreferenceStore } from '~/stores/preferenceStore'

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
const getCurrentRating = async () => {
  try {
    const result = await getApi(`${apiBaseUrl}/stars`)
    const data = await result.json()
    currentRating.value = parseFloat(data.average).toFixed(2)
    currentVoters.value = data.count
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
