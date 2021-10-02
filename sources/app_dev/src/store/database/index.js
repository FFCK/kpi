import VuexORM from '@vuex-orm/core'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Events from '@/store/models/Events'
import Games from '@/store/models/Games'
import Teams from '@/store/models/Teams'
import Players from '@/store/models/Players'
import Status from '@/store/models/Status'

const database = new VuexORM.Database()

database.register(User)
database.register(Preferences)
database.register(Events)
database.register(Games)
database.register(Teams)
database.register(Players)
database.register(Status)

export default database
