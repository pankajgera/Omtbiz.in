<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A deleted invoice, voucher, receipt or payment waiting in the recycle bin.
 * `meta` lists the rows trashed with it and the side effects to undo on restore.
 */
class RecycleBinEntry extends Model
{
    public const RETENTION_DAYS = 7;

    protected $fillable = [
        'company_id',
        'resource_type',
        'resource_id',
        'label',
        'party',
        'amount',
        'meta',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'float',
        'deleted_at' => 'datetime',
    ];

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }

    public function purgeAt()
    {
        return $this->deleted_at->copy()->addDays(self::RETENTION_DAYS);
    }

    public function scopeExpired($query)
    {
        return $query->where('deleted_at', '<=', now()->subDays(self::RETENTION_DAYS));
    }
}
