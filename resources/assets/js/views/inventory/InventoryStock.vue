<template>
  <div class="main-content item-create">
    <div class="page-header">
      <div class="d-flex flex-row flex-wrap justify-content-between align-items-center">
        <div class="d-flex flex-column">
          <h3 class="page-title">{{ $t('general.inventory_stock') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ $t('general.inventory_stock') }}</a></li>
      </ol>
        </div>
        <div class="d-flex flex-wrap">
        <div v-show="inventoryItems.length || filtersApplied" class="mr-4 mb-3 mb-sm-0">
          <base-button
            :outline="true"
            :icon="filterIcon"
            color="theme"
            size="large"
            right-icon
            @click="toggleFilter"
          >
            {{ $t('general.filter') }}
          </base-button>
        </div>
      </div>
    
      </div>
     
      <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('items.worker_name') }} </label>
            <base-input
              v-model.trim="filters.worker_name"
              type="text"
              name="worker_name"
              autocomplete="off"
              @input="setFilter('worker_name')"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('items.item_name') }} </label>
            <base-input
              v-model.trim="filters.item_name"
              type="text"
              name="item_name"
              autocomplete="off"
              @input="setFilter('item_name')"
            />
          </div>
          <div class="col-sm-2">
           <label>{{ $t('general.from') }}</label>
              <base-date-picker
                v-model="filters.from_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
                @input="setFilter('from_date')"
              />

          </div>
          <div class="col-sm-3">
           <label>{{ $t('general.to') }}</label>
              <base-date-picker
                v-model="filters.to_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
                @input="setFilter('to_date')"
              />

          </div>
          <label class="clear-filter" @click="clearFilter"> {{ $t('general.clear_all') }}</label>
        </div>
      </div>
    </transition>
    </div>
    <div class="row">
      <div class="col-sm-12 mb-2 print">
      <base-button
            v-show="inventoryItems"
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
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Inventory Item</h5>
            <table class="p-3 m-3" ref="inventoryStock" v-if="inventoryItems.length > 0">
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
              <td><a style="color:blue" :href="`/inventory/${each.id}/stock`">{{each.id}}</a></td>
              <td>{{each.name}}</td>
              <td>{{each.worker_name ? each.worker_name : '-'}}</td>
              <td>{{each.quantity}}</td>
              <td>₹ {{each.sale_price}}</td>
              <td>{{each.unit}}</td>
              <td>{{each.item_count}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
          <table  class="p-3 m-3"  v-else>
            <tr><td colspan="7">
              No Record Found..
            </td></tr>
          </table>
        
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
export default {
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Inventory Stock',
      inventoryItems: [],
      showFilters: false,
      filtersApplied: false,
      filters: {
        worker_name: '',
        item_name: '',
        from_date: '',
        to_date: ''
      },
    }
  },
  computed: {
    applyFilter() {
        if (this.filters.worker_name || this.filters.from_date ||  this.filters.to_date ||  this.filters.item_name) {
          return true;
        }
        return false;
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
  },
  created () {
    this.loadInventoryStock()
  },
  methods: {
    setFilter () {
      this.loadInventoryStock()
    },
    clearFilter () {
    
       this.filtersApplied = false;
      this.showFilters=false;
      this.filters = {
        worker_name: '',
        item_name: '',
        from_date: '',
        to_date: ''
      }

      this.$nextTick(() => {
        this.filtersApplied = false
      })
      this.loadInventoryStock();
    },
    refreshTable () {
      this.$refs.inventoryStock.refresh()
    },
    toggleFilter () {
      if (this.showFilters && this.filtersApplied) {
        this.clearFilter()
        this.refreshTable()
      }

      this.showFilters = !this.showFilters
    },
    ...mapActions('inventory', [
      'fetchInventoryStock',
    ]),
     print() {
      window.print();
    },
    async loadInventoryStock (filter) {
      let data = {
        worker_name: this.filters.worker_name ? this.filters.worker_name : '',
        item_name: this.filters.item_name ? this.filters.item_name : '',
        from_date: this.filters.from_date ? moment(this.filters.from_date).format('DD/MM/YYYY') : '',
        to_date: this.filters.to_date ? moment(this.filters.to_date).format('DD/MM/YYYY') : '',
        filterBy: this.applyFilter,
      }
      let response = await this.fetchInventoryStock(data)
      this.inventoryItems = response.data.inventoryItems
    }
  }
}
</script>
