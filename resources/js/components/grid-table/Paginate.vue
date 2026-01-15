<template>
  <div class="grid-paginate">
    <div class="grid-paginate-item grid-page-count">
      <span class="grid-paginate-item">Show</span>
      <select v-model.number="count" :value="pageCount">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
        <option value="40">40</option>
        <option value="50">50</option>
      </select>
    </div>
    <span class="grid-paginate-item grid-page-current">Page {{ page + 1 }} of {{ pages }}</span>
    <span class="grid-paginate-item grid-page-rows">Rows: {{ totalRows }}</span>
    <button
      class="grid-paginate-item grid-paginate-button"
      type="button"
      @click="prev"
      :disabled="page === 0"
    >
      Prev
    </button>
    <button
      class="grid-paginate-item grid-paginate-button"
      type="button"
      @click="next"
      :disabled="page + 1 === pages"
    >
      Next
    </button>
  </div>
</template>

<script>
export default {
  props: {
    page: { type: Number, required: true },
    pages: { type: Number, required: true },
    pageCount: { type: Number, required: true },
    totalRows: { type: Number, required: true }
  },
  data () {
    return { count: this.pageCount }
  },
  watch: {
    count () {
      this.$emit('count-changed', this.count)
    }
  },
  methods: {
    prev () {
      this.$emit('prev')
    },
    next () {
      this.$emit('next')
    }
  }
}
</script>

<style lang="scss" scoped>
.grid-paginate {
  display: inline-flex;
}

.grid-paginate-button {
  border: solid 1px #dadada;

  &:disabled {
    border-color: transparent;
    opacity: 0.5;
  }
}

.grid-paginate-item {
  margin-right: 10px;

  &:last-child {
    margin-right: 0;
  }
}
</style>
