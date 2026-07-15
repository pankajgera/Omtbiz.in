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

      <div class="table-component users-table-component">
        <div class="table-component__table-wrapper">
          <base-loader v-if="isRequestOngoing" class="table-loader" />

          <table class="table-component__table table users-table">
            <caption class="table-component__table__caption">
              {{ $tc('users.user', 2) }}
            </caption>
            <thead class="table-component__table__head">
              <tr>
                <th class="users-selection-column" scope="col">
                  <label class="users-select-all-label" for="select-all-users">
                    <input
                      id="select-all-users"
                      v-model="selectAllFieldStatus"
                      type="checkbox"
                      class="users-checkbox"
                      @change="selectAllUsers"
                    >
                    <span>{{ $t('general.select_all') }}</span>
                  </label>
                </th>
                <th scope="col">{{ $t('users.display_name') }}</th>
                <th scope="col">{{ $t('users.email') }}</th>
                <th scope="col">{{ $t('users.role') }}</th>
                <th scope="col">{{ $t('users.added_on') }}</th>
                <th class="users-action-column" scope="col">
                  <span class="sr-only">{{ $t('users.action') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="table-component__table__body">
              <tr v-for="user in users" :key="user.id">
                <td class="users-selection-column">
                  <input
                    :id="`user-${user.id}`"
                    v-model="selectField"
                    :value="user.id"
                    :aria-label="`Select ${user.name}`"
                    type="checkbox"
                    class="users-checkbox"
                  >
                </td>
                <td :data-label="$t('users.display_name')">
                  <router-link :to="{ path: `users/${user.id}/edit` }">
                    {{ user.name }}
                  </router-link>
                </td>
                <td :data-label="$t('users.email')">{{ user.email }}</td>
                <td :data-label="$t('users.role')">{{ user.role }}</td>
                <td :data-label="$t('users.added_on')">{{ user.formattedCreatedAt }}</td>
                <td class="action-dropdown users-action-column">
                  <v-dropdown :show-arrow="false">
                    <template #activator>
                      <button class="table-row-menu" type="button" :aria-label="$t('users.action')">
                        <dot-icon />
                      </button>
                    </template>
                    <v-dropdown-item>
                      <router-link :to="{ path: `users/${user.id}/edit` }" class="dropdown-item">
                        <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                        {{ $t('general.edit') }}
                      </router-link>
                    </v-dropdown-item>
                    <v-dropdown-item>
                      <button class="dropdown-item" type="button" @click="removeUser(user.id)">
                        <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                        {{ $t('general.delete') }}
                      </button>
                    </v-dropdown-item>
                  </v-dropdown>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!users.length && !isRequestOngoing" class="table-component__message">
          There are no matching rows
        </div>

        <table-pagination
          v-if="pagination.totalPages > 1 && !isRequestOngoing"
          :pagination="pagination"
          @pageChange="loadUsers"
        />
      </div>
    </div>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
import { SweetModal, SweetModalTab } from 'sweet-modal-vue-3'
import DotIcon from '../../components/icon/DotIcon'
import AstronautIcon from '../../components/icon/AstronautIcon'
import BaseButton from '../../../js/components/base/BaseButton'
import BaseLoader from '../../components/base/BaseLoader'
import TablePagination from '../../components/base/base-table/components/Pagination'

export default {
  components: {
    DotIcon,
    AstronautIcon,
    SweetModal,
    SweetModalTab,
    BaseButton,
    BaseLoader,
    TablePagination
  },
  data () {
    return {
      showFilters: false,
      filtersApplied: false,
      isRequestOngoing: true,
      userRequestId: 0,
      pagination: {
        totalPages: 0,
        currentPage: 1,
        count: 0
      },
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
          title:this.$tc('users.user', 2)
        }
      ],
    }
  },
  mounted () {
    this.loadRoles()
    this.loadUsers()
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
  unmounted() {
    if (this.timer) {
      clearTimeout(this.timer)
    }

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
      return this.loadUsers(1)
    },
    async loadUsers (page = 1) {
      const requestId = ++this.userRequestId
      let data = {
        display_name: this.filters.display_name,
        email: this.filters.email,
        role: this.filters.role,
        orderByField: 'created_at',
        orderBy: 'desc',
        page
      }

      this.isRequestOngoing = true
      try {
        const response = await this.fetchUsers(data)

        if (requestId !== this.userRequestId) {
          return
        }

        const usersPage = response.data.users
        this.pagination = {
          totalPages: usersPage.last_page,
          currentPage: usersPage.current_page || page,
          count: this.users.length
        }
      } finally {
        if (requestId === this.userRequestId) {
          this.isRequestOngoing = false
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
          } else if (res.data.error) {
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
