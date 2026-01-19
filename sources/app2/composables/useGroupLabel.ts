/**
 * Composable to get translated group labels based on current locale.
 * Uses libelle_en for English locale when available, falls back to libelle (French).
 */
export function useGroupLabel() {
  const { locale } = useI18n()

  /**
   * Get the translated label for a group based on current locale.
   * @param group - Group object with libelle and optional libelle_en
   * @returns The appropriate label for the current locale
   */
  const getGroupLabel = (group: { libelle: string; libelle_en?: string | null }): string => {
    if (locale.value === 'en' && group.libelle_en) {
      return group.libelle_en
    }
    return group.libelle
  }

  return {
    getGroupLabel
  }
}
