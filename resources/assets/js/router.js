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
import Dashboard from './views/dashboard/Dashboard.vue'

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
import PaymentsIndex from './views/payments/Index.vue'
import PaymentCreate from './views/payments/Create.vue'

// Estimates
import EstimateIndex from './views/estimates/Index.vue'
import EstimateCreate from './views/estimates/Create.vue'
import EstimateView from './views/estimates/View.vue'

// Expenses
import ExpensesIndex from './views/expenses/Index'
import ExpenseCreate from './views/expenses/Create.vue'

// Report
import SalesReports from './views/reports/SalesReports'
import ExpensesReport from './views/reports/ExpensesReport'
import ProfitLossReport from './views/reports/ProfitLossReport'
import TaxReport from './views/reports/TaxReport.vue'
import ReportLayout from './views/reports/layout/Index.vue'

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
import AddUser from './views/settings/AddUser.vue'
//import UpdateApp from './views/settings/UpdateApp.vue'

import Wizard from './views/wizard/Index.vue'

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
            {
                path: '/',
                component: Dashboard,
                name: 'dashboard',
                meta: ['admin']
            },
            {
                path: 'dashboard',
                component: Dashboard,
                meta: ['admin']
            },

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
                    }
                ]
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
            {
                path: 'add-user',
                name: 'add-user',
                component: AddUser,
                meta: ['admin']
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
                return next('/dashboard')
                break;
            case 'accountant':
                return next('/invoices')
                break;
            case 'employee':
                return next('/items')
                break;
            default:
                return next('/dashboard')
        }
    }

    if (to.meta.length) {
        if (to.meta.includes('admin') && role === 'admin') {
            console.log('admin')
            next()
        } else if (to.meta.includes('accountant') && role === 'accountant') {
            console.log('accountant')
            next()
        } else if (to.meta.includes('employee') && role === 'employee') {
            console.log('employee')
            next()
        } else {
            switch (role) {
                case 'admin':
                    return next('/dashboard')
                    break;
                case 'accountant':
                    return next('/invoices')
                    break;
                case 'employee':
                    return next('/items')
                    break;
                default:
                    return next('/dashboard')
            }
        }
    }

    return next()
})

export default router