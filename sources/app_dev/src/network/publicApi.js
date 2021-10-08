import api from '@/network/api'

const force = process.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getEvents (mode = 'std') {
    return api.get('/events/' + mode + force)
  },
  checkEvent (eventId) {
    return api.get('/event/' + eventId + force)
  },
  getGames (eventId) {
    return api.get('/games/' + eventId + force)
  },
  getCharts (eventId) {
    return api.get('/charts/' + eventId + force)
  },
  postRating (uid, stars) {
    return api.post('/stars', {
      uid: uid,
      stars: stars
    })
  }
}
