<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expenses Report</title>
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
                        <p class="sub-heading-text">EXPENSES REPORT</p>
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
        <table class="total-expense-table">
            <tr>
                <td>
                    <p class="total-expense-title">TOTAL EXPENSE</p>
                </td>
                <td>
                    <p class="total-expense-money">{!! format_money_pdf($totalExpense) !!}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
