<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'name',
        'invoice_id',
        'date_time',
        'transport',
        'status',
        'company_id'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

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
        //Delete item (bill-ty) if exists in "items" table
        Item::where('dispatch_id', $dispatch->id)->delete();
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
        $invoices = Invoice::whereIn('id', explode(',', $dispatch->invoice_id))->get();
        if ('Sent' === $dispatch->status) {
            foreach ($invoices as $each) {
                $dis = new Dispatch();
                $dis->invoice_id = $each->id;
                $dis->date_time = $dispatch->date_time;
                $dis->time = $dispatch->time;
                $dis->status = 'Draft';
                $dis->transport = $dispatch->transport;
                $dis->person = $dispatch->person;
                $dis->company_id = $dispatch->company_id;
                $dis->save();
            }
            $dispatch->delete();
            //Delete item (bill-ty) if exists in "items" table
            Item::where('dispatch_id', $dispatch->id)->delete();
            return true;
        }
        self::addDispatchBillTy($dispatch, $invoices->sum('total'));
        $dispatch->update([
            'status' => 'Sent',
        ]);
        return true;
    }

    /**
     * Dipatched invoices will create bill-ty
     *
     * @param mixed $dispatch_ids
     * @param int $invoice_total_amount
     * @param int $company_id
     *
     * @return void
     */
    public static function addDispatchBillTy($dispatch_ids, $invoice_total_amount, $company_id)
    {
        $item = new Item();
        $item->name = 'dispatched';
        $item->unit = 'pc';
        $item->description = '';
        $item->company_id = $company_id;
        $item->price = $invoice_total_amount;
        $item->dispatch_id = $dispatch_ids;
        $item->save();
    }
}
