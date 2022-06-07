<template>
  <div class="invoice-create-page main-content">
    <div class="page-header">
      <div class="page-actions row">
        <router-link slot="item-title" class="col-xs-2" to="/invoices">
          <base-button size="large" icon="envelope" color="theme">
            {{ $t('invoices.title') }}
          </base-button>
        </router-link>
      </div>
    </div>
    <form v-if="!initLoading" action="" @submit.prevent="submitInvoiceData" class="ipad-width">
      <div class="page-header">
        <h3 v-if="$route.name === 'invoices.edit'" class="page-title">{{ $t('invoices.edit_invoice') }}</h3>
        <h3 v-else class="page-title">{{ $t('invoices.new_invoice') }} </h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $tc('invoices.invoice', 2) }}</router-link></li>
          <li v-if="$route.name === 'invoices.edit'" class="breadcrumb-item">{{ $t('invoices.edit_invoice') }}</li>
          <li v-else class="breadcrumb-item">{{ $t('invoices.new_invoice') }}</li>
        </ol>
      </div>
      <div class="row invoice-input-group">
        <div class="col-md-6 invoice-customer-container mb-2">
          <label class="form-label">{{ $t('invoices.estimate-list') }}</label>
            <base-select
              v-model="setEstimate"
              :options="estimateList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              :disabled="isDisabled"
              :placeholder="$t('receipts.select_a_list')"
              label="estimate_number"
              track-by="id"
            />
        </div>
        <div class="col-md-6 invoice-customer-container mb-2">
          <label class="form-label">{{ $t('receipts.list') }}</label><span class="text-danger"> *</span>
            <base-select
              v-model="setInvoiceDebtor"
              :invalid="$v.newInvoice.debtors.$error"
              :options="sundryDebtorsList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              :disabled="isDisabled"
              :placeholder="$t('receipts.select_a_list')"
              label="name"
              track-by="id"
            />
            <div v-if="$v.newInvoice.debtors.$error">
              <span v-if="!$v.newInvoice.debtors.required" class="text-danger">{{ $tc('validation.required') }}</span>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 collapse-input">
          <label>{{ $tc('invoices.invoice',1) }} {{ $t('invoices.date') }}<span class="text-danger"> * </span></label>
          <input
            v-model="newInvoice.invoice_date"
            type="date"
            data-date=""
            data-date-format="DD/MM/YYYY"
            class="base-prefix-input"
            @change="$v.newInvoice.invoice_date.$touch()"
            :disabled="isDisabled"
          />
          <span v-if="$v.newInvoice.invoice_date.$error && !$v.newInvoice.invoice_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
        </div>
        <div class="col-md-4 col-sm-6 collapse-input">
          <label>{{ $t('invoices.invoice_number') }}<span class="text-danger"> * </span></label>
          <base-prefix-input
            v-model="invoiceNumAttribute"
            :invalid="$v.invoiceNumAttribute.$error"
            :prefix="invoicePrefix"
            icon="hashtag"
            @input="$v.invoiceNumAttribute.$touch()"
            :prefix-width="55"
            :disabled="true"
          />
          <span v-show="$v.invoiceNumAttribute.$error && !$v.invoiceNumAttribute.required" class="text-danger mt-1"> {{ $tc('validation.required') }}  </span>
        </div>
        <div class="col-md-4 col-sm-6 collapse-input">
          <label>{{ $t('invoices.ref_number') }}</label>
          <base-input
            v-model="newInvoice.reference_number"
            :invalid="$v.newInvoice.reference_number.$error"
            icon="hashtag"
            @input="$v.newInvoice.reference_number.$touch()"
            :disabled="isDisabled"
          />
          <div v-if="$v.newInvoice.reference_number.$error" class="text-danger">{{ $tc('validation.ref_number_maxlength') }}</div>
        </div>
      </div>
      <div class="table-responsive">
      <table class="table item-table">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 10%;">
          <col style="width: 15%;">
          <col style="width: 15%;">
          <col v-if="discountPerInventory === 'YES'" style="width: 15%;">
          <col style="width: 15%;">
        </colgroup>
        <thead class="item-table-header">
          <tr>
            <th class="text-left">
              <span class="column-heading heading-1 item-heading">
                {{ $tc('invoices.inventory.title',2) }}
              </span>
            </th>
            <th class="text-right">
              <span class="column-heading">
                {{ $t('invoices.inventory.quantity') }}
              </span>
            </th>
            <th class="text-left">
              <span class="column-heading">
                {{ $t('invoices.inventory.price') }}
              </span>
            </th>
            <th class="text-left">
              <span class="column-heading">
                {{ $t('invoices.inventory.sale_price') }}
              </span>
            </th>
            <th v-if="discountPerInventory === 'YES'" class="text-right">
              <span class="column-heading">
                {{ $t('invoices.inventory.discount') }}
              </span>
            </th>
            <th class="text-right">
              <span class="column-heading amount-heading">
                {{ $t('invoices.inventory.amount') }}
              </span>
            </th>
          </tr>
        </thead>
        <draggable v-model="inventoryBind" class="item-body" tag="tbody" handle=".handle">
          <invoice-inventory
            v-for="(each, index) in inventoryBind"
            ref="invoiceInventory"
            :key="each.name+index"
            :index="index"
            :inventory-data="each"
            :currency="currency"
            :tax-per-inventory="taxPerInventory"
            :discount-per-inventory="discountPerInventory"
            :is-disable="$route.query.d === 'true'"
            :inventory-type="'invoice'"
            :inventory-list="inventoryList"
            @remove="removeInventory"
            @update="updateInventoryBounce"
            @inventoryValidate="checkInventoryData"
            @endlist="showEndList"
          />
        </draggable>
      </table>
      </div>
      <button v-if="showAddNewInventory" class="add-item-action add-invoice-item" :disabled="isDisabled" @click="addInventory">
        <font-awesome-icon icon="shopping-basket" class="mr-2"/>
        {{ $t('invoices.add_item') }}
      </button>
      <button v-if="showEndOfList" @click="removeEndOfList" class="btn btn-primary" style="margin: 10px">
        End Of List
      </button>

      <div class="invoice-foot">
        <div>
          <label>{{ $t('invoices.notes') }}</label>
          <base-text-area
            v-model="newInvoice.notes"
            rows="3"
            cols="50"
            @input="$v.newInvoice.notes.$touch()"
          />
          <div v-if="$v.newInvoice.notes.$error">
            <span v-if="!$v.newInvoice.notes.maxLength" class="text-danger">{{ $t('validation.notes_maxlength') }}</span>
          </div>
        </div>

        <div class="invoice-total">
          <div class="section">
            <label class="invoice-label">{{ $t('invoices.quantity') }}</label>
            <label class="">
              <div v-html="totalQuantity(inventoryBind)" />
            </label>
          </div>
          <div class="section">
            <label class="invoice-label">{{ $t('invoices.sub_total') }}</label>
            <label class="invoice-amount">
              ₹ {{ subtotal }}
            </label>
          </div>
          <div v-if="discountPerInventory === 'NO' || discountPerInventory === null" class="section mt-2">
            <label class="invoice-label">{{ $t('invoices.discount') }}</label>
            <div
              class="btn-group discount-drop-down"
              role="group"
            >
              <base-input
                v-model="discount"
                :invalid="$v.newInvoice.discount_val.$error"
                input-class="item-discount"
                @input="$v.newInvoice.discount_val.$touch()"
              />
              <v-dropdown :show-arrow="false">
                <button
                  slot="activator"
                  type="button"
                  class="btn item-dropdown dropdown-toggle"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  {{ newInvoice.discount_type == 'fixed' ? currency.symbol : '%' }}
                </button>
                <v-dropdown-item>
                  <a class="dropdown-item" href="#" @click.prevent="selectFixed">
                    {{ $t('general.fixed') }}
                  </a>
                </v-dropdown-item>
                <v-dropdown-item>
                  <a class="dropdown-item" href="#" @click.prevent="selectPercentage">
                    {{ $t('general.percentage') }}
                  </a>
                </v-dropdown-item>
              </v-dropdown>
            </div>
          </div>

          <div v-if="taxPerInventory === 'NO' || taxPerInventory === null">
            <tax
              v-for="(tax, index) in newInvoice.taxes"
              :index="index"
              :total="subtotalWithDiscount"
              :key="tax.id"
              :tax="tax"
              :taxes="newInvoice.taxes"
              :currency="currency"
              :total-tax="totalSimpleTax"
              @remove="removeInvoiceTax"
              @update="updateTax"
            />
          </div>

          <base-popup v-if="taxPerInventory === 'NO' || taxPerInventory === null" ref="taxModal" class="tax-selector">
            <div slot="activator" class="float-right">
              + {{ $t('invoices.add_tax') }}
            </div>
            <tax-select-popup :taxes="newInvoice.taxes" @select="onSelectTax"/>
          </base-popup>

          <div class="section border-top mt-3">
            <label class="invoice-label">{{ $t('invoices.total') }} {{ $t('invoices.amount') }}:</label>
            <label class="invoice-amount total">
              ₹ {{ total }}
            </label>
          </div>
        </div>
      </div>
      <div class="page-actions row" style="margin-left: 3px">
          <!-- <a v-if="$route.name === 'invoices.edit'" :href="`/invoices/pdf/${newInvoice.unique_hash}`" target="_blank" class="mr-3 invoice-action-btn base-button btn btn-outline-primary default-size" outline color="theme">
            {{ $t('general.view_pdf') }}
          </a> -->
          <base-button
            :loading="isLoading"
            :disabled="isLoading"
            icon="save"
            color="theme"
            class="invoice-action-btn"
            type="submit">
            {{ $t('invoices.save_invoice') }}
          </base-button>
        </div>
    </form>
    <base-loader v-else />
  </div>
