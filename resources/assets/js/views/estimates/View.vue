<template>
  <div v-if="estimate" class="main-content estimate-view-page">
    <div class="page-header">
      <h3 class="page-title"> {{ estimate.estimate_number }}</h3>
      <div class="page-actions row">
        <v-dropdown :close-on-select="false" align="left" class="filter-container">
          <span slot="activator" href="#">
            <base-button color="theme">
              <font-awesome-icon icon="ellipsis-h" />
            </base-button>
          </span>
          <v-dropdown-item>
            <router-link :to="{path: `/estimates/${$route.params.id}/edit`}" class="dropdown-item">
              <font-awesome-icon :icon="['fas', 'pencil-alt']" class="dropdown-item-icon"/>
              {{ $t('general.edit') }}
            </router-link>
            <div class="dropdown-item" @click="removeEstimate($route.params.id)">
              <font-awesome-icon :icon="['fas', 'trash']" class="dropdown-item-icon" />
              {{ $t('general.delete') }}
            </div>
          </v-dropdown-item>
        </v-dropdown>
      </div>
    </div>
    <div class="estimate-sidebar">
      <base-loader v-if="isSearching" />
      <div v-else class="side-header">
        <base-input
          v-model="searchData.searchText"
          :placeholder="$t('general.search')"
          input-class="inv-search"
          icon="search"
          type="text"
          align-icon="right"
          @input="onSearched()"
        />
        <div
          class="btn-group ml-3"
          role="group"
          aria-label="First group"
        >
          <v-dropdown :close-on-select="false" align="left" class="filter-container">
            <span slot="activator" href="#">
              <base-button class="inv-button inv-filter-fields-btn" color="default" size="medium">
                <font-awesome-icon icon="filter" />
              </base-button>
            </span>

            <div class="filter-items">
              <input
                id="filter_estimate_date"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="estimate_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_estimate_date">{{ $t('reports.estimates.estimate_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_due_date"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="expiry_date"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_due_date">{{ $t('estimates.due_date') }}</label>
            </div>
            <div class="filter-items">
              <input
                id="filter_estimate_number"
                v-model="searchData.orderByField"
                type="radio"
                name="filter"
                class="inv-radio"
                value="estimate_number"
                @change="onSearched"
              >
              <label class="inv-label" for="filter_estimate_number">{{ $t('estimates.estimate_number') }}</label>
            </div>
          </v-dropdown>
          <base-button class="inv-button inv-filter-sorting-btn" color="default" size="medium" @click="sortData">
            <font-awesome-icon v-if="getOrderBy" icon="sort-amount-up" />
            <font-awesome-icon v-else icon="sort-amount-down" />
          </base-button>
        </div>
      </div>
      <!-- <div class="side-content">
        <router-link
          v-for="(estimate,index) in estimates"
          :to="`/estimates/${estimate.id}/view`"
          :key="index"
          class="side-estimate"
        >
          <div class="left">
            <div class="inv-name">{{ estimate.user.name }}</div>
            <div class="inv-number">{{ estimate.estimate_number }}</div>
          </div>
          <div class="right">
            <div class="inv-amount" v-html="$utils.formatMoney(estimate.total, estimate.user.currency)" />
            <div class="inv-date">{{ estimate.formattedEstimateDate }}</div>
          </div>
        </router-link>
        <p v-if="!estimates.length" class="no-result">
          {{ $t('estimates.no_matching_estimates') }}
        </p>
      </div> -->
    </div>
    <div class="estimate-view-page-container">
      <iframe :src="`${shareableLink}`" class="frame-style"/>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
const _ = require('lodash')

export default {
  data () {
    return {
      id: null,
      count: null,
      estimatesDraft: [],
      estimatesSent: [],
      estimate: null,
      currency: null,
      searchData: {
        orderBy: null,
        orderByField: null,
        searchText: null
      },
      isSendingEmail: false,
      isRequestOnGoing: false,
      isSearching: false
    }
  },
  computed: {
    getOrderBy () {
      if (this.searchData.orderBy === 'asc' || this.searchData.orderBy == null) {
        return true
      }
      return false
    },

    shareableLink () {
      return `/estimates/pdf/${this.estimate.unique_hash}`
    }
  },
  watch: {
    $route (to, from) {
      this.loadEstimate()
    }
  },
  created () {
    this.loadEstimates()
    this.loadEstimate()
    this.onSearched = _.debounce(this.onSearched, 500)
  },
  methods: {
    ...mapActions('estimate', [
      'fetchEstimates',
      'getRecord',
      'searchEstimate',
      'sendEmail',
      'deleteEstimate',
      'selectEstimate',
      'fetchViewEstimate'
    ]),
    async loadEstimates () {
      let response = await this.fetchEstimates()
      if (response.data) {
        this.estimatesDraft = response.data.estimates_draft.data
        this.estimatesSent = response.data.estimates_sent.data
      }
    },
    async loadEstimate () {
      let response = await this.fetchViewEstimate(this.$route.params.id)

      if (response.data) {
        this.estimate = response.data.estimate
      }
    },
    async onSearched () {
      let data = ''
      if (this.searchData.searchText !== '' && this.searchData.searchText !== null && this.searchData.searchText !== undefined) {
        data += `search=${this.searchData.searchText}&`
      }

      if (this.searchData.orderBy !== null && this.searchData.orderBy !== undefined) {
        data += `orderBy=${this.searchData.orderBy}&`
      }

      if (this.searchData.orderByField !== null && this.searchData.orderByField !== undefined) {
        data += `orderByField=${this.searchData.orderByField}`
      }
      this.isSearching = true
      let response = await this.searchEstimate(data)
      this.isSearching = false
      if (response.data) {
        this.estimatesDraft = response.data.estimates_draft.data
        this.estimatesSent = response.data.estimates_sent.data
      }
    },
    sortData () {
      if (this.searchData.orderBy === 'asc') {
        this.searchData.orderBy = 'desc'
        this.onSearched()
        return true
      }
      this.searchData.orderBy = 'asc'
      this.onSearched()
      return true
    },
    async removeEstimate (id) {
      window.swal({
        title: 'Deleted',
        text: 'you will not be able to recover this estimate!',
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true
      }).then(async (value) => {
        if (value) {
          let request = await this.deleteEstimate(id)
          if (request.data.success) {
            window.toastr['success'](this.$tc('estimates.deleted_message', 1))
            this.$router.push('/estimates')
          } else if (request.data.error) {
            window.toastr['error'](request.data.message)
          }
        }
      })
    }
  }
}
</script>
