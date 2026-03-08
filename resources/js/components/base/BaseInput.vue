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
  compatConfig: {
    COMPONENT_V_MODEL: false
  },
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
    modelValue: {
      type: [String, Number, File],
      default: undefined
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
      focus: this.name==='account' ? true : false,
      showPass: false
    }
  },
  computed: {
    inputValue: {
      get () {
        const current = this.modelValue !== undefined ? this.modelValue : this.value
        if (this.type === 'number' && current) {
          return current.toString().replace('-', '')
        }
        return current
      },
      set (value) {
        this.$emit('update:modelValue', value)
        this.$emit('input', value)
      }
    },
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
