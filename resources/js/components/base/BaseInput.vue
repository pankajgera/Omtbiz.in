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
      :class="[{'input-field-left-icon': icon && isAlignLeftIcon ,'input-field-right-icon': icon && !isAlignLeftIcon, 'input-field-password': type === 'password', 'invalid': isFieldValid, 'disabled': disabled, 'small-input': small}, inputClass]"
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
    <button
      v-if="type === 'password'"
      type="button"
      class="password-visibility-toggle"
      :disabled="disabled"
      :tabindex="tabIndex"
      :aria-label="$t(showPass ? 'general.hide_password' : 'general.show_password')"
      :title="$t(showPass ? 'general.hide_password' : 'general.show_password')"
      :aria-pressed="showPass"
      @click="showPass = !showPass"
    >
      <font-awesome-icon :icon="showPass ? 'eye-slash' : 'eye'" aria-hidden="true" />
    </button>
    <font-awesome-icon v-else-if="icon && !isAlignLeftIcon" :icon="icon" class="right-icon" />
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
      if (this.type === 'password' && this.showPass) {
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

<style scoped>
.base-input .input-field-password {
  padding-right: 48px;
}

.password-visibility-toggle {
  position: absolute;
  top: 1px;
  right: 1px;
  bottom: 1px;
  width: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 4px;
  background: transparent;
  color: var(--ui-text-muted);
  cursor: pointer;
}

.password-visibility-toggle:hover:not(:disabled) {
  background: var(--ui-surface-hover);
}

.password-visibility-toggle:focus-visible {
  outline: 2px solid var(--ui-accent);
  outline-offset: -3px;
}

.password-visibility-toggle:disabled {
  color: var(--ui-disabled-text);
  cursor: not-allowed;
}
</style>
