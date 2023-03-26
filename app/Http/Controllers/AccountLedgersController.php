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

        $ledgers = AccountLedger::applyFilters($request->only([
            'date',
            'account',
            'debit',
            'credit',
            'balance',
        ]))
            ->whereCompany($request->header('company'))
            ->orderBy('account', 'asc')
            ->latest()
            ->paginate($limit);


        foreach ($ledgers as $ledger) {
            $all_voucher_ids = Voucher::where('account_ledger_id', $ledger->id)
                ->whereCompany($request->header('company'))
                ->whereNotNull('related_voucher')
                ->get();
            $each_ids = null;
            foreach ($all_voucher_ids as $each) {
                if ($each_ids) {
                    $each_ids = $each_ids . ', ' . $each->related_voucher;
                } else {
                    $each_ids = $each->related_voucher;
                }
            }
            $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
            $related_vouchers = Voucher::with(['invoice.inventories'])->whereIn('id', explode(',', $unique_ids))
                ->where('account', '!=', $ledger->account)
                ->whereCompany($request->header('company'))
                ->orderBy('date', 'desc')
                ->get();
            //Update balance according to 'debit' or 'credit'
            $vouchers_by_ledger = Voucher::where('account_ledger_id', $ledger->id)->get();

            $vouchers_debit_sum = $vouchers_by_ledger->sum('debit');

            $vouchers_credit_sum = $vouchers_by_ledger->sum('credit');
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

            //Extra's for vouchers collection
            foreach ($related_vouchers as $each) {
                $each['voucher_type'] = 'Journal';
                $each['particulars'] = $each->account;
            }
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
        $ledger = AccountLedger::findOrFail($id);
        $all_voucher_ids = Voucher::where('account_ledger_id', $id)
            ->whereCompany($request->header('company'))
            ->whereNotNull('related_voucher')
            ->get();
        $each_ids = null;
        foreach ($all_voucher_ids as $each) {
            if ($each_ids) {
                $each_ids = $each_ids . ', ' . $each->related_voucher;
            } else {
                $each_ids = $each->related_voucher;
            }
        }
        $unique_ids = implode(',', array_unique(explode(',', $each_ids)));
        $related_vouchers = Voucher::with(['invoice.inventories', 'receipt'])->whereIn('id', explode(',', $unique_ids))
            ->where('account', '!=', $ledger->account)
            ->whereCompany($request->header('company'))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'vouchers' => $related_vouchers,
            'ledger' => $ledger,
            'account_master' => AccountMaster::where('id', $ledger->account_master_id)->first(),
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
            ->groupBy('account_ledger_id')
            ->get();

        $ledgers = [];
        foreach ($all_voucher_ids as $each) {
            $lot = Voucher::where('account_ledger_id', $each->account_ledger_id)->count();
            $each['lot'] = $lot;
            $each['party'] = $each->account;
            $each['reference_number'] = Invoice::where('id', $each->invoice_id)->first()->reference_number;
            array_push($ledgers, $each);
        }

        return response()->json([
            'ledger' => $ledgers
        ]);
    }
}
