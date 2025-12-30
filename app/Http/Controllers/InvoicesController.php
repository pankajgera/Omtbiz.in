<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use App\Http\Requests;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use App\Models\AccountMaster;
use App\Models\Inventory;
use App\Models\Item;
use App\Mail\invoicePdf;
use App\Models\AccountLedger;
use App\Models\Dispatch;
use App\Models\Estimate;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Validator;
use App\Models\Voucher;
use App\Services\NumberSequenceService;
use Exception;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 20;

            $invoices = Invoice::with(['inventories', 'user', 'invoiceTemplate', 'master'])
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
                ->whereCompany($request->header('company'), $request['filterBy'])
                ->select('invoices.*', 'users.name')
                ->latest()
                ->paginate($limit);

            $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'mobile_number')->get();

            $income_indirect_ledgers = AccountMaster::where('groups', 'like', 'Income (Indirect)')->select('id', 'name', 'opening_balance')->get();
            $expense_indirect_ledgers = AccountMaster::where('groups', 'like', 'Expenses (Indirect)')->select('id', 'name', 'opening_balance')->get();
            return response()->json([
                'invoices' => $invoices,
                'sundryDebtorsList' => $sundryDebtorsList,
                'incomeIndirectLedgers' => $income_indirect_ledgers,
                'expenseIndirectLedgers' => $expense_indirect_ledgers,
                'invoiceTotalCount' => Invoice::count()
            ]);
        } catch (Exception $e) {
            Log::error('Error while getting invoice index ', [$e]);
        }
        return response()->json(500);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulk(Request $request)
    {
        try {
            $limit = 500;
            $invoices = Invoice::with(['inventories', 'user', 'invoiceTemplate', 'master'])
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
                ->whereCompany($request->header('company'), $request['filterBy'])
                ->select('invoices.*', 'users.name')
                ->orderBy('id', 'asc')
                ->paginate($limit);

            $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'mobile_number')->get();

            $income_indirect_ledgers = AccountMaster::where('groups', 'like', 'Income (Indirect)')->select('id', 'name', 'opening_balance')->get();
            $expense_indirect_ledgers = AccountMaster::where('groups', 'like', 'Expenses (Indirect)')->select('id', 'name', 'opening_balance')->get();
            return response()->json([
                'invoices' => $invoices,
                'sundryDebtorsList' => $sundryDebtorsList,
                'incomeIndirectLedgers' => $income_indirect_ledgers,
                'expenseIndirectLedgers' => $expense_indirect_ledgers,
                'invoiceTotalCount' => Invoice::count()
            ]);
        } catch (Exception $e) {
            Log::error('Error while getting invoice index ', [$e]);
        }
        return response()->json(500);
    }

    /**
     * GET - Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $invoice_num_auto_generate = CompanySetting::getSetting('invoice_auto_generate', $request->header('company'));
        $inventory_negative = CompanySetting::getSetting('allow_negative_inventory', $request->header('company'));
        $nextInvoiceNumberAttribute = null;
        $sequence = NumberSequenceService::getNextSequence(
            (int) $request->header('company'),
            Carbon::now('Asia/Kolkata')
        );
        $nextInvoiceNumber = $sequence['invoice_number'];

        if ($invoice_num_auto_generate == "YES") {
            $nextInvoiceNumberAttribute = $sequence['suffix'];
        }

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'mobile_number')->get();
        $estimateList = Estimate::where('company_id', $request->header('company'))->where('status', '!=', 'SENT')->select('id', 'estimate_number', 'total', 'account_master_id')->get();

        $income_indirect_ledgers = AccountMaster::where('groups', 'like', 'Income (Indirect)')->select('id', 'name', 'opening_balance')->get();
        $expense_indirect_ledgers = AccountMaster::where('groups', 'like', 'Expenses (Indirect)')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'invoice_today_date' => Carbon::now()->toDateString(),
            'nextInvoiceNumberAttribute' => $nextInvoiceNumberAttribute,
            'nextInvoiceNumber' =>  $nextInvoiceNumber,
            'invoiceTemplates' => InvoiceTemplate::all(),
            'invoice_prefix' => $sequence['prefix'],
            'reference_prefix' => '',
            'sundryDebtorsList' => $sundryDebtorsList,
            'incomeIndirectLedgers' => $income_indirect_ledgers,
            'expenseIndirectLedgers' => $expense_indirect_ledgers,
            'estimateList' => $estimateList,
            'inventory_negative' => ('YES' === $inventory_negative),
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
            $number_attributes['invoice_number'] = $request->invoice_number;
            Validator::make($number_attributes, [
                'invoice_number' => 'required'
            ])->validate();

            $invoice_date = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');
            $company_id = (int) $request->header('company');
            $sequence = NumberSequenceService::getNextSequence(
                $company_id,
                Carbon::createFromFormat('Y-m-d', $invoice_date, 'Asia/Kolkata')
            );
            while (
                Invoice::where('invoice_number', $sequence['invoice_number'])->exists()
                || Estimate::where('estimate_number', $sequence['invoice_number'])->exists()
            ) {
                [$series, $left, $right] = NumberSequenceService::incrementSequence(
                    $sequence['series'],
                    $sequence['left'],
                    $sequence['right']
                );
                $sequence = NumberSequenceService::buildSequence($sequence['year'], $series, $left, $right);
            }
            $reference_number = NumberSequenceService::getReferenceNumberForAccount(
                $company_id,
                $request->debtors['id'],
                Carbon::createFromFormat('Y-m-d', $invoice_date, 'Asia/Kolkata')
            );
            if (! $reference_number) {
                $reference_number = $sequence['reference_number'];
            }

            $invoice = Invoice::create([
                'invoice_date' => $invoice_date,
                //'due_date' => $due_date,
                'invoice_number' => $sequence['invoice_number'],
                'reference_number' => $reference_number,
                'user_id' => $request->user_id,
                'company_id' => $company_id,
                'invoice_template_id' => $request->invoice_template_id,
                'status' => Invoice::TO_BE_DISPATCH,
                'paid_status' => Invoice::STATUS_PAID,
                'sub_total' => $request->sub_total,
                'total' => $request->total,
                'due_amount' => $request->total,
                'notes' => $request->notes,
                'indirect_income' =>  $request->income_ledger ? $request->income_ledger['name'] : null,
                'indirect_income_value' => $request->income_ledger_value,
                'indirect_expense' => $request->expense_ledger ? $request->expense_ledger['name'] : null,
                'indirect_expense_value' => $request->expense_ledger_value,
                'unique_hash' => str_random(60),
                'account_master_id' => $request->debtors['id'],
            ]);

            //Added dispatch bill
            $dispatch = new Dispatch();
            $dispatch->name = $invoice->invoice_number;
            $dispatch->invoice_id = $invoice->id;
            $dispatch->date_time = Carbon::now('Asia/Kolkata');
            $dispatch->transport = null;
            $dispatch->status = 'Draft';
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            $invoice->update([
                'dispatch_id' => $dispatch->id,
                'paid_status' => 'TO_BE_DISPATCH',
            ]);

            //Now for each inventory item create journal entry
            $invoiceInventories = $request->inventories;
            $inventory_id = null;
            foreach ($invoiceInventories as $invoiceInventory) {
                $invoiceInventory['company_id'] = $request->header('company');
                $invoiceInventory['type'] = 'invoice';
                $invoiceInventory['quantity'] = normalize_second_last_decimal($invoiceInventory['quantity']);
                $invoiceInventory['price'] = normalize_second_last_decimal($invoiceInventory['price']);
                $invoiceInventory['sale_price'] = normalize_second_last_decimal($invoiceInventory['sale_price'] ?? $invoiceInventory['price']);
                $inventory = $invoice->inventories()->create($invoiceInventory);

                $inventory_id = $inventory->id;
                //Reset inventory quantity
                $quantity_used = (float) ($inventory->quantity);
                $invent = Inventory::where('id', $inventory->inventory_id)->first();
                $invent->update([
                    'quantity' => $invent->quantity - $quantity_used,
                ]);
            }

            //Add journal entry
            //It will be "Sales" type
            $sale_account = AccountMaster::where('name', 'Sales')->first();
            if (!$sale_account) {
                $sale_account = AccountMaster::create([
                    'name' => 'Sales',
                    'groups' => 'Sales Accounts',
                    'address' => '-',
                    'country' => '-',
                    'state' => '-',
                    'opening_balance' => '0',
                    'type' => 'Cr',
                ]);
            }
            $company_id = (int) $request->header('company');
            $account_master_id = (int) $request->debtors['id'];
            $total_amount = (float) $request->total;

            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $sale_account->id,
                'account' => 'Sales',
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'type' => 'Cr',
                'debit' => 0,
                'credit' => $total_amount,
                'balance' => $total_amount,
            ]);
            $dr_account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $account_master_id,
                'account' => $request->debtors['name'],
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'debit' => $total_amount,
                'type' => 'Dr',
                'credit' => 0,
                'balance' => $total_amount,
            ]);

            //Handle vouchers
            //Add journal entry
            //It will add voucher for sales from invoice
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->debtors['name'],
                'debit' => $total_amount,
                'credit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => $invoice_date,
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id,
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $inventory_id,
                'voucher_type' => 'Sales',
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $sale_account->id,
                'account' => 'Sales',
                'debit' => 0,
                'credit' => $total_amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => $invoice_date,
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id,
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $inventory_id,
                'voucher_type' => 'Sales',
            ]);

            //Now update vouchers id to ledger-bill-no and related_voucher
            $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
            $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();
            $account_ledger->update([
                'credit' => $account_ledger->credit + $total_amount,
                'balance' => $account_ledger->balance + $total_amount,
            ]);
            $dr_account_ledger->update([
                'debit' => $dr_account_ledger->debit + $total_amount,
                'balance' => $dr_account_ledger->balance + $total_amount,
            ]);
            foreach ($voucher as $key => $each) {
                if ($key < substr_count($voucher_ids, ',') + 1) {
                    $each->update([
                        'related_voucher' => $voucher_ids,
                    ]);
                }
            }

            $invoice = Invoice::with(['inventories', 'user', 'invoiceTemplate'])->find($invoice->id);

            if ($invoice) {
                //Update estimate
                if ($request->estimate) {
                    Estimate::where('id', $request->estimate['id'])->update([
                        'status' => 'SENT',
                    ]);

                    // update notifications
                    $notifications = auth()->user()->notifications()
                    ->whereNull('read_at')
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get();

                    foreach($notifications as $notifi) {
                        $data = $notifi['data'];
                        if($data['id'] === (int)$request->estimate['id']) {
                            $notifi->update([
                                'read_at' => Carbon::now()
                            ]);

                            break;
                        }
                    }
                }

                return response()->json([
                    'url' => url('/invoices/pdf/' . $invoice->unique_hash),
                    'invoice' => $invoice
                ]);
            }

            return response()->json(204);
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
            'inventories',
            'user',
            'invoiceTemplate',
        ])->findOrFail($id);

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
            'user',
            'invoiceTemplate',
        ])->find($id);
        $income_indirect_ledgers = AccountMaster::where('groups', 'like', 'Income (Indirect)')->select('id', 'name', 'opening_balance')->get();
        $expense_indirect_ledgers = AccountMaster::where('groups', 'like', 'Expenses (Indirect)')->select('id', 'name', 'opening_balance')->get();
        $sundryDebtorsList = AccountMaster::where('id', $invoice->account_master_id)->select('id', 'name', 'opening_balance', 'mobile_number')->get();
        $invoice_prefix = $invoice->getInvoicePrefixAttribute() . '-';
        $reference_prefix = '';
        $inventory_negative = CompanySetting::getSetting('allow_negative_inventory', $request->header('company'));
        $estimateList = Estimate::where('company_id', $request->header('company'))->select('id', 'estimate_number', 'total')->get();
        $find_invoice_estimate = Estimate::where('reference_number', $invoice->reference_number)->get();
        if (0 < count($find_invoice_estimate)) {
            $estimateList = $find_invoice_estimate;
        }
        $number = $invoice->getInvoiceNumAttribute();

        return response()->json([
            'invoiceNumber' =>   $number,
            'invoice' => $invoice,
            'incomeIndirectLedgers' => $income_indirect_ledgers,
            'expenseIndirectLedgers' => $expense_indirect_ledgers,
            'invoiceTemplates' => InvoiceTemplate::all(),
            'shareable_link' => url('/invoices/pdf/' . $invoice->unique_hash),
            'sundryDebtorsList' => $sundryDebtorsList,
            'estimateList' => $estimateList,
            'InvoiceEstimate' => $find_invoice_estimate,
            'invoice_prefix' => $invoice_prefix,
            'reference_prefix' => $reference_prefix,
            'inventory_negative' => ('YES' === $inventory_negative),
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
        $invoice = Invoice::findOrFail($id);
        $oldAmount = $invoice->total;

        if ($oldAmount != $request->total) {
            $oldAmount = round((float) $request->total, 2) - (float) $oldAmount;
        } else {
            $oldAmount = 0.0;
        }

        $invoice->due_amount = ($invoice->due_amount + $oldAmount);

        if ($invoice->due_amount == 0 && $invoice->paid_status != Invoice::STATUS_PAID) {
            $invoice->paid_status = Invoice::STATUS_PAID;
        } elseif ($invoice->due_amount < 0) {
            return response()->json([
                'error' => 'invalid_due_amount'
            ]);
        }
        $invoice_date = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');

        $invoice->status = $request->status;
        $invoice->invoice_date = $invoice_date;
        $invoice->sub_total = $request->sub_total;
        $invoice->total = $request->total;
        $invoice->notes = $request->notes;
        $invoice->indirect_income = $request->income_ledger ? $request->income_ledger['name'] : null;
        $invoice->indirect_income_value = $request->income_ledger_value;
        $invoice->indirect_expense = $request->expense_ledger ? $request->expense_ledger['name'] : null;
        $invoice->indirect_expense_value = $request->expense_ledger_value;

        $invoice->save();

        $requestInvoiceItems = $request->inventories;

        //Add journal entry
        //It will be "Sales" type
        $sale_account_id = AccountMaster::where('name', 'Sales')->first()->id;
        $company_id = (int) $request->header('company');
        $account_master_id = (int) $request->debtors['id'];
        $total_amount = (float) $request->total;
        $account_ledger = AccountLedger::firstOrCreate([
            'account_master_id' => $sale_account_id,
            'account' => 'Sales',
            'company_id' => $company_id,
        ], [
            'date' => Carbon::now()->toDateTimeString(),
            'type' => 'Cr',
            'debit' => 0,
            'credit' => $total_amount,
            'balance' => $total_amount,
        ]);
        $dr_account_ledger = AccountLedger::firstOrCreate([
            'account_master_id' => $account_master_id,
            'account' => $request->debtors['name'],
            'company_id' => $company_id,
        ], [
            'date' => Carbon::now()->toDateTimeString(),
            'debit' => $total_amount,
            'type' => 'Dr',
            'credit' => 0,
            'balance' => $total_amount,
        ]);

        $total_invoice_items_amount = 0;
        $changed_invoice_items = [];
        foreach ($requestInvoiceItems as $req) {
            $req['company_id'] = $request->header('company');
            $req['type'] = 'invoice';
            $req['quantity'] = normalize_second_last_decimal($req['quantity']);
            $req['price'] = normalize_second_last_decimal($req['price']);
            $req['sale_price'] = normalize_second_last_decimal($req['sale_price'] ?? $req['price']);
            $total_invoice_items_amount = $total_invoice_items_amount + $req['total'];
            $new_invoice_item = null;

            //Reset inventory quantity
            //Add, if quantity is reduce in the existing invoice item
            //Subtract, if quantity is add in the existing invoice item
            $request_item_quantity = (float) ($req['quantity']);
            if ($req['invoice_id']) {
                $invent = Inventory::where('id', $req['inventory_id'])->first();
                $existing_invoice_item = InvoiceItem::find($req['id']);

                //If user didn't changed quantity but changed something else
                //In that case we don't update inventory
                if ($request_item_quantity !== $invent->quantity) {
                    $calc_quantity = $request_item_quantity > $existing_invoice_item->quantity ?
                        $request_item_quantity - $existing_invoice_item->quantity :
                        $existing_invoice_item->quantity - $request_item_quantity;
                    $calc_quantity = $calc_quantity > $invent->quantity ?
                        $calc_quantity - $invent->quantity :
                        $invent->quantity - $calc_quantity;
                    $invent->update([
                        'quantity' => $calc_quantity,
                    ]);
                }

                //Existing invoice items, other items in database should be deleted
                array_push($changed_invoice_items, $existing_invoice_item['id']);

                //Update existing invoice item, just in case user had changed anything
                $existing_invoice_item->update([
                    'name' => $req['name'],
                    'price' => $req['price'],
                    'sale_price' => $req['sale_price'],
                    'total' => $req['total'],
                    'inventory_id' => $req['inventory_id'],
                    'quantity' => $request_item_quantity,
                ]);
            } else {
                //Create/add new invoice items
                $existing_invoice_item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'inventory_id' => $req['inventory_id'],
                    'name' => $req['name'],
                    'price' => $req['price'],
                    'sale_price' => $req['sale_price'],
                    'total' => $req['total'],
                    'company_id' => $req['company_id'],
                    'quantity' => $request_item_quantity,
                    'type' => 'invoice',
                ]);

                //Existing invoice items, other items in database should be deleted
                array_push($changed_invoice_items, $existing_invoice_item['id']);

                //update inventory quantity
                $invent = Inventory::where('id', $req['inventory_id'])->first();
                $invent->update([
                    'quantity' => $invent->quantity - $request_item_quantity,
                ]);
            }
        }

        //Delete removed invoice items from database
        $delete_invoice_items = InvoiceItem::where('invoice_id', $invoice->id)->whereNotIn('id', $changed_invoice_items)->get();
        foreach ($delete_invoice_items as $del) {
            //'Add' deleting item quantity back to inventory
            $find_invent = Inventory::where('id', $del->inventory_id)->first();
            $find_invent->update([
                'quantity' => $find_invent->quantity + $del->quantity,
            ]);
            $del->delete();
        }

        $amount = $total_amount;
        //It will add voucher for sales from invoice
        $voucher_1 = Voucher::where([
            'account_master_id' => $account_master_id,
            'account' => $request->debtors['name'],
            'company_id' => $company_id,
            'account_ledger_id' => $dr_account_ledger->id,
            'type' => 'Dr',
            'invoice_id' => $invoice->id,
            'voucher_type' => 'Sales',
        ])->first();
        $voucher_1->update([
            'debit' => $amount,
            'credit' => 0,
            'date' => $invoice->invoice_date,
            'related_voucher' => null,
        ]);

        $voucher_2 = Voucher::where([
            'account_master_id' => $sale_account_id,
            'account' => 'Sales',
            'company_id' => $company_id,
            'account_ledger_id' => $account_ledger->id,
            'invoice_id' => $invoice->id,
            'voucher_type' => 'Sales',
        ])->first();
        $voucher_2->update([
            'debit' => 0,
            'credit' => $amount,
            'date' => $invoice->invoice_date,
            'related_voucher' => null,
            'type' => 'Cr',
        ]);

        //Now update vouchers id to ledger-bill-no and related_voucher
        $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
        $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();
        $account_ledger->update([
            'credit' => $account_ledger->credit + $amount,
            'balance' => $account_ledger->balance + $amount,
        ]);
        $dr_account_ledger->update([
            'debit' => $dr_account_ledger->debit + $amount,
            'balance' => $dr_account_ledger->balance + $amount,
        ]);
        foreach ($voucher as $key => $each) {
            $each->update([
                'debit' => $each->type === 'Dr' ? $amount : 0,
                'credit' => $each->type === 'Cr' ? $amount : 0,
            ]);
            if ($key < substr_count($voucher_ids, ',') + 1) {
                $each->update([
                    'related_voucher' => $voucher_ids,
                ]);
            }
        }

        $invoice = Invoice::with(['inventories', 'user', 'invoiceTemplate'])->find($invoice->id);

        return response()->json([
            'url' => url('/invoices/pdf/' . $invoice->unique_hash),
            'invoice' => $invoice,
            'success' => true
        ]);
    }

    /**
     * Remove the specified invoice from storage.
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

        $vouchers = Voucher::where('invoice_id', $invoice->id)->get();
        foreach ($vouchers as $each) {
            $each->delete();
        }

        $invoice_item = InvoiceItem::where('invoice_id', $id)->get();
        foreach ($invoice_item as $each) {
            //'Add' deleting item quantity back to inventory
            $find_invent = Inventory::where('id', $each->inventory_id)->first();
            $find_invent->update([
                'quantity' => $find_invent->quantity + $each->quantity,
            ]);
        }

        $invoice = Invoice::destroy($id);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete invoice
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            $invoice = Invoice::find($id);

            if ($invoice->payments()->exists() && $invoice->payments()->count() > 0) {
                return response()->json([
                    'error' => 'payment_attached',
                ]);
            }

            $vouchers = Voucher::where('invoice_id', $id)->get();
            foreach ($vouchers as $each) {
                $each->delete();
            }

            $invoice_item = InvoiceItem::where('invoice_id', $id)->get();
            foreach ($invoice_item as $each) {
                //'Add' deleting item quantity back to inventory
                $find_invent = Inventory::where('id', $each->inventory_id)->first();
                $find_invent->update([
                    'quantity' => $find_invent->quantity + $each->quantity,
                ]);
            }

            $invoice = Invoice::destroy($id);

        }
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
        $referenceNumber = NumberSequenceService::getReferenceNumberForAccount(
            (int) $request->header('company'),
            (int) $request->id,
            Carbon::now('Asia/Kolkata')
        );

        if (! $referenceNumber) {
            return response()->json([
                'reference_number' => null,
            ]);
        }

        return response()->json([
            'reference_number' => $referenceNumber,
        ]);
    }

    /**
     * Get invoice details from estimate
     *
     * @param Request $request
     * @param Estimate $estimate
     */
    public function getInvoiceEstimate(Request $request, Estimate $estimate)
    {
        $data = Estimate::with('items')->where('id', $estimate->id)->first();

        return response()->json([
            'estimate' => $data
        ]);
    }
}
