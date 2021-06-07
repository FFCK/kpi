<template>
  <div>

    <el-affix target="body" :offset="0" id="logo">
      <el-menu collapse>
        <el-menu-item>
          <img src="@/assets/logo.png" width="30" height="30" alt="logo" />
          <template #title>KPI Application</template>
        </el-menu-item>
      </el-menu>
    </el-affix>

    <el-affix target="body" :offset="0">
      <el-menu>
        <el-menu-item>
          <i class="el-icon-menu" v-if="!isVisible" @click="isVisible = !isVisible"></i>
          <i class="el-icon-caret-top" v-if="isVisible" @click="isVisible = !isVisible"></i>
        </el-menu-item>
      </el-menu>
    </el-affix>

    <transition name="el-zoom-in-top">
      <el-affix target="body" :offset="56" v-show="isVisible" class="transition-box">
        <el-menu default-active="/" @open="handleOpen" @close="handleClose" router>
          <el-menu-item index="/">
            <i class="el-icon-house"></i>
            <template #title>{{ $t("nav.Home") }}</template>
          </el-menu-item>
          <el-menu-item index="/games">
            <i class="el-icon-date"></i>
            <template #title>{{ $t("nav.Games") }}</template>
          </el-menu-item>
          <el-menu-item index="/ranking">
            <i class="el-icon-medal"></i>
            <template #title>{{ $t("nav.Ranking") }}</template>
          </el-menu-item>
          <el-divider></el-divider>
          <el-submenu index="1">
            <template #title>
              <i class="el-icon-service"></i>
              <span>{{ $t("nav.Staff") }}</span>
            </template>
            <el-menu-item index="/login">
              <i class="el-icon-user-solid"></i>
              <template #title>
                <span v-if="user">{{ $t("nav.MyAccount") }}</span>
                <span v-else>{{ $t("nav.Login") }}</span>
              </template>
            </el-menu-item>
            <el-menu-item-group v-if="user">
              <template #title><span>{{ $t("nav.Staff") }}</span></template>
              <el-menu-item index="/game_report">
                <i class="el-icon-s-order"></i>
                {{ $t("nav.GameReport") }}
              </el-menu-item>
              <el-menu-item index="/stat_report">
                <i class="el-icon-s-data"></i>
                {{ $t("nav.StatReport") }}
              </el-menu-item>
              <el-menu-item index="/scrutineering">
                <i class="el-icon-s-claim"></i>
                {{ $t("nav.Scrutineering") }}
              </el-menu-item>
            </el-menu-item-group>
          </el-submenu>
          <el-submenu index="2">
            <template #title>
              <i class="el-icon-chat-line-round"></i>
              <span>{{ $t("nav.Lang") }}</span>
            </template>
            <el-menu-item>
              <locale-switcher />
            </el-menu-item>
          </el-submenu>
          <el-menu-item index="/about">
            <i class="el-icon-info"></i>
            <template #title>{{ $t("nav.About") }}</template>
          </el-menu-item>
          <el-menu-item index="/about" disabled>
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
  mixins: [userMixin, prefsMixin],
  components: {
    LocaleSwitcher
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  },
  data () {
    return {
      isCollapse: true,
      isVisible: false
    }
  },
  methods: {
    handleOpen (key, keyPath) {
      console.log(key, keyPath)
    },
    handleClose (key, keyPath) {
      console.log(key, keyPath)
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
