<template>
  <div class="row tw:items-start">
    <div class="col-md-4 reports-tab-container report-filters-container">
      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="report-label">{{ $t('reports.customers.ledgers') }}</label>
          <base-select
            ref="selectedLedger"
            v-model="selectedLedger"
            :options="ledgersArr"
            :custom-label="ledgerLabel"
            :allow-empty="false"
            :show-labels="false"
            track-by="id"
            @input="onLedgerSelected"
          />
          <span v-if="vRange.$error && !vRange.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-8">
          <label class="report-label">{{ $t('reports.customers.date_range') }}</label>
          <base-select
            v-model="selectedRange"
            :options="dateRange"
            :allow-empty="false"
            :show-labels="false"
            @input="onChangeDateRange"
          />
          <span v-if="vRange.$error && !vRange.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
      </div>
      <div class="row report-fields-container">
        <div class="col-md-6 report-field-container" v-if="selectedRange !== 'Till Date'">
          <label class="report-label">{{ $t('reports.customers.from_date') }}</label>
          <base-date-picker
            v-model="formData.from_date"
            :invalid="vFormData.from_date.$error"
            :calendar-button="true"
            calendar-button-icon="calendar"
            @change="onDateChanged('from_date')"
          />
          <span v-if="vFormData.from_date.$error && !vFormData.from_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
        <div class="col-md-6 report-field-container">
          <label class="report-label">{{ selectedRange !== 'Till Date' ? $t('reports.customers.to_date') : $t('reports.customers.till_date') }}</label>
          <base-date-picker
            v-model="formData.to_date"
            :invalid="vFormData.to_date.$error"
            :calendar-button="true"
            calendar-button-icon="calendar"
            @change="onDateChanged('to_date')"
          />
          <span v-if="vFormData.to_date.$error && !vFormData.to_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
      </div>
      <div class="row report-submit-button-container">
        <div class="col-md-12 report-action-buttons">
          <base-button
            :loading="isReportLoading"
            icon="sync-alt"
            outline
            color="theme"
            class="report-button"
            @click="getReports()"
          >
            {{ $t('reports.update_report') }}
          </base-button>
          <base-button
            v-if="getReportUrl && !isReportLoading"
            icon="paper-plane"
            outline
            color="theme"
            class="report-button"
            @click="sendReports()"
          >
            {{ $t('reports.customers.send_on_whatsapp') }}
          </base-button>
        </div>
      </div>
    </div>
    <div class="col-sm-8 reports-tab-container report-preview-container">
      <div v-if="isReportLoading" class="report-preview-empty" role="status" aria-live="polite">
        <font-awesome-icon icon="spinner" class="report-preview-icon fa-spin"/>
        <p>{{ $t('reports.customers.generating_report') }}</p>
      </div>
      <iframe
        v-if="getReportUrl"
        v-show="!isReportLoading"
        :key="reportPreviewKey"
        :src="getReportUrl"
        :title="$t('reports.customers.report_preview')"
        class="reports-frame-style"
        @load="onReportLoaded"
      />
      <div v-else-if="!isReportLoading" class="report-preview-empty" role="status">
        <font-awesome-icon icon="file-pdf" class="report-preview-icon"/>
        <p>
          {{ selectedLedger
            ? $t('reports.customers.update_report_preview')
            : $t('reports.customers.select_ledger_preview')
          }}
        </p>
      </div>
      <a v-if="getReportUrl && !isReportLoading" class="base-button btn btn-primary btn-lg report-view-button" @click="viewReportsPDF">
        <font-awesome-icon icon="file-pdf" class="vue-icon icon-left svg-inline--fa fa-download fa-w-16 mr-2" /> <span>{{ $t('reports.view_pdf') }}</span>
      </a>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { validationMixin } from 'vuelidate'
