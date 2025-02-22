<template>
  <div class="items main-content">
    <div class="page-header">
       <Header :title="$tc('notes.notes', 2)" :bread-crumb-links="breadCrumbLinks">
        <div v-show="totalNotes" class="mr-4 mb-3 mb-sm-0">
          <base-button
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            right-icon
            @click="printNote"
          >
            Print
          </base-button>
        </div>
        <div v-show="totalNotes || filtersApplied" class="mr-4 mb-3 mb-sm-0">
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
          <router-link slot="item-title" to="notes/create">
            <base-button
              color="theme"
              icon="plus"
              size="large"
            >
              {{ $t('notes.new_note') }}
            </base-button>
          </router-link>
        </div>
       </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('notes.name') }} </label>
            <base-input
              v-model.trim="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('notes.design_no') }} </label>
            <base-input
              v-model="filters.design_no"
              type="text"
              name="design_no"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('notes.rate') }} </label>
            <base-input
              v-model="filters.rate"
              type="text"
              name="rate"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label"> {{ $tc('notes.average') }} </label>
            <base-input
              v-model="filters.average"
              type="text"
              name="average"
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
        <label class="col title">{{ $t('notes.no_notes') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('notes.list_of_notes') }}</label>
      </div>
      <div class="btn-container">
        <base-button
          :outline="true"
          color="theme"
          class="mt-3"
          size="large"
          @click="$router.push('notes/create')"
        >
          {{ $t('notes.add_new_note') }}
        </base-button>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ notes.length }}</b> {{ $t('general.of') }} <b>{{ totalNotes }}</b></p>
        <transition name="fade">
          <v-dropdown v-if="selectedNotes.length" :show-arrow="false">
            <span slot="activator" href="#" class="table-actions-button dropdown-toggle">
              {{ $t('general.actions') }}
            </span>
            <v-dropdown-item>
              <div class="dropdown-item" @click="removeMultipleNotes">
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
          @change="selectAllNotes"
        >
        <label v-show="!isRequestOngoing" for="select-all" class="custom-control-label selectall">
          <span class="select-all-label">{{ $t('general.select_all') }} </span>
        </label>
      </div>

      <table-component
        ref="table"
        id="to_print"
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
            <div class="custom-control custom-checkbox" id="no-print-check">
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
          :label="$t('notes.name')"
          show="name"
        >
          <template slot-scope="row">
             <router-link :to="{path: `notes/${row.id}/edit`}" >
                {{ row.name }}
              </router-link>
          </template>
        </table-column>
        <table-column
          :label="$t('notes.design_no')"
          show="design_no"
        />
        <table-column
          :label="$t('notes.rate')"
          show="rate"
        />
        <table-column
          :label="$t('notes.average')"
          show="average"
        />
        <table-column
          :key="Math.random()"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template slot-scope="row">
            <span id="no-print-option"> {{ $t('notes.action') }} </span>
            <v-dropdown>
              <span slot="activator" href="#">
                <dot-icon />
              </span>
              <v-dropdown-item>

                <router-link :to="{path: `notes/${row.id}/edit`}" class="dropdown-item">
                  <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                  {{ $t('general.edit') }}
                </router-link>

              </v-dropdown-item>
              <v-dropdown-item>
                <div class="dropdown-item" @click="removeNotes(row.id)">
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
      breadCrumbLinks:[
        {
          url:'dashboard',
          title:this.$t('general.home'),
        },
        {
          url:'#',
          title:this.$tc('notes.notes', 2)
        }
      ],
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        design_no: '',
        rate: '',
        average: ''
      },
      index: null
    }
  },
  computed: {
    ...mapGetters('notes', [
      'notes',
      'selectedNotes',
      'totalNotes',
      'selectAllField'
    ]),
    showEmptyScreen () {
      return !this.totalNotes && !this.isRequestOngoing && !this.filtersApplied
    },
    filterIcon () {
      return (this.showFilters) ? 'times' : 'filter'
    },
    selectField: {
      get: function () {
        return this.selectedNotes
      },
      set: function (val) {
        this.selectNote(val)
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
    },
  },
  destroyed () {
    if (this.selectAllField) {
      this.selectAllNotes()
    }
  },
  methods: {
    ...mapActions('notes', [
      'fetchNotes',
      'selectAllNotes',
      'selectNote',
      'deleteNote',
      'deleteMultipleNotes',
      'setSelectAllState'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        rate: this.filters.rate !== null ? this.filters.rate : '',
        average: this.filters.average !== null ? this.filters.average : '',
        design_no: this.filters.design_no !== null ? this.filters.design_no : '',
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page
      }

      this.isRequestOngoing = true
      let response = await this.fetchNotes(data)
      this.isRequestOngoing = false

      return {
        data: response.data.notes.data,
        pagination: {
          totalPages: response.data.notes.last_page,
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
        design_no: '',
        rate: '',
        average: ''
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
    async removeNotes (id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('notes.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteNote(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('notes.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    async removeMultipleNotes () {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('notes.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleNotes()
          if (res.data.success) {
            window.toastr['success'](this.$tc('notes.deleted_message', 2))
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
    printNote() {
        printJS({
          printable: 'to_print',
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
