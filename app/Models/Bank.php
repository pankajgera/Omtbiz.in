<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Bank extends Model
{
    use Auditable;

    protected $table = 'bank';

    protected $fillable = ['name', 'amount', 'date'];

    public function scopeApplyFilters($query, array $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }
        if (isset($filters['amount']) && $filters['amount'] !== '') {
            $query->where('amount', $filters['amount']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }
        $field = $filters['orderByField'] ?? 'created_at';
        if (!in_array($field, ['name', 'amount', 'date', 'created_at'], true)) {
            $field = 'created_at';
        }
        return $query->orderBy($field, ($filters['orderBy'] ?? 'desc') === 'asc' ? 'asc' : 'desc');
    }

    public static function deleteBank($id)
    {
        return static::findOrFail($id)->delete();
    }
}
