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

use function MongoDB\BSON\toJSON;
use App\Models\User;
use App\Http\Requests\ReceiptRequest;
use App\Models\Voucher;
use stdClass;
use Validator;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $receipts = Receipt::with('user', 'invoice')
            ->join('users', 'users.id', '=', 'receipts.user_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'receipts.invoice_id')
            ->applyFilters($request->only([
                'search',
                'receipt_status',
                'receipt_mode',
                'customer_id',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'))
            ->select('receipts.*', 'users.name', 'invoices.invoice_number')
            ->latest()
            ->paginate($limit);

        return response()->json([
            'receipts' => $receipts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $receipt_prefix = CompanySetting::getSetting('receipt_prefix', $request->header('company'));
        $receipt_num_auto_generate = CompanySetting::getSetting('receipt_auto_generate', $request->header('company'));

        $nextReceiptNumberAttribute = null;
        $nextReceiptNumber = Receipt::getNextReceiptNumber($receipt_prefix);

        if ($receipt_num_auto_generate == "YES") {
            $nextReceiptNumberAttribute = $nextReceiptNumber;
        }

        $usersOfSundryDebitors = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance', 'type')->get();

        $ledger_balance = [];
        foreach($usersOfSundryDebitors as $master) {
            $calc_sum = AccountLedger::where('account_master_id', $master->id)->sum('balance');
            $obj = new stdClass();
            $obj->id = $master->id;
            $obj->balance = $calc_sum;
            array_push($ledger_balance, $obj);
        }
        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextReceiptNumberAttribute' => $nextReceiptNumberAttribute,
            'nextReceiptNumber' => $receipt_prefix . '-' . $nextReceiptNumber,
            'receipt_prefix' => $receipt_prefix,
            'usersOfSundryDebitors' => $usersOfSundryDebitors,
            'ledger_balance' => $ledger_balance,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReceiptRequest $request)
    {
        $receipt_number = explode("-", $request->receipt_number);
        $number_attributes['receipt_number'] = $receipt_number[0] . '-' . sprintf('%06d', intval($receipt_number[1]));

        Validator::make($number_attributes, [
            'receipt_number' => 'required|unique:receipts,receipt_number'
        ])->validate();

        $receipt_date = Carbon::createFromFormat('d/m/Y', $request->receipt_date);

        if ($request->has('invoice_id') && $request->invoice_id != null) {
            $invoice = Invoice::find($request->invoice_id);
            if ($invoice && $invoice->due_amount == $request->amount) {
                $invoice->status = Invoice::STATUS_COMPLETED;
                $invoice->paid_status = Invoice::STATUS_PAID;
                $invoice->due_amount = 0;
            } elseif ($invoice && $invoice->due_amount != $request->amount) {
                $invoice->due_amount = (int)$invoice->due_amount - (int)$request->amount;
                if ($invoice->due_amount < 0) {
                    return response()->json([
                        'error' => 'invalid_amount'
                    ]);
                }
                $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
            }
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
        $cash_account_id = AccountMaster::where('name', 'Cash')->first()->id;
        $bank_account_id = AccountMaster::where('name', 'Bank')->first()->id;
        $dr_account_ledger = AccountLedger::where('account_master_id', $account_master_id)->first();
        if ($request->receipt_mode !== 'Cash') {
            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $bank_account_id,
                'account' => 'Bank',
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'type' => 'Cr',
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $request->amount,
            ]);

            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'debit' => $request->amount,
                'credit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $bank_account_id,
                'account' => 'Bank',
                'debit' => 0,
                'credit' => $request->amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id
            ]);
        } else {
            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'type' => 'Cr',
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $request->amount,
            ]);
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'debit' => $request->amount,
                'credit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $cash_account_id,
                'account' => 'Cash',
                'debit' => 0,
                'credit' => $request->amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id
            ]);
        }

        $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
        $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();
        if ($account_ledger->bill_no) {
            $account_ledger->update([
                'credit' => $account_ledger->credit + (int)$request->amount,
                'balance' => $account_ledger->balance + (int)$request->amount,
                'bill_no' => $account_ledger->bill_no . ',' . $voucher_ids,
            ]);
        } else {
            $account_ledger->update([
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

        $receipt = Receipt::create([
            'receipt_date' => $receipt_date,
            'receipt_number' => $number_attributes['receipt_number'],
            'receipt_status' => $receipt_status,
            'user_id' => $request->user_id,
            'company_id' => $company_id,
            'invoice_id' => $request->invoice_id,
            'receipt_mode' => $request->receipt_mode,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'account_master_id' => $account_master_id
        ]);

        return response()->json([
            'receipt' => $receipt,
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $receipt = Receipt::with('user', 'invoice')->find($id);

        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->where('user_id', $receipt->user_id)->where('due_amount', '>', 0)
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextReceiptNumber' => $receipt->getReceiptNumAttribute(),
            'receipt_prefix' => $receipt->getReceiptPrefixAttribute(),
            'receipt' => $receipt,
            'invoices' => $invoices
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReceiptRequest $request, $id)
    {
        // $receipt_number = explode("-", $request->receipt_number);
        // $number_attributes['receipt_number'] = $receipt_number[0] . '-' . sprintf('%06d', intval($receipt_number[1]));

        // Validator::make($number_attributes, [
        //     'receipt_number' => 'required|unique:receipts,receipt_number' . ',' . $id
        // ])->validate();

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

            if ($invoice->due_amount == 0) {
                $invoice->status = Invoice::STATUS_COMPLETED;
                $invoice->paid_status = Invoice::STATUS_PAID;
            } else {
                $invoice->status = $invoice->getPreviousStatus();
                $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
            }

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $receipt = Receipt::find($id);

        if ($receipt->invoice_id != null) {
            $invoice = Invoice::find($receipt->invoice_id);
            $invoice->due_amount = ((int)$invoice->due_amount + (int)$receipt->amount);

            if ($invoice->due_amount == $invoice->total) {
                $invoice->paid_status = Invoice::STATUS_UNPAID;
            } else {
                $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
            }

            $invoice->status = $invoice->getPreviousStatus();
            $invoice->save();
        }

        $receipt->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            $receipt = Receipt::find($id);

            if ($receipt->invoice_id != null) {
                $invoice = Invoice::find($receipt->invoice_id);
                $invoice->due_amount = ((int)$invoice->due_amount + (int)$receipt->amount);

                if ($invoice->due_amount == $invoice->total) {
                    $invoice->paid_status = Invoice::STATUS_UNPAID;
                } else {
                    $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
                }

                $invoice->status = $invoice->getPreviousStatus();
                $invoice->save();
            }

            $receipt->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
