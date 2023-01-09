<template>
  <div class="items order-index-page main-content">
    <div class="page-header">
      <Header :title="$tc('orders.order', 2)" :bread-crumb-links="breadCrumbLinks">
        <div v-show="totalPendingOrders || totalCompletedOrders || filtersApplied" class="mr-4 mb-3 mb-sm-0">
          <base-button
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
          <router-link slot="item-title" to="/orders/create">
            <base-button
              size="large"
              icon="plus"
              color="theme">
              {{ $t('orders.new_order') }}
            </base-button>
          </router-link>
        </div>
      </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="filter-container">
          <div class="filter-customer">
            <label>{{ $tc('customers.customer',1) }} </label>
              <base-select
              v-model="filters.customer"
              ref="customerSelect"
              :options="sundryDebtorsList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              label="name"
              track-by="id"
              @select="onSelectCustomer"
              @deselect="clearCustomerSearch"
            />
          </div>
          <div class="filter-date">
            <div class="from pr-3">
              <label>{{ $t('general.from') }}</label>
              <base-date-picker
                v-model="filters.from_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
              />
            </div>
            <div class="dashed" />
            <div class="to pl-3">
              <label>{{ $t('general.to') }}</label>
              <base-date-picker
                v-model="filters.to_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
              />
            </div>
          </div>
          <div class="filter-order">
            <label>{{ $t('orders.order_number') }}</label>
            <base-input
              v-model="filters.order_number"
              icon="hashtag"/>
          </div>
        </div>
        <label class="clear-filter" @click="clearFilter">{{ $t('general.clear_all') }}</label>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('orders.no_orders') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('orders.list_of_orders') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('orders/create')"
        >
          {{ $t('orders.new_order') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <h4>Pending Orders</h4>
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ pendingOrders.length }}</b> {{ $t('general.of') }} <b>{{ totalPendingOrders }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedOrders && selectedOrders.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleOrders">
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
          @change="selectAllOrders"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="getPendingOrders"
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
              <label :for="row.id" class="custom-control-label"/>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('orders.number')"
          show="order_number"
        >
          <template slot-scope="row">
            <router-link :to="{path: `orders/${row.id}/edit?d=true`}" class="dropdown-item">
               {{ row.order_number }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('orders.date')"
          sort-as="order_date"
          show="formattedOrderDate"
        />
        <table-column
          :label="$t('orders.name')"
          width="20%"
          show="master.name"
        />
        <table-column
          :label="$t('orders.count')"
          width="20%"
          show="order_items.length"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('orders.action') }}</span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
              <v-dropdown-item>
                <router-link :to="{path: `orders/${row.id}/edit`}" class="dropdown-item" v-if="role === 'admin'">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
                  {{ $t('general.edit') }}
                </router-link>
                <!-- <router-link :to="{path: `orders/${row.id}/view`}" class="dropdown-item">
                  <font-awesome-icon icon="eye" class="dropdown-item-icon" />
                  {{ $t('orders.view') }}
                </router-link> -->
              </v-dropdown-item>
              <!-- <v-dropdown-item v-if="row.status == 'DRAFT'">
                <a class="dropdown-item" href="#/" @click="sendOrder(row.id)" v-if="role === 'admin'">
                  <font-awesome-icon icon="paper-plane" class="dropdown-item-icon" />
                  {{ $t('orders.send_order') }}
                </a>
              </v-dropdown-item> -->
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeOrder(row.id)" v-if="role === 'admin'">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </template>
        </table-column>
      </table-component>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <h4>Completed Orders</h4>
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ completedOrders.length }}</b> {{ $t('general.of') }} <b>{{ totalCompletedOrders }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedOrders && selectedOrders.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleOrders">
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
          @change="selectAllOrders"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="getCompletedOrders"
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
              <label :for="row.id" class="custom-control-label"/>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('orders.number')"
          show="order_number"
        >
          <template slot-scope="row">
            <router-link :to="{path: `orders/${row.id}/edit?d=true`}" class="dropdown-item">
               {{ row.order_number }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('orders.date')"
          sort-as="order_date"
          show="formattedOrderDate"
        />
        <table-column
          :label="$t('orders.name')"
          width="20%"
          show="master.name"
        />
        <table-column
          :label="$t('orders.count')"
          width="20%"
          show="order_items.length"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('orders.action') }}</span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
              <v-dropdown-item>
                <router-link :to="{path: `orders/${row.id}/edit`}" class="dropdown-item" v-if="role === 'admin'">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
                  {{ $t('general.edit') }}
                </router-link>
                <!-- <router-link :to="{path: `orders/${row.id}/view`}" class="dropdown-item">
                  <font-awesome-icon icon="eye" class="dropdown-item-icon" />
                  {{ $t('orders.view') }}
                </router-link> -->
              </v-dropdown-item>
              <!-- <v-dropdown-item v-if="row.status == 'DRAFT'">
                <a class="dropdown-item" href="#/" @click="sendOrder(row.id)" v-if="role === 'admin'">
                  <font-awesome-icon icon="paper-plane" class="dropdown-item-icon" />
                  {{ $t('orders.send_order') }}
                </a>
              </v-dropdown-item> -->
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeOrder(row.id)" v-if="role === 'admin'">
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
import MoonWalkerIcon from '../../../js/components/icon/MoonwalkerIcon'
import moment from 'moment'
import BaseButton from '../../components/base/BaseButton'

