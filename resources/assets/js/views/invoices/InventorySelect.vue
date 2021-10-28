<template>
  <div class="item-selector">
    <base-select
      ref="baseSelect"
      v-model="inventorySelected"
      :options="inventories"
      :show-labels="true"
      :preserve-search="false"
      :initial-search="inventory.name"
      :invalid="invalid"
      :placeholder="$t('invoices.inventory.select_an_inventory')"
      :do-not-select-default="true"
      label="name"
      track-by="id"
      class="multi-select-inventory remove-extra"
      @value="onTextChange"
      @endlist="showEndList"
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
<style>
div.remove-extra div.multiselect__tags span.multiselect__single{
    display: none !important;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  props: {
    inventory: {
      type: Object,
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
    }
  },
  data () {
    return {
      selectedInventory: null,
      loading: false,
    }
  },
  computed: {
    ...mapGetters('inventory', [
      'inventories'
    ]),
    inventorySelected: {
      cache: false,
      get() {
        return this.selectedInventory
      },
      set(newVal) {
        this.selectedInventory = newVal
        this.$emit('select', newVal)
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
    deselectInventory () {
      this.inventorySelected = null
      this.$emit('deselect')
    },
    showEndList(val) {
      this.$emit('endlist', true)
    }
  }
}
</script>
