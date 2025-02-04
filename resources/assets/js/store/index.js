import Vue from 'vue'
import Vuex from 'vuex'

import * as getters from './getters'
import mutations from './mutations'
import actions from './actions'

import auth from './modules/auth'
import user from './modules/user'
import category from './modules/category'
import customer from './modules/customer'
import company from './modules/company'
import companyInfo from './modules/settings/company-info'
import estimate from './modules/estimate'
import expense from './modules/expense'
import invoice from './modules/invoice'
import userProfile from './modules/settings/user-profile'
import payment from './modules/payment'
import receipt from './modules/receipt'
import preferences from './modules/settings/preferences'
import item from './modules/item'
import bill from './modules/bill'
import modal from './modules/modal'
import currency from './modules/currency'
import general from './modules/settings/general'
import profitLossReport from './modules/reports/profit-loss'
import salesReport from './modules/reports/sales'
import ExpensesReport from './modules/reports/expense'
import notes from './modules/notes'
import inventory from './modules/inventory'
import master from './modules/master'
import ledger from './modules/ledger'
import voucher from './modules/voucher'
import group from './modules/group'
import CustomersReport from './modules/reports/customers'
import states from './modules/states'
import banks from './modules/banks'
import dispatch from './modules/disptach'
import orders from './modules/orders'
import credits from './modules/credits'

Vue.use(Vuex)

const initialState = {
    isAppLoaded: false
}

export default new Vuex.Store({
    strict: false,
    state: initialState,
    getters,
    mutations,
    actions,

    modules: {
        auth,
        user,
        category,
        company,
        companyInfo,
        customer,
        estimate,
        item,
        bill,
        invoice,
        expense,
        modal,
        userProfile,
        currency,
        payment,
        receipt,
        preferences,
        general,
        profitLossReport,
        salesReport,
        ExpensesReport,
        notes,
        inventory,
        master,
        ledger,
        voucher,
        group,
        CustomersReport,
        states,
        banks,
        dispatch,
        estimate,
        orders,
        credits,
    }
})
