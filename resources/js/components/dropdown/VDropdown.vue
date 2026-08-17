<template>
  <div
    v-click-outside="closeDropdown"
    :class="[
      'dropdown-group',
      {'active': (toggle || isActive() )},
      {'has-child':hasChild},
      {'toggle-arrow':(hasChild)&&(showArrow)},
      {'dropdown-light': themeLight}]"
  >
    <div class="dropdown-activator" @click.stop.prevent="showDropdown">
      <slot name="activator"/>
    </div>
    <teleport to="body" :disabled="!appendToBody">
      <transition name="bounce">
        <div
          v-show="toggle"
          v-if="hasChild"
          ref="dropdownItems"
          :class="[
            'dropdown-container',
            {
              'align-right': rightAlign,
              'dropdown-container--fixed': appendToBody,
              'dropdown-light': themeLight
            }
          ]"
          :style="appendToBody ? dropdownPosition : null"
          @click="closeOnSelectDropdownItem"
        >
          <slot />
        </div>
      </transition>
    </teleport>
  </div>
</template>
<script>
export default {
  props: {
    activeUrl: {
      type: String,
      require: true,
      default: String
    },
    showArrow: {
      type: Boolean,
      require: true,
      default: true
    },
    themeLight: {
      type: Boolean,
      require: true,
      default: false
    },
    closeOnSelect: {
      type: Boolean,
      require: true,
      default: true
    },
    appendToBody: {
      type: Boolean,
      default: true
    }
  },
  data () {
    return {
      toggle: true,
      hasChild: true,
      rightAlign: false,
      dropdownPosition: null
    }
  },
  mounted () {
    this.$nextTick(() => {
      this.setDropdownPosition()
      if (this.appendToBody) {
        window.addEventListener('resize', this.setDropdownPosition)
        window.addEventListener('scroll', this.setDropdownPosition, true)
      }
      if (!this.$slots.default) {
        this.hasChild = false
      }
      this.toggle = false
    })
  },
  beforeUnmount () {
    if (this.appendToBody) {
      window.removeEventListener('resize', this.setDropdownPosition)
      window.removeEventListener('scroll', this.setDropdownPosition, true)
    }
  },
  methods: {
    setDropdownPosition (event) {
      const menu = this.$refs.dropdownItems

      if (!menu || !this.$el) {
        return
      }

      // Node check, not just a truthiness check: this same handler is bound to
      // `resize` as well as `scroll`, and a resize event's target is `window`,
      // which is not a Node. `menu.contains(window)` throws a TypeError, which
      // aborted the repositioning below and left the menu where it was.
      if (event?.target instanceof Node && menu.contains(event.target)) {
        return
      }

      if (!this.appendToBody) {
        const rect = menu.getBoundingClientRect()
        this.rightAlign = rect.right > window.innerWidth
        return
      }

      if (!this.toggle) {
        return
      }

      const triggerRect = this.$el.getBoundingClientRect()
      const menuRect = menu.getBoundingClientRect()
      const viewportPadding = 8
      const menuGap = 4
      const width = Math.min(
        Math.max(menuRect.width, 160),
        Math.max(window.innerWidth - (viewportPadding * 2), 0)
      )
      const spaceBelow = window.innerHeight - triggerRect.bottom - viewportPadding
      const spaceAbove = triggerRect.top - viewportPadding
      const openAbove = menuRect.height > spaceBelow && spaceAbove > spaceBelow
      const availableHeight = Math.max(openAbove ? spaceAbove : spaceBelow, 96)
      const renderedHeight = Math.min(menuRect.height, availableHeight)

      let left = triggerRect.left
      if (left + width > window.innerWidth - viewportPadding) {
        left = triggerRect.right - width
        this.rightAlign = true
      } else {
        this.rightAlign = false
      }
      left = Math.min(
        Math.max(left, viewportPadding),
        Math.max(window.innerWidth - width - viewportPadding, viewportPadding)
      )

      const desiredTop = openAbove
        ? triggerRect.top - renderedHeight - menuGap
        : triggerRect.bottom + menuGap
      const top = Math.min(
        Math.max(desiredTop, viewportPadding),
        Math.max(window.innerHeight - renderedHeight - viewportPadding, viewportPadding)
      )

      this.dropdownPosition = {
        position: 'fixed',
        top: `${top}px`,
        right: 'auto',
        bottom: 'auto',
        left: `${left}px`,
        width: `${width}px`,
        maxHeight: `${availableHeight}px`,
        overflowY: 'auto',
        zIndex: 12000
      }
    },
    isActive () {
      if (this.activeUrl) {
        return this.$route.path.indexOf(this.activeUrl) > -1
      }
      return false
    },
    showDropdown () {
      this.toggle = !this.toggle
      if (this.toggle && this.appendToBody) {
        this.$nextTick(() => this.setDropdownPosition())
      }
    },
    closeOnSelectDropdownItem () {
      if (this.closeOnSelect === false) {
        this.toggle = true
      } else {
        this.toggle = false
      }
    },
    closeDropdown (event) {
      if (
        this.appendToBody &&
        this.closeOnSelect === false &&
        event?.target &&
        this.$refs.dropdownItems?.contains(event.target)
      ) {
        return
      }
      this.toggle = false
    }
  }
}
</script>
<style>
  .dropdown-item {
    background: transparent;
    transform-origin: top;
    position: relative;
    color:#040405;
    animation-name: example;
    animation-duration: 1s;
    animation-iteration-count: 1;
    animation-direction: alternate;
  }
  .bounce-enter-active {
    /* zoom: 1; */
    transform-origin: top right;
    margin-top: 5px;
    animation: bounce-in 0.4s;
  }
  .bounce-leave-active {
     animation: bounce-in 1s reverse;
  }

@keyframes example {
 0% {color: #FFFFFF;}
 100% {color:#040405;}
}

@keyframes bounce-in {
  from { transform: scale(0); }
  to {  }
}
</style>
