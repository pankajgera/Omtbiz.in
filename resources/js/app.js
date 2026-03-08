/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */
import { createApp } from 'vue'
import router from './router.js'
import Plugin from './helpers/plugin'
import store from './store/index'
import utils from './helpers/utilities'
import { mapGetters } from 'vuex'
import i18n from './plugins/i18n'
import swal from 'sweetalert'
import { setupBootstrap } from './bootstrap'
import Header from './components/Header.vue'
import mitt from 'mitt'

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = createApp({
  computed: {
    ...mapGetters([
      'isAdmin'
    ])
  },
  methods: {
    onOverlayClick () {
      this.$utils.toggleSidebar()
    }
  }
})

app.component('Header', Header)
app.use(router)
app.use(store)
app.use(i18n)
setupBootstrap(app)

app.config.globalProperties.$utils = utils
app.config.globalProperties.$swal = swal

const hub = mitt()
window.hub = {
  $on: hub.on,
  $emit: hub.emit
}
window.i18n = i18n
window.Plugin = Plugin
window.swal = swal

app.mount('#app')
