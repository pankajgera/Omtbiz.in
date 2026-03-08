<template>
  <div :class="{'base-prefix-input': true, 'disabled': disabled}" @click="focusInput">
    <!-- <font-awesome-icon v-if="icon" :icon="icon" class="icon" /> -->
    <p class="prefix-label" :style="'width:'+prefixWidth+'%'"><span class="mr-1">{{ prefix }}</span> </p>
    <input
      ref="basePrefixInput"
      v-model="inputValue"
      :type="type"
      class="prefix-input-field"
      :disabled="disabled"
      @input="handleInput"
      @change="handleChange"
      @keyup="handleKeyupEnter"
      @keydown="handleKeyDownEnter"
      @blur="handleFocusOut"
    >
  </div>
</template>
<style scoped>
.disabled{
  background: rgb(235 241 250) !important;
}
.base-prefix-input p.prefix-label{
  background: #e9e9e9;
  height: 100%;
}
@media screen and (max-width: 1030px) {
  .base-prefix-input p.prefix-label{
    padding: 3px;
  }
}
</style>
<script>
export default {
  compatConfig: {
    COMPONENT_V_MODEL: false
  },
  props: {
    prefix: {
      type: String,
      default: null,
      required: true
    },
    icon: {
      type: String,
      default: null
    },
    modelValue: {
      type: [String, Number, File],
      default: undefined
    },
    value: {
      type: [String, Number, File],
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    prefixWidth: {
      type: Number,
      default: 45,
      required: false
    },
    disabled: {
      type: Boolean,
      default: false,
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
    }
  },
  methods: {
    focusInput () {
      this.$refs.basePrefixInput.focus()
    },
    handleChange (e) {
      this.$emit('change', this.inputValue)
    },
    handleKeyupEnter (e) {
      this.$emit('keyup', this.inputValue)
    },
    handleKeyDownEnter (e) {
      this.$emit('keydown', e, this.inputValue)
    },
    handleFocusOut (e) {
      this.$emit('blur', this.inputValue)
    }
  }
}
</script>
