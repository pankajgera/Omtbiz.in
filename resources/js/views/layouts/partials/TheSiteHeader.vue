<template>
  <header class="site-header tw:flex tw:h-16 tw:items-center tw:gap-2 tw:border-b tw:border-line tw:bg-surface tw:px-3 tw:text-ink tw:shadow-sm tw:sm:gap-4 tw:sm:px-4 tw:lg:px-5">
    <router-link to="/invoices/create" class="brand-main tw:flex tw:items-center tw:gap-2.5 tw:text-ink tw:no-underline" aria-label="Omtbiz home">
      <img
        id="logo-white"
        src="@/assets/img/bill.png"
        alt="Omtbiz Logo"
      >
      <span class="brand-name tw:text-lg tw:font-bold tw:text-ink">Omtbiz</span>
    </router-link>

    <button
      type="button"
      class="nav-toggle tw:grid tw:size-10 tw:place-items-center tw:rounded-md tw:border tw:border-line tw:bg-surface-muted tw:text-ink"
      aria-label="Toggle navigation"
      @click="onNavToggle"
    >
      <div class="hamburger hamburger--arrowturn">
        <div class="hamburger-box">
          <div class="hamburger-inner"/>
        </div>
      </div>
    </button>
    <ul class="action-list tw:ml-auto tw:flex tw:items-center tw:gap-2">
      <li class="theme-switch">
        <button
          type="button"
          role="switch"
          :aria-checked="isDarkTheme"
          :aria-label="themeLabel"
          :title="themeLabel"
          class="header-icon-button tw:grid tw:size-10 tw:place-items-center tw:rounded-md tw:border tw:border-line tw:bg-surface tw:text-ink-muted tw:transition-colors tw:hover:bg-surface-hover tw:hover:text-ink tw:dark:text-amber-300"
          @click="onThemeToggle"
        >
          <font-awesome-icon :icon="isDarkTheme ? 'sun' : 'moon'" />
        </button>
      </li>
      <li class="notifications">
        <v-dropdown :show-arrow="false">
          <button
            slot="activator"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="Notifications"
            class="header-icon-button tw:grid tw:size-10 tw:place-items-center tw:rounded-md tw:border tw:border-line tw:bg-surface tw:text-ink-muted tw:transition-colors tw:hover:bg-surface-hover tw:hover:text-ink"
          >
          <font-awesome-icon icon="bell" class="dropdown-item-icon"/>
          <span class="notification-count">{{ listNotifications ? listNotifications.length : 0 }}</span>
          </button>
          <v-dropdown-item>
            
            <div v-for="(item,index) in listNotifications"
            :key="index">
            <div class="alert alert-success" role="alert">
             
              <p class="mb-1"> <a :href="'/invoices/create?id='+item.data.id" class="bg-transparent">Estimate Number : <span class="badge badge-success">{{ item.data.estimate_number }}</span> </a>
                <!-- <br/>
                Status : <span class="badge badge-secondary">{{ item.data.status }}</span> -->
              </p>
            </div>
        </div>
        <!-- <button type="button" class="btn btn-link" @click="markAsRead">Mark as read</button> -->
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
            aria-label="Open user menu"
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
import moment from "moment";
import { getActiveTheme, handleStoredThemeChange, toggleTheme } from '../../../helpers/theme'
export default {
  data () {
    return {
      theme: getActiveTheme()
    }
  },
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
      let array = this.$store.state.userProfile.notifications;
      if(array) {
        array = array.slice(0,10)
      }
      return array
    },
    isDarkTheme () {
      return this.theme === 'dark'
    },
    themeLabel () {
      return this.isDarkTheme ? 'Switch to light mode' : 'Switch to dark mode'
    }
  },
  created () {
    this.loadData()
    this.loadNotifications()
  },
  mounted () {
    window.addEventListener('storage', this.onThemeStorage)
  },
  beforeUnmount () {
    window.removeEventListener('storage', this.onThemeStorage)
  },
  methods: {
    getFormattedDate(date) {
      return moment(date).format("DD-MM-YYYY");
    },
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
    },
    onThemeToggle () {
      this.theme = toggleTheme()
    },
    onThemeStorage (event) {
      this.theme = handleStoredThemeChange(event)
    }
  }
}
</script>
