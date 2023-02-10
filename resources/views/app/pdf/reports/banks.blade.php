<!DOCTYPE html>
<html lang="en">
<head>
    <title>Banks Report</title>
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
            padding-bottom: 10px;
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
            <table class="bank-total-table" style="margin-top: 20px">
                <tr>
                    <td>
                        <p class="total-bank-title">OPENING BALANCE</p>
                    </td>
                    <td class="bank-total-cell">
                        <p class="" style="float:right; padding:0px; margin: 0px">
                            ₹ {!! $opening_balance ? $opening_balance : 0.00 !!}
                            {!! $master_type !!}
                        </p>
                    </td>
                </tr>
            </table>
            <div class="bank-table-container">
                <table class="bank-table">
                    @foreach ($related_vouchers as $key => $vouchers)
                        <div style="margin: 20px 0;">
                            @foreach($vouchers as $j => $each)
                                @if(isset($each['id']))
                                    <tr>
                                        <td>
                                            <p class="bank-title">
                                                {{ \Carbon\Carbon::parse($each['date'], 'UTC')->isoFormat('DD/MM/YYYY') }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="bank-title">
                                                {{ $each['account'] }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="bank-money">
                                                ₹ {!! ($each['debit'] > 0 ? $each['debit'] : $each['credit']) !!}
                                                {!! ($each['debit'] > 0 ? ' Dr' : ' Cr') !!}
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                    <table class="bank-total-table">
                        <tr>
                            <td>
                                <p class="total-bank-title">CLOSING BALANCE</p>
                            </td>
                            <td class="bank-total-cell">
                                <p class="" style="float:right; padding:0px; margin: 0px">
                                    ₹ {!! $credit_debit_sum !!} {!! $credit_debit_type !!}
                                </p>
                            </td>
                        </tr>
                    </table>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
