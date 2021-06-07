<template>
  <div>
    <title-component :text="$t('nav.Ranking')" />

    <coming-soon />

  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import ComingSoon from '@/components/design/ComingSoon.vue'

export default {
  name: 'Ranking',
  components: {
    TitleComponent,
    ComingSoon
  }
}
</script>
