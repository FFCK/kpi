<script setup lang="ts">
import type { TeamDetail } from '~/types/clubs'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const route = useRoute()

const numero = computed(() => Number(route.params.numero))
const team = ref<TeamDetail | null>(null)
const loading = ref(true)

async function loadTeam() {
  loading.value = true
  try {
    team.value = await api.get<TeamDetail>(`/admin/teams/${numero.value}`)
  } catch {
    // useApi already shows toast
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadTeam()
})
</script>

<template>
  <div>
    <!-- Back link -->
    <div class="mb-4">
      <NuxtLink
        :to="team ? `/clubs?code=${team.codeClub}` : '/clubs'"
        class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800"
      >
        <UIcon name="i-heroicons-arrow-left" class="w-4 h-4" />
        {{ t('clubs.teams.back_to_club') }}
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center gap-2 text-gray-400">
      <UIcon name="i-heroicons-arrow-path" class="w-5 h-5 animate-spin" />
      <span>{{ t('common.loading') }}</span>
    </div>

    <template v-else-if="team">
      <!-- Header -->
      <div class="bg-white border border-gray-200 rounded-lg p-5 mb-4">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
          {{ team.libelle }}
        </h1>

        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
          <!-- Club -->
          <div class="flex items-center gap-1.5">
            <UIcon name="i-heroicons-building-office-2" class="w-4 h-4 text-gray-400" />
            <span class="font-medium">{{ t('clubs.teams.club') }} :</span>
            <NuxtLink :to="`/clubs?code=${team.codeClub}`" class="text-blue-600 hover:text-blue-800">
              {{ team.libelleClub }} ({{ team.codeClub }})
            </NuxtLink>
          </div>

          <!-- Colors -->
          <div v-if="team.color1 || team.color2" class="flex items-center gap-1.5">
            <span class="font-medium">{{ t('clubs.teams.colors') }} :</span>
            <span
              v-if="team.color1"
              class="inline-block w-5 h-5 rounded border border-gray-300"
              :style="{ backgroundColor: team.color1 }"
              :title="team.color1"
            />
            <span
              v-if="team.color2"
              class="inline-block w-5 h-5 rounded border border-gray-300"
              :style="{ backgroundColor: team.color2 }"
              :title="team.color2"
            />
            <span
              v-if="team.colortext"
              class="inline-block w-5 h-5 rounded border border-gray-300 text-[10px] leading-5 text-center font-bold"
              :style="{ backgroundColor: team.colortext }"
              title="Text color"
            >
              A
            </span>
          </div>
        </div>
      </div>

      <!-- Competitions -->
      <div class="bg-white border border-gray-200 rounded-lg p-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">
          {{ t('clubs.teams.competitions') }}
          <span v-if="team.competitions.length > 0" class="text-sm font-normal text-gray-400">
            ({{ team.competitions.length }})
          </span>
        </h2>

        <p v-if="team.competitions.length === 0" class="text-sm text-gray-400 italic">
          {{ t('clubs.teams.no_competitions') }}
        </p>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="text-left py-2 px-3 font-medium text-gray-700">{{ t('clubs.teams.season') }}</th>
                <th class="text-left py-2 px-3 font-medium text-gray-700">{{ t('clubs.teams.competition') }}</th>
                <th class="text-left py-2 px-3 font-medium text-gray-700">{{ t('clubs.teams.team_name') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(comp, idx) in team.competitions"
                :key="`${comp.codeSaison}-${comp.codeCompet}`"
                :class="idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                class="border-b border-gray-100"
              >
                <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ comp.codeSaison }}</td>
                <td class="py-2 px-3 text-gray-900">{{ comp.libelleCompet || comp.codeCompet }}</td>
                <td class="py-2 px-3 text-gray-600">{{ comp.libelleEquipe }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
