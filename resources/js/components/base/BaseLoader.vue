<template>
  <div class="base-loader" role="status" aria-live="polite" aria-label="Loading">
    <div class="spinner" aria-hidden="true"/>
  </div>
</template>
<script>
export default {

}
</script>

<style scoped>
.overlay {
    height: 100%;
    width: 100%;
    background: rgba(255,255,255,0.4);
    position: absolute;
    top: 7%;
    left: 10%;
}
</style>
