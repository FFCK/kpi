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
                    <router-link class="nav-link" to="/">
                        Home<span class="sr-only">(current)</span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/game">
                        Game
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/stats">
                        Stats
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/scrutineering">
                        Scrutineering
                    </router-link>
                </li>
                <!-- <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="navbarScrollingDropdown"
                            role="button"
                            data-toggle="dropdown"
                            aria-expanded="false"
                        >
                            Link
                        </a>
                        <ul
                            class="dropdown-menu"
                            aria-labelledby="navbarScrollingDropdown"
                        >
                            <li>
                                <a class="dropdown-item" href="#">Action</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    >Another action</a
                                >
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    >Something else here</a
                                >
                            </li>
                        </ul>
                    </li> -->
            </ul>
            <span class="navbar-nav">
                <LocaleSwitcher />
            </span>
        </div>
    </nav>
</template>

<script>
import LocaleSwitcher from '@/components/LocaleSwitcher'

export default {
  name: 'Nav',
  components: { LocaleSwitcher }
}
</script>

<style>
.navbar a.nav-link.router-link-active {
    color: #ffffff;
}
</style>
