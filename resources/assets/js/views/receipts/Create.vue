<template>
  <div class="receipt-create main-content">
    <div class="page-header">
      <div class="page-actions row">
        <router-link slot="item-title" class="col-xs-2" to="/receipts">
          <base-button size="large" icon="envelope" color="theme">
            {{ $t('receipts.title') }}
          </base-button>
        </router-link>
      </div>
    </div>
    <form action="" @submit.prevent="submitReceiptData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('receipts.edit_receipt') : $t('receipts.new_receipt') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link slot="item-title" to="/receipts">{{ $tc('receipts.receipt', 2) }}</router-link></li>
          <li class="breadcrumb-item">{{ isEdit ? $t('receipts.edit_receipt') : $t('receipts.new_receipt') }}</li>
        </ol>
      </div>
      <div class="receipt-card card">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.date') }}</label><span class="text-danger"> *</span>
                <base-date-picker
                  v-model="formData.receipt_date"
                  :invalid="$v.formData.receipt_date.$error"
                  :calendar-button="true"
                  calendar-button-icon="calendar"
                  @change="$v.formData.receipt_date.$touch()"
                />
                <div v-if="$v.formData.receipt_date.$error">
                  <span v-if="!$v.formData.receipt_date.required" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.receipt_number') }}</label><span class="text-danger"> *</span>
                <base-prefix-input
                  :invalid="$v.receiptNumAttribute.$error"
                  v-model.trim="receiptNumAttribute"
                  :prefix="receiptPrefix"
                  :disabled="isEdit"
                  @input="$v.receiptNumAttribute.$touch()"
                />
                <div v-if="$v.receiptNumAttribute.$error">
                  <span v-if="!$v.receiptNumAttribute.required" class="text-danger">{{ $tc('validation.required') }}</span>
                  <span v-if="!$v.receiptNumAttribute.numeric" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label">{{ $t('receipts.list') }}</label><span class="text-danger"> *</span>
              <base-select
                v-model="formData.list"
                :invalid="$v.formData.list.$error"
                :options="sundryDebtorList"
                :searchable="true"
                :show-labels="false"
                :placeholder="$t('receipts.select_a_list')"
                label="name"
                track-by="id"
                :disabled="isEdit"
              />
              <div v-if="$v.formData.list.$error">
                <span v-if="!$v.formData.list.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.amount') }}</label><span class="text-danger"> *</span>
                 <base-input
                    v-model.trim="formData.amount"
                    :class="{'invalid' : $v.formData.amount.$error, 'input-field': true}"
                    type="number"
                    :max="15"
                    name="amount"
                  />
                <div v-if="formData.amount">
                  ₹ {{ numberWithCommas(formData.amount) }}
                </div>
                <div v-if="$v.formData.amount.$error">
                  <span v-if="!$v.formData.amount.required" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.receipt_mode') }}</label><span class="text-danger"> *</span>
                <base-select
                  v-model="formData.receipt_mode"
                  :options="getReceiptMode"
                  :searchable="true"
                  :show-labels="false"
                  :class="{'invalid' : $v.formData.receipt_mode.$error}"
                  :placeholder="$t('receipts.select_receipt_mode')"
                  :disabled="isEdit"
                />
                <div v-if="$v.formData.receipt_mode.$error">
                  <span v-if="!$v.formData.receipt_mode.required" class="text-danger">{{ $tc('validation.required') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6 ">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.note') }}</label>
                <base-text-area
                  v-model="formData.notes"
                  :rows="'5'"
                />
              </div>
            </div>
            <div class="col-sm-6">
              <div class="col-sm-12 p-0">
                <div class="form-group">
                  <label class="form-label">{{ $t('receipts.opening_balance') }}</label>
                  <base-prefix-input
                    v-model.trim="openingBalance"
                    :prefix="openingBalanceType ? openingBalanceType + ' - ' + money.prefix : money.prefix"
                    type="number"
                    name="openingBalance"
                    disabled
                  />
                </div>
              </div>
              <div class="col-sm-12 p-0">
                <div class="form-group">
                  <label class="form-label">{{ $t('receipts.closing_balance') }}</label>
                  <base-prefix-input
                    v-model.trim="closingBalance"
                    :prefix="closingBalanceType ? closingBalanceType + ' - ' + money.prefix : money.prefix"
                    type="number"
                    name="closingBalance"
                    disabled
                  />
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group collapse-button-container">
                <base-button
                  :loading="isLoading"
                  icon="save"
                  color="theme"
                  type="submit"
                  class="collapse-button"
                >
                  {{ $t('receipts.save_receipt') }}
                </base-button>
                <br/>
                <base-button v-if="isEdit" outline color="theme" class="report-button" @click="sendReports()">
                  {{ $t('reports.send_report') }}
                </base-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import MultiSelect from 'vue-multiselect'
import { validationMixin } from 'vuelidate'
import moment from 'moment'
import GlobalMixin from '../../helpers/mixins.js';
const { required, between, maxLength, numeric } = require('vuelidate/lib/validators')

export default {
  components: { MultiSelect },
  mixins: [validationMixin, GlobalMixin],
  data () {
    return {
      formData: {
        receipt_number: null,
        receipt_date: null,
        amount: null,
        receipt_mode: null,
        notes: null,
        list: null,
      },
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '₹ ',
        precision: 2,
        masked: false
      },
      customer: null,
      isLoading: false,
      maxPayableAmount: Number.MAX_SAFE_INTEGER,
      isSettingInitialData: true,
      receiptNumAttribute: null,
      receiptPrefix: '',
      sundryDebtorList: [],
      closingBalanceType: '',
      accountLedger: [],
      receiptMode: [],
      siteURL: '',
    }
  },
  validations () {
    return {
      formData: {
        list: {
          required
        },
        receipt_date: {
          required
        },
        receipt_mode: {
          required
        },
        amount: {
          required,
        },
      },
      receiptNumAttribute: {
        required,
        numeric
      }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrencyForInput'
    ]),
    ...mapGetters('user', {
      user: 'currentUser'
    }),
    getReceiptMode () {
      return this.receiptMode.map(i => i.name)
    },
    amount: {
      get: function () {
        return this.formData.amount
      },
      set: function (newValue) {
        if (0 > parseInt(newValue)) {
          this.formData.amount = 0
        } else {
          this.formData.amount = newValue
        }
      }
    },
    isEdit () {
      if (this.$route.name === 'receipts.edit') {
        return true
      }
      return false
    },
    openingBalance() {
      if (this.formData.list && this.formData.list.id) {
        let ledger = this.accountLedger.find(each => each.id === this.formData.list.id);
        return parseFloat(ledger.balance).toFixed(2);
      }
      return 0
    },
    openingBalanceType() {
      if (this.formData.list && this.formData.list.id) {
        let typeObj = this.accountLedger.find(each => each.id === this.formData.list.id);
        return typeObj.type;
      }
      return 'Cr';
    },
    closingBalance() {
      if (this.formData.amount) {
        let open = parseFloat(this.openingBalance);
        let amount = parseFloat(this.formData.amount);
        if (open >= amount) {
          let oa = open - amount;
          let openAmount = parseFloat(oa).toFixed(2);
          if (open > amount) {
            this.closingBalanceType = this.openingBalanceType === 'Dr' ? 'Dr' : 'Cr'
          } else {
            this.closingBalanceType = this.openingBalanceType === 'Cr' ? 'Dr' : 'Cr'
          }
          return openAmount
        } else {
          let ao = Math.abs(amount - open);
          let closeAmount = parseFloat(ao).toFixed(2);
          if (open > amount) {
            this.closingBalanceType = this.openingBalanceType === 'Dr' ? 'Dr' : 'Cr'
          } else {
            this.closingBalanceType = this.openingBalanceType === 'Cr' ? 'Dr' : 'Cr'
          }
          return closeAmount
        }
      }
      return 0
    },
    listBind: {
      cache:false,
      get() {
        return this.formData.list
      },
      set(val) {
        this.formData.list = val
      }
    }
  },
  async mounted () {
    this.$nextTick(() => {
      this.loadData()
      if (this.$route.params.id && !this.isEdit) {
        this.setInvoiceReceiptData()
      }
    })
  },
  methods: {
    ...mapActions('receipt', [
      'fetchCreateReceipt',
      'addReceipt',
      'updateReceipt',
      'fetchReceipt'
    ]),
    ...mapActions('customer', [
      'sendReportOnWhatsApp'
    ]),
    async loadData () {
      if (this.isEdit) {
        let response = await this.fetchReceipt(this.$route.params.id)
        this.formData = { ...response.data.receipt }
        this.formData.receipt_date = response.data.receipt.receipt_date
        this.formData.amount = parseFloat(response.data.receipt.amount)
        this.receiptPrefix = response.data.receipt_prefix
        this.receiptNumAttribute = response.data.nextReceiptNumberAttribute
        this.sundryDebtorList = response.data.usersOfSundryDebitors
        this.formData.list = response.data.usersOfSundryDebitors.filter(i => i.id === response.data.receipt.account_master_id)[0]
        this.accountLedger = response.data.account_ledger
        this.receiptMode = response.data.receipt_mode

        this.siteURL = `/receipts/pdf/${this.formData.id}`
        if (response.data.receipt.invoice !== null) {
          this.maxPayableAmount = parseInt(response.data.receipt.amount) + parseInt(response.data.receipt.invoice.due_amount)
        }
      } else {
        let response = await this.fetchCreateReceipt()
        this.sundryDebtorList = response.data.usersOfSundryDebitors
        this.accountLedger = response.data.account_ledger
        this.receiptNumAttribute = response.data.nextReceiptNumberAttribute
        this.receiptPrefix = response.data.receipt_prefix
        this.formData.receipt_date = moment(new Date()).toString()
        this.receiptMode = response.data.receipt_mode
      }
      return true
    },
    async setInvoiceReceiptData () {
      let data = await this.fetchInvoice(this.$route.params.id)
      this.customer = data.data.invoice.user
    },
    async setReceiptAmountByInvoiceData (id) {
      let data = await this.fetchInvoice(id)
      this.formData.amount = parseFloat(data.data.invoice.due_amount).toFixed(2)
      this.maxPayableAmount = parseFloat(data.data.invoice.due_amount).toFixed(2)
    },
    async submitReceiptData () {
      this.$v.formData.$touch()
      if (this.$v.formData.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }

      this.formData.receipt_number = this.receiptPrefix + '-' + this.receiptNumAttribute
      this.formData.user_id = this.user.id
      this.formData.closing_balance_type = this.closingBalanceType
      this.formData.closing_balance = this.closingBalance
      if (this.isEdit) {
        let data = {
          editData: {
            ...this.formData,
            receipt_date: moment(this.formData.receipt_date).format('DD/MM/YYYY')
          },
          id: this.$route.params.id
        }
        try {
          let response = await this.updateReceipt(data)
          if (response.data.success) {
            window.toastr['success'](this.$t('receipts.updated_message'))
            return true
          }
          if (response.data.error === 'invalid_amount') {
            window.toastr['error'](this.$t('invalid_amount_message'))
            return false
          }
          window.toastr['error'](response.data.error)
        } catch (err) {
          this.isLoading = false
          if (err.response.data.errors.receipt_number) {
            window.toastr['error'](err.response.data.errors.receipt_number)
            return true
          }
          window.toastr['error'](err)
        }
      } else {
        let data = {
          ...this.formData,
          receipt_date: moment(this.formData.receipt_date).format('DD/MM/YYYY')
        }
        this.isLoading = true
        try {
          let response = await this.addReceipt(data)
          if (response.data.success) {
            window.toastr['success'](this.$t('receipts.created_message'))
            this.siteURL = `/receipts/pdf/${response.data.receipt.id}`
            window.swal({
              title: 'Send Receipt',
              text: 'Do you want to send receipt on whatsapp?',
              icon: '/assets/icon/envelope-solid.svg',
              buttons: true,
              dangerMode: false
            }).then(async (value) => {
              if (value) {
                this.sendReports()
              } else {
                setTimeout(() => {
                  window.location.reload()
                }, 1000)
              }
            });
            return true
          }
          if (response.data.error === 'invalid_amount') {
            window.toastr['error'](this.$t('invalid_amount_message'))
            return false
          }
          window.toastr['error'](response.data.error)
        } catch (err) {
          this.isLoading = false
          if (err.response.data.errors.receipt_number) {
            window.toastr['error'](err.response.data.errors.receipt_number)
            return true
          }
          window.toastr['error'](err)
        }
      }
    },
    sendReports() {
      this.isLoading = true
      if (!this.siteURL) {
        window.toastr['error']('Receipt report url not found');
        return;
      }
      let mobile = this.accountLedger.find(i => i.id === this.formData.list.id).mobile_number;
      if (!mobile) {
        window.toastr['error']("Sorry, didn't find mobile number for selected ledger.")
        return
      }
      let fileName = moment(this.formData.receipt_date).format('DD/MM/YYYY');
      this.sendReportOnWhatsApp({ fileName: fileName, number: mobile, filePath: "http://omtbiz.in" + this.siteURL})
      .then((val) => {
        setTimeout(() => {
          this.isLoading = false
          window.location.reload()
        }, 2000)
      })
    }
  }
}
</script>
