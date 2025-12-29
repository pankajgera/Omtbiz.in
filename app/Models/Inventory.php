<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inventory extends Model
{
    protected $fillable = [
        'name',
        'company_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function inventoryItem()
    {
        return $this->hasMany(InventoryItem::class, 'inventory_id');
    }

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }
    public function scopeWhereWorkerName($query, $worker_name)
    {
        $inventory_item = InventoryItem::where('worker_name', $worker_name)->pluck('inventory_id')->toArray();
        return $query->whereIn('id', $inventory_item);
        // return $query->where('worker_name', 'LIKE', '%' . $worker_name . '%');
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

    public function scopeItemBetween($query, $start, $end)
    {
        $inventory_item = InventoryItem::pluck('inventory_id')->toArray();
        $invoice_item = InvoiceItem::whereIn('inventory_id', $inventory_item)->get();
        foreach($invoice_item as $item) {
            $invoice = Invoice::where('id', $item->invoice_id)->first();
            return $query->whereBetween(
                $invoice->invoice_date->format('Y-m-d'),
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        }
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

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->itemBetween($start, $end);
        }
    }

    public static function deleteInventory($id)
    {
        $inventory = Inventory::find($id);
        //Don't allow to delete inventory if invoice is preset
        if (! InvoiceItem::where('inventory_id', $id)->exists()) {
            InventoryItem::where('inventory_id', $inventory->id)->delete();
            $inventory->delete();
            return true;
        }
        throw new Exception('Inventory used in the invoice, cannot delete it.');
    }

    public function updateInventoryQuantity($new_item_quantity)
    {
        $total = Inventory::where('name', $this->name)->first();
        $this->update([
            'quantity' => $total->quantity + $new_item_quantity,
        ]);
    }
}
