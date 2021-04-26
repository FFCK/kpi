<template>
  <div class="container-sm">
    <h1>Game</h1>
  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Game',
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
