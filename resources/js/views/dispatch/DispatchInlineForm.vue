<template>
  <div class="card dispatch-inline-form">
    <div class="dispatch-inline-form__header">
      <h5 class="dispatch-inline-form__title">
        {{ $t('dispatch.dispatch') }}
        <span class="dispatch-inline-form__count">{{ ids.length }}</span>
      </h5>
      <button
        type="button"
        class="dispatch-inline-form__close"
        :aria-label="$t('general.cancel')"
        @click="$emit('cancel')"
      >
        <font-awesome-icon :icon="['fas', 'times']" />
      </button>
    </div>

    <div v-if="isLoadingData" class="dispatch-inline-form__loading">
      <base-loader />
    </div>

    <form v-else action="" @submit.prevent="submitDispatch">
      <div class="card-body">
        <!-- Same field set, in the same order, as the full-page dispatch form.
             The invoice picker is read-only here for the same reason it is
             disabled there under `isEdit`: which invoices belong to a pending
             dispatch is decided when the dispatch is created, not when it's
             sent. -->
        <div class="form-group" v-if="invoice.length">
          <label class="form-label">{{ $t('receipts.invoice') }}</label>
          <base-select
            v-model="invoice"
            :multiple="true"
            :show-pointer="false"
            :options="invoiceList"
            :searchable="false"
            :show-labels="false"
            :allow-empty="true"
            :disabled="true"
            :custom-label="invoiceWithAmount"
            track-by="id"
            class="multi-select-item"
          />
        </div>

        <div class="dispatch-inline-form__grid">
          <div class="form-group">
            <label class="control-label">{{ $t('dispatch.date_time') }}</label><span class="text-danger"> *</span>
            <base-date-picker
              v-model="formData.date_time"
              format="Y-m-d"
              :invalid="$v.formData.date_time.$error"
              :calendar-button="true"
              calendar-button-icon="calendar"
              @change="$v.formData.date_time.$touch()"
            />
          </div>

          <div class="form-group">
            <label class="control-label">{{ $t('dispatch.time') }}</label><span class="text-danger"> *</span>
            <div class="base-date-input">
              <vue-timepicker
                v-model="formData.time"
                format="hh:mm A"
                :hide-clear-button="true"
                @change="$v.formData.time.$touch()"
              >
                <template v-slot:icon>
                  <span class="vdp-datepicker__calendar-button input-group-prepend">
                    <span>
                      <font-awesome-icon :icon="['fas', 'clock']" />
                    </span>
                  </span>
                </template>
              </vue-timepicker>
            </div>
          </div>

          <div class="form-group">
            <label class="control-label">{{ $t('dispatch.person') }}</label>
            <base-input
              v-model.trim="formData.person"
              type="text"
              name="person"
            />
          </div>

          <div class="form-group">
            <label class="control-label">{{ $t('dispatch.transport') }}</label>
            <base-input
              v-model.trim="formData.transport"
              type="text"
              name="transport"
            />
          </div>
        </div>

        <div class="dispatch-inline-form__actions">
          <base-button
            :loading="isLoading"
            :disabled="isLoading"
            icon="save"
            color="theme"
            type="submit"
          >
            {{ $t('dispatch.dispatch') }}
          </base-button>
          <base-button
            :outline="true"
            color="theme"
            type="button"
            @click="$emit('cancel')"
          >
            {{ $t('general.cancel') }}
          </base-button>
        </div>
      </div>
    </form>
  </div>
</template>
<style src="../../../css/vue-timepicker-theme.css"></style>
<script>
import { validationMixin } from 'vuelidate'
import { mapActions } from 'vuex'
import { required } from '@vuelidate/validators'
import VueTimepicker from 'vue3-timepicker'
import 'vue3-timepicker/dist/VueTimepicker.css'

