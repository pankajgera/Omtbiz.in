<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('daybook.title', 2) }}</h3>
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
            {{ $tc('daybook.title', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2 mr-2">
          <base-button
            :outline="true"
            color="theme"
            size="large"
            right-icon
            @click="toggleLedgers"
          >
            {{ $t('general.to_display') }}
          </base-button>
        </div>
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalDaybook || filtersApplied"
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
          <div class="col-sm-2">
            <label class="form-label"> {{ $tc('daybook.date') }} </label>
            <base-input
              v-model="filters.date"
              type="text"
              name="date"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-2">
            <label class="form-label"> {{ $tc('daybook.account') }} </label>
            <base-input
              v-model="filters.account"
              type="text"
              name="account"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-1">
            <label class="form-label"> {{ $tc('daybook.voucher_type') }} </label>
            <base-input
              v-model="filters.voucher_type"
              type="text"
              name="voucher_type"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-1">
            <label class="form-label"> {{ $tc('daybook.voucher_count') }} </label>
            <base-input
              v-model="filters.voucher_count"
              type="text"
              name="voucher_count"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('daybook.voucher_debit') }} </label>
            <base-input
              v-model="filters.voucher_debit"
              type="text"
              name="voucher_debit"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-2">
            <label class="form-label"> {{ $tc('daybook.voucher_credit') }} </label>
            <base-input
              v-model="filters.voucher_credit"
              type="text"
              name="voucher_credit"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('daybook.voucher_balance') }} </label>
            <base-input
              v-model="filters.voucher_balance"
              type="text"
              name="voucher_balance"
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
        <label class="col title">{{ $t('daybook.no_daybook') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('daybook.list_of_daybook') }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <transition name="fade">
          <v-dropdown v-if="selectedLedgers.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
          </v-dropdown>
        </transition>
      </div>

      <div class="custom-control custom-checkbox">
        <input
          id="select-all"
          v-model="selectAllFieldStatus"
          type="checkbox"
          class="custom-control-input"
          @change="selectAllLedgers"
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
          :label="$t('daybook.date')"
          show="date"
        >
          <template slot-scope="row">
             {{ getFormattedDate(row.date) }}
          </template>
        </table-column>
        <table-column
          :label="$t('daybook.account')"
          show="account"
        >
          <template slot-scope="row">
            <router-link :to="{path: `${row.id}/book`}" class="dropdown-item">
               {{ row.account }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('daybook.voucher_type')"
          show="voucher_type"
        >
          <template slot-scope="row">
             {{ row.voucher_type }}
          </template>
        </table-column>
        <table-column
          :label="$t('daybook.voucher_count')"
          show="voucher_count"
        >
          <template slot-scope="row">
             {{ row.voucher_count }}
          </template>
        </table-column>
        <table-column
          :label="$t('daybook.voucher_debit')"
          show="voucher_debit"
        >
          <template slot-scope="row">
            ₹ {{ row.voucher_debit }}
          </template>
        </table-column>
        <table-column
          :label="$t('daybook.voucher_credit')"
          show="voucher_credit"
        >
          <template slot-scope="row">
            ₹ {{ row.voucher_credit }}
          </template>
        </table-column>

        <table-column
          :label="$t('daybook.voucher_balance')"
          show="voucher_balance"
        >
          <template slot-scope="row">
            ₹ {{ row.voucher_balance }}
          </template>
        </table-column>
        <table-column
          :key="Math.random()"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
        <template slot-scope="row">
          <span> {{ $t('daybook.action') }} </span>
          <v-dropdown>
            <a slot="activator" href="#">
              <dot-icon />
            </a>
            <v-dropdown-item>
              <router-link :to="{path: `${row.id}/book`}" class="dropdown-item">
                <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                {{ $t('general.view') }}
              </router-link>
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
import moment from 'moment'

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
        date: '',
        account: '',
        debit: '',
        credit: '',
        balance: ''
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('ledger', [
      'selectedLedgers',
      'selectAllField'
    ]),
    ...mapGetters('voucher', [
      'daybook',
      'totalDaybook',
    ]),
    showEmptyScreen () {
      return !this.totalDaybook && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedLedgers
      },
      set: function (val) {
        this.selectLedger(val)
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
      this.selectAllLedgers()
    }
  },
  methods: {
    ...mapActions('ledger', [
      'selectAllLedgers',
      'selectLedger',
      'deleteLedger',
      'setSelectAllState'
    ]),
    ...mapActions('voucher', [
      'fetchDaybook',
    ]),
    getFormattedDate(date) {
      return moment(date).format('DD-MM-YYYY')
    },
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        date: this.filters.date !== null ? this.filters.date : '',
        account: this.filters.account !== null ? this.filters.account : '',
        debit: this.filters.debit !== null ? this.filters.debit : '',
        credit: this.filters.credit !== null ? this.filters.credit : '',
        balance: this.filters.balance !== null ? this.filters.balance : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchDaybook(data)
      this.isRequestOngoing = false
  console.log(response.data)
      return {
        data: response.data.daybook,
      }
    },
    setFilters () {
      this.filtersApplied = true
      this.refreshTable()
    },
    clearFilter () {
      this.filters = {
        date: '',
        account: '',
        debit: '',
        credit: '',
        balance: '',
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
    toggleLedgers() {
      window.location = '/ledgers'
    },
    setIndex(index) {
      this.index = index
    }
  }
}
</script>
