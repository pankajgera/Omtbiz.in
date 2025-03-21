<template>
  <div class="invoice-index-page invoices main-content">
    <div class="page-header">
       <Header :title="$t('invoices.title')" :bread-crumb-links="breadCrumbLinks">
          <div v-show="totalInvoices || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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
            <router-link slot="item-title" to="/invoices/create">
                <base-button size="large" icon="plus" color="theme">
                  {{ $t('invoices.new_invoice') }}
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
          <div class="filter-invoice">
            <label>{{ $t('invoices.invoice_number') }}</label>
            <base-input
              v-model="filters.invoice_number"
              icon="hashtag"/>
          </div>
        </div>
        <label class="clear-filter" @click="clearFilter">{{ $t('general.clear_all') }}</label>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('invoices.no_invoices') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('invoices.list_of_invoices') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('invoices/create')"
        >
          {{ $t('invoices.new_invoice') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ invoices.length }}</b> {{ $t('general.of') }} <b>{{ filtered_count }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedInvoices.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleInvoices">
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
          @change="selectAllInvoices"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
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
                :ref="'myCheckbox'"
                v-model="selectField"
                :value="row.id"
                type="checkbox"
                class="custom-control-input"
              >
              <label :for="row.id" class="custom-control-label"> </label>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('invoices.number')"
          show="invoice_number"
        >
          <template slot-scope="row">
            <router-link :to="{path: `invoices/${row.id}/edit?nondis=${row.paid_status !== 'DISPATCHED'}`}">
               {{ row.invoice_number }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('invoices.date')"
          sort-as="invoice_date"
          show="formattedInvoiceDate"
        />
        <table-column
          :label="$t('invoices.customer')"
          width="20%"
          sort-as="name"
        >
          <template slot-scope="row">
            {{ row.master.name }}
          </template>
        </table-column>
        <table-column
          :label="$t('invoices.total')"
          sort-as="total"
        >
          <template slot-scope="row">
            <span>{{ $t('invoices.amount') }}</span>
            ₹ {{ numberWithCommas((row.total).toFixed(2)) }}
          </template>
        </table-column>
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
        >
          <template slot-scope="row">
            <span>{{ $t('invoices.action') }}</span>
            <v-dropdown>
              <span slot="activator" href="#">
                <dot-icon />
              </span>
              <v-dropdown-item>
                <router-link :to="{path: `invoices/${row.id}/edit`}" class="dropdown-item" v-if="role === 'admin' || role === 'accountant'">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
                  {{ $t('general.edit') }}
                </router-link>
                <router-link :to="{path: `invoices/${row.id}/view`}" class="dropdown-item">
                  <font-awesome-icon icon="eye" class="dropdown-item-icon" />
                  {{ $t('invoices.view') }}
                </router-link>
              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="sendReports(row.id)" v-if="role === 'admin' || role === 'accountant'">
                  <font-awesome-icon icon="file-pdf" class="vue-icon icon-left svg-inline--fa fa-download fa-w-16 mr-2" />
                  {{ $t('invoices.whatsapp') }}
                </div>
              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeInvoice(row.id)" v-if="role === 'admin' || role === 'accountant'">
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
import GlobalMixin from '../../helpers/mixins.js';
export default {
  components: {
    'moon-walker-icon': MoonWalkerIcon
  },
  mixins:[GlobalMixin],
  data () {
    return {
      showFilters: false,
      //currency: null,
    breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('invoices.invoice', 2)
        }
      ],
      filtersApplied: false,
      isRequestOngoing: true,
      filtered_count: 0,
      filters: {
        invoice_number: '',
        customer: '',
        from_date: '',
        to_date: ''
      },
      role: this.$store.state.user.currentUser.role,
      sundryDebtorsList: [],
      filterBy: false,
    }
  },
  mounted() {
    this.refreshTable();
  },
  computed: {
     applyFilter() {
        if (this.filters.invoice_number || this.filters.customer || this.filters.from_date || this.filters.to_date) {
        return true;
      } return false;
    },
    showEmptyScreen () {
      return !this.totalInvoices && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    ...mapGetters('customer', [
      'customers'
    ]),
    ...mapGetters('invoice', [
      'selectedInvoices',
      'totalInvoices',
      'invoices',
      'selectAllField'
    ]),
    selectField: {
      get: function () {
        return this.selectedInvoices
      },
      set: function (val) {
        this.selectInvoice(val)
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
      this.selectAllInvoices()
    }
  },
  methods: {
    ...mapActions('invoice', [
      'fetchInvoices',
      'fetchInvoice',
      'getRecord',
      'selectInvoice',
      'resetSelectedInvoices',
      'selectAllInvoices',
      'deleteInvoice',
      'deleteMultipleInvoices',
      'setSelectAllState'
    ]),
    ...mapActions('customer', [
      'fetchCustomers',
      'sendReportOnWhatsApp'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },

    async fetchData ({ page, filter, sort }) {
      let data = {
        invoice_number: this.filters.invoice_number,
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
      let response = await this.fetchInvoices(data)
      this.isRequestOngoing = false
      this.sundryDebtorsList = response.data.sundryDebtorsList
      this.filtered_count = response.data.invoices.total
      //this.currency = response.data.currency
      // Use querySelectorAll to select all checkboxes
      const allCheckboxes = this.$el.querySelectorAll('input[type="checkbox"]');

      // Loop through checkboxes and uncheck them
      allCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
      });
      this.selectField = [];

      return {
        data: response.data.invoices.data,
        pagination: {
          totalPages: response.data.invoices.last_page,
          currentPage: page,
          count: response.data.invoices.count
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
        this.resetSelectedInvoices()
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
        invoice_number: '',
        customer: '',
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
    async removeInvoice (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('invoices.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteInvoice(this.id)

          if (res.data.success) {
            window.toastr['success'](this.$tc('invoices.deleted_message'))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('invoices.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.error)
          return true
        }

        this.$refs.table.refresh()
        this.filtersApplied = false
        this.resetSelectedInvoices()
      })
    },
    async removeMultipleInvoices () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('invoices.confirm_delete_2',this.selectField.length > 1 ?  2 :1, { 'count': this.selectField.length }),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let res = await this.deleteMultipleInvoices()
          if (res.data.error === 'payment_attached') {
            window.toastr['error'](this.$t('invoices.payment_attached_message'), this.$t('general.action_failed'))
            return true
          }
          if (res.data) {
            this.$refs.table.refresh()
            this.filtersApplied = false
            this.resetSelectedInvoices()
            window.toastr['success'](this.$tc('invoices.deleted_message', 2))
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
    async sendReports(invoice_id) {
      let response = await this.fetchInvoice(invoice_id);
      let invoice = response.data.invoice
      if (!invoice) {
        window.toastr['error']("Invoice not found for id - " + invoice_id)
        return
      }
      this.isLoading = true
      this.siteURL = `/invoices/pdf/${invoice.unique_hash}`
      if (!response.data.sundryDebtorsList.length) {
        window.toastr['error']("Sundry Debtors list is empty for invoice id - " + invoice_id)
        return
      }
      let mobile = response.data.sundryDebtorsList.find(i => i.id === invoice.account_master_id).mobile_number;
      if (!mobile) {
        window.toastr['error']("Sorry, didn't find mobile number for selected ledger.")
        return
      }
      let fileName = 'Invoice - ' + moment(invoice.invoice_date).format('DD/MM/YYYY');
      this.sendReportOnWhatsApp({ fileName: fileName, number: mobile, filePath: window.location.origin + this.siteURL})
      .then((val) => {
        setTimeout(() => {
          this.isLoading = false
          window.location.reload()
        }, 2000)
      })
    }
  }
}
</script>
