<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMaster extends Model
{
    protected $fillable = [
        'name',
        'groups',
        'address',
        'country',
        'state',
        'opening_balance',
        'type'
    ];

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }

    public function scopeWhereGroups($query, $groups)
    {
        return $query->where('groups', 'LIKE', '%' . $groups . '%');
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

        if ($filters->get('groups')) {
            $query->whereGroups($filters->get('groups'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteAccountMaster($id)
    {
        $master = self::find($id);
        $master->delete();
        return true;
    }

    /**
     * Update account master opening balance
     *
     * @param $id
     * @param $closing_balance
     */
    public static function updateOpeningBalance($id, $closing_balance)
    {
        $master = self::find($id);
        $opening = (int) $master->opening_balance;
        $closing = (int) $closing_balance ? (int) $closing_balance : 0.00;
        $type = ($opening > $closing) ? ($master->type) : (('Cr' === $master->type) ? 'Dr' : 'Cr');
        $master->update([
            'type' => $type,
            'opening_balance' => $closing,
        ]);
        return true;
    }
}
