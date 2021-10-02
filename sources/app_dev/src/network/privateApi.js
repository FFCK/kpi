import api from '@/network/api'

const force = process.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getToken (authToken) {
    return api.post('/login', {}, {
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })
  },
  getTeams (eventId) {
    return api.get('/staff/' + eventId + '/teams' + force)
  },
  getPlayers (eventId, teamId) {
    return api.get('/staff/' + eventId + '/players/' + teamId + force)
  },
  putPlayer (eventId, teamId, playerId, equipt, val) {
    return api.put('/staff/' + eventId + '/player/' + playerId + '/team/' + teamId + '/' + equipt + '/' + val + force)
  }
}
