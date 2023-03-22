<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ $t('general.inventory_stock') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ $t('general.inventory_stock') }}</a></li>
      </ol>
    </div>
    <div class="row" v-if="inventoryItems.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Inventory Item</h5>
          <table class="p-3 m-3">
            <tr>
              <th>ID</th>
              <th>Item</th>
              <th>Worker Name</th>
              <th>Quantity</th>
              <th>Sale Price</th>
              <th>Unit</th>
              <th>Item Used</th>
              <th>Date/Time</th>
            </tr>
            <tr v-for="(each, index) in inventoryItems" :key="index" style="border-top: 1px solid;">
              <td><a style="color:blue" target="_blank" :href="`/inventory/${each.id}/stock`">{{each.id}}</a></td>
              <td>{{each.name}}</td>
              <td>{{each.worker_name ? each.worker_name : '-'}}</td>
              <td>{{each.quantity}}</td>
              <td>₹ {{each.sale_price}}</td>
              <td>{{each.unit}}</td>
              <td>{{each.item_count}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
        </div>
      </div>
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
      title: 'Inventory Stock',
      inventoryItems: [],
    }
  },
  computed: {
  },
  created () {
    this.loadInventoryStock()
  },
  methods: {
    ...mapActions('inventory', [
      'fetchInventoryStock',
    ]),
    async loadInventoryStock () {
      let response = await this.fetchInventoryStock()
      this.inventoryItems = response.data.inventoryItems
    }
  }
}
</script>
