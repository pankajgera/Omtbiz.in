<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('masters.account_master', 2) }}</h3>
        </div>
      </div>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/">
            {{ $t('general.home') }}
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="#">
            {{ $tc('masters.account_master', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-3 mr-4">
          <base-button v-show="totalMasters || filtersApplied" :outline="true" :icon="filterIcon" color="theme"
            size="large" right-icon @click="toggleFilter">
            {{ $t('general.filter') }}
          </base-button>
        </div>
        <div class="col-xs-3">
          <router-link slot="item-title" to="masters/create">
            <base-button color="theme" icon="plus" size="large">
              {{ $t('masters.add_account_master') }}
            </base-button>
          </router-link>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('masters.name') }} </label>
            <base-input v-model.trim="filters.name" type="text" name="name" autocomplete="off" />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('masters.groups') }} </label>
            <base-input v-model="filters.groups" type="text" name="groups" autocomplete="off" />
          </div>
          <label class="clear-filter" @click="clearFilter"> {{ $t('general.clear_all') }}</label>
        </div>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <satellite-icon class="mt-5 mb-4" />
      <div class="row" align="center">
        <label class="col title">{{ $t('masters.no_masters') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('masters.list_of_masters') }}</label>
      </div>
      <div class="btn-container">
        <base-button :outline="true" color="theme" class="mt-3" size="large" @click="$router.push('masters/create')">
          {{ $t('masters.add_new_master') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ masters.length }}</b> {{ $t('general.of') }} <b>{{
          totalMasters }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedMasters.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleMasters">
                <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                {{ $t('general.delete') }}
              </div>
            </v-dropdown-item>
          </v-dropdown>
        </transition>
      </div>

      <div class="custom-control custom-checkbox">
        <input id="select-all" v-model="selectAllFieldStatus" type="checkbox" class="custom-control-input"
          @change="selectAllMasters">
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component ref="table" :data="fetchData" :show-filter="false" table-class="table">

        <table-column :sortable="false" :filterable="false" cell-class="no-click">
          <template slot-scope="row">
            <div class="custom-control custom-checkbox">
              <input :id="row.id" v-model="selectField" :value="row.id" type="checkbox" class="custom-control-input">
              <label :for="row.id" class="custom-control-label" />
            </div>
          </template>
        </table-column>
        <table-column :label="$t('masters.name')" show="name">
          <template slot-scope="row">
            <router-link :to="{ path: `masters/${row.id}/edit` }" class="dropdown-item">
              {{ row.name }}
            </router-link>
          </template>
        </table-column>
        <table-column :label="$t('masters.groups')" show="groups" />

        <table-column :key="Math.random()" :sortable="false" :filterable="false" cell-class="action-dropdown">
          <template slot-scope="row">
            <span> {{ $t('masters.action') }} </span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
              <v-dropdown-item>

                <router-link :to="{ path: `masters/${row.id}/edit` }" class="dropdown-item">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                  {{ $t('general.edit') }}
                </router-link>

              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeMasters(row.id)">
                  <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
                  {{ $t('general.delete') }}
                </div>
              </v-dropdown-item>
              <v-dropdown-item>

                <router-link class="border-0 dropdown-item" :to="{ path: `masters/${row.id}/credit` }"
                  v-if="row && row.groups === 'Sundry Debtors'">
                  <font-awesome-icon icon="credit-card" class="dropdown-item-icon" />
                  {{ $t('masters.button_label') }}
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
body>.expandable-image.expanded {
  width: 100% !important;
}

.expandable-image {
  width: 100px;
}

.table .table-component__table__body td {
  padding: 0px 15px !important;
  height: 20px !important;
}

.table-component__table {
  border-spacing: 0 5px !important;
}

@media (max-width: 768px) {
  .table-component .dropdown-group {
    top: 4px;
  }

  .table .table-component__table__body td.no-click {
    height: 25px !important;
  }

  .table .table-component__table__body td.action-dropdown {
    height: 5px !important;
  }

  .table .table-component__table__body td {
    height: 80px !important;
  }

  .table .table-component__table__body td span.dot {
    position: relative;
  }

  .table .table-component__table__body td span {
    position: relative;
    overflow: auto;
    display: block;
  }
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
  data() {
    return {
      id: null,
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        groups: '',
      },
      index: null,
      isSundryDebtor: false,
    }
  },
  computed: {
    ...mapGetters('master', [
      'masters',
      'selectedMasters',
      'totalMasters',
      'selectAllField'
    ]),
    
    showEmptyScreen() {
      return !this.totalMasters && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon() {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedMasters
      },
      set: function (val) {
        this.selectMaster(val)
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
  destroyed() {
    if (this.selectAllField) {
      this.selectAllMasters()
    }
  },
  methods: {
    ...mapActions('master', [
      'fetchMasters',
      'selectAllMasters',
      'selectMaster',
      'deleteMaster',
      'deleteMultipleMasters',
      'setSelectAllState'
    ]),
    
    refreshTable() {
      this.$refs.table.refresh()
    },
    async fetchData({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        groups: this.filters.groups !== null ? this.filters.groups : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchMasters(data)
      this.isRequestOngoing = false

      return {
        data: response.data.masters.data,
        pagination: {
          totalPages: response.data.masters.last_page,
          currentPage: page
        }
      }
    },
    setFilters() {
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.filtersApplied = true
        this.refreshTable()
        this.isSundryDebtor = this.filters.groups === 'Sundry Debtors'
      }, 1000);
    },
    clearFilter() {
      this.filters = {
        name: '',
        groups: '',
      }

      this.$nextTick(() => {
        this.filtersApplied = false
      })
    },
    toggleFilter() {
      if (this.showFilters && this.filtersApplied) {
        this.clearFilter()
        this.refreshTable()
      }

      this.showFilters = !this.showFilters
    },
    async removeMasters(id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('masters.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMaster(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('masters.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'master_attached') {
            window.toastr['error'](this.$tc('masters.master_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleMasters() {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('masters.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleMasters()
          if (res.data.success) {
            window.toastr['success'](this.$tc('masters.deleted_message', 2))
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
