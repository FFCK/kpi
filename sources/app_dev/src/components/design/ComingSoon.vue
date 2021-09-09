<template>
  <div class="container text-center mt-5">
    <el-alert
      :title="$t('nav.CommingSoon')"
      :description="$t('nav.CommingSoonDescription')"
      type="info"
      effect="dark"
      :closable="false"
      center
      show-icon
    />
  </div>
</template>

<script>
import {
  ElAlert
} from 'element-plus'

export default {
  name: 'CommingSoon',
  components: {
    ElAlert
  }
}
</script>
