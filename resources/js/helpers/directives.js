export function registerDirectives (app) {
  app.directive('click-outside', {
    beforeMount (el, binding) {
      const handler = function (event) {
        if (!(el === event.target || el.contains(event.target))) {
          if (typeof binding.value === 'function') {
            binding.value(event)
          }
        }
      }
      el.__clickOutsideHandler__ = handler
      document.body.addEventListener('click', handler)
    },
    unmounted (el) {
      document.body.removeEventListener('click', el.__clickOutsideHandler__)
      delete el.__clickOutsideHandler__
    }
  })

  app.directive('autoresize', {
    mounted (el) {
      el.style.height = el.scrollHeight + 'px'
      el.style.overflowY = 'hidden'
      el.style.resize = 'none'
      const onInput = function () {
        this.style.height = 'auto'
        this.style.height = this.scrollHeight + 'px'
        this.scrollTop = this.scrollHeight
        window.scrollTo(window.scrollLeft, this.scrollTop + this.scrollHeight)
      }
      el.__autoResizeHandler__ = onInput
      el.addEventListener('input', onInput, false)
    },
    unmounted (el) {
      if (el.__autoResizeHandler__) {
        el.removeEventListener('input', el.__autoResizeHandler__, false)
        delete el.__autoResizeHandler__
      }
    }
  })
}
