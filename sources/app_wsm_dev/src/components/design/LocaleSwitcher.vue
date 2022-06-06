<template>
  <form>
    <select
      v-model="$i18n.locale"
      class="form-select form-select-sm"
      aria-label="Locale"
      @change="changeLocale"
    >
      <option value="en">
        English
      </option>
      <option value="fr">
        Français
      </option>
    </select>
  </form>
</template>

<script>
import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'
import prefsMixin from '@/mixins/prefsMixin'

export default {
  name: 'LocaleSwitcher',
  mixins: [prefsMixin],
  created () {
    this.getLocale()
  },
  methods: {
    changeLocale () {
      Preferences.update({
        where: 1,
        data: {
          locale: this.$i18n.locale
        }
      })
      idbs.dbPut('preferences', Preferences.query().first())
    },
    getLocale () {
      idbs.dbGet('preferences', 1)
        .then(result => {
          this.$i18n.locale = result.locale
        }).catch(_ => {
          const navLanguage = navigator.language.substr(0, 2)
          if (this.$i18n?.availableLocales.includes(navLanguage)) {
            this.$i18n.locale = navLanguage
          }
        })
    }
  }
}
</script>
