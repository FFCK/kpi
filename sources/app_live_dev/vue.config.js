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
  publicPath: '',
  pwa: {
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      swSrc: 'src/service-worker.js',
      importWorkboxFrom: 'disabled'
    },
    appleMobileWebAppCapable: true,
    manifestOptions: {
      name: 'KPI Live Application',
      short_name: 'KPI_LIVE_APP',
      theme_color: '#f15a2a',
      background_color: '#4a4a4a',
      display: 'standalone',
      start_url: '.',
      description: 'KPI Live Application'
    }
  }
}
