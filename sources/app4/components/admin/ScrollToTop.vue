<script setup lang="ts">
interface Props {
  threshold?: number
  title?: string
  titleDown?: string
}

withDefaults(defineProps<Props>(), {
  threshold: 300,
  title: 'Retour en haut',
  titleDown: 'Aller en bas'
})

const showUp = ref(false)
const showDown = ref(true)

const handleScroll = () => {
  const scrollY = window.scrollY
  const maxScroll = document.documentElement.scrollHeight - window.innerHeight
  showUp.value = scrollY > 300
  showDown.value = scrollY < maxScroll - 10
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const scrollToBottom = () => {
  window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' })
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="fixed bottom-4 right-6 z-40 flex flex-col gap-2 items-center">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <button
        v-if="showUp"
        class="p-3 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 transition-colors"
        :title="title"
        @click="scrollToTop"
      >
        <UIcon name="heroicons:arrow-up" class="w-6 h-6" />
      </button>
    </Transition>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <button
        v-if="showDown"
        class="p-3 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 transition-colors"
        :title="titleDown"
        @click="scrollToBottom"
      >
        <UIcon name="heroicons:arrow-down" class="w-6 h-6" />
      </button>
    </Transition>
  </div>
</template>
