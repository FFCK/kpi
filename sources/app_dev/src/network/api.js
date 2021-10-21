import axios from 'axios'
import Status from '@/store/models/Status'
import router from '@/router/index.js'

const api = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
    Pragma: 'no-cache',
    Expires: '0'
  }
})

api.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    if (await error.response.status) {
      Status.update({
        where: 1,
        data: {
          messageText: error.response.data,
          messageClass: 'alert-danger'
        }
      })
      switch (error.response.data) {
        case 'Unauthorized event':
          router.push('Home')
          break

        default:
          router.push('Logout')
          break
      }
    }
  }
)

export default api
