<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('inventory.edit_inventory') : $t('inventory.new_inventory') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/dashboard">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('inventory.edit_inventory') : $t('inventory.new_inventory') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-8">
        <div class="card">
          <form action="" @submit.prevent="submitInventory">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.name') }}</label><span class="text-danger"> *</span>
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
                <label class="control-label">{{ $t('inventory.quantity') }}</label>
                <base-input
                  v-model.trim="formData.quantity"
                  focus
                  type="text"
                  name="quantity"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.price') }}</label>
                <base-input
                  v-model.trim="formData.price"
                  focus
                  type="text"
                  name="price"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.unit') }}</label>
                <base-input
                  v-model.trim="formData.unit"
                  focus
                  type="text"
                  name="unit"
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
                  {{ isEdit ? $t('inventory.update_inventory') : $t('inventory.save_inventory') }}
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
      title: 'Add Inventory',
      formData: {
        name: '',
        quantity: '',
        price: '',
        unit: '',
        per_price: '',
        inventory: ''
      },
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'inventory.edit') {
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
    ...mapActions('inventory', [
      'addInventory',
      'fetchInventory',
      'updateInventory'
    ]),
    async loadEditData () {
      let response = await this.fetchInventory(this.$route.params.id)
      this.formData = response.data.inventory
    },
    async submitInventory () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateInventory(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('inventory.updated_message'))
          this.$router.push('/inventory')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addInventory(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('inventory.created_message'))
          this.$router.push('/inventory')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
  }
}
</script>
