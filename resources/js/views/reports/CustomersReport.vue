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
            v-if="getReportUrl && !isReportLoading && reportInfo && reportInfo.preview_allowed"
            icon="print"
            color="theme"
            class="report-button"
            @click="printReport()"
          >
            {{ $t('reports.print') }}
          </base-button>
          <base-button
            v-if="getReportUrl && !isReportLoading"
            color="success"
            class="report-button whatsapp-report-button"
            @click="sendReports()"
          >
            <img
              :src="whatsappIconUrl"
              alt=""
              class="whatsapp-button-icon"
              aria-hidden="true"
            />
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
      <div v-if="getReportUrl && !isReportLoading && reportInfo && !reportInfo.preview_allowed" class="report-preview-empty" role="status">
        <font-awesome-icon icon="file-pdf" class="report-preview-icon"/>
        <p>{{ $t('reports.customers.preview_too_large', { rows: formatCount(reportInfo.rows), max: formatCount(reportInfo.preview_max_rows) }) }}</p>
      </div>
      <iframe
        v-else-if="getReportUrl && reportInfo && reportInfo.preview_allowed"
        v-show="!isReportLoading"
        ref="reportFrame"
        :key="reportPreviewKey"
        :src="previewSrc"
        :title="$t('reports.customers.report_preview')"
        class="reports-frame-style"
        @load="onReportLoaded"
      />
      <div v-else-if="!isReportLoading && !getReportUrl" class="report-preview-empty" role="status">
        <font-awesome-icon icon="file-pdf" class="report-preview-icon"/>
        <p>
          {{ selectedLedger
            ? $t('reports.customers.update_report_preview')
            : $t('reports.customers.select_ledger_preview')
          }}
        </p>
      </div>
      <a v-if="getReportUrl && !isReportLoading" class="base-button btn btn-primary btn-lg report-view-button" @click="viewReportsPDF">
        <font-awesome-icon icon="file-pdf" class="vue-icon icon-left svg-inline--fa fa-download fa-w-16 me-2" /> <span>{{ $t('reports.view_pdf') }}</span>
      </a>
    </div>
  </div>
</template>

