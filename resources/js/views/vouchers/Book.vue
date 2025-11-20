<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">
        {{ ledgerData ? ledgerData.account : "" }}
      </h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/invoices">{{
            $t("general.home")
          }}</router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/ledgers">{{
            $tc("ledgers.ledgers_list", 2)
          }}</router-link>
        </li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
            <table-component
              ref="table"
              :data="displayArray"
              :show-filter="false"
              table-class="table display-ledger"
            >
              <table-column :label="$t('ledgers.date')" show="date">
                <template slot-scope="row">
                  {{ getFormattedDate(row.date) }}
                </template>
              </table-column>
              <table-column
                :label="$t('ledgers.particulars')"
                show="particulars"
              >
                <template slot-scope="row">
                  <router-link
                    :to="{ path: row.invoice_id ? `/invoices/${row.invoice_id}/edit` :
                      row.receipt_id ? `/receipts/${row.receipt_id}/edit` :
                      `/vouchers/${row.id}/edit`}"
                    class="dropdown-item"
                  >
                    {{ row.account }}
                  </router-link>
                </template>
              </table-column>
              <table-column
                :label="$t('ledgers.voucher_type')"
                show="voucher_type"
              >
                <template slot-scope="row">
                  {{ row.voucher_type }}
                </template>
              </table-column>
              <table-column :label="$t('ledgers.voucher_id')" show="id">
                <template slot-scope="row">
                  {{ row.id }}
                </template>
              </table-column>
              <table-column
                :label="$t('daybook.inventory_item_quantity')"
                show="quantity"
              >
                <template slot-scope="row">
                  {{ row.invoice ? row.invoice.inventories.map(k => parseInt(k.quantity)).reduce((a, b) => a + b) : 0 }}
                </template>
              </table-column>
              <table-column :label="$t('ledgers.debit')" show="debit">
                <template slot-scope="row">
                  ₹ {{ row.debit ? row.debit : "0.00" }}
                </template>
              </table-column>
              <table-column :label="$t('ledgers.credit')" show="credit">
                <template slot-scope="row">
                  ₹ {{ row.credit ? row.credit : "0.00" }}
                </template>
              </table-column>
            </table-component>
          </div>
        </div>
        <div class="row" style="float: right">
          <div class="col-sm-12" style="width: 500px">
            <p class="row">
              <span class="mr-30">Total Quantity:</span>
              <span class="ml-60">
                {{ totalQuantity }}
              </span>
            </p>
            <hr />
            <p class="row" v-if="masterData">
              <span>Opening Balance:</span>
              <span class="ml-60">
                {{
                  masterData.type === "Dr" && masterData.opening_balance
                    ? " ₹ " +
                      parseFloat(masterData.opening_balance).toFixed(2) +
                      " " +
                      masterData.type
                    : " ₹ 0.00"
                }}
              </span>
              <span class="ml-60">
                {{
                  masterData.type === "Cr" && masterData.opening_balance
                    ? " ₹ " +
                      parseFloat(masterData.opening_balance).toFixed(2) +
                      " " +
                      masterData.type
                    : " ₹ 0.00"
                }}
              </span>
            </p>
            <hr />
            <!-- <p class="row">
              <span class="mr-30">Current Total:</span>
              <span class="ml-60">
                {{
                  currentTotalDebit
                    ? " ₹ " + parseFloat(currentTotalDebit).toFixed(2) + " Dr"
                    : " ₹ 0.00"
                }}
              </span>
              <span class="ml-60">
                {{
                  currentTotalCredit
                    ? " ₹ " + parseFloat(currentTotalCredit).toFixed(2) + " Cr"
                    : " ₹ 0.00"
                }}
              </span>
            </p> -->
            <hr />
            <!-- <h6 class="row">
              <span class="mr-10">Closing Balance:</span>
              <span class="ml-60">
                {{
                  ledgerData.type === "Dr" && ledgerData
                    ? " ₹ " + parseFloat(ledgerData.balance).toFixed(2) + " Dr"
                    : " ₹ 0.00"
                }}
              </span>
              <span class="ml-60">
                {{
                  ledgerData.type === "Cr" && ledgerData
                    ? " ₹ " + parseFloat(ledgerData.balance).toFixed(2) + " Cr"
                    : " ₹ 0.00"
                }}
              </span>
            </h6> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.my-grid-class {
  height: 400px;
}
.ml-60 {
  margin-left: 60px;
}
.mr-30 {
  margin-right: 30px;
}
.mr-10 {
  margin-right: 10px;
}
</style>
<style>
.table.display-ledger {
  background: #f4f4ff;
  margin: 30px 0px;
  padding: 30px;
  top: 15px;
}
</style>
<script>
import { validationMixin } from "vuelidate";
import { mapActions, mapGetters } from "vuex";
import {
  required,
  minLength,
  numeric,
  minValue,
  maxLength,
} from 'vuelidate/lib/validators';
// Vue editable grid component and styles
import VueEditableGrid from "../../components/grid-table/VueEditableGrid";
import "../../components/grid-table/VueEditableGrid.css";
import moment from "moment";

export default {
  mixins: {
    validationMixin,
  },
  components: {
    VueEditableGrid,
  },
  data() {
    return {
      isLoading: false,
      title: "Display Account Ledger",
      displayArray: [],
      ledgerData: "",
      masterData: "",
      currentTotalCredit: 0,
      currentTotalDebit: 0,
      totalQuantity: 0,
    };
  },
  created() {
    this.loadEditData();
  },
  methods: {
    ...mapActions("voucher", ["fetchVoucherBook"]),
    async loadEditData() {
      let response = await this.fetchVoucherBook(this.$route.params.id);
      this.displayArray = response.data.vouchers;
      this.ledgerData = response.data.ledger;
      this.masterData = response.data.account_master;
      this.currentTotalCredit = this.ledgerData.credit;
      this.currentTotalDebit = this.ledgerData.debit;
      let quan = this.displayArray.filter(i => i.invoice)
        .map(i => i.invoice.inventories.map(k => parseInt(k.quantity)).reduce((a, b) => a + b)).filter(i => i);
      if (quan.length) {
        this.totalQuantity = quan.reduce((a, c) =>  a + c);
      }
    },
    getFormattedDate(date) {
      return moment(date).format("DD-MM-YYYY");
    },
  },
};
</script>
