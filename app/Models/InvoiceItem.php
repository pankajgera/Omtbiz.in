<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'name',
        'inventory_id',
        'description',
        'company_id',
        'quantity',
        'price',
        'sale_price',
        'total',
        'type'
    ];

    protected $casts = [
        'price' => 'integer',
        'total' => 'integer',
        'quantity' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public function scopeInvoicesBetween($query, $start, $end)
    {
        $query->whereHas('invoice', function ($query) use ($start, $end) {
            $query->whereBetween(
                'invoice_date',
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        });
    }

    public function scopeApplyInvoiceFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->invoicesBetween($start, $end);
        }
    }

    public function scopeInventoryAttributes($query)
    {
        $query->select(
            DB::raw('sum(quantity) as total_quantity, sum(total) as total_amount, invoice_inventory.name')
        )->groupBy('invoice_inventory.name');

    }
}