<script>
import { openReportInNewTab } from '@/helpers/reportTabs'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import useVuelidate from '@vuelidate/core'
import { required } from '@vuelidate/validators';
import whatsappIconUrl from '@fortawesome/fontawesome-free/svgs/brands/whatsapp.svg'
import { createReportShare } from '@/helpers/publicShares'
export default {
  setup () { return { v$: useVuelidate() } },
  data () {
    return {
      whatsappIconUrl,
      range: new Date(),
      dateRange: [
        'Today',
        'Yesterday',
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
        from_date: moment().startOf('month').toISOString(),
        to_date: moment().endOf('month').toISOString()
      },
      url: null,
      siteURL: null,
      ledgersArr: [],
      selectedLedger: null,
      isReportLoading: false,
      reportPreviewKey: 0,
      // Row count and limits for the current report: large ledgers (e.g. Sales) are only
      // previewed as HTML, and only periods up to pdf_max_rows can be turned into a PDF.
      reportInfo: null,
      reportRequestId: 0,
      autoReportTimer: null,
      vouchersListArr: [],
    }
  },
  validations: {
    selectedRange: {
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
      return this.v$?.range || { $error: false, required: true, $touch: () => {} }
    },
    vFormData () {
      return this.v$?.formData || {
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
    // Periods that fit in a PDF preview as the PDF itself, so the browser's PDF viewer (with
    // its print, zoom and download buttons) shows as before; larger ones fall back to HTML.
    // Same-origin path: the share URL is built from APP_URL, and printing the frame needs
    // the frame to be same-origin with this page.
    previewSrc () {
      if (!this.url || !this.reportInfo) {
        return null
      }
      const path = new URL(this.url, window.location.origin).pathname
      return this.reportInfo.pdf_allowed ? path : path + '?preview=1'
    },
    getReportUrl () {
      return this.url
    }
  },
  created() {
    this.loadLedgers()
  },
  watch: {
    range (newRange) {
      this.formData.from_date = moment(newRange).startOf('year').toISOString()
      this.formData.to_date = moment(newRange).endOf('year').toISOString()
    }
  },
  mounted () {
  },
  unmounted () {
    clearTimeout(this.autoReportTimer)
    this.reportRequestId += 1
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
      if (ledger) {
        return this.getReports()
      }
    },
    getThisDate (type, time) {
      return moment()[type](time).toISOString()
    },
    getPreDate (type, time) {
      return moment().subtract(1, time)[type](time).toISOString()
    },
    onChangeDateRange () {
      switch (this.selectedRange) {
        case 'Yesterday':
          this.formData.from_date = moment().subtract(1, 'day').startOf('day').toISOString()
          this.formData.to_date = moment().subtract(1, 'day').endOf('day').toISOString()
          break

        case 'Today':
          this.formData.from_date = moment().toISOString()
          this.formData.to_date = moment().toISOString()
          break

        case 'Till Date':
          this.formData.from_date = moment(this.formData.to_date).startOf('month').toISOString()
          this.formData.to_date = moment(this.formData.to_date).toISOString()
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
      this.reportRequestId += 1
      this.url = null
      this.isReportLoading = false
      this.scheduleReport()
    },
    // Reload the preview whenever a filter changes, once a ledger is picked; the short
    // delay folds quick successive changes (e.g. from and to date) into one request.
    scheduleReport () {
      clearTimeout(this.autoReportTimer)
      if (!this.selectedLedger) {
        return
      }
      this.autoReportTimer = setTimeout(() => this.getReports({ silent: true }), 400)
    },
    onReportLoaded () {
      this.isReportLoading = false
    },
    // Prints whatever the preview shows (the PDF, or the HTML preview for large periods).
    printReport () {
      const frameWindow = this.$refs.reportFrame && this.$refs.reportFrame.contentWindow
      try {
        frameWindow.focus()
        frameWindow.print()
      } catch (error) {
        // Some browsers refuse to print an embedded PDF: open it, where their viewer can print it.
        if (!this.viewReportsPDF()) {
          window.toastr['error'](this.$t('reports.customers.report_load_failed'))
        }
      }
    },
    formatCount (value) {
      return new Intl.NumberFormat('en-IN').format(value || 0)
    },
    // Returns true when the period is too large for a PDF (and tells the user why).
    blockLargePdf () {
      if (!this.reportInfo || this.reportInfo.pdf_allowed) {
        return false
      }
      window.toastr['warning'](this.$t('reports.customers.pdf_too_large', {
        rows: this.formatCount(this.reportInfo.rows),
        max: this.formatCount(this.reportInfo.pdf_max_rows)
      }))
      return true
    },
    viewReportsPDF () {
      if (!this.getReportUrl) {
        return false
      }
      if (this.blockLargePdf()) {
        return true
      }
      return openReportInNewTab(this.getReportUrl)
    },
    prepareReportParameters ({ silent = false } = {}) {
      this.vRange.$touch()
      this.vFormData.$touch()
      if (this.selectedRange === 'Till Date') {
        this.formData.from_date = moment(this.formData.to_date).startOf('month').toISOString()
        this.formData.to_date = moment(this.formData.to_date).toISOString()
      }
      if (this.v$?.$invalid) {
        if (!silent) {
          window.toastr['error']("Error! missing required field or value is invalid.!")
        }
        return false
      }

      if (!this.selectedLedger) {
        if (!silent) {
          window.toastr['error'](this.$t('reports.customers.select_ledger_preview'))
        }
        return false
      }

      return {
        from_date: moment(this.formData.from_date).format('DD/MM/YYYY'),
        to_date: moment(this.formData.to_date).format('DD/MM/YYYY'),
        ledger_id: this.selectedLedger.id
      }
    },
    async getReports ({ silent = false } = {}) {
      clearTimeout(this.autoReportTimer)
      const requestId = ++this.reportRequestId
      this.url = null
      this.reportInfo = null
      const parameters = this.prepareReportParameters({ silent })
      if (!parameters) {
        this.isReportLoading = false
        return false
      }

      this.isReportLoading = true
      this.reportPreviewKey += 1
      try {
        const url = await createReportShare('customers', parameters)
        // Same-origin path: the share URL is built from APP_URL, which may differ from the browser host.
        const summary = await window.axios.get(new URL(url, window.location.origin).pathname, { params: { summary: 1 } })
        if (requestId !== this.reportRequestId) {
          return false
        }
        this.reportInfo = summary.data
        this.url = url
        if (!this.reportInfo.preview_allowed) {
          this.isReportLoading = false
        }
        return true
      } catch (error) {
        if (requestId === this.reportRequestId) {
          this.isReportLoading = false
          window.toastr['error'](this.$t('reports.customers.report_load_failed'))
        }
        return false
      }
    },
    downloadReport () {
      if (!this.getReportUrl || this.isReportLoading) {
        return false
      }
      if (this.blockLargePdf()) {
        // handled: the message explains why, so the layout's "not ready" alert is skipped
        return true
      }

      const downloadLink = document.createElement('a')
      downloadLink.href = this.getReportUrl + '?download=true'
      downloadLink.download = ''
      downloadLink.target = '_blank'
      downloadLink.rel = 'noopener'
      document.body.appendChild(downloadLink)
      downloadLink.click()
      downloadLink.remove()
      return true
    },
    async loadLedgers () {
      let response = await this.fetchLedgersReport()
      this.ledgersArr = response.data.ledgers
    },
    sendReports() {
      if (this.blockLargePdf()) {
        return
      }
      let mobile = this.selectedLedger.account_master.mobile_number
      if (!mobile) {
        window.toastr['error']("Sorry, didn't find mobile number for selected ledger.")
        return
      }
      let fileName = moment(this.formData.from_date).format('DD/MM/YYYY') + '-' + moment(this.formData.to_date).format('DD/MM/YYYY');
      const filePath = new URL(this.url, window.location.origin).href
      this.sendReportOnWhatsApp({ fileName: fileName, number: mobile, filePath })
    }
  }
}
</script>
