import axios from 'axios'

const CUSTOM_AUTH_TOKEN = 'CUSTOM_AUTH_TOKEN'

const axiosInstance = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    Authorization: CUSTOM_AUTH_TOKEN
  }
})

export {
  axiosInstance,
  CUSTOM_AUTH_TOKEN
}
