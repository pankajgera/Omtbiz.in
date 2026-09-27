<style>
    /* Reserve room for repeated column headings on continuation pages. */
    @page { size: A4 portrait; margin: 22mm 12mm 12mm; }
    @page :first { margin-top: 12mm; }
    body { margin: 0; color: #203746; background: #fff; font-family: "DejaVu Sans", sans-serif; font-size: 11px; line-height: 1.5; }
    * { box-sizing: border-box; }
    table { width: 100%; border-collapse: collapse; }
    p { margin: 0; }
    th, td { vertical-align: top; overflow-wrap: break-word; }
    .report-document { border-top: 3px solid #203746; padding-top: 22px; }
    .document-heading { margin-bottom: 24px; border-bottom: 1px solid #587783; }
    .document-heading > tbody > tr > td { padding: 0 0 22px; }
    .document-heading .identity { width: 53%; padding-right: 24px; }
    .document-heading h1 { font-size: 30px; line-height: 1.2; margin: 0 0 20px; color: #183448; }
    .party-label { color: #536274; margin-bottom: 4px; }
    .party-name { font-weight: bold; font-size: 17px; line-height: 1.35; }
    .document-heading .metadata { border-left: 1px solid #b6c3cd; padding-left: 18px; width: 47%; }
    .metadata th { text-align: left; color: #536274; font-weight: normal; width: 44%; }
    .metadata td { text-align: right; }
    .metadata th, .metadata td { padding: 5px 0; font-size: 11px; }
    .line-items { table-layout: fixed; }
    .line-items thead { display: table-header-group; }
    .line-items th, .line-items td { border: 1px solid #acbbc7; padding: 9px 10px; }
    .line-items th { background: #edf1f5; color: #183448; text-align: left; font-weight: bold; }
    .line-items tr { page-break-inside: avoid; }
    .line-items .number { text-align: right; }
    .line-items .quantity { text-align: center; }
    .line-items .summary td { padding-top: 10px; padding-bottom: 10px; }
    .line-items .summary td p { margin-bottom: 6px; }
    .line-items .summary .grand-total { background: #e6f3f3; font-weight: bold; }
    .totals-breakdown { table-layout: fixed; }
    .totals-breakdown td { width: 50%; }
    .document-notes { margin-top: 20px; margin-bottom: 22px; table-layout: fixed; }
    .document-notes td { padding: 0 14px 0 0; width: 50%; }
    .document-notes td + td { border-left: 1px solid #acbbc7; padding-left: 20px; padding-right: 0; }
    .document-notes .label { color: #536274; margin-bottom: 4px; }
    .amount-words { font-weight: bold; }
    .declaration { page-break-inside: avoid; }
    .declaration td { border: 1px solid #acbbc7; padding: 14px; }
    .declaration .signature { width: 36%; text-align: right; }
    .declaration .label { color: #536274; margin-bottom: 5px; }
    .document-footnote { margin-top: 10px; font-size: 9px; color: #536274; }
    /* The summary report family uses the same palette and table rhythm. */
    .summary-report { border-top: 3px solid #203746; padding-top: 22px; }
    .summary-report .sub-container { padding: 0; }
    .summary-report .header { border-bottom: 1px solid #587783; margin-bottom: 22px; }
    .summary-report .header td { padding: 0 0 14px; }
    .summary-report .heading-text { font-size: 26px; font-weight: bold; color: #183448; }
    .summary-report .heading-date-range { font-size: 10px; text-align: right; color: #536274; }
    .summary-report .sub-heading-text { font-size: 17px; font-weight: bold; color: #183448; margin-top: 10px; }
    .summary-report .expenses-title { margin: 22px 0 0; padding: 10px; background: #edf1f5; border: 1px solid #acbbc7; font-weight: bold; }
    .summary-report .expenses-table-container, .summary-report .bank-table-container { padding: 0; }
    .summary-report .expenses-table td, .summary-report .bank-table td { padding: 10px; border: 1px solid #acbbc7; }
    .summary-report tr { page-break-inside: avoid; }
    .summary-report .expense-money, .summary-report .bank-money, .summary-report .income-money,
    .summary-report .expense-total, .summary-report .profit-money, .summary-report .total-expense-money { text-align: right; }
    .summary-report .expense-total-table { margin-top: 12px; }
    .summary-report .expense-total-cell { padding: 10px; font-weight: bold; }
    .summary-report .profit-table, .summary-report .total-expense-table, .summary-report .bank-total-table, .summary-report .income-table { margin: 22px 0; background: #e6f3f3; border: 1px solid #acbbc7; page-break-inside: avoid; }
    .summary-report .profit-table td, .summary-report .total-expense-table td, .summary-report .bank-total-table td, .summary-report .income-table td { padding: 12px 10px; font-weight: bold; }
    .summary-report .profit-money, .summary-report .total-expense-money { font-size: 16px; }
    .summary-report .bank-table td:last-child { width: 24%; }
</style>
