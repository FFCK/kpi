import { Model } from '@vuex-orm/core'
export default class Preferences extends Model {
  static entity = 'preferences'

  static fields () {
    return {
      id: this.number(0),
      selectedEvent: this.number(0),
      locale: this.string(navigator.language.substr(0, 2)),
      pitches: this.number(4),
      databaseSync: this.boolean(false)
    }
  }
}
