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

        $vouchers = Voucher::applyFilters($request->only([
            'type',
            'account',
            'debit_amount',
            'credit_amount',
            'short_narration',
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
        \Log::info('request', [$request]);
        try {
            $ledger = AccountLedger::create([
                'date' => Carbon::now(),
                'debit' => $request[0]->total_debit,
                'credit' => $request[0]->total_credit,
                'balance' => $request[0]->balance,
            ]);

            foreach ($request as $each) {
                $voucher = new Voucher();
                $voucher->account_master_id = AccountMaster::where('name', $each->account)->first()->pluck('id');
                $voucher->account_ledger_id = $ledger->id;
                $voucher->type = $each->type;
                $voucher->account = $each->account;
                $voucher->debit_amount = $each->debit;
                $voucher->credit_amount = $each->credit;
                $voucher->short_narration = $each->short_narration;
                $voucher->save();
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
     * Update an existing Account Ledger.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $voucher = Voucher::find($id);
            $voucher->account_master_id = $request->account_master_id;
            $voucher->type = $request->type;
            $voucher->account = $request->account;
            $voucher->debit_amount = $request->debit_amount;
            $voucher->credit_amount = $request->credit_amount;
            $voucher->short_narration = $request->short_narration;
            $voucher->save();

            $voucher = Voucher::find($voucher->id);

            return response()->json([
                'voucher' => $voucher,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating account voucher', [$e->getMessage()]);
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
