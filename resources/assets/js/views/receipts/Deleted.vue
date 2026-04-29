<template>
  <div class="main-content receipts">
    <div class="page-header">
      <Header :title="$t('receipts.deleted_title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4" />
      <div class="row" align="center">
        <label class="col title">{{ $t('receipts.no_deleted_receipts') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('receipts.list_of_deleted_receipts') }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ receipts.length }}</b> {{ $t('general.of') }} <b>{{ filtered_count }}</b></p>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table mt-5"
      >
        <table-column
          :label="$t('receipts.receipt_number')"
          width="75%"
          show="receipt_number"
        />
        <table-column
          :label="$t('receipts.action')"
          :sortable="false"
          :filterable="false"
          cell-class="action-dropdown no-click"
          width="25%"
        >
          <template slot-scope="row">
            <base-button
              size="small"
              :outline="true"
              color="theme"
              @click="restoreReceipt(row.id)"
            >
              {{ $t('general.restore') }}
            </base-button>
          </template>
        </table-column>
      </table-component>
    </div>
  </div>
</template>

<script>
import MoonWalkerIcon from '../../../js/components/icon/MoonwalkerIcon'

export default {
  components: {
    'moon-walker-icon': MoonWalkerIcon
  },
  data () {
    return {
      receipts: [],
      isRequestOngoing: true,
      filtered_count: 0,
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home')
        },
        {
          url: '#',
          title: this.$t('receipts.deleted_title')
        }
      ]
    }
  },
  computed: {
    showEmptyScreen () {
      return !this.filtered_count && !this.isRequestOngoing
    }
  },
  methods: {
    async fetchData ({ page, sort }) {
      const params = {
        page,
        limit: 20,
        orderByField: (sort && sort.fieldName) || 'deleted_at',
        orderBy: (sort && sort.order) || 'desc'
      }

      this.isRequestOngoing = true
      const response = await window.axios.get('/api/receipts/deleted', { params })
      this.isRequestOngoing = false

      this.receipts = response.data.receipts.data
      this.filtered_count = response.data.receipts.total

      return {
        data: response.data.receipts.data,
        pagination: {
          totalPages: response.data.receipts.last_page,
          currentPage: response.data.receipts.current_page,
          count: response.data.receipts.count
        }
      }
    },
    async restoreReceipt (id) {
      const confirmed = await swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('receipts.restore_confirm'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      })

      if (!confirmed) {
        return
      }

      const response = await window.axios.post(`/api/receipts/${id}/restore`)
      if (response.data && response.data.success) {
        window.toastr['success'](this.$t('receipts.restored_message'))
        this.$refs.table.refresh()
        return
      }

      window.toastr['error'](this.$t('general.action_failed'))
    }
  }
}
</script>
