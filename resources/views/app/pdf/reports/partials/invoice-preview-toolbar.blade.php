<style>
    @media screen {
        body { margin: 24px auto; padding: 0 16px; max-width: calc(210mm + 32px); background: #f4f6f8; }
        .invoice-preview-sheet { width: 100%; min-height: 297mm; padding: 12mm; background: #fff; box-shadow: 0 1px 6px rgb(32 55 70 / 15%); }
    }
    .invoice-preview-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 20px; font-size: 14px; }
    .invoice-preview-toolbar h1 { flex: 1 1 200px; margin: 0; font-size: 24px; }
    .invoice-preview-toolbar a, .invoice-preview-toolbar button { padding: 10px 14px; border: 1px solid #0f766e; border-radius: 6px; background: #fff; color: #0f766e; font: inherit; text-decoration: none; cursor: pointer; }
    .invoice-preview-toolbar button { background: #0f766e; color: #fff; }
    .invoice-preview-toolbar :focus-visible { outline: 3px solid #2563eb; outline-offset: 3px; }
    .invoice-preview-help { font-size: 13px; margin-bottom: 24px; color: #536274; }
    @media screen and (max-width: 700px) {
        body { padding: 0 12px; }
        .invoice-preview-sheet { min-height: auto; padding: 16px; }
        .document-heading, .document-heading > tbody, .document-heading > tbody > tr, .document-heading > tbody > tr > td { display: block; width: 100%; }
        .document-heading .metadata { width: 100%; border-left: 0; padding: 12px 0; }
        .document-heading .identity { width: 100%; padding: 0 0 12px; }
        .metadata th, .metadata td { font-size: 12px; }
        .line-items th, .line-items td { padding: 7px 4px; font-size: 10px; }
        .line-items th { font-size: 9px; padding-left: 2px; padding-right: 2px; white-space: nowrap; }
        .line-items .summary .quantity { font-size: 9px; }
        .declaration td { padding: 10px; }
        .document-notes td + td { padding-left: 12px; }
    }
    @media print {
        .invoice-preview-toolbar, .invoice-preview-help { display: none; }
        body { margin: 0; padding: 0; background: #fff; }
        .invoice-preview-sheet { width: auto; min-height: 0; padding: 0; box-shadow: none; }
        * { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
</style>
<nav class="invoice-preview-toolbar" aria-label="Invoice print controls">
    <h1>Invoice preview</h1>
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ request()->url() }}?download=1" download="invoice.pdf">Download PDF</a>
</nav>
<p class="invoice-preview-help">If your browser does not show a print dialog, download the PDF and print it from your PDF viewer.</p>
