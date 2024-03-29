<template>
  <div class="main-content item-create">
    <div class="page-header">
      <h3 class="page-title">{{ isEdit ? $t('inventory.edit_inventory') : $t('inventory.new_inventory') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
        <li class="breadcrumb-item"><router-link slot="item-title" to="/inventory">{{ $tc('inventory.inventory',2) }}</router-link></li>
        <li class="breadcrumb-item"><a href="#"> {{ isEdit ? $t('inventory.edit_inventory') : $t('inventory.new_inventory') }}</a></li>
      </ol>
    </div>
    <div class="row">
      <div class="col col-12 col-md-12 col-lg-6">
        <div class="card">
          <form action="" @submit.prevent="submitInventory">
            <div class="card-body">
              <span v-if="isEdit" style="font-style:italic;font-size:10px">Edit will show latest inventory item here and you can't edit old items</span>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.name') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.name"
                  :invalid="$v.formData.name.$error"
                  focus
                  type="text"
                  name="name"
                  :disabled="isEdit"
                />
                <div v-if="$v.formData.name.$error">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger">
                    {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.worker_name') }}</label>
                <base-input
                  v-model.trim="formData.worker_name"
                  focus
                  type="text"
                  name="name"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.quantity') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.quantity"
                  focus
                  :invalid="$v.formData.quantity.$error"
                  type="number"
                  min="0"
                  name="quantity"
                  :disabled="isEdit"
                />
                <div v-if="$v.formData.quantity.$error">
                  <span v-if="!$v.formData.quantity.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.quantity.minValue" class="text-danger">
                    {{ $tc('validation.quantity_min_length', $v.formData.name.$params.minValue.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.price') }}</label><span class="text-danger"> *</span>
                <base-input
                  v-model.trim="formData.price"
                  focus
                  :invalid="$v.formData.price.$error"
                  type="number"
                  min="0"
                  name="price"
                />
                <div v-if="$v.formData.price.$error">
                  <span v-if="!$v.formData.price.required" class="text-danger">{{ $t('validation.required') }} </span>
                  <span v-if="!$v.formData.price.minValue" class="text-danger">
                    {{ $tc('validation.price_min_length', $v.formData.name.$params.minValue.min, { count: $v.formData.name.$params.minLength.min }) }}
                  </span>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.sale_price') }}</label>
                <base-input
                  v-model.trim="formData.sale_price"
                  focus
                  type="number"
                  min="0"
                  name="sale_price"
                />
              </div>
              <div class="form-group">
                <label class="control-label">{{ $t('inventory.unit') }}</label>
                 <base-select
                    v-model="formData.unit"
                    :options="unitOptions"
                    :allow-empty="false"
                    :show-labels="false"
                    :searchable="false"
                    class="hide-select-header"
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
    <div class="row" v-if="relatedInventories.length">
      <div class="col col-12 col-md-12 col-lg-12">
        <div class="card">
          <h5 class="p-3">Related Inventory</h5>
          <table class="p-3 m-3">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Worker Name</th>
              <th>Quantity</th>
              <th>Cost Price</th>
              <th>Sale Price</th>
              <th>Unit</th>
              <th>Date/Time</th>
            </tr>
            <tr v-for="(each, index) in relatedInventories" :key="index">
              <td>{{each.id}}</td>
              <td>{{formData.name}}</td>
              <td>{{each.worker_name}}</td>
              <td>{{each.quantity}}</td>
              <td>{{each.price}}</td>
              <td>{{each.sale_price}}</td>
              <td>{{each.unit}}</td>
              <td>{{each.date_time}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
div.hide-select-header div.multiselect__tags input.multiselect__input{
  display: none;
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
      title: 'Add Inventory',
      formData: {
        id: '',
        name: '',
        worker_name: '',
        quantity: '',
        price: '',
        sale_price: '',
        unit: 'pc',
      },
      unitOptions: ['pc', 'sqm'],
      relatedInventories: [],
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
      quantity: {
        required,
        minValue: 1
      },
      price: {
        required,
        minValue: 1
      },
    }
  },
  methods: {
    ...mapActions('inventory', [
      'addInventory',
      'fetchInventory',
      'updateInventory',
    ]),
    async loadEditData () {
      let response = await this.fetchInventory(this.$route.params.id)
      let data = response.data.inventory
      let latest_item = data.inventory_item[data.inventory_item.length - 1]
      this.formData.id = data.id;
      this.formData.name = data.name;
      this.formData.quantity = latest_item.quantity;
      this.formData.price = latest_item.price;
      this.formData.sale_price = latest_item.sale_price;
      this.formData.worker_name = latest_item.worker_name;
      this.formData.unit = latest_item.unit;
      this.relatedInventories = response.data.related_inventories
    },
    async submitInventory () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return false
      }
      this.isLoading = true
      if (this.isEdit) {
        let response = await this.updateInventory(this.formData)
        if (response.data) {
          this.isLoading = false
          window.toastr['success'](this.$tc('inventory.updated_message'))
          this.$router.push('/inventory')
          return true
        }
        window.toastr['error'](response.data.error)
      } else {
        let response = await this.addInventory(this.formData)
        window.toastr['success'](this.$tc('inventory.created_message'))
        this.isLoading = false
        this.$router.push('/inventory')
        return true
      }
    },
  }
}
</script>
