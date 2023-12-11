<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ 'slip - ' . $invoice_number }}</title>
    <style type="text/css">
        body {
            font-family: "DejaVu Sans";
        }
        table {
            border-collapse: collapse;
        }
        p {
            font-size: 15px;
            text-decoration: underline;
        }
        span{
            font-size: 20px;
            padding-bottom: 15px;
        }
        @page {
            size: landscape letter;
            size: 4in 4in;
        }
    </style>
</head>

<body>
    <table style=" width: 100% !important;">
        <tr>
            <td class="border">
                <p class="total-title">Party Name </p>
                <span>{{ $party_name }}</span>
                <p class="total-title">Estimate Number </p>
                <span>{{ $invoice_number }}</span>
                <p class="total-title">Reference Number </p>
                <span>{{ $reference_number }}</span>
            </td>
        </tr>
    </table>
</body>

</html>
