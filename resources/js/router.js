import { createRouter, createWebHistory } from 'vue-router'
import store from './store/index.js'

/*
 |--------------------------------------------------------------------------
 | Admin Views
 |--------------------------------------------------------------------------|
 */

// Layouts
import LayoutBasic from './views/layouts/LayoutBasic.vue'
import LayoutLogin from './views/layouts/LayoutLogin.vue'

// Route views are lazy-loaded so the initial bundle only contains the active workflow.
const Login = () => import('./views/auth/Login.vue')
const ForgotPassword = () => import('./views/auth/ForgotPassword.vue')
const ResetPassword = () => import('./views/auth/ResetPassword.vue')
const Register = () => import('./views/auth/Register.vue')
const NotFoundPage = () => import('./views/errors/404.vue')

/*
 |--------------------------------------------------------------------------
 | Admin Views
 |--------------------------------------------------------------------------|
 */

// Customers
const CustomerIndex = () => import('./views/customers/Index.vue')
const CustomerCreate = () => import('./views/customers/Create.vue')

// Items
const ItemsIndex = () => import('./views/items/Index.vue')
const ItemCreate = () => import('./views/items/Create.vue')

// Raw Bill
const BillIndex = () => import('./views/raw-bill/Index.vue')
const BillCreate = () => import('./views/raw-bill/Create.vue')

// Invoices
const InvoiceIndex = () => import('./views/invoices/Index.vue')
const InvoiceCreate = () => import('./views/invoices/Create.vue')
const InvoiceBulk = () => import('./views/invoices/InvoiceBulk.vue')
const InvoiceView = () => import('./views/invoices/View.vue')

// Payments
const PaymentsIndex = () => import('./views/payments/Index.vue')
const PaymentCreate = () => import('./views/payments/Create.vue')

// Receipt
const ReceiptCreate = () => import('./views/receipts/Create.vue')
const ReceiptIndex = () => import('./views/receipts/Index.vue')
const ReceiptView = () => import('./views/receipts/View.vue')

// Estimates
const EstimateIndex = () => import('./views/estimates/Index.vue')
const EstimateCreate = () => import('./views/estimates/Create.vue')
const EstimateView = () => import('./views/estimates/View.vue')

// Orders
const OrderIndex = () => import('./views/orders/Index.vue')
const OrderCreate = () => import('./views/orders/Create.vue')
const OrderView = () => import('./views/orders/View.vue')

// Expenses
const ExpensesIndex = () => import('./views/expenses/Index.vue')
const ExpenseCreate = () => import('./views/expenses/Create.vue')

// Report
const SalesReports = () => import('./views/reports/SalesReports.vue')
const ExpensesReport = () => import('./views/reports/ExpensesReport.vue')
const ProfitLossReport = () => import('./views/reports/ProfitLossReport.vue')
const CustomersReport = () => import('./views/reports/CustomersReport.vue')
const ReportLayout = () => import('./views/reports/layout/Index.vue')
const BanksReport = () => import('./views/reports/BanksReport.vue')

// Users
const UserIndex = () => import('./views/users/Index.vue')
const UserCreate = () => import('./views/users/Create.vue')

// Settings
const SettingsLayout = () => import('./views/settings/layout/Index.vue')
const CompanyInfo = () => import('./views/settings/CompanyInfo.vue')
const Customization = () => import('./views/settings/Customization.vue')
const Notifications = () => import('./views/settings/Notifications.vue')
const Calculator = () => import('./views/settings/Calculator.vue')
const Preferences = () => import('./views/settings/Preferences.vue')
const UserProfile = () => import('./views/settings/UserProfile.vue')
const ExpenseCategory = () => import('./views/settings/ExpenseCategory.vue')
const MailConfig = () => import('./views/settings/MailConfig.vue')

// notes
const NotesIndex = () => import('./views/notes/Index.vue')
const NotesCreate = () => import('./views/notes/Create.vue')

// inventory
const InventoryIndex = () => import('./views/inventory/Index.vue')
const InventoryCreate = () => import('./views/inventory/Create.vue')
const InventoryStock = () => import('./views/inventory/InventoryStock.vue')
const InvoiceStock = () => import('./views/inventory/InvoiceStock.vue')

// Account Master
const MastersIndex = () => import('./views/masters/Index.vue')
const MastersCreate = () => import('./views/masters/Create.vue')

// Account Ledgers
const LedgersIndex = () => import('./views/ledgers/Index.vue')
const LedgersCreate = () => import('./views/ledgers/Create.vue')
const LedgersDisplay = () => import('./views/ledgers/Display.vue')

