<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">
        {{  $t("daybook.title-2") }}
      </h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/ledgers">{{
            $t("general.home")
          }}</router-link>
        </li>
      </ol>
    </div>
    <div class="row" >
      <div class="col-sm-12">
        <table-component
              ref="table"
              :data="ledgerData"
              :show-filter="false"
            table-class="table"
            >
              <table-column
                :label="$tc('daysheet.lot')"
                show="lot"
              >
                <template slot-scope="row">
                  {{ row.lot }}
                </template>
              </table-column>
              <table-column :label="$tc('daysheet.reference-number')" show="reference_number">
                <template slot-scope="row">
                  {{ row.reference_number }}
                </template>
              </table-column>
              <table-column
                :label="$tc('daysheet.party-name')"
                show="party"
              >
                <template slot-scope="row">
                   {{ row.party }}
                </template>
              </table-column>
            </table-component>
      </div>
    </div>
  </div>
</template>
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
      ledgerData: [],
    };
  },
  created() {
    this.loadEditData();
  },
  methods: {
    ...mapActions("ledger", ["fetchLedgerDaysheet"]),
    async loadEditData() {
      let response = await this.fetchLedgerDaysheet(this.$route.params.id);
       this.isLoading = false
      this.ledgerData = response.data.ledger;
    },
  },
};
</script>
