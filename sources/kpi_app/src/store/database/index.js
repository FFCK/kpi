import VuexORM from '@vuex-orm/core'
import Photo from '@/store/models/Photo'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Events from '@/store/models/Events'
import Games from '@/store/models/Games'
import Errors from '@/store/models/Errors'

const database = new VuexORM.Database()

database.register(Photo)
database.register(User)
database.register(Preferences)
database.register(Events)
database.register(Games)
database.register(Errors)

export default database