// Vouchers
const VouchersIndex = () => import('./views/vouchers/Index.vue')
const VouchersCreate = () => import('./views/vouchers/Create.vue')
const VouchersBook = () => import('./views/vouchers/Book.vue')
const VouchersDaysheet = () => import('./views/vouchers/Daysheet.vue')
const VouchersDaybook = () => import('./views/vouchers/Daybook.vue')

// bank
const BankIndex = () => import('./views/bank/Index.vue')
const BankCreate = () => import('./views/bank/Create.vue')

// Dispatch
const DispatchIndex = () => import('./views/dispatch/Index.vue')
const DispatchCreate = () => import('./views/dispatch/Create.vue')

const routes = [
    /*
     |--------------------------------------------------------------------------
     | Frontend Routes
     |--------------------------------------------------------------------------|
     */

    /*
     |--------------------------------------------------------------------------
     | Auth & Registration Routes
     |--------------------------------------------------------------------------|
     */

    {
        path: '/',
        component: LayoutLogin,
        meta: { redirectIfAuthenticated: true },
        children: [{
                path: '/',
                component: Login
            },
            {
                path: 'login',
                component: Login,
                name: 'login'
            },
            {
                path: '/forgot-password',
                component: ForgotPassword,
                name: 'forgot-password'
            },
            {
                path: '/reset-password/:token',
                component: ResetPassword,
                name: 'reset-password'
            },
            {
                path: 'register',
                component: Register,
                name: 'register'
            }
        ]
    },

    /*
     |--------------------------------------------------------------------------
     | Admin Backend Routes
     |--------------------------------------------------------------------------|
     */
    {
        path: '/',
        component: LayoutBasic, // Change the desired Layout here
        meta: { requiresAuth: true },
        children: [

            // Customer
            {
                path: 'customers',
                component: CustomerIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'customers/create',
                name: 'customers.create',
                component: CustomerCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'customers/:id/edit',
                name: 'customers.edit',
                component: CustomerCreate,
                meta: ['admin', 'accountant']
            },

            // Items
            {
                path: 'bill-ty',
                component: ItemsIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'bill-ty/create',
                name: 'items.create',
                component: ItemCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'bill-ty/:id/edit',
                name: 'items.edit',
                component: ItemCreate,
                meta: ['admin', 'accountant']
            },

            // Account Masters
            {
              path: 'masters',
              component: MastersIndex,
              meta: ['admin', 'accountant']
            },
            {
                path: 'masters/create',
                name: 'masters.create',
                component: MastersCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'masters/:id/edit',
                name: 'masters.edit',
                component: MastersCreate,
                meta: ['admin', 'accountant']
            },

            // Account Ledger
            {
                path: 'ledgers',
                component: LedgersIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'ledgers/create',
                name: 'ledgers.create',
                component: LedgersCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'ledgers/:id/edit',
                name: 'ledgers.edit',
                component: LedgersCreate,
                meta: ['admin', 'accountant']
            },
            {
              path: 'ledgers/:id/display',
              name: 'ledgers.display',
              component: LedgersDisplay,
              meta: ['admin', 'accountant']
            },

            // Voucher
            {
              path: 'vouchers',
              name: 'vouchers.index',
              component: VouchersIndex,
              meta: ['admin', 'accountant']
            },
            {
              path: 'vouchers/approvals',
              name: 'vouchers.approvals',
              component: VouchersIndex,
              meta: ['admin'],
              props: { approvalMode: true }
            },
            {
                path: 'vouchers/create',
                name: 'vouchers.create',
                component: VouchersCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'vouchers/:id/edit',
                name: 'vouchers.edit',
                component: VouchersCreate,
                meta: ['admin', 'accountant']
            },
            {
              path: 'vouchers/:id/book',
              name: 'vouchers.book',
              component: VouchersBook,
              meta: ['admin', 'accountant']
            },
            {
              path: 'vouchers/daybook',
              name: 'vouchers.daybook',
              component: VouchersDaybook,
              meta: ['admin', 'accountant']
            },
            {
              path: 'vouchers/daysheet',
              name: 'vouchers.daysheet',
              component: VouchersDaysheet,
              meta: ['admin', 'accountant']
            },

            //Raw bill
            {
                path: 'bills',
                component: BillIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'bill/create',
                name: 'bill.create',
                component: BillCreate,
                meta: ['admin', 'accountant']
            },
            // Estimate
            {
                path: 'estimates',
                name: 'estimates.index',
                component: EstimateIndex,
                meta: ['admin', 'accountant', 'estimate']
            },
            {
                path: 'estimates/create',
                name: 'estimates.create',
                component: EstimateCreate,
                meta: ['admin', 'accountant', 'estimate']
            },
            {
                path: 'estimates/:id/view',
                name: 'estimates.view',
                component: EstimateView,
                meta: ['admin', 'accountant', 'estimate']
            },
            {
                path: 'estimates/:id/edit',
                name: 'estimates.edit',
                component: EstimateCreate,
                meta: ['admin', 'accountant', 'estimate']
            },

            // Orders
            {
                path: 'orders',
                name: 'orders.index',
                component: OrderIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'orders/create',
                name: 'orders.create',
                component: OrderCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'orders/:id/view',
                name: 'orders.view',
                component: OrderView,
                meta: ['admin', 'accountant']
            },
            {
                path: 'orders/:id/edit',
                name: 'orders.edit',
                component: OrderCreate,
                meta: ['admin', 'accountant']
            },

            // Invoice
            {
                path: 'invoices',
                name: 'invoices.index',
                component: InvoiceIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'invoices/create',
                name: 'invoices.create',
                component: InvoiceCreate,
                meta: ['admin', 'accountant']
            },
            {
              path: 'invoices/bulk',
              name: 'invoices.bulk',
              component: InvoiceBulk,
              meta: ['admin', 'accountant']
          },
            {
                path: 'invoices/:id/view',
                name: 'invoices.view',
                component: InvoiceView,
                meta: ['admin', 'accountant']
            },
            {
                path: 'invoices/:id/edit',
                name: 'invoices.edit',
                component: InvoiceCreate,
                meta: ['admin']
            },

            // Payments
            {
                path: 'payments',
                name: 'payments.index',
                component: PaymentsIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'payments/create',
                name: 'payments.create',
                component: PaymentCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'payments/:id/create',
                name: 'invoice.payments.create',
                component: PaymentCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'payments/:id/edit',
                name: 'payments.edit',
                component: PaymentCreate,
                meta: ['admin']
            },

            //Receipt
            {
              path: 'receipts/create',
              name: 'receipts.create',
              component: ReceiptCreate,
              meta: ['admin', 'accountant']
            },
            {
                path: 'receipts/approvals',
                name: 'receipts.approvals',
                component: ReceiptIndex,
                meta: ['admin'],
                props: { approvalMode: true }
            },
            {
                path: 'receipts/:id/create',
                name: 'invoice.receipts.create',
                component: ReceiptCreate,
                meta: ['admin']
            },
            {
                path: 'receipts/:id/edit',
                name: 'receipts.edit',
                component: ReceiptCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'receipts',
                name: 'receipts.index',
                component: ReceiptIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'receipts/:id/view',
                name: 'receipts.view',
                component: ReceiptView,
                meta: ['admin', 'accountant']
            },

            // Expenses
            {
                path: 'expenses',
                component: ExpensesIndex,
                meta: ['admin', 'accountant']
            },
            {
                path: 'expenses/create',
                name: 'expenses.create',
                component: ExpenseCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'expenses/:id/edit',
                name: 'expenses.edit',
                component: ExpenseCreate,
                meta: ['admin', 'accountant']
            },

            // Reports
            {
                path: 'reports',
                component: ReportLayout,
                children: [{
                        path: 'sales',
                        component: SalesReports,
                        meta: ['admin', 'accountant']
                    },
                    {
                        path: 'expenses',
                        component: ExpensesReport,
                        meta: ['admin', 'accountant']
                    },
                    {
                        path: 'profit-loss',
                        component: ProfitLossReport,
                        meta: ['admin', 'accountant']
                    },
                    {
                      path: 'customers',
                      component: CustomersReport,
                      meta: ['admin', 'accountant']
                    },
                    {
                      path: 'banks',
                      component: BanksReport,
                      meta: ['admin', 'accountant']
                    }
                ]
            },

            // Notes
            {
              path: 'notes',
              component: NotesIndex,
              meta: ['admin', 'accountant']
            },
            {
                path: 'notes/create',
                name: 'notes.create',
                component: NotesCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'notes/:id/edit',
                name: 'notes.edit',
                component: NotesCreate,
                meta: ['admin', 'accountant']
            },

            // Bank
            {
              path: 'bank',
              component: BankIndex,
              meta: ['admin', 'accountant']
            },
            {
                path: 'bank/create',
                name: 'bank.create',
                component: BankCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'bank/:id/edit',
                name: 'bank.edit',
                component: BankCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'calculator',
                name: 'calculator',
                component: Calculator,
                meta: ['admin', 'accountant']
            },
            // Dispatch
            {
              path: 'dispatch',
              component: DispatchIndex,
              meta: ['admin', 'accountant', 'dispatch']
            },
            {
                path: 'dispatch/create',
                name: 'dispatch.create',
                component: DispatchCreate,
                meta: ['admin', 'accountant', 'dispatch']
            },
            {
                path: 'dispatch/:id/edit',
                name: 'dispatch.edit',
                component: DispatchCreate,
                meta: ['admin', 'accountant', 'dispatch']
            },
            {
                path: 'dispatch/:id/to-be-edit',
                name: 'dispatch.tobeedit',
                component: DispatchCreate,
                meta: ['admin', 'accountant', 'dispatch']
            },

             // Inventroy
            {
              path: 'inventory',
              component: InventoryIndex,
              meta: ['admin', 'accountant']
            },
            {
                path: 'inventory/create',
                name: 'inventory.create',
                component: InventoryCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'inventory/:id/edit',
                name: 'inventory.edit',
                component: InventoryCreate,
                meta: ['admin', 'accountant']
            },
            {
              path: 'inventory/stock',
              name: 'inventory.stock',
              component: InventoryStock,
              meta: ['admin', 'accountant']
            },
            {
              path: 'inventory/:id/stock',
              name: 'invoice.stock',
              component: InvoiceStock,
              meta: ['admin', 'accountant']
            },

            // User
            {
                path: 'users',
                component: UserIndex,
                meta: ['admin']
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: UserCreate,
                meta: ['admin']
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: UserCreate,
                meta: ['admin']
            },

            // Settings
            {
                path: 'settings',
                component: SettingsLayout,
                children: [{
                        path: 'company-info',
                        name: 'company.info',
                        component: CompanyInfo,
                        meta: ['admin']
                    },
                    {
                        path: 'customization',
                        name: 'customization',
                        component: Customization,
                        meta: ['admin']
                    },
                    {
                        path: 'user-profile',
                        name: 'user.profile',
                        component: UserProfile,
                        meta: ['admin']
                    },
                    {
                        path: 'preferences',
                        name: 'preferences',
                        component: Preferences,
                        meta: ['admin']
                    },
                    {
                        path: 'expense-category',
                        name: 'expense.category',
                        component: ExpenseCategory,
                        meta: ['admin']
                    },
                    {
                        path: 'mail-configuration',
                        name: 'mailconfig',
                        component: MailConfig,
                        meta: ['admin']
                    },
                    {
                        path: 'notifications',
                        name: 'notifications',
                        component: Notifications,
                        meta: ['admin']
                    },
                ]
            },
        ]
    },

    //  DEFAULT ROUTE
    { path: '/:pathMatch(.*)*', component: NotFoundPage }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    linkActiveClass: 'active'
})

