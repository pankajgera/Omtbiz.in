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
            border: 1px #797979 solid
        }
        table tr.page td {
            text-align: left;
            left: 0;
            border: 1px #797979 solid;
            padding: 2px;
        }

        .table-column {
            font-size: 12px; font-weight: bold
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
            color: #000;
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
            color: #000;
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
            color: #000;
        }

        .bank-title {
            margin-top: 60px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 16px;
            line-height: 21px;
            color: #000;
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
            font-size: 12px;
            line-height: 21px;
            color: #000;
        }

        .bank-money {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: bold;
            font-size: 12px;
            line-height: 21px;
            text-align: right;
            color: #000;
        }

        .bank-total-table {
            border-top: 1px solid #858585;
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
            color: #000;
        }

        .total-bank-table {
            width: 100%;
            margin-top: 40px;
            padding: 15px 20px;
            background: #fff;
            box-sizing: border-box;
        }

        .total-bank-title {
            padding: 0px;
            margin: 0px;
            text-align: left;
            font-style: normal;
            font-weight: 600;
            font-size: 12px;
            line-height: 21px;
            color: #000;
        }

        .footer-total-amount {
            float: right;
            font-size: 12px;
            padding:0px;
            margin: 0px;
            font-weight: bold;
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
                            <p class="table-column">
                                Date
                            </p>
                        </th>
                        <th>
                            <p class="table-column">
                                Particulars
                            </p>
                        </th>
                        <th>
                            <p class="table-column">
                                Quantity
                            </p>
                        </th>
                        <th>
                            <p class="table-column">
                                Debit
                            </p>
                        </th>
                        <th>
                            <p class="table-column">
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
                            <td>
                                <p class="bank-money">
                                    ₹ {!! ($each->credit ? $each->credit : 0.00) !!}
                                </p>
                            </td>
                            <td>
                                <p class="bank-money">
                                    ₹ {!! ($each->debit ? $each->debit : 0.00) !!}
                                </p>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>
                            <p class="total-bank-title" style="padding-top: 30px">Total Quantity</p>
                        </td>
                        <td></td>
                        <td class="bank-total-cell" style="padding-top: 30px">
                            <p class="footer-total-amount" style="padding-top: 10px; float: left">
                                {!! $inventory_sum !!}
                            </p>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <p class="total-bank-title">Opening Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $total_opening_balance_cr ?: 0.00 !!}  Dr
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $total_opening_balance_dr ?: 0.00 !!}  Cr
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="total-bank-title">Current Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $current_balance_cr ?: 0.00 !!}  Dr
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $current_balance_dr ?: 0.00 !!}  Cr
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="total-bank-title">Closing Balance</p>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $closing_balance_cr ?: 0.00 !!}  Dr
                            </p>
                        </td>
                        <td class="bank-total-cell">
                            <p class="footer-total-amount">
                                ₹ {!! $closing_balance_dr ?: 0.00 !!}  Cr
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            <br/>
        </div>
    </div>
</body>
</html>
