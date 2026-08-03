<template>
  <div class="invoice-create-page main-content tw:bg-canvas tw:text-ink">
    <div class="page-header">
      <h3 class="page-title">{{ $tc('settings.setting',1) }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/settings/user-profile">{{ $tc('settings.setting', 2) }}</router-link></li>
      </ol>
    </div>
    <div class="row settings-container tw:items-start">
      <div class="col-lg-3 settings-sidebar-container">
        <ol class="settings-sidebar">
          <li v-for="(menuItem, index) in menuItems.filter(each => each.meta.includes(role))" :key="index" class="settings-menu-item">
            <router-link :class="['link-color tw:no-underline', {'active-setting': hasActiveUrl(menuItem.link)}]" :to="menuItem.link">
              <font-awesome-icon :icon="[menuItem.iconType, menuItem.icon]" class="setting-icon"/>
              <span class="menu-title ml-3">{{ $t(menuItem.title) }}</span>
            </router-link>
          </li>
          <li v-if="'admin' === role" class="settings-menu-item">
            <button type="button" class="link-color ml-2 tw:border-0 tw:bg-transparent tw:text-left" @click.stop="showDeleteDataModal">
              <svg aria-hidden="true" focusable="false" data-prefix="fa" data-icon="trash" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="setting-icon svg-inline--fa fa-trash fa-w-14">
                <path fill="currentColor" d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"></path>
              </svg>
              <span class="menu-title ml-3 pl-1">All Data</span>
            </button>
          </li>
        </ol>
      </div>
      <div class="col-lg-9 tw:min-w-0">
        <router-view v-slot="{ Component }">
          <transition
            name="fade"
            mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>
<script>
import swal from 'sweetalert'

export default {
  data () {
    return {
      menuItems: [
        {
          link: '/settings/user-profile',
          title: 'settings.menu_title.account_settings',
          icon: 'user',
          iconType: 'far',
          meta: ['admin', 'accountant', 'employee']
        },
        {
          link: '/settings/company-info',
          title: 'settings.menu_title.company_information',
          icon: 'building',
          iconType: 'far',
          meta: ['admin']
        },
        {
          link: '/settings/customization',
          title: 'settings.menu_title.customization',
          icon: 'edit',
          iconType: 'fa',
          meta: ['admin']
        },
        {
          link: '/settings/preferences',
          title: 'settings.menu_title.preferences',
          icon: 'cog',
          iconType: 'fas',
          meta: ['admin']
        },
        {
          link: '/settings/expense-category',
          title: 'settings.menu_title.expense_category',
          icon: 'list-alt',
          iconType: 'far',
          meta: ['admin']
        },
        {
          link: '/settings/mail-configuration',
          title: 'settings.mail.mail_config',
          icon: 'envelope',
          iconType: 'fa',
          meta: ['admin']
        },
        {
          link: '/settings/notifications',
          title: 'settings.menu_title.notifications',
          icon: 'bell',
          iconType: 'far',
          meta: ['admin']
        },
      ],
      role: this.$store.state.user.currentUser.role
    }
  },
  watch: {
    '$route.path' (newValue) {
      if (newValue === '/settings') {
        this.$router.push('/settings/user-profile')
      }
    }
  },
  created () {
    if (this.$route.path === '/settings') {
      this.$router.push('/settings/user-profile')
    }
  },
  methods: {
    async showDeleteDataModal () {
      const initialConfirmation = await swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('settings.delete_data.initial_warning'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      })

      if (!initialConfirmation) {
        return
      }

      const confirmation = await swal({
        title: this.$t('settings.delete_data.confirmation_title'),
        text: this.$t('settings.delete_data.confirmation_instruction'),
        content: {
          element: 'input',
          attributes: {
            placeholder: 'DELETE ALL DATA',
            autocomplete: 'off',
            spellcheck: false
          }
        },
        buttons: {
          cancel: true,
          confirm: {
            text: this.$t('settings.delete_data.delete_button'),
            className: 'swal-button--danger'
          }
        },
        dangerMode: true,
        closeOnClickOutside: false
      })

      if (confirmation !== 'DELETE ALL DATA') {
        if (confirmation !== null) {
          await swal({
            title: this.$t('settings.delete_data.incorrect_title'),
            text: this.$t('settings.delete_data.incorrect_message'),
            icon: 'error'
          })
        }
        return
      }

      try {
        await window.axios.delete('/api/settings/data/delete', {
          data: { confirmation }
        })
        window.toastr['success'](this.$t('settings.delete_data.queued_message'))
      } catch (error) {
        window.toastr['error'](error.response?.data?.message || this.$t('general.action_failed'))
      }
    },
    hasActiveUrl (url) {
      return this.$route.path.indexOf(url) > -1
    }
  }
}
</script>
