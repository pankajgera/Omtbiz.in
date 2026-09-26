<template>
  <form
    id="loginForm"
    @submit.prevent="validateBeforeSubmit"
  >
    <div :class="{'form-group' : true }">
      <p class="input-label">{{ $t('login.email') }} <span class="text-danger"> * </span></p>
      <base-input
        :invalid="v$.loginData.email.$error"
        v-model="loginData.email"
        focus
        type="email"
        name="email"
        :id="'login-id'"
        @input="v$.loginData.email.$touch()"
      />
      <div v-if="v$.loginData.email.$error">
        <span v-if="v$.loginData.email.required.$invalid" class="text-danger">
          {{ $tc('validation.required') }}
        </span>
        <span v-else-if="v$.loginData.email.email.$invalid" class="text-danger">
          {{ $tc('validation.email_incorrect') }}
        </span>
      </div>
    </div>
    <div class="form-group">
      <p class="input-label">{{ $t('login.password') }} <span class="text-danger"> * </span></p>
      <base-input
        v-model="loginData.password"
        :invalid="v$.loginData.password.$error"
        type="password"
        name="password"
        show-password
        :id="'login-password'"
        @input="v$.loginData.password.$touch()"
      />
      <div v-if="v$.loginData.password.$error">
        <span v-if="v$.loginData.password.required.$invalid" class="text-danger">{{ $tc('validation.required') }}</span>
        <span v-else-if="v$.loginData.password.minLength.$invalid" class="text-danger">{{ $t('validation.password_min_length', { count: v$.loginData.password.minLength.$params.min }) }}</span>
      </div>
    </div>
    <div v-if="customError" id="login-error" class="text-danger mb-3" role="alert">
      {{ customError }}
    </div>
    <div class="other-actions row">
      <div class="col-sm-12 text-sm-start mb-4">
        <router-link to="forgot-password" class="forgot-link">
          {{ $t('login.forgot_password') }}
        </router-link>
      </div>
    </div>

    <base-button :loading="isLoading" type="submit" color="theme">{{ $t('login.login') }}</base-button>

    <!-- <div class="social-links">

      <span class="link-text">{{ $t('login.or_signIn_with') }}</span>

      <div class="social-logo">
        <icon-facebook class="icon"/>
        <icon-twitter class="icon"/>
        <icon-google class="icon"/>
      </div>

    </div> -->

  </form>
</template>

<script>
import { mapActions } from 'vuex'
import IconFacebook from '../../components/icon/facebook'
import IconTwitter from '../../components/icon/twitter'
import IconGoogle from '../../components/icon/google'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength } from '@vuelidate/validators'

export default {
  setup () {
    return { v$: useVuelidate() }
  },
  components: {
    IconFacebook,
    IconTwitter,
    IconGoogle
  },
  data () {
    return {
      loginData: {
        email: '',
        password: '',
        remember: ''
      },
      submitted: false,
      customError: '',
      isLoading: false
    }
  },
  validations () {
    return {
      loginData: {
        email: { required, email },
        password: { required, minLength: minLength(8) }
      }
    }
  },
  methods: {
    ...mapActions('auth', [ 'login' ]),
    async validateBeforeSubmit () {
      if (this.isLoading) return
      this.customError = ''
      this.v$.$touch()
      if (this.v$.$invalid) {
        return
      }
      this.isLoading = true
      try {
        await this.login(this.loginData)
        let role = Ls.get('role');
        switch (role) {
            case 'admin':
                return this.$router.push('/invoices/create')
            case 'accountant':
                return this.$router.push('/invoices/create')
            case 'dispatch':
                return this.$router.push('/dispatch/create')
            case 'estimate':
                return this.$router.push('/estimates/create')
            default:
                return this.$router.push('/')
        }
      } catch (err) {
        const response = err.response
        const data = response?.data
        if (!response) {
          this.customError = this.$t('login.connection_error')
        } else if (['invalid_grant', 'invalid_credentials'].includes(data?.error)) {
          this.customError = this.$t('login.invalid_credentials')
        } else if (response.status === 429) {
          this.customError = this.$t('login.too_many_attempts')
        } else if (response.status === 422) {
          const messages = Object.values(data?.errors || {}).flat().filter(message => typeof message === 'string')
          this.customError = messages.join(' ') || data?.message || this.$t('login.failed')
        } else {
          this.customError = response.status < 500 && (data?.error_description || data?.message) || this.$t('login.failed')
        }
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>
