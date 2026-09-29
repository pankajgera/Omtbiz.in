<template>
  <div class="recycle-bin main-content">
    <div class="page-header">
      <Header :title="$t('recycle_bin.title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <p class="recycle-bin-note">
      <font-awesome-icon icon="info-circle" class="me-2" />
      {{ $t('recycle_bin.retention_note', { days: retentionDays }) }}
    </p>

    <div class="search-bar row">
      <div class="col-sm-6">
        <base-input
          v-model="search"
          type="text"
          name="search"
          icon="search"
          autocomplete="off"
          :placeholder="$t('recycle_bin.search_placeholder')"
          @input="onFilterChange"
        />
      </div>
      <div class="col-sm-4">
        <base-select
          v-model="type"
          :options="typeOptions"
          :searchable="false"
          :show-labels="false"
          :allow-empty="false"
          label="label"
          track-by="value"
          @input="onFilterChange"
        />
      </div>
    </div>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <font-awesome-icon icon="trash-restore" class="recycle-bin-empty-icon mt-5 mb-4" />
      <div class="row" align="center">
        <label class="col title">{{ $t('recycle_bin.empty_title') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('recycle_bin.empty_text', { days: retentionDays }) }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ rowCount }}</b> {{ $t('general.of') }} <b>{{ total }}</b></p>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table"
      >
        <table-column :label="$t('recycle_bin.record')" show="label" :sortable="false">
          <template #default="row">
            <span :class="['recycle-bin-type', 'recycle-bin-type--' + row.resource_type]">{{ typeLabel(row.resource_type) }}</span>
            <div class="recycle-bin-label">{{ row.label }}</div>
          </template>
        </table-column>
        <table-column :label="$t('recycle_bin.party')" show="party" :sortable="false">
          <template #default="row">{{ row.party || '—' }}</template>
        </table-column>
        <table-column :label="$t('recycle_bin.amount')" show="amount" :sortable="false">
          <template #default="row">₹ {{ numberWithCommas(row.amount || 0) }}</template>
        </table-column>
        <table-column :label="$t('recycle_bin.deleted')" show="deleted_at" :sortable="false">
          <template #default="row">
            <div>{{ formatDateTime(row.deleted_at) }}</div>
            <small class="text-muted">{{ row.deleted_by || '—' }}</small>
          </template>
        </table-column>
        <table-column :label="$t('recycle_bin.auto_delete')" show="purge_at" :sortable="false">
          <template #default="row">
            <span :class="{ 'recycle-bin-soon': isSoon(row.purge_at) }">{{ timeLeft(row.purge_at) }}</span>
          </template>
        </table-column>
        <table-column :sortable="false" :filterable="false" cell-class="action-dropdown no-click">
          <template #default="row">
            <base-button
              size="small"
              color="theme"
              icon="undo"
              :outline="true"
              :loading="restoringId === row.id"
              :disabled="restoringId !== null"
              @click="restore(row)"
            >
              {{ $t('recycle_bin.restore') }}
            </base-button>
          </template>
        </table-column>
      </table-component>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import moment from 'moment'
import GlobalMixin from '../../helpers/mixins.js'

export default {
  mixins: [GlobalMixin],
  data () {
    return {
      search: '',
      type: null,
      total: 0,
      rowCount: 0,
      retentionDays: 7,
      isRequestOngoing: true,
      filtersApplied: false,
      restoringId: null,
      timer: null,
      breadCrumbLinks: [
        { url: 'dashboard', title: this.$t('general.home') },
        { url: '#', title: this.$t('recycle_bin.title') }
      ]
    }
  },
  computed: {
    typeOptions () {
      return [
        { label: this.$t('recycle_bin.all_types'), value: '' },
        { label: this.$t('recycle_bin.types.invoice'), value: 'invoice' },
        { label: this.$t('recycle_bin.types.voucher'), value: 'voucher' },
        { label: this.$t('recycle_bin.types.receipt'), value: 'receipt' },
        { label: this.$t('recycle_bin.types.payment'), value: 'payment' }
      ]
    },
    showEmptyScreen () {
      return !this.total && !this.isRequestOngoing && !this.filtersApplied
    }
  },
  created () {
    this.type = this.typeOptions[0]
  },
  unmounted () {
    clearTimeout(this.timer)
  },
  methods: {
    ...mapActions('recycleBin', ['fetchRecycleBin', 'restoreRecycleBinEntry']),
    async fetchData ({ page }) {
      this.isRequestOngoing = true
      this.filtersApplied = !!(this.search || (this.type && this.type.value))
      try {
        const response = await this.fetchRecycleBin({
          page,
          search: this.search,
          type: this.type ? this.type.value : ''
        })
        const entries = response.data.entries
        this.total = entries.total
        this.rowCount = entries.data.length
        this.retentionDays = response.data.retention_days
        return {
          data: entries.data,
          pagination: { totalPages: entries.last_page, currentPage: entries.current_page || page, count: entries.total }
        }
      } finally {
        this.isRequestOngoing = false
      }
    },
    onFilterChange () {
      clearTimeout(this.timer)
      this.timer = setTimeout(() => this.$refs.table && this.$refs.table.refresh(), 400)
    },
    typeLabel (type) {
      return this.$t('recycle_bin.types.' + type)
    },
    formatDateTime (value) {
      return moment(value).format('DD-MM-YYYY HH:mm')
    },
    timeLeft (purgeAt) {
      const hours = moment(purgeAt).diff(moment(), 'hours')
      if (hours <= 0) {
        return this.$t('recycle_bin.within_the_hour')
      }
      return hours < 24
        ? this.$tc('recycle_bin.hours_left', hours, { count: hours })
        : this.$tc('recycle_bin.days_left', Math.floor(hours / 24), { count: Math.floor(hours / 24) })
    },
    isSoon (purgeAt) {
      return moment(purgeAt).diff(moment(), 'hours') < 24
    },
    restore (row) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('recycle_bin.confirm_restore', { record: this.typeLabel(row.resource_type) + ' ' + row.label }),
        icon: 'warning',
        buttons: true
      }).then(async (confirmed) => {
        if (!confirmed) {
          return
        }
        this.restoringId = row.id
        try {
          await this.restoreRecycleBinEntry(row.id)
          window.toastr['success'](this.$t('recycle_bin.restored_message', { record: this.typeLabel(row.resource_type) + ' ' + row.label }))
          this.$refs.table.refresh()
        } catch (err) {
          const message = err.response && err.response.data && err.response.data.message
          window.toastr['error'](message || this.$t('recycle_bin.restore_failed'))
        } finally {
          this.restoringId = null
        }
      })
    }
  }
}
</script>

<style scoped>
.recycle-bin-note {
  display: flex;
  align-items: center;
  margin: 8px 0 20px;
  padding: 10px 14px;
  color: var(--ui-text);
  background: var(--ui-surface);
  border: 1px solid var(--ui-border);
  border-radius: 6px;
  font-size: 14px;
}
.recycle-bin-type {
  display: inline-block;
  padding: 1px 8px;
  border: 1px solid var(--ui-border-strong);
  border-radius: 10px;
  color: var(--ui-text-muted);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}
.recycle-bin-label {
  margin-top: 4px;
  color: var(--ui-text);
  font-weight: 600;
}
.recycle-bin-soon {
  color: var(--ui-primary);
  font-weight: 600;
}
.recycle-bin-empty-icon {
  font-size: 56px;
  color: var(--ui-text-muted);
}
</style>
