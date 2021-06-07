<template>
  <div>
    <Navbar />

    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>

    <AddToHomeScreen />

  </div>
</template>

<script>
import Navbar from '@/components/design/Navbar'
import AddToHomeScreen from '@/components/design/AddToHomeScreen.vue'

export default {
  components: {
    Navbar,
    AddToHomeScreen
  }
}
</script>

<style lang="scss">
@import "./assets/styles/custom.scss";
@import "~bootstrap";
@import "~bootstrap-icons/font/bootstrap-icons.css";
@import "~bootstrap-select/dist/css/bootstrap-select.min.css";
@import "~element-plus/lib/theme-chalk/index.css";
@import '~element-plus/lib/theme-chalk/display.css';

@import "./assets/styles/app.scss";

#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease-out;
}

.fade-enter-from,
.fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
.filters {
  z-index: 99;
}
.el-menu {
  position: fixed;
  z-index: 1100;
}
</style>
