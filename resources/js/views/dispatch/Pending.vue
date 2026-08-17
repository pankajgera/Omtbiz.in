<template>
  <div class="items main-content">
    <div class="page-header">
      <Header :title="$t('dispatch.to_be_dispatched_page_title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <div class="dispatch-toolbar row">
      <div class="col-md-5">
        <base-input
          v-model.trim="filters.search"
          type="text"
          name="search"
          autocomplete="off"
          :placeholder="$t('dispatch.search_placeholder')"
        />
      </div>
      <div class="col-md-4">
        <base-select
          v-model="filters.name"
          ref="toolbarCustomerSelect"
          :options="sundryDebtorsList"
          :searchable="true"
          :show-labels="false"
          :allow-empty="true"
          :placeholder="$tc('items.party_name')"
          label="name"
          track-by="id"
          @select="onSelectCustomer"
          @deselect="clearCustomerSearch"
        />
      </div>
      <div class="col-md-3">
        <base-select
          v-model="filters.dateFilter"
          :options="dateFilterOptions"
          :searchable="false"
          :show-labels="false"
          :allow-empty="false"
          label="label"
          track-by="value"
        />
      </div>
    </div>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <satellite-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('dispatch.no_dispatch') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('dispatch.list_of_dispatch') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('/dispatch/create')"
        >
          {{ $t('dispatch.add_new_dispatch') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <h4>{{ $t('dispatch.to_be_dispatched') }}</h4>
        <!-- `.table-actions` is space-between, which spreads the title away
             from the buttons but leaves the buttons themselves flush against
             each other. Group them so they get a gap of their own. -->
        <div class="dispatch-table-actions">
          <base-button
            v-show="toBeDispatchedData"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            class="dispatch-print-button"
            right-icon
            @click="printToBeDispatch"
          >
            Print
          </base-button>
          <transition name="fade">
            <base-button
              v-if="selectedToBeDispatch && selectedToBeDispatch.length"
              color="theme"
              size="large"
              :icon="['fas', 'truck']"
              class="dispatch-selected-button"
              @click="openDispatchForm(selectedToBeDispatch)"
            >
              {{ $t('dispatch.dispatch_selected', { count: selectedToBeDispatch.length }) }}
            </base-button>
          </transition>
          <transition name="fade">
            <v-dropdown v-if="selectedToBeDispatch && selectedToBeDispatch.length" :show-arrow="false">
              <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
                {{ $t('general.actions') }}
              </span>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeMultipleDispatch">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </transition>
        </div>
      </div>

      <!-- The dispatch form itself, opened in place by the row button or the
           bulk one above. Keyed on the id list so switching selection while
           it's open re-seeds the fields instead of keeping stale ones. -->
      <transition name="fade">
        <dispatch-inline-form
          v-if="dispatchFormIds.length"
          :key="dispatchFormIds.join(',')"
          :ids="dispatchFormIds"
          class="mb-4"
          @cancel="closeDispatchForm"
          @saved="onDispatched"
        />
      </transition>

      <div class="custom-control custom-checkbox">
        <input
          id="select-to-be-all"
          v-model="selectAllToBeFieldStatus"
          type="checkbox"
          class="custom-control-input"
          @change="selectAllToBeDispatch"
        />
        <label v-show="!isRequestOngoing" for="select-to-be-all" class="custom-control-label selectall">
        </label>
      </div>

      <table-component
        ref="toBeTableDispatch"
        :data="toBeDispatchedData"
        :show-filter="false"
        table-class="table"
      >
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="no-click"
        >
          <template #default="row">
            <div class="custom-control custom-checkbox">
              <input
                :id="'to_be_' + row.id"
                v-model="selectToBeField"
                :value="row.id"
                type="checkbox"
                class="custom-control-input"
              >
              <label :for="'to_be_' + row.id" class="custom-control-label"/>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.invoice_id')"
        >
          <template #default="row">
            <router-link :to="{path: `/dispatch/${row.id}/edit`}" >
              <span> {{ $t('dispatch.invoice_id') }} </span>
              <span v-if="row.invoices.length">{{ row.invoices.map(i => ' ' + i.invoice_number).toString() }}</span>
            </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template #default="row">
            <span> {{ $t('dispatch.name') }} </span>
            <span v-if="row.master">{{ row.master.name }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template #default="row">
            <span> {{ $t('dispatch.action') }} </span>
            <div class="dispatch-row-actions">
              <!-- Dispatching is what this list is for, so it gets its own
                   control rather than sitting a click deep in the menu. Icon
                   only - a labelled button is too heavy to repeat down every
                   row next to the overflow menu. -->
              <button
                type="button"
                class="dispatch-row-button"
                :title="$t('general.dispatch')"
                :aria-label="$t('general.dispatch')"
                @click="openDispatchForm([row.id])"
              >
                <font-awesome-icon :icon="['fas', 'truck']" />
              </button>
              <v-dropdown>
              <span slot="activator" href="#">
                <dot-icon />
              </span>
              <v-dropdown-item>
                <router-link :to="{path: `/dispatch/${row.id}/edit`}" class="dropdown-item">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                  {{ $t('general.edit') }}
                </router-link>
              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeDispatch(row.id)">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
              </v-dropdown>
            </div>
          </template>
        </table-column>
      </table-component>

      <!--  print table -->
      <table-component
        id="to_print_to_be_dispatch"
        ref="toBeTableDispatch1"
        :data="toBeDispatchedData"
        :show-filter="false"
        table-class="table"
      >
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="no-click"
        >
          <template #default="row">
            <div class="custom-control custom-checkbox">
              <input
                :id="'to_be_' + row.id"
                v-model="selectToBeField"
                :value="row.id"
                type="checkbox"
                class="custom-control-input"
              >
              <label :for="'to_be_' + row.id" class="custom-control-label"/>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.invoice_id')"
        >
          <template #default="row">
            <router-link :to="{path: `/dispatch/${row.id}/edit`}" >
              <span> {{ $t('dispatch.invoice_id') }} </span>
              <span v-if="row.invoices.length ">{{ row.invoices.filter((v,i,a)=>a.findIndex(v2=>(v2.account_master_id===v.account_master_id))===i).map(i => ' ' + i.invoice_number + '*' + row.invoices.filter(j=>j.account_master_id===i.account_master_id).length).toString() }}</span>
            </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template #default="row">
            <span> {{ $t('dispatch.name') }} </span>
            <span v-if="row.invoices.length && row.invoices[0].master">{{ row.invoices.map(i => ' ' + i.master.name).toString() }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
      </table-component>
    </div>
  </div>
</template>
<style>
#to_print_to_be_dispatch {
  display:none;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'
import DotIcon from '../../components/icon/DotIcon'
import SatelliteIcon from '../../components/icon/SatelliteIcon'
import BaseButton from '../../../js/components/base/BaseButton'
import DispatchInlineForm from './DispatchInlineForm'

export default {
  components: {
    DotIcon,
    SatelliteIcon,
    BaseButton,
    DispatchInlineForm,
  },
  data () {
    return {
      // Dispatch ids the inline form is currently open for. Empty = closed.
      dispatchFormIds: [],
      sundryDebtorsList: [],
      totalPending: 0,
      dateFilterOptions: [
        { value: 'today', label: this.$t('dispatch.date_filter_today') },
        { value: 'yesterday', label: this.$t('dispatch.date_filter_yesterday') },
        { value: 'next_day', label: this.$t('dispatch.date_filter_next_day') },
        { value: 'this_week', label: this.$t('dispatch.date_filter_this_week') },
        { value: 'this_month', label: this.$t('dispatch.date_filter_this_month') },
        { value: 'all', label: this.$t('dispatch.date_filter_all') },
      ],
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home'),
        },
        {
          url: '/dispatch/dashboard',
          title: this.$t('dispatch.dashboard_title'),
        },
        {
          url: '#',
          title: this.$t('dispatch.to_be_dispatched_page_title'),
        },
      ],
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        search: '',
        name: '',
        dateFilter: { value: 'today', label: this.$t('dispatch.date_filter_today') },
      },
    }
  },
  computed: {
    applyFilter () {
      return !!this.filters.name
    },
    ...mapGetters('dispatch', [
      'selectedToBeDispatch',
      'selectAllToBeField'
    ]),
    showEmptyScreen () {
      return !this.totalPending && !this.isRequestOngoing && !this.filtersApplied
    },
    selectToBeField: {
      get: function () {
        return this.selectedToBeDispatch
      },
      set: function (val) {
        this.selectToBeDispatch(val)
      }
    },
    selectAllToBeFieldStatus: {
      get: function () {
        return this.selectAllToBeField
      },
      set: function (val) {
        this.setSelectAllToBeState(val)
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
    if (this.selectAllToBeField) {
      this.selectAllToBeDispatch()
    }
  },
  methods: {
    ...mapActions('dispatch', [
      'fetchPendingDispatch',
      'selectAllToBeDispatch',
      'selectToBeDispatch',
      'deleteDispatch',
      'deleteMultipleDispatch',
      'setSelectAllToBeState',
    ]),
    refreshTable () {
      this.$refs.toBeTableDispatch1.refresh()
      this.$refs.toBeTableDispatch.refresh()
    },
    async toBeDispatchedData ({ page, filter, sort }) {
      let data = {
        search: this.filters.search || '',
        name: this.filters.name === '' ? this.filters.name : this.filters.name.id,
        date_filter: this.filters.dateFilter ? this.filters.dateFilter.value : 'today',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchPendingDispatch(data)
      this.isRequestOngoing = false
      this.sundryDebtorsList = response.data.sundryDebtorsList
      this.totalPending = response.data.dispatch_inprogress.total
      return {
        data: response.data.dispatch_inprogress.data,
        pagination: {
          totalPages: response.data.dispatch_inprogress.last_page,
          currentPage: response.data.dispatch_inprogress.current_page,
        }
      }
    },
    onSelectCustomer (customer) {
      this.filters.name = customer
    },
    async clearCustomerSearch (removedOption, id) {
      this.filters.name = ''
      this.refreshTable()
    },
    setFilters () {
      if (this.timer) {
        clearTimeout(this.timer)
        this.timer = null
      }
      this.timer = setTimeout(() => {
        this.filtersApplied = true
        this.refreshTable()
      }, 1000)
    },
    async removeDispatch (id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('dispatch.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteDispatch(id)
          if (res.data.dispatch) {
            window.toastr['success'](this.$tc('dispatch.deleted_message', 1))
            this.refreshTable()
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleDispatch () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('dispatch.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleDispatch(this.selectedToBeDispatch)
          if (res.data.dispatch) {
            window.toastr['success'](this.$tc('dispatch.deleted_message', 2))
            this.refreshTable()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    // Opens the dispatch form in place, below the toolbar. `ids` is one row for
    // the row button, or every ticked row for the bulk button. This replaces
    // the old `singleDispatch`, which either dispatched blind with whatever
    // person/transport happened to be on the row, or - when either was missing
    // - popped `/dispatch/:id/edit` open in a new tab.
    openDispatchForm (ids) {
      this.dispatchFormIds = [...ids]
      this.$nextTick(() => {
        let panel = this.$el.querySelector('.dispatch-inline-form')
        if (panel) {
          panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
        }
      })
    },
    closeDispatchForm () {
      this.dispatchFormIds = []
    },
    onDispatched () {
      this.closeDispatchForm()
      // Dispatched rows leave this list, so anything ticked is now stale.
      if (this.selectAllToBeField) {
        this.setSelectAllToBeState(false)
      }
      this.selectToBeDispatch([])
      this.refreshTable()
    },
    printToBeDispatch () {
      printJS({
        printable: 'to_print_to_be_dispatch',
        type: 'html',
        ignoreElements: ['no-print-check', 'no-print-option'],
        scanStyles: true,
        targetStyles: ['*'],
        style: '.hide-print {display: none !important;}.table-component__table th, .table-component__table td {padding: 0.75em 1.25em;vertical-align: top;text-align: left;}.table thead th {border: 0;position: relative;top: 25px; botton: 20px;}.table-component__table { min-width: 100%; border-collapse: separate; table-layout: auto; margin-bottom: 0;border-spacing: 0 15px;} .table .table-component__table__body tr {border-radius: 10px;transition: all ease-in-out 0.2s;} .table .table-component__table__body tr:first-child td {border-top: 0;} .table .table-component__table__body td {padding: 0px 15px !important;height: 20px !important;} .table-component td > span:first-child {background: #EBF1FA;color: #55547A;display: none;font-size: 10px;font-weight: bold;padding: 5px;left: 0;position: absolute;text-transform: uppercase;top: 0;}'
      })
    }
  }
}
</script>
<style scoped>
.table-actions {
  height: 55px;
}
.dispatch-toolbar {
  margin: 25px 0 20px;
  align-items: center;
}
/* Override the global a:hover/:focus rule (resources/assets/sass/base.scss)
   which adds a border + elliptical border-radius to every link on hover -
   it distorts any link wrapping a button, table row, or dropdown item.
   Only border/border-radius are reset (those are the ones marked !important
   in the global rule and cause the visible glitch) - padding is deliberately
   left alone so elements with their own real padding (e.g. .dropdown-item,
   set in tables.scss) aren't stripped of it, which misaligned the Edit
   dropdown item (a router-link) against the Delete item (a plain div). */
a:hover,
a:focus {
  border: none !important;
  border-radius: 0 !important;
}
</style>
