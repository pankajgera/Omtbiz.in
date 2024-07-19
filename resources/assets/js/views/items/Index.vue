<template>
  <div class="items main-content">
    <div class="page-header">
       <Header :title="$tc('items.bill_ty', 2)" :bread-crumb-links="breadCrumbLinks">
        <div  v-show="totalItemsToBe>0 || totalItems>0 || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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
        <div>
          <router-link slot="item-title" to="bill-ty/create">
            <base-button
              color="theme"
              icon="plus"
              size="large"
            >
              {{ $t('items.add_bill') }}
            </base-button>
          </router-link>
        </div>
       </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('items.party_name') }} </label>
            <base-select
              v-model="filters.name"
              ref="customerSelect"
              :options="sundryDebtorsList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              label="name"
              track-by="id"
              @select="onSelectCustomer"
              @deselect="clearCustomerSearch"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('items.bill_ty') }} </label>
            <base-input
              v-model="filters.bill_ty"
              type="text"
              name="bill_ty"
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
        <label class="col title">{{ $t('items.no_items') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('items.list_of_items') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('bill-ty/create')"
        >
          {{ $t('items.add_new_item') }}
        </base-button>
      </div>
    </div>

    <div v-if="!showEmptyScreen">
      <h4>Pending Bill-Ty</h4>
      <div class="table-container">
        <div class="table-actions mt-5">
          <transition name="fade">
            <v-dropdown v-if="selectedItemsToBe.length" :show-arrow="false">
              <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
                {{ $t('general.actions') }}
              </span>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeMultipleItemsToBe">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </transition>
        </div>

        <div class="custom-control custom-checkbox">
          <input
            id="select-all-to-be"
            v-model="selectAllFieldStatusToBe"
            type="checkbox"
            class="custom-control-input"
            @change="selectAllItemsToBe"
          >
          <label v-show="!isRequestOngoing" for="select-all-to-be" class="custom-control-label selectall">
            <span class="select-all-label">{{ $t('general.select_all') }} </span>
          </label>
        </div>
        <table-component
          ref="table"
          :data="fetchDataToBe"
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
                  v-model="selectFieldToBe"
                  :value="row.id"
                  type="checkbox"
                  class="custom-control-input"
                >
                <label :for="row.id" class="custom-control-label"/>
              </div>
            </template>
          </table-column>
          <table-column
            :label="$t('items.party_name')"
            show="party_name"
          >
            <template slot-scope="row">
              <router-link :to="{path: `bill-ty/${row.id}/edit`}">
                {{ row.party_name ? row.party_name : row.name }}
                </router-link>
            </template>
          </table-column>
          <table-column
            :label="$t('items.invoice_number')"
            show="invoice_number"
          >
            <template slot-scope="row">
                {{ row && row.dispatch ? row.dispatch.name : row.name }}
            </template>
          </table-column>
          <table-column
            :label="$t('items.bill_ty')"
            show="bill_ty"
          />
          <table-column
            :label="$t('items.added_on')"
            sort-as="created_at"
            show="formattedCreatedAt"
          />
          <table-column
            label="Image"
            show="images"
          >
            <template v-if="row.images" slot-scope="row">
              <expandable-image
                class="image"
                :src="row.images.original_image_path"
              ></expandable-image>
            </template>
          </table-column>
          <table-column
            :key="Math.random()"
            :sortable="false"
            :filterable="false"
            cell-class="action-dropdown"
          >
            <template slot-scope="row">
              <span> {{ $t('items.action') }} </span>
              <v-dropdown>
                <a slot="activator" href="#">
                  <dot-icon />
                </a>
                <v-dropdown-item>

                  <router-link :to="{path: `bill-ty/${row.id}/edit`}" class="dropdown-item">
                    <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                    {{ $t('general.edit') }}
                  </router-link>

                </v-dropdown-item>
                <v-dropdown-item>
                  <div class="dropdown-item" @click="removeItems(row.id)">
                    <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                    {{ $t('general.delete') }}
                  </div>
                </v-dropdown-item>
              </v-dropdown>
            </template>
          </table-column>
        </table-component>
      </div>

      <h4>Completed Bill-Ty</h4>
      <div class="table-container">
        <div class="table-actions mt-5">
          <transition name="fade">
            <v-dropdown v-if="selectedItems.length" :show-arrow="false">
              <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
                {{ $t('general.actions') }}
              </span>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeMultipleItems">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </transition>
        </div>

        <div class="custom-control custom-checkbox">
          <input
            id="select-all"
            v-model="selectAllFieldStatus"
            type="checkbox"
            class="custom-control-input"
            @change="selectAllItems"
          >
          <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
            <span class="select-all-label">{{ $t('general.select_all') }} </span>
          </label>
        </div>

        <table-component
          ref="tableToBe"
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
            :label="$t('items.party_name')"
            show="party_name"
          >
            <template slot-scope="row">
              <router-link :to="{path: `bill-ty/${row.id}/edit`}" class="dropdown-item">
                {{ row.party_name ? row.party_name : row.name }}
              </router-link>
            </template>
          </table-column>
          <table-column
            :label="$t('items.invoice_number')"
            show="invoice_number"
          >
            <template slot-scope="row">
                {{ row && row.dispatch ? row.dispatch.name : row.name }}
            </template>
          </table-column>
          <!-- <table-column
            :label="$t('items.bill_ty')"
            show="bill_ty"
          /> -->
          <table-column
            :label="$t('items.added_on')"
            sort-as="created_at"
            show="formattedCreatedAt"
          />
          <table-column
            label="Image"
            show="images"
          >
            <template v-if="row.images" slot-scope="row">
              <expandable-image
                class="image"
                :src="row.images.original_image_path"
              ></expandable-image>
            </template>
          </table-column>
          <table-column
            :key="Math.random()"
            :sortable="false"
            :filterable="false"
            cell-class="action-dropdown"
          >
            <template slot-scope="row">
              <span> {{ $t('items.action') }} </span>
              <v-dropdown>
                <a slot="activator" href="#">
                  <dot-icon />
                </a>
                <v-dropdown-item>
                  <router-link :to="{path: `bill-ty/${row.id}/edit`}" class="dropdown-item">
                    <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                    {{ $t('general.edit') }}
                  </router-link>
                </v-dropdown-item>
                <v-dropdown-item>
                  <div class="dropdown-item" @click="removeItems(row.id)">
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
      sundryDebtorsList: [],
      breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('items.bill_ty', 2)
        }
      ],
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        invoice_number: '',
        unit: '',
        bill_ty: ''
      },
      index: null,
    }
  },
  computed: {
       applyFilter() {
        if (this.filters.name || this.filters.bill_ty ||  this.filters.invoice_number ||  this.filters.unit) {
          return true;
        }
        return false;
    },
    ...mapGetters('item', [
      'items',
      'itemsToBe',
      'selectedItems',
      'selectedItemsToBe',
      'totalItems',
      'totalItemsToBe',
      'selectAllField',
      'selectAllFieldToBe',
    ]),
    ...mapGetters('currency', [
      'defaultCurrency'
    ]),
    showEmptyScreen () {
      return !this.isRequestOngoing && !this.filtersApplied && (!(this.totalItems + this.totalItemsToBe))
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedItems
      },
      set: function (val) {
        this.selectItem(val)
      }
    },
    selectFieldToBe: {
      get: function () {
        return this.selectedItemsToBe
      },
      set: function (val) {
        this.selectItemToBe(val)
      }
    },
    selectAllFieldStatus: {
      get: function () {
        return this.selectAllField
      },
      set: function (val) {
        this.setSelectAllState(val)
      }
    },
    selectAllFieldStatusToBe: {
      get: function () {
        return this.selectAllFieldToBe
      },
      set: function (val) {
        this.setSelectAllStateToBe(val)
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
      this.selectAllItems()
    }
    if (this.selectAllFieldToBe) {
      this.selectAllItemsToBe()
    }
  },
  methods: {
    ...mapActions('item', [
      'fetchItems',
      'selectAllItems',
      'selectAllItemsToBe',
      'selectItem',
      'selectItemToBe',
      'deleteItem',
      'deleteMultipleItems',
      'deleteMultipleItemsToBe',
      'setSelectAllState',
      'setSelectAllStateToBe'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name === '' ? this.filters.name : this.filters.name.id,
        unit: this.filters.unit !== null ? this.filters.unit.name : '',
        bill_ty: this.filters.bill_ty !== null ? this.filters.bill_ty : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchItems(data)
      this.isRequestOngoing = false
       this.sundryDebtorsList = response.data.sundryDebtorsList;
      return {
        data: response.data.items.data,
        pagination: {
          totalPages: response.data.items.last_page,
          currentPage: page
        }
      }
    },
    async fetchDataToBe ({ page, filter, sort }) {
      let data = {
        name: this.filters.name === '' ? this.filters.name : this.filters.name.id,
        unit: this.filters.unit !== null ? this.filters.unit.name : '',
        bill_ty: this.filters.bill_ty !== null ? this.filters.bill_ty : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchItems(data)
      this.isRequestOngoing = false
      this.sundryDebtorsList = response.data.sundryDebtorsList;
      return {
        data: response.data.itemsToBe.data,
        pagination: {
          totalPages: response.data.itemsToBe.last_page,
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
        bill_ty: ''
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
    async removeItems (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('items.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteItem(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('items.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'item_attached') {
            window.toastr['error'](this.$tc('items.item_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleItems () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('items.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleItems()
          if (res.data.success) {
            window.toastr['success'](this.$tc('items.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    async removeMultipleItemsToBe () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('items.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleItemsToBe()
          if (res.data.success) {
            window.toastr['success'](this.$tc('items.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    setIndex(index) {
      this.index = index
    },
    async clearCustomerSearch (removedOption, id) {
      this.filters.customer = ''
      this.refreshTable()
    },
    onSelectCustomer (customer) {
      this.filters.customer = customer
    },
  }
}
</script>
