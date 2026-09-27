<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profit & Loss Report</title>
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
                        <p class="sub-heading-text">PROFIT & LOSS REPORT</p>
                    </td>
                </tr>
            </table>

            <table class="income-table">
                <tr>
                    <td>
                        <p class="income-title">Income</p>
                    </td>
                    <td>
                        <p class="income-money">{!! format_money_pdf($income) !!}</p>
                    </td>
                </tr>
            </table>
            <p class="expenses-title">Expenses</p>
            <div class="expenses-table-container">
                <table class="expenses-table">
                    @foreach ($expenseCategories as $expenseCategory)
                        <tr>
                            <td>
                                <p class="expense-title">
                                    {{ $expenseCategory->category->name }}
                                </p>
                            </td>
                            <td>
                                <p class="expense-money">
                                    {!! format_money_pdf($expenseCategory->total_amount) !!}
                                </p>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
        </div>

        <table class="expense-total-table">
            <tr>
                <td class="expense-total-cell">
                    <p class="expense-total">{!! format_money_pdf($totalExpense) !!}</p>
                </td>
            </tr>
        </table>
        <table class="profit-table">
            <tr>
                <td>
                    <p class="profit-title">NET PROFIT</p>
                </td>
                <td>
                    <p class="profit-money">{!! format_money_pdf(($income-$totalExpense)) !!}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
