import api from '@/network/api'

// const force = process.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getToken (authToken) {
    return api.post('/api/login', {}, {
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })
  }
}
