<?php
function numberTowords($num)
{
    $ones = [
        0 => 'ZERO',
        1 => 'ONE',
        2 => 'TWO',
        3 => 'THREE',
        4 => 'FOUR',
        5 => 'FIVE',
        6 => 'SIX',
        7 => 'SEVEN',
        8 => 'EIGHT',
        9 => 'NINE',
        10 => 'TEN',
        11 => 'ELEVEN',
        12 => 'TWELVE',
        13 => 'THIRTEEN',
        14 => 'FOURTEEN',
        15 => 'FIFTEEN',
        16 => 'SIXTEEN',
        17 => 'SEVENTEEN',
        18 => 'EIGHTEEN',
        19 => 'NINETEEN',
    ];
    $tens = [
        0 => 'ZERO',
        1 => 'TEN',
        2 => 'TWENTY',
        3 => 'THIRTY',
        4 => 'FORTY',
        5 => 'FIFTY',
        6 => 'SIXTY',
        7 => 'SEVENTY',
        8 => 'EIGHTY',
        9 => 'NINETY',
    ];
    $hundreds = ['HUNDRED', 'THOUSAND', 'MILLION', 'BILLION', 'TRILLION', 'QUARDRILLION']; /*limit t quadrillion */
    $num = number_format($num, 2, '.', ',');
    $num_arr = explode('.', $num);
    $wholenum = $num_arr[0];
    $decnum = $num_arr[1];
    $whole_arr = array_reverse(explode(',', $wholenum));
    krsort($whole_arr, 1);
    $rettxt = '';
    foreach ($whole_arr as $key => $i) {
        while (substr($i, 0, 1) == '0') {
            $i = substr($i, 1, 5);
        }
        if ($i < 20 && $i > 0) {
            /* echo "getting:".$i; */
            $rettxt .= $ones[$i];
        } elseif ($i < 100 && $i > 0) {
            if (substr($i, 0, 1) != '0') {
                $rettxt .= $tens[substr($i, 0, 1)];
            }
            if (substr($i, 1, 1) != '0') {
                $rettxt .= ' ' . $ones[substr($i, 1, 1)];
            }
        } else {
            if ($i > 0) {
                if (substr($i, 0, 1) != '0') {
                    $rettxt .= $ones[substr($i, 0, 1)] . ' ' . $hundreds[0];
                }
                if (substr($i, 1, 1) != '0') {
                    $rettxt .= ' ' . $tens[substr($i, 1, 1)];
                }
                if (substr($i, 2, 1) != '0') {
                    $rettxt .= ' ' . $ones[substr($i, 2, 1)];
                }
            }
        }
        if ($key > 0) {
            $rettxt .= ' ' . $hundreds[$key] . ' ';
        }
    }
    if ($decnum > 0) {
        $rettxt .= ' and ';
        if ($decnum < 20) {
            $rettxt .= $ones[$decnum];
        } elseif ($decnum < 100) {
            $rettxt .= $tens[substr($decnum, 0, 1)];
            $rettxt .= ' ' . $ones[substr($decnum, 1, 1)];
        }
    }
    return $rettxt . ' ONLY';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Invoice Report</title>
    <style type="text/css">
        body {
            font-family: "DejaVu Sans";
        }
        table {
            border-collapse: collapse;
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
            font-size: 14px;
            color: #000;
            width: 100%;
            text-align: right;
            padding: 0px;
            margin: 0px;
        }
        .td-header {
            font-size: 14px;
            font-weight: bold;
        }
        .sub-heading-text {
            font-style: normal;
            font-weight: 600;
            font-size: 14px;
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
            font-size: 12px;
            line-height: 21px;
            color: #000;
        }
        .title {
            margin-top: 60px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            color: #000;
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
            color: #000;
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
            color: #000;
        }
        .border {
            border: 1px solid #797979 !important;
        }
        tr.td-border td {
            border-top: 1px solid #797979;
            border-bottom: 1px solid #797979;
        }
        td {
            border-right: 1px solid #797979;
            border-left: 1px solid #797979;
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
            font-size: 14px;
            line-height: 21px;
            text-align: right;
            color: #000;
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
            font-size: 14px;
            line-height: 21px;
            color: #000;
        }
        .row-item td p {
            line-height: 1px;
            marign: 0 5px !important;
            padding: 0 5px !important;
        }
        tr    { page-break-inside:avoid; }
        header { position: fixed; top: -110px; right: 0; left: 0; background-color: rgb(233, 250, 255); height: 150px; }
        @page {
            margin: 120px 20px 20px 20px;
          }
    </style>
</head>

<body>
    <header>
        <table style=" width: 100% !important;">
            <tr>
                <td class="border">
                    <p class="sub-heading-text"> Estimate</p>
                    {{-- <p class="heading-text">
                        {{ $company->name }}
                    </p> --}}
                </td>
                <td class="border">
                    <p class="total-title">Estimate Date <span
                            style="float:right; font-size: 12px;">{{ $invoice->invoice_date }}</span></p>
                    <p class="total-title">Estimate Number <span
                            style="float:right; font-size: 12px;">{{ $invoice->invoice_number }}</span></p>
                    <p class="total-title">Reference Number <span
                        style="float:right; font-size: 12px;">{{ $invoice->reference_number }}</span></p>
                    <p class="total-title">Party Name <span
                            style="float:right; font-size: 12px;">{{ $invoice->master->name }}</span></p>
                </td>
            </tr>
        </table>
    </header>
    <div style="top: 40px; position: relative">
        <table style=" width: 100% !important;">
            <tbody>
                <div class="main-container">
                    <div class="sub-container">
                        <table class="table">
                            <tr class="td-border">
                                <td style="width: 10%">
                                    <p class="td-header">
                                        S.No.
                                    </p>
                                </td>
                                <td style="width: 40%">
                                    <p class="td-header">
                                        Description
                                    </p>
                                </td>
                                <td style="width: 10%">
                                    <p class="td-header">
                                        Meter
                                    </p>
                                </td>
                                <td style="width: 20%">
                                    <p class="td-header">
                                        Rate
                                    </p>
                                </td>
                                <td style="width: 20%">
                                    <p class="td-header">
                                        Amount
                                    </p>
                                </td>
                            </tr>
                            @foreach ($invoice_items as $key => $item)
                                <tr class="row-item" @if ($key > 29 && $key%30 === 0) style="page-break-after: always" @endif>
                                    <td style="width: 10%">
                                        <p style="font-size: 12px;">
                                            {{ $key + 1 }}
                                        </p>
                                    </td>
                                    <td style="width: 40%">
                                        <p style="font-size: 12px;">
                                            {{ $item->name }}
                                        </p>
                                    </td>
                                    <td style="width: 10%">
                                        <p style="font-size: 12px;">
                                            {{ $item->quantity }} {{ $item->inventory ? $item->inventory->unit : '' }}
                                        </p>
                                    </td>
                                    <td style="width: 20%">
                                        <p style="font-size: 12px;">
                                            ₹ {{ $item->sale_price }}
                                        </p>
                                    </td>
                                    <td style="width: 20%">
                                        <p style="font-size: 12px;">
                                            ₹ {{ $item->total }}
                                        </p>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="td-border">
                                <td style="width: 10%"></td>
                                <td style="width: 40%"></td>
                                <td style="width: 10%">
                                    <p style="font-size: 12px;">
                                        Total Meter: {{ str_replace('.00', '', $total_quantity) }}
                                    </p>
                                </td>
                                <td style="width: 20%">
                                    <p style="font-size: 12px;">
                                        Subtotal :
                                        <br/>
                                        @if($invoice->indirect_income)
                                            {{ $invoice->indirect_income}} :
                                        @endif
                                        <br/>
                                        @if($invoice->indirect_expense)
                                            {{ $invoice->indirect_expense }} :
                                        @endif
                                        <br/>
                                        <span style="font-weight: bold">Total :<span>
                                    </p>
                                </td>
                                <td style="width: 20%">
                                    <p style="font-size: 12px; font-weight: bold">
                                        ₹ {{ $invoice->sub_total}}
                                        <br/>
                                        @if($invoice->indirect_income)
                                            ₹ {{ $invoice->indirect_income_value }}
                                        @endif
                                        <br/>
                                        @if($invoice->indirect_expense)
                                            (-) ₹ {{ $invoice->indirect_expense_value }}
                                        @endif
                                        <br/>
                                        ₹ {{ $invoice->total }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </tbody>
        </table>
    </div>
    <footer>
        <div style="margin-top: 30px">
            <p style="font-size: 12px;">
                Amount Chargeable (in words)
                <br />
                {{ numberTowords($total_amount) }}
            </p>
        </div>
        <div style="margin: 10px 0px; position: fixed; bottom: 20px">
            <p style="font-size: 12px; bottom: 0">
                Remark: <br>
                {{ $invoice->notes }}
            </p>
            <table>
                <tr class="td-border">
                    <td style="width: 50%">
                        <p style="font-size: 12px;">
                            <u> Declaration: </u> <br>
                            We declare that this estimate shows the actual
                            price of the goods described and that all
                            particulars are true and correct
                        </p>
                    </td>
                    <td style="width: 50%">
                        <p style="font-size: 12px; float: right; bottom: 40px; right: 0; position: fixed">
                            Authorised Signatory
                        </p>
                    </td>
                </tr>
            </table>
            <p style="font-size: 10px; bottom: 0px; margin: 0; padding: 0">This is a Computer Generated Estimate</p>
        </div>
    </footer>
</body>

</html>
