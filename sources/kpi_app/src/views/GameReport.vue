<template>
  <div>
    <title-component :text="$t('nav.GameReport')" />

    <coming-soon />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import ComingSoon from '@/components/design/ComingSoon.vue'

export default {
  name: 'GameReport',
  components: {
    TitleComponent,
    ComingSoon
  }
}
</script>
