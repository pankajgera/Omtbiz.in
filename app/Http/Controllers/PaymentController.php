<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use App\Models\AccountLedger;
use App\Models\AccountMaster;

use function MongoDB\BSON\toJSON;
use App\Models\User;
use App\Http\Requests\PaymentRequest;
use App\Models\Voucher;
use stdClass;
use Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $payments = Payment::with('user', 'invoice')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->applyFilters($request->only([
                'search',
                // 'payment_number',
                'payment_status',
                'payment_mode',
                'customer_id',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'))
            ->select('payments.*', 'users.name', 'invoices.invoice_number')
            ->latest()
            ->paginate($limit);

        return response()->json([
            'payments' => $payments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $payment_prefix = CompanySetting::getSetting('payment_prefix', $request->header('company'));
        $payment_num_auto_generate = CompanySetting::getSetting('payment_auto_generate', $request->header('company'));


        $nextPaymentNumberAttribute = null;
        $nextPaymentNumber = Payment::getNextPaymentNumber($payment_prefix);

        if ($payment_num_auto_generate == "YES") {
            $nextPaymentNumberAttribute = $nextPaymentNumber;
        }

        $usersOfSundryCreditor = AccountMaster::where('groups', 'like', 'Sundry Creditors')->select('id', 'name', 'opening_balance', 'type')->get();

        $account_ledger = [];
        foreach ($usersOfSundryCreditor as $master) {
            $ledger = AccountLedger::where('account_master_id', $master->id)->first();
            $obj = new stdClass();
            $obj->id = $master->id;
            $obj->balance = isset($ledger) ? $ledger->balance : 0;
            $obj->type = isset($ledger) ? $ledger->type : 'Cr';
            array_push($account_ledger, $obj);
        }

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextPaymentNumberAttribute' => $nextPaymentNumberAttribute,
            'nextPaymentNumber' => $payment_prefix . '-' . $nextPaymentNumber,
            'payment_prefix' => $payment_prefix,
            'usersOfSundryCreditor' => $usersOfSundryCreditor,
            'account_ledger' => $account_ledger,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentRequest $request)
    {
        // $payment_number = explode("-", $request->payment_number);
        // $number_attributes['payment_number'] = $payment_number[0] . '-' . sprintf('%06d', intval($payment_number[1]));

        // Validator::make($number_attributes, [
        //     'payment_number' => 'required|unique:payments,payment_number'
        // ])->validate();

        $payment_date = Carbon::createFromFormat('d/m/Y', $request->payment_date);
        $req_amount = (int)$request->amount;

        // if ($request->has('invoice_id') && $request->invoice_id != null) {
        //     $invoice = Invoice::find($request->invoice_id);
        //     if ($invoice && $invoice->due_amount == $request->amount) {
        //         $invoice->status = Invoice::STATUS_COMPLETED;
        //         $invoice->paid_status = Invoice::STATUS_PAID;
        //         $invoice->due_amount = 0;
        //     } elseif ($invoice && $invoice->due_amount != $request->amount) {
        //         $invoice->due_amount = (int)$invoice->due_amount - (int)$request->amount;
        //         if ($invoice->due_amount < 0) {
        //             return response()->json([
        //                 'error' => 'invalid_amount'
        //             ]);
        //         }
        //         $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
        //     }
        //     $invoice->save();
        // }

        $payment_status = 'Draft';

        if ('admin' === Auth::user()->role) {
            $payment_status = 'Done';
        }

        $voucher_ids = [];
        $company_id = (int) $request->header('company');
        $account_master_id = (int) $request->list['id'];
        $cash_account = AccountMaster::where('name', 'Cash')->first();
        $bank_account = AccountMaster::where('name', 'Bank')->first();

        //Create Payment
        $payment = Payment::create([
            'payment_date' => $payment_date,
            //'payment_number' => $number_attributes['payment_number'],
            'payment_status' => $payment_status,
            'user_id' => $request->user_id,
            'company_id' => $company_id,
            //'invoice_id' => $request->invoice_id,
            'payment_mode' => $request->payment_mode,
            'amount' => $req_amount,
            'notes' => $request->notes,
            'account_master_id' => $account_master_id,
        ]);

        $dr_account_ledger = AccountLedger::firstOrCreate([
            'account_master_id' => $account_master_id,
            'account' => $request->list['name'],
            'company_id' => $company_id,
        ], [
            'date' => Carbon::now()->toDateTimeString(),
            'bill_no' => null,
            'debit' => $req_amount,
            'type' => 'Dr',
            'credit' => 0,
            'balance' => $req_amount,
        ]);
        //AccountMaster::updateOpeningBalance($account_master_id, $request->closing_balance);

        if ($request->payment_mode !== 'Cash') {
            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $bank_account->id,
                'account' => 'Bank',
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'debit' => 0,
                'type' => 'Cr',
                'credit' => $req_amount,
                'balance' => $req_amount,
            ]);
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'debit' => $req_amount,
                'credit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $bank_account->id,
                'account' => 'Bank',
                'debit' => 0,
                'credit' => $req_amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id
            ]);
        } else {
            $account_ledger = AccountLedger::firstOrCreate([
                'account_master_id' => $cash_account->id,
                'account' => 'Cash',
                'company_id' => $company_id,
            ], [
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'type' => 'Cr',
                'debit' => 0,
                'credit' => $req_amount,
                'balance' => $req_amount,
            ]);
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master_id,
                'account' => $request->list['name'],
                'debit' => $req_amount,
                'credit' => 0,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $cash_account->id,
                'account' => 'Cash',
                'debit' => 0,
                'credit' => $req_amount,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id
            ]);
        }
        $voucher_ids = $voucher_1->id . ', ' . $voucher_2->id;
        $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();

        //Only update for existing ledger else new one with bill_no
        $account_ledger->update([
            'credit' => $account_ledger->credit > $req_amount ? $account_ledger->credit - $req_amount : $req_amount - $account_ledger->credit,
            'bill_no' => $account_ledger->bill_no ? $account_ledger->bill_no . ',' . $voucher_ids : $voucher_ids,
        ]);
        $dr_account_ledger->update([
            'debit' => $dr_account_ledger->debit > $req_amount ? $dr_account_ledger->debit - $req_amount : $req_amount - $dr_account_ledger->debit,
            'bill_no' => $account_ledger->bill_no ? $dr_account_ledger->bill_no . ',' . $voucher_ids : $voucher_ids,
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
            'payment' => $payment,
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
        $payment = Payment::with('user', 'invoice')->find($id);

        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->where('user_id', $payment->user_id)->where('due_amount', '>', 0)
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextPaymentNumber' => $payment->getPaymentNumAttribute(),
            'payment_prefix' => $payment->getPaymentPrefixAttribute(),
            'payment' => $payment,
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
    public function update(PaymentRequest $request, $id)
    {
        // $payment_number = explode("-", $request->payment_number);
        // $number_attributes['payment_number'] = $payment_number[0] . '-' . sprintf('%06d', intval($payment_number[1]));

        // Validator::make($number_attributes, [
        //     'payment_number' => 'required|unique:payments,payment_number' . ',' . $id
        // ])->validate();

        $payment_date = Carbon::createFromFormat('d/m/Y', $request->payment_date);

        $payment = Payment::find($id);
        $oldAmount = $payment->amount;

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

        $payment->payment_date = $payment_date;
        //$payment->payment_number = $number_attributes['payment_number'];
        $payment->payment_status = $request->payment_status;
        $payment->user_id = $request->user_id;
        $payment->invoice_id = $request->invoice_id;
        $payment->payment_mode = $request->payment_mode;
        $payment->amount = $request->amount;
        $payment->notes = $request->notes;
        $payment->save();

        return response()->json([
            'payment' => $payment,
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
        $payment = Payment::find($id);

        if ($payment->invoice_id != null) {
            $invoice = Invoice::find($payment->invoice_id);
            $invoice->due_amount = ((int)$invoice->due_amount + (int)$payment->amount);

            if ($invoice->due_amount == $invoice->total) {
                $invoice->paid_status = Invoice::STATUS_UNPAID;
            } else {
                $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
            }

            $invoice->status = $invoice->getPreviousStatus();
            $invoice->save();
        }

        $payment->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            $payment = Payment::find($id);

            if ($payment->invoice_id != null) {
                $invoice = Invoice::find($payment->invoice_id);
                $invoice->due_amount = ((int)$invoice->due_amount + (int)$payment->amount);

                if ($invoice->due_amount == $invoice->total) {
                    $invoice->paid_status = Invoice::STATUS_UNPAID;
                } else {
                    $invoice->paid_status = Invoice::STATUS_PARTIALLY_PAID;
                }

                $invoice->status = $invoice->getPreviousStatus();
                $invoice->save();
            }

            $payment->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
