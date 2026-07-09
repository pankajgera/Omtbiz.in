<template>
  <div class="setting-main-container">
    <form @submit.prevent="updateUserData">
      <div class="card setting-card">
        <div class="page-header">
          <h3 class="page-title">{{ $t('settings.account_settings.account_settings') }}</h3>
          <p class="page-sub-title">
            {{ $t('settings.account_settings.section_description') }}
          </p>
        </div>
        <!-- <div class="row mb-4">
          <div class="col-md-6">
            <label class="input-label">{{ $tc('settings.account_settings.profile_picture') }}</label>
            <div id="pick-avatar" class="image-upload-box avatar-upload">
              <div class="overlay">
                <font-awesome-icon class="white-icon" icon="camera"/>
              </div>
              <img v-if="previewAvatar" :src="previewAvatar" class="preview-logo">
              <div v-if="!previewAvatar" class="upload-content">
                <font-awesome-icon class="upload-icon" icon="cloud-upload-alt"/>
                <p class="upload-text"> {{ $tc('general.choose_file') }} </p>
              </div>
            </div>
          </div>
          <avatar-cropper
            :labels="{ submit: 'submit', cancel: 'Cancle'}"
            :cropper-options="cropperOptions"
            :output-options="cropperOutputOptions"
            :output-quality="0.8"
            :upload-handler="cropperHandler"
            trigger="#pick-avatar"
            @changed="setFileObject"
            @error="handleUploadError"
          />
        </div> -->
        <div class="row">
          <div class="col-md-6 mb-4 form-group">
            <label class="input-label">{{ $tc('settings.account_settings.name') }}</label>
            <base-input
              v-model="formData.name"
              :invalid="vFormData.name.$error"
              :placeholder="$t('settings.user_profile.name')"
              @input="vFormData.name.$touch()"
            />
            <div v-if="vFormData.name.$error">
              <span v-if="!vFormData.name.required" class="text-danger">{{ $tc('validation.required') }}</span>
            </div>
          </div>
          <div class="col-md-6 mb-4 form-group">
            <label class="input-label">{{ $tc('settings.account_settings.email') }}</label>
            <base-input
              v-model="formData.email"
              :invalid="vFormData.email.$error"
              :placeholder="$t('settings.user_profile.email')"
              @input="vFormData.email.$touch()"
            />
            <div v-if="vFormData.email.$error">
              <span v-if="!vFormData.email.required" class="text-danger">{{ $tc('validation.required') }}</span>
              <span v-if="!vFormData.email.email" class="text-danger">{{ $tc('validation.email_incorrect') }}</span>
            </div>
          </div>
          <div class="col-md-6 mb-4 form-group">
            <label class="input-label">{{ $tc('settings.account_settings.password') }}</label>
            <base-input
              v-model="formData.password"
              :invalid="vFormData.password.$error"
              :placeholder="$t('settings.user_profile.password')"
              type="password"
              @input="vFormData.password.$touch()"
            />
            <div v-if="vFormData.password.$error">
              <span v-if="!vFormData.password.minLength" class="text-danger"> {{ $tc('validation.password_min_length', vFormData.password.$params.minLength.min, {count: vFormData.password.$params.minLength.min}) }} </span>
            </div>
          </div>
          <div class="col-md-6 mb-4 form-group">
            <label class="input-label">{{ $tc('settings.account_settings.confirm_password') }}</label>
            <base-input
              v-model="formData.confirm_password"
              :invalid="vFormData.confirm_password.$error"
              :placeholder="$t('settings.user_profile.confirm_password')"
              type="password"
              @input="vFormData.confirm_password.$touch()"
            />
            <div v-if="vFormData.confirm_password.$error">
              <span v-if="!vFormData.confirm_password.sameAsPassword" class="text-danger">{{ $tc('validation.password_incorrect') }}</span>
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
              {{ $tc('settings.account_settings.save') }}
            </base-button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions } from 'vuex'
import AvatarCropper from 'vue-avatar-cropper'
import { required, requiredIf, sameAs, email, minLength } from '@vuelidate/validators';
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
        password: '',
        confirm_password: ''
      },
      isLoading: false,
      previewAvatar: null,
      fileObject: null
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
        minLength: minLength(5)
      },
      confirm_password: {
        required: requiredIf('isRequired'),
        sameAsPassword: sameAs('password')
      }
    }
  },
  computed: {
    vFormData () {
      return this.$v?.formData || {
        $error: false,
        $invalid: false,
        $touch: () => {},
        name: { $error: false, required: true, $touch: () => {} },
        email: { $error: false, required: true, email: true, $touch: () => {} },
        password: { $error: false, minLength: true, $touch: () => {}, $params: { minLength: { min: 0 } } },
        confirm_password: { $error: false, sameAsPassword: true, $touch: () => {} }
      }
    },
    isRequired () {
      if (!this.formData.password) {
        return false
      }
      return true
    }
  },
  mounted () {
    this.setInitialData()
  },
  methods: {
    ...mapActions('userProfile', [
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
      this.formData.name = response.data.name
      this.formData.email = response.data.email
      // if (response.data.avatar) {
      //   this.previewAvatar = response.data.avatar
      // } else {
      // }
      this.previewAvatar = '/images/default-avatar.jpg'
    },
    async updateUserData () {
      this.vFormData.$touch()
      if (this.$v.$invalid) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return true
      }
      this.isLoading = true
      let data = {
        name: this.formData.name,
        email: this.formData.email
      }
      if (this.formData.password != null && this.formData.password !== undefined && this.formData.password !== '') {
        data = { ...data, password: this.formData.password }
      }
      let response = await this.editUser(data)
      if (response.data.success) {
        this.isLoading = false
        // if (this.fileObject && this.previewAvatar) {
        //   let avatarData = new FormData()
        //   avatarData.append('admin_avatar', JSON.stringify({
        //     name: this.fileObject.name,
        //     data: this.previewAvatar
        //   }))
        //   this.uploadAvatar(avatarData)
        // }
        window.toastr['success'](this.$t('settings.account_settings.updated_message'))
        return true
      }
      window.toastr['error'](response.data.error)
      return true
    }
  }
}
</script>
