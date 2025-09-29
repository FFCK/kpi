import db from '~/utils/db'

export const useIndexedDB = () => {
  const saveGames = async (eventId, games) => {
    try {
      await db.games.put({
        eventId: eventId,
        games: games,
        timestamp: Date.now()
      })
      // console.log(`Games saved to Dexie for event ${eventId}`)
    } catch (error) {
      console.error('Failed to save games to Dexie:', error)
    }
  }

  const loadGames = async (eventId) => {
    try {
      const result = await db.games.get(eventId)
      if (result && result.games) {
        // console.log(`Games loaded from Dexie for event ${eventId}`)
        return result.games
      }
      return null
    } catch (error) {
      console.error('Failed to load games from Dexie:', error)
      return null
    }
  }

  const clearOldGames = async (maxAge = 7 * 24 * 60 * 60 * 1000) => {
    try {
      const cutoffTime = Date.now() - maxAge
      await db.games.where('timestamp').below(cutoffTime).delete()
      // console.log('Old games cleared from Dexie')
    } catch (error) {
      console.error('Failed to clear old games from Dexie:', error)
    }
  }

  return {
    saveGames,
    loadGames,
    clearOldGames
  }
}