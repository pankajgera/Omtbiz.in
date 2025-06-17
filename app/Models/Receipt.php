<?php

namespace App\Models;

use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Receipt extends Model
{
    use softDeletes;

    public const RECEIPT_MODE_CHECK = 'CHECK';
    public const RECEIPT_MODE_OTHER = 'OTHER';
    public const RECEIPT_MODE_CASH = 'CASH';
    public const RECEIPT_MODE_CREDIT_CARD = 'CREDIT_CARD';
    public const RECEIPT_MODE_BANK_TRANSFER = 'BANK_TRANSFER';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'receipt_date' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'invoice_id',
        'receipt_date',
        'company_id',
        'notes',
        'receipt_number',
        'receipt_status',
        'receipt_mode',
        'amount',
        'account_master_id'
    ];

    protected $appends = [
        'formattedCreatedAt',
        'formattedReceiptDate'
    ];


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

    public function getReceiptNumAttribute()
    {
        $position = $this->strposX($this->receipt_number, "-", 1) + 1;
        return substr($this->receipt_number, $position);
    }

    public static function getNextReceiptNumber($value, $company_id)
    {
        // Get the last created order
        $receipt = Receipt::where('receipt_number', 'LIKE', $value . '-%')
            ->where('company_id', $company_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$receipt) {
            // We get here if there is no order at all
            // If there is no number set it to 0, which will be 1 at the end.
            $number = 0;
        } else {
            $number = explode("-", $receipt->receipt_number);
            $number = $number[1];
        }
        // If we have ORD000001 in the database then we only want the number
        // So the substr returns this 000001

        // Add the string in front and higher up the number.
        // the %05d part makes sure that there are always 6 numbers in the string.
        // so it adds the missing zero's when needed.

        return sprintf('%06d', intval($number) + 1);
    }

    public function getReceiptPrefixAttribute()
    {
        $prefix = explode("-", $this->receipt_number)[0];
        return $prefix;
    }


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function master()
    {
        return $this->belongsTo(AccountMaster::class, 'account_master_id');
    }

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function getFormattedReceiptDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->receipt_date)->format($dateFormat);
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

    public function scopeReceiptNumber($query, $receiptNumber)
    {
        return $query->where('receipts.receipt_number', 'LIKE', '%' . $receiptNumber . '%');
    }

    public function scopeReceiptMode($query, $receiptMode)
    {
        return $query->where('receipts.receipt_mode', $receiptMode);
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id, $filter=null)
    {
        if ($filter==='false') {
            $query->where('receipts.company_id', $company_id)->where('receipts.receipt_date', Carbon::now()->format('Y-m-d'));
        } else {
            $query->where('receipts.company_id', $company_id);
        }
    }

    public function scopeWhereCustomer($query, $customer_id)
    {
        $query->where('receipts.account_master_id', $customer_id);
    }

    public function scopeReceiptBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'receipts.receipt_date',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }


    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('receipt_number')) {
            $query->receiptNumber($filters->get('receipt_number'));
        }

        if ($filters->get('receipt_mode')) {
            $query->receiptMode($filters->get('receipt_mode'));
        }

        if ($filters->get('customer_id')) {
            $query->whereCustomer($filters->get('customer_id'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'receipt_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->receiptBetween($start, $end);
        }
    }
}
