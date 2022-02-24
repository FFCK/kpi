import api from '@/network/api'

const force = import.meta.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getGame (eventId, gameId) {
    return api.get('/report/' + eventId + '/game/' + gameId + force)
  }
}
