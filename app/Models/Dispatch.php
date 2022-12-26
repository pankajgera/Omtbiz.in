<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function scopeWhereName($query, $name)
    {
        return AccountMaster::where('name', $name);
    }

    public function scopeWhereDesignNo($query, $date_time)
    {
        $date = Carbon::parse($date_time)->format('Y-m-d');
        return $query->where(DB::raw("(DATE_FORMAT(date_time,'%Y-%m-%d'))"), $date);
    }
    public function scopeDisptachBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'date_time',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }
    public function scopeWhereAverage($query, $transport)
    {
        return $query->where('transport', 'LIKE', '%' . $transport . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereCompany($query, $company_id, $filter=null)
    {
        $query->where('company_id', $company_id);
        if ($filter==='false') {
            $query->where('company_id', $company_id)->where(DB::raw("(DATE_FORMAT(date_time,'%Y-%m-%d'))"), Carbon::now()->format('Y-m-d'));
        } else {
        }
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

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->disptachBetween($start, $end);
        }
    }

    /**
     * Delete selected dispatch item
     *
     * @param Dispatch $id
     * @return void
     */
    public static function deleteDispatch($id)
    {
        $dispatch = Dispatch::find($id);
        if (isset($dispatch)) {
            $dispatch->delete();
            //Delete item (bill-ty) if exists in "items" table
            Item::where('dispatch_id', $dispatch->id)->delete();
        }
    }

    /**
     * Move dispatch to draft or completed
     *
     * @param  $id
     * @param  $company_id
     * @return Boolean
     */
    public static function moveDispatch($id, $company_id)
    {
        //Selected dispatch might have multiple invoices
        $same_invoice_dispatch = Dispatch::whereIn('invoice_id', [Dispatch::where('id', $id)->value('invoice_id')])->get();
        foreach ($same_invoice_dispatch as $dispatch) {
            if (false === strpos($dispatch->invoice_id, ',')) {
                $invoices = Invoice::whereIn('id', explode(',', $dispatch->invoice_id))->get();
            } else {
                $invoices = Invoice::where('id', $dispatch->invoice_id)->get();
            }
            if ('Sent' === $dispatch->status) {
                $dispatch->update([
                    'status' => 'Draft',
                ]);
                self::removeDispatchBillTy($dispatch);
                continue;
            }
            foreach ($invoices as $each) {
                if (!$dispatch->name) {
                    $dispatch->update([
                        'name' => $each->invoice_number,
                    ]);
                } else {
                    if (false === strpos($dispatch->name, $each->invoice_number)) {
                        $dispatch->update([
                            'name' => $dispatch->name . ', ' . $each->invoice_number,
                        ]);
                    }
                }
            }

            $dispatch->update([
                'status' => 'Sent',
            ]);
            //If invoice contain more than one,
            //then don't add bill ty here
            if (false === strpos($dispatch->invoice_id, ',')) {
                self::addDispatchBillTy($dispatch, $invoices->sum('total'), $company_id, []);
            }
        }
        //Add bill-ty for multiple dispatch (invoice)
        if (false !== strpos($same_invoice_dispatch->first()->invoice_id, ',')) {
            self::addDispatchBillTy($same_invoice_dispatch->first(), $invoices->sum('total'), $company_id, $same_invoice_dispatch->pluck('id')->toArray());
        }
        return true;
    }

    /**
     * Dipatched invoices will create bill-ty
     *
     * @param mixed $dipatch
     * @param int $invoice_total_amount
     * @param int $company_id
     * @param ?array $dispatch_ids
     *
     * @return void
     */
    public static function addDispatchBillTy($dispatch, $invoice_total_amount, $company_id, ?array $dispatch_ids)
    {
        $item = new Item();
        $item->name = $dispatch->name;
        $item->unit = 'pc';
        $item->description = '';
        $item->company_id = $company_id;
        $item->price = $invoice_total_amount;
        $item->dispatch_id = $dispatch_ids ? implode(', ', $dispatch_ids) : $dispatch->id;
        $item->status = 'Draft';
        $item->save();
    }

    /**
     * Remove bill ty if dispatch is moved tobe
     *
     * @param Dispatch $dispatch
     */
    public static function removeDispatchBillTy(Dispatch $dispatch)
    {
        Item::where('dispatch_id', $dispatch->id)->delete();
    }
}
