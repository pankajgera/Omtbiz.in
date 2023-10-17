<template>
  <tr class="item-row invoice-item-row">
    <td colspan="5">
      <table class="full-width">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 10%;">
          <col style="width: 15%;">
          <col style="width: 15%;" v-if="('orders' !== inventoryType)">
          <col v-if="discountPerInventory === 'YES' && ('orders' !== inventoryType)" style="width: 15%;">
          <col style="width: 15%;" v-if="('orders' !== inventoryType)">
        </colgroup>
        <tbody>
          <tr>
            <td class="">
              <div class="item-select-wrapper">
                <div class="sort-icon-wrapper handle">
                  <font-awesome-icon
                    class="sort-icon"
                    icon="grip-vertical"
                  />
                </div>
                <inventory-select
                  ref="inventorySelect"
                  :invalid="$v.invoiceItem.name.$error"
                  :invalid-description="$v.invoiceItem.description.$error"
                  :inventory="inventoryList"
                  :is-disable="isDisable"
                  :picked-inventory="inventoryData"
                  @search="searchVal"
                  @select="onSelectInventory"
                  @deselect="deselectInventory"
                  @onDesriptionInput="$v.invoiceItem.description.$touch()"
                  @onSelectInventory="isSelected = true"
                  @endlist="showEndList"
                />
              </div>
            </td>
            <td class="text-right">
              <base-input
                ref="inventoryQuantity"
                :id="'inventoryQuantity'+index"
                :key="'inventoryQuantity'+index"
                :name="'inventoryQuantity'+index"
                v-model="inventoryQuantityBind"
                :invalid="$v.invoiceItem.quantity.$error"
                type="number"
                small
                :disabled="isDisable || disabled"
                @blur="$v.invoiceItem.quantity.$touch()"
              />
              <div v-if="$v.invoiceItem.quantity.$error">
                <span v-if="!$v.invoiceItem.quantity.maxLength" class="text-danger">{{ $t('validation.quantity_maxlength') }}</span>
              </div>
            </td>
            <td class="text-left" v-if="('orders' !== inventoryType)">
              <div class="d-flex flex-column">
                <div class="flex-fillbd-highlight">
                   <base-input
                    v-model.trim="price"
                    :class="{'invalid' : $v.invoiceItem.price.$error, 'input-field': true}"
                    type="text"
                    name="price"
                    :disabled="true"
                  />
                  <div v-if="$v.invoiceItem.price.$error">
                    <span v-if="!$v.invoiceItem.price.maxLength" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
                  </div>
                </div>

              </div>
            </td>
            <td class="text-left" v-if="('orders' !== inventoryType)">
              <div class="d-flex flex-column">
                <div class="flex-fillbd-highlight">
                   <base-input
                    ref="inventoryPrice"
                    :id="'inventoryPrice'+index"
                    :key="'inventoryPrice'+index"
                    :name="'inventoryPrice'+index"
                    v-model.trim="sale_price"
                    :class="{'invalid' : $v.invoiceItem.sale_price.$error, 'input-field': true}"
                    :disabled="isDisable || disabled"
                    type="number"
                  />
                  <div v-if="$v.invoiceItem.sale_price.$error">
                    <span v-if="!$v.invoiceItem.sale_price.maxLength" class="text-danger">{{ $t('validation.sale_price_maxlength') }}</span>
                  </div>
                </div>

              </div>
            </td>
            <td v-if="discountPerInventory === 'YES' && ('orders' !== inventoryType)">
              <div class="d-flex flex-column bd-highlight">
                <div
                  class="btn-group flex-fill bd-highlight"
                  role="group"
                >
                  <base-input
                    v-model="discount"
                    :invalid="$v.invoiceItem.discount_val.$error"
                    input-class="item-discount"
                    :disabled="isDisable || disabled"
                    @input="$v.invoiceItem.discount_val.$touch()"
                  />
                  <v-dropdown :show-arrow="false" theme-light>
                    <button
                      slot="activator"
                      type="button"
                      class="btn item-dropdown dropdown-toggle"
                      data-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      {{ invoiceItem.discount_type == 'fixed' ? currency.symbol : '%' }}
                    </button>
                    <v-dropdown-inventory>
                      <a class="dropdown-inventory" href="#" @click.prevent="selectFixed" >
                        {{ $t('general.fixed') }}
                      </a>
                    </v-dropdown-inventory>
                    <v-dropdown-inventory>
                      <a class="dropdown-inventory" href="#" @click.prevent="selectPercentage">
                        {{ $t('general.percentage') }}
                      </a>
                    </v-dropdown-inventory>
                  </v-dropdown>
                </div>
              </div>
            </td>
            <td class="text-left">
              <div class="item-amount" v-if="('orders' !== inventoryType)">
                <span>
                   ₹ {{ total }}
                </span>

                <div class="remove-icon-wrapper">
                  <font-awesome-icon
                    class="remove-icon"
                    icon="trash-alt"
                    @click="removeInventory"
                  />
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</template>
<script>
import Guid from 'guid'
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
import InvoiceStub from '../../stub/invoice'
import InventorySelect from './InventorySelect'
const { required, minValue, between, maxLength, requiredIf } = require('vuelidate/lib/validators')

