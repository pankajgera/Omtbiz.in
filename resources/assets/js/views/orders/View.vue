<template>
  <div v-if="order" class="main-content order-view-page">
    <div class="page-header">
      <h3 class="page-title"> {{ order.order_number }}</h3>
      <div class="page-actions row">
        <v-dropdown :close-on-select="false" align="left" class="filter-container">
          <a slot="activator" href="#">
            <base-button color="theme">
              <font-awesome-icon icon="ellipsis-h" />
            </base-button>
          </a>
          <v-dropdown-item>
            <router-link :to="{path: `/orders/${$route.params.id}/edit`}" class="dropdown-item">
              <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
              {{ $t('general.edit') }}
            </router-link>
            <div class="dropdown-item" @click="removeOrder($route.params.id)">
              <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
              {{ $t('general.delete') }}
            </div>
          </v-dropdown-item>
        </v-dropdown>
      </div>
    </div>
    <div class="order-sidebar">
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
            <a slot="activator" href="#">
              <base-button class="inv-button inv-filter-fields-btn" color="default" size="medium">
                <font-awesome-icon icon="filter" />
              </base-button>
            </a>

            <div class="filter-items">
              <input
                id="filter_order_date"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="order_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_order_date">{{ $t('reports.orders.order_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_due_date"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="expiry_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_due_date">{{ $t('orders.due_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_order_number"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="order_number"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_order_number">{{ $t('orders.order_number') }}</label>
            </div>
          </v-dropdown>
          <base-button class="inv-button inv-filter-sorting-btn" color="default" size="medium" @click="sortData">
            <font-awesome-icon v-if="getOrderBy" icon="sort-amount-up" />
            <font-awesome-icon v-else icon="sort-amount-down" />
          </base-button>
        </div>
      </div>
      <!-- <div class="side-content">
        <router-link
          v-for="(order,index) in orders"
          :to="`/orders/${order.id}/view`"
          :key="index"
          class="side-order"
        >
          <div class="left">
            <div class="inv-name">{{ order.user.name }}</div>
            <div class="inv-number">{{ order.order_number }}</div>
          </div>
          <div class="right">
            <div class="inv-amount" v-html="$utils.formatMoney(order.total, order.user.currency)" />
            <div class="inv-date">{{ order.formattedOrderDate }}</div>
          </div>
        </router-link>
        <p v-if="!orders.length" class="no-result">
          {{ $t('orders.no_matching_orders') }}
        </p>
      </div> -->
    </div>
    <div class="order-view-page-container">
      <iframe :src="`${shareableLink}`" class="frame-style"/>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
const _ = require('lodash')

export default {
  data () {
    return {
      id: null,
      count: null,
      orders: [],
      order: null,
      currency: null,
      searchData: {
        orderBy: null,
        orderByField: null,
        searchText: null
      },
      isSendingEmail: false,
      isRequestOnGoing: false,
      isSearching: false
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
      return `/orders/pdf/${this.order.unique_hash}`
    }
  },
  watch: {
    $route (to, from) {
      this.loadOrder()
    }
  },
  created () {
    this.loadOrders()
    this.loadOrder()
    this.onSearched = _.debounce(this.onSearched, 500)
  },
  methods: {
    ...mapActions('orders', [
      'fetchOrders',
      'getRecord',
      'searchOrder',
      //'sendEmail',
      'deleteOrder',
      'selectOrder',
      'fetchViewOrder'
    ]),
    async loadOrders () {
      let response = await this.fetchOrders()
      if (response.data) {
        this.orders = response.data.orders.data
      }
    },
    async loadOrder () {
      let response = await this.fetchViewOrder(this.$route.params.id)

      if (response.data) {
        this.order = response.data.order
      }
    },
    async onSearched () {
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
      let response = await this.searchOrder(data)
      this.isSearching = false
      if (response.data) {
        this.orders = response.data.orders.data
      }
    },
    sortData () {
      if (this.searchData.orderBy === 'asc') {
        this.searchData.orderBy = 'desc'
        this.onSearched()
        return true
      }
      this.searchData.orderBy = 'asc'
      this.onSearched()
      return true
    },
    // async onSendOrder (id) {
    //   window.swal({
    //     title: this.$t('general.are_you_sure'),
    //     text: this.$t('orders.confirm_send_order'),
    //     icon: '/assets/icon/paper-plane-solid.svg',
    //     buttons: true,
    //     dangerMode: true
    //   }).then(async (value) => {
    //     if (value) {
    //       this.isSendingEmail = true
    //       let response = await this.sendEmail({id: this.order.id})
    //       this.isSendingEmail = false
    //       if (response.data.success) {
    //         window.toastr['success'](this.$tc('orders.send_order_successfully'))
    //         return true
    //       }
    //       if (response.data.error === 'user_email_does_not_exist') {
    //         window.toastr['error'](this.$tc('orders.user_email_does_not_exist'))
    //         return true
    //       }
    //       window.toastr['error'](this.$tc('orders.something_went_wrong'))
    //     }
    //   })
    // },
    async removeOrder (id) {
      window.swal({
        title: 'Deleted',
        text: 'you will not be able to recover this order!',
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let request = await this.deleteOrder(id)
          if (request.data.success) {
            window.toastr['success'](this.$tc('orders.deleted_message', 1))
            this.$router.push('/orders')
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    }
  }
}
</script>
