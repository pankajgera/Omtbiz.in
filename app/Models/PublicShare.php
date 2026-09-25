<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicShare extends Model
{
    protected $fillable = [
        'token',
        'company_id',
        'created_by',
        'resource_type',
        'resource_id',
        'parameters',
        'revoked_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'revoked_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }
}
