<template>
  <div class="sidebar-left">
    <div class="sidebar-body scroll-pane">
      <div class="side-nav">
        <div
          v-for="(menuItems, index) in menu"
          :key="index"
          class="menu-group"
        >
          <router-link
            v-for="(item, index1) in menuItems.filter(i => i.meta.includes(role))"
            :key="index1"
            :to="item.route"
            class="menu-item"
            @click.native="Toggle"
          >
            <font-awesome-icon :icon="item.icon" class="icon menu-icon" /> <span class="ml-3 menu-text">{{ $t(item.title) }}</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/babel">
export default {
  props: ['type', 'role'],
  data () {
    return {
      sidebar: 'sidebar',
      menu: [
        [
          {
            title: 'navigation.dashboard',
            icon: 'tachometer-alt',
            route: '/dashboard',
            meta: ['admin']
          },
          {
            title: 'navigation.items',
            icon: 'file',
            route: '/items',
            meta: ['admin', 'accountant', 'employee']
          },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          {
            title: 'navigation.customers',
            icon: 'user',
            route: '/customers',
            meta: ['admin', 'accountant']
          },
          {
            title: 'navigation.invoices',
            icon: 'file-alt',
            route: '/invoices',
            meta: ['admin', 'accountant']
          },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          {
            title: 'navigation.payments',
            icon: 'credit-card',
            route: '/payments',
            meta: ['admin', 'accountant']
          },
          {
            title: 'navigation.reports',
            icon: 'signal',
            route: '/reports',
            meta: ['admin']
          },
          {
            title: 'navigation.notes',
            icon: 'envelope',
            route: '/notes',
            meta: ['admin']
          },
        ],
        [
          {
            title: 'navigation.estimates',
            icon: 'file',
            route: '/estimates',
            meta: ['admin']
          },
          {
            title: 'navigation.expenses',
            icon: 'space-shuttle',
            route: '/expenses',
            meta: ['admin']
          },
          {
            title: 'navigation.users',
            icon: 'user-plus',
            iconType: 'fa',
            route: '/users',
            meta: ['admin', 'accountant']
          },
          {
            title: 'navigation.settings',
            icon: 'cog',
            route: '/settings',
            meta: ['admin', 'accountant']
          },
        ]
      ]
    }
  },
  updated() {
    this.update();
  },
  methods: {
    Toggle () {
      this.$utils.toggleSidebar()
    },
    update(){
      this.$mount();
    },
  }
}
</script>
