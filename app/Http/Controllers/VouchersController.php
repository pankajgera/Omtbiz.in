<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AccountLedger;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
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
            'voucher_status',
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
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isAccountant())) {
            return response()->json([
                'error' => 'admin_or_accountant_only',
            ], 403);
        }

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
        $isEditRequest = collect($request->all())->contains(function ($entry) {
            return is_array($entry) && !empty($entry['is_edit']);
        });
        $isAdmin = Auth::user() && Auth::user()->isAdmin();

        if ($isEditRequest && ($response = $this->adminOnlyResponse())) {
            return $response;
        }

        $ledger = '';
        $ledger_ids = [];
        $voucher_ids = '';
        try {
            foreach ($request->all() as $each) {
                $voucherStatus = $isAdmin ? Voucher::STATUS_DONE : Voucher::STATUS_TO_BE_APPROVED;

                // If accountLedger is already present then update
                // Credit and Debit with balance with 'type'
                $ledgerPresent = AccountLedger::whereCompany($request->header('company'))
                    ->where([
                        'account' => $each['account'],
                        'account_master_id' => $each['account_id'],
                    ])->first();
                $ledger = null;

                if (!empty($ledgerPresent)) {
                    if ($isAdmin) {
                        $updateCredit = 0;
                        $updateDebit = 0;
                        if ('Cr' === $each['type']) {
                            $updateCredit = $ledgerPresent->credit + $each['credit'];
                            $ledgerPresent->update(['credit' => $updateCredit, 'balance' => $updateCredit]);
                        } else {
                            $updateDebit = $ledgerPresent->debit + $each['debit'];
                            $ledgerPresent->update(['debit' => $updateDebit, 'balance' => $updateDebit]);
                        }
                    }
                    $ledger = $ledgerPresent;
                } else {
                    $ledger = AccountLedger::create([
                        'account' => $each['account'],
                        'account_master_id' => $each['account_id'],
                        'type' => $each['type'],
                        // For pending approvals, create ledger shell with zero values.
                        'debit' => $isAdmin ? ($each['debit'] ?? 0) : 0,
                        'credit' => $isAdmin ? ($each['credit'] ?? 0) : 0,
                        'balance' => $isAdmin ? ($each['balance'] ?? 0) : 0,
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
                        'account_ledger_id' => $ledger->id,
                        'account_master_id' => $each['account_id'],
                        'type' => $each['type'],
                        'account' => $each['account'],
                        'debit' => $each['debit'] ?? 0,
                        'credit' => $each['credit'] ?? 0,
                        'short_narration' => $each['short_narration'],
                        'date' => $each['date'],
                        'voucher_status' => $voucherStatus,
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
                        'voucher_status' => $voucherStatus,
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
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

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
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

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

    public function approve(Request $request, $id)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        $voucher = Voucher::whereCompany($request->header('company'))
            ->where('voucher_type', 'Voucher')
            ->findOrFail($id);

        if ($voucher->voucher_status !== Voucher::STATUS_TO_BE_APPROVED) {
            return response()->json([
                'error' => 'voucher_not_pending_approval',
            ], 422);
        }

        $relatedVouchers = $this->getRelatedVoucherRows($request, $voucher);
        foreach ($relatedVouchers as $each) {
            $this->postVoucherToLedger($each, $request->header('company'));
            $each->update([
                'voucher_status' => Voucher::STATUS_DONE,
            ]);
        }

        return response()->json([
            'voucher' => $voucher->fresh(),
            'success' => true,
        ]);
    }

    public function decline(Request $request, $id)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        $voucher = Voucher::whereCompany($request->header('company'))
            ->where('voucher_type', 'Voucher')
            ->findOrFail($id);

        if ($voucher->voucher_status !== Voucher::STATUS_TO_BE_APPROVED) {
            return response()->json([
                'error' => 'voucher_not_pending_approval',
            ], 422);
        }

        $relatedVouchers = $this->getRelatedVoucherRows($request, $voucher);
        foreach ($relatedVouchers as $each) {
            $each->update([
                'voucher_status' => Voucher::STATUS_DECLINED,
            ]);
        }

        return response()->json([
            'voucher' => $voucher->fresh(),
            'success' => true,
        ]);
    }

    public function approveMultiple(Request $request)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        $ids = is_array($request->id) ? array_values(array_unique($request->id)) : [];
        $processed = [];
        $skipped = [];

        foreach ($ids as $id) {
            $voucher = Voucher::whereCompany($request->header('company'))
                ->where('voucher_type', 'Voucher')
                ->find($id);

            if (!$voucher || $voucher->voucher_status !== Voucher::STATUS_TO_BE_APPROVED) {
                $skipped[] = $id;
                continue;
            }

            try {
                $relatedVouchers = $this->getRelatedVoucherRows($request, $voucher);
                foreach ($relatedVouchers as $each) {
                    $this->postVoucherToLedger($each, $request->header('company'));
                    $each->update([
                        'voucher_status' => Voucher::STATUS_DONE,
                    ]);
                }
                $processed[] = $id;
            } catch (Exception $e) {
                Log::error('Error while approving voucher in bulk', [$e]);
                $skipped[] = $id;
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'skipped' => $skipped,
        ]);
    }

    public function declineMultiple(Request $request)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        $ids = is_array($request->id) ? array_values(array_unique($request->id)) : [];
        $processed = [];
        $skipped = [];

        foreach ($ids as $id) {
            $voucher = Voucher::whereCompany($request->header('company'))
                ->where('voucher_type', 'Voucher')
                ->find($id);

            if (!$voucher || $voucher->voucher_status !== Voucher::STATUS_TO_BE_APPROVED) {
                $skipped[] = $id;
                continue;
            }

            $relatedVouchers = $this->getRelatedVoucherRows($request, $voucher);
            foreach ($relatedVouchers as $each) {
                $each->update([
                    'voucher_status' => Voucher::STATUS_DECLINED,
                ]);
            }
            $processed[] = $id;
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'skipped' => $skipped,
        ]);
    }

    private function getRelatedVoucherRows(Request $request, Voucher $voucher)
    {
        if (!$voucher->related_voucher) {
            return collect([$voucher]);
        }

        return Voucher::whereCompany($request->header('company'))
            ->whereIn('id', array_map('intval', explode(',', str_replace(' ', '', $voucher->related_voucher))))
            ->where('voucher_type', 'Voucher')
            ->get();
    }

    private function postVoucherToLedger(Voucher $voucher, $companyId)
    {
        $ledgerPresent = AccountLedger::whereCompany($companyId)
            ->where([
                'account' => $voucher->account,
                'account_master_id' => $voucher->account_master_id,
            ])->first();

        if (!empty($ledgerPresent)) {
            if ('Cr' === $voucher->type) {
                $updated = $ledgerPresent->credit + $voucher->credit;
                $ledgerPresent->update(['credit' => $updated, 'balance' => $updated]);
            } else {
                $updated = $ledgerPresent->debit + $voucher->debit;
                $ledgerPresent->update(['debit' => $updated, 'balance' => $updated]);
            }
            $voucher->update(['account_ledger_id' => $ledgerPresent->id]);
            return;
        }

        $ledger = AccountLedger::create([
            'account' => $voucher->account,
            'account_master_id' => $voucher->account_master_id,
            'type' => $voucher->type,
            'debit' => $voucher->debit ?? 0,
            'credit' => $voucher->credit ?? 0,
            'balance' => 0,
            'date' => $voucher->date,
            'company_id' => $companyId
        ]);

        $voucher->update(['account_ledger_id' => $ledger->id]);
    }
}
