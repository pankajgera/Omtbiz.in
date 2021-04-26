<template>
  <td
      class="cell noselect"
      :id="`cell${rowIndex}-${columnIndex}`"
      :class='{ selected: !onlyBorder && selected, editable, invalid, [column.type || "text"]: true }'
      :title="invalid"
      :style="cellStyle"
      @click='$emit("click", $event)'
  >
      <span class="editable-field" v-if="inputType === 'select'">
        <span v-if="column.field === 'type'">
          <base-select
            ref="select"
            v-model="selectTypeBind"
            :options="setOptions"
            :searchable="true"
            :show-labels="false"
            :allow-empty="false"
            :class="{'remove-extra': !selectTypeBind, 'width-100 style-chooser': true }"
            :placeholder="''"
            track-by="id"
            label="name"
            :open-direction="'bottom'"
          />
        </span>
        <span v-if="column.field === 'account'">
          <base-select
            ref="select"
            v-model="selectAccountBind"
            :options="setOptions"
            :searchable="true"
            :show-labels="false"
            :allow-empty="false"
            :class="{'remove-extra': !selectAccountBind, 'width-500 style-chooser': true }"
            :placeholder="''"
            track-by="id"
            label="name"
            :open-direction="'bottom'"
          />
        </span>
      </span>
      <span v-else>
        <span class="editable-field">
          <input
            :type="inputType"
            ref="input"
            :key="value"
            :placeholder="placeholder"
            :disabled="disableInput"
            :value="value"
            @keyup.enter="setEditableValue"
            @focus="editPending = true"
            @blur="leaved" />
          </span>
      </span>
  </td>
</template>
<style scoped>
  .style-chooser .vs__search::placeholder,
  .style-chooser .vs__dropdown-toggle,
  .style-chooser .vs__dropdown-menu {
    background: #3c4b81;
    border: none;
    color: #394066;
    text-transform: lowercase;
    font-variant: small-caps;
    z-index: 10;
  }
  .style-chooser .vs__clear,
  .style-chooser .vs__open-indicator {
    fill: #394066;
  }
</style>
<style>
  .vs__actions {
    display: none !important;
  }
  .input-group .form-control {
    z-index: 0 !important;
  }
</style>
<script>
import { format } from 'date-fns'
import { cellValueParser, sameDates } from './helpers'
import { cellFormatter } from './vue-filters.js'

