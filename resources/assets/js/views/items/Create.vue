<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('items.edit_bill') : $t('items.new_bill') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/items">{{ $tc('items.bill_ty',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('items.edit_bill') : $t('items.new_bill') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="card">
          <form action="" @submit.prevent="submitItem">
            <div class="card-body">
              <div class="form-group">
                <label class="control-label">{{ $t('items.name') }}</label><span class="text-danger"> *</span>
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
                <label>{{ $t('items.bill_ty') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.bill_ty"
                  focus
                  type="text"
                  name="bill_ty"
                  @input="$v.formData.bill_ty.$touch()"
                />
              </div>
              <div class="form-group">
                <label for="date">{{ $t('items.date') }}</label><span class="text-danger"> *</span>
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
                <label for="description">{{ $t('items.description') }}</label>
                <base-text-area
                  v-model="formData.description"
                  rows="2"
                  name="description"
                  @input="$v.formData.description.$touch()"
                />
                <div v-if="$v.formData.description.$error">
                  <span v-if="!$v.formData.description.maxLength" class="text-danger">{{ $t('validation.description_maxlength') }}</span>
                </div>
              </div>
              <div class="form-group">
                <img :src="previewImage" class="uploading-image" style="width: 100px">
                <input type="file" accept="image/*" name="image" @change="uploadImage">
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
                  {{ isEdit ? $t('items.update_item') : $t('items.save_item') }}
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
      title: 'Add Item',
      units: [
        { name: 'box', value: 'box' },
        { name: 'cm', value: 'cm' },
        { name: 'dz', value: 'dz' },
        { name: 'ft', value: 'ft' },
        { name: 'g', value: 'g' },
        { name: 'in', value: 'in' },
        { name: 'kg', value: 'kg' },
        { name: 'km', value: 'km' },
        { name: 'lb', value: 'lb' },
        { name: 'mg', value: 'mg' },
        { name: 'pc', value: 'pc' }
      ],
      formData: {
        name: '',
        description: '',
        price: '0.00',
        unit: '0',
        image: '',
        date: '',
        bill_ty: ''
      },
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '₹ ',
        precision: 2,
        masked: false
      },
      previewImage: ''
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
    isEdit () {
      if (this.$route.name === 'items.edit') {
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
      // price: {
      //   required,
      //   numeric,
      //   maxLength: maxLength(20),
      //   minValue: minValue(0.1)
      // },
      description: {
        maxLength: maxLength(255)
      },
      // unit: {
      //   required
      // },
      date: {
        required
      },
      bill_ty: {
        required
      }
    }
  },
  methods: {
    ...mapActions('item', [
      'addItem',
      'fetchItem',
      'updateItem'
    ]),
    async loadEditData () {
      let response = await this.fetchItem(this.$route.params.id)
      this.formData = response.data.item
      this.formData.unit = this.units.find(_unit => response.data.item.unit === _unit.name)
      this.fractional_price = response.data.item.price
    },
    async submitItem () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        return false
      }
      if (this.isEdit) {
        this.isLoading = true
        let response = await this.updateItem(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('items.updated_message'))
          this.$router.push('/items')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        this.isLoading = true
        let response = await this.addItem(this.formData)

        if (response.data) {
          window.toastr['success'](this.$tc('items.created_message'))
          this.$router.push('/items')
          this.isLoading = false
          return true
        }
        window.toastr['success'](response.data.success)
      }
    },
    uploadImage (e) {
      const image = e.target.files[0]
      const reader = new FileReader()
      reader.readAsDataURL(image)
      reader.onload = e => {
        this.previewImage = e.target.result
        this.formData.image = this.previewImage
      }
    }
  }
}
</script>
