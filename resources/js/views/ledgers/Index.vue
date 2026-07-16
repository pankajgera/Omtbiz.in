<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('ledgers.title', 2) }}</h3>
        </div>
      </div>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="/">
            {{ $t('general.home') }}
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="#">
            {{ $tc('ledgers.title', 2) }}
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
            @click="toggleDaySheet"
          >
            {{ $t('general.daysheet') }}
          </base-button>
        </div>
        <div class="col-xs-2 mr-2">
          <base-button
            :outline="true"
            color="theme"
            size="large"
            right-icon
            @click="toggleDayBook"
          >
            {{ $t('general.daybook') }}
          </base-button>
        </div>
        <div class="col-xs-2 mr-2">
          <base-button
            :outline="true"
            color="theme"
            size="large"
            right-icon
            @click="toggleInventoryStock"
          >
            {{ $t('general.summary_stock') }}
          </base-button>
        </div>
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalLedgers || filtersApplied"
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
        <!-- <router-link slot="item-title" class="col-xs-2" to="ledgers/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('ledgers.add_new_ledger') }}
          </base-button>
        </router-link> -->
      </div>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section ledger-filter-section">
        <div class="ledger-filter-actions">
          <button class="ledger-clear-filter" type="button" @click="clearFilter">
            <font-awesome-icon :icon="['fas', 'times']" aria-hidden="true" />
            {{ $t('general.clear_all') }}
          </button>
        </div>

        <div class="ledger-filter-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="ledger-filter-field min-w-0">
            <label class="form-label">{{ $t('general.from') }}</label>
            <base-date-picker
              v-model="filters.from_date"
              :calendar-button="true"
              calendar-button-icon="calendar"
            />
          </div>

          <div class="ledger-filter-field min-w-0">
            <label class="form-label">{{ $t('general.to') }}</label>
            <base-date-picker
              v-model="filters.to_date"
              :calendar-button="true"
              calendar-button-icon="calendar"
            />
          </div>

          <div class="ledger-filter-field min-w-0">
            <label class="form-label">{{ $tc('ledgers.account') }}</label>
            <base-input
              v-model="filters.account"
              type="text"
              name="account"
              autocomplete="off"
            />
          </div>

          <div class="ledger-filter-field min-w-0">
            <label class="form-label">{{ $tc('ledgers.balance') }}</label>
            <base-input
              v-model="filters.balance"
              type="text"
              name="balance"
              autocomplete="off"
            />
          </div>
        </div>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <satellite-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('ledgers.no_ledgers') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('ledgers.list_of_ledgers') }}</label>
      </div>
      <!-- <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('ledgers/create')"
        >
          {{ $t('ledgers.add_new_ledger') }}
        </base-button>
      </div> -->
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ ledgers.length }}</b> {{ $t('general.of') }} <b>{{ totalLedgers }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedLedgers.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleLedgers">
                <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                {{ $t('general.delete') }}
              </div>
            </v-dropdown-item>
          </v-dropdown>
        </transition>
      </div>
      <div class="table-component ledgers-table-component">
        <div class="table-component__table-wrapper">
          <base-loader v-if="isRequestOngoing" class="table-loader" />

          <table class="table-component__table table ledgers-table">
            <caption class="table-component__table__caption">
              {{ $tc('ledgers.ledger', 2) }}
            </caption>
            <thead class="table-component__table__head">
              <tr>
                <th class="ledgers-selection-column" rowspan="2" scope="col">
                  <label class="ledgers-select-all-label" for="select-all-ledgers">
                    <input
                      id="select-all-ledgers"
                      v-model="selectAllFieldStatus"
                      type="checkbox"
                      class="ledgers-checkbox"
                      @change="selectAllLedgers"
                    >
                    <span>{{ $t('general.select_all') }}</span>
                  </label>
                </th>
                <th rowspan="2" scope="col">{{ $t('ledgers.account') }}</th>
                <th class="ledgers-balance-heading" colspan="2" scope="colgroup">
                  {{ $t('ledgers.closing_balance') }}
                </th>
                <th class="ledgers-action-column" rowspan="2" scope="col">
                  <span class="sr-only">{{ $t('ledgers.action') }}</span>
                </th>
              </tr>
              <tr>
                <th scope="col">{{ $t('ledgers.debit') }}</th>
                <th scope="col">{{ $t('ledgers.credit') }}</th>
              </tr>
            </thead>
            <tbody class="table-component__table__body">
              <tr v-for="ledger in ledgers" :key="ledger.id">
                <td class="ledgers-selection-column">
                  <input
                    :id="`ledger-${ledger.id}`"
                    v-model="selectField"
                    :value="ledger.id"
                    :aria-label="`Select ${ledger.account}`"
                    type="checkbox"
                    class="ledgers-checkbox"
                  >
                </td>
                <td :data-label="$t('ledgers.account')">
                  <router-link :to="{ path: `ledgers/${ledger.id}/display` }">
                    {{ ledger.account }}
                  </router-link>
                </td>
                <td :data-label="$t('ledgers.debit')">
                  <template v-if="ledger.type === 'Dr'">
                    <span aria-hidden="true">&#8377;</span> {{ numberWithCommas(ledger.balance) }}
                  </template>
                </td>
                <td :data-label="$t('ledgers.credit')">
                  <template v-if="ledger.type === 'Cr'">
                    <span aria-hidden="true">&#8377;</span> {{ numberWithCommas(ledger.balance) }}
                  </template>
                </td>
                <td class="action-dropdown ledgers-action-column">
                  <v-dropdown :show-arrow="false">
                    <template #activator>
                      <button class="table-row-menu" type="button" :aria-label="$t('ledgers.action')">
                        <dot-icon />
                      </button>
                    </template>
                    <v-dropdown-item>
                      <router-link :to="{ path: `ledgers/${ledger.id}/display` }" class="dropdown-item">
                        <font-awesome-icon :icon="['fas', 'eye']" class="dropdown-item-icon" />
                        {{ $t('general.view') }}
                      </router-link>
                    </v-dropdown-item>
                    <v-dropdown-item>
                      <button class="dropdown-item" type="button" @click="removeLedgers(ledger.id)">
                        <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                        {{ $t('general.delete') }}
                      </button>
                    </v-dropdown-item>
                  </v-dropdown>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!ledgers.length && !isRequestOngoing" class="table-component__message">
          There are no matching rows
        </div>

        <table-pagination
          v-if="pagination.totalPages > 1 && !isRequestOngoing"
          :pagination="pagination"
          @pageChange="loadLedgers"
        />
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
import BaseLoader from '../../components/base/BaseLoader'
import TablePagination from '../../components/base/base-table/components/Pagination'
import moment from 'moment'
import GlobalMixin from '../../helpers/mixins.js';
export default {
  components: {
    DotIcon,
    SatelliteIcon,
    BaseButton,
    BaseLoader,
    TablePagination,
  },
  mixins:[GlobalMixin],
  data () {
    return {
      id: null,
      showFilters: true,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      ledgerRequestId: 0,
      pagination: {
        totalPages: 0,
        currentPage: 1,
        count: 0
      },
      filtersApplied: false,
      filters: {
        from_date: '',
        to_date: '',
        account: '',
        credit: '',
        debit: '',
        balance: ''
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('ledger', [
      'ledgers',
      'selectedLedgers',
      'totalLedgers',
      'selectAllField'
    ]),
    showEmptyScreen () {
      return !this.totalLedgers && !this.isRequestOngoing && !this.filtersApplied
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
  mounted() {
    this.loadLedgers()
  },
  unmounted() {
    if (this.timer) {
      clearTimeout(this.timer)
    }

    if (this.selectAllField) {
      this.selectAllLedgers()
    }
  },
  methods: {
    ...mapActions('ledger', [
      'fetchLedgers',
      'selectAllLedgers',
      'selectLedger',
      'deleteLedger',
      'deleteMultipleLedgers',
      'setSelectAllState'
    ]),
    refreshTable () {
      return this.loadLedgers(1)
    },
    async loadLedgers (page = 1) {
      const requestId = ++this.ledgerRequestId
      let data = {
        account: this.filters.account !== null ? this.filters.account : '',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        credit: this.filters.credit !== null ? this.filters.credit : '',
        debit: this.filters.debit !== null ? this.filters.debit : '',
        balance: this.filters.balance !== null ? this.filters.balance : '',
        orderByField: 'created_at',
        orderBy: 'desc',
        page
      }

      this.isRequestOngoing = true
      try {
        const response = await this.fetchLedgers(data)

        if (requestId !== this.ledgerRequestId) {
          return
        }

        const ledgersPage = response.data.ledgers
        this.pagination = {
          totalPages: ledgersPage.last_page,
          currentPage: ledgersPage.current_page || page,
          count: this.ledgers.length
        }
      } finally {
        if (requestId === this.ledgerRequestId) {
          this.isRequestOngoing = false
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
        from_date: '',
        to_date: '',
        account: '',
        credit: '',
        debit: '',
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
    toggleDayBook() {
      window.location = '/vouchers/daybook'
    },
    toggleDaySheet() {
      window.location = '/vouchers/daysheet'
    },
    toggleInventoryStock() {
      window.location = '/inventory/stock'
    },
    async removeLedgers (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('ledgers.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteLedger(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('ledgers.deleted_message', 1))
            this.refreshTable()
            return true
          }

          if (res.data.error === 'ledger_attached') {
            window.toastr['error'](this.$tc('ledgers.ledger_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleLedgers () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('ledgers.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleLedgers()
          if (res.data.success) {
            window.toastr['success'](this.$tc('ledgers.deleted_message', 2))
            this.refreshTable()
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
