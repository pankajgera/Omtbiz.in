<template>
  <div class="order-create-page main-content">
    <div class="page-header">
      <div class="page-actions row">
        <router-link slot="item-title" class="col-xs-2" to="/orders">
          <base-button size="large" icon="envelope" color="theme">
            {{ $t('orders.title') }}
          </base-button>
        </router-link>
      </div>
    </div>
    <form v-if="!initLoading" action="" @submit.prevent="submitOrderData">
      <div class="page-header">
        <h3 v-if="isEdit" class="page-title">{{ $t('orders.edit_order') }}</h3>
        <h3 v-else class="page-title">{{ $t('orders.new_order') }} </h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/orders">{{ $tc('orders.order', 2) }}</router-link></li>
          <li v-if="isEdit" class="breadcrumb-item">{{ $t('orders.edit_order') }}</li>
          <li v-else class="breadcrumb-item">{{ $t('orders.new_order') }}</li>
        </ol>
      </div>
      <div class="row order-input-group">
        <div class="col-md-5 order-customer-container">
          <label class="form-label">{{ $t('receipts.list') }}</label><span class="text-danger"> *</span>
            <base-select
              v-model="setOrderDebtor"
              label="name"
              track-by="id"
              :invalid="$v.newOrder.debtors.$error"
              :options="sundryDebtorsList"
              :required="'required'"
              :searchable="true"
              :show-labels="false"
              :allow-empty="false"
              :disabled="isEdit"
              :placeholder="$t('receipts.select_a_list')"
            />
            <div v-if="$v.newOrder.debtors.$error">
              <span v-if="!$v.newOrder.debtors.required" class="text-danger">{{ $tc('validation.required') }}</span>
            </div>
        </div>
        <div class="col order-input">
          <div class="row">
            <div class="col collapse-input">
              <label>{{ $tc('orders.order',1) }} {{ $t('orders.date') }}<span class="text-danger"> * </span></label>
              <input
                v-model="newOrder.order_date"
                type="date"
                data-date=""
                data-date-format="DD/MM/YYYY"
                class="base-prefix-input"
                @change="$v.newOrder.order_date.$touch()"
                :disabled="isEdit"
              />
              <span v-if="$v.newOrder.order_date.$error && !$v.newOrder.order_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
            </div>
            <div class="col collapse-input">
              <label>{{ $t('orders.order_number') }}<span class="text-danger"> * </span></label>
              <base-prefix-input
                v-model="orderNumAttribute"
                :invalid="$v.orderNumAttribute.$error"
                :prefix="orderPrefix"
                icon="hashtag"
                @input="$v.orderNumAttribute.$touch()"
                :prefix-width="55"
                :disabled="true"
              />
              <span v-show="$v.orderNumAttribute.$error && !$v.orderNumAttribute.required" class="text-danger mt-1"> {{ $tc('validation.required') }}  </span>
            </div>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table item-table">
          <colgroup>
            <col style="width: 60%;">
            <col style="width: 17%;">
            <col style="width: 25%;">
            <col style="width: 0%;">
          </colgroup>
          <thead class="item-table-header">
            <tr>
              <th class="text-left">
                <span class="column-heading heading-1 item-heading">
                  {{ $tc('orders.inventory.title',2) }}
                </span>
              </th>
              <th class="text-right">
                <span class="column-heading">
                  {{ $t('orders.inventory.quantity') }}
                </span>
              </th>
              <th class="text-left"></th>
              <th class="text-left"></th>
            </tr>
          </thead>
          <draggable v-model="inventoryBind" class="item-body" tag="tbody" handle=".handle">
            <invoice-inventory
              v-for="(each, index) in inventoryBind"
              ref="orderInventory"
              :key="each.name+index"
              :index="index"
              :inventory-data="each"
              :currency="currency"
              :discount-per-inventory="discountPerInventory"
              :inventory-type="'orders'"
              :inventory-list="inventoryList"
              :inventory-negative="inventoryNegative"
              @remove="removeInventory"
              @update="updateInventoryBounce"
              @inventoryValidate="checkInventoryData"
              @endlist="showEndList"
            />
          </draggable>
        </table>
      </div>
      <button v-if="showAddNewInventory" class="add-item-action add-order-item" @click="addInventory">
        <font-awesome-icon icon="shopping-basket" class="mr-2"/>
        {{ $t('orders.add_item') }}
      </button>
      <button v-if="showEndOfList" @click="removeEndOfList" class="btn btn-primary" style="margin: 10px">
        End Of List
      </button>

      <div class="order-foot">
        <div>
          <label>{{ $t('orders.notes') }}</label>
          <base-text-area
            v-model="newOrder.notes"
            rows="3"
            cols="50"
            @input="$v.newOrder.notes.$touch()"
          />
          <div v-if="$v.newOrder.notes.$error">
            <span v-if="!$v.newOrder.notes.maxLength" class="text-danger">{{ $t('validation.notes_maxlength') }}</span>
          </div>
        </div>

        <div class="order-total">
          <div class="section">
            <label class="order-label">{{ $t('orders.quantity') }}</label>
            <label class="">
              <div v-html="totalQuantity(inventoryBind)" />
            </label>
          </div>
          <!-- <div class="section">
            <label class="order-label">{{ $t('orders.sub_total') }}</label>
            <label class="order-amount">
              ₹ {{ subtotal }}
            </label>
          </div>

          <div class="section border-top mt-3">
            <label class="order-label">{{ $t('orders.total') }} {{ $t('orders.amount') }}:</label>
            <label class="order-amount total">
              ₹ {{ total }}
            </label>
          </div> -->
        </div>
      </div>
      <div class="page-actions row">
          <!-- <a v-if="isEdit" :href="`/orders/pdf/${newOrder.unique_hash}`" target="_blank" class="mr-3 order-action-btn base-button btn btn-outline-primary default-size" outline color="theme">
            {{ $t('general.view_pdf') }}
          </a> -->
          <base-button
            :loading="isLoading"
            :disabled="isLoading"
            icon="save"
            color="theme"
            class="order-action-btn"
            type="submit">
            {{ $t('orders.save_order') }}
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
.add-order-item{
  border: 0px
}
.add-order-item:focus {
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
import OrderStub from '../../stub/order'
import { mapActions, mapGetters } from 'vuex'
import moment from 'moment'
import { validationMixin } from 'vuelidate'
const { required, between, maxLength, numeric } = require('vuelidate/lib/validators')

export default {
  components: {
    MultiSelect,
    draggable,
    InvoiceInventory
  },
  mixins: [validationMixin],
  data () {
    return {
      newOrder: {
        order_date: null,
        order_number: null,
        user_id: null,
        order_template_id: 1,
        sub_total: null,
        total: null,
        notes: null,
        discount_type: 'fixed',
        discount_val: 0,
        discount: 0,
        //reference_number: null,
        order_items: [{
          ...OrderStub,
        }],
        debtors: '',
      },
      customers: [],
      inventoryList: [],
      orderTemplates: [],
      selectedCurrency: '',
      discountPerInventory: null,
      initLoading: false,
      isLoading: false,
      maxDiscount: 0,
      orderPrefix: null,
      orderNumAttribute: null,
      role: this.$store.state.user.currentUser.role,
      sundryDebtorsList: [], //List of Sundry Debitor name
      url: null,
      siteURL: null,
      showAddNewInventory: true,
      showEndOfList: false,
      inventoryNegative: false,
    }
  },
  validations () {
    return {
      newOrder: {
        order_date: {
          required
        },
        // discount_val: {
        //   between: between(0, this.subtotal)
        // },
        notes: {
          maxLength: maxLength(255)
        },
        // reference_number: {
        //   maxLength: maxLength(255)
        // },
        debtors: {
          required
        }
      },
      orderNumAttribute: {
        required
      }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrency'
    ]),
    ...mapGetters('orders', [
      'getTemplateId',
      //'selectedCustomer'
    ]),
    ...mapGetters('user', {
      user: 'currentUser'
    }),
    currency () {
      return this.selectedCurrency
    },
    setOrderDebtor: {
      cache: false,
      get() {
        return this.newOrder.debtors
      },
      set(value) {
        //this.searchDebtorRefNumber(value)
        this.newOrder.debtors = value
      },
    },
    inventoryBind() {
      let invent = this.newOrder.order_items
      if (this.isEdit) {
        invent = this.newOrder.order_items
      }
      return invent
    },
    isEdit() {
      if (this.$route.name === 'orders.edit') {
        return true
      }
      return false
    }
  },
  watch: {
    subtotal (newValue) {
      if (this.newOrder.discount_type === 'percentage') {
        this.newOrder.discount_val = (this.newOrder.discount * newValue)
      }
    }
  },
  created () {
    this.loadData()
    this.fetchInitialInventory()
    this.updateInventoryBounce = _.debounce((data) => {
      this.updateInventory(data);
    }, 1100);
  },
  methods: {
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('orders', [
      'addOrder',
      'fetchCreateOrder',
      'fetchOrder',
      'updateOrder',
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
        limit: 1000,
        filter: {},
        orderByField: '',
        orderBy: ''
      }).then((resp) => {
        this.inventoryList = resp.data.inventories.data
      })
    },
    async loadData () {
      if (this.isEdit) {
        this.initLoading = true
        let response = await this.fetchOrder(this.$route.params.id)
        if (response.data) {
          this.newOrder = response.data.order
          this.inventoryNegative = response.data.inventory_negative
          this.newOrder.order_date = moment(response.data.order.order_date).format('YYYY-MM-DD')
          this.discountPerInventory = response.data.discount_per_inventory
          this.selectedCurrency = this.defaultCurrency
          this.orderPrefix = response.data.order_prefix
          this.orderNumAttribute = response.data.orderNumber
          this.newOrder.debtors = response.data.sundryDebtorsList[0]
        }
        this.initLoading = false
        return
      }

      this.initLoading = true
      let response = await this.fetchCreateOrder()
      if (response.data) {
        this.discountPerInventory = response.data.discount_per_inventory
        this.selectedCurrency = this.defaultCurrency
        this.newOrder.order_date = response.data.order_today_date
        this.inventoryNegative = response.data.inventory_negative
        this.orderPrefix = response.data.order_prefix
        this.orderNumAttribute = response.data.nextOrderNumberAttribute
        this.sundryDebtorsList = response.data.sundryDebtorsList
      }
      this.initLoading = false
    },
    addInventory () {
      this.inventoryBind.push({...OrderStub})
      this.$nextTick(() => {
        this.$refs.orderInventory[this.inventoryBind.length-1].$el.focus()
        this.$refs.orderInventory[this.inventoryBind.length-1].$children[0].$refs.baseSelect.$el.focus()
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
        this.$refs.orderInventory[data.index].$el.focus()
        if (data.updatingInput === 'sale_price') {
          this.$refs.orderInventory[data.index].$children[3].$refs.baseInput.focus()
        }
        if (data.updatingInput === 'quantity') {
          this.$refs.orderInventory[data.index].$children[1].$refs.baseInput.focus()
        }
      })
    },
    submitOrderData () {
      if (!this.checkValid()) {
        return false
      }
      this.newOrder.order_number = this.orderPrefix + '-' + this.orderNumAttribute

      let data = {
        ...this.newOrder,
        order_date: moment(this.newOrder.order_date).format('DD/MM/YYYY'),
        user_id: this.user.id,
        order_template_id: this.getTemplateId,
      }
      if (this.isEdit) {
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
    async showOrderPopup (order_id) {
      swal({
        title: this.$t('orders.order_report_title'),
        text: this.$t('orders.order_report_text'),
        icon: '/assets/icon/check-circle-solid.svg',
        buttons: true,
        dangerMode: false
      }).then(async (success) => {
        if (success) {
          this.siteURL = `/reports/order/${order_id}`
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
      this.addOrder(data).then((res) => {
        if (res.data) {
          window.toastr['success'](this.$t('orders.created_message'))
          //this.$router.push('/orders/create')
          //this.showOrderPopup(res.data.order.id)
          this.reset()
        }
      }).catch((err) => {s
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
      this.updateOrder(data).then((res) => {
        if (res.data && res.data.order && res.data.order.id) {
          window.toastr['success'](this.$t('orders.updated_message'))
          this.isLoading = false
          //this.showOrderPopup(res.data.order.id)
          this.reset()
        }
        if (res.data.error === 'invalid_due_amount') {
          this.isLoading = false
           window.toastr['error'](this.$t('orders.invalid_due_amount_message'))
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
      this.newOrder.order_items[index].valid = isValid
    },
    checkValid () {
      this.$v.newOrder.$touch()
      window.hub.$emit('checkInventory')
      let isValid = true
      this.newOrder.order_items.forEach((each) => {
        if (!each.valid) {
          isValid = false
        }
      })

      if (this.$v.newOrder.$invalid === false && isValid === true) {
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
