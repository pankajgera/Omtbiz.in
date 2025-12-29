<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'name',
        'description',
        'quantity',
        'remaining_quantity',
        'company_id',
        'inventory_id',
    ];

    protected $casts = [
        'price' => 'float',
        'total' => 'float',
        'quantity' => 'float',
        'remaining_quantity' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id');
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
