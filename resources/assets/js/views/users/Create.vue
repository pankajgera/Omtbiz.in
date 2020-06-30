<template>
  <div class="user-create main-content">
    <form action="" @submit.prevent="submitUserData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('users.edit_user') : $t('users.new_user') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/dashboard">{{ $t('general.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link slot="item-title" to="/users">{{ $tc('users.user', 2) }}</router-link></li>
          <li class="breadcrumb-item">{{ isEdit ? $t('users.edit_user') : $t('users.new_user') }}</li>
        </ol>
        <div class="page-actions header-button-container">
          <base-button
            :loading="isLoading"
            :disabled="isLoading"
            :tabindex="23"
            icon="save"
            color="theme"
            type="submit"
          >
            {{ isEdit ? $t('users.update_user') : $t('users.save_user') }}
          </base-button>
        </div>
      </div>
      <div class="user-card card">
        <div class="card-body">
          <div class="row">
            <div class="section-title col-sm-2">{{ $t('users.basic_info') }}</div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.display_name') }}</label><span class="text-danger"> *</span>
                <base-input
                  :invalid="$v.formData.name.$error"
                  v-model="formData.name"
                  focus
                  type="text"
                  name="name"
                  tab-index="1"
                  @input="$v.formData.name.$touch()"
                />
                <div v-if="$v.formData.name.$error">
                  <span v-if="!$v.formData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
                  <span v-if="!$v.formData.name.minLength" class="text-danger"> {{ $tc('validation.name_min_length', $v.formData.name.$params.minLength.min, { count: $v.formData.name.$params.minLength.min }) }} </span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.email') }}</label>
                <base-input
                  :invalid="$v.formData.email.$error"
                  v-model.trim="formData.email"
                  type="text"
                  name="email"
                  tab-index="3"
                  @input="$v.formData.email.$touch()"
                />
                <div v-if="$v.formData.email.$error">
                  <span v-if="!$v.formData.email.email" class="text-danger"> {{ $tc('validation.email_incorrect') }} </span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.primary_currency') }}</label>
                <base-select
                  v-model="currency"
                  :options="currencies"
                  :custom-label="currencyNameWithCode"
                  :allow-empty="false"
                  :searchable="true"
                  :show-labels="false"
                  :tabindex="5"
                  :placeholder="$t('users.select_currency')"
                  label="name"
                  track-by="id"
                />
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.primary_contact_name') }}</label>
                <base-input
                  v-model.trim="formData.contact_name"
                  :label="$t('users.contact_name')"
                  type="text"
                  tab-index="2"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.phone') }}</label>
                <base-input
                  v-model.trim="formData.phone"
                  type="text"
                  name="phone"
                  tab-index="4"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.website') }}</label>
                <base-input
                  v-model="formData.website"
                  :invalid="$v.formData.website.$error"
                  type="url"
                  @input="$v.formData.website.$touch()"
                />
                <div v-if="$v.formData.website.$error">
                  <span v-if="!$v.formData.website.url" class="text-danger">{{ $tc('validation.invalid_url') }}</span>
                </div>
              </div>
            </div>
          </div>
          <hr> <!-- first row complete  -->
          <div class="row">
            <div class="section-title col-sm-2">{{ $t('users.billing_address') }}</div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.name') }}</label>
                <base-input
                  v-model.trim="billing.name"
                  type="text"
                  name="address_name"
                  tab-index="7"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.state') }}</label>
                <base-input
                  v-model="billing.state"
                  name="billing.state"
                  type="text"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.address') }}</label>
                <base-text-area
                  v-model.trim="billing.address_street_1"
                  :tabindex="11"
                  :placeholder="$t('general.street_1')"
                  type="text"
                  name="billing_street1"
                  rows="2"
                  @input="$v.billing.address_street_1.$touch()"
                />
                <div v-if="$v.billing.address_street_1.$error">
                  <span v-if="!$v.billing.address_street_1.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
                <base-text-area
                  :tabindex="12"
                  v-model.trim="billing.address_street_2"
                  :placeholder="$t('general.street_2')"
                  type="text"
                  name="billing_street2"
                  rows="2"
                  @input="$v.billing.address_street_2.$touch()"
                />
                <div v-if="$v.billing.address_street_2.$error">
                  <span v-if="!$v.billing.address_street_2.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.country') }}</label>
                <base-select
                  v-model="billing_country"
                  :options="billingCountries"
                  :searchable="true"
                  :show-labels="false"
                  :allow-empty="true"
                  :tabindex="8"
                  :placeholder="$t('general.select_country')"
                  label="name"
                  track-by="id"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.city') }}</label>
                <base-input
                  v-model="billing.city"
                  name="billing.city"
                  type="text"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.phone') }}</label>
                <base-input
                  v-model.trim="billing.phone"
                  type="text"
                  name="phone"
                  tab-index="13"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.zip_code') }}</label>
                <base-input
                  v-model.trim="billing.zip"
                  type="text"
                  name="zip"
                  tab-index="14"
                />
              </div>
            </div>
          </div>
          <hr> <!-- second row complete  -->
          <div class="same-address-checkbox-container">
            <div class="p-1">
              <base-button ref="sameAddress" icon="copy" color="theme" class="btn-sm" @click="copyAddress(true)">
                {{ $t('users.copy_billing_address') }}
              </base-button>
            </div>
          </div>

          <div class="row">
            <div class="section-title col-sm-2">
              {{ $t('users.shipping_address') }}
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.name') }}</label>
                <base-input
                  v-model.trim="shipping.name"
                  type="text"
                  name="address_name"
                  tab-index="15"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.state') }}</label>
                <base-input
                  v-model="shipping.state"
                  name="shipping.state"
                  type="text"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.address') }}</label>
                <base-text-area
                  v-model.trim="shipping.address_street_1"
                  :tabindex="19"
                  :placeholder="$t('general.street_1')"
                  type="text"
                  name="street_1"
                  rows="2"
                  @input="$v.shipping.address_street_1.$touch()"
                />
                <div v-if="$v.shipping.address_street_1.$error">
                  <span v-if="!$v.shipping.address_street_1.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
                <base-text-area
                  v-model.trim="shipping.address_street_2"
                  :tabindex="20"
                  :placeholder="$t('general.street_2')"
                  type="text"
                  name="street_2"
                  rows="2"
                  @input="$v.shipping.address_street_2.$touch()"
                />
                <div v-if="$v.shipping.address_street_2.$error">
                  <span v-if="!$v.shipping.address_street_2.maxLength" class="text-danger">{{ $t('validation.address_maxlength') }}</span>
                </div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <label class="form-label">{{ $t('users.country') }}</label>
                <base-select
                  v-model="shipping_country"
                  :options="shippingCountries"
                  :searchable="true"
                  :show-labels="false"
                  :tabindex="16"
                  :allow-empty="true"
                  :placeholder="$t('general.select_country')"
                  label="name"
                  track-by="id"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.city') }}</label>
                <base-input
                  v-model="shipping.city"
                  name="shipping.city"
                  type="text"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.phone') }}</label>
                <base-input
                  v-model.trim="shipping.phone"
                  type="text"
                  name="phone"
                  tab-index="21"
                />
              </div>
              <div class="form-group">
                <label class="form-label">{{ $t('users.zip_code') }}</label>
                <base-input
                  v-model.trim="shipping.zip"
                  type="text"
                  name="zip"
                  tab-index="22"
                />
              </div>
              <div class="form-group collapse-button-container">
                <base-button
                  :tabindex="23"
                  icon="save"
                  color="theme"
                  class="collapse-button"
                  type="submit"
                >
                  {{ $t('users.save_user') }}
                </base-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import MultiSelect from 'vue-multiselect'
import { validationMixin } from 'vuelidate'
import AddressStub from '../../stub/address'
const { required, minLength, email, url, maxLength } = require('vuelidate/lib/validators')

export default {
  components: { MultiSelect },
  mixins: [validationMixin],
  data () {
    return {
      isCopyFromBilling: false,
      isLoading: false,
      formData: {
        name: null,
        contact_name: null,
        email: null,
        phone: null,
        currency_id: null,
        website: null,
        addresses: []
      },
      currency: null,
      billing: {
        name: null,
        country_id: null,
        state: null,
        city: null,
        phone: null,
        zip: null,
        address_street_1: null,
        address_street_2: null,
        type: 'billing'
      },
      shipping: {
        name: null,
        country_id: null,
        state: null,
        city: null,
        phone: null,
        zip: null,
        address_street_1: null,
        address_street_2: null,
        type: 'shipping'
      },
      currencyList: [],

      billing_country: null,
      shipping_country: null,

      billingCountries: [],
      shippingCountries: []
    }
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3)
      },
      email: {
        email
      },
      website: {
        url
      }
    },
    billing: {
      address_street_1: {
        maxLength: maxLength(255)
      },
      address_street_2: {
        maxLength: maxLength(255)
      }
    },
    shipping: {
      address_street_1: {
        maxLength: maxLength(255)
      },
      address_street_2: {
        maxLength: maxLength(255)
      }
    }
  },
  computed: {
    ...mapGetters('currency', [
      'defaultCurrency',
      'currencies'
    ]),
    isEdit () {
      if (this.$route.name === 'users.edit') {
        return true
      }
      return false
    },
    hasBillingAdd () {
      let billing = this.billing
      if (
        billing.name ||
        billing.country_id ||
        billing.state ||
        billing.city ||
        billing.phone ||
        billing.zip ||
        billing.address_street_1 ||
        billing.address_street_2) {
        return true
      }
      return false
    },
    hasShippingAdd () {
      let shipping = this.shipping
      if (
        shipping.name ||
        shipping.country_id ||
        shipping.state ||
        shipping.city ||
        shipping.phone ||
        shipping.zip ||
        shipping.address_street_1 ||
        shipping.address_street_2) {
        return true
      }
      return false
    }
  },
  watch: {
    billing_country (newCountry) {
      if (newCountry) {
        this.billing.country_id = newCountry.id
        this.isDisabledBillingState = false
      } else {
        this.billing.country_id = null
      }
    },
    shipping_country (newCountry) {
      if (newCountry) {
        this.shipping.country_id = newCountry.id
        return true
      } else {
        this.shipping.country_id = null
      }
    }
  },
  mounted () {
    this.fetchCountry()
    if (this.isEdit) {
      this.loaduser()
    } else {
      this.currency = this.defaultCurrency
    }
  },
  methods: {
    currencyNameWithCode ({name, code}) {
      return `${code} - ${name}`
    },
    ...mapActions('user', [
      'adduser',
      'fetchuser',
      'updateuser'
    ]),
    async loaduser () {
      let { data: { user, currencies, currency } } = await this.fetchuser(this.$route.params.id)

      this.formData.id = user.id
      this.formData.name = user.name
      this.formData.contact_name = user.contact_name
      this.formData.email = user.email
      this.formData.phone = user.phone
      this.formData.currency_id = user.currency_id
      this.formData.website = user.website

      if (user.billing_address) {
        this.billing = user.billing_address

        if (user.billing_address.country_id) {
          this.billing_country = this.billingCountries.find((c) => c.id === user.billing_address.country_id)
        }
      }

      if (user.shipping_address) {
        this.shipping = user.shipping_address

        if (user.shipping_address.country_id) {
          this.shipping_country = this.shippingCountries.find((c) => c.id === user.shipping_address.country_id)
        }
      }

      this.currencyList = currencies
      this.formData.currency_id = user.currency_id
      this.currency = currency
    },
    async fetchCountry () {
      let res = await window.axios.get('/api/countries')
      if (res) {
        this.billingCountries = res.data.countries
        this.shippingCountries = res.data.countries
      }
    },
    copyAddress (val) {
      if (val === true) {
        this.isCopyFromBilling = true
        this.shipping = {...this.billing, type: 'shipping'}
        this.shipping_country = this.billing_country
        this.shipping_state = this.billing_state
        this.shipping_city = this.billing_city
      } else {
        this.shipping = {...AddressStub, type: 'shipping'}
        this.shipping_country = null
        this.shipping_state = null
        this.shipping_city = null
      }
    },
    async submitUserData () {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }
      if (this.hasBillingAdd && this.hasShippingAdd) {
        this.formData.addresses = [{...this.billing}, {...this.shipping}]
      } else {
        if (this.hasBillingAdd) {
          this.formData.addresses = [{...this.billing}]
        }
        if (this.hasShippingAdd) {
          this.formData.addresses = [{...this.shipping}]
        }
      }

      if (this.isEdit) {
        if (this.currency) {
          this.formData.currency_id = this.currency.id
        }
        this.isLoading = true
        try {
          let response = await this.updateuser(this.formData)
          if (response.data.success) {
            window.toastr['success'](this.$t('users.updated_message'))
            this.$router.push('/users')
            this.isLoading = false
            return true
          } else {
            this.isLoading = false
            if (response.data.error) {
              window.toastr['error'](this.$t('validation.email_already_taken'))
            }
          }
        } catch (err) {
          if (err.response.data.errors.email) {
            this.isLoading = false
            window.toastr['error'](this.$t('validation.email_already_taken'))
          }
        }
      } else {
        this.isLoading = true
        if (this.currency) {
          this.isLoading = true
          this.formData.currency_id = this.currency.id
        }
        try {
          let response = await this.adduser(this.formData)
          if (response.data.success) {
            window.toastr['success'](this.$t('users.created_message'))
            this.$router.push('/users')
            this.isLoading = false
            return true
          }
        } catch (err) {
          if (err.response.data.errors.email) {
            this.isLoading = false
            window.toastr['error'](this.$t('validation.email_already_taken'))
          }
        }
      }
    }
  }
}
</script>
