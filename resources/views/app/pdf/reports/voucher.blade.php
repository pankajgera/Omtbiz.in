<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ 'voucher-' . $voucher->id }}</title>
    <style type="text/css">
        body {
            font-family: "DejaVu Sans";
            color: #1f2937;
            font-size: 12px;
        }

        h2 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .meta-row {
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Voucher</h2>
    <div class="meta-row"><strong>Company:</strong> {{ $company->name }}</div>
    <div class="meta-row"><strong>Voucher Group:</strong> {{ $voucher->related_voucher ?: $voucher->id }}</div>
    <div class="meta-row"><strong>Date:</strong> {{ \Carbon\Carbon::parse($voucher->date)->format('d/m/Y') }}</div>
    <div class="meta-row"><strong>Narration:</strong> {{ $voucher->short_narration ?: '-' }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Account</th>
                <th>Group</th>
                <th>Type</th>
                <th class="right">Debit</th>
                <th class="right">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vouchers as $index => $each)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $each->account }}</td>
                    <td>{{ optional($each->accountMaster)->groups }}</td>
                    <td>{{ $each->type }}</td>
                    <td class="right">{{ number_format((float) $each->debit, 2) }}</td>
                    <td class="right">{{ number_format((float) $each->credit, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="right"><strong>Total</strong></td>
                <td class="right"><strong>{{ number_format((float) $total_debit, 2) }}</strong></td>
                <td class="right"><strong>{{ number_format((float) $total_credit, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
