<template>
  <div>
    <h1 class="shadow-sm">
      <i>{{ text }}</i>
    </h1>
  </div>
</template>

<script>
export default {
  name: 'Title',
  props: {
    text: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
h1 {
  font-size: 1.7rem;
}
</style>
