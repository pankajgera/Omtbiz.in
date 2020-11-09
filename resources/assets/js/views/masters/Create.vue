<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/masters">{{ $tc('masters.account_master',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('masters.edit_master') : $t('masters.new_master') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="card">
          <form action="" @submit.prevent="submitMaster">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('masters.name') }}</label><span class="text-danger"> *</span>
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
              <div class="form-group col-sm-6">
                <label>{{ $t('expenses.group') }}</label>
                <select v-model="formData.group" name="group" class="form-control ls-select2">
                  <option v-for="(group, index) in groups.filter((v, i, a) => a.indexOf(v) === i)" :key="index" :value="group.id"> {{ group.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label for="address">{{ $t('masters.address') }}</label>
                <base-text-area
                  v-model="formData.address"
                  rows="2"
                  name="address"
                  @input="$v.formData.address.$touch()"
                />
                <div v-if="$v.formData.address.$error">
                  <span v-if="!$v.formData.address.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
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
      title: 'Add Master',
      formData: {
        name: '',
        description: '',
        price: '0.00',
        unit: '0',
        image: '',
        date: '',
        account_master: ''
      },
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '$ ',
        precision: 2,
        masked: false
      },
      previewImage: ''
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrencyForInput'
    ]),
    price: {
      get: function () {
        return this.formData.price / 100
      },
      set: function (newValue) {
        this.formData.price = newValue * 100
      }
    },
    isEdit () {
      if (this.$route.name === 'masters.edit') {
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
      groups: {
        required,
      },
      address: {
        maxLength: maxLength(255)
      },
    }
  },
  methods: {
    ...mapActions('master', [
      'addMaster',
      'fetchMaster',
      'updateMaster'
    ]),
    async loadEditData () {
      let response = await this.fetchMaster(this.$route.params.id)
      this.formData = response.data.master
    },
    async submitMaster () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateMaster(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('masters.updated_message'))
          this.$router.push('/masters')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addMaster(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('masters.created_message'))
          this.$router.push('/masters')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
  }
}
</script>
