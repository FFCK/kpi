import { Model } from '@vuex-orm/core'
import { v4 as uuid } from 'uuid'
export default class Preferences extends Model {
  static entity = 'preferences'

  static fields () {
    return {
      id: this.number(0),
      uid: this.uid(() => uuid()),
      locale: this.string(navigator.language.substr(0, 2)),
      events: this.string('std'),
      event: this.number(0),
      event_name: this.string(''),
      event_place: this.string(''),
      event_logo: this.string(null).nullable(),
      categorie: this.string(''),
      date: this.string(''),
      pitch: this.string(''),
      fav_categories: this.string('[]'),
      fav_teams: this.string('[]'),
      fav_dates: this.string(''),
      show_flags: this.boolean(true),
      stars: this.number(null).nullable(),
      scr_team_id: this.number(null).nullable(),
      scr_team_label: this.string(null).nullable(),
      scr_team_club: this.string(null).nullable(),
      scr_team_logo: this.string(null).nullable(),
      current_game_id: this.number(null).nullable()
    }
  }
}
