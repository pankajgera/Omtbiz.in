<template>
  <div class="items main-content">
    <div class="page-header">
      <Header :title="$t('dispatch.dashboard_title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <div class="row dispatch-dashboard-cards">
      <div class="col-md-4">
        <router-link to="/dispatch/pending" class="dispatch-dashboard-card-link">
          <div class="card dispatch-dashboard-card dispatch-dashboard-card--pending">
            <div class="card-body">
              <div class="dispatch-dashboard-card-icon">
                <font-awesome-icon :icon="['fas', 'clock']" />
              </div>
              <div class="dispatch-dashboard-card-text">
                <p class="dispatch-dashboard-card-label">{{ $t('dispatch.to_be_dispatched') }}</p>
                <p class="dispatch-dashboard-card-value">{{ counts.pending_count }}</p>
              </div>
              <font-awesome-icon :icon="['fas', 'arrow-right']" class="dispatch-dashboard-card-arrow" />
            </div>
          </div>
        </router-link>
      </div>

      <div class="col-md-4">
        <router-link to="/dispatch/completed" class="dispatch-dashboard-card-link">
          <div class="card dispatch-dashboard-card dispatch-dashboard-card--dispatched">
            <div class="card-body">
              <div class="dispatch-dashboard-card-icon">
                <font-awesome-icon :icon="['fas', 'check-circle']" />
              </div>
              <div class="dispatch-dashboard-card-text">
                <p class="dispatch-dashboard-card-label">{{ $t('dispatch.dispatched') }}</p>
                <p class="dispatch-dashboard-card-value">{{ counts.dispatched_count }}</p>
              </div>
              <font-awesome-icon :icon="['fas', 'arrow-right']" class="dispatch-dashboard-card-arrow" />
            </div>
          </div>
        </router-link>
      </div>

      <div class="col-md-4">
        <div class="card dispatch-dashboard-card dispatch-dashboard-card--total">
          <div class="card-body">
            <div class="dispatch-dashboard-card-icon">
              <font-awesome-icon :icon="['fas', 'file-alt']" />
            </div>
            <div class="dispatch-dashboard-card-text">
              <p class="dispatch-dashboard-card-label">{{ $t('dispatch.total_dispatches') }}</p>
              <p class="dispatch-dashboard-card-value">{{ counts.total_count }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row dispatch-dashboard-panels">
      <div class="col-md-6">
        <div class="card dispatch-panel-card">
          <div class="card-header">
            <h3>{{ $t('dispatch.top_parties_pending') }}</h3>
          </div>
          <div class="card-body">
            <ul v-if="topPendingParties.length" class="dispatch-party-list">
              <li v-for="party in topPendingParties" :key="party.account_master_id" class="dispatch-party-item">
                <div class="dispatch-party-row">
                  <span class="dispatch-party-name">{{ party.name || $t('dispatch.unknown_party') }}</span>
                  <span class="dispatch-party-count">{{ party.pending_count }}</span>
                </div>
                <div class="dispatch-party-bar-track">
                  <div class="dispatch-party-bar-fill" :style="{ width: partyBarWidth(party.pending_count) + '%' }" />
                </div>
              </li>
            </ul>
            <p v-else class="dispatch-activity-empty">{{ $t('dispatch.no_pending_backlog') }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card dispatch-panel-card">
          <div class="card-header">
            <h3>{{ $t('dispatch.pending_aging_title') }}</h3>
          </div>
          <div class="card-body">
            <ul v-if="pendingAging.length" class="dispatch-party-list">
              <li v-for="bucket in pendingAging" :key="bucket.key" class="dispatch-party-item">
                <div class="dispatch-party-row">
                  <span class="dispatch-party-name">{{ bucket.label }}</span>
                  <span class="dispatch-party-count">{{ bucket.count }}</span>
                </div>
                <div class="dispatch-party-bar-track">
                  <div
                    class="dispatch-party-bar-fill"
                    :class="`dispatch-party-bar-fill--${bucket.key}`"
                    :style="{ width: agingBarWidth(bucket.count) + '%' }"
                  />
                </div>
              </li>
            </ul>
            <p v-else class="dispatch-activity-empty">{{ $t('dispatch.no_pending_backlog') }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row dispatch-dashboard-panels">
      <div class="col-md-12">
        <div class="card dispatch-panel-card">
          <div class="card-header">
            <h3>{{ $t('dispatch.recent_activity') }}</h3>
            <router-link to="/dispatch/pending" class="dispatch-view-all-link">
              {{ $t('dispatch.view_all') }}
              <font-awesome-icon :icon="['fas', 'arrow-right']" />
            </router-link>
          </div>
          <div class="card-body">
            <div class="dispatch-activity-section">
              <h6 class="dispatch-activity-section-title">
                <font-awesome-icon :icon="['fas', 'clock']" class="dispatch-activity-section-icon dispatch-activity-section-icon--pending" />
                {{ $t('dispatch.recently_added_pending') }}
              </h6>
              <ul v-if="recentPending.length" class="dispatch-activity-list">
                <router-link
                  v-for="row in recentPending"
                  :key="'pending-' + row.id"
                  :to="{ path: `/dispatch/${row.id}/edit` }"
                  tag="li"
                  class="dispatch-activity-item"
                >
                  <span class="dispatch-activity-party">{{ row.master ? row.master.name : $t('dispatch.unknown_party') }}</span>
                  <span class="dispatch-activity-invoices">{{ invoiceSummary(row) }}</span>
                  <span class="dispatch-activity-meta">{{ formatDate(row.created_at) }}</span>
                </router-link>
              </ul>
              <p v-else class="dispatch-activity-empty">{{ $t('dispatch.no_recent_pending') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'

export default {
  data () {
    return {
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home'),
        },
        {
          url: '#',
          title: this.$t('dispatch.dashboard_title'),
        },
      ],
    }
  },
  computed: {
    ...mapGetters('dispatch', ['dashboardCounts']),
    counts () {
      return this.dashboardCounts
    },
    recentPending () {
      return this.counts.recent_pending || []
    },
    topPendingParties () {
      return this.counts.top_pending_parties || []
    },
    pendingAging () {
      return this.counts.pending_aging || []
    },
  },
  created () {
    this.fetchDispatchDashboard()
  },
  methods: {
    ...mapActions('dispatch', ['fetchDispatchDashboard']),
    formatDate (value) {
      return value ? moment(value).format('DD MMM YYYY') : ''
    },
    invoiceSummary (row) {
      if (!row.invoices || !row.invoices.length) {
        return ''
      }
      if (row.invoices.length === 1) {
        return row.invoices[0].invoice_number
      }
      return `${row.invoices[0].invoice_number} +${row.invoices.length - 1} more`
    },
    partyBarWidth (count) {
      const max = Math.max(...this.topPendingParties.map((p) => p.pending_count), 1)
      return Math.max(Math.round((count / max) * 100), 6)
    },
    agingBarWidth (count) {
      const max = Math.max(...this.pendingAging.map((b) => b.count), 1)
      return Math.max(Math.round((count / max) * 100), 6)
    },
  },
}
</script>

<style scoped>
.dispatch-dashboard-cards {
  margin-top: 30px;
}
.dispatch-dashboard-card-link {
  display: block;
  color: inherit;
  text-decoration: none;
  cursor: pointer;
}
/* Override the global a:hover/:focus rule (resources/assets/sass/base.scss)
   which adds a border + elliptical border-radius to every link on hover -
   it renders as a distorted blob on a block-level card like this one. */
.dispatch-dashboard-card-link:hover,
.dispatch-dashboard-card-link:focus {
  padding: 0 !important;
  border: none !important;
  border-radius: 0 !important;
  color: inherit !important;
}
.dispatch-dashboard-card {
  border-radius: 10px;
  border-top: 3px solid #55547a;
}
.dispatch-dashboard-card .card-body {
  display: flex;
  align-items: center;
  padding: 24px;
}
.dispatch-dashboard-card--dispatched {
  border-top-color: #29c76f;
}
.dispatch-dashboard-card--total {
  border-top-color: #6a94f0;
}
.dispatch-dashboard-card-icon {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  margin-right: 18px;
  border-radius: 50%;
  background: rgba(85, 84, 122, 0.1);
  color: #55547a;
  font-size: 20px;
}
.dispatch-dashboard-card--dispatched .dispatch-dashboard-card-icon {
  background: rgba(41, 199, 111, 0.12);
  color: #29c76f;
}
.dispatch-dashboard-card--total .dispatch-dashboard-card-icon {
  background: rgba(106, 148, 240, 0.12);
  color: #6a94f0;
}
.dispatch-dashboard-card-text {
  flex: 1 1 auto;
  min-width: 0;
}
.dispatch-dashboard-card-label {
  margin-bottom: 2px;
  color: #74767f;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}
.dispatch-dashboard-card-value {
  margin-bottom: 0;
  font-size: 30px;
  font-weight: 700;
  color: #2b2b40;
}
.dispatch-dashboard-card-arrow {
  flex: 0 0 auto;
  color: #c4c4d0;
  font-size: 14px;
}

/* Recent activity / top parties panels */
.dispatch-dashboard-panels {
  margin-top: 24px;
}
.dispatch-panel-card {
  height: 100%;
}
.dispatch-panel-card .card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.dispatch-panel-card .card-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
}
.dispatch-view-all-link,
.dispatch-view-all-link:hover,
.dispatch-view-all-link:focus {
  flex: 0 0 auto;
  margin-left: auto;
  padding: 0 !important;
  border: none !important;
  border-radius: 0 !important;
  font-size: 13px;
  font-weight: 600;
  color: #55547a !important;
  text-decoration: none !important;
  white-space: nowrap;
}
.dispatch-view-all-link svg {
  margin-left: 4px;
  font-size: 11px;
}
.dispatch-activity-section + .dispatch-activity-section {
  margin-top: 26px;
}
.dispatch-activity-section-title {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  color: #74767f;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}
.dispatch-activity-section-icon {
  margin-right: 8px;
  font-size: 13px;
}
.dispatch-activity-section-icon--pending {
  color: #55547a;
}
.dispatch-activity-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.dispatch-activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 8px;
  border-radius: 6px;
  cursor: pointer;
  color: inherit;
  text-decoration: none;
}
.dispatch-activity-item:hover {
  background: #f5f6fa;
}
.dispatch-activity-item + .dispatch-activity-item {
  border-top: 1px solid #f0f0f4;
}
.dispatch-activity-party {
  flex: 0 0 auto;
  max-width: 40%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 600;
  color: #2b2b40;
}
.dispatch-activity-invoices {
  flex: 1 1 auto;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #74767f;
  font-size: 13px;
}
.dispatch-activity-meta {
  flex: 0 0 auto;
  color: #a4a6b3;
  font-size: 12px;
  white-space: nowrap;
}
.dispatch-activity-empty {
  margin: 0;
  color: #a4a6b3;
  font-size: 13px;
}

/* Top parties list */
.dispatch-party-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.dispatch-party-item + .dispatch-party-item {
  margin-top: 16px;
}
.dispatch-party-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 6px;
}
.dispatch-party-name {
  font-weight: 600;
  color: #2b2b40;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-right: 12px;
}
.dispatch-party-count {
  flex: 0 0 auto;
  color: #74767f;
  font-size: 13px;
  font-weight: 700;
}
.dispatch-party-bar-track {
  height: 6px;
  border-radius: 3px;
  background: #f0f0f4;
  overflow: hidden;
}
.dispatch-party-bar-fill {
  height: 100%;
  border-radius: 3px;
  background: linear-gradient(90deg, #55547a, #6a94f0);
}
/* Aging buckets - green (fresh) through amber to red (stale), so severity
   reads at a glance instead of needing to compare numbers. */
.dispatch-party-bar-fill--recent {
  background: #29c76f;
}
.dispatch-party-bar-fill--month {
  background: #f0ad4e;
}
.dispatch-party-bar-fill--old {
  background: #e0554f;
}
</style>
