import { Model } from '@vuex-orm/core'

export default class Preferences extends Model {
  static entity = 'preferences'

  static fields () {
    return {
      id: this.number(0),
      locale: this.string(navigator.language.substring(0, 2)),
      event: this.number(0),
      event_name: this.string(''),
      event_place: this.string('')
    }
  }
}
