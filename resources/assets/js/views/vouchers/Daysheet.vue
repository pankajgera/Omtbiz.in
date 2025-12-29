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
      <div class="col-sm-12 mb-5">
      <base-button
            v-show="ledgerData"
            :outline="true"
            :icon="['fas', 'print']"
            color="theme"
            size="large"
            right-icon
            @click="printData"
          >
            Print
          </base-button>
      </div>
      <div class="col-sm-12 daysheet">
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

            <!-- print table here -->
              <div class="row" id="to_print_table">
              <div class="col-md-12 mb-2">
              {{ $tc('daysheet.date') }} {{  new Date().toJSON().slice(0, 10) }}
              </div>
              <div class="col-sm-12">
                <table-component
              id="to_print_table"
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
              <table-column
                :label="$tc('daysheet.sign')"
                show="lot"
              >
                <template slot-scope="row">

                </template>
              </table-column>
              <table-column :label="$tc('daysheet.pm')" show="reference_number">
                <template slot-scope="row">
                  {{ row.reference_number }}
                </template>
              </table-column>
              <table-column
                :label="$tc('daysheet.name')"
                show="party"
              >
                <template slot-scope="row">
                   {{ row.party }}
                </template>
              </table-column>
               <table-column
                :label="$tc('daysheet.city')"
                show="party"
              >
                <template slot-scope="row">

                </template>
              </table-column>
                <table-column
                :label="$tc('daysheet.transport')"
                show="party"
              >
                <template slot-scope="row">

                </template>
              </table-column>
            </table-component>
              </div>
              </div>

      </div>
    </div>
  </div>
</template>
<style>
#to_print_table {
  display:none;
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
        this.ledgerData = response.data.ledger.sort((a, b) => {
          return String(a.reference_number).localeCompare(String(b.reference_number));
        });
    },
    printData() {
      printJS({
        printable: 'to_print_table',
        type: 'html',
        ignoreElements: ['no-print-check', 'no-print-option'],
        scanStyles: true,
        targetStyles: ['*'],
        style: '.hide-print {display: none !important;}.table-component__table th, .table-component__table td {border:1px solid #000;padding: 0.75em 1.25em;vertical-align: top;text-align: left;}.table-component__table { min-width: 100%; border-collapse: collapse; table-layout: auto; margin-bottom: 0;border-spacing: 0 15px;} .table .table-component__table__body tr {border-radius: 10px;transition: all ease-in-out 0.2s;} .table .table-component__table__body tr:first-child td {border-top: 0;} .table  .table-component td > span:first-child {background: #EBF1FA;color: #55547A;display: none;font-size: 10px;font-weight: bold;padding: 5px;left: 0;position: absolute;text-transform: uppercase;top: 0;}'
      })
    }
  },
};
</script>
