<template>
  <div class="items main-content">
    <div class="page-header">
      <h3 class="page-title">{{ $t('credits.title') }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="/">
            {{ $t('general.home') }}
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="/masters">
            Ledgers
          </router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link
            slot="item-title"
            to="#">
            {{ $tc('credits.title', 2) }}
          </router-link>
        </li>
      </ol>
      <div class="page-actions row">
        <div class="col-xs-2 mr-4">
          <base-button
            v-show="totalCustomers || filtersApplied"
            :outline="true"
            :icon="filterIcon"
            size="large"
            color="theme"
            right-icon
            @click="toggleFilter"
          >
            {{ $t('general.filter') }}
          </base-button>
        </div>
        <router-link slot="item-title" class="col-xs-2" to="customers/create">
          <base-button
            size="large"
            icon="plus"
            color="theme">
            {{ $t('customers.new_customer') }}
          </base-button>
        </router-link>
      </div>
    </div>
    <!-- Credits table -->
    <div v-show="!showEmptyScreen" class="table-container">
      <table-component :data="credits" :show-filter="false" table-class="table">
        <table-column label="ID" show="id"></table-column>
        <table-column label="Account Ledger ID" show="account_ledger_id"></table-column>
        <table-column label="Created At" show="created_at"></table-column>
        <table-column label="Credits" show="credits"></table-column>
        <table-column label="Credits Date" show="credits_date"></table-column>
        <table-column label="ID" show="id"></table-column>
        <table-column label="Updated At" show="updated_at"></table-column>
      </table-component>
    </div>


  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
export default {
  data() {
    return {
      credits: [],
    };
  },
  computed: {
    showEmptyScreen() {
      return this.credits == []
    },
  },
  methods: {
    ...mapActions('credits', [
      'fetchCredits',
    ]),
    async getDebtorCredits() {
      try {
        const response = await this.fetchCredits(this.$route.params.id);
        this.credits = response.data;
      } catch (error) {
        console.error('Error fetching debtor credits:', error);
      }
    },

  },
  mounted() {
    this.getDebtorCredits()
  }
};
</script>

<style>
/* Your styles here */
</style>
