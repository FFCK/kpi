<template>
  <div
    class="alert alert-warning alert-dismissible fade show"
    role="alert"
    v-if="updateExists"
  >
    <strong>{{ $t("Update.Message") }} </strong>
    <button
      type="button"
      class="btn btn-sm btn-warning ms-2"
      @click="refreshApp"
    >
      {{ $t("Update.Button") }}
    </button>
  </div>
</template>

<script>
import updateMixin from '@/mixins/updateMixin'

export default {
  name: 'Update',
  mixins: [updateMixin]
}
</script>
