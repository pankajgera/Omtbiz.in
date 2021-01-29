<?php

namespace Crater\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Crater\CompanySetting;
use Crater\Currency;
use Crater\Invoice;
use Crater\Payment;
use Carbon\Carbon;
use Crater\AccountLedger;
use Crater\AccountMaster;

use function MongoDB\BSON\toJSON;
use Crater\User;
use Crater\Http\Requests\PaymentRequest;
use Crater\Voucher;
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

        $usersOfSundryCreditor = AccountMaster::where('groups', 'like', 'Sundry Creditors')->select('name')->get();

        return response()->json([
            'customers' => User::where('role', 'customer')
                ->whereCompany($request->header('company'))
                ->get(),
            'nextPaymentNumberAttribute' => $nextPaymentNumberAttribute,
            'nextPaymentNumber' => $payment_prefix . '-' . $nextPaymentNumber,
            'payment_prefix' => $payment_prefix,
            'usersOfSundryCreditor' => $usersOfSundryCreditor,
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

        if ($request->payment_mode !== 'Cash') {
            $account_master = AccountMaster::where('name', 'Bank')->first();
            $account_ledger = AccountLedger::create([
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'account' => 'Bank',
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $request->amount,
                'account_master_id' => $account_master->id,
                'type' => 'Cr',
                'company_id' => $company_id,
            ]);
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master->id,
                'account' => $request->list,
                'debit' => $request->amount,
                'credit' => 0,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $account_master->id,
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
            $account_master = AccountMaster::where('name', 'Cash')->first();
            $account_ledger = AccountLedger::create([
                'date' => Carbon::now()->toDateTimeString(),
                'bill_no' => null,
                'account' => $request->list,
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $request->amount,
                'account_master_id' => $account_master->id,
                'type' => 'Cr',
                'company_id' => $company_id,
            ]);
            $voucher_1 = Voucher::create([
                'account_master_id' => $account_master->id,
                'account' => $request->list,
                'debit' => $request->amount,
                'credit' => 0,
                'account_ledger_id' => $account_ledger->id,
                'date' => Carbon::now()->toDateTimeString(),
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id
            ]);
            $voucher_2 = Voucher::create([
                'account_master_id' => $account_master->id,
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
        $account_ledger->update([
            'bill_no' => $voucher_ids,
        ]);
        foreach ($voucher as $key => $each) {
            if ($key < substr_count($voucher_ids, ',') + 1) {
                $each->update([
                    'related_voucher' => $voucher_ids,
                ]);
            }
        }

        $payment = Payment::create([
            'payment_date' => $payment_date,
            //'payment_number' => $number_attributes['payment_number'],
            'payment_status' => $payment_status,
            'user_id' => $request->user_id,
            'company_id' => $company_id,
            //'invoice_id' => $request->invoice_id,
            'payment_mode' => $request->payment_mode,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

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
