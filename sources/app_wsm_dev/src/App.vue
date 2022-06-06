<template>
  <div>
    <Navbar :key="$route.path" />

    <Update />

    <Message />

    <router-view v-slot="{ Component }">
      <component :is="Component" />
    </router-view>

    <AddToHomeScreen />
  </div>
</template>

<script>
import AddToHomeScreen from '@/components/design/AddToHomeScreen'
import Update from '@/components/design/Update'
import Message from '@/components/design/Message'
import Navbar from '@/components/design/Navbar'

export default {
  components: {
    AddToHomeScreen,
    Update,
    Message,
    Navbar
  }
}
</script>

<style lang="scss">
@import "./assets/styles/custom.scss";
@import "~bootstrap";
@import "~bootstrap-icons/font/bootstrap-icons.css";

@import "./assets/styles/app.scss";

</style>
