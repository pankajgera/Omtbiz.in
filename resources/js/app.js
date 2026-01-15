/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue from 'vue/dist/vue.esm.js'
import router from './router.js'
import Plugin from './helpers/plugin'
import store from './store/index'
import utils from './helpers/utilities'
import { mapGetters } from 'vuex'
import i18n from './plugins/i18n'
import swal from 'sweetalert'
import './bootstrap'
import Header from './components/Header.vue'

Vue.prototype.$utils = utils
Vue.component('Header', Header)
    /**
     * Next, we will create a fresh Vue application instance and attach it to
     * the page. Then, you may begin adding components to this application
     * or customize the JavaScript scaffolding to fit your unique needs.
     */
window.hub = new Vue()
window.i18n = i18n
window.Plugin = Plugin
const app = new Vue({
    router,
    store,
    i18n,
    swal,
    computed: {
        ...mapGetters([
            'isAdmin'
        ])
    },
    methods: {
        onOverlayClick() {
            this.$utils.toggleSidebar()
        }
    }
}).$mount('#app')
