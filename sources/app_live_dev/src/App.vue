<template>
  <div>
    <router-view v-slot="{ Component }">
      <component :is="Component" />
    </router-view>
  </div>
</template>

<script>

export default {
}
</script>

<style lang="scss">
@import "./assets/styles/custom.scss";
@import "~bootstrap";
@import "~bootstrap-icons/font/bootstrap-icons.css";

@import "./assets/styles/app.scss";

</style>
