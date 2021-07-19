<?php

namespace App\Http\Controllers;

use App\Models\AccountLedger;
use App\Models\AccountMaster;
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
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'ledgers' => $ledgers,
        ]);
    }

    /**
     * Get day book ledgers
     */
    public function getDaybook(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $daybook = AccountLedger::applyFilters($request->only([
            'date',
            'account',
            'debit',
            'credit',
            'balance',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->where('updated_at', '>', Carbon::today())
            ->where('updated_at', '<', Carbon::tomorrow())
            ->paginate($limit);

        foreach ($daybook as $each) {
            $voucher = Voucher::where('account_ledger_id', $each->id)
                ->where('updated_at', '>', Carbon::today())
                ->where('updated_at', '<', Carbon::tomorrow())->get();

            $each['voucher'] = $voucher;
            $each['voucher_type'] = $voucher[0]->voucher_type;
            $each['voucher_count'] = $voucher->count();
            $each['voucher_debit'] = $voucher->sum('debit');
            $each['voucher_credit'] = $voucher->sum('credit');
            $each['voucher_balance'] = $voucher->sum('debit') > $voucher->sum('credit') ? $voucher->sum('debit') - $voucher->sum('credit') : $voucher->sum('credit') - $voucher->sum('debit');
        }
        return response()->json([
            'daybook' => $daybook,
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
        $all_voucher_ids = Voucher::where('account_ledger_id', $id)->whereNotNull('related_voucher')->get();
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
            ->orderBy('id', 'desc')
            ->get();

        //Update balance according to 'debit' or 'credit'
        $vouchers_by_ledger = Voucher::where('account_ledger_id', $id)->get();
        $vouchers_debit_sum = $vouchers_by_ledger->sum('debit');
        $vouchers_credit_sum = $vouchers_by_ledger->sum('credit');
        $balance = $ledger->debit - $ledger->credit;
        $opening_balance = $ledger->accountMaster->opening_balance;
        $ledger->update([
            'type' => $ledger->debit > $ledger->credit ? 'Dr' : 'Cr',
            'credit' => $vouchers_credit_sum,
            'debit' => $vouchers_debit_sum,
            'balance' => $opening_balance > $balance ? $opening_balance - $balance : ($opening_balance > 0 ? $balance - $opening_balance : abs($balance)),
        ]);
        // if ($ledger->balance === $opening_balance) {
        //     AccountMaster::updateOpeningBalance($ledger->accountMaster->id, $ledger->balance);
        // }

        //Extra's for vouchers collection
        foreach ($related_vouchers as $each) {
            $each['voucher_type'] = 'Journal';
            $each['particulars'] = $each->account;
        }

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
            $ledger->voucher_id = $request->voucher_id;
            $ledger->date = $request->date;
            $ledger->type = $request->type;
            $ledger->bill_no = $request->bill_no;
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
            Log::error('Error while saving account ledger', [$e->getMessage()]);
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
            $ledger->voucher_id = $request->voucher_id;
            $ledger->date = $request->date;
            $ledger->type = $request->type;
            $ledger->bill_no = $request->bill_no;
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
            Log::error('Error while updating account ledger', [$e->getMessage()]);
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
}
