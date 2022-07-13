import axios from 'axios'

const api = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
    // 'Access-Control-Allow-Origin': '*',
    Pragma: 'no-cache',
    Expires: '0'
  }
})

api.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    console.log(error.response.data)
  }
)

export default api
