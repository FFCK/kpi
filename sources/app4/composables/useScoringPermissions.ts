import type { Ref, ComputedRef } from 'vue'

/**
 * Permission management for the Scoring console.
 * Mirrors usePresencePermissions (profile thresholds + lock state).
 *
 * ⚠️ Experimentation phase: access is restricted to profile <= 2 (admins/bureau only),
 * NOT yet opened to profile 9 "Table de marque" (ROLE_SCORER). The post-validation
 * target is profile <= 6 || profile === 9 for scoring and <= 6 for validation/lock —
 * raise SCORING_ACCESS_MAX_PROFILE (and the server-side check) when opening up.
 * See DOC/specs/PAGE_SCORING.md §6.3.
 */

/** Max profile allowed to access Scoring during the experimentation phase */
const SCORING_ACCESS_MAX_PROFILE = 2

export function useScoringPermissions(isLocked: Ref<boolean> | ComputedRef<boolean>) {
  const authStore = useAuthStore()

  const hasAccess = computed(() => authStore.profile <= SCORING_ACCESS_MAX_PROFILE)

  // View console + see the "Scoring" link in /games
  const canView = computed(() => hasAccess.value)

  // Enter goals / cards / control the timer
  const canScore = computed(() => hasAccess.value && !isLocked.value)

  // Manage players (status, numbers)
  const canManagePlayers = computed(() => hasAccess.value && !isLocked.value)

  // Validate / lock the match (reuses AdminGames toggleValidation)
  const canValidate = computed(() => hasAccess.value)
  const canLock = computed(() => hasAccess.value)

  return {
    canView,
    canScore,
    canManagePlayers,
    canValidate,
    canLock
  }
}
