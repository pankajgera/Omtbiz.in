<template>
  <div v-if="invoice" class="main-content invoice-view-page">
    <div class="page-header">
      <h3 class="page-title"> {{ invoice.invoice_number }}</h3>
      <div class="page-actions row">
        <router-link v-if="invoice.status === 'SENT'" :to="`/payments/${$route.params.id}/create`">
          <base-button
            color="theme"
          >
            {{ $t('payments.record_payment') }}
          </base-button>
        </router-link>
        <v-dropdown :close-on-select="false" align="left" class="filter-container">
          <span slot="activator" href="#">
            <base-button color="theme">
              <font-awesome-icon icon="ellipsis-h" />
            </base-button>
          </span>
          <v-dropdown-item>
            <router-link :to="{path: `/invoices/${$route.params.id}/edit`}" class="dropdown-item">
              <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
              {{ $t('general.edit') }}
            </router-link>
            <div class="dropdown-item" @click="removeInvoice($route.params.id)">
              <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
              {{ $t('general.delete') }}
            </div>
          </v-dropdown-item>
        </v-dropdown>
      </div>
    </div>
    <div class="invoice-sidebar">
      <div class="side-header">
        <base-input
          v-model="searchData.searchText"
          :placeholder="$t('general.search')"
          input-class="inv-search"
          icon="search"
          type="text"
          align-icon="right"
          @input="onSearch"
        />
        <div
          class="btn-group ml-3"
          role="group"
          aria-label="First group"
        >
          <v-dropdown :close-on-select="false" align="left" class="filter-container">
            <span slot="activator" href="#">
              <base-button class="inv-button inv-filter-fields-btn" color="default" size="medium">
                <font-awesome-icon icon="filter" />
              </base-button>
            </span>

            <div class="filter-items">
              <input
                id="filter_invoice_date"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="invoice_date"
                @change="onSearch"
              >
              <label class="inv-label" for="filter_invoice_date">{{ $t('invoices.invoice_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_invoice_number"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="invoice_number"
                @change="onSearch"
              >
              <label class="inv-label" for="filter_invoice_number">{{ $t('invoices.invoice_number') }}</label>
            </div>
          </v-dropdown>
          <base-button class="inv-button inv-filter-sorting-btn" color="default" size="medium" @click="sortData">
            <font-awesome-icon v-if="getOrderBy" icon="sort-amount-up" />
            <font-awesome-icon v-else icon="sort-amount-down" />
          </base-button>
        </div>
      </div>
      <base-loader v-if="isSearching" />
      <div v-else class="side-content">
        <router-link
          v-for="(invoice,index) in invoices"
          :to="`/invoices/${invoice.id}/view`"
          :key="index"
          class="side-invoice"
        >
          <div class="left">
            <div class="inv-name">{{ invoice.user.name }}</div>
            <div class="inv-number">{{ invoice.invoice_number }}</div>
          </div>
          <div class="right">
            <div class="inv-amount" v-html="'₹ ' + (invoice.due_amount)" />
            <div class="inv-date">{{ invoice.formattedInvoiceDate }}</div>
          </div>
        </router-link>
        <p v-if="!invoices.length" class="no-result">
          {{ $t('invoices.no_matching_invoices') }}
        </p>
      </div>
    </div>
    <div class="invoice-view-page-container" >
      <iframe :src="`${shareableLink}`" class="frame-style"/>
    </div>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
const _ = require('lodash')
export default {
  data () {
    return {
      id: null,
      count: null,
      invoices: [],
      invoice: null,
      currency: null,
      searchData: {
        orderBy: null,
        orderByField: null,
        searchText: null
      },
      isRequestOnGoing: false,
      isSearching: false,
      isSendingEmail: false,
      isMarkingAsSent: false
    }
  },
  computed: {
    getOrderBy () {
      if (this.searchData.orderBy === 'asc' || this.searchData.orderBy == null) {
        return true
      }
      return false
    },
    shareableLink () {
      return `/invoices/pdf/${this.invoice.unique_hash}`
    }
  },
  watch: {
    $route (to, from) {
      this.loadInvoice()
    }
  },
  created () {
    this.loadInvoices()
    this.loadInvoice()
    this.onSearch = _.debounce(this.onSearch, 500)
  },
  methods: {
    ...mapActions('invoice', [
      'fetchInvoices',
      'getRecord',
      'searchInvoice',
      'sendEmail',
      'deleteInvoice',
      'selectInvoice',
      'fetchViewInvoice'
    ]),
    async loadInvoices () {
      let response = await this.fetchInvoices()
      if (response.data) {
        this.invoices = response.data.invoices.data
      }
    },
    async loadInvoice () {
      let response = await this.fetchViewInvoice(this.$route.params.id)

      if (response.data) {
        this.invoice = response.data.invoice
      }
    },
    async onSearch () {
      let data = ''
      if (this.searchData.searchText !== '' && this.searchData.searchText !== null && this.searchData.searchText !== undefined) {
        data += `search=${this.searchData.searchText}&`
      }

      if (this.searchData.orderBy !== null && this.searchData.orderBy !== undefined) {
        data += `orderBy=${this.searchData.orderBy}&`
      }

      if (this.searchData.orderByField !== null && this.searchData.orderByField !== undefined) {
        data += `orderByField=${this.searchData.orderByField}`
      }
      this.isSearching = true
      let response = await this.searchInvoice(data)
      this.isSearching = false
      if (response.data) {
        this.invoices = response.data.invoices.data
      }
    },
    sortData () {
      if (this.searchData.orderBy === 'asc') {
        this.searchData.orderBy = 'desc'
        this.onSearch()
        return true
      }
      this.searchData.orderBy = 'asc'
      this.onSearch()
      return true
    },
    async removeInvoice (id) {
      this.selectInvoice([parseInt(id)])
      this.id = id
      window.swal({
        title: 'Deleted',
        text: 'you will not be able to recover this invoice!',
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let request = await this.deleteInvoice(this.id)
          if (request.data.success) {
            window.toastr['success'](this.$tc('invoices.deleted_message', 1))
            this.$router.push('/invoices/create')
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    }
  }
}
</script>
