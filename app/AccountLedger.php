<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $fillable = [
        'date',
        'type',
        'bill_no',
        'account',
        'debit',
        'credit',
        'balance',
        'short_narration',
    ];

    public function scopeWhereDate($query, $date)
    {
        return $query->where('date', 'LIKE', '%'.$date.'%');
    }

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', 'LIKE', '%'.$type.'%');
    }

    public function scopeWhereAccount($query, $account)
    {
        return $query->where('account', 'LIKE', '%'.$account.'%');
    }

    public function scopeWhereDebit($query, $debit)
    {
        return $query->where('debit', 'LIKE', '%'.$debit.'%');
    }

    public function scopeWhereCredit($query, $credit)
    {
        return $query->where('credit', 'LIKE', '%'.$credit.'%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('date')) {
            $query->whereName($filters->get('date'));
        }

        if ($filters->get('type')) {
            $query->whereDesignNo($filters->get('type'));
        }

        if ($filters->get('account')) {
            $query->whereName($filters->get('account'));
        }

        if ($filters->get('debit')) {
            $query->whereDesignNo($filters->get('debit'));
        }

        if ($filters->get('credit')) {
            $query->whereName($filters->get('credit'));
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
