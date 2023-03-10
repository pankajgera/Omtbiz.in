<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use PDF;
use App\Models\CompanySetting;
use App\Models\Estimate;
use App\Models\User;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use App\Models\EstimateTemplate;
use App\Mail\EstimateViewed;
use App\Mail\InvoiceViewed;
use App\Models\AccountLedger;
use App\Models\EstimateItem;
use App\Models\InvoiceItem;
use App\Models\Receipt;
use App\Models\Voucher;
use Carbon\Carbon;

class FrontendController extends Controller
{
    public function home()
    {
        return view('front.index');
    }

    /**
     * Get customer estimate pdf
     */
    public function getCustomerEstimatePdf($id)
    {
        $estimate = Estimate::with(
            'user',
            'items',
            'user.billingAddress',
            'user.shippingAddress'
        )
            ->where('unique_hash', $id)
            ->first();

        $labels = [];

        $estimateTemplate = EstimateTemplate::find($estimate->estimate_template_id);

        $company = Company::find($estimate->company_id);

        $logo = $company->getMedia('logo')->first();

        if ($logo) {
            $logo = $logo->getFullUrl();
        }

        if ($estimate) {
            $notifyEstimateViewed = CompanySetting::getSetting(
                'notify_estimate_viewed',
                $estimate->company_id
            );

            if ($notifyEstimateViewed == 'YES') {
                $data['estimate'] = Estimate::findOrFail($estimate->id)->toArray();
                $data['user'] = User::find($estimate->user_id)->toArray();
                $notificationEmail = CompanySetting::getSetting(
                    'notification_email',
                    $estimate->company_id
                );

                \Mail::to($notificationEmail)->send(new EstimateViewed($data));
            }
        }

        $companyAddress = User::with(['addresses', 'addresses.country'])->find(1);

        $colors = [
            'invoice_primary_color',
            'invoice_column_heading',
            'invoice_field_label',
            'invoice_field_value',
            'invoice_body_text',
            'invoice_description_text',
            'invoice_border_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($estimate->company_id)
            ->get();

        view()->share([
            'estimate' => $estimate,
            'logo' => $logo ?? null,
            'company_address' => $companyAddress,
            'colors' => $colorSettings,
            'labels' => $labels,
        ]);
        $pdf = PDF::loadView('app.pdf.estimate.' . $estimateTemplate->view);

        return $pdf->stream();
    }


    /**
     * Get customer invoice pdf
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerInvoicePdf($id)
    {
        $invoice = Invoice::with([
            'inventories',
            'user',
            'invoiceTemplate',
        ])
            ->where('unique_hash', $id)
            ->first();

        $labels = [];

        $invoiceTemplate = InvoiceTemplate::find($invoice->invoice_template_id);

        $company = Company::find($invoice->company_id);
        $logo = $company->getMedia('logo')->first();

        if ($logo) {
            $logo = $logo->getFullUrl();
        }

        if ($invoice) {
            $notifyInvoiceViewed = CompanySetting::getSetting(
                'notify_invoice_viewed',
                $invoice->company_id
            );

            // if ($notifyInvoiceViewed == 'YES') {
            //     $data['invoice'] = Invoice::findOrFail($invoice->id)->toArray();
            //     $data['user'] = User::find($invoice->user_id)->toArray();
            //     $notificationEmail = CompanySetting::getSetting(
            //         'notification_email',
            //         $invoice->company_id
            //     );

            //     \Mail::to($notificationEmail)->send(new InvoiceViewed($data));
            // }
        }

        $companyAddress = User::with(['addresses', 'addresses.country'])->find(1);

        $colors = [
            'invoice_primary_color',
            'invoice_column_heading',
            'invoice_field_label',
            'invoice_field_value',
            'invoice_body_text',
            'invoice_description_text',
            'invoice_border_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($invoice->company_id)
            ->get();

        view()->share([
            'invoice' => $invoice,
            'colors' => $colorSettings,
            'company_address' => $companyAddress,
            'logo' => $logo ?? null,
            'labels' => $labels,
        ]);
        $pdf = PDF::loadView('app.pdf.invoice.' . $invoiceTemplate->view);

        return $pdf->stream();
    }

    /**
     * Get estimate view pdf
     */
    public function getEstimatePdf($id)
    {
        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
        ])->where('unique_hash', $id)->first();

        $labels = [];

        $estimateTemplate = EstimateTemplate::find($estimate->estimate_template_id);

        $company = Company::find($estimate->company_id);
        $companyAddress = User::with(['addresses', 'addresses.country'])->find(1);
        $logo = $company->getMedia('logo')->first();

        if ($logo) {
            $logo = $logo->getFullUrl();
        }

