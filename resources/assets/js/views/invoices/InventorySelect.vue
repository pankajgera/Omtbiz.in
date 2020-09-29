<template>
  <div class="item-selector">
    <div v-if="inventory.inventory_id" class="selected-inventory">
      {{ inventory.name }}
      <span class="deselect-icon" @click="deselectInventory">
        <font-awesome-icon icon="times-circle" />
      </span>
    </div>
    <base-select
      v-else
      ref="baseSelect"
      v-model="inventorySelect"
      :options="inventories"
      :show-labels="false"
      :preserve-search="true"
      :initial-search="inventory.name"
      :invalid="invalid"
      :placeholder="$t('invoices.inventory.select_an_inventory')"
      label="name"
      class="multi-select-inventory remove-extra"
      @value="onTextChange"
      @select="(val) => $emit('select', val)"
    >
      <div slot="afterList">
        <button type="button" class="list-add-button" @click="openInventoryModal">
          <font-awesome-icon class="icon" icon="cart-plus" />
          <label>{{ $t('general.add_new_item') }}</label>
        </button>
      </div>
    </base-select>
    <!-- <div class="item-description">
      <base-text-area
        v-autoresize
        v-model="inventory.description"
        :invalid-description="invalidDescription"
        :placeholder="$t('invoices.inventory.type_inventory_description')"
        type="text"
        rows="1"
        class="description-input"
        @input="$emit('onDesriptionInput')"
      />
      <div v-if="invalidDescription">
        <span class="text-danger">{{ $tc('validation.description_maxlength') }}</span>
      </div>
      <textarea type="text" v-autoresize rows="1" class="description-input" v-model="inventory.description" placeholder="Type Inventory Description (optional)" />
    </div> -->
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
      inventorySelect: null,
      loading: false
    }
  },
  computed: {
    ...mapGetters('inventory', [
      'inventories'
    ]),
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
      this.inventorySelect = null
      this.$emit('deselect')
    }
  }
}
</script>
