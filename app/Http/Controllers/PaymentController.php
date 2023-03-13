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

use App\Models\User;
use App\Http\Requests\PaymentRequest;
use App\Models\Voucher;
use Exception;
use Illuminate\Support\Facades\Log;
use stdClass;
use Validator;

use function MongoDB\BSON\toJSON;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $payments = Payment::with('user', 'invoice', 'master')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->applyFilters($request->only([
                'search',
                // 'payment_number',
                'payment_status',
                'payment_mode',
                'customer_id',
                'orderByField',
                'from_date',
                'to_date',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->select('payments.*', 'users.name', 'invoices.invoice_number')
            ->latest()
            ->paginate($limit);

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        return response()->json([
            'payments' => $payments,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $payment_prefix = CompanySetting::getSetting('payment_prefix', $request->header('company'));
        $payment_num_auto_generate = CompanySetting::getSetting('payment_auto_generate', $request->header('company'));

        $nextPaymentNumberAttribute = null;
        $nextPaymentNumber = Payment::getNextPaymentNumber($payment_prefix, $request->header('company'));

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

        $party_list = AccountMaster::whereIn('groups', ['Bank Accounts', 'Cash-in-Hand'])->get();

        return response()->json([
            'nextPaymentNumberAttribute' => $nextPaymentNumberAttribute,
            'nextPaymentNumber' => $payment_prefix . '-' . $nextPaymentNumber,
            'payment_prefix' => $payment_prefix,
            'usersOfSundryCreditor' => $usersOfSundryCreditor,
            'account_ledger' => $account_ledger,
            'party_list' => $party_list,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaymentRequest $request)
    {
        $payment_date = Carbon::createFromFormat('d/m/Y', $request->payment_date);
        $req_amount = (int)$request->amount;

        $payment_status = 'Draft';

        if ('admin' === Auth::user()->role) {
            $payment_status = 'Done';
        }

        $voucher_ids = [];
        $company_id = (int) $request->header('company');
        $cr_account_master = AccountMaster::where('name', $request->payment_mode['name'])->first();

        //Not passing payment number from front end, so find next number from backend
        $payment_prefix = CompanySetting::getSetting('payment_prefix', $company_id);
        $nextOrderNumber = Payment::getNextPaymentNumber($payment_prefix, $company_id);
        $number_attributes['payment_number'] = $payment_prefix . '-' . $nextOrderNumber;

        try {
            //Create Payment
            $payment = Payment::create([
                'payment_date' => $payment_date,
                'payment_number' => $number_attributes['payment_number'],
                'payment_status' => $payment_status,
                'user_id' => $request->user_id,
                'company_id' => $company_id,
                //'invoice_id' => $request->invoice_id,
                'payment_mode' => $request->payment_mode['name'],
                'amount' => $req_amount,
                'notes' => $request->notes,
                'account_master_id' => $cr_account_master->id,
            ]);

            $dr_account_ledger = AccountLedger::where([
                'account_master_id' => $request->party_list['id'],
                'account' => $request->party_list['name'],
                'company_id' => $company_id,
            ])->firstOrFail();
            $cr_account_ledger = AccountLedger::where([
                'account_master_id' => $request->payment_mode['id'],
                'account' => $request->payment_mode['name'],
                'company_id' => $company_id,
            ])->firstOrFail();

            $dr_voucher = Voucher::create([
                'account_master_id' => $request->party_list['id'],
                'account' => $request->party_list['name'],
                'credit' => 0,
                'debit' => $req_amount,
                'account_ledger_id' => $dr_account_ledger->id,
                'date' => $payment_date,
                'related_voucher' => null,
                'type' => 'Dr',
                'company_id' => $company_id,
                'voucher_type' => 'Payment',
                'payment_id' => $payment->id,
            ]);
            $cr_voucher = Voucher::create([
                'account_master_id' => $request->payment_mode['id'],
                'account' => $request->payment_mode['name'],
                'credit' => $req_amount,
                'debit' => 0,
                'account_ledger_id' => $cr_account_ledger->id,
                'date' => $payment_date,
                'related_voucher' => null,
                'type' => 'Cr',
                'company_id' => $company_id,
                'voucher_type' => 'Payment',
                'payment_id' => $payment->id,
            ]);

            $voucher_ids = $cr_voucher->id . ', ' . $dr_voucher->id;
            $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();

            foreach ($voucher as $key => $each) {
                if ($key < substr_count($voucher_ids, ',') + 1) {
                    $each->update([
                        'related_voucher' => $voucher_ids,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error while saving payment', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payment = Payment::with([
            'user',
            ])->find($id);

        $siteData = [
            'payment' => $payment,
            'shareable_link' => url('/payments/pdf/' . $payment->id)
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
        $payment = Payment::with('user', 'invoice', 'master')->find($id);

        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->where('user_id', $payment->user_id)->where('due_amount', '>', 0)
            ->whereCompany($request->header('company'))
            ->get();

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

        $party_list = AccountMaster::whereIn('groups', ['Bank Accounts', 'Cash-in-Hand'])->get();

        return response()->json([
            'nextPaymentNumber' => $payment->getPaymentNumAttribute(),
            'payment_prefix' => $payment->getPaymentPrefixAttribute(),
            'usersOfSundryCreditor' => $usersOfSundryCreditor,
            'payment' => $payment,
            'invoices' => $invoices,
            'account_ledger' => $account_ledger,
            'party_list' => $party_list,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PaymentRequest $request, $id)
    {
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

            $invoice->status = Invoice::DISPATCH;
            $invoice->paid_status = Invoice::STATUS_PAID;
            $invoice->save();
        }

        $payment->payment_date = $payment_date;
        //$payment->payment_number = $number_attributes['payment_number'];
        $payment->payment_status = $request->payment_status;
        $payment->user_id = $request->user_id;
        $payment->invoice_id = $request->invoice_id;
        $payment->payment_mode = $request->party_list['name'];
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if ($payment->invoice_id != null) {
            $invoice = Invoice::find($payment->invoice_id);
            $invoice->due_amount = ((int)$invoice->due_amount + (int)$payment->amount);
            $invoice->paid_status = Invoice::STATUS_PAID;
            $invoice->status = Invoice::TO_BE_DISPATCH;
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
                $invoice->paid_status = Invoice::STATUS_PAID;
                $invoice->status = Invoice::TO_BE_DISPATCH;
                $invoice->save();
            }

            $payment->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
