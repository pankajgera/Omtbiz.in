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
use App\Models\EstimateItem;
use App\Models\InvoiceItem;

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

        $taxTypes = [];
        $taxes = [];
        $labels = [];

        if ($estimate->tax_per_item === 'YES') {
            foreach ($estimate->items as $item) {
                foreach ($item->taxes as $tax) {
                    if (!in_array($tax->name, $taxTypes)) {
                        array_push($taxTypes, $tax->name);
                        array_push($labels, $tax->name . ' (' . $tax->percent . '%)');
                    }
                }
            }

            foreach ($taxTypes as $taxType) {
                $total = 0;

                foreach ($estimate->items as $item) {
                    foreach ($item->taxes as $tax) {
                        if ($tax->name == $taxType) {
                            $total += $tax->amount;
                        }
                    }
                }

                array_push($taxes, $total);
            }
        }

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
            'taxes' => $taxes
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
            'taxes'
        ])
            ->where('unique_hash', $id)
            ->first();

        $taxTypes = [];
        $taxes = [];
        $labels = [];

        if ($invoice->tax_per_item === 'YES') {
            foreach ($invoice->inventories as $item) {
                foreach ($item->taxes as $tax) {
                    if (!in_array($tax->name, $labels)) {
                        array_push($taxTypes, $tax->name);
                        array_push($labels, $tax->name . ' (' . $tax->percent . '%)');
                    }
                }
            }

            foreach ($taxTypes as $taxType) {
                $total = 0;

                foreach ($invoice->inventories as $item) {
                    foreach ($item->taxes as $tax) {
                        if ($tax->name == $taxType) {
                            $total += $tax->amount;
                        }
                    }
                }

                array_push($taxes, $total);
            }
        }

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
            'taxes' => $taxes
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
            'items.taxes',
            'user',
            'estimateTemplate',
            'taxes',
            'taxes.taxType'
        ])
            ->where('unique_hash', $id)
            ->first();

        $taxTypes = [];
        $taxes = [];
        $labels = [];

        if ($estimate->tax_per_item === 'YES') {
            foreach ($estimate->items as $item) {
                foreach ($item->taxes as $tax) {
                    if (!in_array($tax->name, $taxTypes)) {
                        array_push($taxTypes, $tax->name);
                        array_push($labels, $tax->name . ' (' . $tax->percent . '%)');
                    }
                }
            }

            foreach ($taxTypes as $taxType) {
                $total = 0;

                foreach ($estimate->items as $item) {
                    foreach ($item->taxes as $tax) {
                        if ($tax->name == $taxType) {
                            $total += $tax->amount;
                        }
                    }
                }

                array_push($taxes, $total);
            }
        }

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
            'taxes' => $taxes,
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
            'taxes'
        ])
            ->where('unique_hash', $id)
            ->first();

        $taxTypes = [];
        $taxes = [];
        $labels = [];

        if ($invoice->tax_per_item === 'YES') {
            foreach ($invoice->inventories as $item) {
                foreach ($item->taxes as $tax) {
                    if (!in_array($tax->name, $taxTypes)) {
                        array_push($taxTypes, $tax->name);
                        array_push($labels, $tax->name . ' (' . $tax->percent . '%)');
                    }
                }
            }

            foreach ($taxTypes as $taxType) {
                $total = 0;

                foreach ($invoice->inventories as $item) {
                    foreach ($item->taxes as $tax) {
                        if ($tax->name == $taxType) {
                            $total += $tax->amount;
                        }
                    }
                }

                array_push($taxes, $total);
            }
        }

        $invoiceTemplate = InvoiceTemplate::find($invoice->invoice_template_id);
        $company = Company::find($invoice->company_id);

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
            ->whereCompany($invoice->company_id)
            ->get();

        $invoice_i = InvoiceItem::with('inventory')->where('type', 'invoice')->where('invoice_id', $invoice->id);
        $invoice_items = $invoice_i->get();

        $invoiceWith = Invoice::with(['master'])->where('id', $invoice->id)->first();
        view()->share([
            'invoice' => $invoiceWith,
            'total_quantity' => $invoice_i->sum('quantity'),
            'total_amount' => $invoiceWith->sub_total,
            'invoice_items' => $invoice_items,
            'colorSettings' => $colorSettings,
            'company' => $company,
        ]);

        $pdf = PDF::loadView('app.pdf.invoice.' . $invoiceTemplate->view);

        return $pdf->stream();
    }

}
