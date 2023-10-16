<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\CompanySetting;
use PDF;
use Carbon\Carbon;
use App\Models\AccountGroup;
use App\Models\AccountLedger;
use App\Models\AccountMaster;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    /**
     * Customer sales report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
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

    /**
     * Item sale report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
    public function itemsSalesReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();

        $items = InvoiceItem::whereCompany($company->id)
            ->where('type', 'invoice')
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

    /**
     * Expenses report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
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

    /**
     * Profit loss report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
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
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);
        $pdf = PDF::loadView('app.pdf.reports.profit-loss');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }


    /**
     * Customer report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
    public function customersReport($hash, Request $request)
    {
        $company = Company::where('unique_hash', $hash)->first();
        $ledger = AccountLedger::findOrFail($request->ledger_id);
        $from = Carbon::parse(str_replace('/', '-', $request->from_date))->startOfDay();
        $to = Carbon::parse(str_replace('/', '-', $request->to_date))->endOfDay();

        //Update ledger related data
        $response = AccountLedger::ledgerMutation($ledger, $from, $to);

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
            'ledgerType' => $response['calc_type'],
            'ledger' => $ledger,
            'related_vouchers' => $response['related_vouchers'],
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'inventory_sum' => $response['inventory_sum'],
            'total_opening_balance_dr' => $response['total_opening_balance_dr'],
            'total_opening_balance_cr' => $response['total_opening_balance_cr'],
            'current_balance_cr' => $response['current_balance_cr'],
            'current_balance_dr' => $response['current_balance_dr'],
            'closing_balance_cr' => $response['closing_balance_cr'],
            'closing_balance_dr' => $response['closing_balance_dr'],
        ]);

        $pdf = PDF::loadView('app.pdf.reports.customers');
        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    /**
     * Ledger in customer report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLedgersInReport(Request $request)
    {
        $ledgers = AccountLedger::with(['accountMaster'])->where('company_id', $request->header('company'))
            ->orderBy('account', 'asc')
            ->get();
        return response()->json([
            'ledgers' => $ledgers,
        ]);
    }

    /**
     * Bank report
     *
     * @param string $hash
     * @param Request $request
     * @return PDF
     */
    public function banksReport($hash, Request $request)
    {
        $related_vouchers = [];
        $related_masters = AccountMaster::where('name', 'LIKE', 'Bank')->get();
        $balance_array = [];
        $master_ledger_type = [];
        $from = Carbon::parse(str_replace('/', '-', $request->from_date))->startOfDay();
        $to = Carbon::parse(str_replace('/', '-', $request->to_date))->endOfDay();

        foreach ($related_masters as $key => $master) {
            $all_voucher_ids = Voucher::where('account_master_id', $master->id)->whereNotNull('related_voucher')->get();
            $each_ids = null;
            foreach ($all_voucher_ids as $each) {
                if ($each_ids) {
                    $each_ids = $each_ids . ', ' . $each->related_voucher;
                } else {
                    $each_ids = $each->related_voucher;
                }
            }
            $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
            $from = Carbon::parse(str_replace('/', '-', $request->from_date))->startOfDay();
            $to = Carbon::parse(str_replace('/', '-', $request->to_date))->endOfDay();
            $vouchers = Voucher::whereIn('id', explode(',', $unique_ids))
                ->where('account_master_id', '!=', $master->id)
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
                ->orderBy('date')
                ->get();
            if (0 < count($vouchers)) {
                array_push($related_vouchers, $vouchers->toArray());
            }
            array_push($master_ledger_type, $master->type);

            $calc_sum = AccountLedger::where('account_master_id', $master->id)
                ->where('account', '<>', $master->name)
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
                ->sum('balance');

            $current_balance = $master->opening_balance + floatval($calc_sum);
            array_push($balance_array, $current_balance);
        }

        $vouchers_debit_sum = [];
        $vouchers_credit_sum = [];
        foreach ($related_vouchers as $every) {
            foreach ($every as $each) {
                if ($each && isset($each['debit'])) {
                    array_push($vouchers_debit_sum, $each['debit']);
                }
                if ($each && isset($each['credit'])) {
                    array_push($vouchers_credit_sum, $each['credit']);
                }
            }
        }
        $debit_sum = array_sum($vouchers_debit_sum);
        $credit_sum = array_sum($vouchers_credit_sum);
        $credit_debit_sum = $debit_sum > $credit_sum ? $debit_sum - $credit_sum : $credit_sum - $debit_sum;
        $company = Company::where('unique_hash', $hash)->first();

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

        $type_array = array_count_values($master_ledger_type);
        arsort($type_array);
        $type_occurence = key($type_array);

        view()->share([
            'related_vouchers' => $related_vouchers,
            'opening_balance' => array_sum($balance_array),
            'master_type' => $type_occurence,
            'credit_debit_sum' => $credit_debit_sum,
            'credit_debit_type' => $debit_sum > $credit_sum ? 'Dr' : 'Cr',
            'colorSettings' => $colorSettings,
            'company' => $company,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);

        $pdf = PDF::loadView('app.pdf.reports.banks');

        if ($request->has('download')) {
            return $pdf->download();
        }

        return $pdf->stream();
    }

    /**
     * Generate Invoice Report
     *
     * @param Request $request
     * @param string|integer $id
     * @return PDF
     */
    public function invoiceReport(Request $request, $id)
    {
        $company = Company::findOrFail($request->company_id);
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


        $invoice_i = InvoiceItem::with('inventory')->where('type', 'invoice')->where('invoice_id', $id);
        $invoice_items = $invoice_i->get();

        $invoiceWith = Invoice::with(['master'])->where('id', $id)->first();
        $time = substr($invoiceWith->created_at, -8);
        $date = substr($invoiceWith->invoice_date, 0, 10);
        $invoiceWith->invoice_date = Carbon::parse($date . ' ' . $time, 'Asia/Kolkata')->toDateTimeString();
        view()->share([
            'invoice' => $invoiceWith,
            'total_quantity' => $invoice_i->sum('quantity'),
            'total_amount' => $invoiceWith->total,
            'invoice_items' => $invoice_items,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);

        $pdf = PDF::loadView('app.pdf.reports.invoice');

        return $pdf->stream();
    }


    /**
     * Generate Estimate Report
     *
     * @param Request $request
     * @param string|integer $id
     * @return PDF
     */
    public function estimateReport(Request $request, $id)
    {
        $company = Company::findOrFail($request->company_id);
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


        $estimate_i = EstimateItem::with('estimate')->where('estimate_id', $id);
        $estimate_items = $estimate_i->get();

        $estimateWith = Estimate::with(['master'])->where('id', $id)->first();
        $time = substr($estimateWith->created_at, -8);
        $date = substr($estimateWith->invoice_date, 0, 10);
        $estimateWith->invoice_date = Carbon::parse($date . ' ' . $time, 'Asia/Kolkata')->toDateTimeString();
        view()->share([
            'estimate' => $estimateWith,
            'total_quantity' => $estimate_i->sum('quantity'),
            'total_amount' => $estimateWith->sub_total,
            'estimate_items' => $estimate_items,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);

        $pdf = PDF::loadView('app.pdf.reports.estimate');

        return $pdf->stream();
    }

    /**
     * Invoice slip report
     *
     * @param Request $request
     * @param string|integer $id
     * @return void
     */
    public function slipReport(Request $request, $id)
    {
        $invoice = Invoice::with(['master'])->where('id', $id)->first();
        view()->share([
            'party_name' => $invoice->master->name,
            'invoice_number' => $invoice->invoice_number,
            'reference_number' => $invoice->reference_number,
        ]);

        $pdf = PDF::loadView('app.pdf.reports.slip');

        return $pdf->stream();
    }
}
