<template>
    <div>
        <Nav />

        <br class="mt-4 mb-3" />
        <router-view />
    </div>
</template>

<script>
import Nav from '@/components/Nav'

export default {
  components: {
    Nav
  }
}
</script>

<style lang="scss">
@import "./assets/styles/custom.scss";
@import "~bootstrap";
@import "~@fortawesome/fontawesome-free/css/all.css";

@import "./assets/styles/app.scss";

#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
}
</style>
