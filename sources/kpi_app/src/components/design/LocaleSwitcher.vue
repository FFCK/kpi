<template>
  <div>
    <el-select
      v-model="$i18n.locale"
      @change="changeLocale"
    >
      <el-option
        value="en"
        label="English"
      />
      <el-option
        value="fr"
        label="Français"
      />
    </el-select>
  </div>
</template>

<script>
import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'

export default {
  name: 'LocaleSwitcher',
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
      idbs.dbPut('preferences', Preferences.find(1))
    },
    getLocale () {
      idbs.dbGet('preferences', 1)
        .then(result => {
          this.$i18n.locale = result.locale
        }).catch(_ => {
          const navLanguage = navigator.language.substr(0, 2)
          if (this.$i18n.availableLocales.includes(navLanguage)) {
            this.$i18n.locale = navLanguage
          }
        })
    }
  }
}
</script>
