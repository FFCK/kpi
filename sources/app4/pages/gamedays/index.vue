<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { t } = useI18n()
</script>

<template>
  <AdminLegacyRedirect
    php-page="GestionJournee"
    :title="t('legacy_redirect.page_titles.gamedays')"
  />
</template>
