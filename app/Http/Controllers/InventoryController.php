<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Invoice;
use Carbon\Carbon;
use Exception;
use Log;

class InventoryController extends Controller
{
    /**
     * Inventory index
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 20;

            $inventories = Inventory::applyFilters($request->only([
                'name',
                'orderByField',
                'orderBy',
            ]))
                ->whereCompany($request->header('company'))
                ->orderBy('id', 'desc')
                ->paginate($limit);

            foreach($inventories as $each) {
                $lastest_item = InventoryItem::where('inventory_id', $each->id)->orderBy('id', 'desc')->first();
                $each['quantity'] = $each->quantity;
                $each['price'] = $lastest_item->price;
                $each['sale_price'] = $lastest_item->sale_price;
                $each['unit'] = $lastest_item->unit;
                $each['worker_name'] = $lastest_item->worker_name;
            }

            return response()->json([
                'inventories' => $inventories,
                'total' => $inventories->count(),
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Edit inventory
     */
    public function edit(Request $request, $id)
    {
        try {
            $inventory = Inventory::with(['inventoryItem'])->find($id);
            $related_inventories = InventoryItem::where('inventory_id', $id)->orderBy('id', 'asc')->get();
            foreach($related_inventories as $each) {
                $each->date_time = Carbon::parse($each->created_at, 'Asia/Kolkata')->toDateTimeString();
            }
            return response()->json([
                'related_inventories' => 1 < count($related_inventories) ? $related_inventories : [],
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Create Inventory.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$request->price) {
            throw new Exception('Price cannot be null');
        }
        try {
            $find_inventory = Inventory::where('name', $request->name)->where('company_id', $request->header('company'))->first();
            if (empty ($find_inventory)) {
                $inventory = new Inventory();
                $inventory->name = $request->name;
                $inventory->company_id = $request->header('company');
                $inventory->save();

                $items = new InventoryItem();
                $items->inventory_id = $inventory->id;
                $items->worker_name = $request->worker_name;
                $items->quantity = $request->quantity;
                $items->price = $request->price;
                $items->sale_price = $request->sale_price;
                $items->unit = $request->unit;
                $items->save();

                $inventory->updateInventoryQuantity($items->quantity);
            } else {
                $items = new InventoryItem();
                $items->inventory_id = $find_inventory->id;
                $items->worker_name = $request->worker_name;
                $items->quantity = $request->quantity;
                $items->price = $request->price;
                $items->sale_price = $request->sale_price;
                $items->unit = $request->unit;
                $items->save();

                $find_inventory->updateInventoryQuantity($items->quantity);
            }

            return response()->json([
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving inventory', [$e]);
        }
    }

    /**
     * Update an existing Inventory.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$request->price) {
            throw new Exception('Price cannot be null');
        }
        try {
            $inventory = Inventory::find($id);
            $inventory->name = $request->name;
            $inventory->company_id = $request->header('company');
            $inventory->save();

            //Update latest entry
            $items = InventoryItem::where('inventory_id', $inventory->id)->orderBy('id', 'desc')->first();
            $items->worker_name = $request->worker_name;
            $items->quantity = $request->quantity;
            $items->price = $request->price;
            $items->sale_price = $request->sale_price;
            $items->unit = $request->unit;
            $items->save();

            $inventory->updateInventoryQuantity($items->quantity);

            return response()->json([
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating inventory', [$e]);
        }
    }

    /**
     * Delete an existing Inventory.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = Inventory::deleteInventory($id);

        if (!$data) {
            return response()->json([
                'error' => 'inventory_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Inventory.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $inventory = [];
        foreach ($request->id as $id) {
            $inventory = Inventory::deleteInventory($id);
            if (!$inventory) {
                array_push($inventory, $id);
            }
        }

        if (empty($inventory)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'inventory' => $inventory,
        ]);
    }

    /**
     * Increase inventory price
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function increasePrice(Request $request)
    {
        $inventory = Inventory::whereIn('id', $request->selected_ids)->get();

        foreach ($inventory as $each) {
            InventoryItem::where('id', $each->id)->orderBy('id', 'desc')->update([
                'price' => $request->price,
                'sale_price' => $request->sale_price,
            ]);
        }
        return response()->json([
            'inventory' => $inventory,
        ]);
    }
}
