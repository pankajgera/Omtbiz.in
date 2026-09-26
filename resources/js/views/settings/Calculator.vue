<template>
  <div class="calculator-main-container">
 <div class="container mt-5 pt-5">
 <vue-advanced-calculator description="Advanced Calculator" title="Omtbiz Calculator"/>
 </div>
  </div>
</template>
<script>
import {VueAdvancedCalculator} from 'vue-advanced-calculator'
import 'vue-advanced-calculator/dist/vue-advanced-calculator.min.css'
export default {
components: {
  VueAdvancedCalculator
}
}
</script>
