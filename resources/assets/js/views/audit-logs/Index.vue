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

    <div class="search-bar row">
      <div class="col-sm-4">
        <base-input
          v-model="searchInput"
          type="text"
          name="search"
          icon="search"
          autocomplete="off"
          :placeholder="$t('audit_logs.search_placeholder')"
          @input="handleSearchInput"
        />
      </div>
      <div class="col-sm-4">
        <base-select
          v-model="filters.type"
          :options="typeOptions"
          :searchable="false"
          :show-labels="false"
          :allow-empty="false"
          label="label"
          track-by="value"
        />
      </div>
      <div class="col-sm-4">
        <base-select
          v-model="dateRangeOption"
          :options="dateRangeOptions"
          :searchable="false"
          :show-labels="false"
          :allow-empty="false"
          label="label"
          track-by="value"
        />
      </div>
    </div>

    <div v-if="activeFilterChips.length" class="filter-chips">
      <span v-for="chip in activeFilterChips" :key="chip.key" class="filter-chip">
        {{ chip.label }}
        <a href="#" class="filter-chip-remove" @click.prevent="clearFilterKey(chip.key)">×</a>
      </span>
      <a href="#" class="clear-all-chip" @click.prevent="clearFilter">{{ $t('general.clear_all') }}</a>
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
            <label class="form-label">{{ $t('general.from_date') }}</label>
            <base-input
              v-model="filters.from_date"
              type="date"
              name="from_date"
            />
          </div>
          <div class="col-sm-3">
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
        >
          <template slot-scope="row">
            <div>{{ row.formatted_created_at }}</div>
            <small class="text-muted">{{ relativeTime(row.created_at) }}</small>
          </template>
        </table-column>
        <table-column
          :label="$t('audit_logs.document')"
          show="module"
        >
          <template slot-scope="row">
            <router-link v-if="row.document_path" :to="{ path: row.document_path }" class="document-link">
              <span class="document-type">{{ row.module }}</span>
              <span class="document-number">{{ row.document_number || ('#' + row.auditable_id) }}</span>
            </router-link>
            <div v-else>
              <span class="document-type">{{ row.module }}</span>
              <span v-if="row.document_number" class="document-number">{{ row.document_number }}</span>
            </div>
          </template>
        </table-column>
        <table-column
          :label="$t('audit_logs.action')"
          show="action_label"
        >
          <template slot-scope="row">
            <div :class="actionBadgeClass(row.action)">{{ row.action_label || row.action }}</div>
          </template>
        </table-column>
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
          :sortable="false"
          :filterable="false"
          :label="$t('audit_logs.description')"
          show="description"
        >
          <template slot-scope="row">
            <div>{{ row.description }}</div>
            <ul v-if="row.change_summary && row.change_summary.length" class="change-summary">
              <li v-for="(change, idx) in visibleChanges(row)" :key="idx">
                <b>{{ change.field }}:</b> {{ change.old }} → {{ change.new }}
              </li>
            </ul>
            <a
              v-if="row.change_summary && row.change_summary.length > changeSummaryLimit"
              href="#"
              class="change-toggle"
              @click.prevent="toggleChanges(row.id)"
            >
              {{ expandedChanges[row.id]
                ? $t('audit_logs.show_less')
                : $t('audit_logs.show_more', { count: row.change_summary.length - changeSummaryLimit }) }}
            </a>
          </template>
        </table-column>
      </table-component>
    </div>
  </div>
</template>

