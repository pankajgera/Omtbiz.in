<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'name',
        'date_time',
        'transport',
        'status',
        'company_id'
    ];

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }

    public function scopeWhereDesignNo($query, $date_time)
    {
        return $query->where('date_time', 'LIKE', '%' . $date_time . '%');
    }

    public function scopeWhereAverage($query, $transport)
    {
        return $query->where('transport', 'LIKE', '%' . $transport . '%');
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

        if ($filters->get('date_time')) {
            $query->whereDesignNo($filters->get('date_time'));
        }

        if ($filters->get('transport')) {
            $query->whereAverage($filters->get('transport'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    /**
     * Delete selected dispatch item
     *
     * @param Dispatch $id
     * @return bool
     */
    public static function deleteDispatch($id)
    {
        $dispatch = Dispatch::find($id);
        $dispatch->delete();
        return true;
    }

    /**
     * Move dispatch to draft or completed
     *
     * @param Dispatch $id
     * @return bool
     */
    public static function moveDispatch($id)
    {
        $dispatch = Dispatch::find($id);
        $status = 'Completed';
        if ('Completed' === $dispatch->status) {
            $status = 'Draft';
        }
        $dispatch->update([
            'status' => $status
        ]);
        return true;
    }
}
