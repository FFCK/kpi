/**
 * useBracketDisplay — Composable for match bracket notation parsing and display.
 *
 * ## Bracket notation
 *
 * A match label (libelle) can contain a bracket notation: `[PART1-PART2/PART3-PART4]`
 * where separators are any of: `-` `/` `*` `,` `;`
 *
 * | Position | Field                   | Meaning          |
 * |----------|-------------------------|------------------|
 * | PART1    | Équipe A (Id_equipeA)   | Team A code      |
 * | PART2    | Équipe B (Id_equipeB)   | Team B code      |
 * | PART3    | Arbitre principal       | Referee 1 code   |
 * | PART4    | Arbitre secondaire      | Referee 2 code   |
 *
 * ## Code types
 *
 * | Prefix     | Meaning              | Example | FR result               | EN result             |
 * |------------|----------------------|---------|--------------------------|-----------------------|
 * | T or D     | Tirage / Draw        | T1      | Équipe 1                | Team 1                |
 * | V, G or W  | Vainqueur / Winner   | V2      | Vainqueur match 2       | Winner game #2        |
 * | P or L     | Perdant / Loser      | P3      | Perdant match 3         | Loser game #3         |
 * | digit+letter | Rang poule / Group | 1A      | 1er poule A             | 1st Group A           |
 *
 * ## Usage
 *
 * ```vue
 * <script setup>
 * const { bracketLabels } = useBracketDisplay()
 *
 * // In a v-for on games:
 * const labels = computed(() => bracketLabels(game.libelle))
 * // labels.teamA, labels.teamB, labels.refereePrincipal, labels.refereeSecondaire
 * </script>
 * ```
 *
 * Each label is `null` when the bracket does not contain that part.
 *
 * ## Display rules
 *
 * When an entity (team or referee) is **not assigned** and the bracket contains a code for
 * that position, the bracket label is displayed in *italic orange* between parentheses.
 *
 * Ported from PHP: `utyEquipesAffectAutoFR()` / `utyEquipesAffectAuto()` in `sources/commun/MyTools.php`
 */

export interface BracketLabels {
  teamA: string | null
  teamB: string | null
  refereePrincipal: string | null
  refereeSecondaire: string | null
}

export interface BracketRawCodes {
  teamA: string | null
  teamB: string | null
  refereePrincipal: string | null
  refereeSecondaire: string | null
}

/**
 * Parse a single bracket code (e.g. "T1", "V2", "1A") into a human-readable label.
 */
function parseBracketCode(code: string, isFr: boolean): string {
  const letterMatch = code.match(/([A-Z_]+)/)
  const numberMatch = code.match(/(\d+)/)

  if (!letterMatch?.[1] || !numberMatch?.[1]) return ''

  const letters = letterMatch[1]
  const num = numberMatch[1]
  const posNumber = code.indexOf(num)
  const posLetters = code.indexOf(letters)

  if (posNumber > posLetters) {
    // Letter before number: T1, V2, P3, etc.
    switch (letters) {
      case 'T':
      case 'D':
        return isFr ? `Équipe ${num}` : `Team ${num}`
      case 'V':
      case 'G':
      case 'W':
        return isFr ? `Vainqueur match ${num}` : `Winner game #${num}`
      case 'P':
      case 'L':
        return isFr ? `Perdant match ${num}` : `Loser game #${num}`
      default:
        return ''
    }
  }
  else if (posNumber < posLetters) {
    // Number before letter: 1A, 2B, etc.
    const n = parseInt(num)
    if (isFr) {
      const suffix = n === 1 ? '1er' : `${n}ème`
      return `${suffix} poule ${letters}`
    }
    else {
      let suffix: string
      if (n === 1) suffix = '1st'
      else if (n === 2) suffix = '2nd'
      else if (n === 3) suffix = '3rd'
      else suffix = `${n}th`
      return `${suffix} Group ${letters}`
    }
  }

  return ''
}

/**
 * Extract the 4 raw bracket codes without translation (e.g. "1A", "V2", "T1").
 */
