<script setup lang="ts">
import type { ControlPanel, TvMatchesResponse, TvGlobalFilters, TvMatch } from '~/types/tv'
import { PRESENTATIONS } from '~/types/tv'

const props = defineProps<{
  matchData: TvMatchesResponse | null
  globalFilters: TvGlobalFilters
}>()

const panel = defineModel<ControlPanel>({ required: true })

const { t } = useI18n()

const requiredParams = computed(() => {
  const pres = PRESENTATIONS.find(p => p.value === panel.value.presentation)
  return pres?.requiredParams ?? []
})

function needs(param: string): boolean {
  return requiredParams.value.includes(param)
}

// Filtered matches by competition + date
const filteredMatches = computed<TvMatch[]>(() => {
  if (!props.matchData) return []
  return props.matchData.matches.filter(m => {
    if (panel.value.competition && m.codeCompetition !== panel.value.competition) return false
    if (props.globalFilters.date && m.dateMatch !== props.globalFilters.date) return false
    return true
  })
})

function matchLabel(m: TvMatch): string {
  const num = m.numeroOrdre ? `#${m.numeroOrdre}` : ''
  return `${num} T${m.terrain} ${m.heureMatch} ${m.equipeA}-${m.equipeB}`
}
</script>

<template>
  <div class="flex flex-wrap items-end gap-3">
    <!-- Competition -->
    <div v-if="needs('competition')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.competition') }}</label>
      <select
        v-model="panel.competition"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white min-w-[120px]"
      >
        <option value="">—</option>
        <option v-for="c in matchData?.competitions ?? []" :key="c" :value="c">{{ c }}</option>
      </select>
    </div>

    <!-- Match -->
    <div v-if="needs('match')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.match') }}</label>
      <select
        v-model.number="panel.match"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white min-w-[280px]"
      >
        <option :value="null">—</option>
        <option v-for="m in filteredMatches" :key="m.id" :value="m.id">
          {{ matchLabel(m) }}
        </option>
      </select>
    </div>

    <!-- Team A/B -->
    <div v-if="needs('team')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.team') }}</label>
      <select
        v-model="panel.team"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="A">A</option>
        <option value="B">B</option>
      </select>
    </div>

    <!-- Team select (for frame_team) -->
    <div v-if="needs('teamSelect')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.team') }}</label>
      <select
        v-model.number="panel.teamSelect"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white min-w-[180px]"
      >
        <option :value="null">—</option>
        <option v-for="team in matchData?.teams ?? []" :key="team.idEquipe" :value="team.idEquipe">
          {{ team.libelleEquipe }}
        </option>
      </select>
    </div>

    <!-- Player number -->
    <div v-if="needs('number')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.player') }}</label>
      <AdminTvPlayerNumberGrid v-model="panel.number" />
    </div>

    <!-- Pitch -->
    <div v-if="needs('pitch')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.pitch') }}</label>
      <select
        v-model.number="panel.pitch"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option v-for="n in 8" :key="n" :value="n">{{ n }}</option>
      </select>
    </div>

    <!-- Pitchs (text) -->
    <div v-if="needs('pitchs')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.pitchs') }}</label>
      <input
        v-model="panel.pitchs"
        type="text"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white w-32"
        placeholder="1,2,3,4"
      >
    </div>

    <!-- Medal -->
    <div v-if="needs('medal')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.medal') }}</label>
      <select
        v-model="panel.medal"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="BRONZE">Bronze</option>
        <option value="SILVER">Silver</option>
        <option value="GOLD">Gold</option>
      </select>
    </div>

    <!-- Zone -->
    <div v-if="needs('zone')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.zone') }}</label>
      <select
        v-model="panel.zone"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="inter">Inter</option>
        <option value="club">Club</option>
      </select>
    </div>

    <!-- Mode -->
    <div v-if="needs('mode')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.mode') }}</label>
      <select
        v-model="panel.mode"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="full">Full</option>
        <option value="only">Only</option>
        <option value="event">Event</option>
        <option value="static">Static</option>
      </select>
    </div>

    <!-- Round -->
    <div v-if="needs('round')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.round') }}</label>
      <select
        v-model="panel.round"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="*">All</option>
        <option v-for="n in 8" :key="n" :value="String(n)">{{ n }}</option>
      </select>
    </div>

    <!-- Start -->
    <div v-if="needs('start')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.start') }}</label>
      <select
        v-model.number="panel.start"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option :value="0">1-10</option>
        <option :value="10">11-20</option>
        <option :value="20">21-30</option>
        <option :value="30">31-40</option>
      </select>
    </div>

    <!-- Animate -->
    <div v-if="needs('animate')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.animate') }}</label>
      <select
        v-model="panel.animate"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option :value="false">No</option>
        <option :value="true">Yes</option>
      </select>
    </div>

    <!-- Speaker -->
    <div v-if="needs('speaker')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.speaker') }}</label>
      <select
        v-model.number="panel.speaker"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option :value="0">{{ t('tv.speaker_options.no') }}</option>
        <option :value="1">{{ t('tv.speaker_options.yes') }}</option>
        <option :value="2">{{ t('tv.speaker_options.maybe') }}</option>
      </select>
    </div>

    <!-- Count -->
    <div v-if="needs('count')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.count') }}</label>
      <select
        v-model.number="panel.count"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option v-for="n in 4" :key="n" :value="n">{{ n }}</option>
      </select>
    </div>

    <!-- First game (lnStart) -->
    <div v-if="needs('lnStart')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.first_game') }}</label>
      <input
        v-model.number="panel.lnStart"
        type="number"
        min="1"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white w-20"
      >
    </div>

    <!-- Game count (lnLen) -->
    <div v-if="needs('lnLen')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.game_count') }}</label>
      <input
        v-model.number="panel.lnLen"
        type="number"
        min="1"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white w-20"
      >
    </div>

    <!-- Competitions (text) -->
    <div v-if="needs('competList')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.competitions') }}</label>
      <input
        v-model="panel.competList"
        type="text"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white w-40"
        placeholder="CMH,CMF"
      >
    </div>

    <!-- Format -->
    <div v-if="needs('format')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.format') }}</label>
      <select
        v-model="panel.format"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option value="json">JSON</option>
        <option value="csv">CSV</option>
      </select>
    </div>

    <!-- Option -->
    <div v-if="needs('option')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.option') }}</label>
      <select
        v-model.number="panel.option"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option :value="0">{{ t('tv.option_players.with_stats') }}</option>
        <option :value="1">{{ t('tv.option_players.all') }}</option>
        <option :value="2">{{ t('tv.option_players.without_stats') }}</option>
      </select>
    </div>

    <!-- Navbar -->
    <div v-if="needs('navGroup')" class="flex flex-col gap-1">
      <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.navbar') }}</label>
      <select
        v-model="panel.navGroup"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white"
      >
        <option :value="false">No</option>
        <option :value="true">Yes</option>
      </select>
    </div>
  </div>
</template>
