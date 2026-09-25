<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('dispatch.edit_dispatch') : $t('dispatch.new_dispatch') }}</h3>
      <base-button v-if="isEdit" type="button" :disabled="!invoice.length" @click="printDispatch">
        {{ $t('dispatch.print_preview') }}
      </base-button>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/dispatch">{{ $tc('dispatch.dispatch',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('dispatch.edit_dispatch') : $t('dispatch.new_dispatch') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitDispatch">
            <div class="card-body" id="to_print">
              <div class="form-group" v-if="invoiceList && invoiceList.length">
                <label class="form-label">{{ $t('receipts.invoice') }}</label>
                <base-select
                  v-model="invoice"
                  :multiple="true"
                  :show-pointer="false"
                  :options="isEdit ? invoiceList : invoiceList.filter(node=>node.status!=='COMPLETED')"
                  :internal-search="false"
                  :loading="invoiceSearchLoading"
                  :searchable="true"
                  :show-labels="false"
                  :allow-empty="true"
                  :disabled="isEdit"
                  :placeholder="$t('invoices.select_invoice')"
                  :custom-label="invoiceWithAmount"
                  track-by="id"
                  class="multi-select-item"
                  @search-change="onInvoiceSearch"
                  @select="addInvoice"
                  @remove="removeInvoice"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.date_time') }}</label><span class="text-danger"> *</span>
                <base-date-picker
                  v-model="formData.date_time"
                  format="Y-m-d"
                  :invalid="v$.formData.date_time.$error"
                  :calendar-button="true"
                  calendar-button-icon="calendar"
                  @change="v$.formData.date_time.$touch()"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.time') }}</label><span class="text-danger"> *</span>
                <div class="base-date-input">
                  <vue-timepicker
                    v-model="formData.time"
                    format="hh:mm A"
                    :hide-clear-button="true"
                    @change="v$.formData.time.$touch()">
                    <template v-slot:icon>
                      <span class="vdp-datepicker__calendar-button input-group-prepend">
                        <span>
                          <font-awesome-icon id="time-icon" :icon="['fas', 'clock']"/>
                        </span>
                      </span>
                    </template>
                  </vue-timepicker>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('dispatch.person') }}</label>
                <base-input
                  v-model.trim="formData.person"
                  focus
                  type="text"
                  name="person"
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
              <div class="form-group collapse-button-container">
                <base-button
                  id="submit-dispatch"
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
    <dispatch-print-preview ref="printPreview" :dispatch="formData" :invoices="invoice.filter(Boolean)" @close="closePrintPreview" />
  </div>
</template>
<style src="../../../css/vue-timepicker-theme.css"></style>
<script>
import useVuelidate from '@vuelidate/core'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { required, minLength, numeric, minValue, maxLength } from '@vuelidate/validators';
import VueTimepicker from 'vue3-timepicker'
import 'vue3-timepicker/dist/VueTimepicker.css'
import getTime from 'date-fns/fp/getTime'
import DispatchPrintPreview from './DispatchPrintPreview.vue'

