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
    <title>Receipt Report</title>
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
            color: #595959;
            width: 100%;
            text-align: right;
            padding: 0px;
            margin: 0px;
        }
        .sub-heading-text {
            font-style: normal;
            font-weight: 600;
            font-size: 14px;
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
            font-size: 12px;
            line-height: 21px;
            color: #040405;
        }
        .title {
            margin-top: 20px;
            padding-left: 3px;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
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
        .border {
            border: 1px solid #a5a5a5 !important;
        }
        tr.td-border td {
            border-top: 1px solid #a5a5a5;
            border-bottom: 1px solid #a5a5a5;
        }
        td {
            border-right: 1px solid #a5a5a5;
            border-left: 1px solid #a5a5a5;
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
            font-size: 14px;
            line-height: 21px;
            color: #595959;
        }
        .row-item td p {
            line-height: 1px;
            marign: 0 5px !important;
            padding: 0 5px !important;
        }
        header { position: fixed; top: -110px; right: 0; left: 0; background-color: rgb(233, 250, 255); height: 115px; }
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
                    <p class="sub-heading-text"> Receipt</p>
                    {{-- <p class="heading-text">
                        {{ $company->name }}
                    </p> --}}
                </td>
                <td class="border">
                    <p class="total-title">Receipt Date <span
                            style="float:right; font-size: 12px;">{{ $receipt->receipt_date }}</span></p>
                    <p class="total-title">Receipt Number <span
                            style="float:right; font-size: 12px;">{{ $receipt->receipt_number }}</span></p>
                    <p class="total-title">Party Name <span
                            style="float:right; font-size: 12px;">{{ $receipt->master->name }}</span></p>
                </td>
            </tr>
        </table>
    </header>
    <div style="margin-top: 20px;">
        <table style="width: 100% !important">
            <tbody>
                <div class="main-container">
                    <div class="sub-container">
                        <table class="table">
                            <tr class="td-border">
                                <td>
                                    <p style="font-size: 14px; font-weight: bold">
                                        Number
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 14px; font-weight: bold">
                                        Mode
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 14px; font-weight: bold">
                                        Date
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 14px; font-weight: bold">
                                        Party
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 14px; font-weight: bold">
                                        Amount
                                    </p>
                                </td>
                            </tr>
                            <tr class="row-item">
                                <td>
                                    <p style="font-size: 12px;">
                                        {{ $receipt->receipt_number }}
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 12px;">
                                        {{ $receipt->receipt_mode }}
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 12px;">
                                        {{ $receipt->receipt_date }}
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 12px;">
                                        {{ $receipt->master->name }}
                                    </p>
                                </td>
                                <td>
                                    <p style="font-size: 12px;">
                                        ₹ {{ $receipt->amount }}
                                    </p>
                                </td>
                            </tr>
                            <tr class="td-border">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                    <p style="font-size: 12px;">
                                        {{ 'Rs ' . $total_amount }}
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
        <div>
            <p style="font-size: 12px;">
                Amount Chargeable (in words)
                <br />
                {{ numberTowords($total_amount) }}
            </p>
        </div>
        <div style="margin: 10px 0px; position: fixed; bottom: 20px">
            <p style="font-size: 12px; bottom: 0">
                Remark: <br>
                {{ $receipt->notes }}
            </p>
            <table>
                <tr class="td-border">
                    <td style="width: 50%">
                        <p style="font-size: 12px;">
                            <u> Declaration: </u> <br>
                            We declare that this receipt shows the actual
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
            <p style="font-size: 10px; bottom: 0px; margin: 0; padding: 0">This is a Computer Generated Receipt</p>
        </div>
    </footer>
</body>

</html>
