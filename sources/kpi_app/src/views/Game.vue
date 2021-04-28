<template>
  <div class="container-sm">
    <h1>{{ $t('nav.Games') }}</h1>
    <HelloWorld :msg="$t('message')" />
  </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from '@/components/HelloWorld.vue'

export default {
  name: 'Game',
  components: {
    HelloWorld
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
