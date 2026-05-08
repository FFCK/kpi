import { defineStore } from 'pinia'

type ImageType = 'logo_club' | 'logo_nation' | 'logo_competition' | 'bandeau_competition' | 'sponsor_competition'

const SESSION_KEY = 'imageVersions'

function loadFromSession(): Record<string, number> {
  if (typeof sessionStorage === 'undefined') return {}
  try {
    return JSON.parse(sessionStorage.getItem(SESSION_KEY) || '{}')
  } catch {
    return {}
  }
}

function saveToSession(versions: Record<string, number>) {
  if (typeof sessionStorage === 'undefined') return
  sessionStorage.setItem(SESSION_KEY, JSON.stringify(versions))
}

export const useImageVersionStore = defineStore('imageVersion', () => {
  const versions = ref<Record<string, number>>(loadFromSession())

  function get(type: ImageType): number {
    if (!versions.value[type]) {
      versions.value[type] = Date.now()
      saveToSession(versions.value)
    }
    return versions.value[type]
  }

  function bump(type: ImageType) {
    versions.value[type] = Date.now()
    saveToSession(versions.value)
  }

  return { get, bump }
})