router.beforeEach((to, from, next) => {
    let role = Ls.get('role');
    //  Redirect if not authenticated on secured routes
    if (to.matched.some(m => m.meta.requiresAuth)) {
        if (!store.getters['auth/isAuthenticated']) {
            return next('/login')
        }
    }

    if (to.matched.some(m => m.meta.redirectIfAuthenticated) && store.getters['auth/isAuthenticated']) {
        switch (role) {
            case 'admin':
                return next('/invoices/create')
            case 'accountant':
                return next('/invoices/create')
            case 'estimate':
                return next('/estimates/create')
            case 'dispatch':
                return next('/dispatch/create')
            default:
                return next('/invoices/create')
        }
    }

    if (to.meta.length) {
        if (to.meta.includes('admin') && role === 'admin') {
            next()
        } else if (to.meta.includes('accountant') && role === 'accountant') {
            next()
        } else if (to.meta.includes('estimate') && role === 'estimate') {
            next()
        } else if (to.meta.includes('dispatch') && role === 'dispatch') {
            next()
        }
         else if (role && role !== 'undefined') {
            switch (role) {
                case 'admin':
                    return next('/invoices/create')
                case 'accountant':
                    return next('/invoices/create')
                case 'accountant':
                    return next('/bill-ty')
                case 'estimate':
                return next('/estimates/create')
                case 'dispatch':
                    return next('/dispatch/create')
                default:
                    return next('/invoices/create')
            }
        }
    }

    return next()
})

export default router
