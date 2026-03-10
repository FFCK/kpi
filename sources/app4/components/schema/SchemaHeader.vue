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
const showLogo = ref(true)

const imageUrl = (link: string | null) => {
  if (!link) return ''
  return `${legacyBase}${link}`
}

const hasLogo = computed(() => props.competition.logoActif && props.competition.logoLink && showLogo.value)

// Badge helpers
const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-primary-100 text-primary-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-header-100 text-header-800'
  }
}
</script>

<template>
  <div class="mb-4 bg-white rounded-lg shadow">
    <!-- Title and info -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
      <!-- Logo + Title -->
      <div class="flex items-center gap-3">
        <img
          v-if="hasLogo"
          :src="imageUrl(competition.logoLink)"
          :alt="competition.libelle"
          class="max-h-16 object-contain"
          @error="showLogo = false"
        >
        <div>
          <h2 class="text-lg font-semibold text-header-900">
            {{ competition.libelle }}
            <!-- Season badge -->
            <span class="px-2 py-1 text-xs font-medium rounded bg-primary-50 text-primary-700">
              {{ competition.season }}
            </span>
          </h2>
          <p v-if="competition.soustitre2" class="text-sm text-header-700">
            {{ competition.soustitre2 }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <span
          class="px-2 py-1 text-xs font-medium rounded uppercase"
          :class="getLevelColor(competition.codeNiveau)"
        >
          {{ competition.codeNiveau }}
        </span>
        <span class="px-2 py-1 text-xs font-medium rounded uppercase bg-header-100 text-header-800">
          {{ competition.codeTypeclt }}
        </span>
        <!-- Game count badge -->
        <span class="px-2 py-1 text-xs font-medium rounded bg-header-100 text-header-700">
          {{ t('schema.games_count', { count: totalMatches }, totalMatches) }}
        </span>
      </div>
    </div>
  </div>
</template>
