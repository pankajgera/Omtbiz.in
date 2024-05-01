<template>
  <div class="row">
    <div class="col-md-4 reports-tab-container">
      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="report-label">{{ $t('reports.customers.ledgers') }}</label>
          <base-select
            ref="selectedLedger"
            v-model="selectedLedger"
            :options="ledgersArr.map(i => {return i.account + ' (Group: ' + i.account_master.groups + ')'})"
            :allow-empty="false"
            :show-labels="false"
            @input="getReports"
          />
          <span v-if="$v.range.$error && !$v.range.required" class="text-danger"> {{ $t('validation.required') }} </span>
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
          <span v-if="$v.range.$error && !$v.range.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
      </div>
      <div class="row report-fields-container">
        <div class="col-md-6 report-field-container" v-if="selectedRange !== 'Till Date'">
          <label class="report-label">{{ $t('reports.customers.from_date') }}</label>
          <base-date-picker
            v-model="formData.from_date"
            :invalid="$v.formData.from_date.$error"
            :calendar-button="true"
            calendar-button-icon="calendar"
            @change="$v.formData.from_date.$touch()"
          />
          <span v-if="$v.formData.from_date.$error && !$v.formData.from_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
        <div class="col-md-6 report-field-container">
          <label class="report-label">{{ selectedRange !== 'Till Date' ? $t('reports.customers.to_date') : $t('reports.customers.till_date') }}</label>
          <base-date-picker
            v-model="formData.to_date"
            :invalid="$v.formData.to_date.$error"
            :calendar-button="true"
            calendar-button-icon="calendar"
            @change="$v.formData.to_date.$touch()"
          />
          <span v-if="$v.formData.to_date.$error && !$v.formData.to_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
      </div>
      <div class="row report-submit-button-container">
        <div class="col-md-6">
          <base-button outline color="theme" class="report-button" @click="getReports()">
            {{ $t('reports.update_report') }}
          </base-button>
          <br/>
          <base-button v-if="selectedLedger" outline color="theme" class="report-button" @click="sendReports()">
            {{ $t('reports.send_report') }}
          </base-button>
        </div>
      </div>
    </div>
    <div class="col-sm-8 reports-tab-container">
      <iframe :src="getReportUrl" class="reports-frame-style"/>
      <a class="base-button btn btn-primary btn-lg report-view-button" @click="viewReportsPDF">
        <font-awesome-icon icon="file-pdf" class="vue-icon icon-left svg-inline--fa fa-download fa-w-16 mr-2" /> <span>{{ $t('reports.view_pdf') }}</span>
      </a>
    </div>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { validationMixin } from 'vuelidate'
const { required } = require('vuelidate/lib/validators')

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
      ledger: '',
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
    ...mapGetters('company', [
      'getSelectedCompany'
    ]),
    getReportUrl () {
      return this.url
    },
    selectedLedger: {
      get: function() {
        return this.ledger
      },
      set: function(value) {
        this.ledger = (value.substring(0, value.indexOf('(Group:'))).trim();
        // let legder_id = this.ledgersArr.find(i => i.account === value).id
        // if (legder_id) {
        //   this.onChangeLedgers(legder_id)
        // }
      }
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
    this.url = `${this.siteURL}?from_date=${moment(this.formData.from_date).format('DD/MM/YYYY')}&to_date=${moment(this.formData.to_date).format('DD/MM/YYYY')}`
  },
  methods: {
     ...mapActions('customer', [
      'fetchLedgersReport',
      'fetchVouchersReport',
      'sendReportOnWhatsApp'
    ]),
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
          let ledgerStartDate = this.ledgersArr.find(i => i.account === this.ledger)?.date;
          this.formData.from_date = ledgerStartDate
          this.formData.to_date = moment().toString()
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
    },
    setRangeToCustom () {
      this.selectedRange = 'Custom'
    },
    async viewReportsPDF () {
      let data = await this.getReports()
      window.open(this.getReportUrl, '_blank')
      return data
    },
    async getReports (isDownload = false) {
      this.$v.range.$touch()
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }
      this.url = `${this.siteURL}?from_date=${moment(this.formData.from_date).format('DD/MM/YYYY')}&to_date=${moment(this.formData.to_date).format('DD/MM/YYYY')}&ledger_id=${this.ledgersArr.find(i => i.account === this.ledger).id}`
      return true
    },
    downloadReport () {
      if (!this.getReports()) {
        return false
      }
      window.open(this.getReportUrl + '&download=true')
      setTimeout(() => {
        this.url = `${this.siteURL}?from_date=${moment(this.formData.from_date).format('DD/MM/YYYY')}&to_date=${moment(this.formData.to_date).format('DD/MM/YYYY')}`
      }, 200)
    },
    async loadLedgers () {
      let response = await this.fetchLedgersReport()
      this.ledgersArr = response.data.ledgers
    },
    sendReports() {
      let mobile = this.ledgersArr.find(i => i.account === this.selectedLedger).account_master.mobile_number
      if (!mobile) {
        window.toastr['error']("Sorry, didn't find mobile number for selected ledger.")
        return
      }
      let fileName = moment(this.formData.from_date).format('DD/MM/YYYY') + '-' + moment(this.formData.to_date).format('DD/MM/YYYY');
      this.sendReportOnWhatsApp({ fileName: fileName, number: mobile, filePath: "http://omtbiz.in" + this.url})
      // window.open("https://api.whatsapp.com/send/?phone=" +'+91'+ mobile + "&text=" + encodeURIComponent("http://omtbiz.in" + this.url))
    }
  }
}
</script>
