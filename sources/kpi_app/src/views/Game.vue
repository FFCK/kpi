<template>
  <div class="container-sm">
    <h1>{{ $t('nav.Games') }}</h1>

  </div>
</template>

<script>
export default {
  name: 'Game',
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
