<template>
  <div class="invoice-index-page invoices main-content">
    <div class="page-header">
      <Header :title="$t('invoices.deleted_title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4" />
      <div class="row" align="center">
        <label class="col title">{{ $t('invoices.no_deleted_invoices') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('invoices.list_of_deleted_invoices') }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ invoices.length }}</b> {{ $t('general.of') }} <b>{{ filtered_count }}</b></p>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table"
      >
        <table-column
          :label="$t('invoices.number')"
          show="invoice_number"
        />
        <table-column
          :sortable="false"
          :filterable="false"
          cell-class="no-click"
        >
          <template slot-scope="row">
            <base-button
              size="small"
              color="theme"
              @click="restoreInvoice(row.id)"
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
      invoices: [],
      isRequestOngoing: true,
      filtered_count: 0,
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home')
        },
        {
          url: '#',
          title: this.$t('invoices.deleted_title')
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
      const response = await window.axios.get('/api/invoices/deleted', { params })
      this.isRequestOngoing = false

      this.invoices = response.data.invoices.data
      this.filtered_count = response.data.invoices.total

      return {
        data: response.data.invoices.data,
        pagination: {
          totalPages: response.data.invoices.last_page,
          currentPage: response.data.invoices.current_page,
          count: response.data.invoices.count
        }
      }
    },
    async restoreInvoice (id) {
      const confirmed = await swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('invoices.restore_confirm'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      })

      if (!confirmed) {
        return
      }

      const response = await window.axios.post(`/api/invoices/${id}/restore`)
      if (response.data && response.data.success) {
        window.toastr['success'](this.$t('invoices.restored_message'))
        this.$refs.table.refresh()
        return
      }

      window.toastr['error'](this.$t('general.action_failed'))
    }
  }
}
</script>
