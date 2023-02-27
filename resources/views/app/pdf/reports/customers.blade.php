<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customers Report</title>
    {{-- <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet"> --}}
    <style type="text/css">
        body {
            font-family: "DejaVu Sans";
        }

        /* html {
            margin: 0px;
            padding: 0px;
        } */


        table {
            border-collapse: collapse;
        }

        table tr th {
            text-align: left;
            left: 0;
            border: 1px #eee solid
        }
        table tr.page td {
            text-align: left;
            left: 0;
            border: 1px #eee solid;
            padding: 2px;
        }

        .main-container {
            /* padding: 30px 60px; */
        }

        .sub-container{
            padding: 0px 20px;
        }

        .header {
            width: 100%;
            margin-bottom: 20px
        }

        .heading-text {
            font-style: normal;
            font-weight: 600;
            font-size: 24px;
            color: #f54c4c;
            width: 100%;
            text-align: left;
            padding: 0px;
            margin: 0px;
        }

        .heading-date-range {
            font-style: normal;
            font-weight: 600;
            font-size: 15px;
            color: #595959;
            width: 100%;
            text-align: right;
            padding: 0px;
            margin: 0px;
        }

        .sub-heading-text {
            font-style: normal;
            font-weight: 600;
            font-size: 16px;
            /* line-height: 21px; */
            color: #595959;
            padding: 0px;
            margin: 0px;
            margin-top: 6px;
        }

        .types-title {
            margin-top: 20px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 16px;
            line-height: 21px;
            color: #040405;
        }

        .bank-title {
            margin-top: 60px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 16px;
            line-height: 21px;
            color: #040405;
        }

        .bank-table-container {
            padding-left: 10px;
        }

        .bank-table {
            width: 100%;
            padding-bottom: 5px;
        }

        .bank-title {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            color: #595959;
        }

        .bank-money {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            text-align: right;
            color: #595959;
        }

        .bank-total-table {
            border-top: 1px solid #EAF1FB;
            width: 100%;
        }

        .bank-total-cell {
            padding-right: 20px;
        }

        .bank-total {
            padding-top: 10px;
            padding-right: 30px;
            padding: 0px;
            margin: 0px;
            text-align: right;
            font-style: normal;
            font-weight: 500;
            font-size: 16px;
            line-height: 21px;
            text-align: right;
            color: #040405;
        }

        .total-bank-table {
            width: 100%;
            margin-top: 40px;
            padding: 15px 20px;
            background: #F9FBFF;
            box-sizing: border-box;
        }

        .total-bank-title {
            padding: 0px;
            margin: 0px;
            text-align: left;
            font-style: normal;
            font-weight: 600;
            font-size: 16px;
            line-height: 21px;
            color: #595959;
        }

        .total-bank-money {
            padding: 0px;
            margin: 0px;
            text-align: right;
            font-style: normal;
            font-weight: 500;
            font-size: 20px;
            line-height: 21px;
            color: #f54c4c;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="sub-container">
            <div>
                <p class="heading-text">
                    {{ $ledger->account }}
                    <p class="heading-date-range">
                        {{ $from_date }} - {{ $to_date }}
                    </p>
                </p>
            </div>
            <div class="bank-table-container">
                <table class="bank-table">
                    <tr>
                        <th>
                            <p style="font-size: 14px; font-weight: bold">
                                Date
                            </p>
                        </th>
                        <th>
                            <p style="font-size: 14px; font-weight: bold">
                                Particulars
                            </p>
                        </th>
                        <th>
                            <p style="font-size: 14px; font-weight: bold">
                                Quantity
                            </p>
                        </th>
                        <th>
                            <p style="font-size: 14px; font-weight: bold">
                                Debit
                            </p>
                        </th>
                        <th>
                            <p style="font-size: 14px; font-weight: bold">
                                Credit
                            </p>
                        </th>
                    </tr>
                    @foreach ($related_vouchers as $each)
                        <tr class="page">
                            <td>
                                <p class="bank-title">
                                    {{ \Carbon\Carbon::parse($each->date, 'UTC')->isoFormat('DD/MM/YYYY') }}
                                </p>
                            </td>
                            <td>
                                <p class="bank-title">
                                    {{ $each->account }}
                                </p>
                            </td>
                            <td>
                                <p class="bank-title">
                                    {{ $each->invoice && $each->invoice->inventories ? $each->invoice->inventories->sum('quantity') : 0 }}
                                </p>
                            </td>
                            @if($each->credit > 0)
                            <td>
                                <p class="bank-money">
                                    ₹ {!! ($each->credit) !!}
                                </p>
                            </td>
                            <td></td>
                            @else
                            <td></td>
                            <td>
                                <p class="bank-money">
                                    ₹ {!! ($each->debit) !!}
                                </p>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td>
                            <p class="total-bank-title" style="padding-top: 30px">Opening Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        @if ('Cr' === $ledger->accountMaster->type)
                        <td class="bank-total-cell" style="padding-top: 30px">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ 0.00
                            </p>
                        </td>
                        <td class="bank-total-cell" style="padding-top: 30px">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->accountMaster->opening_balance ? $ledger->accountMaster->opening_balance : 0.00 !!}
                                {!! $ledger->accountMaster->opening_balance ? $ledger->accountMaster->type : '' !!}
                            </p>
                        </td>
                        @else
                        <td class="bank-total-cell" style="padding-top: 30px">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->accountMaster->opening_balance ? $ledger->accountMaster->opening_balance : 0.00 !!}
                                {!! $ledger->accountMaster->opening_balance ? $ledger->accountMaster->type : '' !!}
                            </p>
                        </td>
                        <td class="bank-total-cell" style="padding-top: 30px">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ 0.00
                            </p>
                        </td>
                        @endif
                    </tr>
                    <tr>
                        <td>
                            <p class="total-bank-title">Current Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->debit ? $ledger->debit : 0.00 !!}
                                {!! $ledger->debit ? 'Dr' : '' !!}
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->credit ? $ledger->credit : 0.00 !!}
                                {!! $ledger->credit ? 'Cr' : '' !!}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="total-bank-title">Closing Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        @if ('Cr' === $ledgerType)
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ 0.00
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->balance !!} {!! $ledgerType !!}
                            </p>
                        </td>
                        @else
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ {!! $ledger->balance !!} {!! $ledgerType !!}
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="" style="float:right; font-size: 14px; padding:0px; margin: 0px">
                                ₹ 0.00
                            </p>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
            <br/>
        </div>
    </div>
</body>
</html>
