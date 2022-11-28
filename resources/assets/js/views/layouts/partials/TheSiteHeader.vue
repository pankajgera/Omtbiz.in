<template>
  <header class="site-header">
    <a href="/" class="brand-main">
      <img
        id="logo-white"
        src="/assets/img/bill.png"
        alt="Omtbiz Logo"
        class="d-none d-md-inline"
      >
      <img
        id="logo-mobile"
        src="/assets/img/bill.png"
        alt="Laraspace Logo"
        class="d-md-none">
    </a>

    <a
      href="#"
      class="nav-toggle"
      @click="onNavToggle"
    >
      <div class="hamburger hamburger--arrowturn">
        <div class="hamburger-box">
          <div class="hamburger-inner"/>
        </div>
      </div>
    </a>
    <ul class="action-list">
      <li>
        <v-dropdown :show-arrow="false">
          <a
            slot="activator"
            href="#"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            class="avatar"
          >
            <img :src="profilePicture" alt="Avatar">
          </a>
          <v-dropdown-item>
            <router-link class="dropdown-item" to="/settings">
              <font-awesome-icon icon="cogs" class="dropdown-item-icon"/> <span> {{ $t('navigation.settings') }} </span>
            </router-link>
          </v-dropdown-item>
          <v-dropdown-item>
            <a
              href="#"
              class="dropdown-item"
              @click.prevent="logout"
            >
              <font-awesome-icon icon="sign-out-alt" class="dropdown-item-icon"/> <span> {{ $t('navigation.logout') }} </span>
            </a>
          </v-dropdown-item>
        </v-dropdown>
      </li>
    </ul>
  </header>
</template>
<script type="text/babel">
import { mapGetters, mapActions } from 'vuex'

export default {
  computed: {
    ...mapGetters('userProfile', [
      'user'
    ]),
    profilePicture () {
      return '/images/default-avatar.jpg'
    },
    role() {
      return this.$store.state.user.currentUser.role
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    ...mapActions('userProfile', [
      'loadData'
    ]),
    ...mapActions({
      companySelect: 'changeCompany'
    }),
    ...mapActions('auth', [
      'logout'
    ]),
    onNavToggle () {
      this.$utils.toggleSidebar()
    }
  }
}
</script>
