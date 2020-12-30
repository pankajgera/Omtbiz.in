<template>
  <td
      class="cell noselect"
      :id="`cell${rowIndex}-${columnIndex}`"
      :class='{ selected: !onlyBorder && selected, editable, invalid, [column.type || "text"]: true }'
      :title="invalid"
      :style="cellStyle"
      @click='$emit("click", $event)'
  >
      <span v-if="inputType === 'select'">
        <v-select
          ref="select"
          :autocomplete="'on'"
          :append-to-body="true"
          :input-id="'select-account-option'"
          :options="setOptions"
          :select-on-tab="true"
          label="name"
          :class="column.field === 'type' ? 'width-100 style-chooser' : 'width-500 style-chooser'"
          :value="value"
          @input="setEditableValue"
          @option:selected="setSelected"
        >
          <template v-slot:option="option">
            {{ option.name }}
          </template>
        </v-select>
      </span>
      <span v-else>
        <span class="editable-field">
          <input
            :type="inputType"
            ref="input"
            :placeholder="placeholder"
            :disabled="disableInput"
            @keyup.enter="setEditableValue"
            @keydown.tab="setEditableValue"
            @keyup.esc="editCancelled"
            @focus="editPending = true"
            @blur="leaved" />
          </span>
        <!-- <span class="editable-field" v-if="cellEditing[0] === rowIndex && cellEditing[1] === columnIndex">
            <input
              :type="inputType"
              ref="input"
              :placeholder="placeholder"
              @keyup.enter="setEditableValue"
              @keydown.tab="setEditableValue"
              @keyup.esc="editCancelled"
              @focus="editPending = true"
              @blur="leaved" />
        </span>
        <span class="cell-content" v-else>
            <a
              @click.prevent="linkClicked"
              v-if='column.type === "link"'
              href="#"
              >
              {{ row[column.field] | cellFormatter(column, row) }}
              </a>
            <span v-else>
              {{ row[column.field] | cellFormatter(column, row) }}
            </span>
        </span> -->
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
    }
  },
  computed: {
    selected () {
      return this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    },
    // selectedTop () {
    //   return this.rowIndex === this.selStart[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    // },
    // selectedRight () {
    //   return this.columnIndex === this.selEnd[1] && this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0]
    // },
    // selectedBottom () {
    //   return this.rowIndex === this.selEnd[0] && this.columnIndex >= this.selStart[1] && this.columnIndex <= this.selEnd[1]
    // },
    // selectedLeft () {
    //   return this.columnIndex === this.selStart[1] && this.rowIndex >= this.selStart[0] && this.rowIndex <= this.selEnd[0]
    // },
    editable () {
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
        return [{'name':'Dr'}, {'name':'Cr'}]
      }
      return this.masterOptions
    },
    disableInput() {
      let bool = false
      if (this.row.type === 'Dr' && this.column.field === 'credit' || this.row.type === 'Cr' && this.column.field === 'debit') {
          bool = true
      }
      return bool
    }
  },
  watch: {
    cellEditing () {
      //console.log('ww', this.row, this.column)
      if (this.cellEditing[0] === this.rowIndex && this.cellEditing[1] === this.columnIndex) {
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
            }
        }
      }
    }
  },
  methods: {
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
      const value = cellValueParser(this.column, this.row, input, true)
      if (!value) return
      this.editPending = false
      let valueChanged = true
      if (value === this.rowValue) {
        valueChanged = false
      } else if (value && (this.column.type === 'date' || this.column.type === 'datetime')) {
        if (sameDates(value, this.rowValue)) {
          valueChanged = false
        }
      }
      if (this.columnIndex === 3 && $event.key === 'Enter') {
        this.$emit('add-row','Dr')
      }
      if (this.columnIndex === 2 && $event.key === 'Enter') {
        this.$emit('add-row','Cr')
      }
      const { row, column, rowIndex, columnIndex } = this
      this.$emit('edited', { row, column, rowIndex, columnIndex, $event, value, valueChanged })
    },
    editCancelled () {
      this.$emit('edit-cancelled')
    },
    leaved ($event) {
      if (this.editPending) {
        this.setEditableValue($event)
      }
    },
    linkClicked () {
      this.$emit('link-clicked')
    },
    setSelected(val) {
      if (val.id) {
        this.row.account_id = val.id
      }
      if (!this.row.account && this.column.field === 'account') {
         this.row.account = val.name
      }
      if (!this.row.type && this.column.field === 'type') {
          this.row.type = val.name
          this.row.credit = 0;
          this.row.debit = 0;
      }
      this.selectedValue = val
      this.value = this.selectedValue.name
      this.rowValue = this.selectedValue.name
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
