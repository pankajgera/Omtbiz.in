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
use App\Models\AccountMaster;
use App\Models\EstimateItem;
use App\Models\InvoiceItem;
use App\Models\Receipt;
use App\Models\Voucher;
use App\Models\PublicShare;
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
        $estimate = $this->sharedResource($id, 'estimate', Estimate::class, [
            'user',
            'items',
            'user.billingAddress',
            'user.shippingAddress',
        ]);

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

        $companyAddress = $this->companyAddress($estimate->company_id);

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
        $invoice = $this->sharedResource($id, 'invoice', Invoice::class, [
            'inventories',
            'user',
            'invoiceTemplate',
        ]);

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

        $companyAddress = $this->companyAddress($invoice->company_id);

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
        $estimate = $this->sharedResource($id, 'estimate', Estimate::class, [
            'items',
            'user',
            'estimateTemplate',
        ]);

        $labels = [];

        $estimateTemplate = EstimateTemplate::find($estimate->estimate_template_id);

        $company = Company::find($estimate->company_id);
        $companyAddress = $this->companyAddress($estimate->company_id);
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
        $invoice = $this->sharedResource($id, 'invoice', Invoice::class, [
            'inventories',
            'user',
            'invoiceTemplate',
        ]);

        $invoiceTemplate = InvoiceTemplate::find($invoice->invoice_template_id);
        $company = Company::where('id', $invoice->company_id)->first();
        $master = AccountMaster::find($invoice->account_master_id);
        $ledger = AccountLedger::withoutGlobalScopes()
            ->where('company_id', $invoice->company_id)
            ->where('account_master_id', $master->id)
            ->where('account', $master->name)
            ->firstOrFail();

        $all_voucher_ids = Voucher::withoutGlobalScopes()
            ->where('company_id', $invoice->company_id)
            ->where('account_ledger_id', $ledger->id)
            ->whereNotNull('related_voucher')
            ->get();
        $each_ids = null;
        foreach ($all_voucher_ids as $each) {
            if ($each_ids) {
                $each_ids = $each_ids . ', ' . $each->related_voucher;
            } else {
                $each_ids = $each->related_voucher;
            }
        }
        $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
        $related_vouchers = Voucher::withoutGlobalScopes()->with(['invoice.inventories'])
            ->where('company_id', $invoice->company_id)
            ->whereIn('id', explode(',', $unique_ids))
            ->where('account_ledger_id', '!=', $ledger->id)
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

        $invoice_i = InvoiceItem::withoutGlobalScopes()->with('inventory')
            ->where('company_id', $invoice->company_id)
            ->where('type', 'invoice')
            ->where('invoice_id', $invoice->id);
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
        $receipt = $this->sharedResource($id, 'receipt', Receipt::class, [
            'user',
            'master',
        ]);

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

    private function sharedResource(string $token, string $type, string $model, array $relations)
    {
        $share = PublicShare::withoutGlobalScopes()
            ->active()
            ->where('token', $token)
            ->where('resource_type', $type)
            ->firstOrFail();

        return $model::withoutGlobalScopes()
            ->with($relations)
            ->where('company_id', $share->company_id)
            ->findOrFail($share->resource_id);
    }

    private function companyAddress(int $companyId): ?User
    {
        return User::withoutGlobalScopes()
            ->with(['addresses', 'addresses.country'])
            ->where('company_id', $companyId)
            ->where('role', 'admin')
            ->first();
    }

}
