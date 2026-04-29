<template>
  <div class="main-content vouchers">
    <div class="page-header">
      <Header :title="$t('vouchers.deleted_title')" :bread-crumb-links="breadCrumbLinks" />
    </div>

    <div v-cloak v-show="showEmptyScreen" class="col-xs-1 no-data-info" align="center">
      <moon-walker-icon class="mt-5 mb-4" />
      <div class="row" align="center">
        <label class="col title">{{ $t('vouchers.no_deleted_vouchers') }}</label>
      </div>
      <div class="row">
        <label class="description col mt-1" align="center">{{ $t('vouchers.list_of_deleted_vouchers') }}</label>
      </div>
    </div>

    <div v-show="!showEmptyScreen" class="table-container">
      <div class="table-actions mt-5">
        <p class="table-stats">{{ $t('general.showing') }}: <b>{{ vouchers.length }}</b> {{ $t('general.of') }} <b>{{ filtered_count }}</b></p>
      </div>

      <table-component
        ref="table"
        :show-filter="false"
        :data="fetchData"
        table-class="table mt-5"
      >
        <table-column
          :label="$t('vouchers.name')"
          width="75%"
          show="account"
        />
        <table-column
          :label="$t('vouchers.action')"
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
              @click="restoreVoucher(row.id)"
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
      vouchers: [],
      isRequestOngoing: true,
      filtered_count: 0,
      breadCrumbLinks: [
        {
          url: 'dashboard',
          title: this.$t('general.home')
        },
        {
          url: '#',
          title: this.$t('vouchers.deleted_title')
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
      const response = await window.axios.get('/api/vouchers/deleted', { params })
      this.isRequestOngoing = false

      this.vouchers = response.data.vouchers.data
      this.filtered_count = response.data.vouchers.total

      return {
        data: response.data.vouchers.data,
        pagination: {
          totalPages: response.data.vouchers.last_page,
          currentPage: response.data.vouchers.current_page,
          count: response.data.vouchers.count
        }
      }
    },
    async restoreVoucher (id) {
      const confirmed = await swal({
        title: this.$t('general.are_you_sure'),
        text: this.$t('vouchers.restore_confirm'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      })

      if (!confirmed) {
        return
      }

      const response = await window.axios.post(`/api/vouchers/${id}/restore`)
      if (response.data && response.data.success) {
        window.toastr['success'](this.$t('vouchers.restored_message'))
        this.$refs.table.refresh()
        return
      }

      window.toastr['error'](this.$t('general.action_failed'))
    }
  }
}
</script>
