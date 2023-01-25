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
      <button type="button"  @click="showModal" class="btn ml-5 mb-3 btn-default">
 
<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 50 50" width="30px" height="30px">
<path d="M 14 4 C 8.4886661 4 4 8.4886661 4 14 L 4 36 C 4 41.511334 8.4886661 46 14 46 L 36 46 C 41.511334 46 46 41.511334 46 36 L 46 14 C 46 8.4886661 41.511334 4 36 4 L 14 4 z M 14 6 L 36 6 C 40.430666 6 44 9.5693339 44 14 L 44 36 C 44 40.430666 40.430666 44 36 44 L 14 44 C 9.5693339 44 6 40.430666 6 36 L 6 14 C 6 9.5693339 9.5693339 6 14 6 z M 18 9 C 15.250484 9 13 11.250484 13 14 L 13 36 C 13 38.749516 15.250484 41 18 41 L 32 41 C 34.749516 41 37 38.749516 37 36 L 37 14 C 37 11.250484 34.749516 9 32 9 L 18 9 z M 18 11 L 32 11 C 33.668484 11 35 12.331516 35 14 L 35 36 C 35 37.668484 33.668484 39 32 39 L 18 39 C 16.331516 39 15 37.668484 15 36 L 15 14 C 15 12.331516 16.331516 11 18 11 z M 18 13 C 17.448 13 17 13.448 17 14 L 17 18 C 17 18.552 17.448 19 18 19 L 32 19 C 32.552 19 33 18.552 33 18 L 33 14 C 33 13.448 32.552 13 32 13 L 18 13 z M 19 21 A 2 2 0 0 0 19 25 A 2 2 0 0 0 19 21 z M 25 21 A 2 2 0 0 0 25 25 A 2 2 0 0 0 25 21 z M 31 21 A 2 2 0 0 0 31 25 A 2 2 0 0 0 31 21 z M 19 27 A 2 2 0 0 0 19 31 A 2 2 0 0 0 19 27 z M 25 27 A 2 2 0 0 0 25 31 A 2 2 0 0 0 25 27 z M 31 27 A 2 2 0 0 0 31 31 A 2 2 0 0 0 31 27 z M 19 33 C 17.895 33 17 33.895 17 35 C 17 36.105 17.895 37 19 37 L 25 37 C 26.105 37 27 36.105 27 35 C 27 33.895 26.105 33 25 33 L 19 33 z M 31 33 A 2 2 0 0 0 31 37 A 2 2 0 0 0 31 33 z"/>
</svg>
</button>
       <div class="modal" id="showModal">
  <div class="modal-dialog mt-5 pt-5">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">OmtBiz calculator</h5>
        <button type="button" @click="closeModal" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <vue-advanced-calculator description="Advance Cacluclator" title="OmtBiz Cacluclator"/>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>
</template>
<style>
.justify-content-between .btn-sm {
  visibility:hidden !important
}
</style>
<script type="text/babel">
import {VueAdvancedCalculator} from 'vue-advanced-calculator'
import 'vue-advanced-calculator/dist/vue-advanced-calculator.min.css'
export default {
  components: {
  VueAdvancedCalculator
},
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
            title: "navigation.orders",
            icon: "plus",
            route: "/orders",
            meta: ["admin"],
          },
          {
            title: "navigation.estimates",
            icon: "file",
            route: "/estimates",
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
            route: "/receipts",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.payments",
            icon: "credit-card",
            route: "/payments",
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
     closeModal() {
    document.getElementById('showModal').style.display="none";
   },
     showModal() {
    document.getElementById('showModal').style.display="block";
   }
  },
};
</script>
