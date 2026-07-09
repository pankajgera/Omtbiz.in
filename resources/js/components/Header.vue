<template>
  <div class="d-flex flex-row flex-wrap justify-content-between align-items-center">
    <div class="d-flex flex-column">
      <h3 class="page-title">{{ title }}</h3>
        <ol class="breadcrumb">
            <li v-for="(link,index) in breadCrumbLinks" :key="index" class="breadcrumb-item">
              <router-link
                slot="item-title"
                :to="link.url">
                {{ link.title }}
              </router-link>
            </li>
        </ol>
    </div>
    <div class="d-flex flex-wrap">
        <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name:'Header',
  props: {
    title: {
      type: String,
    },
    breadCrumbLinks: {
      type: Array,
      default: () => []
    }
  },
}
</script>
