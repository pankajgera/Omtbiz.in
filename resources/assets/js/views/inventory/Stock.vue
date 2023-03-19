<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ $t('general.stock') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ $t('general.stock') }}</a></li>
      </ol>
    </div>
    <div class="row" v-if="inventoryItems.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Inventory Item</h5>
          <table class="p-3 m-3">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Worker Name</th>
              <th>Quantity</th>
              <th>Cost Price</th>
              <th>Sale Price</th>
              <th>Unit</th>
              <th>Date/Time</th>
            </tr>
            <tr v-for="(each, index) in inventoryItems" :key="index">
              <td>{{each.id}}</td>
              <td>{{each.name}}</td>
              <td>{{each.worker_name}}</td>
              <td>{{each.quantity}}</td>
              <td>₹ {{each.price}}</td>
              <td>₹ {{each.sale_price}}</td>
              <td>{{each.unit}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="row" v-if="invoiceItems.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Invoice Item</h5>
          <table class="p-3 m-3">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Quantity</th>
              <th>Cost Price</th>
              <th>Sale Price</th>
              <th>Total</th>
              <th>Date/Time</th>
            </tr>
            <tr v-for="(each, index) in invoiceItems" :key="index">
              <td>{{each.id}}</td>
              <td>{{each.name}}</td>
              <td>{{each.quantity}}</td>
              <td>₹ {{each.price}}</td>
              <td>₹ {{each.sale_price}}</td>
              <td>₹ {{each.total}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
div.hide-select-header div.multiselect__tags input.multiselect__input{
  display: none;
}
</style>
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
      title: 'Summary Stock',
      unitOptions: ['pc', 'sqm'],
      inventoryItems: [],
      invoiceItems: [],
    }
  },
  computed: {
  },
  created () {
    this.loadSummaryStock()
  },
  methods: {
    ...mapActions('inventory', [
      'fetchSummaryStock',
    ]),
    async loadSummaryStock () {
      let response = await this.fetchSummaryStock()
      this.inventoryItems = response.data.inventoryItems.data
      this.invoiceItems = response.data.invoiceItems.data
    }
  }
}
</script>
