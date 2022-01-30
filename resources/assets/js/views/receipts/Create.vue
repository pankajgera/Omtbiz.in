<template>
  <div class="receipt-create main-content">
    <form action="" @submit.prevent="submitReceiptData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('receipts.edit_receipt') : $t('receipts.new_receipt') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
          <!-- <li class="breadcrumb-item"><router-link slot="item-title" to="/receipts">{{ $tc('receipts.receipt', 2) }}</router-link></li> -->
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
                v-model="listBind"
                :invalid="$v.formData.list.$error"
                :options="sundryDebtorList"
                :searchable="true"
                :show-labels="false"
                :placeholder="$t('receipts.select_a_list')"
                label="name"
                track-by="id"
              />
              <div v-if="$v.formData.list.$error">
                <span v-if="!$v.formData.list.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.invoice') }}</label>
                <base-select
                  v-model="invoice"
                  :options="invoiceListFiltered"
                  :searchable="true"
                  :show-labels="false"
                  :allow-empty="false"
                  :disabled="isEdit || !formData.list"
                  :placeholder="$t('invoices.select_invoice')"
                  :custom-label="invoiceWithAmount"
                  track-by="invoice_number"
                />
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.amount') }}</label><span class="text-danger"> *</span>
                 <base-input
                    v-model.trim="amount"
                    :class="{'invalid' : $v.formData.amount.$error, 'input-field': true}"
                    type="number"
                    :max="15"
                    name="amount"
                  />
                <div v-if="$v.formData.amount.$error">
                  <span v-if="!$v.formData.amount.required" class="text-danger">{{ $t('validation.required') }}</span>
                  <!-- <span v-if="!$v.formData.amount.between && $v.formData.amount.numeric && amount <= 0" class="text-danger">{{ $t('validation.receipt_greater_than_zero') }}</span> -->
                  <!-- <span v-if="!$v.formData.amount.between && amount > 0" class="text-danger">{{ $t('receipts.receipt_greater_than_due_amount') }}</span> -->
                </div>
              </div>
            </div>
            <div class="col-sm-6 mt-2">
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.receipt_mode') }}</label><span class="text-danger"> *</span>
                <base-select
                  v-model="formData.receipt_mode"
                  :options="getReceiptMode"
                  :searchable="true"
                  :show-labels="false"
                  :class="{'invalid' : $v.formData.receipt_mode.$error}"
                  :placeholder="$t('receipts.select_receipt_mode')"
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
const { required, between, maxLength, numeric } = require('vuelidate/lib/validators')

export default {
  components: { MultiSelect },
  mixins: [validationMixin],
  data () {
    return {
      formData: {
        // user_id: null,
        receipt_number: null,
        receipt_date: null,
        amount: null,
        receipt_mode: null,
        invoice_id: null,
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
      invoice: null,
      //customerList: [],
      invoiceList: [],
      invoiceListFiltered: [],
      isLoading: false,
      maxPayableAmount: Number.MAX_SAFE_INTEGER,
      isSettingInitialData: true,
      receiptNumAttribute: null,
      receiptPrefix: '',
      sundryDebtorList: [],
      closingBalanceType: '',
      accountLedger: [],
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
          // between: between(1, this.maxPayableAmount + 1)
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
      return ['Cash', 'Check', 'Credit Card', 'Bank Transfer']
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
        this.invoiceListFiltered = this.invoiceList.filter(i => i.account_master_id === val.id)
        this.formData.list = val
      }
    }
  },
  watch: {
    // customer (newValue) {
    //   this.formData.user_id = newValue.id
    //   if (!this.isEdit) {
    //     if (this.isSettingInitialData) {
    //       this.isSettingInitialData = false
    //     } else {
    //       this.invoice = null
    //       this.formData.invoice_id = null
    //     }
    //     this.formData.amount = 0
    //     this.invoiceList = []
    //     this.fetchCustomerInvoices(newValue.id)
    //   }
    // },
    invoice (newValue) {
      if (newValue) {
        this.formData.invoice_id = newValue.id
        if (!this.isEdit) {
          this.setReceiptAmountByInvoiceData(newValue.id)
        }
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
    ...mapActions('invoice', [
      'fetchInvoice'
    ]),
    ...mapActions('receipt', [
      'fetchCreateReceipt',
      'addReceipt',
      'updateReceipt',
      'fetchReceipt'
    ]),
    invoiceWithAmount ({ invoice_number, due_amount }) {
      return `${invoice_number} (₹ ${parseFloat(due_amount).toFixed(2)})`
    },
    async loadData () {
      this.fetchCustomerInvoices()
      if (this.isEdit) {
        let response = await this.fetchReceipt(this.$route.params.id)
        //this.customerList = response.data.customers
        this.formData = { ...response.data.receipt }
        //this.customer = response.data.receipt.user
        this.formData.receipt_date = moment(response.data.receipt.receipt_date, 'YYYY-MM-DD').toString()
        this.formData.amount = parseFloat(response.data.receipt.amount)
        this.receiptPrefix = response.data.receipt_prefix
        this.receiptNumAttribute = response.data.nextReceiptNumberAttribute
        if (response.data.receipt.invoice !== null) {
          this.maxPayableAmount = parseInt(response.data.receipt.amount) + parseInt(response.data.receipt.invoice.due_amount)
          this.invoice = response.data.receipt.invoice
        }
      } else {
        let response = await this.fetchCreateReceipt()
        this.sundryDebtorList = response.data.usersOfSundryDebitors
        this.accountLedger = response.data.account_ledger
        this.receiptNumAttribute = response.data.nextReceiptNumberAttribute
        this.receiptPrefix = response.data.receipt_prefix
        this.formData.receipt_date = moment(new Date()).toString()
      }
      return true
    },
    async setInvoiceReceiptData () {
      let data = await this.fetchInvoice(this.$route.params.id)
      this.customer = data.data.invoice.user
      this.invoice = data.data.invoice
    },
    async setReceiptAmountByInvoiceData (id) {
      let data = await this.fetchInvoice(id)
      this.formData.amount = parseFloat(data.data.invoice.due_amount).toFixed(2)
      this.maxPayableAmount = parseFloat(data.data.invoice.due_amount).toFixed(2)
    },
    async fetchCustomerInvoices () {
      let response = await axios.get(`/api/invoices/unpaid/`)
      if (response.data) {
        this.invoiceList = response.data.invoices
      }
    },
    async submitReceiptData () {
      //this.$v.customer.$touch()
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
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
            setTimeout(() => {
              window.location.reload()
            }, 2000)
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
            setTimeout(() => {
              window.location.reload()
            }, 2000)
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
    }
  }
}
</script>
