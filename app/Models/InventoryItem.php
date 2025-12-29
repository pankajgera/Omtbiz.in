<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'inventory_id',
        'quantity',
        'worker_name',
        'price',
        'sale_price',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

}
