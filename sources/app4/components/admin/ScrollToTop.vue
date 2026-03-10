<script setup lang="ts">
interface Props {
  threshold?: number
  title?: string
}

withDefaults(defineProps<Props>(), {
  threshold: 300,
  title: 'Retour en haut'
})

const showButton = ref(false)

const handleScroll = () => {
  showButton.value = window.scrollY > 300
}

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <button
      v-if="showButton"
      class="fixed bottom-2 right-6 p-3 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 transition-colors z-40 mb-2"
      :title="title"
      @click="scrollToTop"
    >
      <UIcon name="heroicons:arrow-up" class="w-6 h-6" />
    </button>
  </Transition>
</template>
