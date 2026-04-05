<script setup lang="ts">
import { computed } from 'vue'

/**
 * AdminCompetitionGroupedSelect
 *
 * Sélecteur de compétition avec groupement par section.
 * Affiche les compétitions du contexte de travail, groupées par section (kp_groupe.section).
 *
 * Props:
 * - modelValue: Code compétition sélectionné (null = national sans compétition)
 * - showNationalOption: Afficher l'option "National (sans compétition)" (défaut: true)
 * - required: Champ obligatoire (défaut: false)
 * - disabled: Désactiver le sélecteur (défaut: false)
 *
 * Events:
 * - update:modelValue: Émis lors du changement de sélection
 */

interface Props {
  modelValue: string | null
  showNationalOption?: boolean
  required?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showNationalOption: true,
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
}>()

const workContext = useWorkContextStore()
const { t } = useI18n()

/**
 * Compétitions groupées par section
 * Structure: { section: string, competitions: Competition[] }[]
 */
const groupedCompetitions = computed(() => {
  if (!workContext.competitions || workContext.competitions.length === 0) {
    return []
  }

  // Grouper les compétitions par section
  const groups = new Map<string, typeof workContext.competitions>()

  workContext.competitions.forEach((competition) => {
    // Trouver le CompetitionGroup qui contient cette compétition
    const group = workContext.groups?.find(g =>
      g.competitions.some(c => c.code === competition.code),
    )
    const section = group?.sectionLabel || 'Autres'

    if (!groups.has(section)) {
      groups.set(section, [])
    }
    groups.get(section)!.push(competition)
  })

  // Convertir en array, en conservant l'ordre des sections tel que renvoyé par l'API
  const sectionOrder = workContext.groups?.map(g => g.sectionLabel) || []

  return Array.from(groups.entries())
    .sort((a, b) => {
      const indexA = sectionOrder.indexOf(a[0])
      const indexB = sectionOrder.indexOf(b[0])
      const orderA = indexA === -1 ? 999 : indexA
      const orderB = indexB === -1 ? 999 : indexB
      return orderA - orderB
    })
    .map(([section, competitions]) => ({
      section,
      competitions: competitions.sort((a, b) => a.libelle.localeCompare(b.libelle)),
    }))
})

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  const value = target.value === '' ? null : target.value
  emit('update:modelValue', value)
}
</script>

<template>
  <select
    :value="modelValue || ''"
    :required="required"
    :disabled="disabled"
    class="w-full px-3 py-2 border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-header-100 disabled:cursor-not-allowed"
    @change="handleChange"
  >
    <!-- Option "National (sans compétition)" -->
    <option v-if="showNationalOption" value="">
      {{ t('rc.no_national_competition') }}
    </option>

    <!-- Compétitions groupées par section -->
    <optgroup
      v-for="group in groupedCompetitions"
      :key="group.section"
      :label="group.section"
    >
      <option
        v-for="competition in group.competitions"
        :key="competition.code"
        :value="competition.code"
      >
        {{ competition.code }} - {{ competition.libelle }}
      </option>
    </optgroup>
  </select>
</template>
