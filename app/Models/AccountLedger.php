<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Log;

class AccountLedger extends Model
{
    protected $fillable = [
        'date',
        'type',
        'account',
        'account_master_id',
        'debit',
        'credit',
        'balance',
        'short_narration',
        'company_id'
    ];

    public function accountMaster()
    {
        return $this->belongsTo(AccountMaster::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function scopeWhereDate($query, $date)
    {
        return $query->where('date', 'LIKE', '%' . $date . '%');
    }

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', 'LIKE', '%' . $type . '%');
    }

    public function scopeWhereAccount($query, $account)
    {
        return $query->where('account', 'LIKE', '%' . $account . '%');
    }

    public function scopeWhereDebit($query, $debit)
    {
        return $query->where('debit', 'LIKE', '%' . $debit . '%');
    }

    public function scopeWhereCredit($query, $credit)
    {
        return $query->where('credit', 'LIKE', '%' . $credit . '%');
    }

    public function scopeWhereBalance($query, $balance)
    {
        return $query->where('balance', 'LIKE', '%' . $balance . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeEstimatesBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'date',
            [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]
        );
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public function scopeLedgerBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->ledgerBetween($start, $end);
        }

        if ($filters->get('type')) {
            $query->whereType($filters->get('type'));
        }

        if ($filters->get('account')) {
            $query->whereAccount($filters->get('account'));
        }

        if ($filters->get('debit')) {
            $query->whereDebit($filters->get('debit'));
        }

        if ($filters->get('credit')) {
            $query->whereCredit($filters->get('credit'));
        }

        if ($filters->get('balance')) {
            $query->whereBalance($filters->get('balance'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->estimatesBetween($start, $end);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteAccountLedger($id)
    {
        $find_vouchers = Voucher::where('account_ledger_id', $id)->exists();
        if ($find_vouchers) {
            Log::error('Voucher exists for account_ledger_id ' . $id . ', so we cannnot delete this ledger');
            return false;
        }
        $ledger = self::find($id);
        $ledger->delete();
        return true;
    }

    /**
     * need updation, not using it
     */
    public static function updateBalanceAndType($id, $amount, $type)
    {
        $ledger = self::find($id);
        $calc_balance = $ledger->balance;
        $calc_type = $ledger->type;
        if ('Dr' === $type && 'Dr' === $calc_type) {
            $calc_balance = $ledger->balance - $amount;
            //If value is in -ve then change Dr to Cr
            if (0 > $calc_balance) {
                $calc_type = 'Cr';
                $calc_balance = abs($calc_balance);
            }
        } else if ('Cr' === $type && 'Cr' === $calc_type) {
            $calc_balance = $ledger->balance - $amount;
            //If value is in -ve then change Dr to Cr
            if (0 > $calc_balance) {
                $calc_type = 'Dr';
                $calc_balance = abs($calc_balance);
            }
        }

        $ledger->update([
            'balance' => $calc_balance,
            'type' => $calc_type,
        ]);
    }

    public static function ledgerMutation($ledger, $from, $to)
    {
        $all_voucher_ids = Voucher::where('account_ledger_id', $ledger->id)->whereNotNull('related_voucher')->get();
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
            ->where('account_ledger_id', '!=', $ledger->id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->orderBy('date')
            ->get();

        $inventory_sum = 0;
        $current_balance_cr = 0;
        $current_balance_dr = 0;
        $closing_balance_cr = 0;
        $closing_balance_dr = 0;
        $total_opening_balance_cr = 0;
        $total_opening_balance_dr = 0;
        $master = AccountMaster::where('id', $ledger->account_master_id)->first();
        $master_opening_balance = $master->opening_balance;

        foreach ($related_vouchers as $each) {
            $each['amount'] = 0 < $each->credit ? $each->credit : $each->debit;
            $inventory_sum += $each->invoice && $each->invoice->inventories ? $each->invoice->inventories->sum('quantity') : 0;
            //we show cr to dr when we display
            $current_balance_cr += $each->credit;
            $current_balance_dr += $each->debit;
        }

        //Calculate Opening balance
        $calc_opening_balance = Voucher::whereIn('id', explode(',', $unique_ids))
            ->where('account_ledger_id', '!=', $ledger->id)
            ->whereDate('date', '<', $from)
            ->orderBy('date')
            ->get(['id', 'debit', 'credit']);


        foreach ($calc_opening_balance as $each) {
            if ($each->debit) {
                $total_opening_balance_dr += $each->debit;
            }
            if ($each->credit) {
                $total_opening_balance_cr += $each->credit;
            }
        }

        $sum_opening_current_cr = 0;
        $sum_opening_current_dr = 0;
        // --- 1. Calculate opening balance
        //Total opening balance include previous dates
        if ('Dr' === $master->type) {
            $total_opening_balance_cr = $total_opening_balance_cr + $master_opening_balance;
        } else {
            $total_opening_balance_dr = $total_opening_balance_dr + $master_opening_balance;
        }

        //For dates, add/sub total_opening so we get only single opening
        if ($total_opening_balance_cr > $total_opening_balance_dr) {
            $total_opening_balance_cr = abs($total_opening_balance_cr - $total_opening_balance_dr);
            $total_opening_balance_dr = 0;
        } else if ($total_opening_balance_cr < $total_opening_balance_dr) {
            $total_opening_balance_dr = abs($total_opening_balance_cr - $total_opening_balance_dr);
            $total_opening_balance_cr = 0;
        } else {
            $total_opening_balance_cr = 0;
            $total_opening_balance_dr = 0;
        }

        // --- 2. Calculate current balance
        //Adding opening and current balance with total opening
        $sum_opening_current_cr = $total_opening_balance_cr + $current_balance_cr;
        $sum_opening_current_dr = $total_opening_balance_dr + $current_balance_dr;

        // --- 3. Calculate closing balance
        if ($sum_opening_current_cr > $sum_opening_current_dr) {
            $closing_balance_cr = abs($sum_opening_current_cr - $sum_opening_current_dr);
            $closing_balance_dr = 0;
        } else if ($sum_opening_current_cr < $sum_opening_current_dr) {
            $closing_balance_dr = abs($sum_opening_current_dr - $sum_opening_current_cr);
            $closing_balance_cr = 0;
        } else {
            $closing_balance_cr = 0;
            $closing_balance_dr = 0;
        }

        $vouchers_debit_sum = $all_voucher_ids->sum('debit');
        $vouchers_credit_sum = $all_voucher_ids->sum('credit');

        $opening_balance = $ledger->accountMaster->opening_balance;
        $calc_balance = $ledger->balance;
        $calc_type = $ledger->type;
        $calc_total = 0;

        //Calculate total balance, type, debit/credit and update it in ledger
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

        return [
            'ledger' => $ledger,
            'calc_type' => $calc_type,
            'related_vouchers' => $related_vouchers,
            'inventory_sum' => $inventory_sum,
            'total_opening_balance_dr' => $total_opening_balance_dr,
            'total_opening_balance_cr' => $total_opening_balance_cr,
            'current_balance_cr' => $current_balance_cr,
            'current_balance_dr' => $current_balance_dr,
            'closing_balance_cr' => $closing_balance_cr,
            'closing_balance_dr' => $closing_balance_dr,
        ];
    }
}
