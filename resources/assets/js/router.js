import Vue from 'vue'
import VueRouter from 'vue-router'
import store from './store/index.js'

/*
 |--------------------------------------------------------------------------
 | Admin Views
 |--------------------------------------------------------------------------|
 */

// Layouts
import LayoutBasic from './views/layouts/LayoutBasic.vue'
import LayoutLogin from './views/layouts/LayoutLogin.vue'
import LayoutWizard from './views/layouts/LayoutWizard.vue'

// Auth
import Login from './views/auth/Login.vue'
import ForgotPassword from './views/auth/ForgotPassword.vue'
import ResetPassword from './views/auth/ResetPassword.vue'
import Register from './views/auth/Register.vue'

import NotFoundPage from './views/errors/404.vue'

/*
 |--------------------------------------------------------------------------
 | Admin Views
 |--------------------------------------------------------------------------|
 */

// Dashbord
//import Dashboard from './views/dashboard/Dashboard.vue'

// Customers
import CustomerIndex from './views/customers/Index.vue'
import CustomerCreate from './views/customers/Create.vue'

// Items
import ItemsIndex from './views/items/Index.vue'
import ItemCreate from './views/items/Create.vue'

// Raw Bill
import BillIndex from './views/raw-bill/Index.vue'
import BillCreate from './views/raw-bill/Create.vue'

// Invoices
import InvoiceIndex from './views/invoices/Index.vue'
import InvoiceCreate from './views/invoices/Create.vue'
import InvoiceView from './views/invoices/View.vue'

// Payments
// import PaymentsIndex from './views/payments/Index.vue'
import PaymentCreate from './views/payments/Create.vue'

//Receipt
import ReceiptCreate from './views/receipts/Create.vue'

// Estimates
import EstimateIndex from './views/estimates/Index.vue'
import EstimateCreate from './views/estimates/Create.vue'
import EstimateView from './views/estimates/View.vue'

// Orders
import OrderIndex from './views/orders/Index.vue'
import OrderCreate from './views/orders/Create.vue'
import OrderView from './views/orders/View.vue'


// Expenses
import ExpensesIndex from './views/expenses/Index'
import ExpenseCreate from './views/expenses/Create.vue'

// Report
import SalesReports from './views/reports/SalesReports'
import ExpensesReport from './views/reports/ExpensesReport'
import ProfitLossReport from './views/reports/ProfitLossReport'
import TaxReport from './views/reports/TaxReport.vue'
import CustomersReport from './views/reports/CustomersReport.vue'
import ReportLayout from './views/reports/layout/Index.vue'
import BanksReport from './views/reports/BanksReport.vue'

// Users
import UserIndex from './views/users/Index.vue'
import UserCreate from './views/users/Create.vue'

// Settings
import SettingsLayout from './views/settings/layout/Index.vue'
import CompanyInfo from './views/settings/CompanyInfo.vue'
import Customization from './views/settings/Customization.vue'
import Notifications from './views/settings/Notifications.vue'
import Preferences from './views/settings/Preferences.vue'
import UserProfile from './views/settings/UserProfile.vue'
import TaxTypes from './views/settings/TaxTypes.vue'
import ExpenseCategory from './views/settings/ExpenseCategory.vue'
import MailConfig from './views/settings/MailConfig.vue'
//import UpdateApp from './views/settings/UpdateApp.vue'

// notes
import NotesIndex from './views/notes/Index.vue'
import NotesCreate from './views/notes/Create.vue'

// inventory
import InventoryIndex from './views/inventory/Index.vue'
import InventoryCreate from './views/inventory/Create.vue'
import Inventory from './views/invoices/Inventory.vue'

// Account Master
import MastersIndex from './views/masters/Index.vue'
import MastersCreate from './views/masters/Create.vue'

// Account Ledgers
import LedgersIndex from './views/ledgers/Index.vue'
import LedgersCreate from './views/ledgers/Create.vue'
import LedgersDisplay from './views/ledgers/Display.vue'

// Vouchers
import VouchersIndex from './views/vouchers/Index.vue'
import VouchersCreate from './views/vouchers/Create.vue'
import VouchersBook from './views/vouchers/Book.vue'
import VouchersDaybook from './views/vouchers/Daybook.vue'

import Wizard from './views/wizard/Index.vue'

// bank
import BankIndex from './views/bank/Index.vue'
import BankCreate from './views/bank/Create.vue'

// Dispatch
import DispatchIndex from './views/dispatch/Index.vue'
import DispatchCreate from './views/dispatch/Create.vue'

