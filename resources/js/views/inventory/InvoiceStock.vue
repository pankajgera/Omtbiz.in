<template>
  <div class="main-content item-create inventory-stock-page">
    <div class="page-header">
      <div class="d-flex flex-row flex-wrap justify-content-between align-items-center">
        <div class="d-flex flex-column">
          <h3 class="page-title">{{ $t('general.invoice_stock') }}</h3>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
            <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
            <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory/stock">{{ $tc('general.inventory_stock',2) }}</router-link></li>
            <li class="breadcrumb-item"><a href="#"> {{ $t('general.invoice_stock') }}</a></li>
          </ol>
        </div>
        <div class="inventory-stock-actions">
          <base-button
            v-show="inventoryItems.length || invoiceItems.length"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            right-icon
            @click="print"
          >
            Print
          </base-button>
        </div>
      </div>
    </div>
    <div class="row" v-if="inventoryItems.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card inventory-stock-card">
          <div class="inventory-stock-card__header">
            <h5 class="inventory-stock-card__title">Inwards</h5>
            <span class="inventory-stock-card__count">{{ inventoryItems.length }}</span>
          </div>
          <div class="inventory-stock-table-wrapper">
            <table class="inventory-stock-table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Worker Name</th>
                  <th class="is-numeric">Quantity</th>
                  <th class="is-numeric">Sale Price</th>
                  <th>Unit</th>
                  <th>Date/Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(each, index) in inventoryItems" :key="index">
                  <td>{{each.name}}</td>
                  <td>{{each.worker_name ? each.worker_name : '-'}}</td>
                  <td class="is-numeric" :class="{ 'is-negative': Number(each.quantity) < 0 }">{{each.quantity}}</td>
                  <td class="is-numeric">₹ {{each.sale_price}}</td>
                  <td>{{each.unit}}</td>
                  <td class="is-nowrap">{{each.date_time}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card inventory-stock-card">
          <div class="inventory-stock-card__header">
            <h5 class="inventory-stock-card__title">Outwards</h5>
            <span v-if="invoiceItems.length" class="inventory-stock-card__count">{{ invoiceItems.length }}</span>
          </div>
          <div v-if="invoiceItems.length" class="inventory-stock-table-wrapper">
            <table class="inventory-stock-table">
              <thead>
                <tr>
                  <th>Invoice Number</th>
                  <th>Party Name</th>
                  <th class="is-numeric">Quantity</th>
                  <th class="is-numeric">Sale Price</th>
                  <th class="is-numeric">Total</th>
                  <th>Date/Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(each, index) in invoiceItems" :key="index">
                  <td><a class="inventory-stock-link" :href="`/invoices/${each.invoice_id}/edit`">{{each.invoice_number}}</a></td>
                  <td>{{each.party_name}}</td>
                  <td class="is-numeric" :class="{ 'is-negative': Number(each.quantity) < 0 }">{{each.quantity}}</td>
                  <td class="is-numeric">₹ {{each.sale_price}}</td>
                  <td class="is-numeric">₹ {{each.total}}</td>
                  <td class="is-nowrap">{{each.date_time}}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="inventory-stock-empty">No invoice item found with this inventory</p>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
@media print {
   .site-header, .sidebar-left, .page-header, .print {
      display: none;
   }
   .layout-default .main-content, .layout-icon-sidebar .main-content {
    padding: 10px 10px 10px 10px;
   }
}
</style>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'

export default {
  mixins: [validationMixin],
  data () {
    return {
      isLoading: false,
      title: 'Invoice Stock',
      invoiceItems: [],
      inventoryItems: [],
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
    print() {
      window.print();
    },
    async loadInvoiceStock () {
      let response = await this.fetchInvoiceStock(this.$route.params.id)
      this.invoiceItems = response.data.invoiceItems
      this.inventoryItems = response.data.inventoryItems
    }
  }
}
</script>
