<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('ledgers.edit_ledger') : $t('ledgers.new_ledger') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/ledgers">{{ $tc('ledgers.ledgers_list',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('ledgers.edit_ledger') : $t('ledgers.new_ledger') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <form action="" @submit.prevent="submitLedger">
            <div class="card-body">
              <!---- Grid table start -->
              <vue-editable-grid
                class="my-grid-class"
                ref="grid"
                id="mygrid"
                :column-defs="columnDefs"
                :row-data="rows"
                row-data-key='shipmentId'
                @cell-updated="cellUpdated"
                @row-selected="rowSelected"
                @link-clicked="linkClicked"
              >
                <template v-slot:header>
                  Add / Edit Account Ledgers
                </template>
                <template v-slot:header-r>
                  Total rows: {{ rows.length }}
                </template>
              </vue-editable-grid>
              <!--- Grid table end -->
              <button @click="addNewRow()">Add new</button>
            </div>
          </form>
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

export default {
  mixins: {
    validationMixin
  },
  data () {
    return {
      isLoading: false,
      title: 'Add Account Ledger',
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
          date: '',
          type: '',
          account: '',
          credit: '',
          debit: '',
          short_narration: ''
        }
      ],
      columnDefs: [
        { sortable: true, filter: true, field: 'date', headerName: 'Date (DD/MM/YYYY)', type: 'date', format: 'DD/MM/YYYY', editable: true },
        { sortable: true, filter: true, field: 'type', headerName: 'Type', editable: true },
        { sortable: true, filter: true, field: 'account', headerName: 'Account', editable: true },
        { sortable: true, filter: true, field: 'credit', headerName: 'Credit', type: 'number', editable: true },
        { sortable: true, filter: true, field: 'debit', headerName: 'Debit', type: 'number', editable: true },
        { sortable: true, filter: true, field: 'short_narration', headerName: 'Short Narration', editable: true }
      ]
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'ledgers.edit') {
        return true
      }
      return false
    }
  },
  created () {
    if (this.isEdit) {
      this.loadEditData()
    }
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
    ...mapActions('ledger', [
      'addLedger',
      'fetchLedger',
      'updateLedger'
    ]),
    async loadEditData () {
      let response = await this.fetchLedger(this.$route.params.id)
      this.formData = response.data.ledger
    },
    async submitLedger () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateLedger(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('ledgers.updated_message'))
          this.$router.push('/ledgers')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addLedger(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('ledgers.created_message'))
          this.$router.push('/ledgers')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
    cellUpdated() {

    },
    rowSelected() {

    },
    linkClicked() {

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
