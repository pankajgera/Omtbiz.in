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
        p {
            font-size: 40px;
        }
        p span{
            font-size: 30px;
        }
        @page {
            size: landscape letter;
        }
    </style>
</head>

<body>
    <table style=" width: 100% !important;">
        <tr>
            <td class="border">
                <p class="total-title">Party Name <span
                        style="float:right;">{{ $party_name }}</span></p>
                <p class="total-title">Invoice Number<span
                        style="float:right;">{{ $invoice_number }}</span></p>
                <p class="total-title">Reference Number <span
                        style="float:right;">{{ $reference_number }}</span></p>
            </td>
        </tr>
    </table>
</body>

</html>
