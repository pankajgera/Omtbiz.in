<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'type',
        'account_master_id',
        'account',
        'debit_amount',
        'credit_amount',
        'short_narration',
    ];

    public function accountMaster()
    {
        return $this->belongsTo(\Crater\AccountMaster::class);
    }
}
