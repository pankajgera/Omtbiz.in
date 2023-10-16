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
      :maxlength="type === 'number' ? max : null"
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
      default: Math.random().toString()
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
    }
  },
  data () {
    return {
      inputValue: this.value,
      focus: this.name==='account' ? true : false,
      showPass: false
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
    handleFocusOut (e) {
        this.$emit('blur', this.inputValue)
    }
  }
}
</script>
