<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $fillable = [
        'order_id',
        'name',
        'item_id',
        'description',
        'quantity',
        'company_id',
        'price',
        'sale_price',
        'total',
        'inventory_id',
    ];

    protected $casts = [
        'price' => 'integer',
        'total' => 'integer',
        'quantity' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItems::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }
}
