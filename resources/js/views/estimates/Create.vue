<template>
  <div class="estimate-create-page main-content">
    <div class="page-header">
      <div class="page-actions row">
        <router-link slot="item-title" class="col-xs-2" to="/estimates">
          <base-button size="large" icon="envelope" color="theme">
            {{ $t('estimates.title') }}
          </base-button>
        </router-link>
      </div>
    </div>
    <form v-if="!initLoading" action="" @submit.prevent="submitEstimateData">
      <div class="page-header">
        <h3 v-if="$route.name === 'estimates.edit'" class="page-title">{{ $t('estimates.edit_estimate') }}</h3>
        <h3 v-else class="page-title">{{ $t('estimates.new_estimate') }} </h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/estimates">{{ $tc('estimates.estimate', 2) }}</router-link></li>
          <li v-if="$route.name === 'estimates.edit'" class="breadcrumb-item">{{ $t('estimates.edit_estimate') }}</li>
          <li v-else class="breadcrumb-item">{{ $t('estimates.new_estimate') }}</li>
        </ol>
      </div>
      <div class="row estimate-input-group">
        <div class="col-md-5 estimate-customer-container">
          <label class="form-label">{{ $t('receipts.list') }}</label><span class="text-danger"> *</span>
            <base-select
              v-model="setEstimateDebtor"
              :invalid="$v.newEstimate.debtors.$error"
              :options="sundryDebtorsList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              :disabled="$route.name === 'estimates.edit'"
              :placeholder="$t('receipts.select_a_list')"
              label="name"
              track-by="id"
            />
            <div v-if="$v.newEstimate.debtors.$error">
              <span v-if="!$v.newEstimate.debtors.required" class="text-danger">{{ $tc('validation.required') }}</span>
            </div>
        </div>
        <div class="col estimate-input">
          <div class="row">
            <div class="col collapse-input">
              <label>{{ $tc('estimates.estimate',1) }} {{ $t('estimates.date') }}<span class="text-danger"> * </span></label>
              <input
                v-model="newEstimate.estimate_date"
                type="date"
                data-date=""
                data-date-format="DD/MM/YYYY"
                class="base-prefix-input"
                @change="$v.newEstimate.estimate_date.$touch()"
                :disabled="isEdit"
              />
              <span v-if="$v.newEstimate.estimate_date.$error && !$v.newEstimate.estimate_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
            </div>
            <div class="col collapse-input">
              <label>{{ $t('estimates.estimate_number') }}<span class="text-danger"> * </span></label>
              <base-prefix-input
                v-model="estimateNumAttribute"
                :invalid="$v.estimateNumAttribute.$error"
                :prefix="estimatePrefix"
                icon="hashtag"
                @input="$v.estimateNumAttribute.$touch()"
                :prefix-width="55"
                :disabled="true"
              />
              <span v-show="$v.estimateNumAttribute.$error && !$v.estimateNumAttribute.required" class="text-danger mt-1"> {{ $tc('validation.required') }}  </span>
            </div>
          </div>
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
                  {{ $tc('estimates.inventory.title',2) }}
                </span>
              </th>
              <th class="text-right">
                <span class="column-heading">
                  {{ $t('estimates.inventory.quantity') }}
                </span>
              </th>
              <th class="text-left">
                <span class="column-heading">
                  {{ $t('estimates.inventory.price') }}
                </span>
              </th>
              <th class="text-left">
                <span class="column-heading">
                  {{ $t('estimates.inventory.sale_price') }}
                </span>
              </th>
              <th v-if="discountPerInventory === 'YES'" class="text-right">
                <span class="column-heading">
                  {{ $t('estimates.inventory.discount') }}
                </span>
              </th>
              <th class="text-right">
                <span class="column-heading amount-heading">
                  {{ $t('estimates.inventory.amount') }}
                </span>
              </th>
            </tr>
          </thead>
          <draggable v-model="inventoryBind" class="item-body" tag="tbody" handle=".handle">
            <invoice-inventory
              v-for="(each, index) in inventoryBind"
              ref="estimateInventory"
              :key="each.name+index"
              :index="index"
              :inventory-data="each"
              :currency="currency"
              :discount-per-inventory="discountPerInventory"
              :inventory-type="'estimate'"
              :inventory-list="inventoryListBind"
              :inventory-negative="inventoryNegative"
              @remove="removeInventory"
              @update="updateInventoryBounce"
              @inventoryValidate="checkInventoryData"
              @endlist="showEndList"
            />
          </draggable>
        </table>
      </div>
      <button v-if="showAddNewInventory" class="add-item-action add-estimate-item" @click="addInventory">
        <font-awesome-icon icon="shopping-basket" class="mr-2"/>
        {{ $t('estimates.add_item') }}
      </button>
      <button v-if="showEndOfList" @click="removeEndOfList" class="btn btn-primary" style="margin: 10px">
        End Of List
      </button>

      <div class="estimate-foot">
        <div>
          <label>{{ $t('estimates.notes') }}</label>
          <base-text-area
            v-model="newEstimate.notes"
            rows="3"
            cols="50"
            @input="$v.newEstimate.notes.$touch()"
          />
          <div v-if="$v.newEstimate.notes.$error">
            <span v-if="!$v.newEstimate.notes.maxLength" class="text-danger">{{ $t('validation.notes_maxlength') }}</span>
          </div>
        </div>

        <div class="estimate-total">
          <div class="section">
            <label class="estimate-label">{{ $t('estimates.quantity') }}</label>
            <label class="">
              <div v-html="totalQuantity(inventoryBind)" />
            </label>
          </div>
          <div class="section">
            <label class="estimate-label">{{ $t('estimates.sub_total') }}</label>
            <label class="estimate-amount">
              ₹ {{ subtotal }}
            </label>
          </div>

          <div class="section border-top mt-3">
            <label class="estimate-label">{{ $t('estimates.total') }} {{ $t('estimates.amount') }}:</label>
            <label class="estimate-amount total">
              ₹ {{ total }}
            </label>
          </div>
        </div>
      </div>
      <div class="page-actions row">
          <!-- <a v-if="$route.name === 'estimates.edit'" :href="`/estimates/pdf/${newEstimate.unique_hash}`" target="_blank" class="mr-3 estimate-action-btn base-button btn btn-outline-primary default-size" outline color="theme">
            {{ $t('general.view_pdf') }}
          </a> -->
          <base-button
            :loading="isLoading"
            :disabled="isLoading"
            icon="save"
            color="theme"
            class="estimate-action-btn"
            type="submit">
            {{ $t('estimates.save_estimate') }}
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
.add-estimate-item{
  border: 0px
}
.add-estimate-item:focus {
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
</style>
<script>
import draggable from 'vuedraggable'
import MultiSelect from 'vue-multiselect'
import InvoiceInventory from '../invoices/Inventory'
import EstimateStub from '../../stub/estimate'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { validationMixin } from 'vuelidate'
import Guid from 'guid'
import { required, between, maxLength, numeric } from 'vuelidate/lib/validators';
export default {
  components: {
    MultiSelect,
    draggable,
    InvoiceInventory
  },
  mixins: [validationMixin],
  data () {
    return {
      newEstimate: {
        estimate_date: null,
        estimate_number: null,
        user_id: null,
        estimate_template_id: 1,
        sub_total: null,
        total: null,
        notes: null,
        discount_type: 'fixed',
        discount_val: 0,
        discount: 0,
        items: [{
          ...EstimateStub,
        }],
        debtors: '',
      },
      customers: [],
      inventoryList: [],
      estimateTemplates: [],
      selectedCurrency: '',
      discountPerInventory: null,
      initLoading: false,
      isLoading: false,
      maxDiscount: 0,
      estimatePrefix: null,
      estimateNumAttribute: null,
      role: this.$store.state.user.currentUser.role,
      sundryDebtorsList: [], //List of Sundry Debitor name
      isEdit: false,
      url: null,
      siteURL: null,
      showAddNewInventory: true,
      showEndOfList: false,
      inventoryNegative: false,
    }
  },
  validations () {
    return {
      newEstimate: {
        estimate_date: {
          required
        },
        notes: {
          maxLength: maxLength(255)
        },
        debtors: {
          required
        }
      },
      estimateNumAttribute: {
        required
      }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrency'
    ]),
    ...mapGetters('estimate', [
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
      if (this.newEstimate.discount_val) {
        return this.subtotal - this.newEstimate.discount_val
      }
      return this.subtotal
    },
    total () {
      return this.subtotalWithDiscount
    },
    subtotal () {
      let inventory = this.newEstimate.items
      if (this.$route.name === 'estimates.edit') {
        inventory = this.newEstimate.items
      }
      if (inventory && inventory.length) {
        return inventory.reduce(function (a, b) {
                return a + b['total']
              }, 0)
      }
      return 0
    },
    discount: {
      get: function () {
        return this.newEstimate.discount
      },
      set: function (newValue) {
        if (this.newEstimate.discount_type === 'percentage') {
          this.newEstimate.discount_val = (this.subtotal * newValue)
        } else {
          this.newEstimate.discount_val = newValue
        }
        this.newEstimate.discount = newValue
      }
    },
    setEstimateDebtor: {
      cache: false,
      get() {
        return this.newEstimate.debtors
      },
      set(value) {
        //this.searchDebtorRefNumber(value)
        this.newEstimate.debtors = value
      },
    },
    inventoryBind() {
      let invent = this.newEstimate.items
      if (this.$route.name === 'estimates.edit') {
        invent = this.newEstimate.items
      }
      return invent
    },
    inventoryListBind() {
      return this.$store.state.inventory.inventories
    }
  },
  watch: {
    subtotal (newValue) {
      if (this.newEstimate.discount_type === 'percentage') {
        this.newEstimate.discount_val = (this.newEstimate.discount * newValue)
      }
    }
  },
  created () {
    this.loadData()
    this.fetchInitialInventory()
    this.updateInventoryBounce = _.debounce((data) => {
      this.updateInventory(data);
    }, 1500);
  },
  methods: {
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('estimate', [
      'addEstimate',
      'fetchCreateEstimate',
      'fetchEstimate',
      'updateEstimate',
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
    async fetchInitialInventory () {
      await this.fetchAllInventory({
        limit: 50,
        filter: {},
        orderByField: '',
        orderBy: ''
      }).then((resp) => {
        this.inventoryList = resp.data.inventories.data
      })
    },
    async loadData () {
      if (this.$route.name === 'estimates.edit') {
        this.initLoading = true
        let response = await this.fetchEstimate(this.$route.params.id)
        this.isEdit = true
        if (response.data) {
          this.newEstimate = response.data.estimate
          this.inventoryNegative = response.data.inventory_negative
          this.newEstimate.estimate_date = moment(response.data.estimate.estimate_date).format('YYYY-MM-DD')
          this.discountPerInventory = response.data.discount_per_inventory
          this.selectedCurrency = this.defaultCurrency
          this.estimateTemplates = response.data.estimateTemplates
          this.estimatePrefix = response.data.estimate_prefix
          this.estimateNumAttribute = response.data.estimateNumber
          this.newEstimate.debtors = response.data.sundryDebtorsList[0]
        }
        this.initLoading = false
        return
      }

      this.initLoading = true
      let response = await this.fetchCreateEstimate()
      if (response.data) {
        this.discountPerInventory = response.data.discount_per_inventory
        this.selectedCurrency = this.defaultCurrency
        this.estimateTemplates = response.data.estimateTemplates
        this.newEstimate.estimate_date = response.data.estimate_today_date
        this.inventoryNegative = response.data.inventory_negative
        this.estimatePrefix = response.data.estimate_prefix
        this.estimateNumAttribute = response.data.nextEstimateNumberAttribute
        this.sundryDebtorsList = response.data.sundryDebtorsList
      }
      this.initLoading = false
    },
    openTemplateModal () {
      this.openModal({
        'title': this.$t('general.choose_template'),
        'componentName': 'EstimateTemplate',
        'data': this.estimateTemplates
      })
    },
    addInventory () {
      this.inventoryBind.push({...EstimateStub})
      this.$nextTick(() => {
        this.$refs.estimateInventory[this.inventoryBind.length-1].$el.focus()
        this.$refs.estimateInventory[this.inventoryBind.length-1].$children[0].$refs.baseSelect.$el.focus()
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
        return false
      }
      Object.assign(this.inventoryBind[data.index], {...data.inventory})
      this.$nextTick(() => {
        this.$refs.estimateInventory[data.index].$el.focus()
        if (data.updatingInput === 'sale_price') {
          this.$refs.estimateInventory[data.index].$children[3].$refs.baseInput.focus()
        }
        if (data.updatingInput === 'quantity') {
          this.$refs.estimateInventory[data.index].$children[1].$refs.baseInput.focus()
        }
      })

    },
    submitEstimateData () {
      if (!this.checkValid()) {
        return false
      }
      this.newEstimate.estimate_number = this.estimatePrefix + '-' + this.estimateNumAttribute

      let data = {
        ...this.newEstimate,
        estimate_date: moment(this.newEstimate.estimate_date).format('DD/MM/YYYY'),
        sub_total: this.subtotal,
        total: this.total,
        user_id: this.user.id,
        estimate_template_id: this.getTemplateId,
      }
      if (this.$route.name === 'estimates.edit') {
        this.submitUpdate(data)
        return
      }

      this.submitSave(data)
    },
    reset() {
      setTimeout(() => {
        window.location.reload()
        this.isLoading = false
      }, 2000)
    },
    async showEstimatePopup (estimate_id) {
      swal({
        title: this.$t('estimates.estimate_report_title'),
        text: this.$t('estimates.estimate_report_text'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      }).then(async (success) => {
        if (success) {
          this.siteURL = `/reports/estimate/${estimate_id}`
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
      this.addEstimate(data).then((res) => {
        if (res.data) {
          window.toastr['success'](this.$t('estimates.created_message'))
          let notificationSound = new Audio("/assets/ring.mp3");
          notificationSound.play();
          this.reset()
        }
      }).catch((err) => {
        this.isLoading = false
        if (err) {
          window.toastr['error'](err)
          return true
        }
        this.reset()
      })
    },
    submitUpdate (data) {
      if (this.isLoading) {
        return false
      }
      this.updateEstimate(data).then((res) => {
        if (res.data && res.data.estimate && res.data.estimate.id) {
          window.toastr['success'](this.$t('estimates.updated_message'))
          this.isLoading = false
          //this.showEstimatePopup(res.data.estimate.id)
          this.reset()
        }
        if (res.data.error === 'invalid_due_amount') {
          this.isLoading = false
           window.toastr['error'](this.$t('estimates.invalid_due_amount_message'))
           this.reset()
        }
      }).catch((err) => {
        this.isLoading = false
        if (err) {
          window.toastr['error'](err)
          this.reset()
          return true
        }
      })
    },
    checkInventoryData (index, isValid) {
      this.newEstimate.items[index].valid = isValid
    },
    checkValid () {
      this.$v.newEstimate.$touch()
      window.hub.$emit('checkInventory')
      let isValid = true
      this.newEstimate.items.forEach((each) => {
        if (!each.valid) {
          isValid = false
        }
      })

      if (this.$v.newEstimate.$invalid === false && isValid === true) {
        isValid = true
      }
      return isValid
    },
    showEndList(val) {
      this.showAddNewInventory = !val;
      this.showEndOfList = val;
      this.inventoryBind.splice(this.inventoryBind.length - 1, 1);
    },
    removeEndOfList() {
      this.showEndOfList = false;
      this.showAddNewInventory = true;
    }
  }
}
</script>
