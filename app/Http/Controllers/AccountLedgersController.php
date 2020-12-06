<?php

namespace Crater\Http\Controllers;

use Crater\AccountLedger;
use Exception;
use Illuminate\Http\Request;
use Log;

class AccountLedgersController extends Controller
{
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
            ->latest()
            ->paginate($limit);

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
        $ledger = AccountLedger::find($id);

        return response()->json([
            'ledger' => $ledger,
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
            $ledger->save();

            $ledger = AccountLedger::find($ledger->id);

            return response()->json([
                'ledger' => $ledger,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account ledger', [$e->getMessage()]);
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
