<template>
  <div class="item-modal">
    <form action="" @submit.prevent="submitInventoryData">
      <div class="card-body">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label input-label">
            {{ $t('items.name') }}<span class="required">*</span>
          </label>
          <div class="col-sm-7">
            <base-input
              ref="name"
              :invalid="$v.formData.name.$error"
              v-model="formData.name"
              type="text"
              @input="$v.formData.name.$touch()"
            />

            <div v-if="$v.formData.name.$error">
              <span v-if="!$v.formData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="!$v.formData.name.minLength" class="text-danger"> {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }} </span>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label input-label">{{ $t('items.price') }}<span class="required">*</span></label>
          <div class="col-sm-7">
            <base-input
                v-model.trim="price"
                :class="{'invalid' : $v.formData.price.$error, 'input-field': true}"
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
          <label class="col-sm-4 col-form-label input-label">{{ $t('items.unit') }}</label>
          <div class="col-sm-7">
            <base-select
              v-model="formData.unit"
              :options="units"
              :searchable="true"
              :show-labels="false"
              label="name"
              class="hide-select-header"
            />
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
          @click="submitInventoryData"
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
const { required, minLength, numeric, maxLength, minValue } = require('vuelidate/lib/validators')
export default {
  mixins: [validationMixin],
  data () {
    return {
      isEdit: false,
      isLoading: false,
      tempData: null,
      units: [
        {name: 'pc' ,value: 'pc'},
        {name: 'sqm' ,value: 'sqm'},
      ],
      formData: {
        name: null,
        price: null,
        unit: null
      }
    }
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
      price: {
        required,
        numeric,
        minValue: minValue(0.1),
        maxLength: maxLength(20)
      },
    }
  },
  computed: {
    price: {
      get: function () {
        return this.formData.price / 100
      },
      set: function (newValue) {
        this.formData.price = newValue * 100
      }
    },
    ...mapGetters('modal', [
      'modalDataID'
    ]),
    ...mapGetters('inventory', [
      'getInventoryById'
    ])
  },
  watch: {
    modalDataID () {
      this.isEdit = true
      this.fetchEditData()
    }
  },
  created () {
    if (this.modalDataID) {
      this.isEdit = true
      this.fetchEditData()
    }
  },
  mounted () {
    this.$refs.name.focus = true
  },
  methods: {
    ...mapActions('modal', [
      'closeModal',
      'resetModalData'
    ]),
    ...mapActions('inventory', [
      'addInventory',
      'updateInventory'
    ]),
    ...mapActions('invoice', [
      'setInventory'
    ]),
    resetFormData () {
      this.formData = {
        name: null,
        price: null,
        unit: null,
        id: null
      }

      this.$v.$reset()
    },
    fetchEditData () {
      this.tempData = this.getInventoryById(this.modalDataID)
      if (this.tempData) {
        this.formData.name = this.tempData.name
        this.formData.price = this.tempData.price
        this.formData.unit = this.tempData.unit
        this.formData.id = this.tempData.id
      }
    },
    async submitInventoryData () {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }
      if (this.formData.unit) {
        this.formData.unit = this.formData.unit.name
      }
      this.isLoading = true
      let response
      if (this.isEdit) {
        response = await this.updateInventory(this.formData)
      } else {
        response = await this.addInventory(this.formData)
      }

      if (response.data) {
        window.toastr['success'](this.$tc('items.created_message'))
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
    }
  }
}
</script>