// The dispatch form from views/dispatch/Create.vue, rendered inline on the
// pending list instead of as its own page. Same store actions and same payload
// shape - the point is only that marking a pending dispatch as sent no longer
// costs a navigation away from the list (or, as `singleDispatch` used to do, a
// pop-up window).
export default {
  components: { VueTimepicker },
  mixins: [validationMixin],
  props: {
    // Dispatch ids to send. One for a row action, many when rows are ticked.
    ids: {
      type: Array,
      required: true
    }
  },
  emits: ['cancel', 'saved'],
  data () {
    return {
      isLoading: false,
      isLoadingData: true,
      invoice: [],
      invoiceList: [],
      formData: {
        invoice_id: [],
        date_time: new Date(),
        transport: '',
        person: '',
        time: '',
        status: {
          id: 2,
          name: 'Sent',
        },
        all_selected_dispatch: []
      }
    }
  },
  watch: {
    // Re-loads when the caller switches which rows it's dispatching without
    // closing the panel in between.
    ids: {
      handler: 'loadDispatch',
      immediate: true
    }
  },
  validations: {
    formData: {
      date_time: {
        required,
      },
      time: {
        required,
      },
    }
  },
  methods: {
    ...mapActions('dispatch', [
      'editToBeDispatch',
      'updateToBeDispatch',
    ]),
    invoiceWithAmount ({ invoice_number, due_amount, master }) {
      // master can be null for an invoice whose account_master_id points at a
      // deleted/missing party - don't let that crash the whole picker.
      let count = master ? this.invoice.filter(i => i && i.account_master_id === master.id).length : 0
      let masterName = master ? master.name : this.$t('dispatch.unknown_party')
      return `${invoice_number} (₹ ${parseFloat(due_amount).toFixed(2)}) - (${masterName}) * ${count}`
    },
    // The pending rows carry their invoice ids, but not the invoice records
    // themselves, and /api/dispatch/invoices only returns the newest 50 still
    // pending by default - so ask for these ids specifically or the picker
    // renders empty for anything outside that window.
    async fetchInvoices (invoiceIds) {
      let wanted = (invoiceIds || []).map(i => parseInt(i)).filter(id => !isNaN(id))
      if (! wanted.length) {
        this.invoiceList = []
        return
      }
      let response = await axios.get(`/api/dispatch/invoices`, {
        params: { include_ids: wanted.join(',') }
      })
      this.invoiceList = (response.data && response.data.invoices) || []
    },
    async loadDispatch () {
      if (! this.ids.length) {
        return
      }
      this.isLoadingData = true
      try {
        let response = await this.editToBeDispatch(this.ids.toString())
        let rows = (response.data && response.data.dispatch) || []
        if (! rows.length) {
          window.toastr['error'](this.$t('dispatch.no_dispatch'))
          this.$emit('cancel')
          return
        }

        // Person/transport/date come off the first row, matching how the
        // full-page form seeds itself; every selected row is then written
        // with whatever is submitted here.
        this.formData = {
          ...rows[0],
          status: {
            id: 2,
            name: 'Sent',
          },
          invoice_id: rows.flatMap(each => each.invoice_id || []),
          all_selected_dispatch: rows.map(each => each.id),
        }

        await this.fetchInvoices(this.formData.invoice_id)
        this.invoice = this.formData.invoice_id
          .map(i => this.invoiceList.find(j => j.id === parseInt(i)))
          .filter(Boolean)

        // Default the clock to now rather than to whenever the row was
        // created - this form is used at the moment goods actually leave.
        let current = new Date()
        this.formData.time = current.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
        })
      } catch (err) {
        window.toastr['error'](err)
        this.$emit('cancel')
      } finally {
        this.isLoadingData = false
      }
    },
    async submitDispatch () {
      this.$v.formData.$touch()
      // Explicit rather than leaning on $v.$invalid alone: the date and time
      // are what the controller feeds to Carbon::createFromFormat, and an
      // empty one there is a 500 ("Not enough data available to satisfy
      // format"), not a validation message.
      if (this.$v.$invalid || ! this.formData.date_time || ! this.formData.time) {
        window.toastr['error']("Error! missing required field or value is invalid.!")
        return false
      }
      try {
        this.isLoading = true
        let response = await this.updateToBeDispatch(this.formData)
        if (response.data) {
          window.toastr['success'](this.$tc('dispatch.updated_message'))
          this.$emit('saved')
        }
      } catch (err) {
        if (err) {
          window.toastr['error'](err)
        }
      } finally {
        this.isLoading = false
      }
    },
  }
}
</script>
