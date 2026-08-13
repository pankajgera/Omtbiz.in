<template>
  <nav v-if="shouldShowPagination" class="table-pagination" aria-label="Table pagination">
    <ul class="pagination">
      <li :class="{ disabled: pagination.currentPage === 1 }" class="page-item">
        <button
          :disabled="pagination.currentPage === 1"
          class="page-link pagination-control"
          type="button"
          aria-label="Previous page"
          @click="pageClicked(pagination.currentPage - 1)"
        >&lsaquo;</button>
      </li>
      <li v-if="hasFirst" :class="{ active: isActive(1) }" class="page-item">
        <button
          :aria-current="isActive(1) ? 'page' : undefined"
          class="page-link"
          type="button"
          @click="pageClicked(1)"
        >1</button>
      </li>
      <li v-if="hasFirstEllipsis" class="page-item pagination-gap">
        <span class="page-link pagination-ellipsis">&hellip;</span>
      </li>
      <li
        v-for="page in pages"
        :key="page"
        :class="{ active: isActive(page) }"
        class="page-item"
      >
        <button
          :aria-current="isActive(page) ? 'page' : undefined"
          class="page-link"
          type="button"
          @click="pageClicked(page)"
        >{{ page }}</button>
      </li>
      <li v-if="hasLastEllipsis" class="page-item pagination-gap">
        <span class="page-link pagination-ellipsis">&hellip;</span>
      </li>
      <li
        v-if="hasLast"
        :class="{ active: isActive(pagination.totalPages) }"
        class="page-item"
      >
        <button
          :aria-current="isActive(pagination.totalPages) ? 'page' : undefined"
          class="page-link"
          type="button"
          @click="pageClicked(pagination.totalPages)"
        >{{ pagination.totalPages }}</button>
      </li>
      <li :class="{ disabled: pagination.currentPage === pagination.totalPages }" class="page-item">
        <button
          :disabled="pagination.currentPage === pagination.totalPages"
          class="page-link pagination-control"
          type="button"
          aria-label="Next page"
          @click="pageClicked(pagination.currentPage + 1)"
        >&rsaquo;</button>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  props: {
    pagination: {
      type: Object,
      default: () => ({})
    }
  },

  computed: {
    pages () {
      return this.pagination.totalPages === undefined
        ? []
        : this.pageLinks()
    },

    hasFirst () {
      return this.pagination.totalPages > 1
    },

    hasLast () {
      return this.pagination.totalPages > 2
    },

    hasFirstEllipsis () {
      return this.pages.length > 0 && this.pages[0] > 2
    },

    hasLastEllipsis () {
      const lastPage = this.pages[this.pages.length - 1]
      return this.pages.length > 0 && lastPage < this.pagination.totalPages - 1
    },

    shouldShowPagination () {
      if (this.pagination.totalPages === undefined) {
        return false
      }

      if (this.pagination.count === 0) {
        return false
      }

      return this.pagination.totalPages > 1
    }
  },

  methods: {
    isActive (page) {
      const currentPage = this.pagination.currentPage || 1
      return currentPage === page
    },

    pageClicked (page) {
      if (page === this.pagination.currentPage ||
          page > this.pagination.totalPages ||
          page < 1) {
        return
      }

      this.$emit('pageChange', page)
    },

    pageLinks () {
      const pages = []
      const totalPages = this.pagination.totalPages
      const currentPage = this.pagination.currentPage || 1
      let left = Math.max(2, currentPage - 2)
      let right = Math.min(totalPages - 1, currentPage + 2)

      if (totalPages <= 7) {
        left = 2
        right = totalPages - 1
      } else if (currentPage <= 3) {
        right = 5
      } else if (currentPage >= totalPages - 2) {
        left = totalPages - 4
      }

      for (let i = left; i <= right; i++) {
        pages.push(i)
      }

      return pages
    }
  }
}
</script>
