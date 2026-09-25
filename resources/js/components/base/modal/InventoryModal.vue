<template>
  <div class="item-modal inventory-modal">
    <form action="" @submit.prevent="submitInventoryData">
      <div class="inventory-modal__fields">
        <div class="inventory-modal__field">
          <label for="inventory-modal-name" class="inventory-modal__label">
            {{ $t('inventory.name') }}<span class="required">*</span>
          </label>
          <div class="inventory-modal__control">
            <base-input
              ref="name"
              name="inventory-modal-name"
              :invalid="v$.formData.name.$error"
              v-model.trim="formData.name"
              type="text"
              @input="v$.formData.name.$touch()"
            />

            <div v-if="v$.formData.name.$error">
              <span v-if="v$.formData.name.required.$invalid" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="v$.formData.name.minLength.$invalid" class="text-danger"> {{ $tc('validation.name_min_length', v$.formData.name.minLength.$params.min, { count: v$.formData.name.minLength.$params.min }) }} </span>
            </div>
          </div>
        </div>
        <div class="inventory-modal__field">
          <label for="inventory-modal-worker" class="inventory-modal__label">{{ $t('inventory.worker_name') }}</label>
          <div class="inventory-modal__control">
            <base-input v-model.trim="formData.worker_name" name="inventory-modal-worker" type="text" />
          </div>
        </div>
        <div class="inventory-modal__field">
          <label for="inventory-modal-quantity" class="inventory-modal__label">{{ $t('inventory.quantity') }}<span class="required">*</span></label>
          <div class="inventory-modal__control">
            <base-input
              v-model.trim="formData.quantity"
              :invalid="v$.formData.quantity.$error"
              type="text"
              name="inventory-modal-quantity"
              @input="v$.formData.quantity.$touch()"
            />
            <div v-if="v$.formData.quantity.$error">
              <span v-if="v$.formData.quantity.required.$invalid" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="v$.formData.quantity.numeric.$invalid" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
              <span v-if="v$.formData.quantity.minValue.$invalid" class="text-danger">{{ v$.formData.quantity.minValue.$message }}</span>
            </div>
          </div>
        </div>
        <div class="inventory-modal__field">
          <label for="inventory-modal-price" class="inventory-modal__label">{{ $t('inventory.price') }}<span class="required">*</span></label>
          <div class="inventory-modal__control">
            <base-input
              v-model.trim="formData.price"
              :invalid="v$.formData.price.$error"
              type="text"
              name="inventory-modal-price"
              @input="v$.formData.price.$touch()"
            />
            <div v-if="v$.formData.price.$error">
              <span v-if="v$.formData.price.required.$invalid" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="v$.formData.price.numeric.$invalid" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
              <span v-if="v$.formData.price.maxLength.$invalid" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
              <span v-if="v$.formData.price.minValue.$invalid" class="text-danger">{{ $t('validation.price_minvalue') }}</span>
            </div>
          </div>
        </div>
        <div class="inventory-modal__field">
          <label for="inventory-modal-sale-price" class="inventory-modal__label">{{ $t('inventory.sale_price') }}</label>
          <div class="inventory-modal__control">
            <base-input
              v-model.trim="formData.sale_price"
              :invalid="v$.formData.sale_price.$error"
              type="text"
              name="inventory-modal-sale-price"
              @input="v$.formData.sale_price.$touch()"
            />
            <div v-if="v$.formData.sale_price.$error">
              <span v-if="v$.formData.sale_price.numeric.$invalid" class="text-danger">{{ $tc('validation.numbers_only') }}</span>
              <span v-if="v$.formData.sale_price.maxLength.$invalid" class="text-danger">{{ $t('validation.price_maxlength') }}</span>
              <span v-if="v$.formData.sale_price.minValue.$invalid" class="text-danger">{{ v$.formData.sale_price.minValue.$message }}</span>
            </div>
          </div>
        </div>
        <div class="inventory-modal__field">
          <label class="inventory-modal__label">{{ $t('inventory.unit') }}</label>
          <div class="inventory-modal__control">
            <base-select
              v-model="formData.unit"
              :options="units"
              :aria-label="$t('inventory.unit')"
              :searchable="false"
              :show-labels="false"
               :allow-empty="false"
            />
          </div>
        </div>
      </div>
      <div class="inventory-modal__footer">
        <base-button
          :outline="true"
          class="inventory-modal__cancel"
          color="theme"
          type="button"
          @click="closeInventoryModal"
        >
          {{ $t('general.cancel') }}
        </base-button>
        <base-button
          v-if="isEdit"
          class="inventory-modal__save"
          :loading="isLoading"
          :disabled="isLoading"
          color="theme"
          @click="submitInventoryData"
        >
          {{ $t('general.update') }}
        </base-button>
        <base-button
          v-else
          class="inventory-modal__save"
          :loading="isLoading"
          :disabled="isLoading"
          icon="save"
          color="theme"
          type="submit"
        >
          {{ $t('inventory.save_inventory') }}
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
import { useVuelidate } from '@vuelidate/core'
import { required, minLength, numeric, maxLength, minValue } from '@vuelidate/validators';
export default {
  setup () {
    return { v$: useVuelidate() }
  },
  data () {
    return {
      isEdit: false,
      isLoading: false,
      tempData: null,
      units: ['pc', 'sqm'],
      formData: {
        name: null,
        worker_name: null,
        sale_price: null,
        price: null,
        unit: 'pc',
        quantity: null
      }
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
        numeric,
        minValue: minValue(1)
      },
      sale_price: {
        numeric,
        minValue: minValue(0),
        maxLength: maxLength(20)
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
    ...mapGetters('modal', [
      'modalDataID',
      'modalData'
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
    } else if (this.modalData?.name) {
      this.formData.name = this.modalData.name
    }
  },
  mounted () {
    this.$refs.name.$refs.baseInput.focus()
  },
  methods: {
    ...mapActions('modal', [
      'closeModal',
      'resetModalData'
    ]),
    ...mapActions('inventory', [
      'addInventory',
      'updateInventory',
      'fetchAllInventory'
    ]),
    resetFormData () {
      this.formData = {
        name: null,
        worker_name: null,
        sale_price: null,
        price: null,
        unit: null,
        quantity: null,
        id: null
      }

      this.v$.$reset()
    },
    fetchEditData () {
      this.tempData = this.getInventoryById(this.modalDataID)
      if (this.tempData) {
        this.formData.name = this.tempData.name
        this.formData.worker_name = this.tempData.worker_name
        this.formData.sale_price = this.tempData.sale_price
        this.formData.price = this.tempData.price
        this.formData.unit = this.tempData.unit
        this.formData.quantity = this.tempData.quantity
        this.formData.id = this.tempData.id
      }
    },
    async submitInventoryData () {
      if (this.isLoading) return
      this.v$.formData.$touch()

      if (this.v$.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }
      this.isLoading = true
      const onCreated = this.modalData?.onCreated
      const payload = {
        ...this.formData,
        sale_price: this.formData.sale_price === '' ? null : this.formData.sale_price
      }
      try {
        const response = this.isEdit
          ? await this.updateInventory(payload)
          : await this.addInventory(payload)
        if (!response.data?.inventory?.id) {
          throw new Error(response.data?.error || this.$t('general.action_failed'))
        }
        const inventory = { ...payload, ...response.data.inventory }
        window.toastr['success'](this.$tc(this.isEdit ? 'inventory.updated_message' : 'inventory.created_message'))
        if (onCreated) onCreated(inventory)
        window.hub.$emit('newInventory', inventory)
        this.closeModal()
        // A refresh failure must not turn a successful save into a retry/duplicate.
        this.fetchAllInventory({ name: '', limit: 50, page: 1 }).catch(() => {
          window.toastr['error'](this.$t('general.action_failed'))
        })
      } catch (error) {
        const errors = error.response?.data?.errors
        const message = errors
          ? Object.values(errors).flat().join(' ')
          : error.response?.data?.message || error.message || this.$t('general.action_failed')
        window.toastr['error'](message)
      } finally {
        this.isLoading = false
      }
    },
    closeInventoryModal () {
      this.resetFormData()
      this.closeModal()
      this.resetModalData()
    }
  }
}
</script>
