import type { Ref, ComputedRef } from 'vue'
import type { PresenceMode } from '~/types/presence'

/**
 * Permission management for Presence Sheet
 * Different rules for Team Mode vs Match Mode
 */
export function usePresencePermissions(mode: PresenceMode, isLocked: Ref<boolean> | ComputedRef<boolean>) {
  const authStore = useAuthStore()

  // View permission (all modes)
  const canView = computed(() => authStore.profile <= 10)

  // Edit permission (inline edit, add, delete)
  const canEdit = computed(() => {
    if (isLocked.value) return false
    if (mode === 'team') return authStore.profile <= 8
    if (mode === 'match') return authStore.profile <= 9
    return false
  })

  // Delete permission (same as edit)
  const canDelete = computed(() => canEdit.value)

  // Copy permission
  const canCopy = computed(() => {
    if (isLocked.value) return false
    if (mode === 'team') return authStore.profile <= 4 // Copy from other competition
    if (mode === 'match') return authStore.profile <= 6 // Copy to same day matches
    return false
  })

  // Copy to competition (Match Mode only, requires profile ≤ 4)
  const canCopyToCompetition = computed(() => {
    if (isLocked.value) return false
    if (mode === 'match') return authStore.profile <= 4
    return false
  })

  // Create player (Team Mode only, requires profile ≤ 4)
  const canCreatePlayer = computed(() => {
    if (mode !== 'team') return false
    if (isLocked.value) return false
    return authStore.profile <= 4
  })

  // Search license (Team Mode only, requires profile ≤ 2)
  const canSearchLicense = computed(() => {
    if (mode !== 'team') return false
    return authStore.profile <= 2
  })

  // Initialize from team (Match Mode only)
  const canInitializeFromTeam = computed(() => {
    if (mode !== 'match') return false
    if (isLocked.value) return false
    return authStore.profile <= 9
  })

  // Clear all players (Match Mode only)
  const canClearAll = computed(() => {
    if (mode !== 'match') return false
    if (isLocked.value) return false
    return authStore.profile <= 9
  })

  return {
    canView,
    canEdit,
    canDelete,
    canCopy,
    canCopyToCompetition,
    canCreatePlayer,
    canSearchLicense,
    canInitializeFromTeam,
    canClearAll
  }
}

/**
 * Validate player for national competitions (N* or CF*)
 * Returns true if valid, false otherwise
 */
export function usePlayerValidation() {
  /**
   * Check if player is valid for national competition
   */
  const isPlayerValid = (
    player: any,
    competition: { code: string; codeSaison?: string },
    needsSurclassement: boolean = false
  ): boolean => {
    // Not a national competition - no validation needed
    const code = competition.code
    if (!code.startsWith('N') && !code.startsWith('CF')) {
      return true
    }

    // Check license season
    if (competition.codeSaison && player.origine < competition.codeSaison) {
      return false
    }

    // Check certificate
    if (player.certifCK !== 'OUI') {
      return false
    }

    // Check pagaie (exclude PAGJ, PAGB, or empty)
    if (player.pagaieValide === 0 || ['', 'PAGJ', 'PAGB'].includes(player.pagaieECA)) {
      return false
    }

    // Check surclassement if needed
    if (needsSurclassement && !player.dateSurclassement) {
      return false
    }

    return true
  }

  /**
   * Check if competition requires surclassement
   */
  const requiresSurclassement = (competitionCode: string, categ: string): boolean => {
    // Competitions requiring surclassement
    const surclNecessaire = ['N1D', 'N1F', 'N1H', 'N2', 'N2H', 'N3H', 'N4H', 'NQH', 'CFF', 'CFH', 'MCP']
    const surclNecessaire2 = ['N3', 'N4']

    // Categories exempt from surclassement
    const exemptCategs = ['JUN', 'SEN', 'V1', 'V2', 'V3', 'V4']

    if (exemptCategs.includes(categ)) {
      return false
    }

    return surclNecessaire.includes(competitionCode) || surclNecessaire2.includes(competitionCode)
  }

  /**
   * Get validation errors for a player
   */
  const getValidationErrors = (
    player: any,
    competition: { code: string; codeSaison?: string }
  ): string[] => {
    const errors: string[] = []

    // Not a national competition - no errors
    const code = competition.code
    if (!code.startsWith('N') && !code.startsWith('CF')) {
      return errors
    }

    // Check license season
    if (competition.codeSaison && player.origine < competition.codeSaison) {
      errors.push('Saison de licence invalide')
    }

    // Check certificate
    if (player.certifCK !== 'OUI') {
      errors.push('Certificat médical manquant')
    }

    // Check pagaie
    if (player.pagaieValide === 0 || ['', 'PAGJ', 'PAGB'].includes(player.pagaieECA)) {
      errors.push('Pagaie couleur invalide')
    }

    // Check surclassement if needed
    const needsSurcl = requiresSurclassement(code, player.categ)
    if (needsSurcl && !player.dateSurclassement) {
      errors.push('Surclassement manquant')
    }

    return errors
  }

  return {
    isPlayerValid,
    requiresSurclassement,
    getValidationErrors
  }
}