function extractRawCodes(libelle: string | null): BracketRawCodes {
  const result: BracketRawCodes = { teamA: null, teamB: null, refereePrincipal: null, refereeSecondaire: null }
  if (!libelle) return result
  const bracketMatch = libelle.match(/\[([^\]]+)\]/)
  if (!bracketMatch?.[1]) return result
  const parts = bracketMatch[1].split(/[-/*,;]/)
  const keys: (keyof BracketRawCodes)[] = ['teamA', 'teamB', 'refereePrincipal', 'refereeSecondaire']
  for (let i = 0; i < 4; i++) {
    const code = parts[i]?.trim()
    if (code) result[keys[i]] = code
  }
  return result
}

/**
 * Parse all 4 positions from bracket notation.
 */
function parseBracket(libelle: string | null, isFr: boolean): BracketLabels {
  const result: BracketLabels = {
    teamA: null,
    teamB: null,
    refereePrincipal: null,
    refereeSecondaire: null,
  }

  if (!libelle) return result

  const bracketMatch = libelle.match(/\[([^\]]+)\]/)
  if (!bracketMatch?.[1]) return result

  const parts = bracketMatch[1].split(/[-/*,;]/)
  const keys: (keyof BracketLabels)[] = ['teamA', 'teamB', 'refereePrincipal', 'refereeSecondaire']

  for (let i = 0; i < 4; i++) {
    if (parts[i]) {
      const parsed = parseBracketCode(parts[i].trim(), isFr)
      if (parsed) result[keys[i]] = parsed
    }
  }

  return result
}

/**
 * Tokens that compose a valid bracket encoding once split on separators.
 * Accepts both letter-first codes (T1, D2, V3, G1, W4, P2, L5) and
 * number-first group ranks (1A, 12B). Case-insensitive on input; the caller
 * uppercases before storing.
 */
const BRACKET_TOKEN_RE = /^(?:[TDVGWPL]\d+|\d+[A-Z]+)$/i
const BRACKET_SEPARATORS_RE = /[-/*,;]/

/**
 * Decide whether a free-text label *is* a bracket encoding that happens to be
 * missing its surrounding `[ ]`. We only treat the whole string as an encoding
 * when every separator-delimited token looks like a bracket code, so plain
 * labels (e.g. "Finale", "Match amical") are left untouched.
 */
function looksLikeBareEncoding(text: string): boolean {
  const tokens = text.split(BRACKET_SEPARATORS_RE).map(p => p.trim()).filter(Boolean)
  if (tokens.length === 0) return false
  return tokens.every(tok => BRACKET_TOKEN_RE.test(tok))
}

/**
 * Normalise the coding stored in a match label so it always ends up wrapped in
 * a single, balanced `[ ]` pair.
 *
 * Handles three user mistakes:
 *  - missing both brackets:  `1A-1B`      → `[1A-1B]`
 *  - missing the opening one: `1A-1B]`    → `[1A-1B]`
 *  - missing the closing one: `[1A-1B`    → `[1A-1B]`
 *
 * A label that already has a balanced bracket, or that is plain free text
 * (not an encoding), is returned unchanged (trimmed).
 */
export function normalizeBracketCoding(raw: string | null | undefined): string {
  const text = (raw ?? '').trim()
  if (!text) return ''

  const hasOpen = text.includes('[')
  const hasClose = text.includes(']')

  // Already has a complete, balanced bracket → leave as-is.
  if (hasOpen && hasClose) return text

  // Exactly one bracket present: the inner content is whatever sits next to it.
  // Add the missing bracket only when the content reads like an encoding.
  if (hasOpen !== hasClose) {
    const inner = text.replace(/[[\]]/g, '').trim()
    if (looksLikeBareEncoding(inner)) return `[${inner}]`
    return text
  }

  // No brackets at all: wrap when the whole label is a bare encoding.
  if (looksLikeBareEncoding(text)) return `[${text}]`
  return text
}

/**
 * Composable that provides bracket parsing functions bound to the current locale.
 */
export function useBracketDisplay() {
  const { locale } = useI18n()

  /**
   * Parse bracket notation from a match label.
   * Returns labels for all 4 positions (teamA, teamB, refereePrincipal, refereeSecondaire).
   */
  function bracketLabels(libelle: string | null): BracketLabels {
    return parseBracket(libelle, locale.value === 'fr')
  }

  function bracketRawCodes(libelle: string | null): BracketRawCodes {
    return extractRawCodes(libelle)
  }

  return { bracketLabels, bracketRawCodes }
}
