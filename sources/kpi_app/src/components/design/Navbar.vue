<template>
  <div>
    <nav class="navbar fixed-top navbar-expand-md navbar-dark bg-dark">
      <div class="container-fluid">
        <div>
          <router-link to="/" class="navbar-brand">
            <img
              src="@/assets/logo.png"
              width="30"
              height="30"
              alt="logo"
              class="d-inline-block align-middle"
            />
            KPI App

            <online />
          </router-link>
        </div>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarToggler"
          aria-controls="navbarToggler"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon" />
        </button>
        <div id="navbarToggler" class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link text-nowrap active">
                <i class="bi bi-house" />
                {{ $t("nav.Home") }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/games" class="nav-link text-nowrap">
                <i class="bi bi-list-ol" />
                {{ $t("nav.Games") }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/chart" class="nav-link text-nowrap">
                <i class="bi bi-diagram-2" />
                {{ $t("nav.Chart") }}
              </router-link>
            </li>
            <li class="nav-item dropdown">
              <a
                id="navbarDropdown"
                class="nav-link text-nowrap dropdown-toggle"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="el-icon-service" />
                {{ $t("nav.Staff") }}
              </a>
              <ul
                class="dropdown-menu bg-dark"
                aria-labelledby="navbarDropdown"
              >
                <li v-if="user" class="nav-item">
                  <router-link to="/login" class="nav-link text-nowrap">
                    <i class="bi bi-person-circle" />
                    {{ $t("nav.MyAccount") }}
                  </router-link>
                </li>
                <li v-else class="nav-item">
                  <router-link to="/login" class="nav-link text-nowrap">
                    <i class="bi bi-person-circle" />
                    {{ $t("nav.Login") }}
                  </router-link>
                </li>
                <li v-if="user" class="nav-item">
                  <router-link to="/game_report" class="nav-link text-nowrap">
                    <i class="bi bi-clipboard" />
                    {{ $t("nav.GameReport") }}
                  </router-link>
                </li>
                <li v-if="user" class="nav-item">
                  <router-link to="/stat_report" class="nav-link text-nowrap">
                    <i class="bi bi-clipboard-data" />
                    {{ $t("nav.StatReport") }}
                  </router-link>
                </li>
                <li v-if="user" class="nav-item">
                  <router-link to="/scrutineering" class="nav-link text-nowrap">
                    <i class="bi bi-clipboard-check" />
                    {{ $t("nav.Scrutineering") }}
                  </router-link>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <router-link to="/about" class="nav-link text-nowrap">
                <i class="el-icon-info" />
                {{ $t("nav.About") }}
              </router-link>
            </li>
          </ul>

          <locale-switcher />
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
import LocaleSwitcher from '@/components/design/LocaleSwitcher'
import { userMixin, prefsMixin } from '@/services/mixins'
import Online from '@/components/design/Online.vue'

export default {
  name: 'Navbar',
  components: {
    LocaleSwitcher,
    Online
  },
  mixins: [userMixin, prefsMixin],
  data () {
    return {
      isCollapse: true,
      isVisible: false
    }
  },
  methods: {
    handleSelect (key, keyPath) {
      this.isVisible = false
    }
  }
}
</script>

<style lang="scss" scoped>
@import "@/assets/styles/custom.scss";

.navbar a.nav-link.router-link-active {
  color: $orange;
}

transition {
  z-index: 2000;
}

.el-menu-vertical-demo:not(.el-menu--collapse) {
  width: 200px;
  min-height: 400px;
}

#logo .el-menu {
  right: 0;
}
</style>
