<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class AccountMaster extends Model
{
    protected $fillable = [
        'name',
        'group',
        'address',
        'country',
        'state',
        'opening_balance',
        'type'
    ];

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%'.$name.'%');
    }

    public function scopeWhereGroup($query, $group)
    {
        return $query->where('group', 'LIKE', '%'.$group.'%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('name')) {
            $query->whereName($filters->get('name'));
        }

        if ($filters->get('group')) {
            $query->whereDesignNo($filters->get('group'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteAccountMaster($id)
    {
        \Log::info($id);
        $master = self::find($id);
        $master->delete();
        return true;
    }
}
