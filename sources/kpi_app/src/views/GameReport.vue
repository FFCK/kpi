<template>
  <div>
    <title-component :text="$t('nav.GameReport')" />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'GameReport',
  components: {
    TitleComponent
  }
}
</script>
