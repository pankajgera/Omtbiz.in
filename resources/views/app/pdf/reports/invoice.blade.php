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

        .total-table {
            border-top: 1px solid #EAF1FB;
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
                    <td colspan="2">
                        <p class="sub-heading-text"> Invoice</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="heading-text">
                            {{ $company->name }}
                        </p>
                    </td>
                </tr>
                <br/>
            </table>
            <table class="table">
                <tr>
                    <td>
                        <p class="total-title">Invoice Date <span style="float:right">{{ $invoice->invoice_date }}</span></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="total-title">Invoice Number <span style="float:right">{{ $invoice->invoice_number }}</span></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="total-title">Party Name <span style="float:right">{{ $invoice->master->name }}</span></p>
                    </td>
                </tr>
            </table>
            <table class="table" style="margin-left: 20 auto; display: table">
                @foreach ($invoice_items as $item)
                    <tr>
                        <td>
                            <p class="title-item">
                                {{ $item->name }}
                            </p>
                        </td>
                        <td>
                            <p class="title-item">
                                Quantity: {{ $item->quantity }}
                            </p>
                        </td>
                        <td>
                            <p class="title-item">
                                ₹ {{ $item->sale_price }}
                            </p>
                        </td>
                    </tr>
                @endforeach
            </table>
            <table class="total-table" style="margin-top: 60px">
                <tr>
                    <td>
                        <p class="total-title">Total Quantity
                            <span style="float:right;">
                                {{ $total_quantity }}
                            </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="total-title">Total Amount
                            <span style="float:right;">
                                {{ 'Rs ' . $total_amount }}
                            </span>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
