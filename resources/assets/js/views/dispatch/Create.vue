<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('dispatch.edit_dispatch') : $t('dispatch.new_dispatch') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/dispatch">{{ $tc('dispatch.dispatch',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('dispatch.edit_dispatch') : $t('dispatch.new_dispatch') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-8">
        <div class="card">
          <form action="" @submit.prevent="submitDispatch">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.name') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.name"
                  :invalid="$v.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  @input="$v.formData.name.$touch()"
                />
                <div v-if="$v.formData.name.$error">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger">
                    {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.date_time') }}</label><span class="text-danger"> *</span>
                <base-date-picker
                  v-model="formData.date_time"
                  :invalid="$v.formData.date_time.$error"
                  :calendar-button="true"
                  calendar-button-icon="calendar"
                  @change="$v.formData.date_time.$touch()"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.transport') }}</label>
                <base-input
                  v-model.trim="formData.transport"
                  focus
                  type="text"
                  name="transport"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.status') }}</label><span class="text-danger"> *</span>
                <base-select
                    v-model="formData.status"
                    :options="statusList"
                    :show-labels="false"
                    :placeholder="$t('dispatch.status')"
                    :allow-empty="false"
                    track-by="id"
                    label="name"
                  />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('receipts.invoice') }}</label>
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

              <div class="form-group">
                <base-button
                  :loading="isLoading"
                  :disabled="isLoading"
                  icon="save"
                  color="theme"
                  type="submit"
                  class="collapse-button"
                >
                  {{ isEdit ? $t('dispatch.update_dispatch') : $t('dispatch.save_dispatch') }}
                </base-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
const { required, minLength, numeric, minValue, maxLength } = require('vuelidate/lib/validators')

export default {
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Add Dispatch',
      formData: {
        name: '',
        inovice_id: '',
        date_time: '',
        transport: '',
        status: {},
      },
      invoice: null,
      invoiceList: [],
      statusList: [
        {
          id: 1,
          name: 'Draft',
        },
        {
          id: 2,
          name: 'Sent',
        },
        {
          id: 3,
          name: 'Completed',
        }
      ]
    }
  },
  watch: {
    invoice (newValue) {
      if (newValue) {
        this.formData.invoice_id = newValue.id
      }
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'dispatch.edit') {
        return true
      }
      return false
    }
  },
  created () {
    if (this.isEdit) {
      this.loadEditData()
    }
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
      date_time: {
        required,
      },
      status: {
        required,
      },
    }
  },
  methods: {
    ...mapActions('dispatch', [
      'addDispatch',
      'fetchDispatch',
      'updateDispatch'
    ]),
    invoiceWithAmount ({ invoice_number, due_amount }) {
      return `${invoice_number} (₹ ${parseFloat(due_amount/100).toFixed(2)})`
    },
    async loadEditData () {
      this.fetchCustomerInvoices()
      let response = await this.fetchDispatch(this.$route.params.id)
      this.formData = response.data.dispatch
    },
    async fetchCustomerInvoices () {
      let response = await axios.get(`/api/invoices/unpaid/`)
      if (response.data) {
        this.invoiceList = response.data.invoices
      }
    },
    async submitDispatch () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }

      if (this.isEdit) {
        try {
          this.isLoading = true
          let response = await this.updateDispatch(this.formData)
          if (response.data) {
            this.isLoading = false
            window.toastr['success'](this.$tc('dispatch.updated_message'))
            this.$router.push('/dispatch')
            return true
          }
        } catch (err) {
          if (err) {
            this.isLoading = false
            window.toastr['error'](err)
          }
        }
      } else {
        try {
          this.isLoading = true
          let response = await this.addDispatch(this.formData)

          if (response.data) {
            window.toastr['success'](this.$tc('dispatch.created_message'))
            this.$router.push('/dispatch')
            this.isLoading = false
            return true
          }
        } catch (err) {
          if (err) {
            this.isLoading = false
            window.toastr['error'](err)
          }
        }
      }
    },
  }
}
</script>
