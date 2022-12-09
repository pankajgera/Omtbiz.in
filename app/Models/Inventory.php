<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'worker_name',
        'price',
        'sale_price',
        'unit',
        'company_id'
    ];

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }
    public function scopeWhereWorkerName($query, $worker_name)
    {
        return $query->where('worker_name', 'LIKE', '%' . $worker_name . '%');
    }

    public function scopeWhereQuantity($query, $quantity)
    {
        return $query->where('quantity', 'LIKE', '%' . $quantity . '%');
    }

    public function scopeWherePrice($query, $price)
    {
        return $query->where('price', 'LIKE', '%' . $price . '%');
    }

    public function scopeWhereUnit($query, $unit)
    {
        return $query->where('unit', 'LIKE', '%' . $unit . '%');
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

        if ($filters->get('name')) {
            $query->whereName($filters->get('name'));
        }
        if ($filters->get('worker_name')) {
            $query->whereWorkerName($filters->get('worker_name'));
        }

        if ($filters->get('quantity')) {
            $query->whereQuantity($filters->get('quantity'));
        }

        if ($filters->get('price')) {
            $query->wherePrice($filters->get('price'));
        }

        if ($filters->get('unit')) {
            $query->whereUnit($filters->get('unit'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteInventory($id)
    {
        $inventory = Inventory::find($id);
        $inventory->delete();
        return true;
    }
}
