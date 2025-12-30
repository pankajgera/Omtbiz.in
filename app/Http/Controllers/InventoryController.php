<?php

namespace App\Http\Controllers;

use App\Models\AccountMaster;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
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
                ->paginate($limit);

            foreach ($inventories as $each) {
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
            foreach ($related_inventories as $each) {
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
            $normalized_quantity = normalize_second_last_decimal($request->quantity);
            $normalized_price = normalize_second_last_decimal($request->price);
            $normalized_sale_price = normalize_second_last_decimal($request->sale_price ?? $request->price);
            $find_inventory = Inventory::where('name', $request->name)->where('company_id', $request->header('company'))->first();
            if (empty($find_inventory)) {
                $inventory = new Inventory();
                $inventory->name = $request->name;
                $inventory->quantity = $normalized_quantity;
                $inventory->company_id = $request->header('company');
                $inventory->save();

                $items = new InventoryItem();
                $items->inventory_id = $inventory->id;
                $items->worker_name = $request->worker_name;
                $items->quantity = $normalized_quantity;
                $items->price = $normalized_price;
                $items->sale_price = $normalized_sale_price;
                $items->unit = $request->unit;
                $items->save();

                if ($inventory->quantity !== $items->quantity) {
                    $inventory->updateInventoryQuantity($items->quantity);
                }
                $inventory->price =  $items->price;

                return response()->json([
                    'inventory' => $inventory,
                ]);
            } else {
                $items = new InventoryItem();
                $items->inventory_id = $find_inventory->id;
                $items->worker_name = $request->worker_name;
                $items->quantity = $normalized_quantity;
                $items->price = $normalized_price;
                $items->sale_price = $normalized_sale_price;
                $items->unit = $request->unit;
                $items->save();

                if ($find_inventory->quantity !==  $normalized_quantity) {
                    $find_inventory->updateInventoryQuantity($normalized_quantity);
                }
                return response()->json([
                    'inventory' => $find_inventory,
                ]);
            }


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
            $normalized_price = normalize_second_last_decimal($request->price);
            $normalized_sale_price = normalize_second_last_decimal($request->sale_price);
            $inventory = Inventory::find($id);
            $inventory->name = $request->name;
            $inventory->company_id = $request->header('company');
            $inventory->save();

            //Update latest entry
            $items = InventoryItem::where('inventory_id', $inventory->id)->orderBy('id', 'desc')->first();
            $items->worker_name = $request->worker_name;
            //$items->quantity = $request->quantity;
            $items->price = $normalized_price;
            $items->sale_price = $normalized_sale_price;
            $items->unit = $request->unit;
            $items->save();

            // if ($inventory->quantity !==  $request->quantity) {
            //     $inventory->updateInventoryQuantity($request->quantity);
            // }

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
        $normalized_price = normalize_second_last_decimal($request->price);
        $normalized_sale_price = normalize_second_last_decimal($request->sale_price);
        $inventory = Inventory::whereIn('id', $request->selected_ids)->get();

        foreach ($inventory as $each) {
            InventoryItem::where('id', $each->id)->orderBy('id', 'desc')->update([
                'price' => $normalized_price,
                'sale_price' => $normalized_sale_price,
            ]);
        }
        return response()->json([
            'inventory' => $inventory,
        ]);
    }

    /**
     * Get inventory stock
     *
     * @param Request $request
     */
    public function getInventoryStock(Request $request)
    {
        try {

            $query = DB::table('inventories as inv')
            ->leftJoin('inventory_items as int', 'int.inventory_id', '=', 'inv.id')
            ->leftJoin('invoice_items as ic', 'ic.inventory_id', '=', 'inv.id')
            ->leftJoin('invoices as inc', 'inc.id', '=', 'ic.invoice_id');
            // Set filter

            if (! empty($request->worker_name)) {
                $searchBy = $request->input('worker_name');
                $query =  $query->where(function ($query) use ($searchBy) {
                    return $query->where('int.worker_name', 'LIKE', '%'.$searchBy.'%');
                });
            }
            if (! empty($request->name)) {
                $searchBy = $request->input('name');
                $query=  $query->where(function ($query) use ($searchBy) {
                    return $query->where('inv.name', 'LIKE', '%'.$searchBy.'%');
                });
            }
            if (! empty($request->from_date) && ! empty($request->to_date)) {
                $start_date =  Carbon::createFromFormat('d/m/Y', $request->input('from_date'))->format('Y-m-d');
                $end_date =  Carbon::createFromFormat('d/m/Y', $request->input('to_date'))->format('Y-m-d');
                $query =  $query->whereBetween(
                    'inc.invoice_date',
                    [ $start_date,  $end_date]
                );
            }

            $inventories = $query->select(
                'inc.invoice_date as invoice_date',
                'inv.id as id',
                'inv.name as name',
                'inv.quantity as quantity',
                'int.price as price',
                'int.sale_price as sale_price',
                'int.unit as unit',
                'int.worker_name as worker_name',
                DB::raw("(DATE_FORMAT(int.created_at ,'%Y-%m-%d')) as date_time"),
                DB::raw("SUM(ic.quantity) as item_count"),
            )->orderBy('inv.id', 'desc')
            ->groupBy('inv.id')
            ->get();
            // foreach ($inventories as $each) {
            //     $lastest_item = InventoryItem::where('inventory_id', $each->id)->orderBy('id', 'desc')->first();
            //     $each['quantity'] = $each->quantity;
            //     $each['price'] = $lastest_item->price;
            //     $each['sale_price'] = $lastest_item->sale_price;
            //     $each['unit'] = $lastest_item->unit;
            //     $each['worker_name'] = $lastest_item->worker_name;
            //     $each['date_time'] = Carbon::parse($lastest_item->created_at)->format('d-m-Y');

            //     $invoice_items_count = InvoiceItem::whereCompany($request->header('company'))
            //         ->where('inventory_id', $each->id)
            //         ->orderBy('id', 'desc')->sum('quantity');
            //     $each['item_count'] = intval($invoice_items_count);
            // }

            return response()->json([
                'inventoryItems' => $inventories,
                'total' => $inventories->count(),
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get invoice stock
     *
     * @param Request $request
     */
    public function getInvoiceStock(Request $request, $inventory_id)
    {
        try {
            $inventoryItems = InventoryItem::where('inventory_id', $inventory_id)
            ->orderBy('id', 'desc')->get();

            foreach ($inventoryItems as $each) {
                $each['name'] = Inventory::where('id', $inventory_id)->first()->name;
                $each['date_time'] = Carbon::parse($each->created_at)->format('d-m-Y');
            }

            $invoice_items = InvoiceItem::whereCompany($request->header('company'))
                    ->where('inventory_id', $inventory_id)
                    ->orderBy('id', 'desc')->get();

            foreach ($invoice_items as $each) {
                $party = Invoice::where('id', $each->invoice_id)->first();
                $each['party_name'] = AccountMaster::where('id', $party->account_master_id)->first()->name;
                $each['date_time'] = Carbon::parse($party->invoice_date)->format('d-m-Y');
                $each['invoice_number'] = Invoice::where('id', $each->invoice_id)->first()->invoice_number;
            }

            return response()->json([
                'inventoryItems' => $inventoryItems,
                'invoiceItems' => $invoice_items,
                'total' => $invoice_items->count(),
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
