<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Image;
use Storage;
use Illuminate\Support\Facades\Log;

class Note extends Model
{
    protected $fillable = [
        'name',
        'design_no',
        'images_id',
        'rate',
        'average',
        'per_price',
        'note',
        'company_id'
    ];

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }

    public function scopeWhereDesignNo($query, $design_no)
    {
        return $query->where('design_no', 'LIKE', '%' . $design_no . '%');
    }

    public function scopeWhereRate($query, $rate)
    {
        return $query->where('rate', 'LIKE', '%' . $rate . '%');
    }

    public function scopeWhereAverage($query, $average)
    {
        return $query->where('average', 'LIKE', '%' . $average . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('name')) {
            $query->whereName($filters->get('name'));
        }

        if ($filters->get('design_no')) {
            $query->whereDesignNo($filters->get('design_no'));
        }

        if ($filters->get('rate')) {
            $query->whereRate($filters->get('rate'));
        }

        if ($filters->get('average')) {
            $query->whereAverage($filters->get('average'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public static function deleteNote($id)
    {
        $note = Note::find($id);
        $note->delete();
        return true;
    }

    /** Upload image to s3 and add id to items
     *
     * @request Contain image file
     */
    public function uploadImage($request_image)
    {
        //make an Intervention Image object
        $image = Image::make($request_image);
        $fileName = str_random(30) . '-' . time() . '.jpg';

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

        //save Original
        //$image->save($save_paths['original'].$ds.$fileName);
        $save_to_s3_screen_original = $image->stream();

        //resize
        $resized_image = $image->resize(null, 500, function ($constraint) {
            $constraint->aspectRatio();
        });

        //now save it
        $save_to_s3_screen = $resized_image->stream();
        $filesize = $resized_image->filesize();

        //thumbnail

        $save_to_s3_thumb = $image->fit('181', '121')->stream();

        Storage::disk('s3')->put($save_paths['original'] . $ds . $fileName, $save_to_s3_screen_original->__toString());
        Storage::disk('s3')->put($save_paths['screen'] . $ds . 'screen-' . $fileName, $save_to_s3_screen->__toString());
        Storage::disk('s3')->put($save_paths['thumb'] . $ds . 'thumb-' . $fileName, $save_to_s3_thumb->__toString());

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

        //finally free the memory
        $image->destroy();

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
