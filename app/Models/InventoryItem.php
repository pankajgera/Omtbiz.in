<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class InventoryItem extends Model
{
    use Auditable;

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
        'price' => 'float',
        'sale_price' => 'float',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

}
