<template>
  <div class="items main-content">
    <div class="page-header">
      <div class="d-flex flex-row">
        <div>
          <h3 class="page-title">{{ $tc('notes.notes', 2) }}</h3>
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
            {{ $tc('notes.notes', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalNotes || filtersApplied"
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
        <router-link slot="item-title" class="col-xs-2" to="notes/create">
          <base-button
            color="theme"
            icon="plus"
            size="large"
          >
            {{ $t('notes.add_bill') }}
          </base-button>
        </router-link>
      </div>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.name') }} </label>
            <base-input
              v-model="filters.name"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <!-- <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.unit') }} </label>
            <base-select
              v-model="filters.unit"
              :options="units"
              :searchable="true"
              :show-labels="false"
              :placeholder="$t('notes.select_a_unit')"
              label="name"
              autocomplete="off"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.price') }} </label>
            <base-input
              v-model="filters.price"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div> -->
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.notes') }} </label>
            <base-input
              v-model="filters.notes"
              type="text"
              name="name"
              autocomplete="off"
            />
          </div>
          <!-- <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.notes') }} </label>
            <base-date-picker
              v-model="filters.from_date"
              :invalid="$v.filters.from_date.$error"
              :calendar-button="true"
              calendar-button-icon="calendar"
              @change="$v.filters.from_date.$touch()"
            />
          </div>
          <div class="col-sm-4">
            <label class="form-label"> {{ $tc('notes.notes') }} </label>
            <base-date-picker
              v-model="filters.to_date"
              :invalid="$v.filters.to_date.$error"
              :calendar-button="true"
              calendar-button-icon="calendar"
              @change="$v.filters.to_date.$touch()"
            />
          </div> -->
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
          :label="$t('notes.name')"
          show="name"
        />
        <table-column
          :label="$t('notes.notes')"
          show="notes"
        />
        <table-column
          :label="$t('notes.added_on')"
          sort-as="created_at"
          show="formattedCreatedAt"
        />
        <table-column
          label="Image"
          show="images"
        >
          <template v-if="row.images" slot-scope="row">
            <expandable-image
              class="image"
              :src="row.images.original_image_path"
            ></expandable-image>
          </template>
        </table-column>
        <table-column
          :key="Math.random()"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template slot-scope="row">
            <span> {{ $t('notes.action') }} </span>
            <v-dropdown>
              <a slot="activator" href="#">
                <dot-icon />
              </a>
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
      showFilters: false,
      sortedBy: 'created_at',
      units: [
        { name: 'box', value: 'box' },
        { name: 'cm', value: 'cm' },
        { name: 'dz', value: 'dz' },
        { name: 'ft', value: 'ft' },
        { name: 'g', value: 'g' },
        { name: 'in', value: 'in' },
        { name: 'kg', value: 'kg' },
        { name: 'km', value: 'km' },
        { name: 'lb', value: 'lb' },
        { name: 'mg', value: 'mg' },
        { name: 'pc', value: 'pc' }
      ],
      isRequestOngoing: true,
      filtersApplied: false,
      filters: {
        name: '',
        unit: '',
        price: '',
        notes: ''
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
    ...mapGetters('currency', [
      'defaultCurrency'
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
        this.selectItem(val)
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
      this.selectAllNotes()
    }
  },
  methods: {
    ...mapActions('notes', [
      'fetchNotes',
      'selectAllNotes',
      'selectItem',
      'deleteItem',
      'deleteMultipleNotes',
      'setSelectAllState'
    ]),
    refreshTable () {
      this.$refs.table.refresh()
    },
    async fetchData ({ page, filter, sort }) {
      let data = {
        search: this.filters.name !== null ? this.filters.name : '',
        unit: this.filters.unit !== null ? this.filters.unit.name : '',
        price: this.filters.price * 100,
        notes: this.filters.notes !== null ? this.filters.notes : '',
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
      this.filtersApplied = true
      this.refreshTable()
    },
    clearFilter () {
      this.filters = {
        name: '',
        unit: '',
        price: '',
        notes: ''
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
          let res = await this.deleteItem(this.id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('notes.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'note_attached') {
            window.toastr['error'](this.$tc('notes.note_attached_message'), this.$t('general.action_failed'))
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
    }
  }
}
</script>
