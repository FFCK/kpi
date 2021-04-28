<template>
  <div class="about">
    <h1>{{ $t('nav.About') }}</h1>

    <HelloWorld :msg="$t('message')" />
  </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from '@/components/HelloWorld.vue'

export default {
  name: 'About',
  components: {
    HelloWorld
  }
}
</script>
