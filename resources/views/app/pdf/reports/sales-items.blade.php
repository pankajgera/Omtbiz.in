<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Item Report</title>
    @include('app.pdf.reports.partials.structured-styles')
</head>
<body class="summary-report">
    <div class="main-container">
        <div class="sub-container">
            <table class="header">
                <tr>
                    <td>
                        <p class="heading-text">{{ $company->name }}</p>
                    </td>
                    <td>
                        <p class="heading-date-range">{{ $from_date }} - {{ $to_date }}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="sub-heading-text text-center">Sales Report: By Item</p>
                    </td>
                </tr>
            </table>

            {{-- <table class="income-table">
                <tr>
                    <td>
                        <p class="income-title">Income</p>
                    </td>
                    <td>
                        <p class="income-money">{{ $income }}</p>
                    </td>
                </tr>
            </table> --}}
            <p class="expenses-title">Items</p>
            @foreach ($items as $item)
                <div class="expenses-table-container">
                    <table class="expenses-table">
                        <tr>
                            <td>
                                <p class="expense-title">
                                    {{ $item->name }}
                                </p>
                            </td>
                            <td>
                                <p class="expense-money">
                                    {!! format_money_pdf($item->total_amount) !!}
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach

                <table class="expense-total-table">
                    <tr>
                        <td class="expense-total-cell">
                            <p class="expense-total">
                                {!! format_money_pdf($totalAmount) !!}
                            </p>
                        </td>
                    </tr>
                </table>
        </div>


        <table class="profit-table">
            <tr>
                <td>
                    <p class="profit-title">TOTAL SALES</p>
                </td>
                <td>
                    <p class="profit-money">
                        {!! format_money_pdf($totalAmount) !!}
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
