<template>
  <div class="user-create main-content">
    <form action="" @submit.prevent="submitUserData">
      <div class="page-header">
        <h3 class="page-title">{{ isEdit ? $t('users.edit_user') : $t('users.new_user') }}</h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link slot="item-title" to="/invoices">{{ $t('general.home') }}</router-link></li>
          <li class="breadcrumb-item"><router-link slot="item-title" to="/users">{{ $tc('users.user', 2) }}</router-link></li>
          <li class="breadcrumb-item">{{ isEdit ? $t('users.edit_user') : $t('users.new_user') }}</li>
        </ol>
      </div>
      <div class="user-card card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.name') }}</label><span class="text-danger"> * </span>
              <base-input
                v-model="formData.name"
                :invalid="$v.formData.name.$error"
                :placeholder="$t('users.name')"
                @input="$v.formData.name.$touch()"
              />
              <div v-if="$v.formData.name.$error">
                <span v-if="!$v.formData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.email') }}</label><span class="text-danger"> * </span>
              <base-input
                v-model="formData.email"
                :invalid="$v.formData.email.$error"
                :placeholder="$t('users.email')"
                @input="$v.formData.email.$touch()"
              />
              <div v-if="$v.formData.email.$error">
                <span v-if="!$v.formData.email.required" class="text-danger">{{ $tc('validation.required') }}</span>
                <span v-if="!$v.formData.email.email" class="text-danger">{{ $tc('validation.email_incorrect') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.password') }}</label><span class="text-danger"> * </span>
              <base-input
                v-model="formData.password"
                :invalid="$v.formData.password.$error"
                :placeholder="!isEdit ? $t('users.password') : $t('users.change_password')"
                type="password"
                @input="$v.formData.password.$touch()"
              />
              <div v-if="$v.formData.password.$error">
                <span v-if="!$v.formData.password.minLength" class="text-danger"> {{ $tc('validation.password_min_length', $v.formData.password.$params.minLength.min, {count: $v.formData.password.$params.minLength.min}) }} </span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.confirm_password') }}</label><span class="text-danger"> * </span>
              <base-input
                v-model="formData.confirm_password"
                :invalid="$v.formData.confirm_password.$error"
                :placeholder="!isEdit ? $t('users.confirm_password') : $t('users.change_confirm_password')"
                type="password"
                @input="$v.formData.confirm_password.$touch()"
              />
              <div v-if="$v.formData.confirm_password.$error">
                <span v-if="!$v.formData.confirm_password.sameAsPassword" class="text-danger">{{ $tc('validation.password_incorrect') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.company_name') }}</label><span class="text-danger"> * </span>
              <base-select
                v-model="companyBind"
                :options="companies"
                :class="{'error': $v.formData.company.$error }"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$tc('users.companies')"
                :value="formData.company"
                label="name"
                track-by="id"
                name="company"
                id="company"
              />
              <div v-if="$v.formData.company.$error">
                <span v-if="!$v.formData.company.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('users.role') }}</label><span class="text-danger"> * </span>
              <base-select
                v-model="roleBind"
                :options="roles"
                :class="{'error': $v.formData.role.$error}"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$tc('users.roles')"
                :value="formData.role"
                label="name"
                track-by="id"
                name="role"
                id="role"
              />
              <div v-if="$v.formData.role.$error">
                <span v-if="!$v.formData.role.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
          </div>
          <hr>
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
      </div>
    </form>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import MultiSelect from 'vue-multiselect'
import { validationMixin } from 'vuelidate'
import { required, requiredIf, sameAs, minLength, email, url, maxLength } from 'vuelidate/lib/validators';
export default {
  components: { MultiSelect },
  mixins: [validationMixin],
  data () {
    return {
      isFetchingData: false,
      isCopyFromBilling: false,
      isLoading: false,
      formData: {
        name: null,
        email: null,
        company: null,
        role: null
      },
      companies: [],
      roles: [],
      companyBind: null,
      roleBind: null
    }
  },
  validations: {
    formData: {
      name: {
        required
      },
      email: {
        required,
        email
      },
      password: {
        required,
        minLength: minLength(5)
      },
      confirm_password: {
        required: requiredIf('isRequired'),
        sameAsPassword: sameAs('password')
      },
      company: {
        required
      },
      role: {
        required
      },
    }
  },
  watch: {
    companyBind (newCompany) {
      this.formData.company = newCompany.name
      if (this.isFetchingData) {
        return true
      }
    },
    roleBind (newRole) {
      this.formData.role = newRole.name
      if (this.isFetchingData) {
        return true
      }
    }
  },
  computed: {
    isEdit () {
      if (this.$route.name === 'users.edit') {
        return true
      }
      return false
    },
    isRequired () {
      if (this.formData.password === null || this.formData.password === undefined || this.formData.password === '') {
        return false
      }
      return true
    }
  },
  mounted () {
    if (this.isEdit) {
      this.loaduser()
    }
    this.loadNewUser()
  },
  methods: {
    ...mapActions('user', [
      'addUser',
      'fetchUser',
      'updateUser',
      'fetchRolesAndCompanies'
    ]),
    async loaduser () {
      let { data: { user, companies, roles } } = await this.fetchUser(this.$route.params.id)
      this.isFetchingData = true
      this.formData.id = user.id
      this.formData.name = user.name
      this.formData.email = user.email
      this.formData.company = user.company_name
      this.formData.role = user.role
      this.companyBind = companies.find(each => each.name === user.company_name)
      this.roleBind = roles.find(each => each.name === user.role)
    },

    async loadNewUser () {
      let { data: { companies, roles } } = await this.fetchRolesAndCompanies()

      this.roles = roles;
      this.companies = companies;
    },

    async submitUserData () {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }

      this.isLoading = true
      if (this.isEdit) {
        try {
          let response = await this.updateUser(this.formData)
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
      } else { //Add new user
        try {
          let response = await this.addUser(this.formData)
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
