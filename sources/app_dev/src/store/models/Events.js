import { Model } from '@vuex-orm/core'

export default class Events extends Model {
  // Nom de l'entité = nom du module de Vuex Store.
  static entity = 'events'

  // Liste des champs (schema) du model. `this.attr` est utilisé pour le type
  // de champs. L'argument est la valeur par défaut.
  static fields () {
    return {
      id: this.uid(null),
      libelle: this.string(''),
      place: this.string(''),
      logo: this.string(null).nullable()
    }
  }
}
