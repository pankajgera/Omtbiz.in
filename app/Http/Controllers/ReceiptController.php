<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Receipt;
use Carbon\Carbon;
use App\Models\AccountLedger;
use App\Models\AccountMaster;

use App\Models\User;
use App\Http\Requests\ReceiptRequest;
use App\Models\Voucher;
use stdClass;
use Validator;

use function MongoDB\BSON\toJSON;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $receipts = Receipt::with('user')
            ->join('users', 'users.id', '=', 'receipts.user_id')
            ->leftJoin('account_masters', 'account_masters.id', '=', 'receipts.account_master_id')
            ->applyFilters($request->only([
                'search',
                'receipt_status',
                'receipt_mode',
                'customer_id',
                'orderByField',
                'orderBy',
                'from_date',
                'to_date',
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->select('receipts.*', 'users.name', 'account_masters.name')
            ->latest()
            ->paginate($limit);

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        return response()->json([
            'receipts' => $receipts,
            'total' => Receipt::count(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $receipt_prefix = CompanySetting::getSetting('receipt_prefix', $request->header('company'));
        $receipt_num_auto_generate = CompanySetting::getSetting('receipt_auto_generate', $request->header('company'));

        $nextReceiptNumberAttribute = null;
        $nextReceiptNumber = Receipt::getNextReceiptNumber($receipt_prefix, $request->header('company'));

        if ($receipt_num_auto_generate == "YES") {
            $nextReceiptNumberAttribute = $nextReceiptNumber;
        }

        $usersOfSundryDebitors = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'type')->get();

        $account_ledger = [];
        foreach ($usersOfSundryDebitors as $master) {
            $ledger = AccountLedger::where('account_master_id', $master->id)->first();
            $obj = new stdClass();
            $obj->id = $master->id;
            $obj->balance = isset($ledger) ? $ledger->balance : 0;
            $obj->type = isset($ledger) ? $ledger->type : 'Cr';
            array_push($account_ledger, $obj);
        }

        $receipt_mode = AccountMaster::whereIn('groups', ['Bank Accounts', 'Cash-in-Hand'])->get();

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextReceiptNumberAttribute' => $nextReceiptNumberAttribute,
            'nextReceiptNumber' => $receipt_prefix . '-' . $nextReceiptNumber,
            'receipt_prefix' => $receipt_prefix,
            'usersOfSundryDebitors' => $usersOfSundryDebitors,
            'account_ledger' => $account_ledger,
            'receipt_mode' => $receipt_mode,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ReceiptRequest $request)
    {
        $receipt_number = explode("-", $request->receipt_number);
        $number_attributes['receipt_number'] = $receipt_number[0] . '-' . sprintf('%06d', intval($receipt_number[1]));
        $req_amount = (int)$request->amount;

        Validator::make($number_attributes, [
            'receipt_number' => 'required|unique:receipts,receipt_number'
        ])->validate();

        //Check if same receipt number is already present
        //if YES, then add 1 to this receipt number
        $find_receipt = Receipt::where('receipt_number', '=', $request->receipt_number)->first();
        if (! empty($find_receipt)) {
            $receipt_prefix = CompanySetting::getSetting('receipt_prefix', $request->header('company'));
            $nextOrderNumber = Receipt::getNextReceiptNumber($receipt_prefix, $request->header('company'));
            $number_attributes['receipt_number'] = $receipt_prefix . '-' . $nextOrderNumber;
        }

        $receipt_date = Carbon::createFromFormat('d/m/Y', $request->receipt_date);

        if ($request->has('invoice_id') && $request->invoice_id != null) {
            $invoice = Invoice::find($request->invoice_id);
            $invoice->status = Invoice::DISPATCH;
            $invoice->paid_status = Invoice::STATUS_PAID;
            $invoice->due_amount = 0;
            $invoice->save();
        }

        $receipt_status = 'Draft';

        if ('admin' === Auth::user()->role) {
            $receipt_status = 'Done';
        }
        $voucher_ids = [];
        $voucher_1 = null;
        $voucher_2 = null;
        $company_id = (int) $request->header('company');
        $account_master_id = (int) $request->list['id'];
        $cash_account_id = AccountMaster::firstOrCreate([
            'name' => 'Cash',
        ], [
            'address' => null,
            'groups' => 'Cash-in-Hand',
            'country' => 'IN',
            'state' => 'Rajasthan',
            'opening_balance' => 0,
            'type' => 'Cr',
        ])->id;
        $bank_account_id = AccountMaster::firstOrCreate([
            'name' => 'Bank',
        ], [
            'address' => null,
            'groups' => 'Bank Accounts',
            'country' => 'IN',
            'state' => 'Rajasthan',
            'opening_balance' => 0,
            'type' => 'Cr',
        ])->id;

        //Create receipt
        $receipt = Receipt::create([
            'receipt_date' => $receipt_date,
            'receipt_number' => $number_attributes['receipt_number'],
            'receipt_status' => $receipt_status,
            'user_id' => $request->user_id,
            'company_id' => $company_id,
            'invoice_id' => $request->invoice_id,
            'receipt_mode' => $request->receipt_mode,
            'amount' => $req_amount,
            'notes' => $request->notes,
            'account_master_id' => $account_master_id
        ]);

        $dr_account_ledger = AccountLedger::firstOrCreate([
            'account_master_id' => $account_master_id,
            'account' => $request->list['name'],
            'company_id' => $company_id,
        ], [
            'date' => Carbon::now()->toDateTimeString(),
            'debit' => $req_amount,
            'type' => 'Dr',
            'credit' => 0,
            'balance' => $req_amount,
        ]);

        if ($request->receipt_mode !== 'Cash in Hand') {
            $account_ledger = AccountLedger::where([
                'account_master_id' => $bank_account_id,
                'account' => $request->receipt_mode,
                'company_id' => $company_id,
            ])->first();

            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'credit' => $req_amount,
                'debit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => $receipt_date,
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id,
                'voucher_type' => 'Receipt',
                'receipt_id' => $receipt->id,
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $bank_account_id,
                'account' => $request->receipt_mode,
                'credit' => 0,
                'debit' => $req_amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => $receipt_date,
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id,
                'voucher_type' => 'Receipt',
                'receipt_id' => $receipt->id,
            ]);
        } else {
            $account_ledger = AccountLedger::where([
                'account_master_id' => $cash_account_id,
                'account' => $request->receipt_mode,
                'company_id' => $company_id,
            ])->first();
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'credit' => $req_amount,
                'debit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => $receipt_date,
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id,
                'voucher_type' => 'Receipt',
                'receipt_id' => $receipt->id,
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $cash_account_id,
                'account' => $request->receipt_mode,
                'credit' => 0,
                'debit' => $req_amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => $receipt_date,
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id,
                'voucher_type' => 'Receipt',
                'receipt_id' => $receipt->id,
            ]);
        }

        $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
        $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();

        $account_ledger->update([
            'credit' => $account_ledger->credit > $req_amount ? $account_ledger->credit - $req_amount : $req_amount - $account_ledger->credit,
        ]);
        $dr_account_ledger->update([
            'debit' => $dr_account_ledger->debit > $req_amount ? $dr_account_ledger->debit - $req_amount : $req_amount - $dr_account_ledger->debit,
        ]);

        //Update ledger balance by calculating credit/debit
        $calc_cr_balance = $account_ledger->debit > $account_ledger->credit ? $account_ledger->debit - $account_ledger->credit : $account_ledger->credit - $account_ledger->debit;
        $calc_dr_balance = $dr_account_ledger->credit > $dr_account_ledger->debit ? $dr_account_ledger->credit - $dr_account_ledger->debit : $dr_account_ledger->debit - $dr_account_ledger->credit;
        $account_ledger->update([
            'balance' => $calc_cr_balance,
        ]);
        $dr_account_ledger->update([
            'balance' => $calc_dr_balance,
        ]);
        foreach ($voucher as $key => $each) {
            if ($key < substr_count($voucher_ids, ',') + 1) {
                $each->update([
                    'related_voucher' => $voucher_ids,
                ]);
            }
        }

        return response()->json([
            'receipt' => $receipt,
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $receipt = Receipt::with([
            'user',
            ])->find($id);

        $siteData = [
            'receipt' => $receipt,
            'shareable_link' => url('/receipts/pdf/' . $receipt->id)
        ];

        return response()->json($siteData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $receipt = Receipt::with('user', 'invoice')->find($id);

        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->where('user_id', $receipt->user_id)->where('due_amount', '>', 0)
            ->whereCompany($request->header('company'))
            ->get();

        $usersOfSundryDebitors = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'type')->get();

        $receipt_prefix = CompanySetting::getSetting('receipt_prefix', $request->header('company'));
        $receipt_num_auto_generate = CompanySetting::getSetting('receipt_auto_generate', $request->header('company'));
        $nextReceiptNumberAttribute = null;
        $nextReceiptNumber = Receipt::getNextReceiptNumber($receipt_prefix, $request->header('company'));
        if ($receipt_num_auto_generate == "YES") {
            $nextReceiptNumberAttribute = $nextReceiptNumber;
        }

        $account_ledger = [];
        foreach ($usersOfSundryDebitors as $master) {
            $ledger = AccountLedger::where('account_master_id', $master->id)->first();
            $obj = new stdClass();
            $obj->id = $master->id;
            $obj->balance = isset($ledger) ? $ledger->balance : 0;
            $obj->type = isset($ledger) ? $ledger->type : 'Cr';
            array_push($account_ledger, $obj);
        }

        $receipt_mode = AccountMaster::where('groups', 'Bank Accounts')->where('groups', 'Cash-in-Hand')->get();

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextReceiptNumber' => $receipt->getReceiptNumAttribute(),
            'receipt_prefix' => $receipt->getReceiptPrefixAttribute(),
            'receipt' => $receipt,
            'invoices' => $invoices,
            'usersOfSundryDebitors' => $usersOfSundryDebitors,
            'account_ledger' => $account_ledger,
            'receipt_mode' => $receipt_mode,
            'nextReceiptNumberAttribute' => $nextReceiptNumberAttribute,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ReceiptRequest $request, $id)
    {
        $receipt_date = Carbon::createFromFormat('d/m/Y', $request->receipt_date);

        $receipt = Receipt::find($id);
        $oldAmount = $receipt->amount;

        if ($request->has('invoice_id') && $request->invoice_id && ($oldAmount != $request->amount)) {
            $amount = (int)$request->amount - (int)$oldAmount;
            $invoice = Invoice::find($request->invoice_id);
            $invoice->due_amount = (int)$invoice->due_amount - (int)$amount;

            if ($invoice->due_amount < 0) {
                return response()->json([
                    'error' => 'invalid_amount'
                ]);
            }

            $invoice->status = Invoice::DISPATCH;
            $invoice->paid_status = Invoice::STATUS_PAID;
            $invoice->save();
        }

        $receipt->receipt_date = $receipt_date;
        //$receipt->receipt_number = $number_attributes['receipt_number'];
        $receipt->receipt_status = $request->receipt_status;
        $receipt->user_id = $request->user_id;
        $receipt->invoice_id = $request->invoice_id;
        $receipt->receipt_mode = $request->receipt_mode;
        $receipt->amount = $request->amount;
        $receipt->notes = $request->notes;
        $receipt->save();

        return response()->json([
            'receipt' => $receipt,
            'success' => true
        ]);
    }

    /**
     * Remove the specified receipt with vouchers
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $receipt = Receipt::find($id);

        if ($receipt->invoice_id != null) {
            $invoice = Invoice::find($receipt->invoice_id);
            $invoice->due_amount = ((int)$invoice->due_amount + (int)$receipt->amount);
            $invoice->paid_status = Invoice::STATUS_PAID;
            $invoice->status = Invoice::TO_BE_DISPATCH;
            $invoice->save();
        }

        $vouchers = Voucher::where('receipt_id', $id)->get();
        foreach($vouchers as $each) {
            $each->delete();
        }

        $receipt->delete();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete mulitple receipt with vouchers
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            $receipt = Receipt::find($id);

            if ($receipt->invoice_id != null) {
                $invoice = Invoice::find($receipt->invoice_id);
                $invoice->due_amount = ((int)$invoice->due_amount + (int)$receipt->amount);
                $invoice->paid_status = Invoice::STATUS_PAID;
                $invoice->status = Invoice::TO_BE_DISPATCH;
                $invoice->save();
            }

            $vouchers = Voucher::where('receipt_id', $id)->get();
            foreach($vouchers as $each) {
                $each->delete();
            }

            $receipt->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
