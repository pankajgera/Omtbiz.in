<template>
  <div class="base-input">
    <font-awesome-icon v-if="icon && isAlignLeftIcon" :icon="icon" class="left-icon"/>
    <input
      ref="baseInput"
      v-model="inputValue"
      :id="name ? name : placeholder + id"
      :type="toggleType"
      :disabled="disabled"
      :readonly="readOnly"
      :name="name"
      :tabindex="tabIndex"
      :class="[{'input-field-left-icon': icon && isAlignLeftIcon ,'input-field-right-icon': icon && !isAlignLeftIcon ,'invalid': isFieldValid, 'disabled': disabled, 'small-input': small}, inputClass]"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      class="input-field"
      :accept="fileInput==='image' ? 'image/*' : ''"
      :capture="fileInput==='image' ? 'camera' : ''"
      :min="type === 'number' ? 0 : null"
      :step="step"
      :maxlength="type === 'number' ? max : null"
      @focus="handleFocusIn"
      @input="handleInput"
      @change="handleChange"
      @keyup="handleKeyupEnter"
      @keydown.enter.prevent
      @blur="handleFocusOut"
    >
    <div v-if="showPassword && isAlignLeftIcon" style="cursor: pointer" @click="showPass = !showPass" >
      <font-awesome-icon :icon="!showPass ?'eye': 'eye-slash'" class="right-icon" />
    </div>
    <font-awesome-icon v-if="icon && !isAlignLeftIcon" :icon="icon" class="right-icon" />
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    tabIndex: {
      type: String,
      default: ''
    },
    value: {
      type: [String, Number, File],
      default: ''
    },
    fileInput: {
      type: String,
      default: 'false',
    },
    placeholder: {
      type: String,
      default: ''
    },
    invalid: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    readOnly: {
      type: Boolean,
      default: false
    },
    icon: {
      type: String,
      default: ''
    },
    inputClass: {
      type: String,
      default: ''
    },
    small: {
      type: Boolean,
      default: false
    },
    alignIcon: {
      type: String,
      default: 'left'
    },
    autocomplete: {
      type: String,
      default: 'on'
    },
    showPassword: {
      type: Boolean,
      default: false
    },
    max: {
      type: Number,
      default: null
    },
    step: {
      type: [Number, String],
      default: null
    },
    formatSecondLastDecimal: {
      type: Boolean,
      default: false
    },
    formatTwoDecimals: {
      type: Boolean,
      default: false
    },
    formatOneDecimal: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      inputValue: this.value,
      focus: this.name==='account' ? true : false,
      showPass: false,
      isFocused: false
    }
  },
  computed: {
    isFieldValid () {
      return this.invalid
    },
    isAlignLeftIcon () {
      if (this.alignIcon === 'left') {
        return true
      }
      return false
    },
    toggleType () {
      if (this.showPass) {
        return 'text'
      }
      return this.type
    }
  },
  watch: {
    value () {
      if ('number' === this.type && this.value) {
        this.inputValue = (this.value).toString().replace('-', '')
        // if (this.max && this.value!==null && this.value!=='') {
        //   this.inputValue = this.value.slice(0, this.max)
        // }
      } else {
        this.inputValue = this.value
      }
      if (!this.isFocused) {
        if (this.formatSecondLastDecimal) {
          this.inputValue = this.normalizeSecondLastDecimal(this.value)
        } else if (this.formatOneDecimal) {
          this.inputValue = this.normalizeOneDecimal(this.value)
        } else if (this.formatTwoDecimals) {
          this.inputValue = this.normalizeTwoDecimals(this.value)
        }
      }
    },
    focus () {
      this.focusInput()
    }
  },
  mounted () {
    this.focusInput()
  },
  methods: {
    focusInput () {
      if (this.focus) {
        this.$refs.baseInput.focus()
      }
    },
    handleInput (e) {
        this.$emit('input', this.inputValue)
    },
    handleChange (e) {
        this.$emit('change', this.inputValue)
    },
    handleKeyupEnter (e) {
        this.$emit('keyup', this.inputValue)
    },
    handleFocusIn (e) {
        this.isFocused = true
    },
    handleFocusOut (e) {
        this.isFocused = false
      if (this.formatSecondLastDecimal) {
        const formatted = this.normalizeSecondLastDecimal(this.inputValue)
        if (formatted !== this.inputValue) {
          this.inputValue = formatted
          this.$emit('input', this.inputValue)
        }
      } else if (this.formatOneDecimal) {
        const formatted = this.normalizeOneDecimal(this.inputValue)
        if (formatted !== this.inputValue) {
          this.inputValue = formatted
          this.$emit('input', this.inputValue)
        }
      } else if (this.formatTwoDecimals) {
        const formatted = this.normalizeTwoDecimals(this.inputValue)
        if (formatted !== this.inputValue) {
          this.inputValue = formatted
          this.$emit('input', this.inputValue)
        }
      }
      this.$emit('blur', this.inputValue)
    },
    normalizeSecondLastDecimal (value) {
      if (value === null || value === undefined || value === '') {
        return value
      }

      if (typeof value === 'number') {
        if (Number.isNaN(value)) {
          return value
        }
        return value.toFixed(1)
      }

      const stringValue = String(value)
      const isNegative = stringValue.trim().startsWith('-')
      const unsignedValue = isNegative ? stringValue.trim().slice(1) : stringValue.trim()

      if (unsignedValue.includes('.')) {
        const numericValue = parseFloat(unsignedValue)
        if (Number.isNaN(numericValue)) {
          return value
        }
        const formatted = numericValue.toFixed(1)
        return isNegative ? `-${formatted}` : formatted
      }

      const digits = unsignedValue.replace(/\D+/g, '')

      if (!digits) {
        return value
      }

      const padded = digits.length === 1 ? `0${digits}` : digits
      const formatted = `${padded.slice(0, -1)}.${padded.slice(-1)}`

      return isNegative ? `-${formatted}` : formatted
    },
    normalizeTwoDecimals (value) {
      if (value === null || value === undefined || value === '') {
        return value
      }

      const stringValue = String(value).replace(/,/g, '').trim()
      const isNegative = stringValue.startsWith('-')
      const unsignedValue = isNegative ? stringValue.slice(1) : stringValue

      if (typeof value === 'number' || unsignedValue.includes('.')) {
        const numericValue = parseFloat(unsignedValue)
        if (Number.isNaN(numericValue)) {
          return value
        }
        const formatted = numericValue.toFixed(2)
        return isNegative ? `-${formatted}` : formatted
      }

      const digits = unsignedValue.replace(/\D+/g, '')
      if (!digits) {
        return value
      }

      let formatted = ''
      if (digits.length === 1) {
        formatted = `0.0${digits}`
      } else if (digits.length === 2) {
        formatted = `0.${digits}`
      } else {
        formatted = `${digits.slice(0, -2)}.${digits.slice(-2)}`
      }

      return isNegative ? `-${formatted}` : formatted
    },
    normalizeOneDecimal (value) {
      if (value === null || value === undefined || value === '') {
        return value
      }

      const stringValue = String(value).replace(/,/g, '').trim()
      const isNegative = stringValue.startsWith('-')
      const unsignedValue = isNegative ? stringValue.slice(1) : stringValue

      const numericValue = parseFloat(unsignedValue)
      if (Number.isNaN(numericValue)) {
        return value
      }

      const formatted = numericValue.toFixed(1)
      return isNegative ? `-${formatted}` : formatted
    }
  }
}
</script>
