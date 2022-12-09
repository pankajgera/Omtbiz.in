<template>
  <div class="items main-content">
    <div class="page-header">
      <Header :title="$tc('estimates.estimate', 2)" :bread-crumb-links="breadCrumbLinks">
        <div v-show="totalEstimatesDraft || totalEstimatesSent || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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
          <router-link slot="item-title" to="/estimates/create">
            <base-button
              size="large"
              icon="plus"
              color="theme">
              {{ $t('estimates.new_estimate') }}
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
            <base-customer-select
              ref="customerSelect"
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
          <div class="filter-estimate">
            <label>{{ $t('estimates.estimate_number') }}</label>
            <base-input
              v-model="filters.estimate_number"
              icon="hashtag"/>
          </div>
        </div>
        <label class="clear-filter" @click="clearFilter">{{ $t('general.clear_all') }}</label>
      </div>
    </transition>

    <div v-cloak v-show="(showEmptyScreenDraft && showEmptyScreenSent)" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('estimates.no_estimates') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('estimates.list_of_estimates') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('estimates/create')"
        >
          {{ $t('estimates.new_estimate') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreenDraft" class="table-container">
      <div class="table-actions mt-5">
        <h4>Pending Estimates</h4>
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ estimatesDraft.length }}</b> {{ $t('general.of') }} <b>{{ totalEstimatesDraft }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedEstimates.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleEstimates">
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
          @change="selectAllEstimates"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchDataDraft"
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
          :label="$t('estimates.number')"
          show="estimate_number"
        >
          <template slot-scope="row">
            <router-link :to="{path: `estimates/${row.id}/edit?d=true`}" class="dropdown-item">
               {{ row.estimate_number }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('estimates.date')"
          sort-as="estimate_date"
          show="formattedEstimateDate"
        />
        <table-column
          :label="$t('estimates.name')"
          width="20%"
          show="master.name"
        />
        <table-column
          :label="$t('estimates.count')"
          width="20%"
          show="items.length"
        />
        <table-column
          :label="$t('estimates.total')"
          sort-as="total"
        >
          <template slot-scope="row">
            <span>{{ $t('estimates.amount') }}</span>
             	₹ {{ (row.total).toFixed(2) }}
          </template>
        </table-column>
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('estimates.action') }}</span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
              <v-dropdown-item>
                <router-link :to="{path: `estimates/${row.id}/edit`}" class="dropdown-item" v-if="role === 'admin'">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
                  {{ $t('general.edit') }}
                </router-link>
                <router-link :to="{path: `estimates/${row.id}/view`}" class="dropdown-item">
                  <font-awesome-icon icon="eye" class="dropdown-item-icon" />
                  {{ $t('estimates.view') }}
                </router-link>
              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeEstimate(row.id)" v-if="role === 'admin'">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </template>
        </table-column>
      </table-component>
    </div>

    <!--COMPLETED/SENT START-->

    <div v-show="!showEmptyScreenSent" class="table-container">
      <div class="table-actions mt-5">
        <h4>Completed Estimates</h4>
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ estimatesSent.length }}</b> {{ $t('general.of') }} <b>{{ totalEstimatesSent }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedEstimates.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleEstimates">
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
          @change="selectAllEstimates"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchDataSent"
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
          :label="$t('estimates.number')"
          show="estimate_number"
        >
          <template slot-scope="row">
            <router-link :to="{path: `estimates/${row.id}/edit?d=true`}" class="dropdown-item">
               {{ row.estimate_number }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('estimates.date')"
          sort-as="estimate_date"
          show="formattedEstimateDate"
        />
        <table-column
          :label="$t('estimates.name')"
          width="20%"
          show="master.name"
        />
        <table-column
          :label="$t('estimates.count')"
          width="20%"
          show="items.length"
        />
        <table-column
          :label="$t('estimates.total')"
          sort-as="total"
        >
          <template slot-scope="row">
            <span>{{ $t('estimates.amount') }}</span>
             	₹ {{ (row.total).toFixed(2) }}
          </template>
        </table-column>
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('estimates.action') }}</span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
              <v-dropdown-item>
                <router-link :to="{path: `estimates/${row.id}/view`}" class="dropdown-item">
                  <font-awesome-icon icon="eye" class="dropdown-item-icon" />
                  {{ $t('estimates.view') }}
                </router-link>
              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeEstimate(row.id)" v-if="role === 'admin'">
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
          title:this.$tc('estimates.estimate', 2)
        }
      ],
      //currency: null,
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
        estimate_number: '',
        customer: '',
        status: { name: 'DUE', value: 'UNPAID' },
        from_date: '',
        to_date: ''
      },
      role: this.$store.state.user.currentUser.role
    }
  },

  computed: {
    showEmptyScreenDraft () {
      return !this.totalEstimatesDraft && !this.isRequestOngoing && !this.filtersApplied
    },
    showEmptyScreenSent () {
      return !this.totalEstimatesSent && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    ...mapGetters('customer', [
      'customers'
    ]),
    ...mapGetters('estimate', [
      'selectedEstimates',
      'estimatesDraft',
      'estimatesSent',
      'totalEstimatesDraft',
      'totalEstimatesSent',
      'selectAllField'
    ]),
    selectField: {
      get: function () {
        return this.selectedEstimates
      },
      set: function (val) {
        this.selectEstimate(val)
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
      this.selectAllEstimates()
    }
  },
  methods: {
    ...mapActions('estimate', [
      'fetchEstimates',
      'getRecord',
      'selectEstimate',
      'resetSelectedEstimates',
      'selectAllEstimates',
      'deleteEstimate',
      'deleteMultipleEstimates',
      'sendEmail',
      'setSelectAllState'
    ]),
    ...mapActions('customer', [
      'fetchCustomers'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchDataDraft ({ page, filter, sort }) {
      let data = {
        estimate_number: this.filters.estimate_number,
        customer_id: this.filters.customer === '' ? this.filters.customer : this.filters.customer.id,
        status: 'DRAFT',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchEstimates(data)
      this.isRequestOngoing = false

      return {
        data: response.data.estimates_draft.data,
        pagination: {
          totalPages: response.data.estimates_draft.last_page,
          currentPage: page,
          count: response.data.draft_count
        }
      }
    },
    async fetchDataSent ({ page, filter, sort }) {
      let data = {
        estimate_number: this.filters.estimate_number,
        customer_id: this.filters.customer === '' ? this.filters.customer : this.filters.customer.id,
        status: 'SENT',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchEstimates(data)
      this.isRequestOngoing = false

      return {
        data: response.data.estimates_sent.data,
        pagination: {
          totalPages: response.data.estimates_sent.last_page,
          currentPage: page,
          count: response.data.sent_count
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
        this.resetSelectedEstimates()
        this.refreshTable()
			}, 1000);
    },
    clearFilter () {
      if (this.filters.customer) {
        this.$refs.customerSelect.$refs.baseSelect.removeElement(this.filters.customer)
      }
      this.filters = {
        estimate_number: '',
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
    async removeEstimate (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('estimates.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteEstimate(this.id)

          if (res.data.success) {
            window.toastr['success'](this.$tc('estimates.deleted_message'))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('estimates.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.error)
          return true
        }

        this.$refs.table.refresh()
        this.filtersApplied = false
        this.resetSelectedEstimates()
      })
    },
    async removeMultipleEstimates () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('estimates.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteMultipleEstimates()
          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('estimates.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }
          if (res.data) {
            this.$refs.table.refresh()
            this.filtersApplied = false
            this.resetSelectedEstimates()
            window.toastr['success'](this.$tc('estimates.deleted_message', 2))
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
