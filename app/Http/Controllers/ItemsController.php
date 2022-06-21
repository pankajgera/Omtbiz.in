<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\AccountMaster;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\TaxType;
use App\Models\Tax;
use Carbon\Carbon;
use Exception;

class ItemsController extends Controller
{
    /**
     * Index page for billty
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $items = Item::where('status', 'Sent')->applyFilters($request->only([
            'search',
            'price',
            'unit',
            'orderByField',
            'orderBy',
        ]))->with('images', 'dispatch')
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        foreach ($items as $each) {
            $master = Invoice::where('dispatch_id', $each->dispatch_id)->first();
            $party_name = AccountMaster::where('id', $master->account_master_id)->first();
            if (isset($party_name)) {
                $each['party_name'] = $party_name->name;
            }
        }

        $itemsToBe = Item::where('status', 'Draft')->applyFilters($request->only([
            'search',
            'price',
            'unit',
            'orderByField',
            'orderBy',
        ]))->with('images', 'dispatch')
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        foreach ($itemsToBe as $each) {
            $master = Invoice::where('dispatch_id', $each->dispatch_id)->first();
            $party_name = AccountMaster::where('id', $master->account_master_id)->first();
            if (isset($party_name)) {
                $each['party_name'] = $party_name->name;
            }
        }

        return response()->json([
            'items' => $items,
            'itemsToBe' => $itemsToBe,
            'taxTypes' => TaxType::latest()->get(),
        ]);
    }

    /**
     * Edit billty
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $item = Item::with('taxes')->find($id);

        return response()->json([
            'item' => $item,
            'taxes' => Tax::whereCompany($request->header('company'))
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Create Item.
     *
     * @param App\Http\Requests\ItemsRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\ItemsRequest $request)
    {
        if (!$request->price) {
            throw new Exception('Price cannot be null');
        }
        $date_format = 'Y-m-d\TH:i:s.v\Z';
        if (strpos($request->date_time, ' ') !== false) {
            $date_format = 'Y-m-d H:i:s';
        }
        $date = Carbon::createFromFormat($date_format, $request->date);

        $item = new Item();
        $item->name = $request->name;
        $item->bill_ty = $request->bill_ty;
        $item->date = $date;
        $item->unit = $request->unit;
        $item->description = $request->description;
        $item->company_id = $request->header('company');
        $item->price = $request->price;
        $item->status = 'Draft';
        $item->dispatch_id = implode(', ', $request->dispatch_id);
        $item->save();

        $image = '';
        if ($request->image) {
            $image = $item->uploadImage($request->image);
        }

        if ($request->has('taxes')) {
            foreach ($request->taxes as $tax) {
                $tax['company_id'] = $request->header('company');
                $item->taxes()->create($tax);
            }
        }

        $item = Item::with('taxes')->find($item->id);

        return response()->json([
            'item' => $item,
            'image' => $image,
        ]);
    }

    /**
     * Update an existing Item.
     *
     * @param App\Http\Requests\ItemsRequest $request
     * @param int                               $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Requests\ItemsRequest $request, $id)
    {
        if (!$request->price) {
            throw new Exception('Price cannot be null');
        }
        $date_format = 'Y-m-d\TH:i:s.v\Z';
        if (strpos($request->date_time, ' ') !== false) {
            $date_format = 'Y-m-d H:i:s';
        }
        $date = Carbon::createFromFormat($date_format, $request->date);

        $item = Item::find($id);
        $item->name = $request->name;
        $item->bill_ty = $request->bill_ty;
        $item->date = $date;
        $item->unit = $request->unit;
        $item->description = $request->description;
        $item->price = $request->price;
        $item->dispatch_id = $request->dispatch_id;
        $item->save();

        $oldTaxes = $item->taxes->toArray();

        foreach ($oldTaxes as $oldTax) {
            Tax::destroy($oldTax['id']);
        }

        if ($request->has('taxes')) {
            foreach ($request->taxes as $tax) {
                $tax['company_id'] = $request->header('company');
                $item->taxes()->create($tax);
            }
        }

        $item = Item::with('taxes')->find($item->id);

        return response()->json([
            'item' => $item,
        ]);
    }

    /**
     * Delete an existing Item.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = Item::deleteItem($id);

        if (!$data) {
            return response()->json([
                'error' => 'item_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $items = [];
        foreach ($request->id as $id) {
            $item = Item::deleteItem($id);
            if (!$item) {
                array_push($items, $id);
            }
        }

        if (empty($items)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * Retrive a draft type dispatch
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDispatch(Request $request)
    {
        $dispatch = Dispatch::where('status', 'Sent')->whereNotNull('invoice_id')
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'dispatch' => $dispatch
        ]);
    }
}
