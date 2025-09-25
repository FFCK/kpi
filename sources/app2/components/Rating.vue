<template>
  <div class="p-1 m-1 text-center bg-white rounded-lg shadow-lg">
    <div>
      <span class="p-0 m-0 mr-1" @mouseleave="hoveredStar = 0">
        <UIcon
          v-for="star in maxStars"
          :key="star"
          :name="star <= stars || star <= hoveredStar ? 'i-heroicons-star-solid' : 'i-heroicons-star'"
          class="text-4xl transition-colors duration-300 ease-in-out cursor-pointer"
          :class="(star <= stars || star <= hoveredStar) ? 'text-yellow-400' : 'text-gray-300'"
          @click="rate(star)"
          @mouseenter="hoveredStar = star"
        />
      </span>
      <span v-if="hasCounter" class="inline-block ml-1 text-3xl text-gray-400">
        <span class="font-bold">{{ stars }}</span>
        <span class="mx-1 text-2xl">/</span>
        <span class="text-2xl align-sub">{{ maxStars }}</span>
      </span>
    </div>
    <div v-if="thanks" class="text-3xl text-gray-400">
      <span>{{ t("Rating.Thanks") }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const { t } = useI18n()

const props = defineProps({
  grade: { type: Number, default: 0 },
  maxStars: { type: Number, default: 5 },
  hasCounter: { type: Boolean, default: true },
  thanks: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false }
})

const emit = defineEmits(['rated'])

const stars = ref(props.grade)
const hoveredStar = ref(0)

watch(() => props.grade, (newGrade) => {
  stars.value = newGrade
})

function rate(star) {
  if (props.disabled || props.thanks) return
  if (typeof star === 'number' && star <= props.maxStars && star >= 0) {
    stars.value = star
    emit('rated', stars.value)
  }
}
</script>
