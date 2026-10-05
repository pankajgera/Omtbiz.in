<template>
  <div class="template-container" v-if="isAppLoaded">
    <base-modal />
    <site-header/>
    <site-sidebar type="basic" :role="user.role"/>

    <router-view v-slot="{ Component, route }">
      <component :is="Component" :key="pageKey(route)" />
    </router-view>
    <!-- <site-footer/> -->
  </div>
  <div v-else class="template-container">
    <font-awesome-icon icon="spinner" class="fa-spin"/>
  </div>
</template>
<script type="text/babel">
import SiteHeader from './partials/TheSiteHeader.vue'
import SiteFooter from './partials/TheSiteFooter.vue'
import SiteSidebar from './partials/TheSiteSidebar.vue'
import Layout from '../../helpers/layout'
import BaseModal from '../../components/base/modal/BaseModal'
import { mapActions, mapGetters } from 'vuex'

export default {
  components: {
    SiteHeader, SiteSidebar, SiteFooter, BaseModal
  },
  data () {
    return {
      'header': 'header'
    }
  },
  computed: {
    ...mapGetters([
      'isAppLoaded'
    ]),

    ...mapGetters('company', {
      selectedCompany: 'getSelectedCompany',
      companies: 'getCompanies'
    }),

    ...mapGetters('user', {
      user: 'currentUser'
    }),

    isShow () {
      return true
    },
    Auth() {
      return this.user;
    }
  },
  mounted () {
    Layout.set('layout-default')
  },

  created () {
    this.setInitialCompany()
  },

  methods: {
    // Routes such as invoices/create and invoices/:id/edit share one component. Without a key
    // Vue reuses the open page, so New Invoice kept showing the invoice just edited (and saving
    // it created a copy). Keyed by this layout's child route + params: switching route or record
    // gives a fresh page, while tabs nested inside one page (settings, reports) don't remount it.
    pageKey (route) {
      const page = route.matched[1]
      return (page ? (page.name || page.path) : route.path) + '|' + JSON.stringify(route.params)
    },
    ...mapActions(['bootstrap']),
    ...mapActions('company', ['setSelectedCompany']),
    setInitialCompany () {
      // The API returns only the authenticated user's company. Overwrite any
      // stale or attacker-controlled local selection from older releases.
      this.setSelectedCompany(this.companies[0])
    }
  }
}
</script>
<style lang="scss" scoped>
body {
  background-color: #f8f8f8;
}
</style>
