<template>
  <div>
    <Navbar :key="$route.path" />

    <Update />

    <Message />

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
import AddToHomeScreen from '@/components/design/AddToHomeScreen'
import Update from '@/components/design/Update'
import Message from '@/components/design/Message'

export default {
  components: {
    Navbar,
    AddToHomeScreen,
    Update,
    Message
  }
}
</script>

<style lang="scss">
@import "./assets/styles/custom.scss";
@import "~bootstrap";
@import "~bootstrap-icons/font/bootstrap-icons.css";
@import "~element-plus/lib/theme-chalk/index.css";
@import "~element-plus/lib/theme-chalk/display.css";

@import "./assets/styles/app.scss";

@font-face {
  font-family: "LCD";
  src: url(./assets/fonts/7segments/7segments.ttf);
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}

input:invalid {
  border: red solid 3px;
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
  margin-top: 62px;
  z-index: 99;
}

.el-menu {
  position: fixed;
  z-index: 1100;
}

.num_match {
  font-style: italic;
  color: #666;
}

.lcd {
  font-family: "LCD", Helvetica, Arial;
  min-width: 26px;
  letter-spacing: 0.2rem;
}

.score {
  font-size: 13px;
  margin-left: 1px;
  margin-right: 1px;
  padding: 0;
}

.team_name {
  font-size: 13px;
  padding: 0 5px;
}

.winner {
  color: #fff;
  background-color: #212529;
  border-color: #212529;
}

.looser {
  color: #000;
  background-color: #ddd;
  border-color: #aaa;
}

.anonymous {
  font-style: italic;
}

.team_logo {
  width: 28px;
}
</style>
