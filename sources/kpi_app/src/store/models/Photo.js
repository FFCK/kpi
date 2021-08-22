import { Model } from '@vuex-orm/core'

export default class Photo extends Model {
  // Nom de l'entité = nom du module de Vuex Store.
  static entity = 'photo'

  // Liste des champs (schema) du model. `this.attr` est utilisé pour le type
  // de champs. L'argument est la valeur par défaut.
  static fields () {
    return {
      id: this.uid(null),
      albumId: this.number(0),
      title: this.string(''),
      url: this.string(''),
      thumbnailUrl: this.string('')
    }
  }
}
