<template>
  <transition name="fade">
    <div v-if="modalActive" :class="'size-' + modalSize" class="base-modal">
      <div class="modal-body">
        <div class="close-icon" @click="closeModal">
          <font-awesome-icon icon="times" />
        </div>
        <div class="modal-header p-3">
          <h5 class="modal-heading">{{ modalTitle }}</h5>
        </div>
        <component :is="component" />
      </div>
    </div>
  </transition>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'
import EstimateTemplate from './EstimateTemplate'
import InvoiceTemplate from './InvoiceTemplate'
import CustomerModal from './CustomerModal'
import CategoryModal from './CategoryModal'
import InventoryModal from './InventoryModal'
import IncreasePriceModal from './IncreasePriceModal'
import GroupModal from './GroupModal'

export default {
  components: {
    InventoryModal,
    IncreasePriceModal,
    EstimateTemplate,
    InvoiceTemplate,
    CustomerModal,
    CategoryModal,
    GroupModal
  },
  data () {
    return {
      component: '',
      hasFocus: false
    }
  },
  computed: {
    ...mapGetters('modal', [
      'modalActive',
      'modalTitle',
      'componentName',
      'modalSize',
      'modalData'
    ])
  },
  watch: {
    componentName (component) {
      if (!component) {
        return
      }

      this.component = component
    }
  },
  methods: {
    ...mapActions('modal', [
      'openModal',
      'closeModal'
    ])
  }
}
</script>
<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
