<template>
  <div class="payment-create main-content">
    <form action="" @submit.prevent="submitPaymentData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('payments.edit_payment') : $t('payments.new_payment') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
          <!-- <li class="breadcrumb-item"><router-link slot="item-title" to="/payments">{{ $tc('payments.payment', 2) }}</router-link></li> -->
          <li class="breadcrumb-item">{{ isEdit ? $t('payments.edit_payment') : $t('payments.new_payment') }}</li>
        </ol>
      </div>
      <div class="payment-card card">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.date') }}</label><span class="text-danger"> *</span>
                <base-date-picker
                  v-model="formData.payment_date"
                  :invalid="$v.formData.payment_date.$error"
                  :calendar-button="true"
                  calendar-button-icon="calendar"
                  @change="$v.formData.payment_date.$touch()"
                />
                <div v-if="$v.formData.payment_date.$error">
                  <span v-if="!$v.formData.payment_date.required" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label">{{ $t('payments.list') }}</label><span class="text-danger"> *</span>
              <base-select
                v-model="formData.list"
                :class="{'invalid' : $v.formData.list.$error}"
                :options="sundryCreditorList"
                :searchable="true"
                :show-labels="false"
                :placeholder="$t('payments.select_a_list')"
                label="name"
                track-by="id"
              />
              <div v-if="$v.formData.list.$error">
                <span v-if="!$v.formData.list.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.amount') }}</label><span class="text-danger"> *</span>
                 <base-input
                    v-model.trim="amount"
                    :class="{'invalid' : $v.formData.amount.$error, 'input-field': true}"
                    type="number"
                    name="amount"
                  />
                <div v-if="$v.formData.amount.$error">
                  <span v-if="!$v.formData.amount.required" class="text-danger">{{ $t('validation.required') }}</span>
                  <span v-if="!$v.formData.amount.between && $v.formData.amount.numeric && amount <= 0" class="text-danger">{{ $t('validation.payment_greater_than_zero') }}</span>
                  <span v-if="!$v.formData.amount.between && amount > 0" class="text-danger">{{ $t('validation.payment_greater_than_due_amount') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.payment_mode') }}</label><span class="text-danger"> *</span>
                <base-select
                  v-model="formData.payment_mode"
                  :options="getPaymentMode"
                  :searchable="true"
                  :show-labels="false"
                  :class="{'invalid' : $v.formData.payment_mode.$error}"
                  :placeholder="$t('payments.select_payment_mode')"
                />
                <div v-if="$v.formData.payment_mode.$error">
                  <span v-if="!$v.formData.payment_mode.required" class="text-danger">{{ $tc('validation.required') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6 ">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.note') }}</label>
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
                  {{ $t('payments.save_payment') }}
                </base-button>
              </div>
            </div>
            <div class="page-actions header-button-container">
              <base-button
                :loading="isLoading"
                :disabled="isLoading"
                icon="save"
                color="theme"
                type="submit">
                {{ isEdit ? $t('payments.update_payment') : $t('payments.save_payment') }}
              </base-button>
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
        user_id: null,
        payment_number: null,
        payment_date: null,
        amount: null,
        payment_mode: null,
        // invoice_id: null,
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
      //invoice: null,
      //customerList: [],
      //invoiceList: [],
      isLoading: false,
      maxPayableAmount: Number.MAX_SAFE_INTEGER,
      isSettingInitialData: true,
      //paymentNumAttribute: null,
      paymentPrefix: '',
      sundryCreditorList: [],
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
        payment_date: {
          required
        },
        payment_mode: {
          required
        },
        amount: {
          required,
          between: between(1, this.maxPayableAmount + 1)
        },
      },
      // paymentNumAttribute: {
      //   required,
      //   numeric
      // }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrencyForInput'
    ]),
    ...mapGetters('user', {
      user: 'currentUser'
    }),
    getPaymentMode () {
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
      if (this.$route.name === 'payments.edit') {
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
  },
  async mounted () {
    this.$nextTick(() => {
      this.loadData()
      // if (this.$route.params.id && !this.isEdit) {
      //   this.setInvoicePaymentData()
      // }
    })
  },
  methods: {
    ...mapActions('invoice', [
      'fetchInvoice'
    ]),
    ...mapActions('payment', [
      'fetchCreatePayment',
      'addPayment',
      'updatePayment',
      'fetchPayment'
    ]),
    invoiceWithAmount ({ invoice_number, due_amount }) {
      return `${invoice_number} (${this.$utils.formatGraphMoney(due_amount, this.customer.currency)})`
    },
    async loadData () {
      if (this.isEdit) {
        let response = await this.fetchPayment(this.$route.params.id)
        //this.customerList = response.data.customers
        this.formData = { ...response.data.payment }
        //this.customer = response.data.payment.user
        this.formData.payment_date = moment(response.data.payment.payment_date, 'YYYY-MM-DD').toString()
        this.formData.amount = parseFloat(response.data.payment.amount)
        this.paymentPrefix = response.data.payment_prefix
        //this.paymentNumAttribute = response.data.nextPaymentNumber
        if (response.data.payment.invoice !== null) {
          this.maxPayableAmount = parseInt(response.data.payment.amount) + parseInt(response.data.payment.invoice.due_amount)
          this.invoice = response.data.payment.invoice
        }
      } else {
        let response = await this.fetchCreatePayment()
        this.sundryCreditorList = response.data.usersOfSundryCreditor
        this.accountLedger = response.data.account_ledger
        //this.customerList = response.data.customers
        //this.paymentNumAttribute = response.data.nextPaymentNumberAttribute
        this.paymentPrefix = response.data.payment_prefix
        this.formData.payment_date = moment(new Date()).toString()
      }
      return true
    },
    async setPaymentAmountByInvoiceData (id) {
      let data = await this.fetchInvoice(id)
      this.formData.amount = data.data.invoice.due_amount
      this.maxPayableAmount = data.data.invoice.due_amount
    },
    async submitPaymentData () {
      //this.$v.customer.$touch()
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return true
      }

      //this.formData.payment_number = this.paymentPrefix + '-' + this.paymentNumAttribute
      this.formData.user_id = this.user.id
      this.formData.closing_balance_type = this.closingBalanceType
      this.formData.closing_balance = this.closingBalance
      if (this.isEdit) {
        let data = {
          editData: {
            ...this.formData,
            payment_date: moment(this.formData.payment_date).format('DD/MM/YYYY')
          },
          id: this.$route.params.id
        }
        try {
          let response = await this.updatePayment(data)
          if (response.data.success) {
            window.toastr['success'](this.$t('payments.updated_message'))
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
          window.toastr['error'](err)
        }
      } else {
        let data = {
          ...this.formData,
          payment_date: moment(this.formData.payment_date).format('DD/MM/YYYY')
        }
        this.isLoading = true
        try {
          let response = await this.addPayment(data)
          if (response.data.success) {
            window.toastr['success'](this.$t('payments.created_message'))
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
          window.toastr['error'](err)
        }
      }
    }
  }
}
</script>
