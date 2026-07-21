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
            {{ $tc('masters.account_master', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-3 mr-4">
          <base-button
            v-show="totalMasters || filtersApplied"
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
        <div class="col-xs-3">
          <router-link slot="item-title" to="masters/create">
            <base-button
              color="theme"
              icon="plus"
              size="large"
            >
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
            <base-input
              v-model.trim="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('masters.groups') }} </label>
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
        <label class="col title">{{ $t('masters.no_masters') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('masters.list_of_masters') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('masters/create')"
        >
          {{ $t('masters.add_new_master') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ masters.length }}</b> {{ $t('general.of') }} <b>{{ totalMasters }}</b></p>
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

      <div class="table-component masters-table-component">
        <div class="table-component__table-wrapper">
          <base-loader v-if="isRequestOngoing" class="table-loader" />

          <table class="table-component__table table masters-table">
            <caption class="table-component__table__caption">
              {{ $tc('masters.account_master', 2) }}
            </caption>
            <thead class="table-component__table__head">
              <tr>
                <th class="masters-selection-column" scope="col">
                  <label class="masters-select-all-label" for="select-all-masters">
                    <input
                      id="select-all-masters"
                      v-model="selectAllFieldStatus"
                      type="checkbox"
                      class="masters-checkbox"
                      @change="selectAllMasters"
                    >
                    <span>{{ $t('general.select_all') }}</span>
                  </label>
                </th>
                <th v-for="column in masterColumns" :key="column.field" scope="col">
                  <button
                    :aria-label="sortLabel(column.label, column.field)"
                    class="table-sort-button"
                    type="button"
                    @click="changeSorting(column.field)"
                  >
                    {{ column.label }}
                    <span
                      v-if="sort.fieldName === column.field"
                      :class="sort.order === 'asc' ? 'is-ascending' : 'is-descending'"
                      class="table-sort-indicator"
                      aria-hidden="true"
                    />
                  </button>
                </th>
                <th class="masters-action-column" scope="col">
                  <span class="sr-only">{{ $t('masters.action') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="table-component__table__body">
              <tr v-for="master in masters" :key="master.id">
                <td class="masters-selection-column">
                  <input
                    :id="`master-${master.id}`"
                    v-model="selectField"
                    :value="master.id"
                    :aria-label="`Select ${master.name}`"
                    type="checkbox"
                    class="masters-checkbox"
                  >
                </td>
                <td :data-label="$t('masters.name')">
                  <router-link :to="{ path: `masters/${master.id}/edit` }">
                    {{ master.name }}
                  </router-link>
                </td>
                <td :data-label="$t('masters.groups')">{{ master.groups }}</td>
                <td class="action-dropdown masters-action-column">
                  <v-dropdown :show-arrow="false">
                    <template #activator>
                      <button class="table-row-menu" type="button" :aria-label="$t('masters.action')">
                        <dot-icon />
                      </button>
                    </template>
                    <v-dropdown-item>
                      <router-link :to="{ path: `masters/${master.id}/edit` }" class="dropdown-item">
                        <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                        {{ $t('general.edit') }}
                      </router-link>
                    </v-dropdown-item>
                    <v-dropdown-item>
                      <button class="dropdown-item" type="button" @click="removeMasters(master.id)">
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

        <div v-if="!masters.length && !isRequestOngoing" class="table-component__message">
          There are no matching rows
        </div>

        <table-pagination
          v-if="pagination.totalPages > 1 && !isRequestOngoing"
          :pagination="pagination"
          @pageChange="loadMasters"
        />
      </div>
    </div>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
import DotIcon from '../../components/icon/DotIcon'
import SatelliteIcon from '../../components/icon/SatelliteIcon'
import BaseButton from '../../../js/components/base/BaseButton'
import BaseLoader from '../../components/base/BaseLoader'
import TablePagination from '../../components/base/base-table/components/Pagination'

export default {
  components: {
    DotIcon,
    SatelliteIcon,
    BaseButton,
    BaseLoader,
    TablePagination,
  },
  data () {
    return {
      id: null,
      showFilters: false,
      isRequestOngoing: true,
      masterRequestId: 0,
      pagination: {
        totalPages: 0,
        currentPage: 1,
        count: 0
      },
      sort: {
        fieldName: 'created_at',
        order: 'desc'
      },
      filtersApplied: false,
      filters: {
        name: '',
        groups: '',
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('master', [
      'masters',
      'selectedMasters',
      'totalMasters',
      'selectAllField'
    ]),
    showEmptyScreen () {
      return !this.totalMasters && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    masterColumns () {
      return [
        { field: 'name', label: this.$t('masters.name') },
        { field: 'groups', label: this.$t('masters.groups') }
      ]
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
  mounted () {
    this.loadMasters()
  },
  unmounted() {
    if (this.timer) {
      clearTimeout(this.timer)
    }

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
    refreshTable () {
      return this.loadMasters(1)
    },
    async loadMasters (page = 1) {
      const requestId = ++this.masterRequestId
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        groups: this.filters.groups !== null ? this.filters.groups : '',
        orderByField: this.sort.fieldName,
        orderBy: this.sort.order,
        page
      }

      this.isRequestOngoing = true
      try {
        const response = await this.fetchMasters(data)

        if (requestId !== this.masterRequestId) {
          return
        }

        const mastersPage = response.data.masters
        this.pagination = {
          totalPages: mastersPage.last_page,
          currentPage: mastersPage.current_page || page,
          count: this.masters.length
        }
      } finally {
        if (requestId === this.masterRequestId) {
          this.isRequestOngoing = false
        }
      }
    },
    changeSorting (fieldName) {
      if (this.sort.fieldName === fieldName) {
        this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc'
      } else {
        this.sort.fieldName = fieldName
        this.sort.order = 'asc'
      }

      this.loadMasters(1)
    },
    sortLabel (label, fieldName) {
      if (this.sort.fieldName !== fieldName) {
        return `Sort by ${label}`
      }

      return `Sort by ${label} ${this.sort.order === 'asc' ? 'descending' : 'ascending'}`
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
    async removeMasters (id) {
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
            this.refreshTable()
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
    async removeMultipleMasters () {
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
