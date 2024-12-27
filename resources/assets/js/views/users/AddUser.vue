<template>
  <div class="main-content">
    <div class="setting-main-container">
      <form action="" @submit.prevent="updateUserData">
        <div class="card setting-card" style="padding: 30px">
          <div class="page-header">
            <h3 class="page-title">{{ $t('settings.add-user.add-user') }}</h3>
            <p class="page-sub-title">
              {{ $t('settings.add-user.section_description') }}
            </p>
          </div>
          <div class="row">
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.name') }}</label>
              <base-input
                v-model="formData.name"
                :invalid="$v.formData.name.$error"
                :placeholder="$t('settings.add-user.name')"
                @input="$v.formData.name.$touch()"
              />
              <div v-if="$v.formData.name.$error">
                <span v-if="!$v.formData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.email') }}</label>
              <base-input
                v-model="formData.email"
                :invalid="$v.formData.email.$error"
                :placeholder="$t('settings.add-user.email')"
                @input="$v.formData.email.$touch()"
              />
              <div v-if="$v.formData.email.$error">
                <span v-if="!$v.formData.email.required" class="text-danger">{{ $tc('validation.required') }}</span>
                <span v-if="!$v.formData.email.email" class="text-danger">{{ $tc('validation.email_incorrect') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.password') }}</label>
              <base-input
                v-model="formData.password"
                :invalid="$v.formData.password.$error"
                :placeholder="$t('settings.add-user.password')"
                type="password"
                @input="$v.formData.password.$touch()"
              />
              <div v-if="$v.formData.password.$error">
                <span v-if="!$v.formData.password.minLength" class="text-danger"> {{ $tc('validation.password_min_length', $v.formData.password.$params.minLength.min, {count: $v.formData.password.$params.minLength.min}) }} </span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.confirm_password') }}</label>
              <base-input
                v-model="formData.confirm_password"
                :invalid="$v.formData.confirm_password.$error"
                :placeholder="$t('settings.add-user.confirm_password')"
                type="password"
                @input="$v.formData.confirm_password.$touch()"
              />
              <div v-if="$v.formData.confirm_password.$error">
                <span v-if="!$v.formData.confirm_password.sameAsPassword" class="text-danger">{{ $tc('validation.password_incorrect') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.company-name') }}</label><span class="text-danger"> * </span>
              <base-select
                v-model="formData.company"
                :options="companies"
                :class="{'error': $v.formData.company.$error }"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$tc('settings.add-user.companies')"
                label="name"
                track-by="id"
              />
              <div v-if="$v.formData.company.$error">
                <span v-if="!$v.formData.company.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
            <div class="col-md-6 mb-4 form-group">
              <label class="input-label">{{ $tc('settings.add-user.role') }}</label><span class="text-danger"> * </span>
              <base-select
                v-model="formData.role"
                :options="roles"
                :class="{'error': $v.formData.role.$error}"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$tc('settings.add-user.roles')"
                label="name"
                track-by="id"
              />
              <div v-if="$v.formData.role.$error">
                <span v-if="!$v.formData.role.required" class="text-danger">{{ $tc('validation.required') }}</span>
              </div>
            </div>
          </div>
          <div class="row  mb-4">
            <div class="col-md-12 input-group">
              <base-button
                :loading="isLoading"
                :disabled="isLoading"
                icon="save"
                color="theme"
                type="submit"
              >
                {{ $tc('settings.add-user.save') }}
              </base-button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions } from 'vuex'
import AvatarCropper from 'vue-avatar-cropper'
const { required, requiredIf, sameAs, email, minLength } = require('vuelidate/lib/validators')

export default {
  components: { AvatarCropper },
  mixins: [validationMixin],
  data () {
    return {
      cropperOutputOptions: {
        width: 150,
        height: 150
      },
      cropperOptions: {
        autoCropArea: 1,
        viewMode: 0,
        movable: true,
        zoomable: true
      },
      formData: {
        name: null,
        email: null,
        password: null,
        confirm_password: null,
        company: null,
        role: null,
      },
      isLoading: false,
      previewAvatar: null,
      fileObject: null,
      companies: [],
      roles: []
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
  computed: {
    isRequired () {
      if (this.formData.password === null || this.formData.password === undefined || this.formData.password === '') {
        return false
      }
      return true
    }
  },
  mounted () {
    this.setInitialData()
  },
  methods: {
    ...mapActions('addUser', [
      'loadData',
      'editUser',
      'uploadAvatar'
    ]),
    cropperHandler (cropper) {
      this.previewAvatar = cropper.getCroppedCanvas().toDataURL(this.cropperOutputMime)
    },
    setFileObject (file) {
      this.fileObject = file
    },
    handleUploadError (message, type, xhr) {
      window.toastr['error']('Oops! Something went wrong...')
    },
    async setInitialData () {
      let response = await this.loadData()
      this.companies = response.data.companies
      this.roles = response.data.roles
      this.formData.name = response.data.name
      this.formData.email = response.data.email
      if (response.data.avatar) {
        this.previewAvatar = response.data.avatar
      } else {
        this.previewAvatar = '/images/default-avatar.jpg'
      }
    },
    async updateUserData () {
      this.$v.formData.$touch()
      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }
      this.isLoading = true
      let data = {
        name: this.formData.name,
        email: this.formData.email,
        company: this.formData.company,
        role: this.formData.role
      }
      if (this.formData.password != null && this.formData.password !== undefined && this.formData.password !== '') {
        data = { ...data, password: this.formData.password }
      }
      let response = await this.editUser(data)
      if (response.data.success) {
        this.isLoading = false
        if (this.fileObject && this.previewAvatar) {
          let avatarData = new FormData()
          avatarData.append('admin_avatar', JSON.stringify({
            name: this.fileObject.name,
            data: this.previewAvatar
          }))
          this.uploadAvatar(avatarData)
        }
        window.toastr['success'](this.$t('settings.add-user.updated_message'))
        return true
      }
      window.toastr['error'](response.data.error)
      return true
    }
  }
}
</script>
