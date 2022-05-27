<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('inventory.inventory', 2) }}</h3>
        </div>
      </div>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="dashboard">
            {{ $t('general.home') }}
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="#">
            {{ $tc('inventory.inventory', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalInventories || filtersApplied"
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
        <router-link slot="item-title" class="col-xs-2" to="inventory/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('inventory.new_inventory') }}
          </base-button>
        </router-link>
      </div>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('inventory.name') }} </label>
            <base-input
              v-model.trim="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('inventory.unit') }} </label>
            <base-input
              v-model="filters.unit"
              type="text"
              name="unit"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('inventory.price') }} </label>
            <base-input
              v-model="filters.price"
              type="text"
              name="price"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('inventory.quantity') }} </label>
            <base-input
              v-model="filters.quantity"
              type="text"
              name="quantity"
              autocomplete="off"
            />
          </div>
          <label class="clear-filter" @click="clearFilter"> {{ $t('general.clear_all') }}</label>
        </div>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <satellite-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('inventory.no_inventory') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('inventory.list_of_inventory') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('inventory/create')"
        >
          {{ $t('inventory.add_new_inventory') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ inventory.length }}</b> {{ $t('general.of') }} <b>{{ totalInventories }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedInventory.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleInventory">
                <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                {{ $t('general.delete') }}
              </div>
            </v-dropdown-item>
          </v-dropdown>
        </transition>
      </div>

      <div class="custom-control custom-checkbox">
        <input
          id="select-all-inventory"
          v-model="selectAllFieldStatus"
          type="checkbox"
          class="custom-control-input"
          @change="selectAllInventory"
        >
        <label v-show="!isRequestOngoing" for="select-all-inventory" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        :data="fetchData"
        :show-filter="false"
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
                v-model="selectField"
                :value="row.id"
                type="checkbox"
                class="custom-control-input"
              >
              <label :for="row.id" class="custom-control-label"/>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('inventory.name')"
          show="name"
        >
          <template slot-scope="row">
            <router-link :to="{path: `inventory/${row.id}/edit`}" class="dropdown-item">
               {{ row.name }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('inventory.unit')"
          show="unit"
        />
        <table-column
          :label="$t('inventory.price')"
          show="price"
        />
        <table-column
          :label="$t('inventory.quantity')"
          show="quantity"
        />
        <table-column
          :key="Math.random()"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
        <template slot-scope="row">
          <span> {{ $t('inventory.action') }} </span>
          <v-dropdown>
            <a slot="activator" href="#">
              <dot-icon />
            </a>
            <v-dropdown-item>

              <router-link :to="{path: `inventory/${row.id}/edit`}" class="dropdown-item">
                <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                {{ $t('general.edit') }}
              </router-link>

            </v-dropdown-item>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeInventory(row.id)">
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
<style>
body > .expandable-image.expanded {
  width: 100% !important;
}
.expandable-image{
  width: 100px;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'
import DotIcon from '../../components/icon/DotIcon'
import SatelliteIcon from '../../components/icon/SatelliteIcon'
import BaseButton from '../../../js/components/base/BaseButton'

export default {
  components: {
    DotIcon,
    SatelliteIcon,
    BaseButton,
  },
  data () {
    return {
      id: null,
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        unit: '',
        price: '',
        quantity: ''
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('inventory', [
      'inventory',
      'inventories',
      'selectedInventory',
      'totalInventories',
      'selectAllField'
    ]),
    showEmptyScreen () {
      return !this.totalInventories && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedInventory
      },
      set: function (val) {
        this.selectInventory(val)
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
  destroyed () {
    if (this.selectAllField) {
      this.selectAllInventory()
    }
  },
  methods: {
    ...mapActions('inventory', [
      'fetchAllInventory',
      'selectAllInventory',
      'selectInventory',
      'deleteInventory',
      'deleteMultipleInventory',
      'setSelectAllState'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        price: this.filters.price !== null ? this.filters.price : '',
        quantity: this.filters.quantity !== null ? this.filters.quantity : '',
        unit: this.filters.unit !== null ? this.filters.unit : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchAllInventory(data)
      this.isRequestOngoing = false

      return {
        data: response.data.inventories.data,
        pagination: {
          totalPages: response.data.inventories.last_page,
          currentPage: page
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
        this.refreshTable()
			}, 1000);
    },
    clearFilter () {
      this.filters = {
        name: '',
        unit: '',
        price: '',
        quantity: ''
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
    async removeInventory (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('inventory.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteInventory(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('inventory.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleInventory () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('inventory.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleInventory()
          if (res.data.success) {
            window.toastr['success'](this.$tc('inventory.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    setIndex(index) {
      this.index = index
    }
  }
}
</script>
