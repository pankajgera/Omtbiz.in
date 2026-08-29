<template>
  <div class="main-content item-create inventory-stock-page">
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
        <div class="inventory-stock-actions">
          <base-button
            v-show="inventoryItems.length"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            right-icon
            @click="print"
          >
            Print
          </base-button>
          <base-button
            v-show="inventoryItems.length || filtersApplied"
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
              v-model.trim="filters.name"
              type="text"
              name="name"
              autocomplete="off"
              @input="setFilter('name')"
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
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card inventory-stock-card">
          <div class="inventory-stock-card__header">
            <h5 class="inventory-stock-card__title">Inventory Item</h5>
            <span v-if="inventoryItems.length" class="inventory-stock-card__count">
              {{ inventoryItems.length }}
            </span>
          </div>
          <div v-if="inventoryItems.length > 0" class="inventory-stock-table-wrapper">
            <table class="inventory-stock-table" ref="inventoryStock">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Worker Name</th>
                  <th class="is-numeric">Quantity</th>
                  <th class="is-numeric">Sale Price</th>
                  <th>Unit</th>
                  <th class="is-numeric">Item Used</th>
                  <th>Date/Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(each, index) in inventoryItems" :key="index">
                  <td><a class="inventory-stock-link" :href="`/inventory/${each.id}/stock`">{{each.name}}</a></td>
                  <td>{{each.worker_name ? each.worker_name : '-'}}</td>
                  <td class="is-numeric" :class="{ 'is-negative': Number(each.quantity) < 0 }">{{each.quantity}}</td>
                  <td class="is-numeric">₹ {{each.sale_price}}</td>
                  <td>{{each.unit}}</td>
                  <td class="is-numeric">{{each.item_count}}</td>
                  <td class="is-nowrap">{{each.date_time}}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="inventory-stock-empty">No Record Found..</p>
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
  mixins: [validationMixin],
  data () {
    return {
      isLoading: false,
      title: 'Inventory Stock',
      inventoryItems: [],
      showFilters: false,
      filtersApplied: false,
      filters: {
        worker_name: '',
        name: '',
        from_date: '',
        to_date: ''
      },
    }
  },
  computed: {
    applyFilter() {
        if (this.filters.worker_name || this.filters.from_date ||  this.filters.to_date ||  this.filters.name) {
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
        name: '',
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
        name: this.filters.name ? this.filters.name : '',
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
