<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'type',
        'account_master_id',
        'account',
        'debit_amount',
        'credit_amount',
        'short_narration',
    ];

    public function accountMaster()
    {
        return $this->belongsTo(\Crater\AccountMaster::class);
    }

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', 'LIKE', '%'.$type.'%');
    }

    public function scopeWhereAccount($query, $account)
    {
        return $query->where('account', 'LIKE', '%'.$account.'%');
    }

    public function scopeWhereDebitAmount($query, $debit_amount)
    {
        return $query->where('debit_amount', 'LIKE', '%'.$debit_amount.'%');
    }

    public function scopeWhereCreditAmount($query, $credit_amount)
    {
        return $query->where('credit_amount', 'LIKE', '%'.$credit_amount.'%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('type')) {
            $query->whereName($filters->get('type'));
        }

        if ($filters->get('account')) {
            $query->whereDesignNo($filters->get('account'));
        }

        if ($filters->get('debit_amount')) {
            $query->whereName($filters->get('debit_amount'));
        }

        if ($filters->get('credit_amount')) {
            $query->whereDesignNo($filters->get('credit_amount'));
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
