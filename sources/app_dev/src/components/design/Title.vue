<template>
  <div class="mx-1">
    <div class="bg-dark text-light rounded py-1 text-center">
      <h4>
        <i v-if="icon" :class="icon" />
        {{ text }}
      </h4>
    </div>
  </div>
</template>

<script>

export default {
  name: 'Title',
  props: {
    text: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: null
    }
  }
}
</script>
