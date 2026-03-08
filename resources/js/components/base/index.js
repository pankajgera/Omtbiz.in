import BaseButton from './BaseButton.vue'
import ItemModal from './modal/ItemModal.vue'
import BaseModal from './modal/BaseModal.vue'
import BaseDatePicker from './base-date-picker/BaseDatePicker.vue'
import BaseInput from './BaseInput.vue'
import BaseSwitch from './BaseSwitch.vue'
import BaseTextArea from './BaseTextArea.vue'
import BaseSelect from './base-select/BaseSelect.vue'
import BaseLoader from './BaseLoader.vue'
import BaseCustomerSelect from './BaseCustomerSelect.vue'
import BasePrefixInput from './BasePrefixInput.vue'

import BasePopup from './popup/BasePopup.vue'
import CustomerSelectPopup from './popup/CustomerSelectPopup.vue'

import InventoryModal from './modal/InventoryModal.vue'
import IncreasePriceModal from './modal/IncreasePriceModal.vue'

import {TableColumn, TableComponent} from './base-table/index'
import GroupModal from './modal/GroupModal.vue'

export function registerBaseComponents (app) {
  app.component('base-button', BaseButton)
  app.component('item-modal', ItemModal)
  app.component('base-modal', BaseModal)
  app.component('base-date-picker', BaseDatePicker)
  app.component('base-input', BaseInput)
  app.component('base-switch', BaseSwitch)
  app.component('base-text-area', BaseTextArea)
  app.component('base-loader', BaseLoader)
  app.component('base-prefix-input', BasePrefixInput)

  app.component('table-component', TableComponent)
  app.component('table-column', TableColumn)

  app.component('base-select', BaseSelect)
  app.component('base-customer-select', BaseCustomerSelect)

  app.component('base-popup', BasePopup)
  app.component('customer-select-popup', CustomerSelectPopup)

  app.component('inventory-modal', InventoryModal)
  app.component('increase-price-modal', IncreasePriceModal)
  app.component('group-modal', GroupModal)
}
