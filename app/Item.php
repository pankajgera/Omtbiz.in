<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Image;
use Storage;
use Illuminate\Support\Facades\Log;

class Item extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'price',
        'company_id',
        'description',
        'images_id',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    protected $appends = [
        'formattedCreatedAt',
    ];

    public function scopeWhereSearch($query, $search)
    {
        return $query->where('name', 'LIKE', '%'.$search.'%');
    }

    public function scopeWherePrice($query, $price)
    {
        return $query->where('price', $price);
    }

    public function scopeWhereUnit($query, $unit)
    {
        return $query->where('unit', $unit);
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

        if ($filters->get('price')) {
            $query->wherePrice($filters->get('price'));
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

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function estimateItems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public static function deleteItem($id)
    {
        $item = Item::find($id);

        if ($item->taxes()->exists() && $item->taxes()->count() > 0) {
            return false;
        }

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
        return $this->belongsTo(\Crater\Images::class);
    }

    /** Upload image to s3 and add id to items
     *
     * @request Contain image file
     */
    public function uploadImage($request_image)
    {
        //make an Intervention Image object
        $image = Image::make($request_image);
        Log::info('$image', [$image]);
        $fileName = str_random(30).'-'.time().'.jpg';
        Log::info('$fileName', [$fileName]);
        // store our uploaded file in our uploads folder
        // set our results to have our asset path
        $ds = DIRECTORY_SEPARATOR;
        $today = Carbon::now();
        $timely_url = $today->year.$ds.$today->month.$ds.$today->day;
        $save_paths = [];

        $save_paths['original'] = 'userUploads'.$ds.'originals'.$ds.$timely_url;
        $save_paths['thumb'] = 'userUploads'.$ds.'thumbnails'.$ds.$timely_url;
        $save_paths['screen'] = 'userUploads'.$ds.'screen'.$ds.$timely_url;

        foreach ($save_paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0700, true);
            }
        }

        //save Original
        //$image->save($save_paths['original'].$ds.$fileName);
        $save_to_s3_screen_original = $image->stream();

        //resize
        $resized_image = $image->resize(null, 500, function ($constraint) {$constraint->aspectRatio(); });

        //now save it
        $save_to_s3_screen = $resized_image->stream();
        $filesize = $resized_image->filesize();

        //thumbnail

        $save_to_s3_thumb = $image->fit('181', '121')->stream();

        Storage::disk('s3')->put($save_paths['original'].$ds.$fileName, $save_to_s3_screen_original->__toString());
        Storage::disk('s3')->put($save_paths['screen'].$ds.'screen-'.$fileName, $save_to_s3_screen->__toString());
        Storage::disk('s3')->put($save_paths['thumb'].$ds.'thumb-'.$fileName, $save_to_s3_thumb->__toString());

        //get the data for response
        $url = url($save_paths['screen']);
        $originalUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/'.$save_paths['original'].$ds.$fileName;
        $thumbnailUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/'.$save_paths['thumb'].$ds.'thumb-'.$fileName;
        $screenUrl = 'https://s3.ap-south-1.amazonaws.com/cdn.omtbiz.s3/'.$save_paths['screen'].$ds.'screen-'.$fileName;

        //lets prepare the response
        $success = new \stdClass();
        $success->name = $fileName;
        $success->size = $filesize;
        $success->thumbnailUrl = $thumbnailUrl;

        //finally free the memory
        $image->destroy();

        //make an entry in the database
        $photo = new \Crater\Images();
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
