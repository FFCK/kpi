/**
 * Global plugin: clicking anywhere in a <th> or <td> that contains a checkbox
 * toggles that checkbox, even if the click lands on the cell padding.
 *
 * Uses capture phase to work even when cells have @click.stop (stopPropagation).
 *
 * Only acts when:
 * - The cell contains exactly one checkbox
 * - The click target is NOT already the checkbox itself
 * - The checkbox is not disabled
 */
export default defineNuxtPlugin(() => {
  document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement
    if (!target) return

    // Already clicked the checkbox itself — nothing to do
    if (target instanceof HTMLInputElement && target.type === 'checkbox') return

    // Walk up to find the nearest th or td
    const cell = target.closest('th, td') as HTMLElement | null
    if (!cell) return

    // Find a single checkbox inside
    const checkboxes = cell.querySelectorAll<HTMLInputElement>('input[type="checkbox"]')
    if (checkboxes.length !== 1) return

    const checkbox = checkboxes[0]
    if (checkbox.disabled) return

    // Programmatic click triggers change/input events and v-model updates
    checkbox.click()
  }, true) // capture phase: fires before @click.stop can prevent bubbling
})
