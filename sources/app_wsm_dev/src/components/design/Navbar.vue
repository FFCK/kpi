<template>
  <div class="top-margin">
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
            WSM

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
            <li class="nav-item" v-if="user && user?.profile <= 2">
              <router-link to="/manager" class="nav-link text-nowrap">
                <i class="bi bi-list-ol" />
                {{ $t("nav.Manager") }}
              </router-link>
            </li>
            <li class="nav-item" v-if="user && user?.profile <= 2">
              <router-link to="/faker" class="nav-link text-nowrap">
                <i class="bi bi-radioactive" />
                {{ $t("nav.Faker") }}
              </router-link>
            </li>
            <li class="nav-item" v-if="user">
              <router-link to="/stats" class="nav-link text-nowrap">
                <i class="bi bi-graph-up" />
                {{ $t("nav.Stats") }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/login" class="nav-link text-nowrap">
                <i class="bi bi-person-square" />
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
// import prefsMixin from '@/mixins/prefsMixin'
// import userMixin from '@/mixins/userMixin'
import LocaleSwitcher from '@/components/design/LocaleSwitcher'
import Online from '@/components/design/Online.vue'
import User from '@/store/models/User'

export default {
  name: 'Navbar',
  components: {
    LocaleSwitcher,
    Online
  },
  // mixins: [prefsMixin, userMixin],
  computed: {
    user () {
      return User.query().first()
    }
  },
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

.top-margin {
  margin-top: 62px;
}

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
