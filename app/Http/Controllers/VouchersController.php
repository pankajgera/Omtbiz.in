<?php

namespace Crater\Http\Controllers;

use Carbon\Carbon;
use Crater\AccountLedger;
use Crater\AccountMaster;
use Crater\Voucher;
use Exception;
use Illuminate\Http\Request;
use Log;

class VouchersController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $vouchers = AccountLedger::applyFilters($request->only([
            'orderByField',
            'orderBy',
        ]))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'vouchers' => $vouchers,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $voucher = Voucher::find($id);

        return response()->json([
            'voucher' => $voucher,
        ]);
    }

    /**
     * Create Account Ledger.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $ledger = '';
        try {
            foreach ($request->all() as $each) {
                $ledger = AccountLedger::updateOrCreate([
                    'account' => $each['account'],
                    'account_master_id' => $each['account_id'],
                ], [
                    'debit' => $each['total_debit'],
                    'credit' => $each['total_credit'],
                    'balance' => $each['balance'],
                    'date' => Carbon::now()->toDateTimeString(),
                ]);

                Voucher::updateOrCreate([
                    'account_ledger_id' => $ledger->id,
                    'account_master_id' => $each['account_id'],
                ], [
                    'type' => $each['type'],
                    'account' => $each['account'],
                    'debit_amount' => $each['debit'],
                    'credit_amount' => $each['credit'],
                    'short_narration' => $each['short_narration'],
                    'date' => Carbon::now()->toDateTimeString(),
                ]);
            }

            $voucher = Voucher::where('account_ledger_id', $ledger->id)->get();
            return response()->json([
                'voucher' => $voucher,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account voucher', [$e->getMessage()]);
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
        $data = Voucher::deleteVoucher($id);

        if (!$data) {
            return response()->json([
                'error' => 'voucher_attached',
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
        $vouchers = [];
        foreach ($request->id as $id) {
            $voucher = Voucher::deleteVoucher($id);
            if (!$voucher) {
                array_push($vouchers, $id);
            }
        }

        if (empty($vouchers)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'vouchers' => $vouchers,
        ]);
    }
}
