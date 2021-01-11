<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('banks.edit_bank') : $t('banks.new_bank') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/banks">{{ $tc('banks.banks',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('banks.edit_bank') : $t('banks.new_bank') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-8">
        <div class="card">
          <form action="" @submit.prevent="submitBank">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('banks.name') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.name"
                  :invalid="$v.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  @input="$v.formData.name.$touch()"
                />
                <div v-if="$v.formData.name.$error">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger">
                    {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.design_no') }}</label>
                <base-input
                  v-model.trim="formData.design_no"
                  focus
                  type="text"
                  name="design_no"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.rate') }}</label>
                <base-input
                  v-model.trim="formData.rate"
                  focus
                  type="text"
                  name="rate"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.average') }}</label>
                <base-input
                  v-model.trim="formData.average"
                  focus
                  type="text"
                  name="average"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.per_price') }}</label>
                <base-input
                  v-model.trim="formData.per_price"
                  focus
                  type="text"
                  name="per_price"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.bank') }}</label>
                <base-text-area
                  v-model.trim="formData.bank"
                  focus
                  type="text"
                  name="bank"
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
                  {{ isEdit ? $t('banks.update_bank') : $t('banks.save_bank') }}
                </base-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

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
      title: 'Add Bank',
      formData: {
        name: '',
        design_no: '',
        rate: '',
        average: '',
        per_price: '',
        bank: ''
      },
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'banks.edit') {
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
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
    }
  },
  methods: {
    ...mapActions('banks', [
      'addBank',
      'fetchBank',
      'updateBank'
    ]),
    async loadEditData () {
      let response = await this.fetchBank(this.$route.params.id)
      this.formData = response.data.bank
    },
    async submitBank () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateBank(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('banks.updated_message'))
          this.$router.push('/banks')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addBank(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('banks.created_message'))
          this.$router.push('/banks')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
  }
}
</script>
