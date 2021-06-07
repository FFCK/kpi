<template>
  <div>
    <title-component :text="$t('nav.About')" />

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
  }
}
</script>
