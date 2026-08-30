<?php

namespace App\Http\Controllers;

use App\Models\AccountLedger;
use App\Models\AccountMaster;
use App\Models\Invoice;
use App\Models\Voucher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;

class AccountLedgersController extends Controller
{
    /**
     * Get all ledgers
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $ledgers = AccountLedger::with('accountMaster')
            ->applyFilters($request->only([
                'from_date',
                'to_date',
                'account',
                'debit',
                'credit',
                'balance',
            ]))
            ->whereCompany($request->header('company'))
            ->orderBy('account', 'asc')
            ->latest()
            ->paginate($limit);

        $ledgerIds = $ledgers->pluck('id');

        //Update balance according to 'debit' or 'credit', summed per ledger in a single query
        $voucherSums = Voucher::whereIn('account_ledger_id', $ledgerIds)
            ->visibleOutsideApproval()
            ->selectRaw('account_ledger_id, SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->groupBy('account_ledger_id')
            ->get()
            ->keyBy('account_ledger_id');

        foreach ($ledgers as $ledger) {
            $sum = $voucherSums->get($ledger->id);
            $vouchers_debit_sum = $sum ? (float) $sum->debit_sum : 0;
            $vouchers_credit_sum = $sum ? (float) $sum->credit_sum : 0;
            $opening_balance = $ledger->accountMaster->opening_balance;
            $calc_balance = $ledger->balance;
            $calc_type = $ledger->type;
            $calc_total = 0;

            if ($vouchers_debit_sum > $vouchers_credit_sum) {
                $calc_total = $vouchers_debit_sum - $vouchers_credit_sum;
                $calc_type = 'Dr';
            } else {
                $calc_total = $vouchers_credit_sum - $vouchers_debit_sum;
                $calc_type = 'Cr';
            }
            if ('Dr' === $ledger->accountMaster->type) {
                if ('Dr' === $calc_type) {
                    $calc_balance = $calc_total + $opening_balance;
                } else {
                    if ($calc_total > $opening_balance) {
                        $calc_balance = $calc_total - $opening_balance;
                        $calc_type = 'Cr';
                    } else {
                        $calc_balance = $opening_balance - $calc_total;
                        $calc_type = 'Dr';
                    }
                }
            } else {
                if ('Cr' === $calc_type) {
                    $calc_balance = $calc_total + $opening_balance;
                } else {
                    if ($calc_total > $opening_balance) {
                        $calc_balance  = $calc_total - $opening_balance;
                        $calc_type = 'Dr';
                    } else {
                        $calc_balance = $opening_balance - $calc_total;
                        $calc_type = 'Cr';
                    }
                }
            }

            $ledger->update([
                'type' => $calc_type,
                'credit' => $vouchers_credit_sum,
                'debit' => $vouchers_debit_sum,
                'balance' => $calc_balance,
            ]);
        }

        return response()->json([
            'ledgers' => $ledgers,
        ]);
    }

    /**
     * Edit account ledger
     */
    public function edit(Request $request, $id)
    {
        $ledger = AccountLedger::find($id);

        return response()->json([
            'ledger' => $ledger,
        ]);
    }

    /**
     * Get ledgers to display
     */
    public function display(Request $request, $id)
    {
        $form = $request->params;
        $ledger = AccountLedger::findOrFail($id);
        $from = Carbon::parse(str_replace('/', '-', $form['from_date']))->startOfDay();
        $to = Carbon::parse(str_replace('/', '-', $form['to_date']))->endOfDay();

        //Update ledger related data
        $response = AccountLedger::ledgerMutation($ledger, $from, $to);

        return response()->json([
            'vouchers' => $response['related_vouchers'],
            'ledger' => $ledger,
            'account_master' => AccountMaster::where('id', $ledger->account_master_id)->first(),
            'inventory_sum' => $response['inventory_sum'],
            'total_opening_balance_dr' => $response['total_opening_balance_dr'],
            'total_opening_balance_cr' => $response['total_opening_balance_cr'],
            'current_balance_cr' => $response['current_balance_cr'],
            'current_balance_dr' => $response['current_balance_dr'],
            'closing_balance_cr' => $response['closing_balance_cr'],
            'closing_balance_dr' => $response['closing_balance_dr'],
        ]);
    }

    /**
     * Create Account Ledger.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $ledger = new AccountLedger();
            $ledger->date = $request->date;
            $ledger->type = $request->type;
            $ledger->account = $request->account;
            $ledger->debit = $request->debit;
            $ledger->credit = $request->credit;
            $ledger->balance = $request->balance;
            $ledger->short_narration = $request->short_narration;
            $ledger->company_id = $request->header('company');
            $ledger->save();

            $ledger = AccountLedger::find($ledger->id);

            return response()->json([
                'ledger' => $ledger,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account ledger', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing Account Ledger.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $ledger = AccountLedger::find($id);
            $ledger->date = $request->date;
            $ledger->type = $request->type;
            $ledger->account = $request->account;
            $ledger->debit = $request->debit;
            $ledger->credit = $request->credit;
            $ledger->balance = $request->balance;
            $ledger->short_narration = $request->short_narration;
            $ledger->company_id = $request->header('company');
            $ledger->save();

            $ledger = AccountLedger::find($ledger->id);

            return response()->json([
                'ledger' => $ledger,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating account ledger', [$e]);
        }
    }

    /**
     * Delete an existing Account Ledger.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = AccountLedger::deleteAccountLedger($id);

        if (!$data) {
            return response()->json([
                'error' => 'ledger_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Account Ledger.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $ledgers = [];
        foreach ($request->id as $id) {
            $ledger = AccountLedger::deleteAccountLedger($id);
            if (!$ledger) {
                array_push($ledgers, $id);
            }
        }

        if (empty($ledgers)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'ledgers' => $ledgers,
        ]);
    }

      /**
     * Get ledgers to display
     */
    public function daysheet(Request $request, $id)
    {
        $all_voucher_ids = Voucher::whereCompany($request->header('company'))
            ->where('date', Carbon::now()->format('Y-m-d'))
            ->where('account', '!=', 'Sales')
            ->whereNotNull('invoice_id')
            ->visibleOutsideApproval()
            ->groupBy('account_ledger_id')
            ->get();

        $ledgerIds = $all_voucher_ids->pluck('account_ledger_id');

        $lotCounts = Voucher::whereIn('account_ledger_id', $ledgerIds)
            ->where('date', Carbon::now()->format('Y-m-d'))
            ->whereNotNull('invoice_id')
            ->visibleOutsideApproval()
            ->selectRaw('account_ledger_id, COUNT(*) as lot_count')
            ->groupBy('account_ledger_id')
            ->get()
            ->keyBy('account_ledger_id');

        $invoiceIds = $all_voucher_ids->pluck('invoice_id')->filter()->unique();
        $invoices = Invoice::whereIn('id', $invoiceIds)->get()->keyBy('id');

        $ledgers = [];
        foreach ($all_voucher_ids as $each) {
            $lot = $lotCounts->get($each->account_ledger_id);
            $each['lot'] = $lot ? $lot->lot_count : 0;
            $each['party'] = $each->account;
            $invoice = $each->invoice_id ? $invoices->get($each->invoice_id) : null;
            $each['reference_number'] = $invoice ? $invoice->reference_number : null;
            array_push($ledgers, $each);
        }

        return response()->json([
            'ledger' => $ledgers
        ]);
    }
}
