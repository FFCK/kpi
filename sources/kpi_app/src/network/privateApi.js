import api from '@/network/api'

export default {
  getToken (authToken) {
    return api.post('/login', {}, {
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })
  }
}
