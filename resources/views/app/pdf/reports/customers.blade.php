<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Ledger Report</title>
    <style type="text/css">
        @page {
            size: A4 portrait;
            margin: 92px 34px 58px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
            line-height: 1.4;
            color: #000000;
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-header {
            position: fixed;
            top: -72px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #000000;
        }

        .report-header td {
            vertical-align: bottom;
        }

        .company-name {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 700;
            color: #000000;
        }

        .report-kicker {
            margin: 0;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #000000;
        }

        .header-meta {
            text-align: right;
        }

        .account-name {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 700;
            color: #000000;
        }

        .period {
            margin: 0;
            font-weight: 700;
            color: #000000;
        }

        .report-footer {
            position: fixed;
            right: 0;
            bottom: -38px;
            left: 0;
            height: 24px;
            border-top: 1px solid #555555;
            color: #000000;
            font-size: 9px;
        }

        .report-footer td {
            padding-top: 8px;
        }

        .footer-right {
            text-align: right;
        }

        .page-number:after {
            content: counter(page);
        }

        .report-overview {
            margin: 0 0 14px;
            border: 1px solid #555555;
            background: #ffffff;
        }

        .report-overview td {
            width: 25%;
            padding: 9px 12px;
            border-right: 1px solid #555555;
        }

        .report-overview td:last-child {
            border-right: 0;
        }

        .overview-label {
            display: block;
            margin-bottom: 3px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
        }

        .overview-value {
            font-size: 12px;
            font-weight: 700;
            color: #000000;
        }

        .transactions {
            table-layout: fixed;
        }

        .transactions thead {
            display: table-header-group;
        }

        .transactions th {
            padding: 8px 7px;
            border-top: 1px solid #000000;
            border-bottom: 1.5px solid #000000;
            background: #e6e6e6;
            color: #000000;
            font-size: 9.5px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .transactions td {
            padding: 5px 7px;
            border-bottom: 1px solid #888888;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .transactions tr {
            page-break-inside: avoid;
        }

        .date-column {
            width: 11%;
            white-space: nowrap;
        }

        .particulars-column {
            width: 31%;
        }

        .reference-column {
            width: 23%;
        }

        .quantity-column {
            width: 9%;
            text-align: right !important;
        }

        .amount-column {
            width: 13%;
            text-align: right !important;
            white-space: nowrap;
        }

        .reference {
            font-size: 9.5px;
            color: #000000;
        }

        .amount {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .empty-row td {
            padding: 28px 12px;
            text-align: center;
            font-size: 11px;
            color: #000000;
        }

        .summary-title {
            margin: 18px 0 7px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            color: #000000;
        }

        .summary-context {
            float: right;
            font-size: 9.5px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: #000000;
        }

        .balance-summary {
            page-break-inside: avoid;
            border: 1px solid #000000;
        }

        .balance-summary th,
        .balance-summary td {
            padding: 8px 10px;
            border-bottom: 1px solid #888888;
        }

        .balance-summary th {
            background: #e6e6e6;
            color: #000000;
            font-size: 9.5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .balance-summary td:first-child {
            width: 48%;
            font-weight: 700;
        }

        .balance-summary .money {
            width: 26%;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }

        .balance-summary .closing-row td {
            border-top: 2px solid #000000;
            border-bottom: 0;
            background: #ffffff;
            color: #000000;
            font-size: 11.5px;
        }
    </style>
</head>
<body>
    @php
        $transactionDebit = $related_vouchers->sum('credit');
        $transactionCredit = $related_vouchers->sum('debit');
        $generatedAt = now()->format('d-m-Y H:i');
    @endphp

    <table class="report-header">
        <tr>
            <td>
                <p class="report-kicker">Customer ledger report</p>
                <p class="company-name">{{ $company->name }}</p>
            </td>
            <td class="header-meta">
                <p class="account-name">{{ $ledger->account }}</p>
                <p class="period">{{ $from_date }} to {{ $to_date }}</p>
            </td>
        </tr>
    </table>

    <table class="report-footer">
        <tr>
            <td>Generated {{ $generatedAt }} | {{ $company->name }}</td>
            <td class="footer-right">
                Page <span class="page-number"></span>
            </td>
        </tr>
    </table>

    <main>
        <table class="report-overview">
            <tr>
                <td>
                    <span class="overview-label">Account group</span>
                    <span class="overview-value">{{ optional($ledger->accountMaster)->groups ?: 'Not assigned' }}</span>
                </td>
                <td>
                    <span class="overview-label">Transactions</span>
                    <span class="overview-value">{{ format_inr($related_vouchers->count(), 0) }}</span>
                </td>
                <td>
                    <span class="overview-label">Total quantity</span>
                    <span class="overview-value">{{ format_inr((float) $inventory_sum, 0) }}</span>
                </td>
                <td>
                    <span class="overview-label">Period movement</span>
                    <span class="overview-value">&#8377; {{ format_inr((float) max($transactionDebit, $transactionCredit), 2) }}</span>
                </td>
            </tr>
        </table>

        <table class="transactions">
            <thead>
                <tr>
                    <th class="date-column">Date</th>
                    <th class="particulars-column">Particulars</th>
                    <th class="reference-column">Reference</th>
                    <th class="quantity-column">Quantity</th>
                    <th class="amount-column">Debit</th>
                    <th class="amount-column">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($related_vouchers as $each)
                    @php
                        if ($each->invoice_id) {
                            $reference = optional($each->invoice)->invoice_number ?: 'Invoice';
                        } elseif ($each->receipt_id) {
                            $reference = optional($each->receipt)->receipt_number ?: 'Receipt';
                        } else {
                            $reference = 'Voucher '.$each->id;
                        }

                        $quantity = $each->invoice && $each->invoice->inventories
                            ? $each->invoice->inventories->sum('quantity')
                            : 0;
                    @endphp
                    <tr>
                        <td class="date-column">
                            {{ \Carbon\Carbon::parse($each->date, 'UTC')->format('d-m-Y') }}
                        </td>
                        <td class="particulars-column">{{ $each->account }}</td>
                        <td class="reference-column reference">{{ $reference }}</td>
                        <td class="quantity-column">{{ format_inr((float) $quantity, 0) }}</td>
                        <td class="amount-column amount">&#8377; {{ format_inr((float) ($each->credit ?: 0), 2) }}</td>
                        <td class="amount-column amount">&#8377; {{ format_inr((float) ($each->debit ?: 0), 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6">No transactions were found for the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <section class="summary-section">
            <p class="summary-title">
                Balance summary
                <span class="summary-context">{{ $ledger->account }} | {{ $from_date }} to {{ $to_date }}</span>
            </p>
            <table class="balance-summary">
                <thead>
                    <tr>
                        <th>Balance</th>
                        <th class="money">Debit (Dr)</th>
                        <th class="money">Credit (Cr)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Opening balance</td>
                        <td class="money">&#8377; {{ format_inr((float) ($total_opening_balance_cr ?: 0), 2) }}</td>
                        <td class="money">&#8377; {{ format_inr((float) ($total_opening_balance_dr ?: 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Current period</td>
                        <td class="money">&#8377; {{ format_inr((float) ($current_balance_cr ?: 0), 2) }}</td>
                        <td class="money">&#8377; {{ format_inr((float) ($current_balance_dr ?: 0), 2) }}</td>
                    </tr>
                    <tr class="closing-row">
                        <td>Closing balance</td>
                        <td class="money">&#8377; {{ format_inr((float) ($closing_balance_cr ?: 0), 2) }}</td>
                        <td class="money">&#8377; {{ format_inr((float) ($closing_balance_dr ?: 0), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
