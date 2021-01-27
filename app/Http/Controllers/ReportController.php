<?php

namespace Crater\Http\Controllers;

use Illuminate\Http\Request;
use Crater\User;
use Crater\Invoice;
use Crater\Company;
use Crater\InvoiceItem;
use Crater\Expense;
use Crater\CompanySetting;
use Crater\Tax;
use PDF;
use Carbon\Carbon;
use Crater\AccountLedger;
use Crater\Voucher;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    public function customersSalesReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $start = Carbon::createFromFormat('d/m/Y', $request->from_date);
        $end = Carbon::createFromFormat('d/m/Y', $request->to_date);

        $customers = User::with(['invoices' => function ($query) use ($start, $end) {
            $query->whereBetween(
                'invoice_date',
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        }])
            ->customer()
            ->whereCompany($company->id)
            ->applyInvoiceFilters($request->only(['from_date', 'to_date']))
            ->get();

        $totalAmount = 0;
        foreach ($customers as $customer) {
            $customerTotalAmount = 0;
            foreach ($customer->invoices as $invoice) {
                $customerTotalAmount += $invoice->total;
            }
            $customer->totalAmount = $customerTotalAmount;
            $totalAmount += $customerTotalAmount;
        }

        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'customers' => $customers,
            'totalAmount' => $totalAmount,
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);
        $pdf = PDF::loadView('app.pdf.reports.sales-customers');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    public function itemsSalesReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $items = InvoiceItem::whereCompany($company->id)
            ->applyInvoiceFilters($request->only(['from_date', 'to_date']))
            ->itemAttributes()
            ->get();

        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item->total_amount;
        }

        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'items' => $items,
            'colorSettings' => $colorSettings,
            'totalAmount' => $totalAmount,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);
        $pdf = PDF::loadView('app.pdf.reports.sales-items');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    public function expensesReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $expenseCategories = Expense::with('category')
            ->whereCompany($company->id)
            ->applyFilters($request->only(['from_date', 'to_date']))
            ->expensesAttributes()
            ->get();

        $totalAmount = 0;
        foreach ($expenseCategories as $category) {
            $totalAmount += $category->total_amount;
        }

        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'expenseCategories' => $expenseCategories,
            'colorSettings' => $colorSettings,
            'totalExpense' => $totalAmount,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);
        $pdf = PDF::loadView('app.pdf.reports.expenses');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    public function taxSummery($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $taxTypes = Tax::with('taxType', 'invoice', 'invoiceItem')
            ->whereCompany($company->id)
            ->whereInvoicesFilters($request->only(['from_date', 'to_date']))
            ->taxAttributes()
            ->get();

        $totalAmount = 0;
        foreach ($taxTypes as $taxType) {
            $totalAmount += $taxType->total_tax_amount;
        }

        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];

        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'taxTypes' => $taxTypes,
            'totalTaxAmount' => $totalAmount,
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);

        $pdf = PDF::loadView('app.pdf.reports.tax-summary');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    public function profitLossReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $invoicesAmount = Invoice::whereCompany($company->id)
            ->applyFilters($request->only(['from_date', 'to_date']))
            ->wherePaidStatus(Invoice::STATUS_PAID)
            ->sum('total');

        $expenseCategories = Expense::with('category')
            ->whereCompany($company->id)
            ->applyFilters($request->only(['from_date', 'to_date']))
            ->expensesAttributes()
            ->get();

        $totalAmount = 0;
        foreach ($expenseCategories as $category) {
            $totalAmount += $category->total_amount;
        }

        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'company' => $company,
            'income' => $invoicesAmount,
            'expenseCategories' => $expenseCategories,
            'totalExpense' => $totalAmount,
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);
        $pdf = PDF::loadView('app.pdf.reports.profit-loss');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }


    public function customersReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();
        $vouchers_by_ledger = Voucher::where('account_ledger_id', $request->ledger_id)->get();
        $ledger = AccountLedger::findOrFail($request->ledger_id);
        $all_voucher_ids = Voucher::where('account_ledger_id', $request->ledger_id)->whereNotNull('related_voucher')->get();
        $each_ids = null;
        foreach ($all_voucher_ids as $each) {
            if ($each_ids) {
                $each_ids = $each_ids . ', ' . $each->related_voucher;
            } else {
                $each_ids = $each->related_voucher;
            }
        }
        $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
        $related_vouchers = Voucher::whereIn('id', explode(',', $unique_ids))
            ->where('account_ledger_id', '!=', $request->ledger_id)
            ->orderBy('id')
            ->get();
        $totalAmount = 0;
        foreach ($related_vouchers as $each) {
            $each['amount'] = 0 < $each->credit ? $each->credit : $each->debit;
        }
        $vouchers_debit_sum = $vouchers_by_ledger->sum('debit');
        $vouchers_credit_sum = $vouchers_by_ledger->sum('credit');
        $balance = $ledger->debit - $ledger->credit;
        $opening_balance = $ledger->accountMaster->opening_balance;
        $calc_balance = $opening_balance > $balance ? $opening_balance - $balance :
            ($opening_balance > 0 ? $balance - $opening_balance : abs($balance));
        if ($vouchers_debit_sum > $vouchers_credit_sum) {
            $ledger->update([
                'type' => 'Dr',
                'credit' => $vouchers_credit_sum,
                'debit' => $vouchers_debit_sum,
                'balance' => $calc_balance,
            ]);
        } elseif ($vouchers_debit_sum < $vouchers_credit_sum) {
            $ledger->update([
                'type' => 'Cr',
                'credit' => $vouchers_credit_sum,
                'debit' => $vouchers_debit_sum,
                'balance' => $calc_balance,
            ]);
        }
        $ledgerType = $ledger->type === 'Cr' ? 'Dr' : 'Cr';
        $totalAmount = $ledger->balance;
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $company->id);
        $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->format($dateFormat);
        $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->format($dateFormat);

        $colors = [
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];

        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($company->id)
            ->get();

        view()->share([
            'ledgerType' => $ledgerType,
            'opening_balance' => $opening_balance,
            'opening_balance_type' => $ledger->accountMaster->type,
            'related_vouchers' => $related_vouchers,
            'totalAmount' => $totalAmount,
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);

        $pdf = PDF::loadView('app.pdf.reports.customers');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    public function getLedgersInReport(Request $request)
    {
        $ledgers = AccountLedger::where('company_id', $request->header('company'))->get();
        return response()->json([
            'ledgers' => $ledgers,
        ]);
    }
}
