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
              >
                <template v-slot:header>
                  Add / Edit Account Vouchers
                </template>
                <template v-slot:header-r>
                  Total rows: {{ rows.length }}
                </template>
              </vue-editable-grid>
              <!--- Grid table end -->
              <button @click="addNewRow()" class="btn btn-theme-outline">Add new</button>
              <button @click="submitVoucher()" class="btn btn-success">Save Voucher</button>
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
      // formData: {
      //   date: '',
      //   type: '',
      //   account: '',
      //   credit: '',
      //   debit: '',
      //   short_narration: ''
      // },
      rows: [
        {
          type: '',
          account: '',
          credit: '',
          debit: '',
          short_narration: '',
          total_debit: '',
          total_credit: '',
          balance: '',
        }
      ],
      columnDefs: [
        { sortable: true, filter: true, field: 'type', headerName: 'Type', type: 'text', placeholder: 'C / D', size: '100px', editable: true },
        { sortable: true, filter: true, field: 'account', headerName: 'Account', type: 'select', size: '500px', editable: true },
        { sortable: true, filter: true, field: 'credit', headerName: 'Credit', type: 'numeric', size: '150px', editable: true },
        { sortable: true, filter: true, field: 'debit', headerName: 'Debit', type: 'numeric', size: '150px', editable: true },
        { sortable: true, filter: true, field: 'short_narration', headerName: 'Short Narration', type: 'text', size: '150px', editable: true }
      ],
      resetActiveColIndex: false,
      masterData: [],
    }
  },
  computed: {
    isEdit () {
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
  // validations: {
  //   rows: {
  //     date: {
  //       required,
  //       type: Date
  //     },
  //     type: {
  //       required
  //     },
  //     account: {
  //       required
  //     }
  //   }
  // },
  methods: {
    ...mapActions('voucher', [
      'addVoucher',
      'fetchVoucher',
      'updateVoucher'
    ]),
    ...mapActions('master', [
      'fetchMasters'
    ]),
    async loadEditData () {
      let response = await this.fetchVoucher(this.$route.params.id)
      this.formData = response.data.voucher
    },
    async loadMasters () {
      let response = await this.fetchMasters({limit: 500})
      this.masterData = response.data.masters.data
    },
    async submitVoucher () {
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
      });
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateVoucher(this.rows)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('vouchers.updated_message'))
          this.$router.push('/vouchers')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addVoucher(this.rows)

        if (response.data) {
          window.toastr['success'](this.$tc('vouchers.created_message'))
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
    cellUpdated($event) {
      console.log('cellUpdated', $event)
      if ($event.$event.key === 'Tab' && $event.columnIndex === 4) {
        this.addNewRow();
        this.resetActiveColIndex = true;
      }

      if ($event.columnIndex === 0) {
        if ($event.value === 'Dr' || $event.value === 'D' || $event.value === 'd') {
          $event.row.type = 'D';
          $event.value = 'D';
        } else {
          $event.row.type = 'C';
          $event.value = 'C';
        }
      }

      if ($event.columnIndex === 4) {

      }
    },
    rowSelected($event) {
      console.log('rowSelected', $event)
      // if (this.resetActiveColIndex) {
      //   $event.colIndex = 0;
      // }
      //Type of Voucher Column
      if ($event.colIndex === 0) {
        if ($event.rowData.type === 'Dr' || $event.rowData.type === 'D' || $event.rowData.type === 'd') {
          $event.rowData.type = 'D';
        } else {
          $event.rowData.type = 'C';
        }
      }

      //Account Column
      if ($event.colIndex === 1) {

      }

      $event.colData.editable = true;
      //Credit Column
      if ($event.colIndex === 2 && $event.rowData.type === 'D') {
        console.log('cant edit credit')
        $event.colData.editable = false;
      }

      //Debit Column
      if ($event.colIndex === 3 && $event.rowData.type === 'C') {
        console.log('cant edit debit')
        $event.colData.editable = false;
      }

      if ($event.colIndex === 4) {
        // console.log('event', $event)
        // this.addNewRow();
        // this.resetActiveColIndex = true;
      }

    },
    linkClicked($event) {
      console.log('linkClicked', $event)
    },
    contextMenu($event) {
      console.log('contextMenu', $event)
    },
    addNewRow() {
      this.rows.push({
          date: '',
          type: '',
          account: '',
          credit: '',
          debit: '',
          short_narration: ''
        });
    }
  }
}
</script>
