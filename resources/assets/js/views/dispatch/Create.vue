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
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitDispatch">
            <div class="card-body" id="to_print">
              <div class="form-group" v-if="invoiceList && invoiceList.length && !change_invoice">
                <label class="form-label">{{ $t('receipts.invoice') }}</label>
                <base-select
                  :multiple="true"
                  v-model="invoice"
                  :show-pointer="false"
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
              <div class="form-group" v-if="change_invoice">
                <span class="ml-2" v-for="(value, index) in filterInvoice " :key="index" >
                  {{ '('+value.data[0].invoice_number + ' - ' + '(' + value.data[0].master.name+')' + ' * ' + value.count +')' }}
                </span>
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
                <label class="control-label">{{ $t('dispatch.time') }}</label><span class="text-danger"> *</span>
                <div class="base-date-input">
                  <vue-timepicker
                    v-model="formData.time"
                    format="hh:mm A"
                    :hide-clear-button="true"
                    @change="$v.formData.time.$touch()">
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
              <div class="form-group">
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
  </div>
</template>
<style>
.vue__time-picker {
  width: 100%;
}
.base-date-input .vue__time-picker input.display-time {
  width: 100%;
  height: 40px;
  background: #FFFFFF;
  border: 1px solid #EBF1FA;
  box-sizing: border-box;
  border-radius: 5px ;
  display: inline-block;
  padding: 0px 6px 0px 40px ;
  font-size: 1rem;
  line-height: 1.4;
  cursor: pointer;
}
</style>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
const { required, minLength, numeric, minValue, maxLength } = require('vuelidate/lib/validators')
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'
import getTime from 'date-fns/fp/getTime/index'

export default {
  components: { VueTimepicker },
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      filterInvoice: [],
      change_invoice: false,
      invoice_count: '',
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
  destroyed() {
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
      if (index) {
        this.formData.invoice_id.splice(index, 1)
      }
    },
    invoiceWithAmount ({ invoice_number, due_amount, master}) {
      let count = this.invoice.filter(i => i.account_master_id === master.id).length;
      return `${invoice_number} (₹ ${parseFloat(due_amount).toFixed(2)}) - (${master.name}) * ${count}`
    },
    loadInvoice() {
      this.invoice = []
      this.formData.invoice_id.map(i => {
        let findFromList = this.invoiceList.find(j => j.id === parseInt(i));
        this.invoice.push(findFromList);
      })
      let current = new Date();
      this.formData.time = current.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      });
    },
    printDispatch() {
      return printJS({
            onPrintDialogClose: () => {
              this.$router.push('/dispatch')
            },
            printable: 'to_print',
            type: 'html',
            ignoreElements: ['submit-dispatch', 'print-dispatch', 'time-icon', 'select-date-icon', 'clear-icon', 'caret', 'tag_icon', 'hide_tags'],
            scanStyles: true,
            targetStyles: ['*'],
            style: '.base-date-input .vue__time-picker input.display-time {width: 100%;height: 40px;background: #FFFFFF;border: 1px solid #EBF1FA;box-sizing: border-box;border-radius: 5px;display: inline-block;padding: 0px 6px 0px 40px;font-size: 1rem;line-height: 1.4;cursor: pointer;}.base-input .input-field {width: 100%;height: 40px;padding: 8px 13px;text-align: left;background: #FFFFFF;border: 1px solid #EBF1FA;box-sizing: border-box;border-radius: 5px;font-style: normal;font-weight: 400;font-size: 14px;line-height: 21px; margin-bottom:5px}.multiselect__tag {position: relative;display: inline-block;padding: 4px 26px 4px 10px;border-radius: 5px;margin-right: 10px;color: #fff;line-height: 1;background: #41b883;margin-bottom: 5px;white-space: nowrap;overflow: hidden;max-width: 100%;text-overflow: ellipsis;}.skin-crater .multiselect .multiselect__tags-wrap .multiselect__tag {background: #1eaec5;color: #fff;}.base-date-input .date-field {width: 100%;height: 40px;background: #FFFFFF;border: 1px solid #EBF1FA;box-sizing: border-box;border-radius: 5px;display: inline-block;padding: 0px 6px 0px 40px;font-size: 1rem;line-height: 1.4;cursor: pointer; color:#333}.multiselect__tags {min-height: 40px;display: block;padding: 8px 40px 0 8px;border-radius: 5px;border: 1px solid #EBF1FA;background: #fff;font-size: 14px;  color:#333 } .multiselect__tags-wrap .multiselect__select span { color:#000 !important}'
          })
    },
    async loadEditData () {
      let response = await this.editDispatch(this.$route.params.id)
      this.formData = response.data.dispatch
      this.formData.status = {
          id: 2,
          name: 'Sent',
        };
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
      this.loadInvoice()
      this.assignToBeDispatch = true
      this.formData['all_selected_dispatch'] = [];
      response.data.dispatch.map(each => this.formData.all_selected_dispatch.push(each.id))
    },
    async fetchInvoices () {
      let response = await axios.get(`/api/dispatch/invoices`)
      if (response.data) {
        console.log(response.data.invoices)
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
    async showDispatchPopup (invoice_id, invoices_master_id) {
      this.change_invoice = true;
      this.filterInvoice =  this.invoice.map(node=>{
           let new_node = {};
            new_node.count = this.invoice.filter(i => i.account_master_id === node.account_master_id).length;
            new_node.data = this.invoice.filter(i => i.account_master_id === node.account_master_id).sort((a, b) => {
              return new Date(a.created_at) - new Date(b.created_at);
            });
           new_node.account_master_id = node.account_master_id;
           new_node.name = node.master.name;
           new_node.id = node.id;
          return new_node;
      });
      this.filterInvoice = this.filterInvoice.filter((v,i,a)=>a.findIndex(v2=>(v2.account_master_id===v.account_master_id))===i);
      swal({
        title: this.$t('dispatch.invoice_report_title'),
        text: this.$t('dispatch.invoice_report_text'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      }).then(async (success) => {
        if (success) {
          this.printDispatch();
        } else {
          this.resetSelectedDispatch()
          this.resetSelectedToBeDispatch()
          this.$router.push('/dispatch')
        }
        this.change_invoice = false;
      })
    },
    async submitDispatch () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
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
          this.showDispatchPopup(response.data.dispatch.id, response.data.invoices)
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