export default {
  components: { VueTimepicker, DispatchPrintPreview },
  setup () {
    return { v$: useVuelidate() }
  },
  data () {
    return {
      isLoading: false,
      returnAfterPrint: false,
      title: 'Add Dispatch',
      formData: {
        name: '',
        invoice_id: [],
        date_time: new Date(),
        transport: '',
        person: '',
        time: '',
        status: {
          id: 2,
          name: 'Sent',
        },
        all_selected_dispatch: []
      },
      invoice: [],
      invoiceList: [],
      invoiceSearchLoading: false,
      invoiceSearchTimer: null,
      assignToBeDispatch: false,
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
    let current = new Date();
    this.formData.time = current.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
    });
  },
  unmounted() {
    this.resetSelectedDispatch()
    this.resetSelectedToBeDispatch()
  },
  validations: {
    formData: {
      date_time: {
        required,
      },
      time: {
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
      if (index !== -1) {
        this.formData.invoice_id.splice(index, 1)
      }
    },
    invoiceWithAmount ({ invoice_number, due_amount, master}) {
      let invoiceArr = this.invoice;
      if (! invoiceArr.length) {
        invoiceArr = this.invoiceList
      }
      if (invoiceArr) {
        // master can be null for an invoice whose account_master_id points
        // at a deleted/missing party - don't let that crash the whole picker.
        let count = master ? invoiceArr.filter(i => i.account_master_id === master.id).length : 0;
        let masterName = master ? master.name : 'Unknown party';
        return `${invoice_number} (₹ ${parseFloat(due_amount).toFixed(2)}) - (${masterName}) * ${count}`
      }
    },
    loadInvoice() {
      this.invoice = []
      this.formData.invoice_id.map(i => {
        let findFromList = this.invoiceList.find(j => j.id === parseInt(i));
        this.invoice.push(findFromList);
      })
    },
    async printDispatch () {
      await this.$nextTick()
      this.$refs.printPreview.open()
    },
    closePrintPreview () {
      if (this.returnAfterPrint) {
        this.$router.push('/dispatch')
      }
    },
    async loadEditData () {
      let response = await this.editDispatch(this.$route.params.id)
      this.formData = response.data.dispatch
      this.formData.status = {
          id: 2,
          name: 'Sent',
        };
      await this.ensureInvoicesLoaded(this.formData.invoice_id)
      this.loadInvoice()
    },
    async loadIsToBeDispatch() {
      let response = await this.editToBeDispatch(this.isToBeDispatch.toString())
      this.formData = response.data.dispatch[0]
      this.formData.status = {
          id: 2,
          name: 'Sent',
        };
      let invoiceId = []
      response.data.dispatch.map(each => each.invoice_id.map(i => invoiceId.push(i)))
      this.formData.invoice_id = invoiceId
      await this.ensureInvoicesLoaded(this.formData.invoice_id)
      this.loadInvoice()
      this.assignToBeDispatch = true
      this.formData['all_selected_dispatch'] = [];
      response.data.dispatch.map(each => this.formData.all_selected_dispatch.push(each.id))
    },
    // The invoice picker's options list (fetchInvoices) only returns bills
    // still pending dispatch - once a dispatch is sent, its invoice(s) get
    // marked COMPLETED and drop out of that list, so re-opening the edit
    // page for an already-sent dispatch would otherwise have nothing to
    // match against and render the invoice field empty. Top up invoiceList
    // with whichever ids are actually assigned to this dispatch, regardless
    // of their status, before trying to resolve them in loadInvoice().
    async ensureInvoicesLoaded (invoiceIds) {
      let missingIds = (invoiceIds || [])
        .map(i => parseInt(i))
        .filter(id => !isNaN(id) && !this.invoiceList.some(inv => inv.id === id))
      if (! missingIds.length) {
        return
      }
      let response = await axios.get(`/api/dispatch/invoices`, { params: { include_ids: missingIds.join(',') } })
      if (response.data && response.data.invoices) {
        let existingIds = this.invoiceList.map(inv => inv.id)
        let toAdd = response.data.invoices.filter(inv => ! existingIds.includes(inv.id))
        this.invoiceList = this.invoiceList.concat(toAdd)
      }
    },
    async fetchInvoices () {
      // No search/limit here - this is the default page-load fetch, so it
      // only shows the newest 50 pending invoices (the endpoint's default
      // cap). Use the search box to find anything older/more specific -
      // see onInvoiceSearch(). This endpoint used to load every pending
      // invoice unbounded, which crashed (memory) or timed out (gateway)
      // once a company's backlog grew into the tens of thousands.
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
    // Search-as-you-type for the invoice picker (create mode only - it's
    // disabled while editing). Debounced so we're not firing a request per
    // keystroke.
    onInvoiceSearch (query) {
      clearTimeout(this.invoiceSearchTimer)
      this.invoiceSearchTimer = setTimeout(async () => {
        this.invoiceSearchLoading = true
        try {
          let response = await axios.get(`/api/dispatch/invoices`, { params: { search: query } })
          if (response.data) {
            this.invoiceList = response.data.invoices
          }
        } finally {
          this.invoiceSearchLoading = false
        }
      }, 350)
    },
    async showDispatchPopup () {
      const confirmed = await swal({
        title: this.$t('dispatch.invoice_report_title'),
        text: this.$t('dispatch.invoice_report_text'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      })
      if (confirmed) {
        this.returnAfterPrint = true
        await this.printDispatch()
      } else {
        this.$router.push('/dispatch')
      }
    },
    async submitDispatch () {
      this.v$.formData.$touch()
      if (this.v$.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
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
          await this.showDispatchPopup()
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