export default {
  filters: {
    cellFormatter
  },
  props: {
    column: { type: Object },
    row: { type: Object },
    columnIndex: { type: Number },
    rowIndex: { type: Number },
    selStart: { type: Array },
    selEnd: { type: Array },
    cellEditing: { type: Array },
    cellsWithErrors: { type: Object },
    onlyBorder: { type: Boolean },
    masterOptions: { type: Array, default: [] },
    placeholder: { type: String, default: null }
  },
  data () {
    return { value: null, rowValue: null, editPending: false, selectedValue: null}
  },
  mounted() {
    if (this.column.field === 'type') {
      this.value = this.row.type
      this.rowValue = this.row.type
      this.selectedValue = this.setOptions.find(each => each.name === this.value)
    }
    if (this.column.field === 'account') {
      this.rowValue = this.row.account
      this.value = this.row.account
    }
    if (this.column.field === 'credit' && this.row.credit) {
      this.rowValue = this.row.credit
      this.value = this.row.credit
    }
    if (this.column.field === 'debit' && this.row.debit) {
      this.rowValue = this.row.debit
      this.value = this.row.debit
    }
    // if (this.$refs.select && this.column.field === 'type') {
    //   this.$refs.select.$refs.search.focus()
    // }
  },
  computed: {
    selectTypeBind: {
      get: function () {
        if (this.column.field === 'type') {
          return this.selectedValue
        }
        return this.value
      },
      set: function (val) {
        if (this.column.field === 'type') {
          this.selectedValue = val
          this.value = this.selectedValue.name
          this.rowValue = this.selectedValue.name
          const { row, column, rowIndex, columnIndex } = this
          let valueChanged = true
          let event = this.$refs.select.$el
          let value = this.selectedValue.name
          this.$emit('edited', { row, column, rowIndex, columnIndex, event, value, valueChanged })
        }
      }
    },
    selectAccountBind: {
      get: function () {
        if (this.column.field === 'account') {
          return this.selectedValue
        }
        return this.value
      },
      set: function (val) {
        if (this.column.field === 'account') {
          this.selectedValue = val
          this.value = this.selectedValue.name
          this.rowValue = this.selectedValue.name
          const { row, column, rowIndex, columnIndex } = this
          let valueChanged = true
          let event = this.$refs.select.$el
          let value = this.selectedValue.name
          this.$emit('edited', { row, column, rowIndex, columnIndex, event, value, valueChanged })
        }
      }
    },
    selected () {
      //return true
      return this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    },
    selectedTop () {
      return this.rowIndex === this.selStart[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    },
    selectedRight () {
      return this.columnIndex === this.selEnd[1] && this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0]
    },
    selectedBottom () {
      return this.rowIndex === this.selEnd[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    },
    selectedLeft () {
      return this.columnIndex === this.selStart[1] && this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0]
    },
    editable () {
      if (this.disableInput) {
        return false
      }
      //return true
      return this.cellEditing[0] === this.rowIndex && this.cellEditing[1] === this.columnIndex
    },
    invalid () {
      return this.cellsWithErrors[`cell${this.rowIndex}-${this.columnIndex}`]
    },
    inputType () {
      switch (this.column.type) {
        case 'text': return 'text'
        case 'link': return 'text'
        case 'numeric': return 'number'
        case 'currency': return 'number'
        case 'percent': return 'number'
        case 'date': return 'date'
        case 'datetime': return 'datetime-local'
        case 'select': return 'select'
      }
      return 'text'
    },
    cellStyle () {
      const cellStyle = this.row.$cellStyle && this.row.$cellStyle[this.column.field]
      return { ...this.row.$rowStyle, ...cellStyle }
    },
    setOptions() {
      if (this.column.field === 'type') {
        return [{id: 1, name: 'Dr'}, {id: 2, name: 'Cr'}]
      }
      return this.masterOptions
    },
    disableInput() {
      let bool = false
      if (this.row.type === 'Dr' && this.column.field === 'credit' || this.row.type === 'Cr' && this.column.field === 'debit') {
        this.rowValue = null
        this.value = null
        bool = true
      }
      return bool
    },
    isEdit() {
      if (this.$route.name === 'vouchers.edit') {
          return true
        }
        return false
    }
  },
  created () {
    if (this.isEdit) {
      this.loadEditData()
    }
  },
  watch: {
    cellEditing () {
      if (this.cellEditing[0] === this.rowIndex && this.cellEditing[1] === this.columnIndex && !this.disableInput) {
        this.rowValue = this.getEditableValue(this.row[this.column.field])
        this.value = this.getEditableValue(this.row[this.column.field])

        Vue.nextTick(() => {
          const input = this.inputType !== 'select' ? this.$refs.input : this.$refs.select.$refs.search
          if (this.inputType === 'select') {
            input.focus()
            if (!this.selectedValue) return
            input.value = this.selectedValue.name
            this.value = this.selectedValue.name
            this.rowValue = this.selectedValue.name
          }
          if (!this.value && this.value !== 0 && this.value !== false) {
            input.value = null
            input.focus()
            return
          }
          if (this.column.type === 'datetime' || this.column.type === 'date') {
            const formattedDate = this.column.type === 'datetime'
              ? `${format(this.value, 'yyyy-MM-dd')}T${format(this.value, 'HH:mm')}`
              : `${format(this.value, 'yyyy-MM-dd')}`
            setTimeout(() => {
              input.value = formattedDate
              input.focus()
            }, 50)
          } else {
            input.value = this.value
            input.focus()
          }
        })
      }
      if (this.inputType === 'select') {
        if (this.selectedValue) {
            this.value = this.selectedValue.name
            this.rowValue = this.selectedValue.name
            if (this.column.field === 'type') {
              this.row.type = this.selectedValue.name
            }
            if(this.column.field === 'account') {
              this.row.account = this.selectedValue.name
              this.row.account_id = this.selectedValue.id
            }
        }
      }
      if (this.row.type === 'Cr' && this.column.field === 'credit' && !this.rowValue && !this.disableInput) {
        this.rowValue = this.row.debit
        this.value = this.row.debit
      }
      if (this.row.type === 'Dr' && this.column.field === 'debit' && !this.rowValue && !this.disableInput) {
        this.rowValue = this.row.credit
        this.value = this.row.credit
      }
    },
  },
  methods: {
    loadEditData() {
      if (this.row.type !== '') {
        if (this.column.field === 'type') {
          this.selectedValue = {name: this.row.type, id: this.row.account_id}
          this.rowValue = this.row.type
          this.value = this.row.type
        }
        if (this.column.field === 'account') {
          this.selectedValue = {name: this.row.account, id: this.row.account_id}
          this.rowValue = this.row.account
          this.value = this.row.account
        }
        if (this.column.field === 'debit') {
          this.rowValue = this.row.debit
          this.value = this.row.debit
        }
        if (this.column.field === 'credit') {
          this.rowValue = this.row.credit
          this.value = this.row.credit
        }
      }
    },
    getEditableValue (value) {
      if (this.column.type === 'datetime' || this.column.type === 'date') {
        if (typeof value === 'string') {
          const parsedDate = new Date(value)
          return isNaN(parsedDate) ? null : parsedDate
        }
      }
      return value
    },
    setEditableValue ($event) {
      const input = this.inputType !== 'select' ? this.$refs.input.value : this.selectedValue ? this.selectedValue.name : null
      let value = cellValueParser(this.column, this.row, input, true)
      if (!value) return
      this.editPending = false
      let valueChanged = true
      // if (value === this.rowValue) {
      //   valueChanged = false
      // } else if (value && (this.column.type === 'date' || this.column.type === 'datetime')) {
      //   if (sameDates(value, this.rowValue)) {
      //     valueChanged = false
      //   }
      // }
      if (!this.disableInput) {
        this.rowValue = value
        this.value = value
        const { row, column, rowIndex, columnIndex } = this
        this.$emit('edited', { row, column, rowIndex, columnIndex, $event, value, valueChanged })
      }
    },
    leaved ($event) {
      if (this.editPending) {
        this.setEditableValue($event)
      }
    },
    linkClicked () {
      this.$emit('link-clicked')
    },
  }
}
</script>

