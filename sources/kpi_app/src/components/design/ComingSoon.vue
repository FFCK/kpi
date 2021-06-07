<template>
  <div class="container text-center mt-5">
    <el-alert
      :title="$t('nav.CommingSoon')"
      :description="$t('nav.CommingSoonDescription')"
      type="info"
      effect="dark"
      :closable="false"
      center
      show-icon>
    </el-alert>
  </div>
</template>

<script>
export default {
  name: 'CommingSoon'
}
</script>
