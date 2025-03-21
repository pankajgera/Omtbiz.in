<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('vouchers.edit_voucher') : $t('vouchers.new_voucher') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/vouchers">{{ $tc('vouchers.vouchers_list',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('vouchers.edit_voucher') : $t('vouchers.new_voucher') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
              <!---- Grid table start -->
              <vue-editable-grid
                class="my-grid-class"
                ref="grid"
                id="mygrid"
                :column-defs="columnDefs"
                :row-data="rows"
                row-data-key='voucherId'
                :master-options="masterDataBind"
                @cell-updated="cellUpdated"
                @row-selected="rowSelected"
              >
                <template v-slot:header>
                  Add / Edit Account Vouchers
                </template>
                <template v-slot:header-r>
                  Total rows: {{ rows.length }}
                </template>
              </vue-editable-grid>
              <!--- Grid table end -->
              <div class="col-md-12 p-0 mb-4 mt-4">
                <div class="row">
                  <div class="col-md-6">
                    <label>{{ $t('vouchers.description') }}</label>
                    <textarea
                      type="text"
                      autofocus
                      rows="2"
                      width="400"
                      class="form-control description-input mb-3"
                      v-model="short_narration"
                      id="narration-voucher"
                      placeholder="Type Short Narration (optional)" />
                  </div>
                  <div class="col-md-6">
                    <label>{{ $t('vouchers.date') }}</label><span class="text-danger"> * </span>
                    <base-date-picker
                      v-model="date"
                      :calendar-button="true"
                      calendar-button-icon="calendar"
                    />
                  </div>
              </div>
              </div>
              <button @click="addNewRow()" class="btn btn-theme-outline">Add new</button>
              <button @click="validateSubmitVoucher()" class="btn btn-primary">Save Voucher</button>
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
import { validationMixin } from 'vuelidate'
import { mapActions, mapGetters } from 'vuex'
const { required, minLength, numeric, minValue, maxLength } = require('vuelidate/lib/validators')

// Vue editable grid component and styles
import VueEditableGrid from '../../components/grid-table/VueEditableGrid'
import '../../components/grid-table/VueEditableGrid.css'