</template>
<style scoped>
input.base-prefix-input:disabled {
    background: rgba(59, 59, 59, 0.3) !important;
    border-color: rgba(118, 118, 118, 0.3) !important;
}
.add-invoice-item{
  border: 0px
}
.add-invoice-item:focus {
  border: 1px solid salmon
}
.table-responsive {
  overflow-x: inherit ;
}
@media screen and (max-width:400px) {
  .heading-1 {
    padding: 5px 180px;
  }
  .table-responsive {
    overflow-x: auto !important;
  }
  .multiselect__content-wrapper {
    overflow-x: visible !important;
  }
}
@media screen and (min-width: 768px) and (max-width: 1030px) {
  .ipad-width {
    width: 133%;
  }
}
</style>
<script>
import draggable from 'vuedraggable'
import MultiSelect from 'vue-multiselect'
import InvoiceInventory from './Inventory'
import InvoiceStub from '../../stub/invoice'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { validationMixin } from 'vuelidate'
import Guid from 'guid'
import TaxStub from '../../stub/tax'
import Tax from './InvoiceTax'
const { required, between, maxLength, numeric } = require('vuelidate/lib/validators')

export default {
  components: {
    InvoiceInventory,
    MultiSelect,
    Tax,
    draggable
  },
  mixins: [validationMixin],
  data () {
    return {
      newInvoice: {
        invoice_date: null,
        invoice_number: null,
        user_id: null,
        invoice_template_id: 1,
        sub_total: null,
        total: null,
        tax: null,
        notes: null,
        discount_type: 'fixed',
        discount_val: 0,
        discount: 0,
        reference_number: null,
        inventories: [{
          ...InvoiceStub,
          taxes: [{...TaxStub, id: Guid.raw()}]
        }],
        taxes: [],
        debtors: '',
        estimate: '',
      },
      customers: [],
      inventoryList: [],
      invoiceTemplates: [],
      selectedCurrency: '',
      taxPerInventory: null,
      discountPerInventory: null,
      initLoading: false,
      isLoading: false,
      maxDiscount: 0,
      invoicePrefix: null,
      invoiceNumAttribute: null,
      role: this.$store.state.user.currentUser.role,
      sundryDebtorsList: [], //List of Sundry Debitor name
      estimateList: [], //List of estimates
      isDisabled: false,
      url: null,
      siteURL: null,
      showAddNewInventory: true,
      showEndOfList: false,
    }
  },
  validations () {
    return {
      newInvoice: {
        invoice_date: {
          required
        },
        discount_val: {
          between: between(0, this.subtotal)
        },
        notes: {
          maxLength: maxLength(255)
        },
        reference_number: {
          maxLength: maxLength(255)
        },
        debtors: {
          required
        }
      },
      invoiceNumAttribute: {
        required
      }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrency'
    ]),
    ...mapGetters('invoice', [
      'getTemplateId',
      //'selectedCustomer'
    ]),
    ...mapGetters('user', {
      user: 'currentUser'
    }),
    currency () {
      return this.selectedCurrency
    },
    subtotalWithDiscount () {
      if (this.newInvoice.discount_val) {
        return this.subtotal - this.newInvoice.discount_val
      }
      return this.subtotal
    },
    total () {
      return this.subtotalWithDiscount + this.totalTax
    },
    subtotal () {
      let inventory = this.newInvoice.inventories;
      if (inventory.length) {
        return inventory.reduce(function (a, b) {
                return a + b['total']
              }, 0)
      }
      return 0
    },
    discount: {
      get: function () {
        return this.newInvoice.discount
      },
      set: function (newValue) {
        if (this.newInvoice.discount_type === 'percentage') {
          this.newInvoice.discount_val = (this.subtotal * newValue)
        } else {
          this.newInvoice.discount_val = newValue
        }
        this.newInvoice.discount = newValue
      }
    },
    totalSimpleTax () {
      return window._.sumBy(this.newInvoice.taxes, function (tax) {
        if (!tax.compound_tax) {
          return tax.amount
        }
        return 0
      })
    },
    totalCompoundTax () {
      return window._.sumBy(this.newInvoice.taxes, function (tax) {
        if (tax.compound_tax) {
          return tax.amount
        }
        return 0
      })
    },
    totalTax () {
      if (this.taxPerInventory === 'NO' || this.taxPerInventory === null) {
        return this.totalSimpleTax + this.totalCompoundTax
      }

      return window._.sumBy(this.newInvoice.inventories, function (tax) {
        return tax.tax
      })
    },
    setInvoiceDebtor: {
      cache: false,
      get() {
        return this.newInvoice.debtors
      },
      set(value) {
        this.searchDebtorRefNumber(value)
        this.newInvoice.debtors = value
      },
    },
    setEstimate: {
      cache: false,
      get() {
        return this.newInvoice.estimate
      },
      set(value) {
        this.isDisabled = true
        this.newInvoice.estimate = value
        this.getInvoiceFromEstimate()
      }
    },
    inventoryBind() {
      return this.newInvoice.inventories
    }
  },
  watch: {
    subtotal (newValue) {
      if (this.newInvoice.discount_type === 'percentage') {
        this.newInvoice.discount_val = (this.newInvoice.discount * newValue)
      }
    }
  },
  created () {
    this.loadData()
    this.fetchInitialInventory()
    window.hub.$on('newTax', this.onSelectTax)
    this.updateInventoryBounce = _.debounce((data) => {
      this.updateInventory(data);
    }, 500);
  },
  methods: {
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('invoice', [
      'addInvoice',
      'fetchCreateInvoice',
      'getInvoiceEstimate',
      'fetchInvoice',
      'updateInvoice',
      'fetchReferenceNumber',
    ]),
    ...mapActions('inventory', [
      'fetchAllInventory'
    ]),
    totalQuantity(inventory){
      if (inventory.length) {
        return inventory.map(i => parseInt(i.quantity)).reduce((a,b) => a + b)
      }
      return 0
    },
    selectFixed () {
      if (this.newInvoice.discount_type === 'fixed') {
        return
      }
      this.newInvoice.discount_val = this.newInvoice.discount
      this.newInvoice.discount_type = 'fixed'
    },
    selectPercentage () {
      if (this.newInvoice.discount_type === 'percentage') {
        return
      }
      this.newInvoice.discount_val = (this.subtotal * this.newInvoice.discount)
      this.newInvoice.discount_type = 'percentage'
    },
    updateTax (data) {
      Object.assign(this.newInvoice.taxes[data.index], {...data.inventory})
    },
    async fetchInitialInventory () {
      await this.fetchAllInventory({
        filter: {},
        orderByField: '',
        orderBy: ''
      })
    },
    async loadData () {
      if (this.$route.name === 'invoices.edit') {
        this.initLoading = true
        let response = await this.fetchInvoice(this.$route.params.id)
        if (this.$route.query && this.$route.query.nondis === 'false') {
          this.isDisabled = true
        } else {
          this.isDisabled = false
        }
        if (response.data) {
          this.newInvoice = response.data.invoice
          this.inventoryList = response.data.invoice.inventories
          this.newInvoice.invoice_date = moment(response.data.invoice.invoice_date).format('YYYY-MM-DD')
          this.discountPerInventory = response.data.discount_per_inventory
          this.taxPerInventory = response.data.tax_per_inventory
          this.selectedCurrency = this.defaultCurrency
          this.invoiceTemplates = response.data.invoiceTemplates
          this.invoicePrefix = response.data.invoice_prefix
          this.invoiceNumAttribute = response.data.invoiceNumber
          this.newInvoice.debtors = response.data.sundryDebtorsList[0]
          this.newInvoice.estimate = response.data.estimateList[0]
        }
        this.initLoading = false
        return
      }

      this.initLoading = true
      let response = await this.fetchCreateInvoice()
      if (response.data) {
        this.discountPerInventory = response.data.discount_per_inventory
        this.taxPerInventory = response.data.tax_per_inventory
        this.selectedCurrency = this.defaultCurrency
        this.invoiceTemplates = response.data.invoiceTemplates
        this.newInvoice.invoice_date = response.data.invoice_today_date
        this.inventoryList = response.data.inventories
        this.invoicePrefix = response.data.invoice_prefix
        this.invoiceNumAttribute = response.data.nextInvoiceNumberAttribute
        this.sundryDebtorsList = response.data.sundryDebtorsList

        response.data.estimateList.map(i => {
          let obj = {}
          let debtor = this.sundryDebtorsList.find(a => i.account_master_id === a.id);
          obj['id'] = i.id;
          obj['total'] = i.total;
          obj['estimate_number'] = i.estimate_number + (debtor ? (' - ' + debtor.name) : '');

          this.estimateList.push(obj)
        })
      }
      this.initLoading = false
    },
    openTemplateModal () {
      this.openModal({
        'title': this.$t('general.choose_template'),
        'componentName': 'InvoiceTemplate',
        'data': this.invoiceTemplates
      })
    },
    addInventory () {
      this.inventoryBind.push({...InvoiceStub, taxes: [{...TaxStub, id: Guid.raw()}]})
      this.$nextTick(() => {
        this.$refs.invoiceInventory[this.inventoryBind.length-1].$el.focus()
        this.$refs.invoiceInventory[this.inventoryBind.length-1].$children[0].$refs.baseSelect.$el.focus()
      })
    },
    removeInventory (index) {
      this.inventoryBind.splice(index, 1)
      this.inventoryBind.filter(i => i).map((each, key) => {
        each['index'] = key
        this.updateInventory(each)
      })
    },
    updateInventory (data) {
      if (data.inventory && !data.inventory.inventory_id) {
        window.toastr['error']('Inventory item does not exist or not selected')
        return false
      }
      Object.assign(this.inventoryBind[data.index], {...data.inventory})
      this.$nextTick(() => {
        this.$refs.invoiceInventory[data.index].$el.focus()
        if (data.updatingInput === 'sale_price') {
          this.$refs.invoiceInventory[data.index].$children[3].$refs.baseInput.focus()
        }
        if (data.updatingInput === 'quantity') {
          this.$refs.invoiceInventory[data.index].$children[1].$refs.baseInput.focus()
        }
      })
    },
    submitInvoiceData () {
      if (!this.checkValid()) {
        return false
      }
      this.newInvoice.invoice_number = this.invoicePrefix + '-' + this.invoiceNumAttribute

      let data = {
        ...this.newInvoice,
        invoice_date: moment(this.newInvoice.invoice_date).format('DD/MM/YYYY'),
        sub_total: this.subtotal,
        total: this.total,
        tax: this.totalTax,
        user_id: this.user.id,
        invoice_template_id: this.getTemplateId,
      }

      if (this.$route.name === 'invoices.edit') {
        this.submitUpdate(data)
        return
      }

      this.submitSave(data)
    },
    reset() {
      setTimeout(() => {
        window.location.reload()
        this.isLoading = false
      }, 1000)
    },
    async showInvoicePopup (invoice_id) {
      swal({
        title: this.$t('invoices.invoice_report_title'),
        text: this.$t('invoices.invoice_report_text'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      }).then(async (success) => {
        if (success) {
          this.siteURL = `/reports/invoice/${invoice_id}`
          this.url = `${this.siteURL}?company_id=${this.user.company_id}`
          printJS({
            printable: this.url,
            type: 'pdf',
            onPrintDialogClose: () => {
              this.reset();
            }
          })
        } else {
          this.reset()
        }
      })
    },
    submitSave (data) {
      if (this.isLoading) {
        return false
      }
      this.isLoading = true
      this.addInvoice(data).then((res) => {
        if (res.data) {
          window.toastr['success'](this.$t('invoices.created_message'))
          //this.$router.push('/invoices/create')
          this.showInvoicePopup(res.data.invoice.id)
        }
      }).catch((err) => {
        this.isLoading = false
        if (err) {
          window.toastr['error'](err)
          return true
        }
        console.log(err)
      })
    },
    submitUpdate (data) {
      if (this.isLoading) {
        return false
      }
      this.isLoading = true
      this.updateInvoice(data).then((res) => {
        if (res.data.success) {
          window.toastr['success'](this.$t('invoices.updated_message'))
          this.isLoading = false
          this.showInvoicePopup(res.data.invoice.id)
        }

        if (res.data.error === 'invalid_due_amount') {
          this.isLoading = false
          window.toastr['error'](this.$t('invoices.invalid_due_amount_message'))
        }

      }).catch((err) => {
        this.isLoading = false
        if (err) {
          window.toastr['error'](err)
          return true
        }
        console.log(err)
      })
    },
    checkInventoryData (index, isValid) {
      this.newInvoice.inventories[index].valid = isValid
    },
    onSelectTax (selectedTax) {
      let amount = 0

      if (selectedTax.compound_tax && this.subtotalWithDiscount) {
        amount = ((this.subtotalWithDiscount + this.totalSimpleTax) * selectedTax.percent)
      } else if (this.subtotalWithDiscount && selectedTax.percent) {
        amount = (this.subtotalWithDiscount * selectedTax.percent)
      }

      this.newInvoice.taxes.push({
        ...TaxStub,
        id: Guid.raw(),
        name: selectedTax.name,
        percent: selectedTax.percent,
        compound_tax: selectedTax.compound_tax,
        tax_type_id: selectedTax.id,
        amount
      })

      this.$refs.taxModal.close()
    },
    removeInvoiceTax (index) {
      this.newInvoice.taxes.splice(index, 1)
    },
    checkValid () {
      this.$v.newInvoice.$touch()
      window.hub.$emit('checkInventory')
      let isValid = true
      this.newInvoice.inventories.forEach((each) => {
        if (!each.valid) {
          isValid = false
        }
      })
      if (this.$v.newInvoice.$invalid === false && isValid === true) {
        isValid = true
      }
      return isValid
    },
    async searchDebtorRefNumber(data) {
       this.newInvoice.reference_number = null;
       let response = await this.fetchReferenceNumber(data)
        if (response.data && response.data.invoice) {
          this.newInvoice.reference_number = response.data.invoice.reference_number
        } else {
          this.newInvoice.reference_number = this.invoiceNumAttribute
        }
    },
    showEndList(val) {
      this.showAddNewInventory = !val;
      this.showEndOfList = val;
      this.inventoryBind.splice(this.inventoryBind.length - 1, 1);
    },
    removeEndOfList() {
      this.showEndOfList = false;
      this.showAddNewInventory = true;
    },
    async getInvoiceFromEstimate() {
      let resp = await this.getInvoiceEstimate(this.newInvoice.estimate.id)
      let invoice = resp.data.estimate
      let inventory = invoice.items.map(i => {
        i.sale_price = i.sale_price ? i.sale_price : i.price
        i.sub_total = i.total
        return i
      })

      //set invoice data
      this.newInvoice = {
        invoice_date: moment(invoice.estimate_date).format('YYYY-MM-DD'),
        invoice_number: this.invoicePrefix + '-' + this.invoiceNumAttribute,
        user_id: invoice.user_id,
        invoice_template_id: 1,
        sub_total: invoice.sub_total,
        total: invoice.total,
        tax: invoice.tax,
        notes: invoice.notes,
        discount_type: 'fixed',
        discount_val: 0,
        discount: 0,
        inventories: inventory,
        taxes: [],
        reference_number: null,
        debtors: this.sundryDebtorsList.find(i => i.id === invoice.account_master_id),
        estimate: this.newInvoice.estimate
      };

      //set reference number
      this.searchDebtorRefNumber(invoice.account_master_id)
    }
  }
}
</script>
