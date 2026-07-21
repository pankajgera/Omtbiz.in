import { h } from 'vue'

export default {
  props: ['column', 'row', 'responsiveLabel'],

  setup (props) {
    return () => {
      const cellProps = props.column.cellClass
        ? { class: props.column.cellClass }
        : {}

      if (props.column.template) {
        return h('td', cellProps, props.column.template(props.row.data))
      }

      const value = props.column.formatter(
        props.row.getValue(props.column.show),
        props.row.data
      )

      return h('td', cellProps, [
        h('span', props.responsiveLabel),
        h('span', { innerHTML: value === null || value === undefined ? '' : value })
      ])
    }
  }
}
