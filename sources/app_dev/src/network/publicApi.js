import api from '@/network/api'

const force = process.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getEvents () {
    return api.get('/events' + force)
  },
  getGames (eventId) {
    return api.get('/games/' + eventId + force)
  },
  getCharts (eventId) {
    return api.get('/charts/' + eventId + force)
  }
}
