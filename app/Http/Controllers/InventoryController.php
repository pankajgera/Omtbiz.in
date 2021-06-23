<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Exception;
use Log;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 20;

            $inventories = Inventory::applyFilters($request->only([
                'name',
                'quantity',
                'price',
                'unit',
                'orderByField',
                'orderBy',
            ]))
                ->whereCompany($request->header('company'))
                ->latest()
                ->paginate($limit);

            return response()->json([
                'inventories' => $inventories,
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $inventory = Inventory::find($id);

            return response()->json([
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
        try {
            $inventory = new Inventory();
            $inventory->name = $request->name;
            $inventory->quantity = $request->quantity;
            $inventory->price = $request->price;
            $inventory->unit = $request->unit;
            $inventory->company_id = $request->header('company');
            $inventory->save();

            return response()->json([
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving inventory', [$e->getMessage()]);
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
        try {
            $inventory = Inventory::find($id);
            $inventory->name = $request->name;
            $inventory->quantity = $request->quantity;
            $inventory->price = $request->price;
            $inventory->unit = $request->unit;
            $inventory->company_id = $request->header('company');
            $inventory->save();

            return response()->json([
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating inventory', [$e->getMessage()]);
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
     * Update an existing Inventory Price.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePrice(Request $request, $id)
    {
        try {
            $inventory = Inventory::find($id);
            $inventory->sale_price = $request->sale_price;
            $inventory->save();

            return response()->json([
                'inventory' => $inventory,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating inventory item price', [$e->getMessage()]);
        }
    }
}
