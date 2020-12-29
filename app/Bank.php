<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['name', 'amount', 'date'];
}
