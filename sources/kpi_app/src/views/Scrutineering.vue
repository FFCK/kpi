<template>
  <div>
    <title-component
      :text="$t('nav.Scrutineering')"
    />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'Scrutineering',
  components: {
    TitleComponent
  }
}
</script>
