<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    protected $fillable = [
        'type',
        'date',
        'account_ledger_id',
        'account_master_id',
        'account',
        'debit',
        'credit',
        'short_narration',
        'related_voucher',
        'company_id',
        'invoice_id',
        'invoice_item_id',
        'voucher_type',
        'receipt_id',
    ];

    public function accountMaster()
    {
        return $this->belongsTo(\App\Models\AccountMaster::class);
    }

    public function accountLedger()
    {
        return $this->belongsTo(\App\Models\AccountLedger::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function receipt()
    {
        return $this->belongsTo(\App\Models\Receipt::class);
    }

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', 'LIKE', '%' . $type . '%');
    }

    public function scopeWhereAccount($query, $account)
    {
        return $query->where('account', 'LIKE', '%' . $account . '%');
    }

    public function scopeWhereDebitAmount($query, $debit)
    {
        return $query->where('debit', 'LIKE', '%' . $debit . '%');
    }

    public function scopeWhereCreditAmount($query, $credit)
    {
        return $query->where('credit', 'LIKE', '%' . $credit . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id, $filter=null)
    {
        if ($filter==='false') {
            $reciepts = Receipt::where('receipt_date', Carbon::now()->format('Y-m-d'))->pluck('id')->toArray();
            $invoices = Invoice::where('invoice_date', Carbon::now()->format('Y-m-d'))->pluck('id')->toArray();
            $vouchers = $this::where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), Carbon::now()->format('Y-m-d'))->whereNull('receipt_id')->whereNull('invoice_id')->pluck('id')->toArray();
            return $query->whereIn('receipt_id', $reciepts)->orWhereIn('invoice_id', $invoices)->orWhereIn('id', $vouchers);
        } else {
            $query->where('company_id', $company_id);
        }
    }

    public function scopeVoucherBetween($query, $start, $end)
    {
        $reciepts = Receipt::whereBetween('receipt_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->pluck('id')->toArray();
        $invoices = Invoice::whereBetween('invoice_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->pluck('id')->toArray();
        $vouchers = $this::whereBetween(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), [$start->format('Y-m-d'), $end->format('Y-m-d')])->whereNull('receipt_id')->whereNull('invoice_id')->pluck('id')->toArray();

        return $query->whereIn('receipt_id', $reciepts)->orWhereIn('invoice_id', $invoices)->orWhereIn('id', $vouchers);

        // return $query->whereBetween(
        //     'date',
        //     [$start->format('Y-m-d'), $end->format('Y-m-d')]
        // );
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('type')) {
            $query->whereType($filters->get('type'));
        }
        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->voucherBetween($start, $end);
        }
        if ($filters->get('account')) {
            $query->whereAccount($filters->get('account'));
        }

        if ($filters->get('debit')) {
            $query->whereDebit($filters->get('debit'));
        }

        if ($filters->get('credit')) {
            $query->whereCredit($filters->get('credit'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteAccountLedger($id)
    {
        $master = self::find($id);
        $master->delete();
        return true;
    }
}
