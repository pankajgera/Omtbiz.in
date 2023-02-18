<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceTemplate;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    public const STATUS_PAID = 'PAID';
    public const DISPATCH = 'DISPATCH';
    public const TO_BE_DISPATCH = 'TO_BE_DISPATCH';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'invoice_date',
        'due_date'
    ];

    protected $casts = [
        'total' => 'integer',
        'sub_total' => 'integer',
        'discount' => 'float',
        'discount_val' => 'integer',
    ];

    protected $fillable = [
        'invoice_date',
        'due_date',
        'invoice_number',
        'reference_number',
        'user_id',
        'company_id',
        'invoice_template_id',
        'status',
        'paid_status',
        'sub_total',
        'discount_per_item',
        'total',
        'discount',
        'discount_type',
        'discount_val',
        'due_amount',
        'notes',
        'unique_hash',
        'sent',
        'viewed',
        'account_master_id',
        'dispatch_id',
    ];

    protected $appends = [
        'formattedCreatedAt',
        'formattedInvoiceDate',
        'formattedDueDate'
    ];

    public static function getNextInvoiceNumber($value, $company_id)
    {
        // Get the last created order
        $lastOrder = Invoice::orderBy('created_at', 'desc')->where('company_id', $company_id)->first();

        if (!$lastOrder) {
            // We get here if there is no order at all
            // If there is no number set it to 0, which will be 1 at the end.
            $number = 0;
        } else {
            $number = explode("-", $lastOrder->invoice_number);
            $number = $number[2];

        }
        // If we have ORD000001 in the database then we only want the number
        // So the substr returns this 000001

        // Add the string in front and higher up the number.
        // the %06d part makes sure that there are always 6 numbers in the string.
        // so it adds the missing zero's when needed.
        return  sprintf('%06d', intval($number) + 1);
    }

    public function inventories()
    {
        return $this->hasMany(InvoiceItem::class)->where('type', 'invoice');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceTemplate()
    {
        return $this->belongsTo(InvoiceTemplate::class);
    }

    public function master()
    {
        return $this->belongsTo(AccountMaster::class, 'account_master_id');
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

    public function getInvoiceNumAttribute()
    {
        $position = $this->strposX($this->invoice_number, "-", 1) + 1;
        return substr($this->invoice_number, $position);
    }

    public function getInvoicePrefixAttribute()
    {
        $prefix = explode("-", $this->invoice_number)[0];
        return $prefix;
    }

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function getFormattedDueDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->due_date)->format($dateFormat);
    }

    public function getFormattedInvoiceDateAttribute($value)
    {
        return Carbon::parse($this->invoice_date)->format('Y-m-d');
        // $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        // return Carbon::parse($this->invoice_date)->format($dateFormat);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeWhereStatus($query, $status)
    {
        return $query->where('invoices.status', $status);
    }

    public function scopeWherePaidStatus($query, $status)
    {
        return $query->where('invoices.paid_status', $status);
    }

    public function scopeWhereInvoiceNumber($query, $invoiceNumber)
    {
        return $query->where('invoices.invoice_number', 'LIKE', '%' . $invoiceNumber . '%');
    }

    public function scopeInvoicesBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'invoices.invoice_date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->whereHas('user', function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('contact_name', 'LIKE', '%' . $term . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $term . '%');
            });
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('status')) {
            if (
                $filters->get('status') == self::STATUS_PAID
            ) {
                $query->wherePaidStatus($filters->get('status'));
            } else {
                $query->whereStatus($filters->get('status'));
            }
        }

        if ($filters->get('paid_status')) {
            $query->wherePaidStatus($filters->get('status'));
        }

        if ($filters->get('invoice_number')) {
            $query->whereInvoiceNumber($filters->get('invoice_number'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->invoicesBetween($start, $end);
        }

        if ($filters->get('customer_id')) {
            $query->whereCustomer($filters->get('customer_id'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'invoice_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopeWhereCompany($query, $company_id, $filter=null)
    {
        if ($filter==='false') {
            $query->where('invoices.company_id', $company_id)->where('invoice_date', Carbon::now()->format('Y-m-d'));
        } else {
            $query->where('invoices.company_id', $company_id);
        }
    }

    public function scopeWhereCustomer($query, $customer_id)
    {
        $query->where('invoices.account_master_id', $customer_id);
    }
}
