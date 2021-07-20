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

        .sub-container {
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

        .title {
            margin-top: 60px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 16px;
            line-height: 21px;
            color: #040405;
        }

        .table-container {
            padding-left: 10px;
        }

        .table {
            width: 100%;
            padding-bottom: 10px;
        }

        .title-item {
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            color: #595959;
            width: 100%;
            float: right;
            text-align: right;
        }

        .money {
            padding: 0px;
            margin: 0px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            text-align: right;
            color: #595959;
        }
        .border{
            border: 1px solid #a5a5a5 !important;
        }
        tr.td-border td {
            border-top: 1px solid #a5a5a5;
            border-bottom: 1px solid #a5a5a5;
        }

        td {
            border-right: 1px solid #a5a5a5;
        }

        p {
            padding: 5px !important;
        }

        .total-table {
            width: 100%;
        }

        .total-cell {
            padding-right: 20px;
        }

        .total {
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

        .total-table {
            width: 100%;
            margin-top: 40px;
            padding: 15px 0px;
            box-sizing: border-box;
        }

        .total-title {
            padding: 0px;
            margin: 0px;
            text-align: left;
            font-style: normal;
            font-weight: 600;
            font-size: 16px;
            line-height: 21px;
            color: #595959;
        }

        .total-money {
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
                    <td colspan="2" class="border">
                        <p class="sub-heading-text"> Invoice</p>
                        <p class="heading-text">
                            {{ $company->name }}
                        </p>
                    </td>
                    <td class="border">
                        <p class="total-title">Invoice Date <span style="float:right; font-size: 13px;">{{ $invoice->invoice_date }}</span></p>
                        <p class="total-title">Invoice Number <span style="float:right; font-size: 13px;">{{ $invoice->invoice_number }}</span></p>
                        <p class="total-title">Party Name <span style="float:right; font-size: 13px;">{{ $invoice->master->name }}</span></p>
                    </td>
                </tr>
            </table>
            <table class="table">
                <tr class="td-border">
                    <td>
                        <p>
                            S.No.
                        </p>
                    </td>
                    <td>
                        <p>
                            Description
                        </p>
                    </td>
                    <td>
                        <p>
                            Quantity
                        </p>
                    </td>
                    <td>
                        <p>
                            Rate
                        </p>
                    </td>
                    <td>
                        <p>
                            Per
                        </p>
                    </td>
                    <td>
                        <p>
                            Amount
                        </p>
                    </td>
                </tr>
                @foreach ($invoice_items as $key => $item)
                    <tr>
                        <td>
                            <p style="font-size: 13px;">
                                {{ $key + 1 }}
                            </p>
                        </td>
                        <td>
                            <p style="font-size: 13px;">
                                {{ $item->name }}
                            </p>
                        </td>
                        <td>
                            <p style="font-size: 13px;">
                                {{ $item->quantity }} {{ $item->inventory->unit }}
                            </p>
                        </td>
                        <td>
                            <p style="font-size: 13px;">
                                ₹ {{ $item->sale_price }}
                            </p>
                        </td>
                        <td>
                            <p style="font-size: 13px;">
                                {{ $item->inventory->unit }}
                            </p>
                        </td>
                        <td>
                            <p style="font-size: 13px;">
                                ₹ {{ $item->total }}
                            </p>
                        </td>
                    </tr>
                @endforeach
                <tr class="td-border">
                    <td></td>
                    <td></td>
                    <td>
                        <p style="font-size: 13px;">
                            {{ $total_quantity }}
                        </p>
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <p style="font-size: 13px;">
                            {{ 'Rs ' . $total_amount }}
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
