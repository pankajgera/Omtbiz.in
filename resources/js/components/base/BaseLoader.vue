<template>
  <div class="base-loader" role="status" aria-live="polite">
    <div class="base-loader__indicator">
      <span class="spinner" aria-hidden="true" />
      <span class="base-loader__label">{{ $t('general.loading') }}</span>
    </div>
  </div>
</template>

<script>
export default {}
</script>
