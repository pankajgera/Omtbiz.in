<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">
        {{ ledgerData ? ledgerData.account : '' }}
      </h3>
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
            <!---- Grid table start -->
            <vue-editable-grid
              class="my-grid-class"
              ref="grid"
              id="displayLedger"
              :column-defs="columnDefs"
              :row-data="displayArray"
              :master-options="[]"
              row-data-key="ledgerDisplayId"
            >
              <template v-slot:header-r>
                Total rows: {{ rows.length }}
              </template>
            </vue-editable-grid>
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
          balance: "",
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
        {
          sortable: true,
          filter: false,
          field: "balance",
          headerName: "Balance",
          editable: false,
        },
      ],
      displayArray: [],
      ledgerData: '',
    };
  },
  created() {
    this.loadEditData();
  },
  methods: {
    ...mapActions("ledger", ["fetchLedgerDisplay"]),
    async loadEditData() {
      let response = await this.fetchLedgerDisplay(this.$route.params.id);
      this.ledgerData = response.data.ledger;
      this.displayArray.push(this.ledgerData);
    },
  },
};
</script>
