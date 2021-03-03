<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    protected $fillable = [
        'item_id',
        'caption',
        'name',
        'description',
        'image_path',
        'thumbnail_path',
        'original_image_path',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\Item::class);
    }
}