export default {
  mixins: {
    validationMixin
  },
  components: {
    VueEditableGrid
  },
  data () {
    return {
      isLoading: false,
      title: 'Add Account Voucher',
      rows: [
        {
          type: '',
          account: '',
          account_id: 0,
          credit: null,
          debit: null,
          total_debit: 0,
          total_credit: 0,
          balance: 0,
        }
      ],
      columnDefs: [
        { sortable: true, filter: false, field: 'type', headerName: 'Type', type: 'select', placeholder: 'C / D', size: '100px', editable: true },
        { sortable: true, filter: false, field: 'account', headerName: 'Account', type: 'select', size: '500px', editable: true },
        { sortable: true, filter: false, field: 'debit', headerName: 'Debit', type: 'numeric', size: '200px', editable: true },
        { sortable: true, filter: false, field: 'credit', headerName: 'Credit', type: 'numeric', size: '200px', editable: true },
      ],
      masterData: [],
      short_narration: '',
      date: new Date(),
      alreadySubmitted: false,
    }
  },
  computed: {
    isEdit() {
      if (this.$route.name === 'vouchers.edit') {
          return true
        }
        return false
    },
    masterDataBind() {
      var row = this.rows
      return this.masterData.filter((i, k) => {
        return row.map(j => {
          if (i.name === j.account) {
            this.masterData.splice(k, 1)
          }
        })
      })
    },
  },
  created () {
    if (this.isEdit) {
      this.loadEditData()
    }
    this.loadMasters();
  },
  methods: {
    ...mapActions('voucher', [
      'addVoucher',
      'fetchVoucher',
    ]),
    ...mapActions('master', [
      'fetchMasters'
    ]),
    async loadEditData () {
      let response = await this.fetchVoucher(this.$route.params.id)
      this.rows = []
      response.data.voucher.map(each => {
        let obj = {}
        obj['id'] = each.id
        obj['account_ledger_id'] = each.account_ledger_id
        obj['type'] = each.type
        obj['account'] = each.account,
        obj['account_id'] = each.account_master_id,
        obj['credit'] = parseInt(each.credit),
        obj['debit'] = parseInt(each.debit),
        obj['total_debit'] = 0,
        obj['total_credit'] = 0,
        obj['balance'] = 0,
        this.rows.push(obj)
        this.short_narration = each.short_narration
        this.date = each.date
      })
    },
    async loadMasters () {
      let response = await this.fetchMasters({limit: false})
      this.masterData = response.data.masters
    },
    validateSubmitVoucher() {
      if (this.alreadySubmitted) {
        swal({
          title: this.$t('general.are_you_sure'),
          text: this.$tc('vouchers.duplicate_vouchers', 2),
          icon: '/assets/icon/trash-solid.svg',
          buttons: true,
          dangerMode: false
        }).then(async (duplicate) => {
          if (duplicate) {
            this.submitVoucher()
          }
        })
      } else {
        this.submitVoucher()
      }
    },
    async submitVoucher () {
      let rows = this.rows
      rows = rows.filter(each => each['account'] !== '');
      let credit_sum = rows.map(o => o.credit).reduce((a,c) => a + c);
      let debit_sum = rows.map(o => o.debit).reduce((a,c) => a + c);

      let calc_balance = 0;
      if (credit_sum !== debit_sum || !credit_sum || !debit_sum) {
          swal({
            title: this.$t('vouchers.balace_not_equal_title'),
            text: this.$t('vouchers.balace_not_equal_desc'),
            icon: 'error',
            buttons: false,
            dangerMode: true
          })
          return false
      }
      if (!this.date) {
        swal({
            title: 'Field required',
            text: 'Voucher date required',
            icon: 'error',
            buttons: false,
            dangerMode: true
          })
          return false
      }

      rows.map(each => {
        each['total_debit'] = debit_sum
        each['total_credit'] = credit_sum
        each['balance'] = calc_balance
        each['short_narration'] = this.short_narration
        each['date'] = this.date
        each['is_edit'] = this.isEdit
      });

      try {
        this.isLoading = true
        let response = await this.addVoucher(rows)
        if (response.data) {
          window.toastr['success'](this.$tc('vouchers.created_message'))
          this.isLoading = false
          this.alreadySubmitted = true;
          setTimeout(() => {
            window.location.reload()
          }, 2000)
          return true
        }
      } catch (err) {
        if (err) {
          this.isLoading = false
          window.toastr['error'](err)
        }
      }
    },
    cellUpdated($event) {
      let rows = this.rows
      if ($event.columnIndex === 2 && $event.$event.key === 'Enter' || $event.columnIndex === 3 && $event.$event.key === 'Enter')
      {
        this.$nextTick(() => {
          let credit_sum = rows.map(o => o.credit).reduce((a,c) => a + c)
          let debit_sum = rows.map(o => o.debit).reduce((a,c) => a + c)
          let calc_total = credit_sum > debit_sum ? credit_sum - debit_sum : debit_sum - credit_sum
          let calc_type = credit_sum > debit_sum ? 'Dr' : 'Cr'
          if (calc_total !== 0 && calc_type === 'Dr' && $event.value > 0) {
            this.addNewRow('Dr', calc_total)
            $event.rowIndex = $event.rowIndex + 1
            $event.columnIndex = 0
            $event.$event.target.blur()
          } else if (calc_total !== 0 && calc_type === 'Cr' && $event.value > 0) {
            this.addNewRow('Cr', calc_total)
            $event.rowIndex = $event.rowIndex + 1
            $event.columnIndex = 0
            $event.$event.target.blur()
          } else if (calc_total === 0) {
            $event.$event.target.blur()
            $event.$event.path[10].childNodes[2].lastChild.focus()
          }
        });
      }

      if ((rows.length - 1) === $event.rowIndex) {
        if ($event.columnIndex === 2 && $event.$event.key === 'Tab' || $event.columnIndex === 3 && $event.$event.key === 'Tab')
        {
          $event.$event.target.blur()
          $event.$event.path[10].childNodes[2].lastChild.focus()
        }
      }
    },
    rowSelected($event) {
      if($event.rowData && $event.rowData.type === 'Cr') {
        $event.rowData.debit = null;
      } else if ($event.rowData && $event.rowData.type === 'Dr') {
        $event.rowData.credit = null;
      }
      //Type of Voucher Column
      if ($event.colIndex === 0) {

      }
      //Account Column
      if ($event.colIndex === 1) {

      }
    },
    addNewRow(typ = null, sum = null) {
      let deb = typ == 'Dr' ? sum : null
      let cred = typ == 'Cr' ? sum : null
      this.rows.push({
          type: typ,
          account: '',
          account_id: 0,
          credit: cred,
          debit: deb,
          total_debit: 0,
          total_credit: 0,
          balance: 0,
        });
    }
  }
}
</script>
