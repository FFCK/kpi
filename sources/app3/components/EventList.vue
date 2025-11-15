<template>
  <div>
    <div v-if="events.length === 0" class="text-center text-gray-400 py-8">
      {{ t('events.events') }} - {{ t('common.loading') }}
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-700">
            <th class="text-left p-2">{{ t('match.period') }}</th>
            <th class="text-left p-2">{{ t('events.eventTime') }}</th>
            <th class="text-left p-2">{{ t('match.teamA') }}</th>
            <th class="text-left p-2">{{ t('match.teamB') }}</th>
            <th class="text-right p-2">{{ t('common.delete') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(event, index) in events"
            :key="index"
            class="border-b border-gray-700 hover:bg-gray-700"
          >
            <td class="p-2">
              <span class="px-2 py-1 bg-gray-600 rounded text-xs">
                {{ event.period }}
              </span>
            </td>
            <td class="p-2 font-mono">{{ event.time }}</td>

            <!-- Team A Events -->
            <td class="p-2">
              <div v-if="event.team === 'A'" class="flex items-center gap-2">
                <EventIcon :type="event.eventType" />
                <span v-if="event.playerNumber">{{ event.playerNumber }} -</span>
                <span>{{ event.playerName }}</span>
                <span v-if="event.reason" class="text-xs text-gray-400">
                  ({{ t(`events.reasons.${event.reason}`) }})
                </span>
              </div>
            </td>

            <!-- Team B Events -->
            <td class="p-2">
              <div v-if="event.team === 'B'" class="flex items-center gap-2">
                <EventIcon :type="event.eventType" />
                <span v-if="event.playerNumber">{{ event.playerNumber }} -</span>
                <span>{{ event.playerName }}</span>
                <span v-if="event.reason" class="text-xs text-gray-400">
                  ({{ t(`events.reasons.${event.reason}`) }})
                </span>
              </div>
            </td>

            <td class="p-2 text-right">
              <button
                @click="$emit('deleteEvent', index)"
                class="text-red-400 hover:text-red-300 px-2"
              >
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import type { MatchEvent } from '~/utils/db'

defineProps<{
  events: MatchEvent[]
}>()

defineEmits<{
  deleteEvent: [index: number]
}>()

const { t } = useI18n()
</script>
