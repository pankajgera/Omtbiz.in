<template>
  <div class="sidebar-left">
    <div class="sidebar-body scroll-pane">
      <nav class="side-nav" aria-label="Primary navigation">
        <div v-for="(menuItems, index) in menu" :key="index" class="menu-group">
          <router-link
            v-for="(item, index1) in menuItems.filter((i) =>
              i.meta.includes(role)
            )"
            :key="index1"
            :to="item.route"
            :exact="!!item.exact"
            class="menu-item"
            @click.native="Toggle"
          >
            <font-awesome-icon :icon="item.icon" class="icon menu-icon" />
            <span class="ml-3 menu-text">{{ $t(item.title) }}</span>
          </router-link>
        </div>
      </nav>
      <button type="button" @click="showModal" class="calculator-button" aria-label="Open calculator" title="Calculator">
        <font-awesome-icon icon="calculator" />
      </button>
       <div v-if="calculatorOpen" class="modal calculator-modal" id="showModal" role="dialog" aria-modal="true" aria-labelledby="calculator-title">
  <div class="modal-dialog mt-5 pt-5">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="calculator-title" class="modal-title">Calculator</h5>
        <button type="button" @click="closeModal" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <vue-advanced-calculator description="Advance Cacluclator" title="Cacluclator"/>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>
</template>
<script type="text/babel">
import { defineAsyncComponent } from 'vue'
import 'vue-advanced-calculator/dist/vue-advanced-calculator.min.css'

const VueAdvancedCalculator = defineAsyncComponent(() =>
  import('vue-advanced-calculator').then(module => module.VueAdvancedCalculator)
)

export default {
  components: {
  VueAdvancedCalculator
},
  props: ["type", "role"],
  data() {
    return {
      sidebar: "sidebar",
      calculatorOpen: false,
      menu: [
        [
          {
            title: "navigation.master",
            icon: "file",
            route: "/masters",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.inventory",
            icon: "envelope",
            route: "/inventory",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.orders",
            icon: "plus",
            route: "/orders",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.estimates",
            icon: "file",
            route: "/estimates",
            meta: ["admin", "estimate", "accountant"],
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
            meta: ["admin", "accountant", "dispatch"],
          },
          {
            title: "navigation.items",
            icon: "file",
            route: "/bill-ty",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.receipts",
            icon: "credit-card",
            route: "/receipts/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.receipt_approvals",
            icon: "check-circle",
            route: "/receipts/approvals",
            meta: ["admin"],
          },
          {
            title: "navigation.payments",
            icon: "credit-card",
            route: "/payments",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.voucher",
            icon: "file-alt",
            route: "/vouchers",
            exact: true,
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.voucher_approvals",
            icon: "check-circle",
            route: "/vouchers/approvals",
            meta: ["admin"],
          },
          {
            title: "navigation.notes",
            icon: "envelope",
            route: "/notes",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.ledger",
            icon: "file",
            route: "/ledgers",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.reports",
            icon: "signal",
            route: "/reports",
            meta: ["admin", "accountant"],
          },
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
  methods: {
    Toggle() {
      this.$utils.toggleSidebar();
    },
    closeModal() {
      this.calculatorOpen = false;
    },
    showModal() {
      this.calculatorOpen = true;
    }
  },
};
</script>
