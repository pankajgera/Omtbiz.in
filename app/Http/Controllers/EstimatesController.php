<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateTemplate;
use Carbon\Carbon;
use App\Http\Requests\EstimatesRequest;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\User;
use App\Models\Item;
use Validator;
use App\Models\CompanySetting;
use App\Models\Company;
use App\Mail\EstimatePdf;
use App\Models\AccountMaster;
use App\Models\Inventory;
use App\Models\TaxType;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;

class EstimatesController extends Controller
{
    /**
     * Index page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $estimates_inprogress = Estimate::where('status', 'DRAFT')->with([
            'items',
            'master'
        ])->join('users', 'users.id', '=', 'estimates.user_id')
            ->applyFilters($request->only([
                'status',
                'customer_id',
                'estimate_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->select('estimates.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $estimates_completed = Estimate::where('status', 'SENT')->with([
            'items',
            'master'
        ])->join('users', 'users.id', '=', 'estimates.user_id')
            ->applyFilters($request->only([
                'status',
                'customer_id',
                'estimate_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'))
            ->select('estimates.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $siteData = [
            'estimates_draft' => $estimates_inprogress,
            'estimates_sent' => $estimates_completed,
            'draft_count' => Estimate::where('status', 'DRAFT')->count(),
            'sent_count' => Estimate::where('status', 'SENT')->count(),
        ];

        return response()->json($siteData);
    }

    /**
     * Create new estimate page data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $estimate_prefix = CompanySetting::getSetting('estimate_prefix', $request->header('company'));
        $estimate_num_auto_generate = CompanySetting::getSetting('estimate_auto_generate', $request->header('company'));

        $nextEstimateNumberAttribute = null;
        $nextEstimateNumber = Estimate::getNextEstimateNumber($estimate_prefix);

        if ($estimate_num_auto_generate == "YES") {
            $nextEstimateNumberAttribute = $nextEstimateNumber;
        }

        $tax_per_item = CompanySetting::getSetting('tax_per_item', $request->header('company'));
        $discount_per_item = CompanySetting::getSetting('discount_per_item', $request->header('company'));
        $customers = User::where('role', 'customer')->get();

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'estimate_today_date' => Carbon::now()->toDateString(),
            'customers' => $customers,
            'inventories' => Inventory::query()->get(),
            'nextEstimateNumberAttribute' => $nextEstimateNumberAttribute,
            'nextEstimateNumber' => $estimate_prefix . '-' . $nextEstimateNumber,
            'taxes' => Tax::whereCompany($request->header('company'))->latest()->get(),
            'tax_per_item' => $tax_per_item,
            'discount_per_item' => $discount_per_item,
            'estimateTemplates' => EstimateTemplate::all(),
            'shareable_link' => '',
            'estimate_prefix' => $estimate_prefix,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Create new estimate
     *
     * @param EstimatesRequest $request
     * @return JsonResponse
     */
    public function store(EstimatesRequest $request)
    {
        $estimate_number = $request->estimate_number;
        $number_attributes['estimate_number'] = $request->estimate_number;

        Validator::make($number_attributes, [
            'estimate_number' => 'required'
        ])->validate();

        $estimate_date = Carbon::createFromFormat('d/m/Y', $request->estimate_date);
        $status = Estimate::DRAFT;
        $tax_per_item = CompanySetting::getSetting(
            'tax_per_item',
            $request->header('company')
        ) ? CompanySetting::getSetting(
            'tax_per_item',
            $request->header('company')
        ) : 'NO';

        $discount_per_item = CompanySetting::getSetting(
            'discount_per_item',
            $request->header('company')
        ) ? CompanySetting::getSetting(
            'discount_per_item',
            $request->header('company')
        ) : 'NO';

        $estimate = Estimate::create([
            'estimate_date' => $estimate_date,
            'expiry_date' => $estimate_date,
            'estimate_number' => $number_attributes['estimate_number'],
            //'reference_number' => $request->reference_number,
            'user_id' => $request->user_id,
            'company_id' => $request->header('company'),
            'estimate_template_id' => $request->estimate_template_id,
            'status' => $status,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'discount_val' => $request->discount_val,
            'sub_total' => $request->sub_total,
            'total' => $request->total,
            'tax_per_item' => $tax_per_item,
            'discount_per_item' => $discount_per_item,
            'tax' => $request->tax,
            'notes' => $request->notes,
            'unique_hash' => str_random(60),
            'account_master_id' => $request->debtors['id'],
        ]);

        $estimateItems = $request->items;

        foreach ($estimateItems as $estimateItem) {
            $estimateItem['company_id'] = $request->header('company');
            $estimateItem['type'] = 'estimate';
            $estimateItem['price'] = $estimateItem['price'];
            $estimateItem['sale_price'] = $estimateItem['sale_price'];
            $item = $estimate->items()->create($estimateItem);

            if (array_key_exists('taxes', $estimateItem) && $estimateItem['taxes']) {
                foreach ($estimateItem['taxes'] as $tax) {
                    if (gettype($tax['amount']) !== "NULL") {
                        $tax['company_id'] = $request->header('company');
                        $item->taxes()->create($tax);
                    }
                }
            }
        }

        if ($request->has('taxes')) {
            foreach ($request->taxes as $tax) {
                if (gettype($tax['amount']) !== "NULL") {
                    $tax['company_id'] = $request->header('company');
                    $estimate->taxes()->create($tax);
                }
            }
        }

        if ($request->has('estimateSend')) {
            $data['estimate'] = $estimate->toArray();
            $userId = $data['estimate']['user_id'];
            $data['user'] = User::find($userId)->toArray();
            $data['company'] = Company::find($estimate->company_id);
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

            \Mail::to($email)->send(new EstimatePdf($data, $notificationEmail));
        }

        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
            'taxes'
        ])->find($estimate->id);

        return response()->json([
            'estimate' => $estimate,
            'url' => url('/estimates/pdf/' . $estimate->unique_hash),
        ]);
    }

    /**
     * Show single estimate
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $estimate = Estimate::with([
            'items',
            'items.taxes',
            'user',
            'estimateTemplate',
            'taxes',
            'taxes.taxType'
        ])->find($id);

        $siteData = [
            'estimate' => $estimate,
            'shareable_link' => url('/estimates/pdf/' . $estimate->unique_hash)
        ];

        return response()->json($siteData);
    }

    /**
     * Edit single estimate
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $estimate = Estimate::with([
            'items',
            'items.taxes',
            'user',
            'estimateTemplate',
            'taxes',
            'taxes.taxType'
        ])->find($id);
        $customers = User::where('role', 'customer')->get();
        $sundryDebtorsList = AccountMaster::where('id', $estimate->account_master_id)->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'customers' => $customers,
            'inventories' => Inventory::query()->get(),
            'estimateNumber' => $estimate->getEstimateNumAttribute(),
            'taxes' => Tax::latest()->whereCompany($request->header('company'))->get(),
            'estimate' => $estimate,
            'estimateTemplates' => EstimateTemplate::all(),
            'tax_per_item' => $estimate->tax_per_item,
            'discount_per_item' => $estimate->discount_per_item,
            'shareable_link' => url('/estimates/pdf/' . $estimate->unique_hash),
            'estimate_prefix' => $estimate->getEstimatePrefixAttribute(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Update single estimate
     *
     * @param EstimatesRequest $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function update(EstimatesRequest $request, $id)
    {
        // $estimate_number = explode("-", $request->estimate_number);
        // $number_attributes['estimate_number'] = $estimate_number[0] . '-' . sprintf('%06d', intval($estimate_number[1]));
        // Validator::make($number_attributes, [
        //     'estimate_number' => 'required|unique:estimates,estimate_number' . ',' . $id
        // ])->validate();

        // $estimate_date = Carbon::createFromFormat('d/m/Y', $request->estimate_date);
        // $expiry_date = Carbon::createFromFormat('d/m/Y', $request->estimate_date);

        $estimate = Estimate::findOrFail($id);
        // $estimate->estimate_date = $estimate_date;
        // $estimate->expiry_date = $expiry_date;
        // $estimate->estimate_number = $number_attributes['estimate_number'];
        // $estimate->reference_number = $request->reference_number;
        // $estimate->user_id = $request->user_id;
        // $estimate->estimate_template_id = $request->estimate_template_id;
        $estimate->discount = $request->discount;
        $estimate->discount_type = $request->discount_type;
        $estimate->discount_val = $request->discount_val;
        $estimate->sub_total = $request->sub_total;
        $estimate->total = $request->total;
        $estimate->tax = $request->tax;
        $estimate->notes = $request->notes;
        $estimate->save();

        $oldItems = $estimate->items->toArray();
        $oldTaxes = $estimate->taxes->toArray();
        $estimateItems = $request->items;

        foreach ($oldItems as $oldItem) {
            EstimateItem::destroy($oldItem['id']);
        }

        foreach ($oldTaxes as $oldTax) {
            Tax::destroy($oldTax['id']);
        }

        foreach ($estimateItems as $estimateItem) {
            $estimateItem['company_id'] = $request->header('company');
            $estimateItem['type'] = 'estimate';
            $estimateItem['price'] = $estimateItem['price'];
            $estimateItem['sale_price'] = $estimateItem['sale_price'];
            $item = $estimate->items()->create($estimateItem);

            if (array_key_exists('taxes', $estimateItem) && $estimateItem['taxes']) {
                foreach ($estimateItem['taxes'] as $tax) {
                    if (gettype($tax['amount']) !== "NULL") {
                        $tax['company_id'] = $request->header('company');
                        $item->taxes()->create($tax);
                    }
                }
            }
        }

        if ($request->has('taxes')) {
            foreach ($request->taxes as $tax) {
                if (gettype($tax['amount']) !== "NULL") {
                    $tax['company_id'] = $request->header('company');
                    $estimate->taxes()->create($tax);
                }
            }
        }

        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
            'taxes'
        ])->find($estimate->id);

        return response()->json([
            'estimate' => $estimate,
            'url' => url('/estimates/pdf/' . $estimate->unique_hash),
        ]);
    }

    /**
     * Delete single estimate
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        Estimate::deleteEstimate($id);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Send estimate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEstimate(Request $request)
    {
        $estimate = Estimate::findOrFail($request->id);

        $data['estimate'] = $estimate->toArray();
        $userId = $data['estimate']['user_id'];
        $data['user'] = User::find($userId)->toArray();
        $data['company'] = Company::find($estimate->company_id);

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

        \Mail::to($email)->send(new EstimatePdf($data, $notificationEmail));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Estimate to invoice
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function estimateToInvoice(Request $request, $id)
    {
        $estimate = Estimate::with(['items', 'items.taxes', 'user', 'estimateTemplate', 'taxes'])->find($id);
        $invoice_date = Carbon::parse($estimate->estimate_date);
        $due_date = Carbon::parse($estimate->estimate_date)->addDays(7);
        $tax_per_item = CompanySetting::getSetting(
            'tax_per_item',
            $request->header('company')
        ) ? CompanySetting::getSetting(
            'tax_per_item',
            $request->header('company')
        ) : 'NO';
        $discount_per_item = CompanySetting::getSetting(
            'discount_per_item',
            $request->header('company')
        ) ? CompanySetting::getSetting(
            'discount_per_item',
            $request->header('company')
        ) : 'NO';
        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));

        $invoice = Invoice::create([
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'invoice_number' => "INV-" . Invoice::getNextInvoiceNumber($invoice_prefix),
            //'reference_number' => $estimate->reference_number,
            'user_id' => $estimate->user_id,
            'company_id' => $request->header('company'),
            'invoice_template_id' => 1,
            'status' => Invoice::TO_BE_DISPATCH,
            'paid_status' => Invoice::STATUS_PAID,
            'sub_total' => $estimate->sub_total,
            'discount' => $estimate->discount,
            'discount_type' => $estimate->discount_type,
            'discount_val' => $estimate->discount_val,
            'total' => $estimate->total,
            'due_amount' => $estimate->total,
            'tax_per_item' => $tax_per_item,
            'discount_per_item' => $discount_per_item,
            'tax' => $estimate->tax,
            'notes' => $estimate->notes,
            'unique_hash' => str_random(60)
        ]);

        $invoiceItems = $estimate->items->toArray();

        foreach ($invoiceItems as $invoiceItem) {
            $invoiceItem['company_id'] = $request->header('company');
            $invoiceItem['name'] = $invoiceItem['name'];
            $item = $invoice->items()->create($invoiceItem);

            if (array_key_exists('taxes', $invoiceItem) && $invoiceItem['taxes']) {
                foreach ($invoiceItem['taxes'] as $tax) {
                    $tax['company_id'] = $request->header('company');

                    if ($tax['amount']) {
                        $item->taxes()->create($tax);
                    }
                }
            }
        }

        if ($estimate->taxes) {
            foreach ($estimate->taxes->toArray() as $tax) {
                $tax['company_id'] = $request->header('company');
                $invoice->taxes()->create($tax);
            }
        }

        $invoice = Invoice::with([
            'items',
            'user',
            'invoiceTemplate',
            'taxes'
        ])->find($invoice->id);

        return response()->json([
            'invoice' => $invoice
        ]);
    }

    /**
     * Delete estimate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            Estimate::deleteEstimate($id);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
