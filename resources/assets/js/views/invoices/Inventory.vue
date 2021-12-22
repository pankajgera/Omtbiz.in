<template>
  <tr class="item-row invoice-item-row">
    <td colspan="5">
      <table class="full-width">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 10%;">
          <col style="width: 15%;">
          <col style="width: 15%;">
          <col v-if="discountPerInventory === 'YES'" style="width: 15%;">
          <col style="width: 15%;">
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
                  :invalid="$v.inventory.name.$error"
                  :invalid-description="$v.inventory.description.$error"
                  :inventory="inventory"
                  :is-disable="isDisable"
                  @search="searchVal"
                  @select="onSelectInventory"
                  @deselect="deselectInventory"
                  @onDesriptionInput="$v.inventory.description.$touch()"
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
                v-model="inventory.quantity"
                :invalid="$v.inventory.quantity.$error"
                type="number"
                small
                :disabled="isDisable"
                @input="$v.inventory.quantity.$touch()"
              />
              <div v-if="$v.inventory.quantity.$error">
                <span v-if="!$v.inventory.quantity.maxLength" class="text-danger">{{ $t('validation.quantity_maxlength') }}</span>
              </div>
            </td>
            <td class="text-left">
              <div class="d-flex flex-column">
                <div class="flex-fillbd-highlight">
                   <base-input
                    v-model.trim="price"
                    :class="{'invalid' : $v.inventory.price.$error, 'input-field': true}"
                    type="text"
                    name="price"
                    :disabled="true"
                  />
                  <div v-if="$v.inventory.price.$error">
                    <span v-if="!$v.inventory.price.maxLength" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
                  </div>
                </div>

              </div>
            </td>
            <td class="text-left">
              <div class="d-flex flex-column">
                <div class="flex-fillbd-highlight">
                   <base-input
                    ref="inventoryPrice"
                    :id="'inventoryPrice'+index"
                    :key="'inventoryPrice'+index"
                    :name="'inventoryPrice'+index"
                    v-model.trim="sale_price"
                    :class="{'invalid' : $v.inventory.sale_price.$error, 'input-field': true}"
                    :disabled="isDisable"
                    type="text"
                  />
                  <div v-if="$v.inventory.sale_price.$error">
                    <span v-if="!$v.inventory.sale_price.maxLength" class="text-danger">{{ $t('validation.sale_price_maxlength') }}</span>
                  </div>
                </div>

              </div>
            </td>
            <td v-if="discountPerInventory === 'YES'" class="">
              <div class="d-flex flex-column bd-highlight">
                <div
                  class="btn-group flex-fill bd-highlight"
                  role="group"
                >
                  <base-input
                    v-model="discount"
                    :invalid="$v.inventory.discount_val.$error"
                    input-class="item-discount"
                    :disabled="isDisable"
                    @input="$v.inventory.discount_val.$touch()"
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
                      {{ inventory.discount_type == 'fixed' ? currency.symbol : '%' }}
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
              <div class="item-amount">
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
import TaxStub from '../../stub/tax'
import InvoiceStub from '../../stub/invoice'
import InventorySelect from './InventorySelect'
import Tax from './Tax'
const { required, minValue, between, maxLength } = require('vuelidate/lib/validators')

