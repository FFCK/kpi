<template>
  <el-affix target="body" :offset="0">
    <div class="header">
      <el-page-header
        icon="el-icon-arrow-left"
        :title="$t('nav.Back')"
        :content="text"
        @back="goBack"
      />
    </div>
  </el-affix>
</template>

<script>
export default {
  name: 'Title',
  props: {
    text: {
      type: String,
      required: true
    }
  },
  methods: {
    goBack () {
      this.$router.go(-1)
    }
  }
}
</script>

<style lang="scss" scoped>
.header {
  font-size: 1.7rem;
  height: 56px;
  padding-top: 15px;
  margin: 0 66px 0 100px;
  background-color: white;
}
</style>
