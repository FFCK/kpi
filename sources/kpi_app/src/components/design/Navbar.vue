<template>
  <div>
    <el-affix
      id="logo"
      target="body"
      :offset="0"
    >
      <el-menu collapse>
        <el-menu-item>
          <router-link to="/about">
            <img
              src="@/assets/logo.png"
              width="30"
              height="30"
              alt="logo"
            >
            <template #title>
              KPI Application
            </template>
          </router-link>
        </el-menu-item>
      </el-menu>
    </el-affix>

    <el-affix
      target="body"
      :offset="0"
    >
      <el-menu>
        <el-menu-item @click="isVisible = !isVisible">
          <i
            v-if="!isVisible"
            class="el-icon-menu"
          />
          <i
            v-if="isVisible"
            class="el-icon-caret-top"
          />
        </el-menu-item>
      </el-menu>
    </el-affix>

    <transition name="el-zoom-in-top">
      <el-affix
        v-show="isVisible"
        target="body"
        :offset="56"
        class="transition-box"
      >
        <el-menu
          default-active="/"
          router
          @select="handleSelect"
        >
          <el-menu-item index="/">
            <i class="el-icon-house" />
            <template #title>
              {{ $t("nav.Home") }}
            </template>
          </el-menu-item>
          <el-menu-item index="/games">
            <i class="el-icon-date" />
            <template #title>
              {{ $t("nav.Games") }}
            </template>
          </el-menu-item>
          <el-menu-item index="/ranking">
            <i class="el-icon-medal" />
            <template #title>
              {{ $t("nav.Ranking") }}
            </template>
          </el-menu-item>
          <el-divider />
          <el-submenu index="1">
            <template #title>
              <i class="el-icon-service" />
              <span>{{ $t("nav.Staff") }}</span>
            </template>
            <el-menu-item index="/login">
              <i class="el-icon-user-solid" />
              <template #title>
                <span v-if="user">{{ $t("nav.MyAccount") }}</span>
                <span v-else>{{ $t("nav.Login") }}</span>
              </template>
            </el-menu-item>
            <el-menu-item-group v-if="user">
              <template #title>
                <span>{{ $t("nav.Staff") }}</span>
              </template>
              <el-menu-item index="/game_report">
                <i class="el-icon-s-order" />
                {{ $t("nav.GameReport") }}
              </el-menu-item>
              <el-menu-item index="/stat_report">
                <i class="el-icon-s-data" />
                {{ $t("nav.StatReport") }}
              </el-menu-item>
              <el-menu-item index="/scrutineering">
                <i class="el-icon-s-claim" />
                {{ $t("nav.Scrutineering") }}
              </el-menu-item>
            </el-menu-item-group>
          </el-submenu>
          <el-submenu index="2">
            <template #title>
              <i class="el-icon-chat-line-round" />
              <span>{{ $t("nav.Lang") }}</span>
            </template>
            <el-menu-item>
              <locale-switcher />
            </el-menu-item>
          </el-submenu>
          <el-menu-item index="/about">
            <i class="el-icon-info" />
            <template #title>
              {{ $t("nav.About") }}
            </template>
          </el-menu-item>
          <el-menu-item
            index="/about"
            disabled
          >
            {{ version }}
          </el-menu-item>
        </el-menu>
      </el-affix>
    </transition>
  </div>
</template>

<script>
import LocaleSwitcher from '@/components/design/LocaleSwitcher'
import { userMixin, prefsMixin } from '@/services/mixins'

export default {
  name: 'Navbar',
  components: {
    LocaleSwitcher
  },
  mixins: [userMixin, prefsMixin],
  data () {
    return {
      isCollapse: true,
      isVisible: false
    }
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
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
