<template>
  <div v-if="receipt" class="main-content receipt-view-page">
    <div class="page-header">
      <h3 class="page-title"> {{ receipt.receipt_number }}</h3>
      <div class="page-actions row">
        <v-dropdown :close-on-select="false" align="left" class="filter-container">
          <span slot="activator" href="#">
            <base-button color="theme">
              <font-awesome-icon icon="ellipsis-h" />
            </base-button>
          </span>
          <v-dropdown-item>
            <router-link :to="{path: `/receipts/${$route.params.id}/edit`}" class="dropdown-item">
              <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
              {{ $t('general.edit') }}
            </router-link>
            <div class="dropdown-item" @click="removeReceipt($route.params.id)">
              <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
              {{ $t('general.delete') }}
            </div>
          </v-dropdown-item>
        </v-dropdown>
      </div>
    </div>
    <div class="receipt-sidebar">
      <base-loader v-if="isSearching" />
      <div v-else class="side-header">
        <base-input
          v-model="searchData.searchText"
          :placeholder="$t('general.search')"
          input-class="inv-search"
          icon="search"
          type="text"
          align-icon="right"
          @input="onSearched()"
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
                id="filter_receipt_date"
                v-model="searchData.receiptByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="receipt_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_receipt_date">{{ $t('reports.receipts.receipt_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_due_date"
                v-model="searchData.receiptByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="expiry_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_due_date">{{ $t('receipts.due_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_receipt_number"
                v-model="searchData.receiptByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="receipt_number"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_receipt_number">{{ $t('receipts.receipt_number') }}</label>
            </div>
          </v-dropdown>
          <base-button class="inv-button inv-filter-sorting-btn" color="default" size="medium" @click="sortData">
            <font-awesome-icon v-if="getReceiptBy" icon="sort-amount-up" />
            <font-awesome-icon v-else icon="sort-amount-down" />
          </base-button>
        </div>
      </div>
      <!-- <div class="side-content">
        <router-link
          v-for="(receipt,index) in receipts"
          :to="`/receipts/${receipt.id}/view`"
          :key="index"
          class="side-receipt"
        >
          <div class="left">
            <div class="inv-name">{{ receipt.user.name }}</div>
            <div class="inv-number">{{ receipt.receipt_number }}</div>
          </div>
          <div class="right">
            <div class="inv-amount" v-html="$utils.formatMoney(receipt.total, receipt.user.currency)" />
            <div class="inv-date">{{ receipt.formattedReceiptDate }}</div>
          </div>
        </router-link>
        <p v-if="!receipts.length" class="no-result">
          {{ $t('receipts.no_matching_receipts') }}
        </p>
      </div> -->
    </div>
    <div class="receipt-view-page-container">
      <iframe :src="`${shareableLink}`" class="frame-style"/>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import _ from 'lodash';
export default {
  data () {
    return {
      id: null,
      count: null,
      receipts: [],
      receipt: null,
      currency: null,
      searchData: {
        receiptBy: null,
        receiptByField: null,
        searchText: null
      },
      isSendingEmail: false,
      isRequestOnGoing: false,
      isSearching: false
    }
  },
  computed: {
    getReceiptBy () {
      if (this.searchData.receiptBy === 'asc' || this.searchData.receiptBy == null) {
        return true
      }
      return false
    },

    shareableLink () {
      return `/receipts/pdf/${this.receipt.id}`
    }
  },
  watch: {
    $route (to, from) {
      this.loadReceipt()
    }
  },
  created () {
    this.loadReceipts()
    this.loadReceipt()
    this.onSearched = _.debounce(this.onSearched, 500)
  },
  methods: {
    ...mapActions('receipt', [
      'fetchReceipts',
      'getRecord',
      'searchReceipt',
      'sendEmail',
      'deleteReceipt',
      'selectReceipt',
      'fetchViewReceipt'
    ]),
    async loadReceipts () {
      let response = await this.fetchReceipts()
      if (response.data) {
        this.receipts = response.data.receipts.data
      }
    },
    async loadReceipt () {
      let response = await this.fetchViewReceipt(this.$route.params.id)
      if (response.data) {
        this.receipt = response.data.receipt
      }
    },
    async onSearched () {
      let data = ''
      if (this.searchData.searchText !== '' && this.searchData.searchText !== null && this.searchData.searchText !== undefined) {
        data += `search=${this.searchData.searchText}&`
      }

      if (this.searchData.receiptBy !== null && this.searchData.receiptBy !== undefined) {
        data += `receiptBy=${this.searchData.receiptBy}&`
      }

      if (this.searchData.receiptByField !== null && this.searchData.receiptByField !== undefined) {
        data += `receiptByField=${this.searchData.receiptByField}`
      }
      this.isSearching = true
      let response = await this.searchReceipt(data)
      this.isSearching = false
      if (response.data) {
        this.receipts = response.data.receipts.data
      }
    },
    sortData () {
      if (this.searchData.receiptBy === 'asc') {
        this.searchData.receiptBy = 'desc'
        this.onSearched()
        return true
      }
      this.searchData.receiptBy = 'asc'
      this.onSearched()
      return true
    },
    async onSendReceipt (id) {
      window.swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('receipts.confirm_send_receipt'),
        icon: '/assets/icon/paper-plane-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          this.isSendingEmail = true
          let response = await this.sendEmail({id: this.receipt.id})
          this.isSendingEmail = false
          if (response.data.success) {
            window.toastr['success'](this.$tc('receipts.send_receipt_successfully'))
            return true
          }
          if (response.data.error === 'user_email_does_not_exist') {
            window.toastr['error'](this.$tc('receipts.user_email_does_not_exist'))
            return true
          }
          window.toastr['error'](this.$tc('receipts.something_went_wrong'))
        }
      })
    },
    async removeReceipt (id) {
      window.swal({
        title: 'Deleted',
        text: 'you will not be able to recover this receipt!',
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let request = await this.deleteReceipt(id)
          if (request.data.success) {
            window.toastr['success'](this.$tc('receipts.deleted_message', 1))
            this.$router.push('/receipts')
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    }
  }
}
</script>
