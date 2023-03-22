<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ $t('general.invoice_stock') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory/stock">{{ $tc('general.inventory_stock',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ $t('general.invoice_stock') }}</a></li>
      </ol>
    </div>
    <div class="row" v-if="invoiceItems.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Invoice Item</h5>
          <table class="p-3 m-3">
            <tr>
              <th>ID</th>
              <th>Item Name</th>
              <th>Party Name</th>
              <th>Quantity</th>
              <th>Sale Price</th>
              <th>Total</th>
              <th>Date/Time</th>
            </tr>
            <tr v-for="(each, index) in invoiceItems" :key="index" style="border-top: 1px solid;">
              <td><a style="color:blue" target="_blank" :href="`/invoices/${each.id}/edit`">{{each.id}}</a></td>
              <td>{{each.name}}</td>
              <td>{{each.party_name}}</td>
              <td>{{each.quantity}}</td>
              <td>₹ {{each.sale_price}}</td>
              <td>₹ {{each.total}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div v-else>
      No invoice item found with this inventory
    </div>
  </div>
</template>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'

export default {
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Invoice Stock',
      invoiceItems: [],
    }
  },
  computed: {
  },
  created () {
    this.loadInvoiceStock()
  },
  methods: {
    ...mapActions('inventory', [
      'fetchInvoiceStock',
    ]),
    async loadInvoiceStock () {
      let response = await this.fetchInvoiceStock(this.$route.params.id)
      this.invoiceItems = response.data.invoiceItems
    }
  }
}
</script>
