<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager as InterventionImageManager;
use Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Traits\Auditable;

class Item extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'unit',
        'price',
        'company_id',
        'description',
        'images_id',
        'dispatch_id',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    // Sibling models (Invoice, Expense, Receipt, Payment, User, ...) all
    // append formattedCreatedAt so the frontend's "Added On" column
    // (show="formattedCreatedAt") has something to read - Item never did,
    // so that column silently rendered blank despite created_at being set
    // and the accessor itself working fine when called directly.
    protected $appends = ['formattedCreatedAt'];

    public function scopeWhereSearch($query, $search)
    {
        return $query->where('name', 'LIKE', '%' . $search . '%');
    }

    public function scopeWherePrice($query, $price)
    {
        return $query->where('price', $price);
    }

    public function scopeWhereUnit($query, $unit)
    {
        return $query->where('unit', $unit);
    }

    public function scopeWhereName($query, $name)
    {
        $invoices = Invoice::where('account_master_id', $name)->pluck('dispatch_id')->toArray();
        return $query->whereIn('dispatch_id', $invoices);
    }
    public function scopeWhereCompany($query, $company_id)
    {
        // Used to also restrict to today's rows whenever $filter (the
        // 'filterBy' request param) came in as the literal string 'false' -
        // that string check was backwards from the caller's intent
        // (resources/js/views/items/Index.vue sends the JS boolean
        // `applyFilter`, which axios serializes as the *string* "false"
        // whenever no filter is actually applied), so the Pending list was
        // silently limited to today-only on every normal, unfiltered page
        // load and only showed its full history once a filter was applied.
        $query->where('company_id', $company_id);
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('name')) {
            $query->whereName($filters->get('name'));
        }

        if ($filters->get('unit')) {
            $query->whereUnit($filters->get('unit'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);

        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function estimateItems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function dispatch()
    {
        // hasOne, not hasMany: dispatch_id holds a single dispatch's id (as a
        // string), so this always matches at most one Dispatch row. The
        // frontend (resources/js/views/items/Index.vue) reads it as a single
        // object - `row.dispatch.name` - which hasMany broke by serializing
        // it as a one-element array instead, silently rendering blank.
        return $this->hasOne(Dispatch::class, 'id', 'dispatch_id');
    }

    public static function deleteItem($id)
    {
        $item = Item::find($id);

        if ($item->invoiceItems()->exists() && $item->invoiceItems()->count() > 0) {
            return false;
        }

        if ($item->estimateItems()->exists() && $item->estimateItems()->count() > 0) {
            return false;
        }

        $item->delete();

        return true;
    }

    public function images()
    {
        return $this->belongsTo(Images::class, 'images_id');
    }

    /** Upload image to s3 and add id to items
     *
     * @request Contain image file
     */
    public function uploadImage($request_image)
    {
        $manager = InterventionImageManager::usingDriver(config('image.driver'));
        $image = $manager->decode($request_image);
        $fileName = Str::random(30) . '-' . time() . '.jpg';

        // store our uploaded file in our uploads folder
        // set our results to have our asset path
        $ds = DIRECTORY_SEPARATOR;
        $today = Carbon::now();
        $timely_url = $today->year . $ds . $today->month . $ds . $today->day;
        $save_paths = [];

        $save_paths['original'] = 'userUploads' . $ds . 'originals' . $ds . $timely_url;
        $save_paths['thumb'] = 'userUploads' . $ds . 'thumbnails' . $ds . $timely_url;
        $save_paths['screen'] = 'userUploads' . $ds . 'screen' . $ds . $timely_url;

        // foreach ($save_paths as $path) {
        //     if (!is_dir($path)) {
        //         mkdir($path, 0700, true);
        //     }
        // }

        $original = $image->encode(new JpegEncoder());
        $screen = (clone $image)->scale(height: 500)->encode(new JpegEncoder());
        $thumbnail = (clone $image)->cover(181, 121)->encode(new JpegEncoder());
        $filesize = strlen((string) $screen);

        Storage::disk('s3')->put($save_paths['original'] . $ds . $fileName, (string) $original);
        Storage::disk('s3')->put($save_paths['screen'] . $ds . 'screen-' . $fileName, (string) $screen);
        Storage::disk('s3')->put($save_paths['thumb'] . $ds . 'thumb-' . $fileName, (string) $thumbnail);

        //get the data for response
        $url = url($save_paths['screen']);
        $originalUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/' . $save_paths['original'] . $ds . $fileName;
        $thumbnailUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/' . $save_paths['thumb'] . $ds . 'thumb-' . $fileName;
        $screenUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/' . $save_paths['screen'] . $ds . 'screen-' . $fileName;

        //lets prepare the response
        $success = new \stdClass();
        $success->name = $fileName;
        $success->size = $filesize;
        $success->thumbnailUrl = $thumbnailUrl;

        unset($image);

        //make an entry in the database
        $photo = new \App\Models\Images();
        $photo->item_id = $this->id;
        $photo->name = $fileName;
        $photo->image_path = $screenUrl;
        $photo->thumbnail_path = $thumbnailUrl;
        $photo->original_image_path = $originalUrl;
        $photo->save();

        $this->update([
            'images_id' => $photo->id,
        ]);
        // return our results in a files object
        return true;
    }
}