export default {
  components: {
    'moon-walker-icon': MoonWalkerIcon,
    BaseButton,
  },
  data () {
    return {
      showFilters: false,
      breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('orders.order', 2)
        }
      ],
      status: [
        {
          label: 'Status',
          isDisable: true,
          options: [
            { name: 'DRAFT', value: 'DRAFT' },
            { name: 'DUE', value: 'UNPAID' },
            { name: 'SENT', value: 'SENT' },
            { name: 'VIEWED', value: 'VIEWED' },
            { name: 'OVERDUE', value: 'OVERDUE' },
            { name: 'COMPLETED', value: 'COMPLETED' }
          ]
        },
        {
          label: 'Paid Status',
          options: [
            { name: 'UNPAID', value: 'UNPAID' },
            { name: 'PAID', value: 'PAID' },
            { name: 'PARTIALLY PAID', value: 'PARTIALLY_PAID' }
          ]
        }
      ],
      filtersApplied: false,
      isRequestOngoing: true,
      filters: {
        order_number: '',
        customer: '',
        status: { name: 'DUE', value: 'UNPAID' },
        from_date: '',
        to_date: ''
      },
      role: this.$store.state.user.currentUser.role,
      sundryDebtorsList: [],
      filterBy: false,
      getPendingOrders: [],
      getCompletedOrders: [],
    }
  },
  computed: {
    applyFilter() {
        if (this.filters.estimate_number || this.filters.customer || this.filters.from_date || this.filters.to_date) {
        return true;
      } return false;
    },
    showEmptyScreen () {
      return !this.totalPendingOrders && !this.totalCompletedOrders && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    ...mapGetters('customer', [
      'customers'
    ]),
    ...mapGetters('orders', [
      'selectedOrders',
      'totalPendingOrders',
      'totalCompletedOrders',
      'pendingOrders',
      'completedOrders',
      'selectAllField'
    ]),
    selectField: {
      get: function () {
        return this.selectedOrders
      },
      set: function (val) {
        this.selectOrder(val)
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
  created () {
    this.fetchCustomers()
  },
  destroyed () {
    if (this.selectAllField) {
      this.selectAllOrders()
    }
  },
  methods: {
    ...mapActions('orders', [
      'fetchOrders',
      'getRecord',
      'selectOrder',
      'resetSelectedOrders',
      'selectAllOrders',
      'deleteOrder',
      'deleteMultipleOrders',
      'sendEmail',
      'setSelectAllState'
    ]),
    ...mapActions('customer', [
      'fetchCustomers'
    ]),
    async sendOrder (id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('orders.confirm_send'),
        icon: '/assets/icon/paper-plane-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          const data = {
            id: id
          }
          let response = await this.sendEmail(data)
          this.refreshTable()
          if (response.data.success) {
            window.toastr['success'](this.$tc('orders.send_order_successfully'))
            return true
          }
          if (response.data.error === 'user_email_does_not_exist') {
            window.toastr['error'](this.$tc('orders.user_email_does_not_exist'))
            return false
          }
          window.toastr['error'](this.$tc('orders.something_went_wrong'))
        }
      })
    },
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        order_number: this.filters.order_number,
        customer_id: this.filters.customer === '' ? this.filters.customer : this.filters.customer.id,
        status: '',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchOrders(data)
      this.isRequestOngoing = false
      this.sundryDebtorsList = response.data.sundryDebtorsList

      this.setPendingOrder(data);
      this.setCompletedOrder(data);
    },
    setPendingOrder(response) {
      this.getPendingOrders = {
        data: response.data.pending_orders.data,
        pagination: {
          totalPages: response.data.pending_orders.last_page,
          currentPage: page,
          count: response.data.pending_count
        }
      }
      return this.getPendingOrders
    },
    setCompletedOrder(response) {
      this.getCompletedOrders = {
        data: response.data.completed_orders.data,
        pagination: {
          totalPages: response.data.completed_orders.last_page,
          currentPage: page,
          count: response.data.completed_count
        }
      }
      this.getCompletedOrders
    },
    setFilters () {
      if (this.timer) {
          clearTimeout(this.timer);
          this.timer = null;
      }
      this.timer = setTimeout(() => {
				this.filtersApplied = true
        this.resetSelectedOrders()
        this.refreshTable()
			}, 1000);
    },
    clearFilter () {
       this.filterBy=false;
        this.clearCustomerSearch();
      if (this.filters.customer) {
        this.$refs.customerSelect.$refs.baseSelect.removeElement(this.filters.customer)
      }
      this.filters = {
        order_number: '',
        customer: '',
        status: '',
        from_date: '',
        to_date: ''
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
    onSelectCustomer (customer) {
      this.filters.customer = customer
    },
    async removeOrder (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('orders.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteOrder(this.id)

          if (res.data.success) {
            window.toastr['success'](this.$tc('orders.deleted_message'))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('orders.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.error)
          return true
        }

        this.$refs.table.refresh()
        this.filtersApplied = false
        this.resetSelectedOrders()
      })
    },
    async removeMultipleOrders () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('orders.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteMultipleOrders()
          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('orders.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }
          if (res.data) {
            this.$refs.table.refresh()
            this.filtersApplied = false
            this.resetSelectedOrders()
            window.toastr['success'](this.$tc('orders.deleted_message', 2))
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    async clearCustomerSearch (removedOption, id) {
      this.filters.customer = ''
      this.refreshTable()
    },
    async clearStatusSearch (removedOption, id) {
      this.filters.status = ''
      this.refreshTable()
    }
  }
}
</script>
