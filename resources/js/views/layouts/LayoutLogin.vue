<template>
  <div class="login-page login-3">
    <div class="site-wrapper">
      <div class="login-box">
        <div class="box-wrapper">
          <div class="logo-main">
            <img
                src="/assets/img/bill.png"
                alt="Omtbiz Logo">
          </div>
          <router-view></router-view>
        </div>
      </div>
      <div class="content-box">
        <h1>{{ $t('layout_login.super_simple_invoicing') }}<br>
          {{ $t('layout_login.for_freelancer') }}<br>
          {{ $t('layout_login.small_businesses') }} <br>
        </h1>

        <p>
          {{ $t('layout_login.omtbiz_help') }}<br>
        </p>

        <!-- <div class="content-bottom"/> -->
        <div class="page-copyright">
            <p>{{ $t('layout_login.copyright_omtbiz') }}</p>
          </div>
      </div>
    </div>
  </div>
</template>

<script type="text/babel">
export default {
  watch: {
    $route: 'onRouteChange'
  },
  mounted () {
    this.setLayoutClasses()
  },
  methods: {
    setLayoutClasses () {
      let body = $('body')
      body.removeClass()
      body.addClass('login-page login-1 skin-omtbiz')
    }
  },
  onRouteChange () {
    $('body').removeClass('login-page')
  }
}
</script>
