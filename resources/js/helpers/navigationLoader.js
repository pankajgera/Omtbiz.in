import { reactive } from 'vue'

export const navigationLoader = reactive({ active: false })

// Register before async authorization guards so their network wait is visible.
export function installNavigationLoader (router) {
  let activeRoute = null
  let startedAt = 0
  let timer = null

  router.beforeEach((to, from) => {
    if (to.fullPath === from.fullPath) return
    window.clearTimeout(timer)
    activeRoute = to
    startedAt = Date.now()
    navigationLoader.active = true
  })

  const finish = (to) => {
    // A cancelled, older navigation must not hide a newer navigation's loader.
    if (to !== activeRoute) return
    window.clearTimeout(timer)
    timer = window.setTimeout(() => {
      if (to === activeRoute) {
        navigationLoader.active = false
        activeRoute = null
      }
    }, Math.max(0, 150 - (Date.now() - startedAt)))
  }

  router.afterEach((to) => finish(to))
  router.onError((error, to) => finish(to))
}
