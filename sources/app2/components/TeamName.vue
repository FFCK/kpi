<template>
  <NuxtLink
    v-if="canLink && teamLabel && teamLabel[0] !== '¤'"
    :to="`/team/${encodeURIComponent(teamLabel)}`"
    :class="[
      teamBlockClass,
      'px-2 py-1 rounded hover:opacity-80 cursor-pointer inline-block'
    ]"
    v-html="displayName"
  />
  <span
    v-else
    :class="[
      teamBlockClass,
      'px-2 py-1 rounded inline-block'
    ]"
    v-html="displayName"
  />
</template>

<script setup>
import { computed } from 'vue'
import { useGameDisplay } from '~/composables/useGameDisplay'
import { useI18n } from 'vue-i18n'

const { teamNameResize } = useGameDisplay()
const { t } = useI18n()

const props = defineProps({
  teamLabel: {
    type: String,
    default: ''
  },
  isWinner: {
    type: Boolean,
    default: false
  },
  isHighlighted: {
    type: Boolean,
    default: false
  },
  canLink: {
    type: Boolean,
    default: true
  }
})

// Display team name with proper decoding
const displayName = computed(() => {
  if (!props.teamLabel) return ''

  // Handle encoded team names (starting with ¤)
  if (props.teamLabel[0] === '¤') {
    const parts = props.teamLabel.split('|')
    if (parts.length === 4) {
      const [, number, type, extra] = parts
      if (type === 'Group') {
        return teamNameResize(`${t('Games.Code.Group')}${extra}`)
      } else if (type === 'Winner') {
        return teamNameResize(`${t('Games.Code.Winner')}${extra}`)
      } else if (type === 'Looser') {
        return teamNameResize(`${t('Games.Code.Looser')}${extra}`)
      } else if (type === 'Team') {
        return teamNameResize(`${t('Games.Code.Team')}${extra}`)
      }
    }
  }

  return teamNameResize(props.teamLabel)
})

// Team block styling with cumulative options
const teamBlockClass = computed(() => {
  // Highlighted team (selected/filtered)
  if (props.isHighlighted) {
    return {
      'bg-yellow-400': true,
      'text-black': true,
      'font-bold': props.isWinner, // Bold only if also winner
      'border': props.isWinner,
      'border-black': props.isWinner
    }
  }

  // Winner team (not highlighted)
  if (props.isWinner) {
    return {
      'bg-gray-800': true,
      'text-white': true,
      'font-bold': true
    }
  }

  // Default (other cases)
  return {
    'bg-gray-200': true,
    'text-black': true
  }
})
</script>
