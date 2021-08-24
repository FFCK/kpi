// import axios from 'axios'
import VuexORM from '@vuex-orm/core'
// import VuexORMAxios from '@vuex-orm/plugin-axios'
import Photo from '@/store/models/Photo'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Events from '@/store/models/Events'
import Games from '@/store/models/Games'
import Errors from '@/store/models/Errors'

// VuexORM.use(VuexORMAxios, {
//   axios,
//   baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087/api',
//   withCredentials: true,
//   headers: {
//     Accept: 'application/json',
//     'X-Requested-With': 'XMLHttpRequest',
//     'Content-Type': 'application/json'
//   }
// })

const database = new VuexORM.Database()

database.register(Photo)
database.register(User)
database.register(Preferences)
database.register(Events)
database.register(Games)
database.register(Errors)

export default database
