<script setup lang="ts">
const props = defineProps<{
  presentation: string
}>()

const imgError = ref(false)

watch(() => props.presentation, () => {
  imgError.value = false
})

const config = useRuntimeConfig()
const imgSrc = computed(() => {
  return `${config.public.legacyBaseUrl}/img/presentations/${props.presentation}.png`
})
</script>

<template>
  <div v-if="presentation && !imgError" class="mt-2">
    <img
      :src="imgSrc"
      :alt="presentation"
      class="max-w-[200px] max-h-[120px] rounded border border-gray-200 object-contain"
      @error="imgError = true"
    >
  </div>
</template>
