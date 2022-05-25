import VuexORM from '@vuex-orm/core'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Events from '@/store/models/Events'
import Games from '@/store/models/Games'
import Teams from '@/store/models/Teams'
import Players from '@/store/models/Players'
import Status from '@/store/models/Status'
import GameReports from '@/store/models/GameReports'
import GameReportEvents from '@/store/models/GameReportEvents'
import GameReportPlayers from '@/store/models/GameReportPlayers'

const database = new VuexORM.Database()

database.register(User)
database.register(Preferences)
database.register(Events)
database.register(Games)
database.register(Teams)
database.register(Players)
database.register(Status)
database.register(GameReports)
database.register(GameReportEvents)
database.register(GameReportPlayers)

export default database
