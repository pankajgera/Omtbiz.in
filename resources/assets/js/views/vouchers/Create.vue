<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('vouchers.edit_voucher') : $t('vouchers.new_voucher') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
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
                @link-clicked="linkClicked"
                @contextmenu="contextMenu"
                @add-new-row="addNewRow"
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
                  rows="2"
                  width="400"
                  class="form-control description-input m-3"
                  v-model="short_narration"
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
          account_id: '',
          credit: 0,
          debit: 0,
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
      //resetActiveColIndex: false,
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
      this.rows = response.data.voucher
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
      let credit_sum = this.rows.map(o => o.credit).reduce((a,c) => a + parseInt(c));
      let debit_sum = this.rows.map(o => o.debit).reduce((a,c) => a + parseInt(c));

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
      this.isLoading = true
      let response = await this.addVoucher(this.rows)

      if (response.data) {
        window.toastr['success'](this.$tc('vouchers.created_message'))
        this.isLoading = false
        this.alreadySubmitted = true;
        return true
      }
      window.toastr['success'](response.data.success)
    },
    cellUpdated($event) {
      console.log($event)
      if ($event.columnIndex === 0) {
        // if ($event.value === 'Cr' || $event.value === 'C' || $event.value === 'c') {
        //   $event.row.type = 'C'
        //   $event.value = 'C'
        //   this.addNewRow('D')
        //   if ($event.$event.key === 'Enter') {
        //     this.addNewRow('C');
        //     //this.resetActiveColIndex = true;
        //   }
        // } else if ($event.value === 'Dr' || $event.value === 'D' || $event.value === 'd') {
        //   $event.row.type = 'D'
        //   $event.value = 'D'
        //   this.addNewRow('C')
        //   if ($event.$event.key === 'Enter') {
        //     this.addNewRow('D');
        //     //this.resetActiveColIndex = true;
        //   }
        // }
      }

      if($event.row.type === 'C') {
        $event.row.debit = 0;
      } else {
        $event.row.credit = 0;
      }

      if ($event.columnIndex === 2 && $event.$event.key === 'Enter' || $event.columnIndex === 3 && $event.$event.key === 'Enter') {
        $event.rowIndex = $event.rowIndex + 1
        $event.columnIndex = 0
        $event.$event.target.blur()
      }
    },
    rowSelected($event) {
      //console.log($event)
      // if (this.resetActiveColIndex) {
      //   $event.colIndex = 0;
      // }

      if($event.rowData.type === 'C') {
        $event.rowData.debit = 0;
      } else {
        $event.rowData.credit = 0;
      }

      //Type of Voucher Column
      if ($event.colIndex === 0) {
        // if ($event.rowData.type !== 'Dr' || $event.rowData.type !== 'D' || $event.rowData.type !== 'd') {
        //   $event.rowData.type = 'C';
        // } else {
        //   $event.rowData.type = 'D';
        // }
      }

      //Account Column
      if ($event.colIndex === 1) {

      }

      // $event.colData.editable = true;
      // //Credit Column
      // if ($event.colIndex === 2 && $event.rowData.type === 'D') {
      //   $event.colData.editable = false;
      // }

      // //Debit Column
      // if ($event.colIndex === 3 && $event.rowData.type === 'C') {
      //   $event.colData.editable = false;
      // }

    },
    linkClicked($event) {
    },
    contextMenu($event) {
    },
    addNewRow(val) {
      this.rows.push({
          type: val,
          account: '',
          account_id: '',
          credit: 0,
          debit: 0,
          total_debit: 0,
          total_credit: 0,
          balance: 0,
        });
    }
  }
}
</script>
