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
              <!-- <div class="form-group">
                <label for="date">{{ $t('ledgers.date') }}</label><span class="text-danger"> *</span>
                <base-date-picker
                  v-model="formData.date"
                  :invalid="$v.formData.date.$error"
                  :calendar-button="true"
                  calendar-button-icon="calendar"
                  @change="$v.formData.date.$touch()"
                />
                <div v-if="$v.formData.date.$error">
                  <span v-if="!$v.formData.date" class="text-danger">{{ $t('validation.required') }}</span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('ledgers.type') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.type"
                  :invalid="$v.formData.type.$error"
                  focus
                  type="text"
                  name="type"
                  @input="$v.formData.type.$touch()"
                />
                <div v-if="$v.formData.type.$error">
                  <span v-if="!$v.formData.type.required" class="text-danger">{{ $t('validation.required') }} </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('ledgers.account') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.account"
                  :invalid="$v.formData.account.$error"
                  focus
                  type="text"
                  name="account"
                  @input="$v.formData.account.$touch()"
                />
                <div v-if="$v.formData.account.$error">
                  <span v-if="!$v.formData.account.required" class="text-danger">{{ $t('validation.required') }} </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('ledgers.debit') }}</label>
                <base-input
                  v-model.trim="formData.debit"
                  focus
                  type="number"
                  name="debit"
                  @input="$v.formData.debit.$touch()"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('ledgers.credit') }}</label>
                <base-input
                  v-model.trim="formData.credit"
                  focus
                  type="number"
                  name="credit"
                  @input="$v.formData.credit.$touch()"
                />
              </div>
              <div class="form-group">
                <label for="short_narration">{{ $t('ledgers.short_narration') }}</label>
                <base-text-area
                  v-model="formData.short_narration"
                  rows="2"
                  name="short_narration"
                  @input="$v.formData.short_narration.$touch()"
                />
              </div>
              <div class="form-group">
                <base-button
                  :loading="isLoading"
                  :disabled="isLoading"
                  icon="save"
                  color="theme"
                  type="submit"
                  class="collapse-button"
                >
                  {{ isEdit ? $t('ledgers.update_ledger') : $t('ledgers.save_ledger') }}
                </base-button>
              </div> -->


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
        { sortable: true, filter: true, field: 'short_narration', headerName: 'Short Narration', editable: false }
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
      console.log('add new row');
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
