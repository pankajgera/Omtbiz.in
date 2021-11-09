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
              <div class="form-group" v-if="invoiceList && invoiceList.length">
                <label class="form-label">{{ $t('receipts.invoice') }}</label>
                <base-select
                  :multiple="true"
                  v-model="invoice"
                  :options="invoiceList"
                  :searchable="true"
                  :show-labels="false"
                  :allow-empty="true"
                  :disabled="isEdit"
                  :custom-label="invoiceWithAmount"
                  track-by="invoice_number"
                  class="multi-select-item"
                  @select="addInvoice"
                  @remove="removeInvoice"
                />
              </div>
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
                    :invalid="$v.formData.status.$error"
                    :show-labels="false"
                    :placeholder="$t('dispatch.status')"
                    :allow-empty="false"
                    track-by="id"
                    label="name"
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
import moment from 'moment'
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
        invoice_id: [],
        date_time: '',
        transport: '',
        status: {
          id: 2,
          name: 'Sent',
        },
        all_selected_dispatch: []
      },
      invoice: [],
      invoiceList: null,
      assignToBeDispatch: false,
      statusList: [
        {
          id: 1,
          name: 'Draft',
        },
        {
          id: 2,
          name: 'Sent',
        }
      ],
      isToBeDispatch: []
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'dispatch.edit' || this.assignToBeDispatch) {
        return true
      }
      return false
    },
    formatDate() {
      if (this.formData.date_time) {
        moment(this.formData.date_time).format('DD-MM-YYYY HH:mm:ss')
      }
      return moment().format('DD-MM-YYYY HH:mm:ss');
    }
  },
  created () {
    this.fetchInvoices()
  },
  destroyed() {
    this.resetSelectedDispatch()
    this.resetSelectedToBeDispatch()
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
      'editDispatch',
      'editToBeDispatch',
      'dipatchedData',
      'updateDispatch',
      'updateToBeDispatch',
      'resetSelectedDispatch',
      'resetSelectedToBeDispatch'
    ]),
    addInvoice (value) {
      if (value) {
        this.formData.invoice_id.push(value.id)
      }
    },
    removeInvoice (value) {
      let index = this.formData.invoice_id.findIndex(each => each === value.id)
      if (index) {
        this.formData.invoice_id.splice(index, 1)
      }
    },
    invoiceWithAmount ({ invoice_number, due_amount }) {
      return `${invoice_number} (₹ ${parseFloat(due_amount).toFixed(2)})`
    },
    loadInvoice() {
      this.invoice = []
      this.formData.invoice_id.map(i => {
        let findFromList = this.invoiceList.find(j => j.id === parseInt(i));
        this.invoice.push(findFromList)
      })
    },
    async loadEditData () {
      let response = await this.editDispatch(this.$route.params.id)
      this.formData = response.data.dispatch
      this.formData.status = this.statusList.filter(each => each.name === response.data.dispatch.status)[0]
      this.loadInvoice()
    },
    async loadIsToBeDispatch() {
      let response = await this.editToBeDispatch(this.isToBeDispatch.toString())
      this.formData = response.data.dispatch[0]
      this.formData.status = this.statusList[1]
      let invoiceId = []
      response.data.dispatch.map(each => each.invoice_id.map(i => invoiceId.push(i)))
      this.formData.invoice_id = invoiceId
      this.loadInvoice()
      this.assignToBeDispatch = true
      this.formData['all_selected_dispatch'] = [];
      response.data.dispatch.map(each => this.formData.all_selected_dispatch.push(each.id))
    },
    async fetchInvoices () {
      let response = await axios.get(`/api/dispatch/invoices`)
      if (response.data) {
        this.invoiceList = response.data.invoices
        if (this.isEdit) {
          this.loadEditData()
        }
        this.isToBeDispatch = this.$store.state.dispatch.selectedToBeDispatch
        if (this.isToBeDispatch.length) {
          this.loadIsToBeDispatch()
        }
      }
    },
    async submitDispatch () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }

      try {
        this.isLoading = true
        let response = null;
        if (this.isEdit) {
          if (this.assignToBeDispatch) {
            response = await this.updateToBeDispatch(this.formData)
          } else {
            response = await this.updateDispatch(this.formData)
          }
        } else {
          response = await this.addDispatch(this.formData)
        }
        if (response.data) {
          this.isLoading = false
          if (this.isEdit) {
            window.toastr['success'](this.$tc('dispatch.updated_message'))
          } else {
            window.toastr['success'](this.$tc('dispatch.created_message'))
          }
          this.$router.push('/dispatch')
          return true
        }
      } catch (err) {
        if (err) {
          this.isLoading = false
          window.toastr['error'](err)
        }
      }
    },
  }
}
</script>
