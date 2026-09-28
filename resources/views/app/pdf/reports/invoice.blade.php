<?php
$numberTowords = function ($num)
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
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'invoice - ' . $invoice->invoice_number }}</title>
    @include('app.pdf.reports.partials.structured-styles')
    <style>
        .report-document { font-size: 13px; }
        .party-name { font-size: 19px; }
        .metadata th, .metadata td { font-size: 15px; font-weight: bold; }
        .document-heading .identity { width: 42%; }
        .document-heading .metadata { width: 58%; }
        .document-footnote { font-size: 11px; }
        .line-items th:first-child { padding-left: 5px; padding-right: 5px; white-space: nowrap; }
    </style>
    @if ($printPreview ?? false)
        @include('app.pdf.reports.partials.invoice-preview-styles')
    @endif
</head>
<body>
    @if ($printPreview ?? false)
        <div class="invoice-preview-actions">
            <button id="print-report" type="button" autofocus>Print</button>
        </div>
        <main class="invoice-preview-sheet">
    @endif
    @include('app.pdf.reports.partials.structured-document', [
        'document' => $invoice,
        'documentKind' => 'invoice',
        'documentTitle' => 'Estimate',
        'documentItems' => $invoice_items,
        'metadata' => ['Estimate Date' => $invoice->invoice_date, 'Estimate Number' => $invoice->invoice_number, 'Reference Number' => (strpos($invoice->reference_number, '-') !== false ? explode('-', $invoice->reference_number)[2] : $invoice->reference_number)],
    ])
    @if ($printPreview ?? false)
        </main>
        @include('app.pdf.reports.partials.invoice-preview-pagination')
    @endif
</body>
</html>
