<template>
  <dialog ref="dialog" class="dispatch-print-preview" aria-labelledby="dispatch-preview-title" @close="$emit('close')">
    <header class="preview-toolbar">
      <h3 id="dispatch-preview-title">{{ $t('dispatch.print_preview') }}</h3>
      <div class="preview-actions">
        <button type="button" class="btn btn-primary" @click="print">{{ $t('dispatch.print_dispatch') }}</button>
        <button type="button" class="btn preview-close" @click="$refs.dialog.close()">{{ $t('dispatch.close_preview') }}</button>
      </div>
    </header>
    <p v-if="printError" role="alert">{{ $t('dispatch.print_error') }}</p>
    <div ref="document" class="preview-document">
      <h2>{{ $t('dispatch.dispatch') }}</h2>
      <dl>
        <dt>{{ $t('dispatch.date_time') }}</dt><dd>{{ date }}</dd>
        <dt>{{ $t('dispatch.time') }}</dt><dd>{{ dispatch.time || '—' }}</dd>
        <dt>{{ $t('dispatch.person') }}</dt><dd>{{ dispatch.person || '—' }}</dd>
        <dt>{{ $t('dispatch.transport') }}</dt><dd>{{ dispatch.transport || '—' }}</dd>
      </dl>
      <table>
        <thead><tr><th>{{ $t('dispatch.invoice_id') }}</th><th>{{ $t('dispatch.name') }}</th></tr></thead>
        <tbody>
          <tr v-for="invoice in invoices" :key="invoice.id">
            <td>{{ invoice.invoice_number }}</td>
            <td>{{ invoice.master ? invoice.master.name : $t('dispatch.unknown_party') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </dialog>
</template>

<script>
import moment from 'moment'
import printJS from 'print-js'

export default {
  props: {
    dispatch: { type: Object, required: true },
    invoices: { type: Array, default: () => [] }
  },
  emits: ['close'],
  data: () => ({ printError: false }),
  computed: {
    date () {
      return this.dispatch.date_time ? moment(this.dispatch.date_time).format('DD-MM-YYYY') : '—'
    }
  },
  methods: {
    open () {
      this.printError = false
      this.$refs.dialog.showModal()
    },
    print () {
      this.printError = false
      try {
        printJS({
          printable: this.$refs.document,
          type: 'html',
          scanStyles: false,
          documentTitle: this.$t('dispatch.dispatch'),
          style: 'body { color: #000; background: #fff; font: 14px sans-serif; padding: 24px; } dl { display: grid; grid-template-columns: 120px 1fr; gap: 8px; } dd { margin: 0; } table { width: 100%; border-collapse: collapse; } th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }',
          onError: () => { this.printError = true }
        })
      } catch {
        this.printError = true
      }
    }
  }
}
</script>

<style scoped>
.dispatch-print-preview {
  width: min(800px, calc(100vw - 32px));
  max-height: calc(100dvh - 32px);
  margin: auto;
  padding: 24px;
  border: 1px solid var(--ui-border);
  border-radius: 8px;
  color: var(--ui-text);
  background: var(--ui-surface);
}
.dispatch-print-preview::backdrop { background: rgb(0 0 0 / 50%); }
.preview-toolbar, .preview-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.preview-toolbar { justify-content: space-between; margin-bottom: 24px; }
.preview-toolbar h3 { margin: 0; }
.preview-close { color: var(--ui-text) !important; border: 1px solid var(--ui-border); background: var(--ui-surface); }
.preview-document { background: #fff; color: #000; padding: 20px; overflow-x: auto; }
.preview-document h2 { color: #000 !important; }
.preview-document dl { display: grid; grid-template-columns: 100px minmax(0, 1fr); gap: 8px; }
.preview-document dd { margin: 0; overflow-wrap: anywhere; }
.preview-document table { width: 100%; border-collapse: collapse; }
.preview-document th, .preview-document td { padding: 10px; border: 1px solid #ccc; text-align: left; overflow-wrap: anywhere; }
@media (max-width: 600px) { .dispatch-print-preview, .preview-document { padding: 12px; } }
</style>
