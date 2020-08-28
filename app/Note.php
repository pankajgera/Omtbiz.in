<?php

namespace Crater;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'name',
        'design_no',
        'rate',
        'average',
        'per_price',
    ];

    public function scopeWhereSearch($query, $search)
    {
        return $query->where('name', 'LIKE', '%'.$search.'%');
    }

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function scopeWhereDesignNo($query, $design_no)
    {
        return $query->where('design_no', $design_no);
    }

    public function scopeWhereRate($query, $rate)
    {
        return $query->where('rate', $rate);
    }

    public function scopeWhereAverage($query, $average)
    {
        return $query->where('average', $average);
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
}
