import { Model } from '@vuex-orm/core'

export default class Preferences extends Model {
  static entity = 'preferences'

  static fields () {
    return {
      id: this.number(0),
      locale: this.string(navigator.language.substr(0, 2)),
      event: this.number(0),
      event_name: this.string(''),
      event_place: this.string(''),
      event_logo: this.string(null).nullable(),
      categorie: this.string(''),
      date: this.string(''),
      pitch: this.string(''),
      fav_categories: this.string('[]'),
      fav_teams: this.string('[]'),
      fav_dates: this.string('')
    }
  }
}
