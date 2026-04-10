import type { ControlPanel, TvGlobalFilters } from '~/types/tv'

/**
 * Composable for building TV presentation URLs.
 * Replaces both legacy Go() and ChangeVoie() URL construction patterns.
 * All URLs are built client-side, then sent to POST /admin/tv/activate.
 */
export function useTvUrl() {
  function buildUrl(
    panel: ControlPanel,
    filters: TvGlobalFilters,
    season: string
  ): string {
    const { presentation, match, pitch, competition, team, number: playerNum,
      speaker, count, pitchs, medal, zone, mode, round, start, animate,
      lnStart, lnLen, competList, format, option, navGroup, teamSelect } = panel
    const { eventId: evt, css, lang, date } = filters
    const anime = animate ? '1' : '0'

    // Suffixes for score variants
    const scoreSuffixMap: Record<string, string> = {
      score: '', score_o: '_o', score_e: '_e', score_s: '_s',
      score_club: '', score_club_o: '_o', score_club_e: '_e', score_club_s: '_s',
    }

    switch (presentation) {
      // ── Go() type: live/tv2.php?show=... ──

      case 'match':
      case 'match2':
        return `live/tv2.php?show=${presentation}&match=${match}&css=${css}&lang=${lang}`

      case 'match_score':
        return `live/tv2.php?show=match_score&match=${match}&anime=${anime}&css=${css}&lang=${lang}`

      case 'list_team':
      case 'list_coachs':
        return `live/tv2.php?show=${presentation}&match=${match}&team=${team}&css=${css}&lang=${lang}`

      case 'team':
        return `live/tv2.php?show=team&match=${match}&team=${team}&css=${css}&lang=${lang}`

      case 'referee':
        return `live/tv2.php?show=referee&match=${match}&css=${css}&lang=${lang}`

      case 'player':
      case 'coach':
        return `live/tv2.php?show=${presentation}&match=${match}&team=${team}&number=${playerNum}&css=${css}&lang=${lang}`

      case 'final_ranking':
        return `live/tv2.php?show=final_ranking&saison=${season}&competition=${competition}&start=${start}&css=${css}&lang=${lang}`

      case 'podium':
        return `live/tv2.php?show=podium&saison=${season}&competition=${competition}&anime=${anime}&css=${css}&lang=${lang}`

      // ── ChangeVoie() type: various PHP pages ──

      case 'empty':
      case 'voie':
      case 'logo':
      case 'player_pictures':
        return `live/tv2.php?show=${presentation}&css=${css}`

      case 'score':
      case 'score_o':
      case 'score_e':
      case 'score_s': {
        const suffix = scoreSuffixMap[presentation]
        return `live/score${suffix}.php?event=${evt}&terrain=${pitch}&css=${css}&speaker=${speaker}`
      }

      case 'score_club':
      case 'score_club_o':
      case 'score_club_e':
      case 'score_club_s': {
        const base = presentation.startsWith('score_club_') ? presentation.replace('score_club_', '') : ''
        const suffix = base ? `_${base}` : ''
        return `live/score_club${suffix}.php?event=${evt}&terrain=${pitch}&css=${css}&speaker=${speaker}`
      }

      case 'live':
        return `app_live/#/${evt}/${pitch}/score/${zone}/${mode}/${css}/en/`

      case 'teams':
        return `live/teams.php?event=${evt}&terrain=${pitch}&css=${css}&anime=${anime}`

      case 'teams_club':
        return `live/teams_club.php?event=${evt}&terrain=${pitch}&css=${css}&anime=${anime}`

      case 'next_game':
        return `live/next_game.php?event=${evt}&terrain=${pitch}&css=${css}&anime=${anime}`

      case 'next_game_club':
        return `live/next_game_club.php?event=${evt}&terrain=${pitch}&css=${css}&anime=${anime}`

      case 'liveteams':
        return `live/liveteams.php?event=${evt}&terrain=${pitch}&speaker=${speaker}`

      case 'multi_score':
        return `live/multi_score.php?event=${evt}&count=${count}&speaker=${speaker}&refresh=10`

      // ── Frame pages ──

      case 'frame_terrains':
        return `frame_terrains.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&terrains=${pitchs}&filtreJour=${date}&Css=${css}`

      case 'frame_phases':
        return `frame_phases.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Round=${round}&Css=${css}`

      case 'frame_categories':
        return `frame_categories.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&terrains=${pitchs}&filtreJour=${date}&Css=${css}&start=${lnStart}&len=${lnLen}`

      case 'frame_chart':
        return `frame_chart.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Round=${round}&Css=${css}`

      case 'frame_details':
        return `frame_details.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Round=${round}&Css=${css}`

      case 'frame_team':
        return `frame_team.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Team=${teamSelect}&Round=${round}&Css=${css}`

      case 'frame_stats':
        return `frame_stats.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Css=${css}`

      case 'frame_classement':
        return `frame_classement.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Css=${css}`

      case 'frame_qr':
        return `frame_qr.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Css=${css}`

      case 'frame_matchs':
        return `frame_matchs.php?event=${evt}&lang=en&Saison=${season}&Compet=${competition}&Team=${teamSelect}&Round=${round}&Css=${css}&navGroup=${navGroup ? '1' : '0'}`

      // ── API ──

      case 'api_players':
        return `api_players.php?saison=${season}&competitions=${competList}&format=${format}`

      case 'api_stats':
        return `api_stats.php?saison=${season}&competitions=${competList}&all=${option}&format=${format}`

      // ── Cache ──

      case 'force_cache_match':
        return `live/force_cache_match.php?match=${match}`

      default:
        return `live/tv2.php?show=${presentation}&css=${css}`
    }
  }

  /**
   * Check if a presentation is a "force cache" type that should be
   * called directly (AJAX) instead of activating on a channel.
   */
  function isDirectAction(presentation: string): boolean {
    return presentation === 'force_cache_match'
  }

  return { buildUrl, isDirectAction }
}
