<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CompanySetting;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\Auditable;

class Company extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Auditable;

    protected $fillable = ['name', 'logo', 'unique_hash'];

    protected $appends=['logo'];

    public function getLogoAttribute()
    {
        $logo = $this->getMedia('logo')->first();
        if ($logo) {
            return  asset($logo->getUrl());
        }
        return ;
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function settings()
    {
        return $this->hasMany(CompanySetting::class);
    }
}
