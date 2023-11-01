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
      <li class="notifications">
        <v-dropdown :show-arrow="false">
          <button
            slot="activator"
            href="#"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            class="btn btn-success"
          >
          <font-awesome-icon icon="bell" class="dropdown-item-icon"/> <span class="badge bg-secondary">4</span>
          </button>
          <v-dropdown-item>
            <div class="alert alert-primary" role="alert">
              This is a primary alert—check it out!
            </div>
            <div v-for="(item,index) in listNotifications"
            :key="index">
            {{ item.id }}
        </div>
        </v-dropdown-item>
        </v-dropdown>
      </li>
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
      'user',
      'notifications'
    ]),
    profilePicture () {
      return '/images/default-avatar.jpg'
    },
    role() {
      return this.$store.state.user.currentUser.role
    }, 
    listNotifications() {
      return this.$store.state.userProfile.notifications
    },
  },
  created () {
    this.loadData()
    this.loadNotifications()
  },
  methods: {
    ...mapActions('userProfile', [
      'loadData',
      'loadNotifications',
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
<style>
.notifications .dropdown-group .dropdown-container {
  min-width: 23rem !important;
}
</style>