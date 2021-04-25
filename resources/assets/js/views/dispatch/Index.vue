<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('dispatch.dispatch', 2) }}</h3>
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
            {{ $tc('dispatch.dispatch', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalDispatch || filtersApplied"
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
        <router-link slot="item-title" class="col-xs-2" to="dispatch/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('dispatch.new_dispatch') }}
          </base-button>
        </router-link>
      </div>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('dispatch.name') }} </label>
            <base-input
              v-model="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('dispatch.date_time') }} </label>
            <base-input
              v-model="filters.date_time"
              type="text"
              name="date_time"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('dispatch.status') }} </label>
            <base-input
              v-model="filters.status"
              type="text"
              name="status"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
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
        <transition name="fade">
          <v-dropdown v-if="selectedDispatch.length" :show-arrow="false">
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
        ref="table"
        :data="inProgressData"
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
          :label="$t('dispatch.name')"
          show="name"
        />
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <table-column
          :label="$t('dispatch.status')"
          show="status"
        />
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
        <table-column
          :key="Math.random()"
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
    </div>
    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <!-- <p class="table-stats">{{ $t('general.showing') }}: <b>{{ dispatch.length }}</b> {{ $t('general.of') }} <b>{{ totalDispatch }}</b></p> -->
        <transition name="fade">
          <v-dropdown v-if="selectedDispatch.length" :show-arrow="false">
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
        ref="table"
        :data="completedData"
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
          :label="$t('dispatch.name')"
          show="name"
        />
        <table-column
          :label="$t('dispatch.date_time')"
          show="date_time"
        />
        <table-column
          :label="$t('dispatch.status')"
          show="status"
        />
        <table-column
          :label="$t('dispatch.transport')"
          show="transport"
        />
        <table-column
          :key="Math.random()"
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
        date_time: '',
        status: '',
        transport: ''
      },
      index: null,
    }
  },
  computed: {
    ...mapGetters('dispatch', [
      'dispatch',
      'selectedDispatch',
      'totalDispatch',
      'selectAllField'
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
      this.selectAllDispatch()
    }
  },
  methods: {
    ...mapActions('dispatch', [
      'fetchDispatch',
      'selectAllDispatch',
      'selectDispatch',
      'deleteDispatch',
      'deleteMultipleDispatch',
      'setSelectAllState'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async inProgressData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        status: this.filters.status !== null ? this.filters.status : '',
        transport: this.filters.transport !== null ? this.filters.transport : '',
        date_time: this.filters.date_time !== null ? this.filters.date_time : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchDispatch(data)
      this.isRequestOngoing = false

      return {
        data: response.data.dispatch_inprogress.data,
        pagination: {
          totalPages: response.data.dispatch_inprogress.last_page,
          currentPage: response.data.dispatch_inprogress.current_page,
        }
      }
    },
    async completedData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        status: this.filters.status !== null ? this.filters.status : '',
        transport: this.filters.transport !== null ? this.filters.transport : '',
        date_time: this.filters.date_time !== null ? this.filters.date_time : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchDispatch(data)
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
      this.filtersApplied = true
      this.refreshTable()
    },
    clearFilter () {
      this.filters = {
        name: '',
        date_time: '',
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
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('dispatch.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteDispatch(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('dispatch.deleted_message', 1))
            this.$refs.table.refresh()
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
          let res = await this.deleteMultipleDispatch()
          if (res.data.success) {
            window.toastr['success'](this.$tc('dispatch.deleted_message', 2))
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
