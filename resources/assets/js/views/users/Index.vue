<template>
  <div class="user-create main-content">
    <div class="page-header">
      <Header :title="$tc('users.title', 2)" :bread-crumb-links="breadCrumbLinks">
        <div class="mr-4 mb-3 mb-sm-0">
          <base-button
            v-show="totalUsers || filtersApplied"
            :outline="true"
            :icon="filterIcon"
            size="large"
            color="theme"
            right-icon
            @click="toggleFilter"
          >
            {{ $t('general.filter') }}
          </base-button>
        </div>
        <div>
          <router-link slot="item-title" class="" to="users/create">
            <base-button
              size="large"
              icon="plus"
              color="theme">
              {{ $t('users.new_user') }}
            </base-button>
          </router-link>
        </div>
      </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-4">
            <label class="form-label">{{ $t('users.display_name') }}</label>
            <base-input
              v-model="filters.display_name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label">{{ $t('users.email') }}</label>
            <base-input
              v-model="filters.email"
              type="text"
              name="address_name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label">{{ $t('users.role') }}</label>
            <base-select
                v-model="filters.role"
                :options="roles"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$tc('users.roles')"
                label="name"
                track-by="id"
              />
          </div>
          <label class="clear-filter" @click="clearFilter">{{ $t('general.clear_all') }}</label>
        </div>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <astronaut-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('users.no_users') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('users.list_of_users') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('users/create')"
        >
          {{ $t('users.add_new_user') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ users.length }}</b> {{ $t('general.of') }} <b>{{ totalUsers }}</b></p>

        <transition name="fade">
          <v-dropdown v-if="selectedUsers.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleUsers">
                <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                {{ $t('general.delete') }}
              </div>
            </v-dropdown-item>
          </v-dropdown>
        </transition>
      </div>

      <div class="custom-control custom-checkbox">
        <input
          id="select-all"
          v-model="selectAllFieldStatus"
          type="checkbox"
          class="custom-control-input"
          @change="selectAllUsers"
        >
        <label for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table"
      >
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="no-click"
        >
          <template slot-scope="row">
            <div class="custom-control custom-checkbox">
              <input
                :id="row.id"
                v-model="selectField"
                :value="row.id"
                type="checkbox"
                class="custom-control-input"
              >
              <label :for="row.id" class="custom-control-label" />
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('users.display_name')"
          show="name"
        />
        <table-column
          :label="$t('users.email')"
          show="email"
        />
        <table-column
          :label="$t('users.role')"
          show="role"
        />
        <table-column
          :label="$t('users.added_on')"
          sort-as="created_at"
          show="formattedCreatedAt"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template slot-scope="row">
            <span> {{ $t('users.action') }} </span>
            <v-dropdown>
              <span slot="activator" href="#">
                <dot-icon />
              </span>
              <v-dropdown-item>

                <router-link :to="{path: `users/${row.id}/edit`}" class="dropdown-item">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
                  {{ $t('general.edit') }}
                </router-link>

              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeUser(row.id)">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </template>
        </table-column>
      </table-component>
    </div>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
import { SweetModal, SweetModalTab } from 'sweet-modal-vue'
import DotIcon from '../../components/icon/DotIcon'
import AstronautIcon from '../../components/icon/AstronautIcon'
import BaseButton from '../../../js/components/base/BaseButton'

export default {
  components: {
    DotIcon,
    AstronautIcon,
    SweetModal,
    SweetModalTab,
    BaseButton
  },
  data () {
    return {
      showFilters: false,
      filtersApplied: false,
      isRequestOngoing: true,
      filters: {
        display_name: '',
        email: '',
        role: ''
      },
      roles: [],
      breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('user.user', 2)
        }
      ],
    }
  },
  mounted () {
    this.loadRoles();
  },
  computed: {
    showEmptyScreen () {
      return !this.totalUsers && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    ...mapGetters('user', [
      'users',
      'selectedUsers',
      'totalUsers',
      'selectAllField'
    ]),
    selectField: {
      get: function () {
        return this.selectedUsers
      },
      set: function (val) {
        this.selectUser(val)
      }
    },
    selectAllFieldStatus: {
      get: function () {
        return this.selectAllField
      },
      set: function (val) {
        this.setSelectAllState(val)
      }
    }
  },
  watch: {
    filters: {
      handler: 'setFilters',
      deep: true
    }
  },
  destroyed () {
    if (this.selectAllField) {
      this.selectAllUsers()
    }
  },
  methods: {
    ...mapActions('user', [
      'fetchUsers',
      'selectAllUsers',
      'selectUser',
      'deleteUser',
      'deleteMultipleUsers',
      'setSelectAllState',
      'fetchRolesAndCompanies'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        display_name: this.filters.display_name,
        email: this.filters.email,
        role: this.filters.role,
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchUsers(data)
      this.isRequestOngoing = false

      return {
        data: response.data.users.data,
        pagination: {
          totalPages: response.data.users.last_page,
          currentPage: page
        }
      }
    },
    setFilters () {
      if (this.timer) {
          clearTimeout(this.timer);
          this.timer = null;
      }
      this.timer = setTimeout(() => {
				this.filtersApplied = true
        this.refreshTable()
			}, 1000);
    },
    clearFilter () {
      this.filters = {
        display_name: '',
        email: '',
        role: ''
      }

      this.$nextTick(() => {
        this.filtersApplied = false
      })
    },
    toggleFilter () {
      if (this.showFilters && this.filtersApplied) {
        this.clearFilter()
        this.refreshTable()
      }

      this.showFilters = !this.showFilters
    },
    async removeUser (id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('users.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteUser(id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('users.deleted_message'))
            this.refreshTable()
            return true
          } else if (request.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    async removeMultipleUsers () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('users.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let request = await this.deleteMultipleUsers()
          if (request.data.success) {
            window.toastr['success'](this.$tc('users.deleted_message', 2))
            this.refreshTable()
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    },
    async loadRoles () {
      let { data: { companies, roles } } = await this.fetchRolesAndCompanies()

      this.roles = roles;
    },
  }
}
</script>
