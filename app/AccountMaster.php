<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class AccountMaster extends Model
{
    protected $fillable = [
        'name',
        'group',
        'address',
    ];
}
