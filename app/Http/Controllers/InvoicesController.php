<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\Company;
use Illuminate\Support\Collection;
use App\Models\Currency;
use App\Models\InvoiceTemplate;
use App\Http\Requests;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use App\Models\AccountLedger;
use App\Models\AccountMaster;
use App\Models\Inventory;
use App\Models\Item;
use App\Mail\invoicePdf;
use function MongoDB\BSON\toJSON;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Mailgun\Mailgun;
use PDF;
use Validator;
use App\Models\TaxType;
use App\Models\Tax;
use App\Models\Voucher;
use Exception;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 10;

            $invoices = Invoice::with(['inventories', 'user', 'invoiceTemplate', 'taxes'])
                ->join('users', 'users.id', '=', 'invoices.user_id')
                ->applyFilters($request->only([
                    'status',
                    'paid_status',
                    'customer_id',
                    'invoice_number',
                    'from_date',
                    'to_date',
                    'orderByField',
                    'orderBy',
                    'search',
                ]))
                ->whereCompany($request->header('company'))
                ->select('invoices.*', 'users.name')
                ->latest()
                ->paginate($limit);

            return response()->json([
                'invoices' => $invoices,
                'invoiceTotalCount' => Invoice::count()
            ]);
        } catch (Exception $e) {
            Log::error('Error while getting invoice index ', [$e->getMessage()]);
        }
    }

    /**
     * GET - Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $tax_per_item = CompanySetting::getSetting('tax_per_item', $request->header('company'));
        $discount_per_item = CompanySetting::getSetting('discount_per_item', $request->header('company'));
        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));
        $invoice_num_auto_generate = CompanySetting::getSetting('invoice_auto_generate', $request->header('company'));

        $nextInvoiceNumberAttribute = null;
        $nextInvoiceNumber = Invoice::getNextInvoiceNumber($invoice_prefix);

        if ($invoice_num_auto_generate == "YES") {
            $nextInvoiceNumberAttribute = $nextInvoiceNumber;
        }

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'invoice_today_date' => Carbon::now()->toDateString(),
            'nextInvoiceNumberAttribute' => $nextInvoiceNumberAttribute,
            'nextInvoiceNumber' =>  $invoice_prefix . '-' . Carbon::now()->year . '-' . Carbon::now()->month . '-' . $nextInvoiceNumber,
            'inventory' => Inventory::all(),
            'invoiceTemplates' => InvoiceTemplate::all(),
            'tax_per_item' => $tax_per_item,
            'discount_per_item' => $discount_per_item,
            'invoice_prefix' => $invoice_prefix . '-' . Carbon::now()->year . '-' . Carbon::now()->month,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\InvoicesRequest $request)
    {
        try {
            // $invoice_number = explode("-", $request->invoice_number);
            // $number_attributes['invoice_number'] = $invoice_number[0] . '-' . sprintf('%06d', intval($invoice_number[1]));
            $number_attributes['invoice_number'] = $request->invoice_number;
            Validator::make($number_attributes, [
                'invoice_number' => 'required'
            ])->validate();

            $invoice_date = Carbon::createFromFormat('d-m-Y', $request->invoice_date);
            //$due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
            $status = Invoice::STATUS_DRAFT;

            $tax_per_item = CompanySetting::getSetting('tax_per_item', $request->header('company')) ?? 'NO';
            $discount_per_item = CompanySetting::getSetting('discount_per_item', $request->header('company')) ?? 'NO';

            if ($request->has('invoiceSend')) {
                $status = Invoice::STATUS_SENT;
            }

            $invoice = Invoice::create([
                'invoice_date' => $invoice_date,
                //'due_date' => $due_date,
                'invoice_number' => $number_attributes['invoice_number'],
                'reference_number' => $request->reference_number,
                'user_id' => $request->user_id,
                'company_id' => $request->header('company'),
                'invoice_template_id' => $request->invoice_template_id,
                'status' => 'DRAFT',
                'paid_status' => Invoice::STATUS_UNPAID,
                'sub_total' => $request->sub_total,
                'discount' => $request->discount,
                'discount_type' => $request->discount_type,
                'discount_val' => $request->discount_val,
                'total' => $request->total,
                'due_amount' => $request->total,
                'tax_per_item' => $tax_per_item,
                'discount_per_item' => $discount_per_item,
                'tax' => $request->tax,
                'notes' => $request->notes,
                'unique_hash' => str_random(60),
                'account_master_id' => $request->debtors['id'],
            ]);

            $invoiceInventories = $request->inventory;

            foreach ($invoiceInventories as $invoiceInventory) {
                $invoiceInventory['company_id'] = $request->header('company');
                $inventory = $invoice->inventories()->create($invoiceInventory);
                if (array_key_exists('taxes', $invoiceInventory) && $invoiceInventory['taxes']) {
                    foreach ($invoiceInventory['taxes'] as $tax) {
                        $tax['company_id'] = $request->header('company');
                        if (gettype($tax['amount']) !== "NULL") {
                            $inventory->taxes()->create($tax);
                        }
                    }
                }
            }

            if ($request->has('taxes')) {
                foreach ($request->taxes as $tax) {
                    $tax['company_id'] = $request->header('company');

                    if (gettype($tax['amount']) !== "NULL") {
                        $invoice->taxes()->create($tax);
                    }
                }
            }

            if ($request->has('invoiceSend')) {
                $data['invoice'] = Invoice::findOrFail($invoice->id)->toArray();
                $data['user'] = User::find($request->user_id)->toArray();
                $data['company'] = Company::find($invoice->company_id);

                $notificationEmail = CompanySetting::getSetting(
                    'notification_email',
                    $request->header('company')
                );

                $email = $data['user']['email'];

                if (!$email) {
                    return response()->json([
                        'error' => 'user_email_does_not_exist'
                    ]);
                }

                if (!$notificationEmail) {
                    return response()->json([
                        'error' => 'notification_email_does_not_exist'
                    ]);
                }

                \Mail::to($email)->send(new invoicePdf($data, $notificationEmail));
            }

            $invoice = Invoice::with(['inventories', 'user', 'invoiceTemplate', 'taxes'])->find($invoice->id);

            return response()->json([
                'url' => url('/invoices/pdf/' . $invoice->unique_hash),
                'invoice' => $invoice
            ]);
        } catch (Exception $e) {
            Log::error('Error while storing invoice ', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::with([
            'items',
            'items.taxes',
            'user',
            'invoiceTemplate',
            'taxes.taxType'
        ])->find($id);

        $siteData = [
            'invoice' => $invoice,
            'shareable_link' => url('/invoices/pdf/' . $invoice->unique_hash)
        ];

        return response()->json($siteData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $invoice = Invoice::with([
            'inventories',
            'inventories.taxes',
            'user',
            'invoiceTemplate',
            'taxes.taxType'
        ])->find($id);

        return response()->json([
            'nextInvoiceNumber' => $invoice->getInvoiceNumAttribute(),
            'invoice' => $invoice,
            'invoiceTemplates' => InvoiceTemplate::all(),
            'tax_per_item' => $invoice->tax_per_item,
            'discount_per_item' => $invoice->discount_per_item,
            'shareable_link' => url('/invoices/pdf/' . $invoice->unique_hash),
            'invoice_prefix' => $invoice->getInvoicePrefixAttribute()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Requests\InvoicesRequest $request, $id)
    {
        $invoice_number = explode("-", $request->invoice_number);
        $number_attributes['invoice_number'] = $invoice_number[0] . '-' . sprintf('%06d', intval($invoice_number[1]));

        Validator::make($number_attributes, [
            'invoice_number' => 'required|unique:invoices,invoice_number' . ',' . $id
        ])->validate();

        $invoice_date = Carbon::createFromFormat('d-m-Y', $request->invoice_date);
        //$due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);

        $invoice = Invoice::find($id);
        $oldAmount = $invoice->total;

        if ($oldAmount != $request->total) {
            $oldAmount = (int)round($request->total) - (int)$oldAmount;
        } else {
            $oldAmount = 0;
        }

        $invoice->due_amount = ($invoice->due_amount + $oldAmount);

        if ($invoice->due_amount == 0 && $invoice->paid_status != Invoice::STATUS_PAID) {
            //$invoice->status = Invoice::STATUS_COMPLETED;
            $invoice->paid_status = Invoice::STATUS_PAID;
        } elseif ($invoice->due_amount < 0 && $invoice->paid_status != Invoice::STATUS_UNPAID) {
            return response()->json([
                'error' => 'invalid_due_amount'
            ]);
        } elseif ($invoice->due_amount != 0 && $invoice->paid_status == Invoice::STATUS_PAID) {
            //$invoice->status = $invoice->getPreviousStatus();
            $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
        }

        $invoice->status = $request->status;
        $invoice->invoice_date = $invoice_date;
        //$invoice->due_date = $due_date;
        $invoice->invoice_number =  $number_attributes['invoice_number'];
        $invoice->reference_number = $request->reference_number;
        $invoice->user_id = $request->user_id;
        $invoice->invoice_template_id = $request->invoice_template_id;
        $invoice->sub_total = $request->sub_total;
        $invoice->total = $request->total;
        $invoice->discount = $request->discount;
        $invoice->discount_type = $request->discount_type;
        $invoice->discount_val = $request->discount_val;
        $invoice->tax = $request->tax;
        $invoice->notes = $request->notes;
        $invoice->save();

        $oldItems = $invoice->items->toArray();
        $oldTaxes = $invoice->taxes->toArray();
        $invoiceItems = $request->items;

        foreach ($oldItems as $oldItem) {
            InvoiceItem::destroy($oldItem['id']);
        }

        foreach ($oldTaxes as $oldTax) {
            Tax::destroy($oldTax['id']);
        }
        foreach ($invoiceItems as $invoiceItem) {
            $invoiceItem['company_id'] = $request->header('company');
            $item = $invoice->items()->create($invoiceItem);

            if (array_key_exists('taxes', $invoiceItem) && $invoiceItem['taxes']) {
                foreach ($invoiceItem['taxes'] as $tax) {
                    $tax['company_id'] = $request->header('company');
                    if (gettype($tax['amount']) !== "NULL") {
                        $item->taxes()->create($tax);
                    }
                }
            }
        }

        if ($request->has('taxes')) {
            foreach ($request->taxes as $tax) {
                $tax['company_id'] = $request->header('company');

                if (gettype($tax['amount']) !== "NULL") {
                    $invoice->taxes()->create($tax);
                }
            }
        }

        $invoice = Invoice::with(['items', 'user', 'invoiceTemplate', 'taxes'])->find($invoice->id);

        return response()->json([
            'url' => url('/invoices/pdf/' . $invoice->unique_hash),
            'invoice' => $invoice,
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if ($invoice->payments()->exists() && $invoice->payments()->count() > 0) {
            return response()->json([
                'error' => 'payment_attached'
            ]);
        }

        $invoice = Invoice::destroy($id);

        return response()->json([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            $invoice = Invoice::find($id);

            if ($invoice->payments()->exists() && $invoice->payments()->count() > 0) {
                return response()->json([
                    'error' => 'payment_attached'
                ]);
            }
        }

        $invoice = Invoice::destroy($request->id);

        return response()->json([
            'success' => true
        ]);
    }



    /**
     * Mail a specific invoice to the correponding cusitomer's email address.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvoice(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);

        $data['invoice'] = $invoice->toArray();
        $userId = $data['invoice']['user_id'];
        $data['user'] = User::find($userId)->toArray();
        $data['company'] = Company::find($invoice->company_id);
        $email = $data['user']['email'];
        $notificationEmail = CompanySetting::getSetting(
            'notification_email',
            $request->header('company')
        );

        if (!$email) {
            return response()->json([
                'error' => 'user_email_does_not_exist'
            ]);
        }

        if (!$notificationEmail) {
            return response()->json([
                'error' => 'notification_email_does_not_exist'
            ]);
        }

        \Mail::to($email)->send(new invoicePdf($data, $notificationEmail));

        if ($invoice->status == Invoice::STATUS_DRAFT) {
            $invoice->status = Invoice::STATUS_SENT;
            $invoice->sent = true;
            $invoice->save();
        }


        return response()->json([
            'success' => true
        ]);
    }


    /**
     * Mark a specific invoice as sent.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsSent(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);
        $invoice->status = Invoice::STATUS_SENT;
        $invoice->sent = true;
        $invoice->save();

        return response()->json([
            'success' => true
        ]);
    }


    /**
     * Mark a specific invoice as paid.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);
        $invoice->status = Invoice::STATUS_COMPLETED;
        $invoice->paid_status = Invoice::STATUS_PAID;
        $invoice->due_amount = 0;
        $invoice->save();

        return response()->json([
            'success' => true
        ]);
    }


    /**
     * Retrive a specified user's unpaid invoices from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomersUnpaidInvoices(Request $request)
    {
        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->where('due_amount', '>', 0)
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'invoices' => $invoices
        ]);
    }

    /**
     * Get reference number for invoice
     * In invoice when ledger/master (Sundry debtor) is selected then
     * reference number would be same for that Sundry debtor for whole day
     */
    public function referenceNumber(Request $request)
    {
        $find_today_first_invoice = Invoice::where('invoice_date', Carbon::now()->toDateString())
            ->where('account_master_id', $request->id)
            ->orderBy('id', 'asc')->first();

        return response()->json([
            'invoice' => $find_today_first_invoice
        ]);
    }
}
