import type {
  CompetitionSearchResult,
  CompetitionFromPreviousSeason
} from '~/types/competitions'

/**
 * Composable for competitions-specific API calls
 */
export const useCompetitionsApi = () => {
  const api = useApi()

  /**
   * Search competitions from previous seasons for autocomplete
   * @param query Search term (min 2 characters)
   * @param currentSeasonCode Current season code to exclude (YYYY format)
   * @param limit Maximum number of results (default 10)
   */
  const searchPreviousSeasons = async (
    query: string,
    currentSeasonCode: string,
    limit = 10
  ): Promise<CompetitionSearchResult[]> => {
    try {
      console.log('[API] Searching previous seasons:', { query, currentSeasonCode, limit })
      const results = await api.get<CompetitionSearchResult[]>(
        '/admin/competitions/-search-previous-seasons',
        {
          query,
          currentSeasonCode,
          limit
        }
      )
      console.log('[API] Search results:', results)
      return results
    } catch (error) {
      console.error('[API] Error searching previous seasons:', error)
      return []
    }
  }

  /**
   * Get complete competition data from a previous season
   * Used to pre-fill form when importing from previous season
   * @param code Competition code
   * @param seasonCode Season code (YYYY format)
   */
  const getCompetitionFromPreviousSeason = async (
    code: string,
    seasonCode: string
  ): Promise<CompetitionFromPreviousSeason> => {
    console.log('[API] Getting competition from previous season:', { code, seasonCode })
    const result = await api.get<CompetitionFromPreviousSeason>(
      `/admin/competitions/-from-previous-season/${code}/${seasonCode}`
    )
    console.log('[API] Competition data received:', result)
    return result
  }

  return {
    searchPreviousSeasons,
    getCompetitionFromPreviousSeason
  }
}
