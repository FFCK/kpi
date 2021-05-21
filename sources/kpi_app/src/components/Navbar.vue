<template>
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">
      <img src="../assets/logo.png" width="30" height="30" alt="logo" />
    </a>
    <button
      class="navbar-toggler"
      type="button"
      data-toggle="collapse"
      data-target="#navbar"
      aria-controls="navbar"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbar">
      <ul class="navbar-nav mr-auto my-2 my-lg-0 navbar-nav-scroll">
        <li class="nav-item">
          <router-link class="nav-link" to="/" @click="collapse">
            {{ $t("nav.Home") }}<span class="sr-only">(current)</span>
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/games" @click="collapse">
            {{ $t("nav.Games") }}
          </router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/ranking" @click="collapse">
            {{ $t("nav.Ranking") }}
          </router-link>
        </li>
        <li class="nav-item dropdown">
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="navbarScrollingDropdown"
            role="button"
            data-toggle="dropdown"
            aria-expanded="false"
          >
            {{ $t("nav.Staff") }}
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
            <li class="nav-item">
              <router-link class="dropdown-item" to="/login" @click="collapse">
                <span v-if="user">{{ $t("nav.MyAccount") }}</span>
                <span v-else>{{ $t("nav.Login") }}</span>
              </router-link>
            </li>
            <li v-if="user"><hr class="dropdown-divider" /></li>
            <li class="nav-item" v-if="user">
              <router-link class="dropdown-item" to="/game_report" @click="collapse">
                {{ $t("nav.GameReport") }}
              </router-link>
            </li>
            <li class="nav-item" v-if="user">
              <router-link class="dropdown-item" to="/stat_report" @click="collapse">
                {{ $t("nav.StatReport") }}
              </router-link>
            </li>
            <li class="nav-item" v-if="user">
              <router-link class="dropdown-item" to="/scrutineering" @click="collapse">
                {{ $t("nav.Scrutineering") }}
              </router-link>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <router-link class="nav-link" to="/about" @click="collapse">
            {{ $t("nav.About") }}
          </router-link>
        </li>
      </ul>
      <span class="navbar-nav">
        <LocaleSwitcher />
      </span>
      <span class="navbar-text"> {{ version }}</span>
    </div>
  </nav>
</template>

<script>
import LocaleSwitcher from '@/components/LocaleSwitcher'
import { userMixin } from '@/services/mixins'
import $ from 'jquery'

export default {
  name: 'Navbar',
  mixins: [userMixin],
  components: { LocaleSwitcher },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  },

  methods: {
    collapse () {
      $('.collapse').collapse('toggle')
    }
  }
}
</script>

<style lang="scss" scoped>
@import "@/assets/styles/custom.scss";

.navbar a.nav-link.router-link-active {
  color: $orange;
}
</style>