import { required } from '@vuelidate/validators';
export default {
  mixins: [validationMixin],
  data () {
    return {
      range: new Date(),
      dateRange: [
        'Today',
        'Till Date',
        'This Week',
        'This Month',
        'This Quarter',
        'This Year',
        'Previous Week',
        'Previous Month',
        'Previous Quarter',
        'Previous Year',
        'Custom'
      ],
      selectedRange: 'This Month',
      formData: {
        from_date: moment().startOf('month').toString(),
        to_date: moment().endOf('month').toString()
      },
      url: null,
      siteURL: null,
      ledgersArr: [],
      selectedLedger: null,
      isReportLoading: false,
      reportPreviewKey: 0,
      vouchersListArr: [],
    }
  },
  validations: {
    range: {
      required
    },
    formData: {
      from_date: {
        required
      },
      to_date: {
        required
      }
    }
  },
  computed: {
    vRange () {
      return this.$v?.range || { $error: false, required: true, $touch: () => {} }
    },
    vFormData () {
      return this.$v?.formData || {
        $error: false,
        $invalid: false,
        $touch: () => {},
        from_date: { $error: false, required: true, $touch: () => {} },
        to_date: { $error: false, required: true, $touch: () => {} }
      }
    },
    ...mapGetters('company', [
      'getSelectedCompany'
    ]),
    getReportUrl () {
      return this.url
    }
  },
  created() {
    this.loadLedgers()
  },
  watch: {
    range (newRange) {
      this.formData.from_date = moment(newRange).startOf('year').toString()
      this.formData.to_date = moment(newRange).endOf('year').toString()
    }
  },
  mounted () {
    this.siteURL = `/reports/customers/${this.getSelectedCompany.unique_hash}`
  },
  methods: {
     ...mapActions('customer', [
      'fetchLedgersReport',
      'fetchVouchersReport',
      'sendReportOnWhatsApp'
    ]),
    ledgerLabel (ledger) {
      return `${ledger.account} (Group: ${ledger.account_master.groups})`
    },
    onLedgerSelected (ledger) {
      this.selectedLedger = ledger
      this.invalidateReport()
    },
    getThisDate (type, time) {
      return moment()[type](time).toString()
    },
    getPreDate (type, time) {
      return moment().subtract(1, time)[type](time).toString()
    },
    onChangeDateRange () {
      switch (this.selectedRange) {
        case 'Today':
          this.formData.from_date = moment().toString()
          this.formData.to_date = moment().toString()
          break

        case 'Till Date':
          this.formData.from_date = moment(this.formData.to_date).startOf('month').toString()
          this.formData.to_date = moment(this.formData.to_date).toString()
          break

        case 'This Week':
          this.formData.from_date = this.getThisDate('startOf', 'isoWeek')
          this.formData.to_date = this.getThisDate('endOf', 'isoWeek')
          break

        case 'This Month':
          this.formData.from_date = this.getThisDate('startOf', 'month')
          this.formData.to_date = this.getThisDate('endOf', 'month')
          break

        case 'This Quarter':
          this.formData.from_date = this.getThisDate('startOf', 'quarter')
          this.formData.to_date = this.getThisDate('endOf', 'quarter')
          break

        case 'This Year':
          this.formData.from_date = this.getThisDate('startOf', 'year')
          this.formData.to_date = this.getThisDate('endOf', 'year')
          break

        case 'Previous Week':
          this.formData.from_date = this.getPreDate('startOf', 'isoWeek')
          this.formData.to_date = this.getPreDate('endOf', 'isoWeek')
          break

        case 'Previous Month':
          this.formData.from_date = this.getPreDate('startOf', 'month')
          this.formData.to_date = this.getPreDate('endOf', 'month')
          break

        case 'Previous Quarter':
          this.formData.from_date = this.getPreDate('startOf', 'quarter')
          this.formData.to_date = this.getPreDate('endOf', 'quarter')
          break

        case 'Previous Year':
          this.formData.from_date = this.getPreDate('startOf', 'year')
          this.formData.to_date = this.getPreDate('endOf', 'year')
          break

        default:
          break
      }
      this.invalidateReport()
    },
    onDateChanged (field) {
      this.vFormData[field].$touch()
      this.setRangeToCustom()
      this.invalidateReport()
    },
    setRangeToCustom () {
      this.selectedRange = 'Custom'
    },
    invalidateReport () {
      this.url = null
      this.isReportLoading = false
    },
    onReportLoaded () {
      this.isReportLoading = false
    },
    viewReportsPDF () {
      if (!this.getReportUrl) {
        return false
      }
      window.open(this.getReportUrl, '_blank')
      return true
    },
    prepareReportUrl () {
      this.vRange.$touch()
      this.vFormData.$touch()
      if (this.selectedRange === 'Till Date') {
        this.formData.from_date = moment(this.formData.to_date).startOf('month').toString()
        this.formData.to_date = moment(this.formData.to_date).toString()
      }
      if (this.$v?.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return false
      }

      if (!this.selectedLedger) {
        window.toastr['error'](this.$t('reports.customers.select_ledger_preview'))
        return false
      }

      return `${this.siteURL}?from_date=${moment(this.formData.from_date).format('DD/MM/YYYY')}&to_date=${moment(this.formData.to_date).format('DD/MM/YYYY')}&ledger_id=${this.selectedLedger.id}`
    },
    getReports () {
      const reportUrl = this.prepareReportUrl()
      if (!reportUrl) {
        this.isReportLoading = false
        return false
      }

      this.isReportLoading = true
      this.reportPreviewKey += 1
      this.url = reportUrl
      return true
    },
    downloadReport () {
      const reportUrl = this.prepareReportUrl()
      if (!reportUrl) {
        return false
      }
      window.open(reportUrl + '&download=true')
      return true
    },
    async loadLedgers () {
      let response = await this.fetchLedgersReport()
      this.ledgersArr = response.data.ledgers
    },
    sendReports() {
      let mobile = this.selectedLedger.account_master.mobile_number
      if (!mobile) {
        window.toastr['error']("Sorry, didn't find mobile number for selected ledger.")
        return
      }
      let fileName = moment(this.formData.from_date).format('DD/MM/YYYY') + '-' + moment(this.formData.to_date).format('DD/MM/YYYY');
      this.sendReportOnWhatsApp({ fileName: fileName, number: mobile, filePath: window.location.origin + this.url})
      // window.open("https://api.whatsapp.com/send/?phone=" +'+91'+ mobile + "&text=" + encodeURIComponent("http://omtbiz.in" + this.url))
    }
  }
}
</script>
