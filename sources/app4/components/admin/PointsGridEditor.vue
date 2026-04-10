<script setup lang="ts">
const { t, locale } = useI18n()

interface Props {
  modelValue: Record<string, number> | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: Record<string, number> | null): void
}>()

const numPositions = ref(10)
const positions = ref<(number | null)[]>(Array(10).fill(null))
const defaultPoints = ref(0)
const isActive = ref(false)
const showPreview = ref(false)

// Flag to prevent re-init loop when we emit
let isEmitting = false

const initFromValue = (value: Record<string, number> | null) => {
  if (isEmitting) return

  if (!value || Object.keys(value).length === 0) {
    isActive.value = false
    return
  }

  isActive.value = true
  defaultPoints.value = value.default ?? 0

  const numericKeys = Object.keys(value)
    .filter(k => k !== 'default')
    .map(Number)
    .filter(n => !isNaN(n))

  const maxPos = numericKeys.length > 0 ? Math.max(...numericKeys) : 10
  numPositions.value = Math.max(maxPos, 1)

  const arr: (number | null)[] = []
  for (let i = 1; i <= numPositions.value; i++) {
    const key = String(i)
    arr.push(key in value ? value[key] : null)
  }
  positions.value = arr
}

watch(() => props.modelValue, (val) => {
  initFromValue(val)
}, { immediate: true, deep: true })

watch(numPositions, (newCount, oldCount) => {
  if (newCount === oldCount) return
  const clamped = Math.max(1, Math.min(50, newCount))
  if (clamped !== newCount) {
    numPositions.value = clamped
    return
  }
  if (newCount > oldCount) {
    for (let i = oldCount; i < newCount; i++) {
      positions.value.push(null)
    }
  } else {
    positions.value = positions.value.slice(0, newCount)
  }
  emitGrid()
})

const buildGrid = (): Record<string, number> => {
  const grid: Record<string, number> = {}
  for (let i = 0; i < positions.value.length; i++) {
    const val = positions.value[i]
    if (val !== null && val !== undefined && !isNaN(val)) {
      grid[String(i + 1)] = val
    }
  }
  grid['default'] = defaultPoints.value ?? 0
  return grid
}

const emitGrid = () => {
  if (!isActive.value) return
  isEmitting = true
  emit('update:modelValue', buildGrid())
  nextTick(() => { isEmitting = false })
}

watch(positions, () => emitGrid(), { deep: true })
watch(defaultPoints, () => emitGrid())

const getPositionLabel = (pos: number): string => {
  if (locale.value === 'fr') {
    return pos === 1
      ? t('competitions.multi.points_grid_editor.position_label_first')
      : t('competitions.multi.points_grid_editor.position_label', { n: pos })
  }
  if (pos === 1) return t('competitions.multi.points_grid_editor.position_label_first')
  if (pos === 2) return t('competitions.multi.points_grid_editor.position_label_second')
  if (pos === 3) return t('competitions.multi.points_grid_editor.position_label_third')
  return t('competitions.multi.points_grid_editor.position_label', { n: pos })
}

const activate = () => {
  isActive.value = true
  numPositions.value = 10
  positions.value = Array(10).fill(null)
  defaultPoints.value = 0
  emitGrid()
}

const clear = () => {
  isActive.value = false
  isEmitting = true
  emit('update:modelValue', null)
  nextTick(() => { isEmitting = false })
}

const jsonPreview = computed(() => {
  if (!isActive.value) return ''
  return JSON.stringify(buildGrid())
})
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-header-700 mb-1">
      {{ t('competitions.multi.points_grid') }}
    </label>
    <p class="text-xs text-header-500 mb-2">
      {{ t('competitions.multi.points_grid_hint') }}
    </p>

    <!-- Inactive: configure button -->
    <div v-if="!isActive">
      <button
        type="button"
        class="text-sm text-primary-600 hover:text-primary-800 font-medium"
        @click="activate"
      >
        {{ t('competitions.multi.points_grid_editor.configure') }}
      </button>
    </div>

    <!-- Active: editor -->
    <div v-else class="space-y-3">
      <!-- Number of positions -->
      <div class="flex items-center gap-2">
        <label class="text-sm text-header-600">
          {{ t('competitions.multi.points_grid_editor.num_positions') }}
        </label>
        <input
          v-model.number="numPositions"
          type="number"
          min="1"
          max="50"
          class="w-20 px-2 py-1 border border-header-300 rounded text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
        <span class="text-xs text-header-400">{{ t('competitions.multi.points_grid_editor.num_positions_hint') }}</span>
      </div>

      <!-- Positions grid -->
      <div class="max-h-64 overflow-y-auto border border-header-300 rounded-lg bg-white p-2">
        <div class="space-y-1">
          <div v-for="(_, idx) in positions" :key="idx" class="flex items-center justify-between gap-2">
            <label :for="`pos-${idx}`" class="text-sm text-header-700 min-w-24">{{ getPositionLabel(idx + 1) }}</label>
            <input
              :id="`pos-${idx}`"
              v-model.number="positions[idx]"
              type="number"
              min="0"
              placeholder="—"
              class="w-20 px-2 py-1 border border-header-300 rounded text-sm text-right focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
          </div>
        </div>
      </div>

      <!-- Default points -->
      <div class="flex items-center gap-2">
        <label for="default-points" class="text-sm text-header-600">
          {{ t('competitions.multi.points_grid_editor.default_points') }}
        </label>
        <input
          id="default-points"
          v-model.number="defaultPoints"
          type="number"
          min="0"
          class="w-20 px-2 py-1 border border-header-300 rounded text-sm text-right focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        >
        <span class="text-xs text-header-400">{{ t('competitions.multi.points_grid_editor.default_points_hint') }}</span>
      </div>

      <!-- JSON preview -->
      <div>
        <button
          type="button"
          class="text-xs text-header-500 hover:text-header-700"
          @click="showPreview = !showPreview"
        >
          {{ showPreview ? '▾' : '▸' }} {{ t('competitions.multi.points_grid_editor.json_preview') }}
        </button>
        <pre
          v-if="showPreview"
          class="mt-1 p-2 bg-header-100 rounded text-xs font-mono text-header-700 overflow-x-auto"
        >{{ jsonPreview }}</pre>
      </div>

      <!-- Clear -->
      <button
        type="button"
        class="text-xs text-danger-500 hover:text-danger-700"
        @click="clear"
      >
        {{ t('competitions.multi.points_grid_editor.clear') }}
      </button>
    </div>
  </div>
</template>
