<script setup lang="ts">
import type { SchemaCompetition } from '~/types/schema'

const props = defineProps<{
  competition: SchemaCompetition
  totalMatches: number
  showMatchCount: boolean
  showTimeSlots: boolean
  isCp: boolean
}>()

const { t } = useI18n()
const config = useRuntimeConfig()
const legacyBase = config.public.legacyBaseUrl as string

// Image visibility (hidden on 404)
const showBandeau = ref(true)
const showLogo = ref(true)
const showSponsor = ref(true)

const imageUrl = (link: string | null) => {
  if (!link) return ''
  return `${legacyBase}${link}`
}

const title = computed(() => {
  let s = props.competition.libelle
  if (props.competition.soustitre2) {
    s += ' - ' + props.competition.soustitre2
  }
  return s
})

const hasImages = computed(() => {
  return (props.competition.bandeauActif && props.competition.bandeauLink && showBandeau.value)
    || (props.competition.logoActif && props.competition.logoLink && showLogo.value)
    || (props.competition.sponsorActif && props.competition.sponsorLink && showSponsor.value)
})
</script>

<template>
  <div class="mb-4 bg-white rounded-lg shadow">
    <!-- Images zone (printable) -->
    <div
      v-if="hasImages"
      class="flex flex-wrap items-center justify-center gap-6 px-6 py-4 bg-gray-50 border-b"
    >
      <img
        v-if="competition.bandeauActif && competition.bandeauLink && showBandeau"
        :src="imageUrl(competition.bandeauLink)"
        :alt="competition.libelle"
        class="max-h-20 object-contain"
        @error="showBandeau = false"
      >
      <img
        v-if="competition.logoActif && competition.logoLink && showLogo"
        :src="imageUrl(competition.logoLink)"
        :alt="competition.libelle"
        class="max-h-16 object-contain"
        @error="showLogo = false"
      >
      <img
        v-if="competition.sponsorActif && competition.sponsorLink && showSponsor"
        :src="imageUrl(competition.sponsorLink)"
        alt="Sponsor"
        class="max-h-16 object-contain"
        @error="showSponsor = false"
      >
    </div>

    <!-- Title and info -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
      <!-- Title -->
      <h2 class="text-lg font-semibold text-gray-900">
        {{ title }}
        <!-- Season badge -->
        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-50 text-blue-700">
          {{ competition.season }}
        </span>
      </h2>

      <!-- Game count badge -->
      <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
        {{ t('schema.games_count', { count: totalMatches }, totalMatches) }}
      </span>
    </div>
  </div>
</template>
