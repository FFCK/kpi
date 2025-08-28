import { ref, computed, onMounted } from 'vue'

export function useUser(prefs) {
  const authorized = ref(false)
  const user = computed(() => User.query().first())

  async function getUser() {
    if (User.query().count() === 0) {
      const result = await idbs.dbGetAll('user')
      if (result.length === 1) {
        User.insertOrUpdate({ data: result })
      }
    }
  }

  async function checkAuthorized() {
    await getUser()
    if (user.value && prefs?.event !== undefined) {
      const userEvents = user.value.events.split('|').map(e => parseInt(e))
      authorized.value = userEvents.includes(prefs.event)
    } else {
      authorized.value = false
    }
  }

  onMounted(getUser)

  return {
    user,
    authorized,
    getUser,
    checkAuthorized
  }
}
