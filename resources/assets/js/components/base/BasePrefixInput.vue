<template>
  <div :class="{'base-prefix-input': true, 'disabled': disabled}" @click="focusInput">
    <!-- <font-awesome-icon v-if="icon" :icon="icon" class="icon" /> -->
    <p class="prefix-label" :style="'width:'+prefixWidth+'%'"><span class="'mr-1">{{ prefix }}</span>-</p>
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
  background: rgb(196, 196, 196) !important;
}
</style>
<script>
export default {
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
      default: 15,
      required: false
    },
    disabled: {
      type: Boolean,
      default: false,
    }
  },
  data () {
    return {
      inputValue: this.value
    }
  },
  watch: {
    'value' () {
      this.inputValue = this.value
    }
  },
  methods: {
    focusInput () {
      this.$refs.basePrefixInput.focus()
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
    handleKeyDownEnter (e) {
      this.$emit('keydown', e, this.inputValue)
    },
    handleFocusOut (e) {
      this.$emit('blur', this.inputValue)
    }
  }
}
</script>
