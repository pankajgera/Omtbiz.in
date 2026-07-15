<template>
  <!-- Never render the contents -->
  <!-- The scoped slot won't have the required data -->
  <div v-if="false">
    <slot></slot>
  </div>
</template>

<script>
import settings from '../settings'
export default {
  inject: {
    tableComponent: {
      default: null
    }
  },

  props: {
    show: { required: false, type: String },
    label: { default: null, type: String },
    dataType: { default: 'string', type: String },

    sortable: { default: true, type: Boolean },
    sortBy: { default: null },

    filterable: { default: true, type: Boolean },
    sortAs: { default: null },
    filterOn: { default: null },

    formatter: { default: v => v, type: Function },

    hidden: { default: false, type: Boolean },

    cellClass: { default: settings.cellClass },
    headerClass: { default: settings.headerClass },
  },

  mounted () {
    if (this.tableComponent) {
      this.tableComponent.registerColumn(this)
    }
  },

  beforeUnmount () {
    if (this.tableComponent) {
      this.tableComponent.unregisterColumn(this)
    }
  }
}
</script>
