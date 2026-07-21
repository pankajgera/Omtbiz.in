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
      <div class="table-actions notes-table-actions mt-5">
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

      <div id="to_print" class="table-component notes-table-component">
        <div class="table-component__table-wrapper">
          <base-loader v-if="isRequestOngoing" class="table-loader" />

          <table class="table-component__table table notes-table">
            <caption class="table-component__table__caption">
              {{ $tc('notes.notes', 2) }}
            </caption>
            <thead class="table-component__table__head">
              <tr>
                <th class="notes-selection-column hide-print" scope="col">
                  <label class="notes-select-all-label" for="select-all">
                    <input
                      id="select-all"
                      v-model="selectAllFieldStatus"
                      type="checkbox"
                      class="notes-checkbox"
                      @change="selectAllNotes"
                    >
                    <span>{{ $t('general.select_all') }}</span>
                  </label>
                </th>
                <th v-for="column in noteColumns" :key="column.field" scope="col">
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
                <th class="notes-action-column hide-print" scope="col">
                  <span class="sr-only">{{ $t('notes.action') }}</span>
                </th>
              </tr>
            </thead>
            <tbody class="table-component__table__body">
              <tr v-for="note in notes" :key="note.id">
                <td class="notes-selection-column hide-print">
                  <input
                    :id="`note-${note.id}`"
                    v-model="selectField"
                    :value="note.id"
                    :aria-label="`Select ${note.name}`"
                    type="checkbox"
                    class="notes-checkbox"
                  >
                </td>
                <td :data-label="$t('notes.name')">
                  <router-link :to="{ path: `notes/${note.id}/edit` }">
                    {{ note.name }}
                  </router-link>
                </td>
                <td :data-label="$t('notes.design_no')">{{ note.design_no }}</td>
                <td :data-label="$t('notes.rate')">{{ note.rate }}</td>
                <td :data-label="$t('notes.average')">{{ note.average }}</td>
                <td class="action-dropdown notes-action-column hide-print">
                  <v-dropdown :show-arrow="false">
                    <template #activator>
                      <button class="table-row-menu" type="button" :aria-label="$t('notes.action')">
                        <dot-icon />
                      </button>
                    </template>
                    <v-dropdown-item>
                      <router-link :to="{ path: `notes/${note.id}/edit` }" class="dropdown-item">
                        <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon" />
                        {{ $t('general.edit') }}
                      </router-link>
                    </v-dropdown-item>
                    <v-dropdown-item>
                      <button class="dropdown-item" type="button" @click="removeNotes(note.id)">
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

        <div v-if="!notes.length && !isRequestOngoing" class="table-component__message">
          There are no matching rows
        </div>

        <table-pagination
          v-if="pagination.totalPages > 1 && !isRequestOngoing"
          :pagination="pagination"
          @pageChange="loadNotes"
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
import printJS from 'print-js'
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
      isRequestOngoing: true,
      noteRequestId: 0,
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
    noteColumns () {
      return [
        { field: 'name', label: this.$t('notes.name') },
        { field: 'design_no', label: this.$t('notes.design_no') },
        { field: 'rate', label: this.$t('notes.rate') },
        { field: 'average', label: this.$t('notes.average') }
      ]
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
  mounted () {
    this.loadNotes()
  },
  unmounted() {
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
      return this.loadNotes(1)
    },
    async loadNotes (page = 1) {
      const requestId = ++this.noteRequestId
      let data = {
        name: this.filters.name !== null ? this.filters.name : '',
        rate: this.filters.rate !== null ? this.filters.rate : '',
        average: this.filters.average !== null ? this.filters.average : '',
        design_no: this.filters.design_no !== null ? this.filters.design_no : '',
        orderByField: this.sort.fieldName,
        orderBy: this.sort.order,
        page
      }

      this.isRequestOngoing = true
      try {
        const response = await this.fetchNotes(data)

        if (requestId !== this.noteRequestId) {
          return
        }

        const notesPage = response.data.notes
        this.pagination = {
          totalPages: notesPage.last_page,
          currentPage: notesPage.current_page || page,
          count: this.notes.length
        }
      } finally {
        if (requestId === this.noteRequestId) {
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

      this.loadNotes(1)
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
            this.refreshTable()
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
            this.refreshTable()
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
