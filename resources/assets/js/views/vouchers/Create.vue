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
      <div class="col-sm-12">
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
                :master-options="masterData"
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
              <div class="col-sm-12">
                <textarea
                  type="text"
                  v-autoresize
                  autofocus
                  rows="2"
                  width="400"
                  class="form-control description-input m-3"
                  v-model="short_narration"
                  id="narration-voucher"
                  placeholder="Type Short Narration (optional)" />
              </div>
              <button @click="addNewRow()" class="btn btn-theme-outline">Add new</button>
              <button @click="validateSubmitVoucher()" class="btn btn-success">Save Voucher</button>
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
      alreadySubmitted: false,
    }
  },
  computed: {
    isEdit() {
      if (this.$route.name === 'vouchers.edit') {
          return true
        }
        return false
    }
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
        obj['type'] = each.type
        obj['account'] = each.account,
        obj['account_id'] = each.account_master_id,
        obj['credit'] = parseInt(each.credit),
        obj['debit'] = parseInt(each.debit),
        obj['total_debit'] = 0,
        obj['total_credit'] = 0,
        obj['balance'] = 0,
        this.rows.push(obj)
      })
    },
    async loadMasters () {
      let response = await this.fetchMasters({limit: 500})
      this.masterData = response.data.masters.data
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
      this.rows = this.rows.filter(each => each['account'] !== '');
      let credit_sum = this.rows.map(o => o.credit).reduce((a,c) => a + c);
      let debit_sum = this.rows.map(o => o.debit).reduce((a,c) => a + c);

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
      this.rows.map(each => {
        each['total_debit'] = debit_sum
        each['total_credit'] = credit_sum
        each['balance'] = calc_balance
        each['short_narration'] = this.short_narration
      });

      try {
        this.isLoading = true
        let response = await this.addVoucher(this.rows)

        if (response.data) {
          window.toastr['success'](this.$tc('vouchers.created_message'))
          this.isLoading = false
          this.alreadySubmitted = true;
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
      if ($event.columnIndex === 0) {

      }

      // if($event.row.type === 'Cr') {
      //   $event.row.debit = 0;
      // } else if ($event.row.type === 'Dr') {
      //   $event.row.credit = 0;
      // }
      if ($event.columnIndex === 2 && $event.$event.key === 'Enter' || $event.columnIndex === 3 && $event.$event.key === 'Enter')
      {
        let typeValue = '';
        let credit_sum = this.rows.map(o => {return o.credit; }).reduce((a,c) => a + c)
        let debit_sum = this.rows.map(o => { return o.debit; }).reduce((a,c) => a + c)
        if (0 < credit_sum && credit_sum > debit_sum || $event.columnIndex === 3 && credit_sum + $event.value > debit_sum) {
          this.addNewRow('Dr', $event.value)
        } else if (0 < debit_sum && credit_sum < debit_sum || $event.columnIndex === 2 && debit_sum + $event.value > credit_sum) {
          this.addNewRow('Cr', $event.value)
        }

        $event.rowIndex = $event.rowIndex + 1
        $event.columnIndex = 0
        $event.$event.target.blur()
      }

      if ((this.rows.length - 1) === $event.rowIndex) {
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

      // if ($event.colIndex === 2 && $event.rowData.type === 'Cr') {

      // }
      // if ($event.colIndex === 3) {

      // }

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
