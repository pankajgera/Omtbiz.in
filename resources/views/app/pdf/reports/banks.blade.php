<!DOCTYPE html>
<html lang="en">
<head>
    <title>Banks Report</title>
    @include('app.pdf.reports.partials.structured-styles')
</head>
<body class="summary-report">
    <div class="main-container">
        <div class="sub-container">
            <table class="header">
                <tr>
                    <td>
                        {{-- <p class="heading-text">
                            {{ $company->name }}
                        </p> --}}
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

                    @endforeach
                </table>
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
            </div>
        </div>
    </div>
</body>
</html>
