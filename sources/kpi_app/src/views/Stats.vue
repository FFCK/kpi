<template>
  <div class="container-sm">
    <h1>{{ $t('nav.Stats') }}</h1>
    <span class="bi bi-bar-chart-line h1 text-primary"></span>

  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Stats',
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
