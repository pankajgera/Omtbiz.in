<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">
        {{ ledgerData ? ledgerData.account : '' }}
      </h3>
      <h4 style="float: right">
         Balance: ₹ {{ ledgerData ? ledgerData.balance : null}} {{ ledgerData ? ledgerData.type === 'D' ? 'Dr' : 'Cr' : null  }}
      </h4>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/">{{
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
               <table-column
                  :label="$t('ledgers.date')"
                  show="date"
                >
                  <template slot-scope="row">
                    {{ getFormattedDate(row.date) }}
                  </template>
                </table-column>
                <table-column
                  :label="$t('ledgers.credit')"
                  show="credit"
                >
                  <template slot-scope="row">
                    ₹ {{ row.credit }}
                  </template>
                </table-column>
                <table-column
                  :label="$t('ledgers.debit')"
                  show="debit"
                >
                  <template slot-scope="row">
                    ₹ {{ row.debit }}
                  </template>
                </table-column>
              </table-component>
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
</style>
<style>
.table.display-ledger {
  background: #f4f4ff;
  margin:30px 0px;
  padding:30px;
  top: 15px;
}
</style>
<script>
import { validationMixin } from "vuelidate";
import { mapActions, mapGetters } from "vuex";
const {
  required,
  minLength,
  numeric,
  minValue,
  maxLength,
} = require("vuelidate/lib/validators");
// Vue editable grid component and styles
import VueEditableGrid from "../../components/grid-table/VueEditableGrid";
import "../../components/grid-table/VueEditableGrid.css";
import moment from 'moment'

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
      rows: [
        {
          date: "",
          type: "",
          credit: "",
          debit: "",
        },
      ],
      columnDefs: [
        {
          sortable: true,
          filter: false,
          field: "date",
          headerName: "Date Time",
          type: "datetime",
          editable: false,
        },
        {
          sortable: true,
          filter: false,
          field: "type",
          headerName: "Type",
          editable: false,
        },
        {
          sortable: true,
          filter: false,
          field: "credit",
          headerName: "Credit",
          type: "number",
          editable: false,
        },
        {
          sortable: true,
          filter: false,
          field: "debit",
          headerName: "Debit",
          type: "number",
          editable: false,
        },
      ],
      displayArray: [],
      ledgerData: ''
    };
  },
  created() {
    this.loadEditData();
  },
  methods: {
    ...mapActions("ledger", ["fetchLedgerDisplay"]),
    async loadEditData() {
      let response = await this.fetchLedgerDisplay(this.$route.params.id);
      this.displayArray = response.data.vouchers;
      this.ledgerData = response.data.ledger;
    },
    getFormattedDate(date) {
      return moment(date).format('LLLL');
    }
  },
};
</script>
