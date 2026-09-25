<template>
  <div class="item-selector inventory-select-control">
    <base-select
      ref="baseSelect"
      v-model="inventorySelected"
      :options="inventoriesOptions"
      :show-labels="false"
      :preserve-search="false"
      :allow-empty="false"
      :searchable="true"
      :custom-label="customLabel"
      :invalid="invalid"
      :placeholder="$t('invoices.inventory.select_an_inventory')"
      :do-not-select-default="true"
      :disabled="isDisable"
      :loading="loading"
      :select-on-tab="selectOnTab"
      :can-select-on-tab="isInventoryOption"
      append-to-body
      label="name"
      track-by="id"
      @value="onTextChange"
      @tab-select="$emit('advance')"
      @empty-enter="openInventoryModal"
    >
      <template #afterList="{ search, options, loading: searching }">
        <button
          v-if="search.trim() && !options.length && !searching"
          type="button"
          class="list-add-button"
          @click="openInventoryModal(search)"
        >
          <font-awesome-icon class="icon" icon="cart-plus" />
          {{ $t('inventory.add_inventory') }}
        </button>
      </template>
    </base-select>
    <button
      v-if="clearable && inventorySelected"
      type="button"
      class="clear-inventory-button"
      :disabled="isDisable"
      :aria-label="$t('general.clear_selected_item')"
      :title="$t('general.clear_selected_item')"
      @click="clearSelection"
    >
      <span aria-hidden="true">×</span>
    </button>
  </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'

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
    },
    selectOnTab: {
      type: Boolean,
      default: false
    },
    clearable: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      loading: false,
      ownsModal: false,
      inventoryCreated: false,
      searchText: '',
    }
  },
  computed: {
    ...mapGetters('modal', ['modalActive']),
    inventoriesOptions() {
      //First array item to add "End of list" option
      let array = [];
      array.push({
        company_id: 1,
        id: 0,
        name: "End of List",
        price: "0",
        quantity: 0,
        sale_price: 0,
        unit: "pc",
      })
      array.push(...this.inventory)
      return array
    },
    inventorySelected: {
      cache: false,
      get() {
        return this.pickedInventory?.inventory_id
          ? { ...this.pickedInventory, id: this.pickedInventory.inventory_id }
          : null
      },
      set(newVal) {
        if (!newVal) return
        if (0 === newVal.id) {
          this.$emit('endlist', true)
        } else {
          this.$emit('select', newVal)
        }
      }
    }
  },
  watch: {
    modalActive (active) {
      if (!active && this.ownsModal) {
        this.ownsModal = false
        if (!this.inventoryCreated) this.$nextTick(this.focusSearch)
      }
    }
  },
  methods: {
    clearSelection () {
      if (this.isDisable) return
      this.$refs.baseSelect.updateSearch('')
      this.$emit('deselect')
      this.$nextTick(this.focusSearch)
    },
    focusSearch () {
      this.$refs.baseSelect?.focusSearch()
    },
    isInventoryOption (option) {
      return Boolean(option.id)
    },
    ...mapActions('modal', [
      'openModal'
    ]),
    ...mapActions('inventory', [
      'fetchAllInventory'
    ]),
    customLabel ({ name, price, sale_price }) {
      if (name !== 'End of List') {
        return `${name} - ₹${price ? price : sale_price}`
      }
      return `${name}`
    },
    async searchInventory (search) {
      let data = {
        name: search,
        orderByField: '',
        orderBy: '',
        page: 1,
        limit: 50,
      }
      this.loading = true
      try {
        await this.fetchAllInventory(data)
      } catch (error) {
        window.toastr['error'](this.$t('general.action_failed'))
      } finally {
        this.loading = false
      }
    },
    onTextChange (val) {
      this.searchText = val
      this.searchInventory(val)
      this.$emit('search', val)
    },
    openInventoryModal (search = this.searchText) {
      if (this.isDisable || this.loading || !search.trim()) return
      this.$refs.baseSelect.deactivate()
      this.ownsModal = true
      this.inventoryCreated = false
      this.openModal({
        'title': this.$t('inventory.add_inventory'),
        'componentName': 'InventoryModal',
        data: {
          name: search.trim(),
          onCreated: (inventory) => {
            this.inventoryCreated = true
            this.inventorySelected = inventory
          }
        }
      })
    },
    showEndList(val) {
      this.$emit('endlist', true)
    }
  }
}
</script>
<style scoped>
.inventory-select-control {
  display: flex;
  align-items: center;
  gap: 4px;
}

.inventory-select-control > .base-select {
  flex: 1;
  min-width: 0;
}

.clear-inventory-button {
  flex: 0 0 32px;
  height: 32px;
  padding: 0;
  border: 0;
  border-radius: 4px;
  background: transparent;
  color: inherit;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.clear-inventory-button:hover:not(:disabled),
.clear-inventory-button:focus-visible {
  background: rgba(128, 128, 128, 0.15);
}

.clear-inventory-button:disabled {
  opacity: 0.5;
  cursor: default;
}
</style>
