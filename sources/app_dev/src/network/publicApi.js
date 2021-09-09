import api from '@/network/api'

export default {
  getEvents () {
    return api.get('/events')
  },
  getGames (eventId) {
    return api.get('/games/' + eventId)
  },
  getCharts (eventId) {
    return api.get('/charts/' + eventId)
  }
}
