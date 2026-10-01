<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['company_id', 'option', 'value'];

    /**
     * Values already read in this request, keyed by company and option. Date-format
     * accessors call getSetting() for every row they serialise, which was one query per
     * row (15,000+ on a large ledger). Cleared whenever any setting is saved or deleted.
     */
    private static array $resolved = [];

    public static function flushResolved(): void
    {
        static::$resolved = [];
    }

    protected static function booted()
    {
        static::saved(fn () => static::$resolved = []);
        static::deleted(fn () => static::$resolved = []);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function setSetting($key, $setting, $company_id)
    {
        $old = self::whereOption($key)->whereCompany($company_id)->first();

        if ($old) {
            $old->value = $setting;
            $old->save();
            return;
        }

        $set = new CompanySetting();
        $set->option = $key;
        $set->value = $setting;
        $set->company_id = $company_id;
        $set->save();
    }

    public static function getSetting($key, $company_id)
    {
        $cacheKey = $company_id . '|' . $key;
        if (array_key_exists($cacheKey, static::$resolved)) {
            return static::$resolved[$cacheKey];
        }

        return static::$resolved[$cacheKey] = static::readSetting($key, $company_id);
    }

    private static function readSetting($key, $company_id)
    {
        $setting = static::whereOption($key)->whereCompany($company_id)->first();

        if ($setting && $setting->value !== null && $setting->value !== '') {
            return $setting->value;
        }

        $defaults = [
            'carbon_date_format' => 'd M Y',
            'moment_date_format' => 'DD MMM YYYY',
        ];

        return $defaults[$key] ?? null;
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public static function getInventoryType($key, $company_id)
    {
        $setting = static::whereOption($key)->whereCompany($company_id)->first();

        if ($setting) {
            return $setting->value;
        } else {
            return null;
        }
    }
}
