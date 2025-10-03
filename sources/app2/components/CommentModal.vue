<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="close">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
        <button @click="close" class="text-gray-400 hover:text-gray-600">
          <UIcon name="i-heroicons-x-mark" class="h-6 w-6" />
        </button>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          {{ t('Scrutineering.Comment') }}
        </label>
        <textarea
          ref="textareaRef"
          v-model="localComment"
          :maxlength="255"
          rows="5"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :placeholder="t('Scrutineering.EnterComment')"
          @keydown.enter="handleEnterKey"
        />
        <div class="text-xs text-gray-500 text-right mt-1">
          {{ localComment.length }}/255
        </div>
      </div>

      <div class="flex justify-between space-x-3">
        <button
          v-if="localComment"
          @click="clearComment"
          class="px-4 py-2 text-sm text-red-600 border border-red-600 rounded hover:bg-red-50"
        >
          {{ t('Scrutineering.Clear') }}
        </button>
        <div class="flex-1"></div>
        <button
          @click="close"
          class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50"
        >
          {{ t('Scrutineering.Cancel') }}
        </button>
        <button
          @click="save"
          class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          {{ t('Scrutineering.Save') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const { t } = useI18n()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  comment: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'save'])

const localComment = ref('')
const initialComment = ref('')
const textareaRef = ref(null)

watch(() => props.comment, (newVal) => {
  localComment.value = newVal || ''
}, { immediate: true })

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    localComment.value = props.comment || ''
    initialComment.value = props.comment || ''
    nextTick(() => {
      textareaRef.value?.focus()
    })
  }
})

const close = () => {
  emit('close')
}

const save = () => {
  if (localComment.value !== initialComment.value) {
    emit('save', localComment.value)
  } else {
    close()
  }
}

const handleEnterKey = (event) => {
  if (!event.shiftKey) {
    event.preventDefault()
    save()
  }
}

const clearComment = () => {
  localComment.value = ''
}
</script>
