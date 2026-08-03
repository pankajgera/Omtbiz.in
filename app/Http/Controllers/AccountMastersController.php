<?php

namespace App\Http\Controllers;

use App\Models\AccountGroup;
use App\Models\AccountLedger;
use App\Models\AccountMaster;
use App\Models\State;
use App\Models\Voucher;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;

use function PHPSTORM_META\type;

class AccountMastersController extends Controller
{
    public function index(Request $request)
    {
        $masters = [];

        if ($request->has('limit') && $request->limit === 'false') {
            $masters = AccountMaster::orderBy('name')->get();
        } else {
            $masters = AccountMaster::applyFilters($request->only([
                'name',
                'groups',
                'orderByField',
                'orderBy',
            ]))
                ->latest()
                ->paginate(15);
        }

        return response()->json([
            'masters' => $masters,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $master = AccountMaster::find($id);
        $master->state = State::where('name', $master->state)->first();
        return response()->json([
            'master' => $master,
        ]);
    }

    /**
     * Create Account Master.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $master = new AccountMaster();
            $master->name = $request->name;
            $master->mobile_number = $request->mobile_number;
            $master->groups = $request->groups;
            $master->address = $request->address;
            $master->country = $request->country;
            $master->state = $request->state;
            $master->opening_balance = $request->opening_balance;
            $master->type = $request->type;
            $master->save();

            $master = AccountMaster::find($master->id);

            // Sync opening ledger without a second audit entry (master create is the user action).
            AuditLogger::withoutAuditing(function () use ($request, $master) {
                $ledger = new AccountLedger();
                $ledger->date = Carbon::now('Asia/Kolkata');
                $ledger->type = $request->type;
                $ledger->account = $request->name;
                $ledger->debit = 'Dr' === $request->type ? $request->opening_balance : 0;
                $ledger->credit = 'Cr' === $request->type ? $request->opening_balance : 0;
                $ledger->balance = $request->opening_balance;
                $ledger->short_narration = null;
                $ledger->account_master_id = $master->id;
                $ledger->company_id = $request->header('company');
                $ledger->save();
            });

            return response()->json([
                'master' => $master,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account master', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing Account Master.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $master = AccountMaster::find($id);
            $ledger = AccountLedger::where('account_master_id', $master->id)->where('account', $master->name)->first();

            $oldOpeningBalance = (float) $master->opening_balance;
            $oldType = $master->type;

            $master->name = $request->name;
            $master->mobile_number = $request->mobile_number;
            $master->groups = $request->groups;
            $master->address = $request->address;
            $master->country = $request->country;
            $master->state = $request->state;
            $master->opening_balance = $request->opening_balance;
            $master->type = $request->type;
            $master->save();

            if ($ledger) {
                // Side-effect sync of ledger/vouchers — keep a single audit row for the master update.
                AuditLogger::withoutAuditing(function () use ($request, $master, $ledger, $oldOpeningBalance, $oldType) {
                    $ledger->account = $request->name;
                    $ledger->type = $request->type;
                    $ledger->account_master_id = $master->id;
                    $ledger->company_id = $request->header('company');

                    // Ledger debit/credit already include vouchers. Never replace them with
                    // opening_balance alone — only apply the opening-balance delta when it changes.
                    $newOpeningBalance = (float) $request->opening_balance;
                    $openingDelta = $newOpeningBalance - $oldOpeningBalance;

                    if (abs($openingDelta) > 0.00001 || $oldType !== $request->type) {
                        if ($oldType === $request->type) {
                            if ('Dr' === $request->type) {
                                $ledger->debit = (float) $ledger->debit + $openingDelta;
                            } else {
                                $ledger->credit = (float) $ledger->credit + $openingDelta;
                            }
                            $ledger->balance = (float) $ledger->balance + $openingDelta;
                        } else {
                            // Type flipped: remove old opening from previous side, add new opening to new side.
                            if ('Dr' === $oldType) {
                                $ledger->debit = (float) $ledger->debit - $oldOpeningBalance;
                            } else {
                                $ledger->credit = (float) $ledger->credit - $oldOpeningBalance;
                            }

                            if ('Dr' === $request->type) {
                                $ledger->debit = (float) $ledger->debit + $newOpeningBalance;
                                $ledger->balance = (float) $ledger->debit;
                            } else {
                                $ledger->credit = (float) $ledger->credit + $newOpeningBalance;
                                $ledger->balance = (float) $ledger->credit;
                            }
                        }
                    }

                    $ledger->save();

                    $ledger_vouchers = Voucher::where('account_ledger_id', $ledger->id)->get();
                    foreach ($ledger_vouchers as $voucher) {
                        $voucher->update([
                            'account' => $request->name,
                        ]);
                    }
                });
            }

            return response()->json([
                'master' => $master,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating account master', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an existing Account Master.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = AccountMaster::deleteAccountMaster($id);

        if (!$data) {
            return response()->json([
                'error' => 'master_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Account Master.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $masters = [];
        foreach ($request->id as $id) {
            $master = AccountMaster::deleteAccountMaster($id);
            if (!$master) {
                array_push($masters, $id);
            }
        }

        if (empty($masters)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'masters' => $masters,
        ]);
    }

    /**
     * Check if same master name is present
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkName(Request $request)
    {
        $name_exists = AccountMaster::where('name', $request->name)->exists();

        return response()->json([
            'name_exists' => $name_exists,
        ]);
    }
}
