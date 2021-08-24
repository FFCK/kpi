<template>
  <el-affix target="body" :offset="0">
    <div class="header"></div>
  </el-affix>
</template>

<script>
import { ElAffix } from 'element-plus'

export default {
  name: 'Title',
  components: {
    ElAffix
  },
  props: {
    text: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
.header {
  font-size: 1.7rem;
  height: 56px;
  padding-top: 15px;
  padding-left: 10px;
  margin: 0 66px 0 71px;
  background-color: white;
}
</style>
