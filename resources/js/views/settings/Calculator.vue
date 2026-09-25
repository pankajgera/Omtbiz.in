<template>
  <div class="calculator-main-container">
 <div class="container mt-5 pt-5">
 <vue-advanced-calculator description="Advance Cacluclator" title="OmtBiz Cacluclator"/>
 </div>
  </div>
</template>
<script>
import {VueAdvancedCalculator} from 'vue-advanced-calculator'
import 'vue-advanced-calculator/dist/vue-advanced-calculator.min.css'
export default {
components: {
  VueAdvancedCalculator
},
  data () {
    return {
    
    }
  },
  mounted() {
    document.getElementById('showModal').style.display="block";
  },
  methods: {
   closeModal() {
    document.getElementById('showModal').style.display="none";
   }
}
}
</script>
