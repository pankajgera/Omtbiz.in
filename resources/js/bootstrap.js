import { VMoney } from 'v-money3'
import VueExpandableImage from 'vue-expandable-image'
import axios from 'axios'
import $ from 'jquery'
import _ from 'lodash'
import toastr from 'toastr'
import { registerFontAwesome } from '../plugins/vue-font-awesome'
import { registerDirectives } from './helpers/directives'
import { registerBaseComponents } from './components/base'
import Ls from './services/ls'
import store from './store/index.js'
import VDropdown from './components/dropdown/VDropdown.vue'
import VDropdownItem from './components/dropdown/VDropdownItem.vue'
import VDropdownDivider from './components/dropdown/VDropdownDivider.vue'
import DotIcon from './components/icon/DotIcon.vue'
import CustomerModal from './components/base/modal/CustomerModal.vue'
import CategoryModal from './components/base/modal/CategoryModal.vue'

/**
 * Global css plugins
 */

export function setupBootstrap (app) {
  window._ = _
  window.axios = axios
  window.Ls = Ls
  window.$ = window.jQuery = $

  window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'Authorization': '',
    'company': 0,
  }

  window.axios.interceptors.request.use(function (config) {
    const AUTH_TOKEN = Ls.get('auth.token')
    if (AUTH_TOKEN) {
      config.headers.set('Authorization', `Bearer ${AUTH_TOKEN}`)
    }

    const companyId = Ls.get('selectedCompany')
    if (companyId) {
      config.headers.set('company', companyId)
    }

    return config
  }, function (error) {
    console.error(error)
    return Promise.reject(error)
  })

  window.axios.interceptors.response.use(undefined, function (err) {
    return new Promise((resolve, reject) => {
      if (err.response.data.error === 'invalid_credentials') {
        window.toastr['error']('Invalid Credentials')
      }
      if (err.response.data && (err.response.statusText === 'Unauthorized' || err.response.data === ' Unauthorized.')) {
        store.dispatch('auth/logout', true)
      } else {
        throw err
      }
    })
  })

  window.toastr = toastr

  registerFontAwesome(app)
  registerDirectives(app)
  registerBaseComponents(app)

  app.directive('money', VMoney)

  if (VueExpandableImage && typeof VueExpandableImage.install === 'function') {
    app.use(VueExpandableImage)
  } else {
    app.component('expandable-image', VueExpandableImage)
  }

  app.component('v-dropdown', VDropdown)
  app.component('v-dropdown-item', VDropdownItem)
  app.component('v-dropdown-divider', VDropdownDivider)

  app.component('dot-icon', DotIcon)
  app.component('customer-modal', CustomerModal)
  app.component('category-modal', CategoryModal)
}
