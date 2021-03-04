<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $fillable = [
        'date',
        'type',
        'bill_no',
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
        return $this->belongsToMany(Voucher::class);
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

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('date')) {
            $query->whereDate($filters->get('date'));
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
        $master = self::find($id);
        $master->delete();
        return true;
    }
}