<style lang="scss" scoped>
@import './variables';

.cell {
  padding: 0 $cell-side-paddings;
  position: relative;
  display: flex;
  align-items: center;
  min-height: 40px;
  border: solid 1px transparent;
  border-bottom-color: $cell-border-color;
  border-right-color: $cell-border-color;
  cursor: default;

  &.selected {
    border-color: $cell-selected-border-color;
  }

  &.selected-top {
    border-top-color: $cell-selected-border-color;
  }

  &.selected-right {
    border-right-color: $cell-selected-border-color;
  }

  &.selected-bottom {
    border-bottom-color: $cell-selected-border-color;
  }

  &.selected-left {
    border-left-color: $cell-selected-border-color;
  }

  &.currency,
  &.numeric,
  &.percent
  {
    text-align: right;
  }

  &.editable {
    padding: 0;
    display: flex;
    box-shadow: 1px 1px 4px #cbcbcb;
  }

  &.invalid {
    &::after {
      content: "\26A0";
      position: absolute;
      right: 6px;
      top: 0;
      color: red;
      font-weight: bold;
      font-size: 20px;
    }
  }

  .cell-content {
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    overflow: hidden;
  }

  .editable-field {
    height: 100%;
    width: 100%;
    display: flex;

    input {
      height: 100%;
      border: none;
      outline: none;
      width: 100%;

      &:disabled {
        background: #eeeeee;
        cursor: not-allowed;
      }
    }
  }
}

.noselect {
  -webkit-touch-callout: none; /* iOS Safari */
  -webkit-user-select: none; /* Safari */
  -khtml-user-select: none; /* Konqueror HTML */
  -moz-user-select: none; /* Old versions of Firefox */
  -ms-user-select: none; /* Internet Explorer/Edge */
  user-select: none; /* Non-prefixed version, currently supported by Chrome, Edge, Opera and Firefox */
}
</style>