Vue.use(VueRouter)

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
     | Onboarding Routes
     |--------------------------------------------------------------------------|
     */
    // {
    //     path: '/on-boarding',
    //     component: LayoutWizard,
    //     children: [{
    //         path: '/',
    //         component: Wizard,
    //         name: 'wizard'
    //     }]
    // },
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
            // Dashbord
            // {
            //     path: '/',
            //     component: Dashboard,
            //     name: 'dashboard',
            //     meta: ['admin']
            // },
            // {
            //     path: 'dashboard',
            //     component: Dashboard,
            //     meta: ['admin']
            // },

            // Customer
            {
                path: 'customers',
                component: CustomerIndex,
                meta: ['admin']
            },
            {
                path: 'customers/create',
                name: 'customers.create',
                component: CustomerCreate,
                meta: ['admin']
            },
            {
                path: 'customers/:id/edit',
                name: 'customers.edit',
                component: CustomerCreate,
                meta: ['admin']
            },

            // Items
            {
                path: 'items',
                component: ItemsIndex,
                meta: ['admin', 'employee']
            },
            {
                path: 'items/create',
                name: 'items.create',
                component: ItemCreate,
                meta: ['admin', 'employee']
            },
            {
                path: 'items/:id/edit',
                name: 'items.edit',
                component: ItemCreate,
                meta: ['admin', 'employee']
            },

            // Account Masters
            {
              path: 'masters',
              component: MastersIndex,
              meta: ['admin']
            },
            {
                path: 'masters/create',
                name: 'masters.create',
                component: MastersCreate,
                meta: ['admin']
            },
            {
                path: 'masters/:id/edit',
                name: 'masters.edit',
                component: MastersCreate,
                meta: ['admin']
            },

            // Account Ledger
            {
                path: 'ledgers',
                component: LedgersIndex,
                meta: ['admin']
            },
            {
                path: 'ledgers/create',
                name: 'ledgers.create',
                component: LedgersCreate,
                meta: ['admin']
            },
            {
                path: 'ledgers/:id/edit',
                name: 'ledgers.edit',
                component: LedgersCreate,
                meta: ['admin']
            },
            {
              path: 'ledgers/:id/display',
              name: 'ledgers.display',
              component: LedgersDisplay,
              meta: ['admin']
            },

            // Voucher
            {
              path: 'vouchers',
              component: VouchersIndex,
              meta: ['admin']
            },
            {
                path: 'vouchers/create',
                name: 'vouchers.create',
                component: VouchersCreate,
                meta: ['admin']
            },
            {
                path: 'vouchers/:id/edit',
                name: 'vouchers.edit',
                component: VouchersCreate,
                meta: ['admin']
            },
            {
              path: 'vouchers/:id/book',
              name: 'vouchers.book',
              component: VouchersBook,
              meta: ['admin']
            },
            {
              path: 'vouchers/daybook',
              name: 'vouchers.daybook',
              component: VouchersDaybook,
              meta: ['admin']
            },

            //Raw bill
            {
                path: 'bills',
                component: BillIndex,
                meta: ['admin']
            },
            {
                path: 'bill/create',
                name: 'bill.create',
                component: BillCreate,
                meta: ['admin']
            },
            // Estimate
            {
                path: 'estimates',
                name: 'estimates.index',
                component: EstimateIndex,
                meta: ['admin']
            },
            {
                path: 'estimates/create',
                name: 'estimates.create',
                component: EstimateCreate,
                meta: ['admin']
            },
            {
                path: 'estimates/:id/view',
                name: 'estimates.view',
                component: EstimateView,
                meta: ['admin']
            },
            {
                path: 'estimates/:id/edit',
                name: 'estimates.edit',
                component: EstimateCreate,
                meta: ['admin']
            },

            // Orders
            {
                path: 'orders',
                name: 'orders.index',
                component: OrderIndex,
                meta: ['admin']
            },
            {
                path: 'orders/create',
                name: 'orders.create',
                component: OrderCreate,
                meta: ['admin']
            },
            {
                path: 'orders/:id/view',
                name: 'orders.view',
                component: OrderView,
                meta: ['admin']
            },
            {
                path: 'orders/:id/edit',
                name: 'orders.edit',
                component: OrderCreate,
                meta: ['admin']
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
            // {
            //     path: 'payments',
            //     name: 'payments.index',
            //     component: PaymentsIndex,
            //     meta: ['admin', 'accountant']
            // },
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
                meta: ['admin', 'accountant']
            },

            //Receipt
            {
              path: 'receipts/create',
              name: 'receipts.create',
              component: ReceiptCreate,
              meta: ['admin', 'accountant']
            },
            {
                path: 'receipts/:id/create',
                name: 'invoice.receipts.create',
                component: ReceiptCreate,
                meta: ['admin', 'accountant']
            },
            {
                path: 'receipts/:id/edit',
                name: 'receipts.edit',
                component: ReceiptCreate,
                meta: ['admin', 'accountant']
            },

            // Expenses
            {
                path: 'expenses',
                component: ExpensesIndex,
                meta: ['admin']
            },
            {
                path: 'expenses/create',
                name: 'expenses.create',
                component: ExpenseCreate,
                meta: ['admin']
            },
            {
                path: 'expenses/:id/edit',
                name: 'expenses.edit',
                component: ExpenseCreate,
                meta: ['admin']
            },

            // Reports
            {
                path: 'reports',
                component: ReportLayout,
                children: [{
                        path: 'sales',
                        component: SalesReports,
                        meta: ['admin']
                    },
                    {
                        path: 'expenses',
                        component: ExpensesReport,
                        meta: ['admin']
                    },
                    {
                        path: 'profit-loss',
                        component: ProfitLossReport,
                        meta: ['admin']
                    },
                    {
                        path: 'taxes',
                        component: TaxReport,
                        meta: ['admin']
                    },
                    {
                      path: 'customers',
                      component: CustomersReport,
                      meta: ['admin']
                    },
                    {
                      path: 'banks',
                      component: BanksReport,
                      meta: ['admin']
                    }
                ]
            },

            // Notes
            {
              path: 'notes',
              component: NotesIndex,
              meta: ['admin']
            },
            {
                path: 'notes/create',
                name: 'notes.create',
                component: NotesCreate,
                meta: ['admin']
            },
            {
                path: 'notes/:id/edit',
                name: 'notes.edit',
                component: NotesCreate,
                meta: ['admin']
            },

            // Bank
            {
              path: 'bank',
              component: BankIndex,
              meta: ['admin']
            },
            {
                path: 'bank/create',
                name: 'bank.create',
                component: BankCreate,
                meta: ['admin']
            },
            {
                path: 'bank/:id/edit',
                name: 'bank.edit',
                component: BankCreate,
                meta: ['admin']
            },

            // Dispatch
            {
              path: 'dispatch',
              component: DispatchIndex,
              meta: ['admin']
            },
            {
                path: 'dispatch/create',
                name: 'dispatch.create',
                component: DispatchCreate,
                meta: ['admin']
            },
            {
                path: 'dispatch/:id/edit',
                name: 'dispatch.edit',
                component: DispatchCreate,
                meta: ['admin']
            },
            {
                path: 'dispatch/:id/to-be-edit',
                name: 'dispatch.tobeedit',
                component: DispatchCreate,
                meta: ['admin']
            },

             // Inventroy
            {
              path: 'inventory',
              component: InventoryIndex,
              meta: ['admin']
            },
            {
                path: 'inventory/create',
                name: 'inventory.create',
                component: InventoryCreate,
                meta: ['admin']
            },
            {
                path: 'inventory/:id/edit',
                name: 'inventory.edit',
                component: InventoryCreate,
                meta: ['admin']
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
                        meta: ['admin', 'accountant', 'employee']
                    },
                    {
                        path: 'preferences',
                        name: 'preferences',
                        component: Preferences,
                        meta: ['admin']
                    },
                    {
                        path: 'tax-types',
                        name: 'tax.types',
                        component: TaxTypes,
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
                    // {
                    //   path: 'update-app',
                    //   name: 'updateapp',
                    //   component: UpdateApp
                    // }
                ]
            },
        ]
    },

    //  DEFAULT ROUTE
    { path: '*', component: NotFoundPage }
]

const router = new VueRouter({
    routes,
    mode: 'history',
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
                break;
            case 'accountant':
                return next('/invoices/create')
                break;
            case 'employee':
                return next('/items')
                break;
            default:
                return next('/invoices/create')
                break;
        }
    }

    if (to.meta.length) {
        if (to.meta.includes('admin') && role === 'admin') {
            next()
        } else if (to.meta.includes('accountant') && role === 'accountant') {
            next()
        } else if (to.meta.includes('employee') && role === 'employee') {
            next()
        } else if (role && role !== 'undefined') {
            switch (role) {
                case 'admin':
                    return next('/invoices/create')
                    break;
                case 'accountant':
                    return next('/invoices/create')
                    break;
                case 'employee':
                    return next('/items')
                    break;
                default:
                    return next('/invoices/create')
                    break;
            }
        }
    }

    return next()
})

export default router