export default {
  components: {
    Tax,
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
    taxPerInventory: {
      type: String,
      default: ''
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
    }
  },
  data () {
    return {
      isClosePopup: false,
      inventorySelect: null,
      inventory: {...this.inventoryData},
      maxDiscount: 0,
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '₹ ',
        precision: 2,
        masked: false
      },
      isSelected: false,
      updatingInput: ''
    }
  },
  computed: {
    ...mapGetters('inventories', [
      'inventories'
    ]),
    ...mapGetters('modal', [
      'modalActive'
    ]),
    subtotal: {
      cache: false,
      get: function () {
        return parseInt(this.inventory.sale_price) * this.inventory.quantity
      },
      set: function (newValue) {
        return parseInt(this.inventory.sale_price) * this.inventory.quantity
      }
    },
    discount: {
      get: function () {
        return this.inventory.discount
      },
      set: function (newValue) {
        if (this.inventory.discount_type === 'percentage') {
          this.inventory.discount_val = (this.subtotal * newValue)
        } else {
          this.inventory.discount_val = newValue
        }

        this.inventory.discount = newValue
        this.updatingInput = 'discount'
      }
    },
    total () {
      return this.subtotal - this.inventory.discount_val
    },
    totalSimpleTax () {
      return window._.sumBy(this.inventory.taxes, function (tax) {
        if (!tax.compound_tax) {
          return tax.amount
        }

        return 0
      })
    },
    totalCompoundTax () {
      return window._.sumBy(this.inventory.taxes, function (tax) {
        if (tax.compound_tax) {
          return tax.amount
        }

        return 0
      })
    },
    totalTax () {
      return this.totalSimpleTax + this.totalCompoundTax
    },
    price: {
      get: function () {
        return this.inventory.price
      },
      set: function (newValue) {
        if (parseFloat(newValue) > 0) {
          this.inventory.price = newValue
          this.maxDiscount = this.inventory.price
        } else {
          this.inventory.price = newValue
        }
        this.updatingInput = 'price'
      }
    },
    sale_price: {
      get: function () {
        return this.inventory.sale_price ? this.inventory.sale_price : this.inventory.price
      },
      set: function (newValue) {
        if (parseFloat(newValue) > 0) {
          this.inventory.sale_price = newValue
          this.maxDiscount = newValue
          this.subtotal = newValue
          if (this.inventoryType === 'invoice') {
            this.setNewInventoryPrice(this.inventory);
          }
        } else {
          this.inventory.sale_price = newValue
        }
        this.updatingInput = 'sale_price'
      }
    }
  },
  watch: {
    inventory: {
      handler: 'updateInventory',
      deep: true
    },
    subtotal (newValue) {
      if (this.inventory.discount_type === 'percentage') {
        this.inventory.discount_val = (this.inventory.discount * newValue)
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
      inventory: {
        name: {
          required
        },
        quantity: {
          required,
          minValue: minValue(0),
          maxLength: maxLength(20)
        },
        price: {
          required,
          minValue: minValue(1),
          maxLength: maxLength(20)
        },
        sale_price: {
          required,
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
      if (!this.inventory.inventory_id && this.modalActive && this.isSelected) {
        this.onSelectInventory(val)
      }
    });
    this.setNewInventoryPrice = _.debounce((data) => {
				this.updateNewInventorySalePrice(data);
			}, 600);
  },
  methods: {
    ...mapActions('inventory', [
      'updateInventoryPrice'
    ]),
    searchVal (val) {
      this.inventory.name = val
    },
    deselectInventory () {
      this.inventory = {...InvoiceStub, id: this.inventory.id, taxes: [{...TaxStub, id: Guid.raw()}]}
      this.$nextTick(() => {
        this.$refs.inventorySelect.$refs.baseSelect.$refs.search.focus()
      })
    },
    onSelectInventory (inventory) {
      this.inventory.name = inventory.name
      this.inventory.price = inventory.price
      this.inventory.sale_price = inventory.sale_price ? inventory.sale_price : inventory.price
      this.inventory.inventory_id = inventory.id
      this.inventory.description = inventory.description
      this.updatingInput = 'quantity'
      this.updateInventory()
    },
    selectFixed () {
      if (this.inventory.discount_type === 'fixed') {
        return
      }
      this.inventory.discount_val = this.inventory.discount
      this.inventory.discount_type = 'fixed'
    },
    selectPercentage () {
      if (this.inventory.discount_type === 'percentage') {
        return
      }
      this.inventory.discount_val = (this.subtotal * this.inventory.discount)
      this.inventory.discount_type = 'percentage'
    },
    updateInventory () {
      this.$emit('update', {
        'index': this.index,
        'updatingInput': this.updatingInput,
        'inventory': {
          ...this.inventory,
          total: this.total,
          totalSimpleTax: this.totalSimpleTax,
          totalCompoundTax: this.totalCompoundTax,
          totalTax: this.totalTax,
          tax: this.totalTax,
          taxes: [...this.inventory.taxes]
        }
      })
    },
    removeInventory () {
      this.$emit('remove', this.index)
    },
    validateInventory () {
      this.$v.inventory.$touch()
      if (this.inventory !== null) {
        this.$emit('inventoryValidate', this.index, !this.$v.$invalid)
      } else {
        this.$emit('inventoryValidate', this.index, false)
      }
    },
    updateNewInventorySalePrice(data) {
      this.updateInventoryPrice(data).then((res) => {
        if (res.data) {
          window.toastr['success'](this.$t('invoices.updated_inventory_price'))
        }
      }).catch((err) => {
        this.isLoading = false
        if (err) {
          window.toastr['error'](err)
          return true
        }
      })
    },
    showEndList(val) {
      this.$emit('endlist', true)
    }
  }
}
</script>
