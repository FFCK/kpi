<template>
  <div
    class="alert alert-warning alert-dismissible fade show"
    role="alert"
    v-if="updateExists"
  >
    <strong>{{ $t("update.Message") }} </strong>
    <button type="button" class="btn btn-sm btn-warning" @click="refreshApp">
      {{ $t("update.Button") }}
    </button>
    <button
      type="button"
      class="btn-close"
      data-bs-dismiss="alert"
      aria-label="Close"
    ></button>
  </div>
</template>

<script>
import update from '@/mixins/update'

export default {
  name: 'Update',
  mixins: [update]
}
</script>
