import { ref, computed } from 'vue'
import { useGameStore } from '~/stores/gameStore'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useApi } from '~/composables/useApi'
import dayjs from 'dayjs'

export const useGames = () => {
  const gameStore = useGameStore()
  const preferenceStore = usePreferenceStore()
  const { getApi } = useApi()
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl

  const games = computed(() => gameStore.games)
  const gamesCount = computed(() => gameStore.games.length)
  const filteredGames = ref([])
  const filteredGamesCount = computed(() => filteredGames.value.reduce((acc, group) => acc + group.filtered.length, 0))

  const categories = ref([])
  const game_dates = ref([])
  const teams = ref([])
  const refs = ref([])

  const showRefs = ref(true)
  const showFlags = ref(true)

  const fav_categories = ref([])
  const fav_teams = ref([])
  const fav_dates = ref('')

  const visibleButton = ref(true)

  const loadGames = async () => {
    if (!preferenceStore.preferences.lastEvent) return
    gameStore.loading = true
    visibleButton.value = false
    setTimeout(() => {
      visibleButton.value = true
    }, 3000)
    try {
      const response = await getApi(`${apiBaseUrl}/games/${preferenceStore.preferences.lastEvent.id}`)
      const data = await response.json()
      const gamelist = data.map(game => {
        game.g_score_a = game.g_score_a?.replace('?', '') || game.g_score_a
        game.g_score_b = game.g_score_b?.replace('?', '') || game.g_score_b
        game.g_score_detail_a = parseInt(game.g_score_detail_a) || 0
        game.g_score_detail_b = parseInt(game.g_score_detail_b) || 0
        game.r_1 = game.r_1 && game.r_1 !== '-1' ? game.r_1.replace(/\) (INT-|NAT-|REG-|INT|REG|OTM|JO)[ABCS]{0,1}/, ')') : null
        game.r_2 = game.r_2 && game.r_2 !== '-1' ? game.r_2.replace(/\) (INT-|NAT-|REG-|INT|REG|OTM|JO)[ABCS]{0,1}/, ')') : null
        game.t_a_label ??= gameEncode(game.g_code, 1)
        game.t_b_label ??= gameEncode(game.g_code, 2)
        game.r_1 ??= gameEncode(game.g_code, 3)
        game.r_2 ??= gameEncode(game.g_code, 4)
        return game
      })
      await gameStore.clearAndUpdateGames(gamelist)
      loadCategories()
      filterGames()
    } catch (error) {
      gameStore.error = error
      console.error('Failed to load games:', error)
    } finally {
      gameStore.loading = false
    }
  }

  const loadCategories = () => {
    const allGames = [...gameStore.games]
    categories.value = [...new Set(allGames.map(x => x.c_code))].sort()
    game_dates.value = [...new Set(allGames.map(x => x.g_date))].sort()
    teams.value = [
      ...new Set(
        allGames
          .map(x => (x.t_a_label && x.t_a_label[0] !== '¤' ? x.t_a_label : null))
          .concat(allGames.map(x => (x.t_b_label && x.t_b_label[0] !== '¤' ? x.t_b_label : null)))
      )
    ]
      .filter(value => value !== null)
      .sort()
    refs.value = [
      ...new Set(allGames.map(x => x.r_1_name).concat(allGames.map(x => x.r_2_name)))
    ]
      .filter(value => value !== null)
      .sort()
  }

  const getFav = async () => {
    await preferenceStore.fetchItems()
    fav_categories.value = JSON.parse(preferenceStore.preferences.fav_categories || '[]')
    fav_teams.value = JSON.parse(preferenceStore.preferences.fav_teams || '[]')
    fav_dates.value = preferenceStore.preferences.fav_dates || ''
    showFlags.value = preferenceStore.preferences.show_flags ?? true
    filterGames()
  }

  const changeFav = async () => {
    await preferenceStore.putItem('fav_categories', JSON.stringify(fav_categories.value))
    await preferenceStore.putItem('fav_teams', JSON.stringify(fav_teams.value))
    await preferenceStore.putItem('fav_dates', fav_dates.value)
    await preferenceStore.putItem('show_flags', showFlags.value)
    filterGames()
  }

  const filterGames = () => {
    let newFilteredGames = [...gameStore.games]
    if (fav_teams.value.length > 0) {
      newFilteredGames = newFilteredGames.filter(game => {
        return (
          fav_teams.value.includes(game.t_a_label) ||
          fav_teams.value.includes(game.t_b_label) ||
          (game.r_1 && fav_teams.value.includes(game.r_1.split(' (')[0])) ||
          (game.r_2 && fav_teams.value.includes(game.r_2.split(' (')[0])) ||
          (game.r_1 && fav_teams.value.includes(game.r_1.split('(').pop().split(')')[0])) ||
          (game.r_2 && fav_teams.value.includes(game.r_2.split('(').pop().split(')')[0])) ||
          fav_teams.value.includes(game.r_1_name) ||
          fav_teams.value.includes(game.r_2_name)
        )
      })
    }

    switch (fav_dates.value) {
      case '':
        break
      case 'Next':
        newFilteredGames = newFilteredGames.filter(value => value.g_status !== 'END')
        break
      case 'Prev':
        newFilteredGames = newFilteredGames.filter(value => value.g_status !== 'ATT')
        break
      case 'Today':
        newFilteredGames = newFilteredGames.filter(value => value.g_date === dayjs().format('YYYY-MM-DD'))
        break
      case 'Tomorrow':
        newFilteredGames = newFilteredGames.filter(
          value => value.g_date === dayjs().add(1, 'day').format('YYYY-MM-DD')
        )
        break
      default:
        newFilteredGames = newFilteredGames.filter(value => value.g_date === fav_dates.value)
        break
    }

    if (fav_categories.value.length > 0) {
      newFilteredGames = newFilteredGames.filter(value => fav_categories.value.includes(value.c_code))
    }

    newFilteredGames = newFilteredGames.map(value => {
      value.t_a_label = fav_teams.value.includes(value.t_a_label) ? `<mark>${value.t_a_label}</mark>` : value.t_a_label
      value.t_b_label = fav_teams.value.includes(value.t_b_label) ? `<mark>${value.t_b_label}</mark>` : value.t_b_label
      if (value.r_1) {
        value.r_1 = fav_teams.value.includes(value.r_1.split(' (')[0]) ? value.r_1.replace(value.r_1.split(' (')[0], `<mark>${value.r_1.split(' (')[0]}</mark>`) : value.r_1
        value.r_1 = fav_teams.value.includes(value.r_1.split('(').pop().split(')')[0]) ? value.r_1.replace(value.r_1.split('(').pop().split(')')[0], `<mark>${value.r_1.split('(').pop().split(')')[0]}</mark>`) : value.r_1
        value.r_1 = fav_teams.value.includes(value.r_1_name) ? value.r_1.replace(value.r_1.split(' (')[0], `<mark>${value.r_1.split(' (')[0]}</mark>`) : value.r_1
      }
      if (value.r_2) {
        value.r_2 = fav_teams.value.includes(value.r_2.split(' (')[0]) ? value.r_2.replace(value.r_2.split(' (')[0], `<mark>${value.r_2.split(' (')[0]}</mark>`) : value.r_2
        value.r_2 = fav_teams.value.includes(value.r_2.split('(').pop().split(')')[0]) ? value.r_2.replace(value.r_2.split('(').pop().split(')')[0], `<mark>${value.r_2.split('(').pop().split(')')[0]}</mark>`) : value.r_2
        value.r_2 = fav_teams.value.includes(value.r_2_name) ? value.r_2.replace(value.r_2.split(' (')[0], `<mark>${value.r_2.split(' (')[0]}</mark>`) : value.r_2
      }
      return value
    })

    const filteredGamesDates = [...new Set(newFilteredGames.map(x => x.g_date))]
    const newGames = []
    filteredGamesDates.forEach(goupDate => {
      const filtered = newFilteredGames.filter(
        value => value.g_date === goupDate
      )
      newGames.push({
        goupDate: goupDate,
        filtered: filtered
      })
    })
    filteredGames.value = newGames
  }

  const gameEncode = (gameCode, codeNumber) => {
    const readCode = gameCode ? gameCode.split(/[\[\]]/)[1].split(/[-/*,;]/g)[codeNumber - 1] : null
    if (!readCode) {
      return null
    }
    const resultLetter = readCode.match(/([A-Z]+)/)[0]
    const resultNumberArray = readCode.match(/([0-9]+)/)
    const resultNumber = resultNumberArray[0]
    const resultNumberIndex = resultNumberArray.index
    if (resultNumberIndex === 0) {
      return '¤|' + resultNumber + '|Group|' + resultLetter
    }

    let result
    switch (resultLetter) {
      case 'W': // Winner
      case 'V': // Vainqueur
      case 'G': // Gagnant
        result = '¤||Winner|' + resultNumber
        break
      case 'L': // Looser
      case 'P': // Perdant
        result = '¤||Looser|' + resultNumber
        break
      case 'D': // Draw
      case 'T': // Tirage
        result = '¤||Team|' + resultNumber
        break
      default:
        result = null
        break
    }
    return result
  }

  return {
    games,
    gamesCount,
    filteredGames,
    filteredGamesCount,
    categories,
    game_dates,
    teams,
    refs,
    showRefs,
    showFlags,
    fav_categories,
    fav_teams,
    fav_dates,
    visibleButton,
    loadGames,
    getFav,
    changeFav
  }
}
