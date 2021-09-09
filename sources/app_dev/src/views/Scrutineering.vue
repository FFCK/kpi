<template>
  <div>
    <title-component :text="$t('nav.Scrutineering')" />

    <coming-soon />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import ComingSoon from '../components/design/ComingSoon.vue'

export default {
  name: 'Scrutineering',
  components: {
    TitleComponent,
    ComingSoon
  }
}
</script>
