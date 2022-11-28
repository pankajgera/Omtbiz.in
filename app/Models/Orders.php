<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanySetting;
use Carbon\Carbon;

class Order extends Model
{
    const TO_BE_DISPATCH = 'TO_BE_DISPATCH';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'order_date',
        'expiry_date'
    ];

    protected $appends = [
        'formattedExpiryDate',
        'formattedOrderDate'
    ];

    protected $fillable = [
        'order_date',
        'expiry_date',
        'order_number',
        'user_id',
        'company_id',
        'reference_number',
        'status',
        'sub_total',
        'total',
        'notes',
        'unique_hash',
        'account_master_id'
    ];

    protected $casts = [
        'total' => 'integer',
        'sub_total' => 'integer',
    ];

    public static function getNextOrderNumber($value)
    {
         // Get the last created order
         $lastOrder = Order::where('order_number', 'LIKE', $value . '-%')
                        ->orderBy('created_at', 'desc')
                        ->first();

        if (!$lastOrder) {
            // We get here if there is no order at all
            // If there is no number set it to 0, which will be 1 at the end.
            $number = 0;
        } else {
            $number = explode("-",$lastOrder->order_number);
            $number = $number[1];
        }

        // If we have ORD000001 in the database then we only want the number
        // So the substr returns this 000001

        // Add the string in front and higher up the number.
        // the %05d part makes sure that there are always 6 numbers in the string.
        // so it adds the missing zero's when needed.

        return sprintf('%06d', intval($number) + 1);
    }

    public function inventories()
    {
        return $this->hasMany(InvoiceItem::class)->where('type', 'order');
    }

    public function master()
    {
        return $this->belongsTo(AccountMaster::class, 'account_master_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOrderNumAttribute()
    {
        $position = $this->strposX($this->order_number, "-", 1) + 1;
        return substr($this->order_number, $position);
    }

    public function getOrderPrefixAttribute()
    {
        $prefix = explode("-",$this->order_number)[0];
        return $prefix;
    }

    private function strposX($haystack, $needle, $number)
    {
        if ($number == '1') {
            return strpos($haystack, $needle);
        } elseif ($number > '1') {
            return strpos(
                $haystack,
                $needle,
                $this->strposX($haystack, $needle, $number - 1) + strlen($needle)
            );
        } else {
            return error_log('Error: Value for parameter $number is out of range');
        }
    }

    public function getFormattedExpiryDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->expiry_date)->format($dateFormat);
    }

    public function getFormattedOrderDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->order_date)->format($dateFormat);
    }

    public function scopeOrdersBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'orders.order_date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopeWhereStatus($query, $status)
    {
        return $query->where('orders.status', $status);
    }

    public function scopeWhereOrderNumber($query, $orderNumber)
    {
        return $query->where('orders.order_number', $orderNumber);
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->whereHas('user', function ($query) use ($term) {
                $query->where('name', 'LIKE', '%'.$term.'%')
                    ->orWhere('contact_name', 'LIKE', '%'.$term.'%')
                    ->orWhere('company_name', 'LIKE', '%'.$term.'%');
            });
        }
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('order_number')) {
            $query->whereOrderNumber($filters->get('order_number'));
        }

        if ($filters->get('status')) {
            $query->whereStatus($filters->get('status'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->ordersBetween($start, $end);
        }

        if ($filters->get('customer_id')) {
            $query->whereCustomer($filters->get('customer_id'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'order_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('orders.company_id', $company_id);
    }

    public function scopeWhereCustomer($query, $customer_id)
    {
        $query->where('orders.user_id', $customer_id);
    }

    public static function deleteOrder($id)
    {
        $order = Order::find($id);

        if ($order->items()->exists()) {
            $order->items()->delete();
        }

        $order->delete();

        return true;
    }
}
