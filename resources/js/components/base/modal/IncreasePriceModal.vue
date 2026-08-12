<template>
  <div class="item-modal">
    <form action="" @submit.prevent="submitPriceInventory">
      <div class="card-body">
        <div class="form-group row">
          <label class="col-sm-3 col-form-label input-label">{{ $t('items.price') }}<span class="required">*</span></label>
          <div class="col-sm-5">
            <base-input
                ref="price"
                v-model.trim="price"
                :class="{'invalid' : $v.formData.price.$error, 'input-field': true}"
                format-two-decimals
                type="text"
                name="price"
              />
            <div v-if="$v.formData.price.$error">
              <span v-if="!$v.formData.price.required" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="!$v.formData.price.numeric" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
              <span v-if="!$v.formData.price.maxLength" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
              <span v-if="!$v.formData.price.minValue" class="text-danger">{{ $t('validation.price_minvalue') }}</span>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-3 col-form-label input-label">{{ $t('items.sale_price') }}<span class="required">*</span></label>
          <div class="col-sm-5">
            <base-input
                ref="sale_price"
                v-model.trim="sale_price"
                :class="{'invalid' : $v.formData.sale_price.$error, 'input-field': true}"
                format-two-decimals
                type="text"
                name="sale_price"
              />
            <div v-if="$v.formData.sale_price.$error">
              <span v-if="!$v.formData.sale_price.required" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="!$v.formData.sale_price.numeric" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
              <span v-if="!$v.formData.sale_price.maxLength" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
              <span v-if="!$v.formData.sale_price.minValue" class="text-danger">{{ $t('validation.price_minvalue') }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">

        <base-button
          :outline="true"
          class="mr-3"
          color="theme"
          type="button"
          @click="closeInventoryModal"
        >
          {{ $t('general.cancel') }}
        </base-button>
        <base-button
          v-if="isEdit"
          :loading="isLoading"
          color="theme"
          @click="submitPriceInventory"
        >
          {{ $t('general.update') }}
        </base-button>
        <base-button
          v-else
          :loading="isLoading"
          icon="save"
          color="theme"
          type="submit"
        >
          {{ $t('general.save') }}
        </base-button>
      </div>
    </form>
  </div>
</template>
<style>
div.hide-select-header div.multiselect__tags input.multiselect__input{
  display: none;
}
</style>
<script>
import { mapActions, mapGetters } from 'vuex'
import { validationMixin } from 'vuelidate'
import { required, minLength, maxLength, minValue } from 'vuelidate/lib/validators'
const decimal = (value) => {
  if (value === null || value === undefined || value === '') {
    return true
  }
  return /^\d+(\.\d+)?$/.test(String(value))
}
export default {
  mixins: [validationMixin],
  data () {
    return {
      isEdit: false,
      isLoading: false,
      tempData: null,
      formData: {
        price: null,
        sale_price: null,
        selected_ids: [],
      }
    }
  },
  validations: {
    formData: {
      price: {
        required,
        decimal,
        minValue: minValue(0.1),
        maxLength: maxLength(20)
      },
      sale_price: {
        required,
        decimal,
        minValue: minValue(0.1),
        maxLength: maxLength(20)
      },
    }
  },
  computed: {
    price: {
      get: function () {
        return this.formData.price
      },
      set: function (newValue) {
        this.formData.price = newValue
      }
    },
    sale_price: {
      get: function () {
        return this.formData.sale_price
      },
      set: function (newValue) {
        this.formData.sale_price = newValue
      }
    },
    ...mapGetters('modal', [
      'modalData',
    ]),
    ...mapGetters('inventory', [
      'getInventoryById'
    ])
  },
  mounted () {
    this.$refs.price.focus = true
  },
  methods: {
    ...mapActions('modal', [
      'closeModal',
      'resetModalData'
    ]),
    ...mapActions('inventory', [
      'increaseInventoryPrice',
    ]),
    ...mapActions('invoice', [
      'setInventory'
    ]),
    resetFormData () {
      this.formData = {
        price: null,
        sale_price: null,
        selected_ids: []
      }
      this.$v.$reset()
    },
    async submitPriceInventory () {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }
      this.formData.selected_ids = this.modalData
      this.isLoading = true
      let response = await this.increaseInventoryPrice(this.formData)
      if (response.data) {
        window.toastr['success'](this.$tc('items.price_increase_message'))
        this.setInventory(response.data.inventory)
        window.hub.$emit('newInventory', response.data.inventory)
        this.isLoading = false
        this.resetModalData()
        this.resetFormData()
        this.closeModal()
        return true
      }
      window.toastr['error'](response.data.error)
    },
    closeInventoryModal () {
      this.resetFormData()
      this.closeModal()
      this.resetModalData()
      window.location.reload()
    }
  }
}
</script>
