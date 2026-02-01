<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { t } = useI18n()
</script>

<template>
  <AdminLegacyRedirect
    php-page="GestionUtilisateur"
    :title="t('legacy_redirect.page_titles.users')"
  />
</template>
