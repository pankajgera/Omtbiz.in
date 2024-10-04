<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credits extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_ledger_id',
        'credits',
        'credits_date',
        'due_amount'
    ];
}
