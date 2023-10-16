<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AccountLedger;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\Request;
use Log;

class VouchersController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $vouchers = Voucher::applyFilters($request->only([
            'name',
            'groups',
            'orderByField',
            'orderBy',
            'from_date',
            'to_date'
        ]))
            ->whereCompany($request->header('company'))
            ->with(['accountMaster'])
            ->where('voucher_type', 'Voucher')
            ->latest()
            ->paginate($limit);

        return response()->json([
            'vouchers' => $vouchers,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $voucher = Voucher::where('related_voucher', 'like', '%' . $id . '%')->select([
            'id',
            'type',
            'account',
            'account_master_id',
            'account_ledger_id',
            'credit',
            'debit',
            'short_narration',
            'date',
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
                        $ledgerPresent->update(['credit' => $updateCredit, 'balance' => $updateCredit]);
                    } else {
                        $updateDebit = $ledgerPresent->debit + $each['debit'];
                        $ledgerPresent->update(['debit' => $updateDebit, 'balance' => $updateDebit]);
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
                        'date' => $each['date'],
                        'company_id' => $request->header('company')
                    ]);
                }

                $voucher = null;
                if ($each['is_edit']) {
                    Voucher::where([
                        'company_id' => $request->header('company'),
                        'id' => $each['id'],
                    ])->update([
                        'account_ledger_id' => $each['account_ledger_id'],
                        'account_master_id' => $each['account_id'],
                        'type' => $each['type'],
                        'account' => $each['account'],
                        'debit' => $each['debit'] ?? 0,
                        'credit' => $each['credit'] ?? 0,
                        'short_narration' => $each['short_narration'],
                        'date' => $each['date'],
                    ]);
                    $voucher = Voucher::find($each['id']);
                } else {
                    $voucher = Voucher::create([
                        'account_ledger_id' => $ledger->id,
                        'account_master_id' => $each['account_id'],
                        'type' => $each['type'],
                        'account' => $each['account'],
                        'debit' => $each['debit'] ?? 0,
                        'credit' => $each['credit'] ?? 0,
                        'short_narration' => $each['short_narration'],
                        'date' => $each['date'],
                        'company_id' => $request->header('company'),
                        'voucher_type' => 'Voucher',
                    ]);
                }

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
            Log::error('Error while saving account voucher', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
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

    /**
     * Get day book ledgers
     */
    public function getDaybook(Request $request)
    {
        $day_voucher = Voucher::applyFilters($request->only([
            'type',
            'account',
            'debit',
            'credit',
            'from_date',
            'to_date'
        ]))->whereCompany($request->header('company'), $request['filterBy'])
            ->get();

        $voucher = [];
        foreach ($day_voucher as $key => $each) {
            if (0 === $key % 2) {
                $each['voucher_count'] = Voucher::whereRaw("find_in_set(" . $each->id . ",related_voucher)")->count();
                $each['voucher_debit'] = $each->debit;
                $each['voucher_credit'] = $each->credit;
                $each['quantity'] = InvoiceItem::where('invoice_id', $each->invoice_id)->sum('quantity');
                array_push($voucher, $each);
            }
        }
        return response()->json([
            'daybook' => $voucher,
            'total' => count($voucher),
        ]);
    }

    /**
     * Get ledgers to book
     */
    public function book(Request $request, $id)
    {
        $related_vouchers = Voucher::whereRaw("find_in_set(" . $id . ",related_voucher)")
            ->whereCompany($request->header('company'))
            ->where('updated_at', '>', Carbon::today())
            ->where('updated_at', '<', Carbon::tomorrow())
            ->get();

        //Extra's for vouchers collection
        foreach ($related_vouchers as $each) {
            $each['particulars'] = $each->account;
            if ($each->invoice_id) {
                $each['invoice'] = Invoice::with(['inventories'])->where('id', $each->invoice_id)->first();
            }
        }

        return response()->json([
            'vouchers' => $related_vouchers,
        ]);
    }
}
