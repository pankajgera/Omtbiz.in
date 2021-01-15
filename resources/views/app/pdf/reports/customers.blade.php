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

        .main-container {
            /* padding: 30px 60px; */
        }

        .sub-container{
            padding: 0px 20px;
        }

        .header {
            width: 100%;
            margin-bottom: 60px
        }

        .heading-text {
            font-style: normal;
            font-weight: 600;
            font-size: 24px;
            color: #5851D8;
            width: 100%;
            text-align: left;
            padding: 0px;
            margin: 0px;
        }

        .heading-date-range {
            font-style: normal;
            font-weight: 600;
            font-size: 15px;
            color: #A5ACC1;
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

        .tax-title {
            margin-top: 60px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 16px;
            line-height: 21px;
            color: #040405;
        }

        .tax-table-container {
            padding-left: 10px;
        }

        .tax-table {
            width: 100%;
            padding-bottom: 10px;
        }

        .tax-title {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            color: #595959;
        }

        .tax-money {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            text-align: right;
            color: #595959;
        }

        .tax-total-table {
            border-top: 1px solid #EAF1FB;
            width: 100%;
        }

        .tax-total-cell {
            padding-right: 20px;
        }

        .tax-total {
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

        .total-tax-table {
            width: 100%;
            margin-top: 40px;
            padding: 15px 20px;
            background: #F9FBFF;
            box-sizing: border-box;
        }

        .total-tax-title {
            padding: 0px;
            margin: 0px;
            text-align: left;
            font-style: normal;
            font-weight: 600;
            font-size: 16px;
            line-height: 21px;
            color: #595959;
        }

        .total-tax-money {
            padding: 0px;
            margin: 0px;
            text-align: right;
            font-style: normal;
            font-weight: 500;
            font-size: 20px;
            line-height: 21px;
            color: #5851D8;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="sub-container">
            <table class="header">
                <tr>
                    <td>
                        <p class="heading-text">
                            {{ $company->name }}
                        </p>
                    </td>
                    <td>
                        <p class="heading-date-range">
                            {{ $from_date }} - {{ $to_date }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="sub-heading-text"> REPORT</p>
                    </td>
                </tr>
            </table>
            <div class="tax-table-container">
                <table class="tax-table">
                    @foreach ($related_vouchers as $each)
                        <tr>
                            <td>
                                <p class="tax-title">
                                    {{ \Carbon\Carbon::parse($each->date, 'UTC')->isoFormat('DD/MM/YYYY') }}
                                </p>
                            </td>
                            <td>
                                <p class="tax-title">
                                    {{ $each->account }}
                                </p>
                            </td>
                            <td>
                                <p class="tax-money">
                                    ₹ {!! ($each->debit > 0 ? $each->debit : $each->credit) !!}
                                    {!! ($each->debit > 0 ? ' Dr' : ' Cr') !!}
                                </p>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <table class="tax-total-table">
            <tr>
                <td>
                    <p class="total-tax-title">OPENING BALANCE</p>
                </td>
                <td class="tax-total-cell">
                    <p class="" style="float:right; padding:0px; margin: 0px">
                        ₹ {!! $opening_balance ? $opening_balance : 0.00 !!} {!! $opening_balance ? $opening_balance_type : '' !!}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="total-tax-title">CLOSING BALANCE</p>
                </td>
                <td class="tax-total-cell">
                    <p class="" style="float:right; padding:0px; margin: 0px">
                        ₹ {!! $totalAmount !!} {!! $ledger->type !!}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
