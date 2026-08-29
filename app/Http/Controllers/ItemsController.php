<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\AccountMaster;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Item;
use Carbon\Carbon;
use Exception;
use stdClass;

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
            'name',
            'unit',
            'orderByField',
            'orderBy',
        ]))->with('images', 'dispatch')
            ->whereCompany($request->header('company'))
            ->latest()
            // Named page param, paired with the itemsToBe paginator below:
            // the Pending and Completed tables on the frontend page
            // independently, but both used to share the plain 'page' query
            // key, so paging one table silently re-paginated the other
            // list's query too whenever both requests happened to fire
            // close together.
            ->paginate($limit, ['*'], 'page');

        foreach ($items as $each) {
            $master = Invoice::where('dispatch_id', $each->dispatch_id)->first();
            if (isset($master)) {
                $party_name = AccountMaster::where('id', $master->account_master_id)->first();
                if (isset($party_name)) {
                    $each['party_name'] = $party_name->name;
                }
            } else {
                $each['party_name'] = '';
            }

            if (false !== strpos($each->dispatch_id, ',')) {
                $each['dispatch'] = Dispatch::whereIn('id', [$each->dispatch_id])->first();
            }
        }

        $itemsToBe = Item::where('status', 'Draft')->applyFilters($request->only([
            'search',
            'name',
            'unit',
            'orderByField',
            'orderBy',
        ]))->with('images', 'dispatch')
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit, ['*'], 'itemsToBePage');

        foreach ($itemsToBe as $each) {
            $master = Invoice::where('dispatch_id', $each->dispatch_id)->first();
            if (isset($master)) {
                $party_name = AccountMaster::where('id', $master->account_master_id)->first();
                if (isset($party_name)) {
                    $each['party_name'] = $party_name->name;
                }
            } else {
                $each['party_name'] = '';
            }

            if (false !== strpos($each->dispatch_id, ',')) {
                $each['dispatch'] = Dispatch::whereIn('id', [$each->dispatch_id])->first();
            }
        }
        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'items' => $items,
            'itemsToBe' => $itemsToBe,
            'sundryDebtorsList' => $sundryDebtorsList,
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
        $item = Item::with(['images'])->where('id', $id)->first();
        $all_dispatch = Dispatch::whereIn('id', explode(',', $item->dispatch_id))->get();
        if (0 < count($all_dispatch)) {
            $item['dispatch'] = $all_dispatch;
        }
        return response()->json([
            'item' => $item,
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
        $date = $this->parseItemDate($request->date);

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

        $item = Item::find($item->id);

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
        $date = $this->parseItemDate($request->date);

        $item = Item::find($id);
        $item->name = $request->name;
        $item->bill_ty = $request->bill_ty;
        $item->date = $date;
        $item->unit = $request->unit;
        $item->description = $request->description;
        $item->price = $request->price;
        $item->dispatch_id = $request->dispatch_id;
        $item->status = 'Sent';
        $item->save();


        $image = '';
        if ($request->image) {
            $image = $item->uploadImage($request->image);
        }

        $item = Item::find($item->id);

        return response()->json([
            'item' => $item,
            'image' => $image,
        ]);
    }

    /**
     * Parse the date posted by the bill-ty form.
     *
     * `items.date` is nullable and plenty of existing rows have no date. The
     * edit form seeds itself from the record, so opening one of those rows and
     * saving posts `date: null` - which used to reach
     * Carbon::createFromFormat('Y-m-d\TH:i:s.v\Z', null) and 500 the request
     * with "Not enough data available to satisfy format".
     *
     * The format is still matched rather than handed to Carbon::parse() on
     * purpose: the ISO string the date picker sends carries a `Z`, and the
     * literal-escaped `\Z` here reads it as a wall-clock time in the app
     * timezone. Carbon::parse() would read the same string as UTC and shift
     * every saved date by the offset.
     *
     * Fractional seconds are matched with `.u`, not the `.v` this used before.
     * `v` caps at three digits, but a date round-tripped through the API comes
     * back with six ("2026-08-17T08:44:56.000000Z"), which threw a second
     * InvalidFormatException - "Trailing data" - on any record that did have a
     * date. `u` accepts both widths and yields the same instant.
     *
     * @param string|null $value
     *
     * @return \Carbon\Carbon|null
     */
    private function parseItemDate($value)
    {
        $value = is_string($value) ? trim($value) : $value;
        if (empty($value)) {
            return null;
        }

        if (strpos($value, ' ') !== false) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value);
        }

        // Date-only, e.g. "2026-08-17". createFromFormat would otherwise fill
        // the time from the current clock.
        if (strpos($value, 'T') === false) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $value);
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
        $items_already_done = Item::whereNotNull('dispatch_id')->pluck('dispatch_id')->toArray();

        $dispatch = Dispatch::where('status', 'Sent')->whereNotNull('invoice_id')
            ->whereCompany($request->header('company'))
            ->whereNotIn('id', $items_already_done)
            ->get();

        foreach ($dispatch as $each) {
            $obj = new stdClass();
            $obj->invoice = Invoice::whereIn('id', [$each->invoice_id])->get();
            $obj->count = Invoice::whereIn('id', [$each->invoice_id])->count();
            foreach ($obj->invoice as $master) {
                $obj->master = AccountMaster::whereIn('id', [$master->account_master_id])->first();
            }
            $each['value'] = $obj;
        }

        return response()->json([
            'dispatch' => $dispatch,
        ]);
    }
}
