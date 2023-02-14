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

            $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
            return response()->json([
                'invoices' => $invoices,
                'sundryDebtorsList' => $sundryDebtorsList,
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
        $discount_per_item = CompanySetting::getSetting('discount_per_item', $request->header('company'));
        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));
        $invoice_num_auto_generate = CompanySetting::getSetting('invoice_auto_generate', $request->header('company'));
        $inventory_negative = CompanySetting::getSetting('allow_negative_inventory', $request->header('company'));
        $nextInvoiceNumberAttribute = null;
        $nextInvoiceNumber = Invoice::getNextInvoiceNumber($invoice_prefix, $request->header('company'));

        if ($invoice_num_auto_generate == "YES") {
            $nextInvoiceNumberAttribute = $nextInvoiceNumber;
        }

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        $estimateList = Estimate::where('company_id', $request->header('company'))->where('status', '!=', 'SENT')->select('id', 'estimate_number', 'total', 'account_master_id')->get();

        return response()->json([
            'invoice_today_date' => Carbon::now()->toDateString(),
            'nextInvoiceNumberAttribute' => $nextInvoiceNumberAttribute,
            'nextInvoiceNumber' =>  $invoice_prefix . '-' . $nextInvoiceNumber,
            'inventories' => Inventory::query()->get(),
            'invoiceTemplates' => InvoiceTemplate::all(),
            'discount_per_item' => $discount_per_item,
            'invoice_prefix' => $invoice_prefix,
            'sundryDebtorsList' => $sundryDebtorsList,
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

            $invoice_date = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('d-m-Y');
            //$due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
            $status = Invoice::TO_BE_DISPATCH;

            $discount_per_item = CompanySetting::getSetting('discount_per_item', $request->header('company')) ?? 'NO';

            $invoice = Invoice::create([
                'invoice_date' => $invoice_date,
                //'due_date' => $due_date,
                'invoice_number' => $number_attributes['invoice_number'],
                'reference_number' => $request->reference_number,
                'user_id' => $request->user_id,
                'company_id' => $request->header('company'),
                'invoice_template_id' => $request->invoice_template_id,
                'status' => Invoice::TO_BE_DISPATCH,
                'paid_status' => Invoice::STATUS_PAID,
                'sub_total' => $request->sub_total,
                'discount' => $request->discount,
                'discount_type' => $request->discount_type,
                'discount_val' => $request->discount_val,
                'total' => $request->total,
                'due_amount' => $request->total,
                'discount_per_item' => $discount_per_item,
                'notes' => $request->notes,
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
                $inventory = $invoice->inventories()->create($invoiceInventory);

                $inventory_id = $inventory->id;
                //Reset inventory quantity
                $invent = InventoryItem::where('inventory_id', $inventory->inventory_id)->where('quantity', '!=', 0)->orderBy('id', 'desc')->get();
                $quantity_used = (int) ($inventory->quantity);
                if (0 < count($invent)) {
                    foreach($invent as $each_invent_item) {
                        if ($quantity_used < $each_invent_item->quantity) {
                            $each_invent_item->update([
                                'quantity' => $each_invent_item->quantity - $quantity_used,
                            ]);
                            break;
                        }
                        //update quantity, it should decrease by item current quantity
                        $original = $quantity_used;
                        $quantity_used = $quantity_used - $each_invent_item->quantity;
                        \Log::info('quantity_used', [$quantity_used, $original]);
                        if(0 < $quantity_used) {
                            \Log::info('IN', [$quantity_used]);
                            $each_invent_item->update([
                                'quantity' => 0,
                            ]);
                            continue;
                        }
                        if (0 === $original) {
                            break;
                        }
                        if (0 < $original) {
                            $each_invent_item->update([
                                'quantity' => $quantity_used,
                            ]);
                        }
                        if (0 > $original) {
                            $each_invent_item->update([
                                'quantity' => $original - $quantity_used,
                            ]);
                        }
                    }
                }
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
            $total_amount = (int) ($request->total);
            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $sale_account->id,
                'account' => 'Sales',
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
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
                'bill_no' => null,
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
                'date' => Carbon::now()->toDateTimeString(),
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
                'date' => Carbon::now()->toDateTimeString(),
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
            if ($account_ledger->bill_no) {
                $account_ledger->update([
                    'credit' => $account_ledger->credit + $total_amount,
                    'balance' => $account_ledger->balance + $total_amount,
                    'bill_no' => $account_ledger->bill_no . ',' . $voucher_ids,
                ]);
                $dr_account_ledger->update([
                    'debit' => $dr_account_ledger->debit + $total_amount,
                    'balance' => $dr_account_ledger->balance + $total_amount,
                    'bill_no' => $dr_account_ledger->bill_no . ',' . $voucher_ids,
                ]);
            } else {
                $account_ledger->update([
                    'bill_no' => $voucher_ids,
                ]);
                $dr_account_ledger->update([
                    'bill_no' => $voucher_ids,
                ]);
            }
            foreach ($voucher as $key => $each) {
                if ($key < substr_count($voucher_ids, ',') + 1) {
                    $each->update([
                        'related_voucher' => $voucher_ids,
                    ]);
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

            $invoice = Invoice::with(['inventories', 'user', 'invoiceTemplate'])->find($invoice->id);

            if ($invoice) {
                //Update estimate
                if ($request->estimate) {
                    Estimate::where('id', $request->estimate['id'])->update([
                        'status' => 'SENT',
                        'reference_number' => $invoice->invoice_number,
                    ]);
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
        $sundryDebtorsList = AccountMaster::where('id', $invoice->account_master_id)->select('id', 'name', 'opening_balance')->get();
        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));
        $inventory_negative = CompanySetting::getSetting('allow_negative_inventory', $request->header('company'));
        $estimateList = Estimate::where('company_id', $request->header('company'))->select('id', 'estimate_number', 'total')->get();
        $find_invoice_estimate = Estimate::where('reference_number', $invoice->invoice_number)->get();
        if (0 < count($find_invoice_estimate)) {
            $estimateList = $find_invoice_estimate;
        }
        $number = explode("-", $invoice->invoice_number);
        $number = $number[2];

        return response()->json([
            'invoiceNumber' =>   $number,
            'invoice' => $invoice,
            'invoiceTemplates' => InvoiceTemplate::all(),
            'shareable_link' => url('/invoices/pdf/' . $invoice->unique_hash),
            'sundryDebtorsList' => $sundryDebtorsList,
            'estimateList' => $estimateList,
            'InvoiceEstimate' => $find_invoice_estimate,
            'invoice_prefix' => $invoice_prefix,
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
            $oldAmount = (int)round($request->total) - (int)$oldAmount;
        } else {
            $oldAmount = 0;
        }

        $invoice->due_amount = ($invoice->due_amount + $oldAmount);

        if ($invoice->due_amount == 0 && $invoice->paid_status != Invoice::STATUS_PAID) {
            $invoice->paid_status = Invoice::STATUS_PAID;
        } elseif ($invoice->due_amount < 0) {
            return response()->json([
                'error' => 'invalid_due_amount'
            ]);
        }

        $invoice->status = $request->status;
        $invoice->sub_total = $request->sub_total;
        $invoice->total = $request->total;
        $invoice->discount = $request->discount;
        $invoice->discount_type = $request->discount_type;
        $invoice->discount_val = $request->discount_val;
        $invoice->notes = $request->notes;
        $invoice->save();

        $requestInvoiceItems = $request->inventories;

        //Add journal entry
        //It will be "Sales" type
        $sale_account_id = AccountMaster::where('name', 'Sales')->first()->id;
        $company_id = (int) $request->header('company');
        $account_master_id = (int) $request->debtors['id'];
        $total_amount = (int) ($request->total);
        $account_ledger = AccountLedger::firstOrCreate([
            'account_master_id' => $sale_account_id,
            'account' => 'Sales',
            'company_id' => $company_id,
        ], [
            'date' => Carbon::now()->toDateTimeString(),
            'bill_no' => null,
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
            'bill_no' => null,
            'debit' => $total_amount,
            'type' => 'Dr',
            'credit' => 0,
            'balance' => $total_amount,
        ]);

        $total_invoice_items_amount = 0;
        $existing_invoice_items = [];
        foreach ($requestInvoiceItems as $req) {
            $req['company_id'] = $request->header('company');
            $req['type'] = 'invoice';
            $total_invoice_items_amount = $total_invoice_items_amount + $req['total'];
            $new_invoice_item = null;

            //Existing invoice items, other items in database should be deleted
            array_push($existing_invoice_items, $req['id']);

            //Reset inventory quantity
            //Add, if quantity is reduce in the existing invoice item
            //Subtract, if quantity is add in the existing invoice item
            if ($req['invoice_id']) {
                $invent = InventoryItem::where('inventory_id', $req['inventory_id'])->orderBy('id', 'desc')->get();
                $request_item_quantity = (int) ($req['quantity']);
                $existing_invoice_item = InvoiceItem::findOrFail($req['id']);
                $difference_between_updated_invoice_item_quantity = $request_item_quantity - $existing_invoice_item->quantity;
                if (0 < count($invent)) {
                    foreach($invent as $each_invent_item) {
                        $absolute_quantity = ((int) $difference_between_updated_invoice_item_quantity);
                        //-ve means we have to decrease
                        if (0 > $difference_between_updated_invoice_item_quantity) {
                            if ($each_invent_item->quantity > $absolute_quantity) {
                                $decrease = $each_invent_item->quantity - $absolute_quantity;
                                $each_invent_item->update([
                                    'quantity' => $decrease,
                                ]);
                                break;
                            }
                            $absolute_quantity = $absolute_quantity - $each_invent_item->quantity;
                            $each_invent_item->update([
                                'quantity' => $absolute_quantity,
                            ]);
                            break;
                        }
                        //+ve means we have to increase
                        $increase = $each_invent_item->quantity + $absolute_quantity;
                        $each_invent_item->update([
                            'quantity' => $increase,
                        ]);
                        break;
                    }
                }
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
                $new_invoice_item = InvoiceItem::create([
                    'name' => $req['name'],
                    'price' => $req['price'],
                    'sale_price' => $req['sale_price'],
                    'total' => $req['total'],
                    'inventory_id' => $req['inventory_id'],
                    'invoice_id' => $req['id'],
                    'company_id' => $req['company_id'],
                    'quantity' => (int) ($req['quantity']),
                    'type' => 'invoice',
                    'discount_type' => 'fixed',
                    'discount' => 0,
                ]);

                //update inventory quantity
                $invent = InventoryItem::where('inventory_id', $new_invoice_item->inventory_id)->where('quantity', '!=', 0)->orderBy('id', 'desc')->get();
                $invent_quantity = (int) ($invent->sum('quantity'));
                $request_item_quantity = (int) ($req['quantity']);
                $updated_quantity = $invent_quantity - $new_invoice_item->quantity;
                if (0 < count($invent)) {
                    foreach($invent as $each_invent_item) {
                        $new_quantity = $updated_quantity;
                        if ($each_invent_item->quantity < $new_quantity) {
                            $new_quantity = $new_quantity - $each_invent_item->quantity;
                            $each_invent_item->update([
                                'quantity' => $new_quantity,
                            ]);
                            continue;
                        }
                        $new_quantity = $each_invent_item->quantity - $new_quantity;
                        $each_invent_item->update([
                            'quantity' => $new_quantity,
                        ]);
                        break;
                    }
                }
            }
        }

        //Delete removed invoice items from database
        $delete_invoice_items = InvoiceItem::where('invoice_id', $invoice->id)->whereNotIn('id', $existing_invoice_items)->get();
        foreach ($delete_invoice_items as $del) {
            $find_invent = InventoryItem::where('inventory_id', $del->inventory_id)->orderBy('id', 'desc')->first();
            //Add deleting item quantity back to inventory
            $find_invent->update([
                'quantity' => $find_invent->quantity + $del->quantity,
            ]);
            $del->delete();
        }

        $amount = $total_invoice_items_amount;
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
            'date' => Carbon::now()->toDateTimeString(),
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
            'date' => Carbon::now()->toDateTimeString(),
            'related_voucher' => null,
            'type' => 'Cr',
        ]);

        //Now update vouchers id to ledger-bill-no and related_voucher
        $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
        $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();
        if ($account_ledger->bill_no) {
            $account_ledger->update([
                'credit' => $account_ledger->credit + $amount,
                'balance' => $account_ledger->balance + $amount,
                'bill_no' => $account_ledger->bill_no . ',' . $voucher_ids,
            ]);
            $dr_account_ledger->update([
                'debit' => $dr_account_ledger->debit + $amount,
                'balance' => $dr_account_ledger->balance + $amount,
                'bill_no' => $dr_account_ledger->bill_no . ',' . $voucher_ids,
            ]);
        } else {
            $account_ledger->update([
                'bill_no' => $voucher_ids,
            ]);
            $dr_account_ledger->update([
                'bill_no' => $voucher_ids,
            ]);
        }
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

        $vouchers = Voucher::where('invoice_id', $id)->get();
        foreach ($vouchers as $each) {
            $each->delete();
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
        }

        $vouchers = Voucher::where('invoice_id', $id)->get();
        foreach ($vouchers as $each) {
            $each->delete();
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
        $find_today_first_invoice = Invoice::where('invoice_date', Carbon::now('Asia/Kolkata')->toDateString())
            ->where('account_master_id', $request->id)
            ->orderBy('id', 'asc')->first();

        return response()->json([
            'invoice' => $find_today_first_invoice
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
