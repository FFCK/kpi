import { Model } from '@vuex-orm/core'

export default class Players extends Model {
  static entity = 'players'

  static fields () {
    return {
      id: this.uid(),
      player_id: this.number(0),
      team_id: this.number(0),
      gender: this.string(''),
      first_name: this.string(''),
      last_name: this.string(''),
      num: this.number(0),
      cap: this.string(''),
      kayak_status: this.number(null).nullable(),
      kayak_print: this.number(null).nullable(),
      vest_status: this.number(null).nullable(),
      vest_print: this.number(null).nullable(),
      helmet_status: this.number(null).nullable(),
      helmet_print: this.number(null).nullable(),
      paddle_count: this.number(null).nullable(),
      paddle_print: this.number(null).nullable()
    }
  }
}