export default {
  components: {
    InventorySelect
  },
  mixins: [validationMixin],
  props: {
    inventoryData: {
      type: Object,
      default: null
    },
    index: {
      type: Number,
      default: null
    },
    type: {
      type: String,
      default: ''
    },
    currency: {
      type: [Object, String],
      required: true
    },
    discountPerInventory: {
      type: String,
      default: ''
    },
    isDisable: {
      type: Boolean,
      default: false,
      required: false
    },
    inventoryType: {
      type: String,
      default: '',
    },
    inventoryList: {
      type: [Object, Array],
      required: false,
    },
    inventoryNegative: {
      type: Boolean,
      default: false,
      required: true,
    },
    isEdit: {
      type: Boolean,
      default: false,
      required: false,
    }
  },
  data () {
    return {
      isClosePopup: false,
      inventorySelect: null,
      invoiceItem: {...this.inventoryData},
      maxDiscount: 0,
      isSelected: false,
      updatingInput: '',
    }
  },
  computed: {
    // ...mapGetters('inventories', [
    //   'inventories'
    // ]),
    ...mapGetters('modal', [
      'modalActive'
    ]),
    disabled() {
      return !this.invoiceItem.inventory_id;
    },
    subtotal: {
      cache: false,
      get: function () {
        return parseInt(this.invoiceItem.sale_price ? this.invoiceItem.sale_price : this.invoiceItem.price) * this.invoiceItem.quantity
      },
      set: function (newValue) {
        return parseInt(newValue ? newValue : this.invoiceItem.price) * this.invoiceItem.quantity
      }
    },
    discount: {
      get: function () {
        return this.invoiceItem.discount
      },
      set: function (newValue) {
        if (this.invoiceItem.discount_type === 'percentage') {
          this.invoiceItem.discount_val = (this.subtotal * newValue)
        } else {
          this.invoiceItem.discount_val = newValue
        }

        this.invoiceItem.discount = newValue
        this.updatingInput = 'discount'
      }
    },
    total () {
      return this.subtotal
    },
    price: {
      get: function () {
        return this.invoiceItem.price
      },
      set: function (newValue) {
        if (parseFloat(newValue) > 0) {
          this.invoiceItem.price = newValue
          this.maxDiscount = this.invoiceItem.price
        } else {
          this.invoiceItem.price = newValue
        }
        this.updatingInput = 'price'
      }
    },
    sale_price: {
      get: function () {
        return this.invoiceItem.sale_price ? this.invoiceItem.sale_price : this.invoiceItem.price
      },
      set: function (newValue) {
        if (parseFloat(newValue) > 0) {
          this.invoiceItem.sale_price = newValue
          this.maxDiscount = newValue
          this.subtotal = newValue
        } else {
          this.invoiceItem.sale_price = newValue
        }
        this.updatingInput = 'sale_price'
      }
    },
    inventoryQuantityBind: {
      get: function() {
        return this.invoiceItem.quantity
      },
      set: function (newValue) {
        let maxQuantityAvailable = 0;
        if(this.inventoryList.length) {
          let quantity = parseInt(
          this.inventoryList.find(i =>
            i.name === this.invoiceItem.name &&
            parseInt(i.price) === parseInt(this.invoiceItem.price)
          ));
          if(quantity) {
            maxQuantityAvailable = quantity.quantity;
          } else {
            maxQuantityAvailable = parseInt(newValue);
          }

        }
        if (maxQuantityAvailable < newValue && !this.inventoryNegative && 'orders' !== this.inventoryType && 'estimate' !== this.inventoryType) {
          swal({
            title: this.$t('invoices.out_of_stock'),
            text: this.$t('invoices.update_inventory_quantity', {'max': maxQuantityAvailable}),
            icon: '/assets/icon/check-circle-solid.svg',
            buttons: true,
            dangerMode: true
          }).then(async (success) => {
            if (success) {
              let id = this.invoiceItem.id ? this.invoiceItem.id : this.invoiceItem.inventory_id;
              //this.invoiceItem.quantity = null
              window.open('/inventory/' + id + '/edit', '_blank').focus()
            } else {
              //this.invoiceItem.quantity = null
            }
          })
        } else {
          this.invoiceItem.quantity = parseInt(newValue)
        }
        this.updatingInput = 'quantity'
      }
    }
  },
  watch: {
    invoiceItem: {
      handler: 'updateInventory',
      deep: true
    },
    subtotal (newValue) {
      if (this.invoiceItem.discount_type === 'percentage') {
        this.invoiceItem.discount_val = (this.invoiceItem.discount * newValue)
      }
    },
    modalActive (val) {
      if (!val) {
        this.isSelected = false
      }
    }
  },
  validations () {
    return {
      invoiceItem: {
        name: {
          required
        },
        quantity: {
          required,
          minValue: minValue(0),
          maxLength: maxLength(20)
        },
        price: {
          required: requiredIf(function () {
            return ('orders' !== this.inventoryType)
          }),
          minValue: minValue(1),
          maxLength: maxLength(20)
        },
        sale_price: {
          required: requiredIf(function () {
            return ('orders' !== this.inventoryType)
          }),
          minValue: minValue(1),
          maxLength: maxLength(20)
        },
        discount_val: {
          between: between(0, this.maxDiscount)
        },
        description: {
          maxLength: maxLength(255)
        }
      }
    }
  },
  created () {
    window.hub.$on('checkInventory', this.validateInventory)
    window.hub.$on('newInventory', (val) => {
      if (!this.invoiceItem.inventory_id && this.modalActive && this.isSelected) {
        this.onSelectInventory(val)
      }
    });
  },
  methods: {
    searchVal (val) {
      this.invoiceItem.name = val
    },
    deselectInventory () {
      this.invoiceItem = {...InvoiceStub, id: this.invoiceItem.id}
      this.$nextTick(() => {
        this.$refs.inventorySelect.$refs.baseSelect.$refs.search.focus()
      })
    },
    onSelectInventory (newItem) {
      if (!newItem || 0 === newItem.id) {
        return;
      }

      this.invoiceItem.name = newItem.name
      this.invoiceItem.price = newItem.price
      this.invoiceItem.sale_price = newItem.sale_price ? newItem.sale_price : newItem.price
      this.invoiceItem.inventory_id = newItem.id
      this.invoiceItem.description = newItem.description
      this.updatingInput = 'quantity'
      this.updateInventory()
    },
    selectFixed () {
      if (this.invoiceItem.discount_type === 'fixed') {
        return
      }
      this.invoiceItem.discount_val = this.invoiceItem.discount
      this.invoiceItem.discount_type = 'fixed'
    },
    selectPercentage () {
      if (this.invoiceItem.discount_type === 'percentage') {
        return
      }
      this.invoiceItem.discount_val = (this.subtotal * this.invoiceItem.discount)
      this.invoiceItem.discount_type = 'percentage'
    },
    updateInventory () {
      this.$emit('update', {
        'index': this.index,
        'updatingInput': this.updatingInput,
        'inventory': {
          ...this.invoiceItem,
          total: this.total,
        }
      })
    },
    removeInventory () {
      this.$emit('remove', this.index)
    },
    validateInventory () {
      this.$v.invoiceItem.$touch()
      if (this.invoiceItem !== null) {
        this.$emit('inventoryValidate', this.index, !this.$v.$invalid)
      } else {
        this.$emit('inventoryValidate', this.index, false)
      }
    },
    showEndList(val) {
      this.$emit('endlist', true)
    }
  }
}
</script>
