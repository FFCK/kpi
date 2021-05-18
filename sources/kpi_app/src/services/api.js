import axios from 'axios'

const CUSTOM_AUTH_TOKEN = 'CUSTOM_AUTH_TOKEN'

const api = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json'
  }
})

export {
  api,
  CUSTOM_AUTH_TOKEN
}
