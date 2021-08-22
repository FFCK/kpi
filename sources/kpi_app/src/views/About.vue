<template>
  <div>
    <title-component :text="$t('nav.About')" />

    {{ version }}

    <coming-soon />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import ComingSoon from '@/components/design/ComingSoon.vue'

export default {
  name: 'About',
  components: {
    TitleComponent,
    ComingSoon
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  }
}
</script>
