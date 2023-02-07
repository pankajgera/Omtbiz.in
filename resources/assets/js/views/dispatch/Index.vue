<template>
  <div class="items main-content">
    <div class="page-header">
      <Header :title="$tc('dispatch.dispatch', 2)" :bread-crumb-links="breadCrumbLinks">
        <div v-show="totalDispatch || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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

        <router-link slot="item-title" to="dispatch/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('dispatch.new_dispatch') }}
          </base-button>
        </router-link>
        </div>
       </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
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
          <div class="col-sm-2">
           <label>{{ $t('general.from') }}</label>
              <base-date-picker
                v-model="filters.from_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
              />

          </div>
          <div class="col-sm-3">
           <label>{{ $t('general.to') }}</label>
              <base-date-picker
                v-model="filters.to_date"
                :calendar-button="true"
                calendar-button-icon="calendar"
              />

          </div>
          <div class="col-sm-2">
            <label class="form-label"> {{ $tc('dispatch.status') }} </label>
            <base-input
              v-model.trim="filters.status"
              type="text"
              name="status"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-2">
            <label class="form-label"> {{ $tc('dispatch.transport') }} </label>
            <base-input
              v-model="filters.transport"
              type="text"
              name="transport"
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
          @click="$router.push('dispatch/create')"
        >
          {{ $t('dispatch.add_new_dispatch') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <!-- <p class="table-stats">{{ $t('general.showing') }}: <b>{{ dispatch.length }}</b> {{ $t('general.of') }} <b>{{ totalDispatch }}</b></p> -->
        <h4>To Be Dispatch</h4>
        <base-button
            v-show="toBeDispatchedData"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            :style="['position: absolute',' margin-right: 5%']"
            right-icon
            @click="printToBeDispatch"
          >
            Print
          </base-button>
        <transition name="fade">
          <v-dropdown v-if="selectedToBeDispatch && selectedToBeDispatch.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <!-- <v-dropdown-item>
              <div class="dropdown-item" @click="multipleDispatch('draft')">
                <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                {{ $t('general.dispatch') }}
              </div>
            </v-dropdown-item> -->
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleDispatch('draft')">
                <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                {{ $t('general.delete') }}
              </div>
            </v-dropdown-item>
          </v-dropdown>
        </transition>
      </div>

      <div class="custom-control custom-checkbox">
        <input
          id="select-to-be-all"
          v-model="selectAllToBeFieldStatus"
          type="checkbox"
          class="custom-control-input"
          @change="selectAllToBeDispatch"
        />
        <label v-show="!isRequestOngoing" for="select-to-be-all" class="custom-control-label selectall">
          <!-- <span class="select-to-be-all-label">{{ $t('general.select_all') }} </span> -->
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
          <template slot-scope="row">
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
        <template slot-scope="row">
            <router-link :to="{path: `dispatch/${row.id}/edit`}" >
              <span> {{ $t('dispatch.invoice_id') }} </span>
          <span v-if="row.invoices.length">{{ row.invoices.map(i => ' ' + i.invoice_number).toString() }}</span>
          </router-link>
        </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template slot-scope="row">
            <span> {{ $t('dispatch.name') }} </span>
            <span v-if="row.master">{{ row.master.name }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <!-- <table-column
          :label="$t('dispatch.status')"
          show="status"
        /> -->
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
        <template slot-scope="row">
          <span> {{ $t('dispatch.action') }} </span>
          <v-dropdown>
            <a slot="activator" href="#">
              <dot-icon />
            </a>
            <v-dropdown-item>
              <div @click="singleDispatch(row.id)" class="dropdown-item">
                <font-awesome-icon :icon="['fas', 'circle']" class="dropdown-item-icon" />
                {{ $t('general.dispatch') }}
              </div>
            </v-dropdown-item>
            <v-dropdown-item>
              <router-link :to="{path: `dispatch/${row.id}/edit`}" class="dropdown-item">
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
          <template slot-scope="row">
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
        <template slot-scope="row">
            <router-link :to="{path: `dispatch/${row.id}/edit`}" >
              <span> {{ $t('dispatch.invoice_id') }} </span>
          <span v-if="row.invoices.length ">{{ row.invoices.filter((v,i,a)=>a.findIndex(v2=>(v2.account_master_id===v.account_master_id))===i).map(i => ' ' + i.invoice_number + '*' + row.invoices.filter(j=>j.account_master_id===i.account_master_id).length).toString() }}</span>
          </router-link>
        </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template slot-scope="row">
            <span> {{ $t('dispatch.name') }} </span>
             <span v-if="row.invoices.length && row.invoices[0].master">{{ row.invoices.map(i => ' ' + i.master.name).toString() }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <!-- <table-column
          :label="$t('dispatch.status')"
          show="status"
        /> -->
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
      </table-component>
    </div>
    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <!-- <p class="table-stats">{{ $t('general.showing') }}: <b>{{ dispatch.length }}</b> {{ $t('general.of') }} <b>{{ totalDispatch }}</b></p> -->
        <h4>Dispatched</h4>
          <base-button
            v-show="dipatchedCompletedData"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            right-icon
            @click="printDispatched"
          >
            Print
          </base-button>
        <transition name="fade">
          <v-dropdown v-if="selectedDispatch && selectedDispatch.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleDispatch('sent')">
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
          @change="selectAllDispatch"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="tableDispatch"
        :data="dipatchedCompletedData"
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
          :label="$t('dispatch.invoice_id')"
        >
          <template slot-scope="row">
              <router-link :to="{path: `dispatch/${row.id}/edit`}" >
                <span> {{ $t('dispatch.invoice_id') }} </span>

                <span v-if="row.invoices.length ">{{ row.invoices.map(i => ' ' + i.invoice_number).toString() }}</span>
            </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template slot-scope="row">
              <span> {{ $t('dispatch.name') }} </span>
              <span v-if="row.master">{{ row.invoices.map(i => ' ' + i.master.name).toString() }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <!-- <table-column
          :label="$t('dispatch.status')"
          show="status"
        /> -->
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
        <template slot-scope="row">
          <span> {{ $t('dispatch.action') }} </span>
          <v-dropdown>
            <a slot="activator" href="#">
              <dot-icon />
            </a>
            <v-dropdown-item>
              <router-link :to="{path: `dispatch/${row.id}/edit`}" class="dropdown-item">
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
        </template>
      </table-column>
      </table-component>

      <!-- print table here -->
      <table-component
        id="to_print_dispatched"
        ref="tableDispatch1"
        :data="dipatchedCompletedData"
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
       <table-column :label="$t('dispatch.invoice_id')">
          <template slot-scope="row">
              <router-link :to="{path: `dispatch/${row.id}/edit`}" >
                <span> {{ $t('dispatch.invoice_id') }} </span>

                <span v-if="row.invoices.length ">{{ row.invoices.filter((v,i,a)=>a.findIndex(v2=>(v2.account_master_id===v.account_master_id))===i).map(i => ' ' + i.invoice_number + '*' + row.invoices.filter(j=>j.account_master_id===i.account_master_id).length).toString() }}</span>
            </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.name')"
        >
          <template slot-scope="row">
              <span> {{ $t('dispatch.name') }} </span>
              <span v-if="row.master">{{ row.master.name }}</span>
          </template>
        </table-column>
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <!-- <table-column
          :label="$t('dispatch.status')"
          show="status"
        /> -->
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
      </table-component>
    </div>
  </div>
</template>
<style>
body > .expandable-image.expanded {
  width: 100% !important;
}
#to_print_dispatched, #to_print_to_be_dispatch {
  display:none;
}
.expandable-image{
  width: 100px;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'
import DotIcon from '../../components/icon/DotIcon'
import moment from 'moment'
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
      change_invoice: false,
      breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('dispatch.dispatch', 2)
        }
      ],
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        date_time: '',
        status: '',
        transport: '',
        from_date: '',
        to_date: ''
      },
      index: null,
    }
  },
  computed: {
      applyFilter() {
        if (this.filters.name || this.filters.from_date ||  this.filters.to_date ||  this.filters.transport || this.filters.status) {
          return true;
        }
        return false;
    },
    ...mapGetters('dispatch', [
      'dispatch',
      'toBeDispatch',
      'selectedDispatch',
      'selectedToBeDispatch',
      'totalDispatch',
      'selectAllField',
      'selectAllToBeField'
    ]),
    showEmptyScreen () {
      return !this.totalDispatch && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedDispatch
      },
      set: function (val) {
        this.selectDispatch(val)
      }
    },
    selectToBeField: {
      get: function () {
        return this.selectedToBeDispatch
      },
      set: function (val) {
        this.selectToBeDispatch(val)
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
    if (this.selectAllField) {
      this.selectAllDispatch()
    }
    if (this.selectAllToBeField) {
      this.selectAllToBeDispatch()
    }
  },
  methods: {
    ...mapActions('dispatch', [
      'dipatchedData',
      'updateDispatch',
      'selectAllDispatch',
      'selectAllToBeDispatch',
      'selectDispatch',
      'selectToBeDispatch',
      'deleteDispatch',
      'deleteMultipleDispatch',
      'setSelectAllState',
      'setSelectAllToBeState',
      'moveMultipleDispatch',
      'moveMultipleToBeDispatch',
    ]),
    refreshTable () {
      this.$refs.tableDispatch.refresh()
      this.$refs.tableDispatch1.refresh()
      this.$refs.toBeTableDispatch1.refresh()
      this.$refs.toBeTableDispatch.refresh()
    },
    async toBeDispatchedData ({ page, filter, sort }) {
      let data = {
       name: this.filters.name === '' ? this.filters.name : this.filters.name.id,
        status: this.filters.status !== null ? this.filters.status : '',
        transport: this.filters.transport !== null ? this.filters.transport : '',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.dipatchedData(data)
      this.isRequestOngoing = false
      this.sundryDebtorsList = response.data.sundryDebtorsList;
      return {
        data: response.data.dispatch_inprogress.data,
        pagination: {
          totalPages: response.data.dispatch_inprogress.last_page,
          currentPage: response.data.dispatch_inprogress.current_page,
        }
      }
    },
    onSelectCustomer (customer) {
      this.filters.name = customer.name
    },
    async clearCustomerSearch (removedOption, id) {
      this.filters.name = ''
      this.refreshTable()
    },
    async dipatchedCompletedData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name === '' ? this.filters.name : this.filters.name.id,
        status: this.filters.status !== null ? this.filters.status : '',
        transport: this.filters.transport !== null ? this.filters.transport : '',
        from_date: this.filters.from_date === '' ? this.filters.from_date : moment(this.filters.from_date).format('DD/MM/YYYY'),
        to_date: this.filters.to_date === '' ? this.filters.to_date : moment(this.filters.to_date).format('DD/MM/YYYY'),
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        filterBy: this.applyFilter,
        page
      }

      this.isRequestOngoing = true
      let response = await this.dipatchedData(data)
      this.isRequestOngoing = false
      return {
        data: response.data.dispatch_completed.data,
        pagination: {
          totalPages: response.data.dispatch_completed.last_page,
          currentPage: response.data.dispatch_completed.current_page,
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
        this.refreshTable();
			}, 1000);
    },
    clearFilter () {
       this.filtersApplied = false;
      this.showFilters=false;
      this.filters = {
        name: '',
        from_date: '',
        to_date: '',
        status: '',
        transport: ''
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
            this.$refs.tableDispatch.refresh()
            this.$refs.toBeTableDispatch.refresh()
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleDispatch (type) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('dispatch.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          const ids = 'draft' === type ? this.selectedToBeDispatch : this.selectedDispatch;
          let res = await this.deleteMultipleDispatch(ids)
          if (res.data.dispatch) {
            window.toastr['success'](this.$tc('dispatch.deleted_message', 2))
            this.$refs.tableDispatch.refresh()
            this.$refs.toBeTableDispatch.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
    // async multipleDispatch (type) {
    //   let dispatchArray = this.dispatch.filter((i) => this.selectedDispatch.includes(i.id));
    //   let tobeDispatchArray = this.toBeDispatch.filter((i) => this.selectedToBeDispatch.includes(i.id));
    //   let modal_text = this.$tc('dispatch.confirm_to_be_dispatch', 2);
    //   if (type === 'draft') {
    //     modal_text = this.$tc('dispatch.confirm_dispatch', 2);
    //   }
    //   swal({
    //     title: this.$t('general.are_you_sure'),
    //     text: modal_text,
    //     icon: '/assets/icon/paper-plane-solid.svg',
    //     buttons: true,
    //     dangerMode: false
    //   }).then(async (willSend) => {
    //     if (willSend) {
    //       if ('sent' === type) {
    //         let name = '';
    //         dispatchArray.map(i => {
    //           if (name && name !== i.master.name) {
    //              window.toastr['error']('To move multiple dispatch, party name should be same.')
    //              return
    //           }
    //         });
    //         let res = await this.moveMultipleDispatch(this.selectedDispatch)
    //         if (res) {
    //           window.toastr['success'](this.$tc('dispatch.multiple_dispatch_message', 2))
    //           window.location.reload()
    //         } else {
    //           window.toastr['error'](res.data.message)
    //         }
    //       } else {
    //         let name = '';
    //         let allowMoving = true;
    //         let showEdit = [];
    //         tobeDispatchArray.some(i => {
    //           if (name && name !== i.master.name) {
    //              window.toastr['error']('To move multiple dispatch, party name should be same.')
    //              allowMoving = false
    //              return true
    //           }
    //           name = i.master.name
    //           if (!i.person || !i.transport || i.invoices && !i.invoices.length) {
    //             showEdit.push(i)
    //             allowMoving = false
    //           }
    //         })
    //         if (showEdit.length) {
    //           showEdit.map(i => {
    //             window.open('/dispatch/' + i.id + '/edit', '_blank').focus()
    //           })
    //         }
    //         if (allowMoving) {
    //           let res = await this.moveMultipleDispatch(this.selectedToBeDispatch)
    //           if (res) {
    //             window.toastr['success'](this.$tc('dispatch.multiple_dispatch_message', 2))
    //             window.location.reload()
    //           } else {
    //             window.toastr['error'](res.data.message)
    //           }
    //         }
    //       }
    //     }
    //   })
    // },
    async singleDispatch (id) {
      let data = this.toBeDispatch.find(i => i.id === id);
      data.invoice_id = data.invoices.map(i => i.id.toString())
      data.status = {
          id: 2,
          name: 'Sent',
      }
      if (data) {
        if (!data.person || !data.transport || data.invoices && !data.invoices.length) {
          window.open('/dispatch/' + id + '/edit', '_blank').focus();
        } else {
          let res = await this.updateDispatch(data);
          if (res.data.dispatch) {
            window.location.reload()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      }
    },
    setIndex(index) {
      this.index = index
    },
    printToBeDispatch() {
        printJS({
          printable: 'to_print_to_be_dispatch',
          type: 'html',
          ignoreElements: ['no-print-check', 'no-print-option'],
          scanStyles: true,
          targetStyles: ['*'],
          style: '.hide-print {display: none !important;}.table-component__table th, .table-component__table td {padding: 0.75em 1.25em;vertical-align: top;text-align: left;}.table thead th {border: 0;position: relative;top: 25px; botton: 20px;}.table-component__table { min-width: 100%; border-collapse: separate; table-layout: auto; margin-bottom: 0;border-spacing: 0 15px;} .table .table-component__table__body tr {border-radius: 10px;transition: all ease-in-out 0.2s;} .table .table-component__table__body tr:first-child td {border-top: 0;} .table .table-component__table__body td {padding: 0px 15px !important;height: 20px !important;} .table-component td > span:first-child {background: #EBF1FA;color: #55547A;display: none;font-size: 10px;font-weight: bold;padding: 5px;left: 0;position: absolute;text-transform: uppercase;top: 0;}'
        })
    },
    printDispatched() {

        printJS({
          printable: 'to_print_dispatched',
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
