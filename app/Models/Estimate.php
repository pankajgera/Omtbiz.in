<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanySetting;
use Carbon\Carbon;

class Estimate extends Model
{
    public const TO_BE_DISPATCH = 'TO_BE_DISPATCH';
    public const DRAFT = 'DRAFT';
    public const SENT = 'SENT';
    public const COMPLETED = 'COMPLETED';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'estimate_date',
        'expiry_date'
    ];

    protected $appends = [
        'formattedExpiryDate',
        'formattedEstimateDate'
    ];

    protected $fillable = [
        'estimate_date',
        'expiry_date',
        'estimate_number',
        'user_id',
        'company_id',
        'reference_number',
        'estimate_template_id',
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

    public static function getNextEstimateNumber($value, $company_id)
    {
        // Get the last created order
        $lastOrder = Estimate::where('estimate_number', 'LIKE', $value . '-%')
                       ->where('company_id', $company_id)
                       ->orderBy('created_at', 'desc')
                       ->first();

        if (!$lastOrder) {
            // We get here if there is no order at all
            // If there is no number set it to 0, which will be 1 at the end.
            $number = 0;
        } else {
            $number = explode("-", $lastOrder->estimate_number);
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
        return $this->hasMany(InvoiceItem::class)->where('type', 'estimate');
    }

    public function master()
    {
        return $this->belongsTo(AccountMaster::class, 'account_master_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\EstimateItem');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function estimateTemplate()
    {
        return $this->belongsTo('App\Models\EstimateTemplate');
    }

    public function getEstimateNumAttribute()
    {
        $position = $this->strposX($this->estimate_number, "-", 1) + 1;
        return substr($this->estimate_number, $position);
    }

    public function getEstimatePrefixAttribute()
    {
        $prefix = explode("-", $this->estimate_number)[0];
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

    public function getFormattedEstimateDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->estimate_date)->format($dateFormat);
    }

    public function scopeEstimatesBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'estimates.estimate_date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopeWhereStatus($query, $status)
    {
        return $query->where('estimates.status', $status);
    }

    public function scopeWhereEstimateNumber($query, $estimateNumber)
    {
        return   $query->where('estimates.estimate_number', 'LIKE', '%'.$estimateNumber.'%');
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

        if ($filters->get('estimate_number')) {
            $query->whereEstimateNumber($filters->get('estimate_number'));
        }

        if ($filters->get('status')) {
            $query->whereStatus($filters->get('status'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->estimatesBetween($start, $end);
        }

        if ($filters->get('customer_id')) {
            $query->whereCustomer($filters->get('customer_id'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'estimate_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id, $filter=null)
    {
        // dd($filter);
        if ($filter==='false') {
            $query->where('estimates.company_id', $company_id)->where('estimates.estimate_date', Carbon::now()->format('Y-m-d'));
        } else {
            $query->where('estimates.company_id', $company_id);
        }
    }

    public function scopeWhereCustomer($query, $customer_id)
    {
        $query->where('estimates.account_master_id', $customer_id);
    }

    public static function deleteEstimate($id)
    {
        $estimate = Estimate::find($id);

        if ($estimate->items()->exists()) {
            $estimate->items()->delete();
        }

        $estimate->delete();

        return true;
    }
}
