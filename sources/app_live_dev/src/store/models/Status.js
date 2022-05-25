import { Model } from '@vuex-orm/core'

export default class Status extends Model {
  static entity = 'status'

  static fields () {
    return {
      id: this.number(1),
      online: this.boolean(true),
      messageText: this.string(''),
      messageClass: this.string('alert-info')
    }
  }
}
