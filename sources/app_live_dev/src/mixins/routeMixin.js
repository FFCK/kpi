const allowedDisplays = ['main', 'match', 'score']
const allowedStyles = ['default', 'thury2014', 'saintomer2017', 'welland2018', 'saintomer2022', 'saintomer2022b']
const allowedLangs = ['en', 'fr']
const allowedZones = ['club', 'inter']
const allowedModes = ['full', 'only', 'events', 'static']
// match : next, -

export default {
  computed: {
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      options: this.$route.params.options || [],
      css: 'default',
      display: 'main',
      mode: 'full',
      zone: 'inter'
    }
  },
  methods: {
    checkOptions () {
      this.options.forEach(option => {
        // display
        if (allowedDisplays.includes(option) && option !== this.display) {
          this.display = option
          if (option === 'score') {
            this.loadCss('score')
          }
        }
        // css
        if (allowedStyles.includes(option) && option !== this.css) {
          const oldCss = this.css
          this.css = option
          this.loadCss(this.css)
          this.trashCss(oldCss)
        }
        // lang
        if (allowedLangs.includes(option) && option !== this.$i18n.locale) {
          this.$i18n.locale = option
        }
        // zone
        if (allowedZones.includes(option) && option !== this.zone) {
          this.zone = option
        }
        // mode
        if (allowedModes.includes(option) && option !== this.mode) {
          this.mode = option
        }
      })
      // console.log('Options : ', this.options)
    },
    loadCss (css) {
      const element = document.createElement('link')
      element.setAttribute('rel', 'stylesheet')
      element.setAttribute('type', 'text/css')
      element.setAttribute('href', this.baseUrl + '/live/css/' + css + '.css?' + this.version)
      document.getElementsByTagName('head')[0].appendChild(element)
    },
    trashCss (oldCss) {
      const element = document.querySelector('link[href="' + this.baseUrl + '/live/css/' + oldCss + '.css?' + this.version + '"]')
      if (element !== null) {
        element.remove()
      }
    }
  }
}
