<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('bank.edit_bank') : $t('banks.new_bank') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/bank">{{ $tc('banks.banks',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('bank.edit_bank') : $t('banks.new_bank') }}</a></li>
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
                  :invalid="v$.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  @input="v$.formData.name.$touch()"
                />
                <div v-if="v$.formData.name.$error">
                  <span v-if="v$.formData.name.required.$invalid" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="v$.formData.name.minLength.$invalid" class="text-danger">
                    {{ $tc('validation.name_min_length', v$.formData.name.minLength.$params.min, { count: v$.formData.name.minLength.$params.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.amount') }} *</label>
                <base-input v-model="formData.amount" type="number" name="amount" :invalid="v$.formData.amount.$error" />
                <span v-if="v$.formData.amount.$error" class="text-danger">{{ $t('validation.required') }}</span>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('banks.date') }} *</label>
                <base-input v-model="formData.date" type="date" name="date" :invalid="v$.formData.date.$error" />
                <span v-if="v$.formData.date.$error" class="text-danger">{{ $t('validation.required') }}</span>
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
import useVuelidate from '@vuelidate/core'
import { mapActions, mapGetters } from 'vuex'
import { required, minLength, decimal, minValue } from '@vuelidate/validators';
export default {
  setup () { return { v$: useVuelidate() } },
  data () {
    return {
      isLoading: false,
      title: 'Add Bank',
      formData: {
        name: '',
        amount: '',
        date: ''
      },
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'bank.edit') {
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
      amount: { required, decimal, minValue: minValue(0) },
      date: { required },
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
      this.formData = { ...response.data.bank, date: response.data.bank.date?.slice(0, 10) }
    },
    async submitBank () {
      this.v$.formData.$touch()
      if (this.v$.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return false
      }
      this.isLoading = true
      try {
        if (this.isEdit) {
          let response = await this.updateBank(this.formData)
          if (response.data.bank) {
            this.isLoading = false
            window.toastr['success'](this.$tc('banks.updated_message'))
            this.$router.push('/bank')
            return true
          }
          window.toastr['error'](response.data.error)
        } else {
          let response = await this.addBank(this.formData)
          if (response.data.bank) {
            window.toastr['success'](this.$tc('banks.created_message'))
            this.$router.push('/bank')
            this.isLoading = false
            return true
          }
          window.toastr.error(response.data.error || this.$t('banks.save_failed'))
        }
      } catch (error) {
        window.toastr.error(error.response?.data?.message || this.$t('banks.save_failed'))
      } finally {
        this.isLoading = false
      }
    },
  }
}
</script>
