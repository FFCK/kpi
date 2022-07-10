// vue.config.js
process.env.VUE_APP_VERSION = require('./package.json').version

/**
 * @type {import('@vue/cli-service').ProjectOptions}
 */
module.exports = {
  pluginOptions: {
    i18n: {
      locale: 'en',
      fallbackLocale: 'en',
      localeDir: 'locales',
      enableInSFC: false,
      enableLegacy: true,
      runtimeOnly: false,
      compositionOnly: true,
      fullInstall: true
    }
  },
  configureWebpack: {
    devtool: 'source-map'
  },
  publicPath: ''
}