        $colors = [
            'invoice_primary_color',
            'invoice_column_heading',
            'invoice_field_label',
            'invoice_field_value',
            'invoice_body_text',
            'invoice_description_text',
            'invoice_border_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($estimate->company_id)
            ->get();

        $estimate_i = EstimateItem::with('inventory')->where('estimate_id', $estimate->id);
        $estimate_items = $estimate_i->get();

        $estimateWith = Estimate::with(['master'])->where('id', $estimate->id)->first();

        view()->share([
            'logo' => $logo ?? null,
            'company_address' => $companyAddress,
            'colors' => $colorSettings,
            'labels' => $labels,
            'estimate' => $estimateWith,
            'total_quantity' => $estimate_i->sum('quantity'),
            'total_amount' => $estimateWith->sub_total,
            'estimate_items' => $estimate_items,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);
        $pdf = PDF::loadView('app.pdf.estimate.' . $estimateTemplate->view);

        return $pdf->stream();
    }

    /**
     * Get invoice view pdf
     */
    public function getInvoicePdf($id)
    {
        $invoice = Invoice::with([
            'inventories',
            'user',
            'invoiceTemplate',
        ])->where('unique_hash', $id)->first();

        $invoiceTemplate = InvoiceTemplate::find($invoice->invoice_template_id);
        $company = Company::where('id', $invoice->company_id)->first();
        $ledger = AccountLedger::findOrFail($invoice->account_master_id);

        $all_voucher_ids = Voucher::where('account_ledger_id', $ledger->id)->whereNotNull('related_voucher')->get();
        $each_ids = null;
        foreach ($all_voucher_ids as $each) {
            if ($each_ids) {
                $each_ids = $each_ids . ', ' . $each->related_voucher;
            } else {
                $each_ids = $each->related_voucher;
            }
        }
        $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
        $related_vouchers = Voucher::with(['invoice.inventories'])->whereIn('id', explode(',', $unique_ids))
            ->where('account', '!=', $ledger->account)
            ->orderBy('date')
            ->get();

        foreach ($related_vouchers as $each) {
            $each['amount'] = 0 < $each->credit ? $each->credit : $each->debit;
        }

        $vouchers_debit_sum = $all_voucher_ids->sum('debit');
        $vouchers_credit_sum = $all_voucher_ids->sum('credit');

        $opening_balance = $ledger->accountMaster->opening_balance;
        $calc_balance = $ledger->balance;
        $calc_type = $ledger->type;
        $calc_total = 0;

        //Calculate total balance, type, debit/credit
        if ($vouchers_debit_sum > $vouchers_credit_sum) {
            $calc_total = $vouchers_debit_sum - $vouchers_credit_sum;
            $calc_type = 'Dr';
        } else {
            $calc_total = $vouchers_credit_sum - $vouchers_debit_sum;
            $calc_type = 'Cr';
        }
        if ('Dr' === $ledger->accountMaster->type) {
            if ('Dr' === $calc_type) {
                $calc_balance = $calc_total + $opening_balance;
            } else {
                if ($calc_total > $opening_balance) {
                    $calc_balance = $calc_total - $opening_balance;
                    $calc_type = 'Cr';
                } else {
                    $calc_balance = $opening_balance - $calc_total;
                    $calc_type = 'Dr';
                }
            }
        } else {
            if ('Cr' === $calc_type) {
                $calc_balance = $calc_total + $opening_balance;
            } else {
                if ($calc_total > $opening_balance) {
                    $calc_balance  = $calc_total - $opening_balance;
                    $calc_type = 'Dr';
                } else {
                    $calc_balance = $opening_balance - $calc_total;
                    $calc_type = 'Cr';
                }
            }
        }

        $ledger->update([
            'type' => $calc_type,
            'credit' => $vouchers_credit_sum,
            'debit' => $vouchers_debit_sum,
            'balance' => $calc_balance,
        ]);

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

        $invoice_i = InvoiceItem::with('inventory')->where('type', 'invoice')->where('invoice_id', $invoice->id);
        $invoice_items = $invoice_i->get();

        $time = substr($invoice->created_at, -8);
        $date = substr($invoice->invoice_date, 0, 10);
        $invoice->invoice_date = Carbon::parse($date . ' ' . $time, 'Asia/Kolkata')->toDateTimeString();

        view()->share([
            'invoice' => $invoice,
            'invoice_items' => $invoice_items,
            'ledgerType' => $calc_type,
            'ledger' => $ledger,
            'total_quantity' => $invoice_i->sum('quantity'),
            'total_amount' => $invoice->total,
            'related_vouchers' => $related_vouchers,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);

        $pdf = PDF::loadView('app.pdf.invoice.' . $invoiceTemplate->view);

        return $pdf->stream();
    }


    /**
     * Get receipt view pdf
     */
    public function getReceiptPdf($id)
    {
        $receipt = Receipt::with([
            'user',
            'master',
        ])->where('id', $id)->first();

        $company = Company::find($receipt->company_id);

        $logo = $company->getMedia('logo')->first();

        if ($logo) {
            $logo = $logo->getFullUrl();
        }

        $colors = [
            'receipt_primary_color',
            'receipt_column_heading',
            'receipt_field_label',
            'receipt_field_value',
            'receipt_body_text',
            'receipt_description_text',
            'receipt_border_color'
        ];
        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($receipt->company_id)
            ->get();

        view()->share([
            'receipt' => $receipt,
            'total_amount' => $receipt->amount,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);

        $pdf = PDF::loadView('app.pdf.receipt.receipt');

        return $pdf->stream();
    }

}
