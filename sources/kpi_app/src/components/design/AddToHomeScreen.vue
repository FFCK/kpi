<template>
  <nav
    v-if="deferredPrompt"
    class="navbar fixed-bottom navbar-light bg-light"
  >
    <span class="navbar-text">
      {{ $t("AddToHomeScreen.message") }}
    </span>

    <form class="form-inline">
      <button
        class="btn btn-sm btn-outline-secondary mx-2 my-sm-0"
        @click="dismiss"
      >
        <span class="bi bi-x text-primary" />
        {{ $t("AddToHomeScreen.Dismiss") }}
      </button>
      <button
        class="btn btn-sm btn-outline-primary mx-2 my-sm-0"
        @click="install"
      >
        <span class="bi bi-house-fill text-primary" />
        {{ $t("AddToHomeScreen.Install") }}
      </button>
    </form>
  </nav>
</template>

<script>
import Cookies from 'js-cookie'

export default {
  name: 'AddToHomeScreen',
  data () {
    return {
      deferredPrompt: null
    }
  },
  created () {
    // Si l'application n'est pas déjà installée
    window.addEventListener('beforeinstallprompt', e => {
      e.preventDefault()
      // S'il n'y a pas de cookie
      if (Cookies.get('add-to-home-screen') === undefined) {
        // On stocke l'événement pour le traiter au moment du clic sur Install
        this.deferredPrompt = e
      }
    })
    // Si l'application est déjà installée, on ne fait rien
    window.addEventListener('appinstalled', () => {
      this.deferredPrompt = null
    })
  },
  methods: {
    async dismiss (e) {
      e.preventDefault()
      // Refus de l'utilisateur : on ne lui redemandera pas avant 15 jours
      Cookies.set('add-to-home-screen', null, { expires: 15 })
      this.deferredPrompt = null
    },
    async install (e) {
      e.preventDefault()
      // L'utilisateur veut installer, on lance le prompt du navigateur lié à l'événement beforeinstallprompt
      this.deferredPrompt.prompt()
    }
  }
}
</script>
