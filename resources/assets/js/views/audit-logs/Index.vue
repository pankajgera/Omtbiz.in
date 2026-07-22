<template>
  <div class="audit-logs main-content">
    <div class="page-header">
      <Header :title="$t('audit_logs.title')" :bread-crumb-links="breadCrumbLinks">
        <div class="mr-4 mb-3 mb-sm-0">
          <base-button
            v-show="totalAuditLogs || filtersApplied"
            :outline="true"
            :icon="filterIcon"
            size="large"
            color="theme"
            right-icon
            @click="toggleFilter"
          >
            {{ $t('general.filter') }}
          </base-button>
        </div>
      </Header>
    </div>

    <transition name="fade">
      <div v-show="showFilters" class="filter-section">
        <div class="row">
          <div class="col-sm-3">
            <label class="form-label">{{ $t('audit_logs.user') }}</label>
            <base-input
              v-model="filters.user"
              type="text"
              name="user"
              autocomplete="off"
              :placeholder="$t('audit_logs.user_placeholder')"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label">{{ $t('audit_logs.action') }}</label>
            <base-select
              v-model="filters.action"
              :options="actionOptions"
              :searchable="true"
              :show-labels="false"
              :allow-empty="true"
              :placeholder="$t('audit_logs.select_action')"
              label="label"
              track-by="value"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label">{{ $t('audit_logs.module') }}</label>
            <base-select
              v-model="filters.module"
              :options="moduleOptions"
              :searchable="true"
              :show-labels="false"
              :allow-empty="true"
              :placeholder="$t('audit_logs.select_module')"
              label="label"
              track-by="value"
            />
          </div>
          <div class="col-sm-3">
            <label class="form-label">{{ $t('general.from_date') }}</label>
            <base-input
              v-model="filters.from_date"
              type="date"
              name="from_date"
            />
          </div>
          <div class="col-sm-3 mt-2">
            <label class="form-label">{{ $t('general.to_date') }}</label>
            <base-input
              v-model="filters.to_date"
              type="date"
              name="to_date"
            />
          </div>
          <label class="clear-filter" @click="clearFilter">{{ $t('general.clear_all') }}</label>
        </div>
      </div>
    </transition>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <astronaut-icon class="mt-5 mb-4"/>
      <div class="row" align="center">
        <label class="col title">{{ $t('audit_logs.no_logs') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('audit_logs.list_of_logs') }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">
          {{ $t('general.showing') }}: <b>{{ pageStart }}–{{ pageEnd }}</b>
          {{ $t('general.of') }} <b>{{ totalAuditLogs }}</b>
        </p>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table"
      >
        <table-column
          :label="$t('audit_logs.date_time')"
          show="formatted_created_at"
        />
        <table-column
          :label="$t('audit_logs.user')"
          show="user_name"
        >
          <template slot-scope="row">
            <div>{{ row.user_name || '—' }}</div>
            <small class="text-muted">{{ row.user_email }}</small>
          </template>
        </table-column>
        <table-column
          :label="$t('audit_logs.action')"
          show="action_label"
        >
          <template slot-scope="row">
            <span>{{ $t('audit_logs.action') }}</span>
            <div :class="actionBadgeClass(row.action)">{{ row.action_label || row.action }}</div>
          </template>
        </table-column>
        <table-column
          :label="$t('audit_logs.module')"
          show="module"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          :label="$t('audit_logs.description')"
          show="description"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          :label="$t('audit_logs.ip')"
          show="ip_address"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown"
        >
          <template slot-scope="row">
            <span>{{ $t('audit_logs.action') }}</span>
            <v-dropdown>
              <span slot="activator" href="#">
                <dot-icon />
              </span>
              <v-dropdown-item>
                <div class="dropdown-item" @click="showDetails(row)">
                  <font-awesome-icon :icon="['fas', 'eye']" class="dropdown-item-icon"/>
                  {{ $t('audit_logs.view_details') }}
                </div>
              </v-dropdown-item>
            </v-dropdown>
          </template>
        </table-column>
      </table-component>
    </div>

    <div v-if="selectedLog" class="modal" style="display:block;" @click.self="selectedLog = null">
      <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('audit_logs.details') }}</h5>
            <button type="button" class="close" @click="selectedLog = null">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p><strong>{{ $t('audit_logs.description') }}:</strong> {{ selectedLog.description }}</p>
            <p><strong>{{ $t('audit_logs.user') }}:</strong> {{ selectedLog.user_name }} ({{ selectedLog.user_email }})</p>
            <p><strong>{{ $t('audit_logs.action') }}:</strong> {{ selectedLog.action_label }}</p>
            <p><strong>{{ $t('audit_logs.module') }}:</strong> {{ selectedLog.module }}</p>
            <p><strong>{{ $t('audit_logs.ip') }}:</strong> {{ selectedLog.ip_address || '—' }}</p>
            <p><strong>{{ $t('audit_logs.url') }}:</strong> {{ selectedLog.url || '—' }}</p>
            <div v-if="selectedLog.old_values" class="mt-3">
              <strong>{{ $t('audit_logs.old_values') }}</strong>
              <pre class="audit-json">{{ formatJson(selectedLog.old_values) }}</pre>
            </div>
            <div v-if="selectedLog.new_values" class="mt-3">
              <strong>{{ $t('audit_logs.new_values') }}</strong>
              <pre class="audit-json">{{ formatJson(selectedLog.new_values) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.audit-json {
  background: #f7f7f7;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  max-height: 240px;
  overflow: auto;
  font-size: 12px;
}
.badge-created { color: #1b7a3d; font-weight: 600; }
.badge-updated { color: #b36b00; font-weight: 600; }
.badge-deleted { color: #b00020; font-weight: 600; }
.badge-login { color: #0b5ed7; font-weight: 600; }
.badge-logout { color: #6c757d; font-weight: 600; }
.badge-failed { color: #b00020; font-weight: 600; }
</style>

<style>
/* Unscoped so it wins over Bootstrap .table thead th border-bottom */
.audit-logs .table-actions {
  height: auto;
  min-height: 40px;
  margin-bottom: 16px;
  padding-bottom: 12px;
}
.audit-logs .table-container .table-component {
  margin-top: 8px;
}
.audit-logs .table.table-component__table thead th,
.audit-logs .table-component__table thead th,
.audit-logs table.table thead th {
  border: none !important;
  border-bottom: none !important;
  border-top: none !important;
  vertical-align: middle !important;
  padding-top: 8px !important;
  padding-bottom: 20px !important;
  background: transparent !important;
  box-shadow: none !important;
  line-height: 1.4 !important;
}
.audit-logs .table-component__table {
  border-spacing: 0 12px !important;
}
</style>

<script>
import { mapActions, mapGetters } from 'vuex'
import DotIcon from '../../components/icon/DotIcon'
import AstronautIcon from '../../components/icon/AstronautIcon'
import BaseButton from '../../../js/components/base/BaseButton'

export default {
  components: {
    DotIcon,
    AstronautIcon,
    BaseButton
  },
  data () {
    const today = this.getTodayDate()
    return {
      showFilters: false,
      filtersApplied: false,
      isRequestOngoing: true,
      selectedLog: null,
      currentPage: 1,
      perPage: 15,
      filters: {
        user: '',
        action: '',
        module: '',
        from_date: today,
        to_date: today
      },
      actionOptions: [
        { label: 'Login', value: 'login' },
        { label: 'Logout', value: 'logout' },
        { label: 'Login Failed', value: 'login_failed' },
        { label: 'Created', value: 'created' },
        { label: 'Updated', value: 'updated' },
        { label: 'Deleted', value: 'deleted' }
      ],
      moduleOptions: [
        { label: 'Auth', value: 'auth' },
        { label: 'User', value: 'user' },
        { label: 'Invoice', value: 'invoice' },
        { label: 'Order', value: 'order' },
        { label: 'Estimate', value: 'estimate' },
        { label: 'Inventory', value: 'inventory' },
        { label: 'Voucher', value: 'voucher' },
        { label: 'Receipt', value: 'receipt' },
        { label: 'Payment', value: 'payment' },
        { label: 'Bill-ty', value: 'item' },
        { label: 'Dispatch', value: 'dispatch' },
        { label: 'Note', value: 'note' },
        { label: 'Account Master', value: 'master' },
        { label: 'Ledger', value: 'ledger' },
        { label: 'Group', value: 'group' },
        { label: 'Expense', value: 'expense' },
        { label: 'Bank', value: 'bank' },
        { label: 'Company', value: 'company' }
      ],
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home')
        },
        {
          url: '#',
          title: this.$t('audit_logs.title')
        }
      ]
    }
  },
  computed: {
    ...mapGetters('auditLogs', [
      'auditLogs',
      'totalAuditLogs'
    ]),
    showEmptyScreen () {
      return !this.totalAuditLogs && !this.isRequestOngoing && !this.hasCustomFilters
    },
    hasCustomFilters () {
      const today = this.getTodayDate()
      return !!(
        this.filters.user ||
        this.filters.action ||
        this.filters.module ||
        this.filters.from_date !== today ||
        this.filters.to_date !== today
      )
    },
    filterIcon () {
      return this.showFilters ? 'angle-up' : 'angle-down'
    },
    pageStart () {
      if (!this.totalAuditLogs) {
        return 0
      }
      return ((this.currentPage - 1) * this.perPage) + 1
    },
    pageEnd () {
      return Math.min(this.currentPage * this.perPage, this.totalAuditLogs)
    }
  },
  watch: {
    filters: {
      handler () {
        this.$refs.table && this.$refs.table.refresh()
      },
      deep: true
    }
  },
  methods: {
    ...mapActions('auditLogs', [
      'fetchAuditLogs'
    ]),
    getTodayDate () {
      const d = new Date()
      const month = `${d.getMonth() + 1}`.padStart(2, '0')
      const day = `${d.getDate()}`.padStart(2, '0')
      return `${d.getFullYear()}-${month}-${day}`
    },
    toggleFilter () {
      this.showFilters = !this.showFilters
    },
    clearFilter () {
      const today = this.getTodayDate()
      this.filters = {
        user: '',
        action: '',
        module: '',
        from_date: today,
        to_date: today
      }
      this.filtersApplied = false
      this.$refs.table && this.$refs.table.refresh()
    },
    getFilterParams () {
      const action = this.filters.action && this.filters.action.value
        ? this.filters.action.value
        : (this.filters.action || '')
      const module = this.filters.module && this.filters.module.value
        ? this.filters.module.value
        : (this.filters.module || '')

      return {
        user: this.filters.user,
        action,
        module,
        from_date: this.filters.from_date,
        to_date: this.filters.to_date
      }
    },
    async fetchData ({ page, filter, sort }) {
      const params = {
        ...this.getFilterParams(),
        page,
        limit: this.perPage,
        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc'
      }

      this.filtersApplied = this.hasCustomFilters
      this.isRequestOngoing = true
      this.currentPage = page || 1

      try {
        const response = await this.fetchAuditLogs(params)
        this.isRequestOngoing = false
        return {
          data: response.data.audit_logs.data,
          pagination: {
            totalPages: response.data.audit_logs.last_page,
            currentPage: response.data.audit_logs.current_page || page,
            count: response.data.audit_logs.total
          }
        }
      } catch (e) {
        this.isRequestOngoing = false
        return {
          data: [],
          pagination: {
            totalPages: 1,
            currentPage: 1,
            count: 0
          }
        }
      }
    },
    showDetails (row) {
      this.selectedLog = row
    },
    formatJson (value) {
      try {
        return JSON.stringify(value, null, 2)
      } catch (e) {
        return String(value)
      }
    },
    actionBadgeClass (action) {
      if (action === 'created') return 'badge-created'
      if (action === 'updated') return 'badge-updated'
      if (action === 'deleted') return 'badge-deleted'
      if (action === 'login') return 'badge-login'
      if (action === 'logout') return 'badge-logout'
      if (action === 'login_failed') return 'badge-failed'
      return ''
    }
  }
}
</script>