<style scoped>
.badge-created { color: #1b7a3d; font-weight: 600; }
.badge-updated { color: #b36b00; font-weight: 600; }
.badge-deleted { color: #b00020; font-weight: 600; }
.badge-login { color: #0b5ed7; font-weight: 600; }
.badge-logout { color: #6c757d; font-weight: 600; }
.badge-failed { color: #b00020; font-weight: 600; }

.search-bar {
  margin-bottom: 12px;
  align-items: flex-start;
}

.filter-chips {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  background: #eef1f7;
  border-radius: 14px;
  padding: 4px 10px;
  font-size: 12px;
  color: #40495c;
}

.filter-chip-remove {
  margin-left: 6px;
  color: #6c757d;
  text-decoration: none;
  font-weight: 700;
}

.clear-all-chip {
  font-size: 12px;
  text-decoration: underline;
}

.document-link {
  display: flex;
  flex-direction: column;
  text-decoration: none;
}

.change-summary {
  list-style: none;
  padding: 0;
  margin: 4px 0 0;
}

.change-summary li {
  font-size: 12px;
  color: #6c757d;
}

.change-toggle {
  display: inline-block;
  margin-top: 2px;
  font-size: 12px;
}

.document-type {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.document-number {
  font-weight: 600;
}

.document-link .document-number {
  color: #2a5bd7;
}

</style>

<style>
/* Unscoped so it wins over Bootstrap .table thead th border-bottom */
.audit-logs .table-actions {
  height: auto;
  min-height: 40px;
  margin-bottom: 16px;
  padding-bottom: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
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
import moment from 'moment'
import AstronautIcon from '../../components/icon/AstronautIcon'
import BaseButton from '../../../js/components/base/BaseButton'

export default {
  components: {
    AstronautIcon,
    BaseButton
  },
  data () {
    const typeOptions = [
      { label: this.$t('audit_logs.all_types'), value: '' },
      { label: this.$t('navigation.invoices'), value: 'invoice' },
      { label: this.$t('navigation.estimates'), value: 'estimate' },
      { label: this.$t('navigation.orders'), value: 'order' },
      { label: this.$t('navigation.inventory'), value: 'inventory' },
      { label: this.$t('navigation.voucher'), value: 'voucher' },
      { label: this.$t('navigation.receipts'), value: 'receipt' },
      { label: this.$t('audit_logs.login_logout'), value: 'auth' }
    ]
    // Default to Invoice only — a full "All Types" load pulls in every
    // module and is exactly the noisy view this page was built to avoid.
    const defaultTypeValue = 'invoice'
    const defaultType = typeOptions.find(o => o.value === defaultTypeValue)

    return {
      showFilters: false,
      filtersApplied: false,
      isRequestOngoing: true,
      currentPage: 1,
      perPage: 15,
      searchInput: '',
      searchDebounce: null,
      changeSummaryLimit: 3,
      expandedChanges: {},
      defaultTypeValue,
      filters: {
        search: '',
        type: defaultType,
        user: '',
        from_date: '',
        to_date: ''
      },
      typeOptions,
      dateRangeOption: { label: this.$t('audit_logs.all_time'), value: '' },
      dateRangeOptions: [
        { label: this.$t('audit_logs.all_time'), value: '' },
        { label: this.$t('audit_logs.today'), value: 'today' },
        { label: this.$t('audit_logs.this_week'), value: 'week' },
        { label: this.$t('audit_logs.this_month'), value: 'month' }
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
      const typeValue = this.filters.type && this.filters.type.value
      return !!(
        this.filters.search ||
        (typeValue && typeValue !== this.defaultTypeValue) ||
        this.filters.user ||
        this.filters.from_date ||
        this.filters.to_date
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
    },
    activeFilterChips () {
      const chips = []

      if (this.filters.search) {
        chips.push({ key: 'search', label: `"${this.filters.search}"` })
      }

      const typeValue = this.filters.type && this.filters.type.value
      if (typeValue && typeValue !== this.defaultTypeValue) {
        const option = this.typeOptions.find(o => o.value === typeValue)
        chips.push({ key: 'type', label: option ? option.label : typeValue })
      }

      if (this.filters.user) {
        chips.push({ key: 'user', label: `${this.$t('audit_logs.user')}: ${this.filters.user}` })
      }

      if (this.filters.from_date) {
        chips.push({ key: 'from_date', label: `${this.$t('general.from_date')}: ${this.filters.from_date}` })
      }

      if (this.filters.to_date) {
        chips.push({ key: 'to_date', label: `${this.$t('general.to_date')}: ${this.filters.to_date}` })
      }

      return chips
    }
  },
  watch: {
    filters: {
      handler () {
        this.$refs.table && this.$refs.table.refresh()
      },
      deep: true
    },
    dateRangeOption (option) {
      const value = option && option.value
      if (!value) {
        this.filters.from_date = ''
        this.filters.to_date = ''
      } else {
        this.setQuickRange(value)
      }
    }
  },
  methods: {
    ...mapActions('auditLogs', [
      'fetchAuditLogs'
    ]),
    toggleFilter () {
      this.showFilters = !this.showFilters
    },
    handleSearchInput (value) {
      clearTimeout(this.searchDebounce)
      this.searchDebounce = setTimeout(() => {
        this.filters.search = value
      }, 300)
    },
    clearFilterKey (key) {
      if (key === 'search') {
        this.searchInput = ''
        this.filters.search = ''
      } else if (key === 'type') {
        this.filters.type = this.defaultTypeOption()
      } else if (key === 'from_date' || key === 'to_date') {
        this.filters.from_date = ''
        this.filters.to_date = ''
        this.dateRangeOption = { label: this.$t('audit_logs.all_time'), value: '' }
      } else {
        this.filters[key] = ''
      }
    },
    clearFilter () {
      this.searchInput = ''
      this.dateRangeOption = { label: this.$t('audit_logs.all_time'), value: '' }
      this.filters = {
        search: '',
        type: this.defaultTypeOption(),
        user: '',
        from_date: '',
        to_date: ''
      }
      this.filtersApplied = false
      this.$refs.table && this.$refs.table.refresh()
    },
    defaultTypeOption () {
      return this.typeOptions.find(o => o.value === this.defaultTypeValue)
    },
    setQuickRange (range) {
      const pad = (n) => String(n).padStart(2, '0')
      const toInputDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
      const now = new Date()
      let from = now

      if (range === 'week') {
        const day = now.getDay() === 0 ? 7 : now.getDay()
        from = new Date(now)
        from.setDate(now.getDate() - day + 1)
      } else if (range === 'month') {
        from = new Date(now.getFullYear(), now.getMonth(), 1)
      }

      this.filters.from_date = toInputDate(from)
      this.filters.to_date = toInputDate(now)
    },
    getFilterParams () {
      const type = (this.filters.type && this.filters.type.value) || ''
      // Default view covers invoices/estimates/orders/inventory/vouchers/
      // receipts (create/update/delete) plus login/logout activity. Other
      // modules (users, banks, etc.) stay out to keep this readable; the
      // Type filter narrows further.
      const module = type || 'invoice,estimate,order,inventory,voucher,receipt,auth'

      return {
        search: this.filters.search,
        module,
        action: 'created,updated,deleted,login,logout,login_failed',
        user: this.filters.user,
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
    actionBadgeClass (action) {
      if (action === 'created') return 'badge-created'
      if (action === 'updated') return 'badge-updated'
      if (action === 'deleted') return 'badge-deleted'
      if (action === 'login') return 'badge-login'
      if (action === 'logout') return 'badge-logout'
      if (action === 'login_failed') return 'badge-failed'
      return ''
    },
    relativeTime (date) {
      return date ? moment(date).fromNow() : ''
    },
    visibleChanges (row) {
      if (this.expandedChanges[row.id] || row.change_summary.length <= this.changeSummaryLimit) {
        return row.change_summary
      }
      return row.change_summary.slice(0, this.changeSummaryLimit)
    },
    toggleChanges (id) {
      this.$set(this.expandedChanges, id, !this.expandedChanges[id])
    }
  }
}
</script>
