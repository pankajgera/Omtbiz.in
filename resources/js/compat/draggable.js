import draggable from 'vuedraggable'

/**
 * vuedraggable v4 is a native Vue 3 component: it reads `$slots.item` as a
 * function and calls it with `{ element, index }`.
 *
 * The app runs on @vue/compat with RENDER_FUNCTION enabled globally, which
 * wraps every render function that takes fewer than two arguments and then
 * serves `$slots` through the legacy proxy. That proxy invokes each slot with
 * no arguments and hands back the resulting vnode array, so vuedraggable's
 * `item` slot receives `undefined` and the component throws before it renders.
 *
 * Opting this one component out of RENDER_FUNCTION compat restores the Vue 3
 * behaviour it expects, without changing how the rest of the app renders.
 * vuedraggable calls the `h` it imports directly rather than the one compat
 * injects, so it needs nothing from the legacy render path.
 */
draggable.compatConfig = {
  ...(draggable.compatConfig || {}),
  RENDER_FUNCTION: false,
}

export default draggable
