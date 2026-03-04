import type {
  SchemaSearchResult,
  CompetitionCopyDetail,
  CompetitionOptionGroup,
  CopyCompetitionPayload,
  CopyCompetitionResponse
} from '~/types/competition-copy'

/**
 * Composable for competition copy API calls
 */
export const useCompetitionCopyApi = () => {
  const api = useApi()

  /**
   * Search competition schemas by number of teams
   */
  const searchSchemas = async (
    nbEquipes: number,
    type: string = '',
    tri: string = 'saison'
  ): Promise<SchemaSearchResult[]> => {
    const params: Record<string, string | number> = { nbEquipes }
    if (type) params.type = type
    if (tri) params.tri = tri

    const result = await api.get<{ schemas: SchemaSearchResult[] }>(
      '/admin/competitions/-schemas',
      params
    )
    return result.schemas
  }

  /**
   * Get competition copy detail (origin)
   */
  const getCopyDetail = async (
    season: string,
    code: string
  ): Promise<CompetitionCopyDetail> => {
    return await api.get<CompetitionCopyDetail>(
      `/admin/competitions/${season}/${code}/copy-detail`
    )
  }

  /**
   * Get competition options for destination dropdown
   */
  const getCompetitionOptions = async (
    season: string
  ): Promise<CompetitionOptionGroup[]> => {
    const result = await api.get<{ groups: CompetitionOptionGroup[] }>(
      '/admin/competitions/-options',
      { season }
    )
    return result.groups
  }

  /**
   * Copy competition structure
   */
  const copyCompetition = async (
    payload: CopyCompetitionPayload
  ): Promise<CopyCompetitionResponse> => {
    return await api.post<CopyCompetitionResponse>(
      '/admin/competitions/-copy',
      payload
    )
  }

  /**
   * Update competition comments
   */
  const updateComments = async (
    season: string,
    code: string,
    commentaires: string
  ): Promise<void> => {
    await api.patch(`/admin/competitions/${season}/${code}/comments`, {
      commentaires
    })
  }

  return {
    searchSchemas,
    getCopyDetail,
    getCompetitionOptions,
    copyCompetition,
    updateComments
  }
}
