<template>
  <div class="item-selector">
    <base-select
      ref="baseSelect"
      v-model="inventorySelected"
      :options="inventoriesOptions"
      :show-labels="true"
      :preserve-search="false"
      :allow-empty="false"
      :searchable="true"
      :initial-search="inventory.name"
      :custom-label="customLabel"
      :invalid="invalid"
      :placeholder="$t('invoices.inventory.select_an_inventory')"
      :do-not-select-default="true"
      :disabled="isDisable"
      label="name"
      track-by="id"
      @value="onTextChange"
    >
      <div slot="afterList">
        <button type="button" class="list-add-button" @click="openInventoryModal">
          <font-awesome-icon class="icon" icon="cart-plus" />
          <label>{{ $t('general.add_new_item') }}</label>
        </button>
      </div>
    </base-select>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
import { selectInventory } from '../../store/modules/inventory/actions';

export default {
  props: {
    inventory: {
      type: [Object, Array],
      required: true
    },
    invalid: {
      type: Boolean,
      required: false,
      default: false
    },
    invalidDescription: {
      type: Boolean,
      required: false,
      default: false
    },
    isDisable: {
      type: Boolean,
      default: false,
      required: false
    },
    pickedInventory: {
      type: [Object],
      required: false
    }
  },
  data () {
    return {
      newInventory: this.pickedInventory && this.pickedInventory.id ? this.pickedInventory : null,
      loading: false,
    }
  },
  computed: {
    // ...mapGetters('inventory', [
    //   'inventories'
    // ]),
    inventoriesOptions() {
      //First array item to add "End of list" option
      let array = [];
      array.push({
        company_id: 1,
        id: 0,
        name: "End of List",
        price: "0",
        quantity: "0",
        sale_price: 0,
        unit: "pc",
      })
      array.push(...this.inventory)
      return array
    },
    inventorySelected: {
      cache: false,
      get() {
        return this.newInventory
      },
      set(newVal) {
        if (0 === newVal.id) {
          this.$emit('endlist', true)
        } else {
          this.newInventory = newVal
          this.$emit('select', newVal)
        }
      }
    }
  },
  watch: {
    invalidDescription (newValue) {
      console.log(newValue)
    }
  },
  methods: {
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('inventory', [
      'fetchAllInventory'
    ]),
    customLabel ({ name, sale_price }) {
      if (name !== 'End of List') {
        return `${name} - ₹${sale_price}`
      }
      return `${name}`
    },
    async searchInventory (search) {
      let data = {
        filter: {
          name: search,
          unit: '',
          price: ''
        },
        orderByField: '',
        orderBy: '',
        page: 1
      }
      this.loading = true
      await this.fetchAllInventory(data)
      this.loading = false
    },
    onTextChange (val) {
      this.searchInventory(val)
      this.$emit('search', val)
    },
    openInventoryModal () {
      this.$emit('onSelectInventory')
      this.openModal({
        'title': 'Add Inventory',
        'componentName': 'InventoryModal'
      })
    },
    showEndList(val) {
      this.$emit('endlist', true)
    }
  }
}
</script>
