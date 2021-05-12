import axios from 'axios'
import { CUSTOM_AUTH_TOKEN } from '@/services/axiosInstance'
import VuexORM from '@vuex-orm/core'
import VuexORMAxios from '@vuex-orm/plugin-axios'
import Photo from '@/store/models/Photo'

VuexORM.use(VuexORMAxios, {
  axios,
  baseURL: process.env.VUE_APP_API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    Authorization: CUSTOM_AUTH_TOKEN
  }
})

const database = new VuexORM.Database()

database.register(Photo)

export default database
