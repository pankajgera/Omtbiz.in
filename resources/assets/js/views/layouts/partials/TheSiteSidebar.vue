<template>
  <div class="sidebar-left">
    <div class="sidebar-body scroll-pane">
      <div class="side-nav">
        <div
          v-for="(menuItems, index) in setSidebarWithRole"
          :key="index"
          class="menu-group"
        >
          <router-link
            v-for="(item, index1) in menuItems"
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
            route: '/dashboard'
          },
          {
            title: 'navigation.items',
            icon: 'file',
            route: '/items'
          },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          {
            title: 'navigation.customers',
            icon: 'user',
            route: '/customers'
          },
          {
            title: 'navigation.estimates',
            icon: 'file',
            route: '/adsamin/estimates'
          },
          {
            title: 'navigation.invoices',
            icon: 'file-alt',
            route: '/invoices'
          },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          {
            title: 'navigation.payments',
            icon: 'credit-card',
            route: '/payments'
          },
          {
            title: 'navigation.expenses',
            icon: 'space-shuttle',
            route: '/expenses'
          },
          {
            title: 'navigation.reports',
            icon: 'signal',
            route: '/reports'
          },
          {
            title: 'navigation.settings',
            icon: 'cog',
            route: '/settings'
          }
        ]
      ],
    }
  },
  updated() {
    this.update();
  },
  computed: {
    setSidebarWithRole() {
      let links = [];
      switch(this.role) {
        case 'admin':
          links = [[
              {
                title: 'navigation.dashboard',
                icon: 'tachometer-alt',
                route: '/dashboard'
              },
              {
                title: 'navigation.items',
                icon: 'file',
                route: '/items'
              },
              {
                title: 'navigation.customers',
                icon: 'user',
                route: '/customers'
              },
              {
                title: 'navigation.estimates',
                icon: 'file',
                route: '/adsamin/estimates'
              },
              {
                title: 'navigation.invoices',
                icon: 'file-alt',
                route: '/invoices'
              },
              {
                title: 'navigation.payments',
                icon: 'credit-card',
                route: '/payments'
              },
              {
                title: 'navigation.expenses',
                icon: 'space-shuttle',
                route: '/expenses'
              },
              {
                title: 'navigation.reports',
                icon: 'signal',
                route: '/reports'
              },
              {
                title: 'navigation.settings',
                icon: 'cog',
                route: '/settings'
              }
          ]];
          return links;
          break;
        case 'accountant':
          break;
        case 'employee':
          links = [[
              {
                title: 'navigation.items',
                icon: 'file',
                route: '/items'
              }
          ]];
          return links;
          break;
      }
    }
  },
  methods: {
    Toggle () {
      this.$utils.toggleSidebar()
    },
    update(){
      this.$mount();
    }
  }
}
</script>
