import { computed } from 'vue'
import { useGameStore } from '~/stores/gameStore'
import { useChartStore } from '~/stores/chartStore'
import { useEventStore } from '~/stores/eventStore'

export const useLoadingState = () => {
  const gameStore = useGameStore()
  const chartStore = useChartStore()
  const eventStore = useEventStore()

  const isLoading = computed(() => {
    return gameStore.loading || chartStore.loading || eventStore.loading
  })

  return {
    isLoading
  }
}
