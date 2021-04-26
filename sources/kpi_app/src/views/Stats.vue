<template>
  <div class="container-sm">
    <h1>Stats</h1>
    <span class="fas fa-chart-bar"></span>
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
