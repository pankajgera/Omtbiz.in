<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    protected $fillable = [
        'estimate_id',
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
        'price' => 'float',
        'sale_price' => 'float',
        'discount_val' => 'float',
        'tax' => 'float',
        'total' => 'float',
        'quantity' => 'float',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
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
