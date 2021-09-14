<template>
  <div class="sidebar-left">
    <div class="sidebar-body scroll-pane">
      <div class="side-nav">
        <div v-for="(menuItems, index) in menu" :key="index" class="menu-group">
          <router-link
            v-for="(item, index1) in menuItems.filter((i) =>
              i.meta.includes(role)
            )"
            :key="index1"
            :to="item.route"
            class="menu-item"
            @click.native="Toggle"
          >
            <font-awesome-icon :icon="item.icon" class="icon menu-icon" />
            <span class="ml-3 menu-text">{{ $t(item.title) }}</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/babel">
export default {
  props: ["type", "role"],
  data() {
    return {
      sidebar: "sidebar",
      menu: [
        [
          {
            title: "navigation.master",
            icon: "file",
            route: "/masters",
            meta: ["admin"],
          },
          {
            title: "navigation.inventory",
            icon: "envelope",
            route: "/inventory",
            meta: ["admin"],
          },
          {
            title: "navigation.invoices",
            icon: "file-alt",
            route: "/invoices/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.dispatch",
            icon: "file",
            route: "/dispatch",
            meta: ["admin"],
          },
          {
            title: "navigation.items",
            icon: "file",
            route: "/items",
            meta: ["admin", "accountant", "employee"],
          },
          {
            title: "navigation.receipts",
            icon: "credit-card",
            route: "/receipts/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.payments",
            icon: "credit-card",
            route: "/payments/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.voucher",
            icon: "file",
            route: "/vouchers/create",
            meta: ["admin"],
          },
          {
            title: "navigation.notes",
            icon: "envelope",
            route: "/notes",
            meta: ["admin"],
          },
          {
            title: "navigation.ledger",
            icon: "file",
            route: "/ledgers",
            meta: ["admin"],
          },
          {
            title: "navigation.reports",
            icon: "signal",
            route: "/reports",
            meta: ["admin"],
          },
          // {
          //   title: 'navigation.dashboard',
          //   icon: 'tachometer-alt',
          //   route: '/dashboard',
          //   meta: ['admin']
          // },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          // {
          //   title: 'navigation.customers',
          //   icon: 'user',
          //   route: '/customers',
          //   meta: ['admin', 'accountant']
          // },
          // {
          //   title: 'navigation.bills',
          //   icon: 'star',
          //   route: '/bills'
          // },
          // {
          //   title: 'navigation.bank',
          //   icon: 'file',
          //   route: '/bank',
          //   meta: ['admin']
          // },
        ],
        [
          // {
          //   title: "navigation.estimates",
          //   icon: "file",
          //   route: "/estimates",
          //   meta: ["admin"],
          // },
          // {
          //   title: "navigation.expenses",
          //   icon: "space-shuttle",
          //   route: "/expenses",
          //   meta: ["admin"],
          // },
          {
            title: "navigation.users",
            icon: "user-plus",
            iconType: "fa",
            route: "/users",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.settings",
            icon: "cog",
            route: "/settings",
            meta: ["admin", "accountant"],
          },
        ],
      ],
    };
  },
  updated() {
    this.update();
  },
  methods: {
    Toggle() {
      this.$utils.toggleSidebar();
    },
    update() {
      this.$mount();
    },
  },
};
</script>
