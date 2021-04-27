// vue.config.js

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
    theme_color: '#f15a2a',
    background_color: '#4a4a4a',
    display: 'standalone',
    scope: '/',
    start_url: '/',
    name: 'KPI Application',
    short_name: 'KPI_APP',
    description: 'KPI Application for games, stats and scrutineering',
    icons: [
      {
        src: 'android-icon-192x192.png',
        sizes: '192x192'
      },
      {
        src: 'android-icon-144x144.png',
        sizes: '144x144'
      },
      {
        src: 'android-icon-96x96.png',
        sizes: '96x96'
      },
      {
        src: 'android-icon-72x72.png',
        sizes: '72x72'
      },
      {
        src: 'android-icon-48x48.png',
        sizes: '48x48'
      },
      {
        src: 'android-icon-36x36.png',
        sizes: '36x36'
      },
      {
        src: 'favicon-32x32.png',
        sizes: '32x32'
      },
      {
        src: 'favicon-16x16.png',
        sizes: '16x16'
      }
    ]
  }
}
