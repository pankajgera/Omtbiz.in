<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">
        {{ ledgerData ? ledgerData.account : "" }}
      </h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/invoices">{{$t("general.home")}}</router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link slot="item-title" to="/ledgers">{{$tc("ledgers.ledgers_list", 2)}}</router-link>
        </li>
      </ol>
    </div>
    <div class="row" style="margin-bottom: 80px">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
            <div class="reports-tab-container">
              <div class="row">
                <div class="col-md-4 report-field-container">
                  <label class="report-label">{{ $t('reports.customers.date_range') }}</label>
                  <base-select
                    v-model="selectedRange"
                    :options="dateRange"
                    :allow-empty="false"
                    :show-labels="false"
                    @input="onChangeDateRange"
                  />
                  <span v-if="$v.range.$error && !$v.range.required" class="text-danger"> {{ $t('validation.required') }} </span>
                </div>
                <div class="col-md-4 report-field-container">
                  <label class="report-label">{{ $t('reports.customers.from_date') }}</label>
                  <base-date-picker
                    v-model="formData.from_date"
                    :invalid="$v.formData.from_date.$error"
                    :calendar-button="true"
                    calendar-button-icon="calendar"
                    @change="$v.formData.from_date.$touch()"
                    @input="loadEditData()"
                  />
                  <span v-if="$v.formData.from_date.$error && !$v.formData.from_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
                </div>
                <div class="col-md-4 report-field-container">
                  <label class="report-label">{{ $t('reports.customers.to_date') }}</label>
                  <base-date-picker
                    v-model="formData.to_date"
                    :invalid="$v.formData.to_date.$error"
                    :calendar-button="true"
                    calendar-button-icon="calendar"
                    @change="$v.formData.to_date.$touch()"
                    @input="loadEditData()"
                  />
                  <span v-if="$v.formData.to_date.$error && !$v.formData.to_date.required" class="text-danger"> {{ $t('validation.required') }} </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <base-loader v-if="isLoading" class="table-loader" />
            <table-component
              ref="tableDisplay"
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
                :label="$t('ledgers.inventory_item_quantity')"
                show="quantity"
              >
                <template slot-scope="row">
                  {{ row.invoice ? row.invoice.inventories.map(k => parseInt(k.quantity)).reduce((a, b) => a + b) : 0 }}
                </template>
              </table-column>
              <!--- Debitor will be debit but for ledger display it will show credit amount -->
              <table-column :label="$t('ledgers.debit')" show="debit">
                <template slot-scope="row">
                  ₹ {{ row.credit ? numberWithCommas(row.credit) : "0.00" }}
                </template>
              </table-column>
              <!--- Creditor will be credit but for ledger display it will show debit amount -->
              <table-column :label="$t('ledgers.credit')" show="credit">
                <template slot-scope="row">
                  ₹ {{ row.debit ? numberWithCommas(row.debit) : "0.00" }}
                </template>
              </table-column>
              <table-column :label="$t('general.delete')">
                <template slot-scope="row">
                  <a v-if="'Voucher' === row.voucher_type" href="#" class="d-block text-center" @click="removeVoucher(row.id)">
                    <font-awesome-icon :icon="['fas', 'trash']" />
                  </a>
                </template>
              </table-column>
            </table-component>
            <p class="row footer-total">
              <span class="mr-30">Total Quantity:</span>
              <span class="ml-60">
                {{ inventorySum }}
              </span>
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12" style="background: #fff; position: fixed; bottom: 0; padding: 0; right: 0; height: 100px">
            <p class="row p-footer">
              <span class="col-sm-4">Opening Balance:</span>
              <span class="col-sm-4">
                ₹ {{ totalOpeningBalanceCr ? totalOpeningBalanceCr :  0.00  }} Dr
              </span>
              <span class="col-sm-4">
                ₹ {{ totalOpeningBalanceDr ? totalOpeningBalanceDr :  0.00  }} Cr
              </span>
            </p>
            <br/>
            <p class="row p-footer">
              <span class="col-sm-4">Current Total:</span>
              <span class="col-sm-4">
                ₹ {{ currentBalanceCr ? currentBalanceCr :  0.00  }} Dr
              </span>
              <span class="col-sm-4">
                ₹ {{ currentBalanceDr ? currentBalanceDr :  0.00  }} Cr
              </span>
            </p>
            <br/>
            <hr />
            <h6 class="row p-footer">
              <span class="col-sm-4">Closing Balance:</span>
              <span class="col-sm-4">
                ₹ {{ closingBalanceCr ? closingBalanceCr :  0.00  }} Dr
              </span>
              <span class="col-sm-4">
                ₹ {{ closingBalanceDr ? closingBalanceDr :  0.00  }} Cr
              </span>
            </h6>
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
  .p-footer {
    float: right; margin: 0 2% 0 0; width: 33.33%;
  }
  @media (min-width: 1500px) and (max-width: 2400px) {
    .footer-total {
      margin-left: 50%;
    }
  }
  @media (max-width: 1499px) {
    .footer-total {
      margin-left: 47%;
    }
  }
  @media (max-width: 992px) {
    .footer-total {
      margin-left: 47%;
    }
  }
  @media (max-width: 749px) {
    .footer-total {
      margin-left: 37%;
    }
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
import GlobalMixin from '../../helpers/mixins.js';
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
    validationMixin
  },
  mixins:[GlobalMixin],
  components: {
    VueEditableGrid,
  },
  validations: {
    range: {
      required
    },
    formData: {
      from_date: {
        required
      },
      to_date: {
        required
      }
    }
  },
  watch: {
    range (newRange) {
      this.formData.from_date = moment(newRange).startOf('year').toString()
      this.formData.to_date = moment(newRange).endOf('year').toString()
    }
  },
  data() {
    return {
      isLoading: false,
      title: "Display Account Ledger",
      displayArray: [],
      ledgerData: "",
      // masterData: "",
      currentTotalCredit: 0,
      currentTotalDebit: 0,
      // totalQuantity: 0,
      range: new Date(),
      dateRange: [
        'Today',
        'This Week',
        'This Month',
        'This Quarter',
        'This Year',
        'Previous Week',
        'Previous Month',
        'Previous Quarter',
        'Previous Year',
        'Custom'
      ],
      selectedRange: 'This Month',
      formData: {
        from_date: moment().startOf('month').toString(),
        to_date: moment().endOf('month').toString()
      },
      inventorySum: 0,
      totalOpeningBalanceDr: 0,
      totalOpeningBalanceCr: 0,
      currentBalanceCr: 0,
      currentBalanceDr: 0,
      closingBalanceCr: 0,
      closingBalanceDr: 0,
    };
  },
  created() {
    this.loadEditData();
  },
  methods: {
    ...mapActions('voucher', ["deleteVoucher"]),
    ...mapActions("ledger", ["fetchLedgerDisplay"]),
    async loadEditData() {
      this.isLoading=true;
      let data = {
          formData: {
            from_date: moment(this.formData.from_date).format('DD/MM/YYYY'),
            to_date: moment(this.formData.to_date).format('DD/MM/YYYY'),
          },
          id: this.$route.params.id
      };
      let response = await this.fetchLedgerDisplay({'id': data.id, 'params': data.formData});
      this.displayArray = response.data.vouchers;
      this.ledgerData = response.data.ledger;
      // this.masterData = response.data.account_master;
      this.inventorySum = response.data.inventory_sum;
      this.totalOpeningBalanceDr = response.data.total_opening_balance_dr;
      this.totalOpeningBalanceCr = response.data.total_opening_balance_cr;
      this.currentBalanceCr = response.data.current_balance_cr;
      this.currentBalanceDr = response.data.current_balance_dr;
      this.closingBalanceCr = response.data.closing_balance_cr;
      this.closingBalanceDr = response.data.closing_balance_dr;
      // this.currentTotalCredit = this.ledgerData.credit;
      // this.currentTotalDebit = this.ledgerData.debit;
      // let quan = this.displayArray.filter(i => i.invoice)
      //   .map(i => i.invoice.inventories.map(k => parseInt(k.quantity)).reduce((a, b) => a + b)).filter(i => i);
      // if (quan.length) {
      //   this.totalQuantity = quan.reduce((a, c) =>  a + c);
      // }
      this.isLoading=false;
    },
    getFormattedDate(date) {
      return moment(date).format("DD-MM-YYYY");
    },
    async removeVoucher (id) {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('vouchers.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteVoucher(id)
          if (res.data.success) {
            window.toastr['success'](this.$tc('vouchers.deleted_message', 1))
            // this.$refs.tableDisplay.refresh()
            window.location.reload()
            return true
          }

          if (res.data.error === 'voucher_attached') {
            window.toastr['error'](this.$tc('vouchers.voucher_attached_message'), this.$t('general.action_failed'))
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
    getThisDate (type, time) {
      return moment()[type](time).toString()
    },
    getPreDate (type, time) {
      return moment().subtract(1, time)[type](time).toString()
    },
    onChangeDateRange () {
      switch (this.selectedRange) {
        case 'Today':
          this.formData.from_date = moment().toString()
          this.formData.to_date = moment().toString()
          break

        case 'This Week':
          this.formData.from_date = this.getThisDate('startOf', 'isoWeek')
          this.formData.to_date = this.getThisDate('endOf', 'isoWeek')
          break

        case 'This Month':
          this.formData.from_date = this.getThisDate('startOf', 'month')
          this.formData.to_date = this.getThisDate('endOf', 'month')
          break

        case 'This Quarter':
          this.formData.from_date = this.getThisDate('startOf', 'quarter')
          this.formData.to_date = this.getThisDate('endOf', 'quarter')
          break

        case 'This Year':
          this.formData.from_date = this.getThisDate('startOf', 'year')
          this.formData.to_date = this.getThisDate('endOf', 'year')
          break

        case 'Previous Week':
          this.formData.from_date = this.getPreDate('startOf', 'isoWeek')
          this.formData.to_date = this.getPreDate('endOf', 'isoWeek')
          break

        case 'Previous Month':
          this.formData.from_date = this.getPreDate('startOf', 'month')
          this.formData.to_date = this.getPreDate('endOf', 'month')
          break

        case 'Previous Quarter':
          this.formData.from_date = this.getPreDate('startOf', 'quarter')
          this.formData.to_date = this.getPreDate('endOf', 'quarter')
          break

        case 'Previous Year':
          this.formData.from_date = this.getPreDate('startOf', 'year')
          this.formData.to_date = this.getPreDate('endOf', 'year')
          break

        default:
          break
      }
      this.loadEditData()
    },
    setRangeToCustom () {
      this.selectedRange = 'Custom'
    },
  },
};
</script>
