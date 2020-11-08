<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $fillable = [
        'voucher_id',
        'date',
        'type',
        'bill_no',
        'account',
        'debit',
        'credit',
        'balance',
        'short_narration',
    ];

    public function voucher()
    {
        return $this->belongsTo(\Crater\Voucher::class);
    }
}
