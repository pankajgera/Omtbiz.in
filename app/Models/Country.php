<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public function address()
    {
        return $this->hasMany(Address::class);
    }
}
