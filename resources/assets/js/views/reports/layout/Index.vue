<template>
  <div class="profit-loss-reports reports main-content">
    <div class="page-header">
      <h3 class="page-title"> {{ $tc('reports.report', 2) }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="/">
            {{ $t('general.home') }}
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="/reports/customers">
            {{ $tc('reports.report', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2">
          <base-button icon="download" size="large" color="theme" @click="onDownload()">
            {{ $t('reports.download_pdf') }}
          </base-button>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <!-- Tabs -->
        <ul class="tabs">
          <li class="tab">
            <router-link class="tab-link" to="/reports/customers">{{ $t('reports.customers.customers') }}</router-link>
          </li>
          <!-- <li class="tab">
            <router-link class="tab-link" to="/reports/banks">{{ $t('reports.banks.banks') }}</router-link>
          </li> -->
        </ul>
      </div>
    </div>
    <transition
      name="fade"
      mode="out-in">
      <router-view ref="report"/>
    </transition>
  </div>
</template>

<script>
export default {
  watch: {
    '$route.path' (newValue) {
      if (newValue === '/reports') {
        this.$router.push('/reports/customers')
      }
    }
  },
  created () {
    if (this.$route.path === '/reports') {
      this.$router.push('/reports/customers')
    }
  },
  methods: {
    onDownload () {
      if (this.$refs.report && this.$refs.report.$children[0] && this.$refs.report.$children[0].$el.children[1].innerText) {
        this.$refs.report.downloadReport()
      } else {
        swal({
          title: 'Missing ledger',
          text: 'Please select a ledger first',
          icon: '/assets/icon/times-circle-solid.svg',
          buttons: true,
          dangerMode: false
        })
      }
    }
  }

}
</script>

<style scoped>
.tab {
  padding: 0 !important;
}

.tab-link {
  padding: 10px 30px;
  display: block
}
</style>
