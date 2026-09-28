<style>
    .invoice-preview-actions { width: 210mm; margin: 0 auto 16px; display: flex; justify-content: flex-end; }
    .invoice-preview-actions button { padding: 10px 24px; border: 0; border-radius: 6px; background: #117c75; color: #fff; font: bold 16px "DejaVu Sans", sans-serif; cursor: pointer; }
    .invoice-preview-actions button:hover { background: #0d655f; }
    .invoice-preview-actions button:focus-visible { outline: 3px solid #203c4a; outline-offset: 3px; }
    .invoice-preview-sheet { position: relative; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm; background: #fff; }
    .invoice-preview-page-number { display: none; }
    .invoice-preview-sheet.is-paginated .report-document { display: flex; flex-direction: column; min-height: 273mm; }
    .invoice-preview-sheet.is-paginated .report-document > * { flex-shrink: 0; }
    .invoice-preview-sheet.is-paginated .document-footer { margin-top: auto; }
    .invoice-preview-sheet.is-continuation .report-document { border-top: 0; padding-top: 0; }
    @media screen {
        /* Preserve the A4 layout; narrow screens can scroll instead of reflowing the document. */
        body { margin: 0; padding: 24px 16px; min-width: calc(210mm + 32px); background: #f4f6f8; }
        .invoice-preview-sheet + .invoice-preview-sheet { margin-top: 24px; }
        .invoice-preview-page-number { display: block; position: absolute; right: 12mm; bottom: 6mm; font-size: 10px; color: #536274; }
    }
    @media print {
        .invoice-preview-actions { display: none !important; }
        /* Keep browser-generated titles, URLs, dates and page counts out of the page margins. */
        @page {
            size: A4 portrait;
            margin: 0;
            @bottom-right {
                content: counter(page) "/" counter(pages);
                margin-top: -10mm;
                margin-right: 12mm;
                height: 6mm;
                vertical-align: middle;
                font: 10px "DejaVu Sans", sans-serif;
                color: #536274;
            }
        }
        @page :first { margin: 0; }
        body { margin: 0; padding: 0; background: #fff; }
        /* Repeat the document's own whitespace on each printed page. */
        .invoice-preview-sheet { width: auto; min-height: 0; padding: 12mm; background: transparent; box-shadow: none; -webkit-box-decoration-break: clone; box-decoration-break: clone; }
        .invoice-preview-sheet.is-paginated { width: 210mm; min-height: 297mm; break-after: page; box-decoration-break: slice; -webkit-box-decoration-break: slice; }
        .invoice-preview-sheet.is-paginated:last-of-type { break-after: auto; }
        * { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
</style>
