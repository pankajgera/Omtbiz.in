<template>
  <tr class="item-row order-item-row">
    <td colspan="5">
      <table class="full-width">
        <colgroup>
          <col style="width: 40%;">
          <col style="width: 10%;">
          <col style="width: 15%;">
          <col v-if="discountPerItem === 'YES'" style="width: 15%;">
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
                <item-select
                  ref="itemSelect"
                  :invalid="$v.item.name.$error"
                  :invalid-description="$v.item.description.$error"
                  :item="item"
                  @search="searchVal"
                  @select="onSelectItem"
                  @deselect="deselectItem"
                  @onDesriptionInput="$v.item.description.$touch()"
                  @onSelectItem="isSelected = true"
                />
              </div>
            </td>
            <td class="text-right">
              <base-input
                v-model="item.quantity"
                :invalid="$v.item.quantity.$error"
                type="number"
                step="0.01"
                small
                @keyup="updateItem"
                @input="$v.item.quantity.$touch()"
              />
              <div v-if="$v.item.quantity.$error">
                <span v-if="!$v.item.quantity.maxLength" class="text-danger">{{ $t('validation.quantity_maxlength') }}</span>
              </div>
            </td>
            <td class="text-left">
              <div class="d-flex flex-column">
                <div class="flex-fillbd-highlight">
                  <base-input
                    v-model.trim="price"
                    :class="{'invalid' : $v.formData.price.$error, 'input-field': true}"
                    type="text"
                    name="price"
                  />
                  <div v-if="$v.item.price.$error">
                    <span v-if="!$v.item.price.maxLength" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
                  </div>
                </div>
              </div>
            </td>
            <td v-if="discountPerItem === 'YES'" class="">
              <div class="d-flex flex-column bd-highlight">
                <div
                  class="btn-group flex-fill bd-highlight"
                  role="group"
                >
                  <base-input
                    v-model="discount"
                    :invalid="$v.item.discount_val.$error"
                    input-class="item-discount"
                    @input="$v.item.discount_val.$touch()"
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
                      {{ item.discount_type == 'fixed' ? currency.symbol : '%' }}
                    </button>
                    <v-dropdown-item>
                      <a class="dropdown-item" href="#" @click.prevent="selectFixed" >
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
                <!-- <div v-if="$v.item.discount.$error"> discount error </div> -->
              </div>
            </td>
            <td class="text-right">
              <div class="item-amount">
                <span>
                  <div v-html="$utils.formatMoney(total, currency)" />
                </span>

                <div class="remove-icon-wrapper">
                  <font-awesome-icon
                    v-if="index > 0"
                    class="remove-icon"
                    icon="trash-alt"
                    @click="removeItem"
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
import { mapGetters } from 'vuex'
import OrderStub from '../../stub/order'
import ItemSelect from './ItemSelect'
const { required, minValue, between, maxLength } = require('vuelidate/lib/validators')

export default {
  components: {
    ItemSelect
  },
  mixins: [validationMixin],
  props: {
    itemData: {
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
    discountPerItem: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      isClosePopup: false,
      itemSelect: null,
      item: {...this.itemData},
      maxDiscount: 0,
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '₹ ',
        precision: 2,
        masked: false
      },
      isSelected: false
    }
  },
  computed: {
    ...mapGetters('item', [
      'items'
    ]),
    ...mapGetters('modal', [
      'modalActive'
    ]),
    subtotal () {
      return this.item.price * this.item.quantity
    },
    discount: {
      get: function () {
        return this.item.discount
      },
      set: function (newValue) {
        if (this.item.discount_type === 'percentage') {
          this.item.discount_val = (this.subtotal * newValue) / 100
        } else {
          this.item.discount_val = newValue * 100
        }

        this.item.discount = newValue
      }
    },
    total () {
      return this.subtotal - this.item.discount_val
    },
    price: {
      get: function () {
        if (parseFloat(this.item.price) > 0) {
          return this.item.price / 100
        }

        return this.item.price
      },
      set: function (newValue) {
        if (parseFloat(newValue) > 0) {
          this.item.price = newValue * 100
          this.maxDiscount = this.item.price
        } else {
          this.item.price = newValue
        }
      }
    }
  },
  watch: {
    item: {
      handler: 'updateItem',
      deep: true
    },
    subtotal (newValue) {
      if (this.item.discount_type === 'percentage') {
        this.item.discount_val = (this.item.discount * newValue) / 100
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
      item: {
        name: {
          required
        },
        quantity: {
          required,
          minValue: minValue(1),
          maxLength: maxLength(20)
        },
        price: {
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
    window.hub.$on('checkItems', this.validateItem)
    window.hub.$on('newItem', (val) => {
      if (!this.item.order_id && this.modalActive && this.isSelected) {
        this.onSelectItem(val)
      }
    })
  },
  methods: {
    searchVal (val) {
      this.item.name = val
    },
    deselectItem () {
      this.item = {...OrderStub, id: this.item.id}
      this.$nextTick(() => {
        this.$refs.itemSelect.$refs.baseSelect.$refs.search.focus()
      })
    },
    onSelectItem (item) {
      this.item.name = item.name
      this.item.price = item.price
      this.item.order_id = item.id
      this.item.id = item.id
      this.item.description = item.description
    },
    selectFixed () {
      if (this.item.discount_type === 'fixed') {
        return
      }

      this.item.discount_val = this.item.discount * 100
      this.item.discount_type = 'fixed'
    },
    selectPercentage () {
      if (this.item.discount_type === 'percentage') {
        return
      }

      this.item.discount_val = (this.subtotal * this.item.discount) / 100

      this.item.discount_type = 'percentage'
    },
    updateItem () {
      this.$emit('update', {
        'index': this.index,
        'item': {
          ...this.item,
          total: this.total,
        }
      })
    },
    removeItem () {
      this.$emit('remove', this.index)
    },
    validateItem () {
      this.$v.item.$touch()

      if (this.item !== null) {
        this.$emit('itemValidate', this.index, !this.$v.$invalid)
      } else {
        this.$emit('itemValidate', this.index, false)
      }
    }
  }
}
</script>
