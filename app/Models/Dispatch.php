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
        $invoices = Invoice::where('account_master_id', $name)->pluck('id')->toArray();
        if (! count($invoices)) {
            return $query->whereRaw('1 = 0');
        }

        // invoice_id can be a comma-separated list for multi-parcel dispatches
        return $query->where(function ($q) use ($invoices) {
            foreach ($invoices as $invoiceId) {
                $q->orWhereRaw('FIND_IN_SET(?, REPLACE(invoice_id, " ", ""))', [(string) $invoiceId]);
            }
        });
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
            [$start->copy()->startOfDay()->format('Y-m-d H:i:s'), $end->copy()->endOfDay()->format('Y-m-d H:i:s')]
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
        // Default list (no filters) shows today's dispatches in Asia/Kolkata
        if ($filter === 'false' || $filter === false || $filter === 0 || $filter === '0') {
            $query->where(
                DB::raw("(DATE_FORMAT(date_time,'%Y-%m-%d'))"),
                Carbon::now('Asia/Kolkata')->format('Y-m-d')
            );
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
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $dispatch->invoice_id)));
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();

            if ('Sent' === $dispatch->status) {
                $dispatch->update([
                    'status' => 'Draft',
                ]);
                foreach ($invoices as $invoice) {
                    $invoice->update([
                        'status' => 'TO_BE_DISPATCH',
                        'paid_status' => 'TO_BE_DISPATCH',
                    ]);
                }
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
                $each->update([
                    'dispatch_id' => $dispatch->id,
                    'paid_status' => 'DISPATCHED',
                    'status' => 'COMPLETED',
                ]);
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
        if ($same_invoice_dispatch->isNotEmpty() && false !== strpos($same_invoice_dispatch->first()->invoice_id, ',')) {
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $same_invoice_dispatch->first()->invoice_id)));
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();
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
