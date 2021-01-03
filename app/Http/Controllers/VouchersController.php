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
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'vouchers' => $vouchers,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $voucher = Voucher::where('related_voucher', 'like', '%' . $id . '%')->select([
            'type',
            'account',
            'account_master_id',
            'credit',
            'debit'
        ])->get();

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
        $ledger_ids = [];
        $voucher_ids = '';
        try {
            foreach ($request->all() as $each) {
                // If accountLedger is already present then update
                // Credit and Debit with balance with 'type'
                $ledgerPresent = AccountLedger::whereCompany($request->header('company'))
                    ->where([
                        'account' => $each['account'],
                        'account_master_id' => $each['account_id'],
                    ])->first();
                $ledger = null;
                if (!empty($ledgerPresent)) {
                    $updateCredit = 0;
                    $updateDebit = 0;
                    if ('Cr' === $each['type']) {
                        $updateCredit = $ledgerPresent->credit + $each['credit'];
                        $ledgerPresent->update(['credit' => $updateCredit]);
                    } else {
                        $updateDebit = $ledgerPresent->debit + $each['debit'];
                        $ledgerPresent->update(['debit' => $updateDebit]);
                    }

                    $ledger = $ledgerPresent;
                } else {
                    $ledger = AccountLedger::create([
                        'account' => $each['account'],
                        'account_master_id' => $each['account_id'],
                        'type' => $each['type'],
                        'debit' => $each['debit'] ?? 0,
                        'credit' => $each['credit'] ?? 0,
                        'balance' => $each['balance'],
                        'date' => Carbon::now()->toDateTimeString(),
                        'company_id' => $request->header('company')
                    ]);
                }

                $voucher = Voucher::updateOrCreate([
                    'account_ledger_id' => $ledger->id,
                    'account_master_id' => $each['account_id'],
                    'type' => $each['type'],
                    'account' => $each['account'],
                    'debit' => $each['debit'],
                    'credit' => $each['credit'],
                ], [
                    'short_narration' => $each['short_narration'],
                    'date' => Carbon::now()->toDateTimeString(),
                    'company_id' => $request->header('company')
                ]);

                //Update voucher_id's in ledger->bill_no
                $bill_no = 0;
                $existing_voucher_id = $ledger->bill_no;
                if (!empty($existing_voucher_id)) {
                    $bill_no = $existing_voucher_id . ', ' . $voucher->id;
                } else {
                    $bill_no = $voucher->id;
                }
                $ledger->update([
                    'bill_no' => $bill_no,
                ]);

                array_push($ledger_ids, $ledger->id);
                if (!empty($voucher_ids)) {
                    $voucher_ids = $voucher_ids . ', ' . $voucher->id;
                } else {
                    $voucher_ids = $voucher->id;
                }
            }

            $voucher = Voucher::whereCompany($request->header('company'))->whereIn('id', explode(',', $voucher_ids))->orderBy('id')->get();
            foreach ($voucher as $key => $each) {
                if ($key < substr_count($voucher_ids, ',') + 1) {
                    $each->update([
                        'related_voucher' => $voucher_ids,
                    ]);
                }
            }
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
