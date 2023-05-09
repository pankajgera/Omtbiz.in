<template>
  <div class="items main-content">
    <div class="page-header">
      <Header :title="$tc('vouchers.voucher', 2)" :bread-crumb-links="breadCrumbLinks">
        <div v-show="totalVouchers || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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
        <router-link slot="item-title" to="vouchers/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('vouchers.add_voucher') }}
          </base-button>
        </router-link>
          </div>
      </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('vouchers.name') }} </label>
            <base-input
              v-model.trim="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('vouchers.groups') }} </label>
            <base-input
              v-model="filters.groups"
              type="text"
              name="groups"
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
        <label class="col title">{{ $t('vouchers.no_vouchers') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('vouchers.list_of_vouchers') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('vouchers/create')"
        >
          {{ $t('vouchers.add_new_voucher') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ vouchers.length }}</b> {{ $t('general.of') }} <b>{{ totalVouchers }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedVouchers.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleVouchers">
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
          @change="selectAllVouchers"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
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
          :label="$t('vouchers.name')"
          show="account"
        >
          <template slot-scope="row">
            <router-link :to="{path: `vouchers/${row.id}/edit`}" class="dropdown-item">
              {{ row.account }}
            </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('ledgers.debit')"
          show="debit"
        >
          <template slot-scope="row">
            ₹ {{ row.debit }}
          </template>
        </table-column>
        <table-column
          :label="$t('ledgers.credit')"
          show="credit"
        >
          <template slot-scope="row">
            ₹ {{ row.credit }}
          </template>
        </table-column>
        <table-column
          :label="$t('vouchers.groups')"
          show="groups"
        >
          <template slot-scope="row">
            {{ row.account_master.groups }}
          </template>
        </table-column>
        <table-column
          :key="Math.random()"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
        <template slot-scope="row">
          <span> {{ $t('vouchers.action') }} </span>
          <v-dropdown>
            <a slot="activator" href="#">
              <dot-icon />
            </a>
            <v-dropdown-item>

              <router-link :to="{path: `vouchers/${row.id}/edit`}" class="dropdown-item">
                <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                {{ $t('general.edit') }}
              </router-link>

            </v-dropdown-item>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeVouchers(row.id)">
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
      breadCrumbLinks:[
      {
        url:'dashboard',
        title:this.$t('general.home'),
      },
      {
        url:'#',
        title:this.$tc('vouchers.voucher')
      }
    ],
      filters: {
        name: '',
        groups: '',
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('voucher', [
      'vouchers',
      'selectedVouchers',
      'totalVouchers',
      'selectAllField'
    ]),
    showEmptyScreen () {
      return !this.totalVouchers && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedVouchers
      },
      set: function (val) {
        this.selectVoucher(val)
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
      this.selectAllVouchers()
    }
  },
  methods: {
    ...mapActions('voucher', [
      'fetchVouchers',
      'selectAllVouchers',
      'selectVoucher',
      'deleteVoucher',
      'deleteMultipleVouchers',
      'setSelectAllState'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        groups: this.filters.groups !== null ? this.filters.groups : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchVouchers(data)
      this.isRequestOngoing = false

      return {
        data: response.data.vouchers.data,
        pagination: {
          totalPages: response.data.vouchers.last_page,
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
        groups: '',
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
    async removeVouchers (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('vouchers.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteVoucher(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('vouchers.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'voucher_attached') {
            window.toastr['error'](this.$tc('vouchers.voucher_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleVouchers () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('vouchers.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleVouchers()
          if (res.data.success) {
            window.toastr['success'](this.$tc('vouchers.deleted_message', 2))
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
