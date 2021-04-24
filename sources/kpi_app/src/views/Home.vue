<template>
  <div class="home">
    <img alt="Vue logo" src="../assets/logo.png" width="100" height="100">

    <button class="btn btn-primary" @click="changePage">About !</button>
  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Home',
  methods: {
    changePage () {
      this.$router.push({ name: 'About' })
    }
  }
}
</script>
