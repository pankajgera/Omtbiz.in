<template>
  <textarea
    v-model="inputValue"
    :rows="rows"
    :cols="cols"
    :disabled="disabled"
    :class="['base-text-area',{'invalid': isFieldValid, 'disabled': disabled}, inputClass]"
    :placeholder="placeholder"
    class="text-area-field"
    @input="handleInput"
    @change="handleChange"
    @keyup="handleKeyupEnter"
  />
</template>

<script>
export default {
  compatConfig: {
    COMPONENT_V_MODEL: false
  },
  props: {
    rows: {
      type: String,
      default: '4'
    },
    cols: {
      type: String,
      default: '10'
    },
    modelValue: {
      type: String,
      default: undefined
    },
    value: {
      type: String,
      default: ''
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
    icon: {
      type: String,
      default: ''
    },
    inputClass: {
      type: String,
      default: ''
    }
  },
  computed: {
    inputValue: {
      get () {
        return this.modelValue !== undefined ? this.modelValue : this.value
      },
      set (value) {
        this.$emit('update:modelValue', value)
        this.$emit('input', value)
      }
    },
    isFieldValid () {
      return this.invalid
    }
  },
  methods: {
    handleChange (e) {
      this.$emit('change', this.inputValue)
    },
    handleKeyupEnter (e) {
      this.$emit('keyup', this.inputValue)
    }
  }
}
</script>
