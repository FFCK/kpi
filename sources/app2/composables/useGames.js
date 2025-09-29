import { ref, computed } from 'vue'
import { useGameStore } from '~/stores/gameStore'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useApi } from '~/composables/useApi'
import { useIndexedDB } from '~/composables/useIndexedDB'
import dayjs from 'dayjs'

export const useGames = () => {
  const gameStore = useGameStore()
  const preferenceStore = usePreferenceStore()
  const { getApi } = useApi()
  const { saveGames, loadGames: loadGamesFromDB, clearOldGames } = useIndexedDB()
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

    const eventId = preferenceStore.preferences.lastEvent.id
    gameStore.loading = true
    visibleButton.value = false
    setTimeout(() => {
      visibleButton.value = true
    }, 3000)

    try {
      // Essayer de charger depuis IndexedDB d'abord
      const cachedGames = await loadGamesFromDB(eventId)
      if (cachedGames && cachedGames.length > 0) {
        console.log('Loading games from IndexedDB cache')
        const gamelist = processGameData(cachedGames)
        await gameStore.clearAndUpdateGames(gamelist)
        loadCategories()
        filterGames()
      }

      // Charger depuis l'API en arrière-plan
      try {
        console.log('Loading games from API')
        const response = await getApi(`${apiBaseUrl}/games/${eventId}`)
        const data = await response.json()
        const gamelist = processGameData(data)

        // Sauvegarder dans IndexedDB
        await saveGames(eventId, data)

        // Mettre à jour l'interface seulement si les données ont changé
        if (JSON.stringify(gamelist) !== JSON.stringify(gameStore.games)) {
          await gameStore.clearAndUpdateGames(gamelist)
          loadCategories()
          filterGames()
        }
      } catch (apiError) {
        console.error('Failed to load games from API, using cached data:', apiError)
        if (!cachedGames) {
          gameStore.error = apiError
        }
      }

      // Nettoyer les anciennes données
      await clearOldGames()
    } catch (error) {
      gameStore.error = error
      console.error('Failed to load games:', error)
    } finally {
      gameStore.loading = false
    }
  }

  const processGameData = (data) => {
    return data.map(game => {
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
  }

  const loadCategories = () => {
    const allGames = [...gameStore.games]
    categories.value = [...new Set(allGames.map(x => x.c_label))].sort()
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
      newFilteredGames = newFilteredGames.filter(value => fav_categories.value.includes(value.c_label))
    }

    newFilteredGames = newFilteredGames.map(value => {
      const newValue = { ...value };

      // Équipes : ajouter des propriétés pour le highlighting côté composant
      newValue.t_a_highlighted = fav_teams.value.includes(value.t_a_label)
      newValue.t_b_highlighted = fav_teams.value.includes(value.t_b_label)

      // Arbitres : surlignage jaune (mark) - cherche le nom avec plusieurs stratégies (insensible à la casse)
      if (value.r_1) {
        // Essayer plusieurs stratégies de correspondance (case-insensitive)
        const r1Full = value.r_1 || ''
        const r1Name = value.r_1_name || ''
        const r1Short = r1Full.split(' (')[0]

        // Trouver la correspondance en comparant toutes les variations possibles
        let matchingFilterName = null
        let nameToHighlight = null

        for (const filterName of fav_teams.value) {
          // Comparaisons insensibles à la casse et aux espaces
          const filterNameClean = filterName.toLowerCase().replace(/\s+/g, ' ').trim()
          const r1NameClean = r1Name.toLowerCase().replace(/\s+/g, ' ').trim()
          const r1ShortClean = r1Short.toLowerCase().replace(/\s+/g, ' ').trim()
          const r1FullClean = r1Full.toLowerCase().replace(/\s+/g, ' ').trim()

          if (filterNameClean === r1NameClean ||
              filterNameClean === r1ShortClean ||
              filterNameClean === r1FullClean) {
            matchingFilterName = filterName
            // Utiliser le nom le plus approprié pour le remplacement
            if (filterNameClean === r1NameClean) {
              nameToHighlight = r1Name
            } else if (filterNameClean === r1ShortClean) {
              nameToHighlight = r1Short
            } else {
              nameToHighlight = r1Short // par défaut
            }
            break
          }
        }

        if (matchingFilterName && nameToHighlight) {
          // Utiliser une regex insensible à la casse pour le remplacement
          const regex = new RegExp(nameToHighlight.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i')
          newValue.r_1 = r1Full.replace(regex, `<mark>$&</mark>`)
        } else {
          newValue.r_1 = r1Full
        }
      }
      if (value.r_2) {
        // Essayer plusieurs stratégies de correspondance (case-insensitive)
        const r2Full = value.r_2 || ''
        const r2Name = value.r_2_name || ''
        const r2Short = r2Full.split(' (')[0]

        // Trouver la correspondance en comparant toutes les variations possibles
        let matchingFilterName = null
        let nameToHighlight = null

        for (const filterName of fav_teams.value) {
          // Comparaisons insensibles à la casse et aux espaces
          const filterNameClean = filterName.toLowerCase().replace(/\s+/g, ' ').trim()
          const r2NameClean = r2Name.toLowerCase().replace(/\s+/g, ' ').trim()
          const r2ShortClean = r2Short.toLowerCase().replace(/\s+/g, ' ').trim()
          const r2FullClean = r2Full.toLowerCase().replace(/\s+/g, ' ').trim()

          if (filterNameClean === r2NameClean ||
              filterNameClean === r2ShortClean ||
              filterNameClean === r2FullClean) {
            matchingFilterName = filterName
            // Utiliser le nom le plus approprié pour le remplacement
            if (filterNameClean === r2NameClean) {
              nameToHighlight = r2Name
            } else if (filterNameClean === r2ShortClean) {
              nameToHighlight = r2Short
            } else {
              nameToHighlight = r2Short // par défaut
            }
            break
          }
        }

        if (matchingFilterName && nameToHighlight) {
          // Utiliser une regex insensible à la casse pour le remplacement
          const regex = new RegExp(nameToHighlight.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i')
          newValue.r_2 = r2Full.replace(regex, `<mark>$&</mark>`)
        } else {
          newValue.r_2 = r2Full
        }
      }

      return newValue
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

  const resetAllFilters = async () => {
    fav_categories.value = []
    fav_teams.value = []
    fav_dates.value = ''
    showFlags.value = true
    await preferenceStore.putItem('fav_categories', '[]')
    await preferenceStore.putItem('fav_teams', '[]')
    await preferenceStore.putItem('fav_dates', '')
    await preferenceStore.putItem('show_flags', true)
    filterGames()
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
    changeFav,
    resetAllFilters
  }
}
