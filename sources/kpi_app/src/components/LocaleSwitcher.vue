<template>
  <div>
    <el-select v-model="$i18n.locale" @change="changeLocale">
      <el-option value="en-US" label="English"></el-option>
      <el-option value="fr-FR" label="Français"></el-option>
    </el-select>
  </div>
</template>

<script>
import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'

export default {
  name: 'LocaleSwitcher',
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
          const navLanguage = navigator.language
          if (this.$i18n.availableLocales.includes(navLanguage)) {
            this.$i18n.locale = navLanguage
          }
        })
    }
  },
  created () {
    this.getLocale()
  }
}
</script>
