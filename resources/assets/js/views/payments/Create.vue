<template>
  <div class="payment-create main-content">
    <form action="" @submit.prevent="submitPaymentData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('payments.edit_payment') : $t('payments.new_payment') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link slot="item-title" to="/payments">{{ $tc('payments.payment', 2) }}</router-link></li>
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
              <div class="form-group">
                <label class="form-label">{{ $t('payments.payment_number') }}</label><span class="text-danger"> *</span>
                <base-prefix-input
                  :invalid="$v.paymentNumAttribute.$error"
                  v-model.trim="paymentNumAttribute"
                  :prefix="paymentPrefix"
                  @input="$v.paymentNumAttribute.$touch()"
                />
                <div v-if="$v.paymentNumAttribute.$error">
                  <span v-if="!$v.paymentNumAttribute.required" class="text-danger">{{ $tc('validation.required') }}</span>
                  <span v-if="!$v.paymentNumAttribute.numeric" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label">{{ $t('payments.list') }}</label><span class="text-danger"> *</span>
              <base-select
                v-model="formData.list"
                :invalid="$v.formData.list.$error"
                :options="listArr"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :disabled="isEdit"
                :placeholder="$t('payments.select_a_list')"
                label="name"
                track-by="id"
              />
              <div v-if="$v.formData.list.$error">
                <span v-if="!$v.formData.list.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <!-- <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.invoice') }}</label>
                <base-select
                  v-model="invoice"
                  :options="invoiceList"
                  :searchable="true"
                  :show-labels="false"
                  :allow-empty="false"
                  :disabled="isEdit"
                  :placeholder="$t('invoices.select_invoice')"
                  :custom-label="invoiceWithAmount"
                  track-by="invoice_number"
                />
              </div>
            </div> -->
            <div class="col-sm-6">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.amount') }}</label><span class="text-danger"> *</span>
                <div class="base-input">
                  <money
                    :class="{'invalid' : $v.formData.amount.$error}"
                    v-model="amount"
                    v-bind="customerCurrency"
                    class="input-field"
                  />
                </div>
                <div v-if="$v.formData.amount.$error">
                  <span v-if="!$v.formData.amount.required" class="text-danger">{{ $t('validation.required') }}</span>
                  <span v-if="!$v.formData.amount.between && $v.formData.amount.numeric && amount <= 0" class="text-danger">{{ $t('validation.payment_greater_than_zero') }}</span>
                  <span v-if="!$v.formData.amount.between && amount > 0" class="text-danger">{{ $t('validation.payment_greater_than_due_amount') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mt-2">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.payment_mode') }}</label><span class="text-danger"> *</span>
                <base-select
                  v-model="formData.payment_mode"
                  :options="getPaymentMode"
                  :searchable="true"
                  :show-labels="false"
                  :class="{'invalid' : $v.formData.amount.$error}"
                  :placeholder="$t('payments.select_payment_mode')"
                />
              </div>
            </div>
            <div class="col-sm-6 ">
              <div class="form-group">
                <label class="form-label">{{ $t('payments.note') }}</label>
                <base-text-area
                  v-model="formData.notes"
                />
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
        // user_id: null,
        payment_number: null,
        payment_date: null,
        amount: 0,
        payment_mode: null,
        // invoice_id: null,
        notes: null,
        list: null,
      },
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '$ ',
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
      paymentNumAttribute: null,
      paymentPrefix: '',
      listArr: [{id: 1, name: 'Sundry Creditor'}]
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
      paymentNumAttribute: {
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
    getPaymentMode () {
      return ['Cash', 'Check', 'Credit Card', 'Bank Transfer']
    },
    amount: {
      get: function () {
        return this.formData.amount
      },
      set: function (newValue) {
        this.formData.amount = newValue
      }
    },
    isEdit () {
      if (this.$route.name === 'payments.edit') {
        return true
      }
      return false
    },
    customerCurrency () {
      if (this.customer && this.customer.currency) {
        return {
          decimal: this.customer.currency.decimal_separator,
          thousands: this.customer.currency.thousand_separator,
          prefix: this.customer.currency.symbol + ' ',
          precision: this.customer.currency.precision,
          masked: false
        }
      } else {
        return this.defaultCurrencyForInput
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
    // invoice (newValue) {
    //   if (newValue) {
    //     this.formData.invoice_id = newValue.id
    //     if (!this.isEdit) {
    //       this.setPaymentAmountByInvoiceData(newValue.id)
    //     }
    //   }
    // }
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
        this.paymentNumAttribute = response.data.nextPaymentNumber
        if (response.data.payment.invoice !== null) {
          this.maxPayableAmount = parseInt(response.data.payment.amount) + parseInt(response.data.payment.invoice.due_amount)
          this.invoice = response.data.payment.invoice
        }
        // this.fetchCustomerInvoices(this.customer.id)
      } else {
        let response = await this.fetchCreatePayment()
        //this.customerList = response.data.customers
        this.paymentNumAttribute = response.data.nextPaymentNumberAttribute
        this.paymentPrefix = response.data.payment_prefix
        this.formData.payment_date = moment(new Date()).toString()
      }
      return true
    },
    // async setInvoicePaymentData () {
    //   let data = await this.fetchInvoice(this.$route.params.id)
    //   this.customer = data.data.invoice.user
    //   this.invoice = data.data.invoice
    // },
    async setPaymentAmountByInvoiceData (id) {
      let data = await this.fetchInvoice(id)
      this.formData.amount = data.data.invoice.due_amount
      this.maxPayableAmount = data.data.invoice.due_amount
    },
    // async fetchCustomerInvoices (userID) {
    //   let response = await axios.get(`/api/invoices/unpaid/${userID}`)
    //   if (response.data) {
    //     this.invoiceList = response.data.invoices
    //   }
    // },
    async submitPaymentData () {
      //this.$v.customer.$touch()
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return true
      }

      this.formData.payment_number = this.paymentPrefix + '-' + this.paymentNumAttribute
      this.formData.list = this.formData.list.name
      this.formData.user_id = this.user.id
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
            this.$router.push('/payments')
            return true
          }
          if (response.data.error === 'invalid_amount') {
            window.toastr['error'](this.$t('invalid_amount_message'))
            return false
          }
          window.toastr['error'](response.data.error)
        } catch (err) {
          this.isLoading = false
          if (err.response.data.errors.payment_number) {
            window.toastr['error'](err.response.data.errors.payment_number)
            return true
          }
          window.toastr['error'](err.response.data.message)
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
            this.$router.push('/payments')
            this.isLoading = true
            return true
          }
          if (response.data.error === 'invalid_amount') {
            window.toastr['error'](this.$t('invalid_amount_message'))
            return false
          }
          window.toastr['error'](response.data.error)
        } catch (err) {
          this.isLoading = false
          if (err.response.data.errors.payment_number) {
            window.toastr['error'](err.response.data.errors.payment_number)
            return true
          }
          window.toastr['error'](err.response.data.message)
        }
      }
    }
  }
}
</script>
