<template>
  <div id="app-sidebar" class="sidebar-left tw:flex tw:flex-col tw:border-r tw:border-line tw:bg-sidebar tw:text-sidebar-ink">
    <div class="sidebar-body scroll-pane tw:min-h-0 tw:flex-1 tw:overflow-y-auto">
      <nav class="side-nav" aria-label="Primary navigation">
        <section
          v-for="section in visibleMenuSections"
          :key="section.id"
          class="menu-group tw:space-y-1"
          :aria-labelledby="`sidebar-section-${section.id}`"
        >
          <h2
            :id="`sidebar-section-${section.id}`"
            class="menu-section-title"
          >
            {{ $t(section.title) }}
          </h2>
          <router-link
            v-for="item in section.items"
            :key="item.route"
            :to="item.route"
            :exact="!!item.exact"
            class="menu-item tw:flex tw:min-h-10 tw:items-center tw:gap-3 tw:rounded-md tw:px-3 tw:py-2 tw:text-sm tw:font-medium tw:text-sidebar-ink tw:no-underline tw:transition-colors tw:hover:bg-sidebar-hover tw:hover:text-sidebar-ink tw:focus-visible:bg-sidebar-hover"
            @click.native="Toggle"
          >
            <font-awesome-icon :icon="item.icon" class="icon menu-icon" aria-hidden="true" />
            <span class="menu-text tw:text-inherit tw:no-underline">{{ $t(item.title) }}</span>
          </router-link>
        </section>
      </nav>
    </div>
      <button
        ref="calculatorButton"
        type="button"
        class="calculator-button tw:grid tw:size-10 tw:place-items-center tw:self-start tw:rounded-md tw:border tw:border-line tw:bg-transparent tw:text-sidebar-ink tw:hover:bg-sidebar-hover"
        aria-label="Open calculator"
        aria-keyshortcuts="Alt+C"
        title="Calculator"
        @click="showModal"
      >
        <font-awesome-icon icon="calculator" />
      </button>
    <div
      v-if="calculatorOpen"
      id="showModal"
      class="modal calculator-modal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="calculator-title"
      @click.self="closeModal"
      @keydown="handleModalKeydown"
    >
      <div ref="calculatorDialog" class="modal-dialog calculator-dialog" role="document">
        <div class="modal-content calculator-content">
          <div class="modal-header calculator-header">
            <h2 id="calculator-title" class="modal-title">Calculator</h2>
            <button
              type="button"
              class="calculator-close"
              aria-label="Close calculator"
              aria-keyshortcuts="Escape"
              title="Close calculator"
              @click="closeModal"
            >
              <font-awesome-icon icon="times" aria-hidden="true" />
            </button>
          </div>
          <div class="modal-body calculator-body">
            <div class="calculator-mode-switch" role="group" aria-label="Calculator mode">
              <button
                v-for="mode in calculatorModes"
                :key="mode.value"
                type="button"
                class="calculator-mode-button"
                :class="{ active: calculatorMode === mode.value }"
                :aria-pressed="calculatorMode === mode.value ? 'true' : 'false'"
                @click="setCalculatorMode(mode.value)"
                @keydown.stop
              >
                {{ mode.label }}
              </button>
            </div>
            <span id="calculator-keyboard-help" class="calculator-keyboard-help">
              Use number and operator keys, Enter to calculate, Backspace to delete, C to clear, and Escape to close.
            </span>
            <vue-advanced-calculator
              id="omtbiz-calculator"
              ref="advancedCalculator"
              class="calculator-engine"
              :default-mode="calculatorMode"
              aria-label="Calculator keypad"
              aria-describedby="calculator-keyboard-help"
              description="Advanced Calculator"
              title="Calculator"
            />
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
    VueAdvancedCalculator,
  },
  props: ["type", "role"],
  data() {
    return {
      sidebar: "sidebar",
      calculatorOpen: false,
      calculatorMode: "standard",
      calculatorModes: [
        { value: "standard", label: "Standard" },
        { value: "scientific", label: "Scientific" },
      ],
      menuSectionDefinitions: [
        { id: "business", title: "navigation.sections.business" },
        { id: "finance", title: "navigation.sections.finance" },
        { id: "insights", title: "navigation.sections.insights" },
        { id: "administration", title: "navigation.sections.administration" },
      ],
      menu: [
        [
          {
            title: "navigation.master",
            icon: "book-open",
            route: "/masters",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.inventory",
            icon: "boxes",
            route: "/inventory",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.orders",
            icon: "shopping-cart",
            route: "/orders",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.estimates",
            icon: "clipboard-list",
            route: "/estimates",
            meta: ["admin", "estimate", "accountant"],
          },
          {
            title: "navigation.invoices",
            icon: "file-invoice-dollar",
            route: "/invoices/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.dispatch",
            icon: "truck",
            route: "/dispatch",
            meta: ["admin", "accountant", "dispatch"],
          },
          {
            title: "navigation.items",
            icon: "file-contract",
            route: "/bill-ty",
            meta: ["admin", "accountant"],
          },
        ],
        [
          {
            title: "navigation.receipts",
            icon: "receipt",
            route: "/receipts/create",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.receipt_approvals",
            icon: "user-check",
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
            icon: "ticket-alt",
            route: "/vouchers",
            exact: true,
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.voucher_approvals",
            icon: "shield-alt",
            route: "/vouchers/approvals",
            meta: ["admin"],
          },
        ],
        [
          {
            title: "navigation.notes",
            icon: "sticky-note",
            route: "/notes",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.ledger",
            icon: "balance-scale",
            route: "/ledgers",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.reports",
            icon: "chart-bar",
            route: "/reports",
            meta: ["admin", "accountant"],
          },
        ],
        [
          {
            title: "navigation.users",
            icon: "users",
            route: "/users",
            meta: ["admin", "accountant"],
          },
          {
            title: "navigation.audit_logs",
            icon: "history",
            route: "/audit-logs",
            meta: ["admin"],
          },
          {
            title: "navigation.settings",
            icon: "sliders-h",
            route: "/settings",
            meta: ["admin", "accountant"],
          },
        ],
      ],
    };
  },
  computed: {
    visibleMenuSections() {
      return this.menu
        .map((items, index) => ({
          ...this.menuSectionDefinitions[index],
          items: items.filter((item) => item.meta.includes(this.role)),
        }))
        .filter((section) => section.items.length);
    },
  },
  methods: {
    Toggle() {
      if (window.matchMedia('(max-width: 991px)').matches) {
        this.$utils.toggleSidebar();
      }
    },
    closeModal() {
      if (!this.calculatorOpen) {
        return;
      }

      this.calculatorOpen = false;
      this.$nextTick(() => {
        const focusTarget = this.previouslyFocusedElement || this.$refs.calculatorButton;
        focusTarget?.focus?.();
      });
    },
    showModal() {
      if (this.calculatorOpen) {
        this.focusCalculator();
        return;
      }

      this.previouslyFocusedElement = document.activeElement;
      this.calculatorOpen = true;
      this.$nextTick(() => this.focusCalculator());
    },
    focusCalculator(attempt = 0) {
      const calculator = this.$el.querySelector(".calculator-modal .vac-container");

      if (calculator) {
        calculator.focus({ preventScroll: true });
        return;
      }

      if (attempt < 10) {
        window.setTimeout(() => this.focusCalculator(attempt + 1), 30);
      }
    },
    setCalculatorMode(mode) {
      this.calculatorMode = mode;
      this.$refs.advancedCalculator?.changeMode?.(mode);
      this.$nextTick(() => this.focusCalculator());
    },
    handleGlobalShortcut(event) {
      if (!event.altKey || event.ctrlKey || event.metaKey || event.key.toLowerCase() !== "c") {
        return;
      }

      event.preventDefault();
      this.calculatorOpen ? this.closeModal() : this.showModal();
    },
    handleModalKeydown(event) {
      if (event.key === "Escape") {
        event.preventDefault();
        this.closeModal();
        return;
      }

      if (event.key !== "Tab") {
        return;
      }

      const focusable = Array.from(
        this.$refs.calculatorDialog.querySelectorAll(
          'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )
      ).filter((element) => element.offsetParent !== null);

      if (!focusable.length) {
        event.preventDefault();
        return;
      }

      const currentIndex = focusable.indexOf(document.activeElement);
      const shouldWrapForward = !event.shiftKey && currentIndex === focusable.length - 1;
      const shouldWrapBackward = event.shiftKey && currentIndex <= 0;

      if (shouldWrapForward || shouldWrapBackward) {
        event.preventDefault();
        focusable[shouldWrapForward ? 0 : focusable.length - 1].focus();
      }
    },
  },
  mounted() {
    window.addEventListener("keydown", this.handleGlobalShortcut);
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.handleGlobalShortcut);
  },
};
</script>
