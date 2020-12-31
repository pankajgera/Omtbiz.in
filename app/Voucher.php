<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'type',
        'date',
        'account_ledger_id',
        'account_master_id',
        'account',
        'debit',
        'credit',
        'short_narration',
        'related_voucher',
        'company_id'
    ];

    public function accountMaster()
    {
        return $this->belongsTo(\Crater\AccountMaster::class);
    }

    public function accountLedger()
    {
        return $this->belongsTo(\Crater\AccountLedger::class);
    }

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', 'LIKE', '%' . $type . '%');
    }

    public function scopeWhereAccount($query, $account)
    {
        return $query->where('account', 'LIKE', '%' . $account . '%');
    }

    public function scopeWhereDebitAmount($query, $debit)
    {
        return $query->where('debit', 'LIKE', '%' . $debit . '%');
    }

    public function scopeWhereCreditAmount($query, $credit)
    {
        return $query->where('credit', 'LIKE', '%' . $credit . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
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

        if ($filters->get('debit')) {
            $query->whereName($filters->get('debit'));
        }

        if ($filters->get('credit')) {
            $query->whereDesignNo($filters->get('credit'));
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
