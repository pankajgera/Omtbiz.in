<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\OrdersRequest;
use App\Models\User;
use Validator;
use App\Models\CompanySetting;
use App\Models\Company;
use App\Models\AccountMaster;
use App\Models\OrderItems;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Index page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $pending_orders = Orders::with([
            'orderItems',
            'master'
        ])
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->applyFilters($request->only([
                'status',
                'order_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->where('status', 'DRAFT')
            ->select('orders.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $completed_orders = Orders::with([
            'orderItems',
            'master'
        ])
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->applyFilters($request->only([
                'status',
                'order_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->where('status', 'COMPLETED')
            ->select('orders.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        $siteData = [
            'pending_orders' => $pending_orders,
            'completed_orders' => $completed_orders,
            'pending_count' => Orders::whereCompany($request->header('company'))->where('status', 'DRAFT')->count(),
            'completed_count' => Orders::whereCompany($request->header('company'))->where('status', 'COMPLETED')->count(),
            'sundryDebtorsList' => $sundryDebtorsList
        ];

        return response()->json($siteData);
    }

    /**
     * Create new order page data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $order_prefix = CompanySetting::getSetting('order_prefix', $request->header('company'));
        $order_num_auto_generate = CompanySetting::getSetting('order_auto_generate', $request->header('company'));

        $nextOrderNumberAttribute = null;
        $nextOrderNumber = Orders::getNextOrderNumber($order_prefix, $request->header('company'));

        if ($order_num_auto_generate == "YES") {
            $nextOrderNumberAttribute = $nextOrderNumber;
        }

        $customers = User::where('role', 'customer')->get();

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'order_today_date' => Carbon::now()->toDateString(),
            'customers' => $customers,
            'nextOrderNumberAttribute' => $nextOrderNumberAttribute,
            'nextOrderNumber' => $order_prefix . '-' . $nextOrderNumber,
            'shareable_link' => '',
            'order_prefix' => $order_prefix,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Create new order
     *
     * @param OrdersRequest $request
     * @return JsonResponse
     */
    public function store(OrdersRequest $request)
    {
        $number_attributes['order_number'] = $request->order_number;

        Validator::make($number_attributes, [
            'order_number' => 'required'
        ])->validate();

        $companyId = (int) $request->header('company');
        $order_prefix = CompanySetting::getSetting('order_prefix', $companyId);
        $order_date = Carbon::createFromFormat('d/m/Y', $request->order_date)->format('Y-m-d');
        $status = Orders::DRAFT;

        $order = DB::transaction(function () use ($request, $number_attributes, $companyId, $order_prefix, $order_date, $status) {
            Company::where('id', $companyId)->lockForUpdate()->first();

            $finalOrderNumber = $this->getNextAvailableOrderNumber(
                $number_attributes['order_number'],
                $companyId,
                $order_prefix
            );
            $reference_number = $this->getReferenceNumberForOrder(
                $companyId,
                (int) $request->debtors['id'],
                $order_date,
                $finalOrderNumber
            );

            if (! $reference_number || ! $finalOrderNumber) {
                abort(500);
            }

            return Orders::create([
                'order_date' => $order_date,
                'expiry_date' => $order_date,
                'order_number' => $finalOrderNumber,
                'user_id' => $request->user_id,
                'company_id' => $companyId,
                'status' => $status,
                'notes' => $request->notes,
                'unique_hash' => str_random(60),
                'account_master_id' => $request->debtors['id'],
                'reference_number' => $reference_number,
            ]);
        }, 3);

        foreach ($request->order_items as $orderItem) {
            $orderItem['company_id'] = $request->header('company');
            $orderItem['type'] = 'order';
            $item = $order->orderItems()->create($orderItem);
        }

        $order = Orders::with([
            'orderItems',
            'user',
        ])->find($order->id);

        return response()->json([
            'order' => $order,
            'url' => url('/orders/pdf/' . $order->unique_hash),
        ]);
    }

    private function getReferenceNumberForOrder($companyId, $accountMasterId, $orderDate, $orderNumber)
    {
        $firstOrder = Orders::where('company_id', $companyId)
            ->where('account_master_id', $accountMasterId)
            ->where('order_date', $orderDate)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->first();

        if ($firstOrder) {
            return $firstOrder->order_number;
        }

        return $orderNumber;
    }

    private function getNextAvailableOrderNumber($requestedOrderNumber, $companyId, $orderPrefix)
    {
        $orderNumber = $requestedOrderNumber;
        $attempt = 0;
        $maxAttempts = 20;

        while ($attempt < $maxAttempts) {
            $exists = Orders::where('company_id', $companyId)
                ->where('order_number', $orderNumber)
                ->exists();

            if (! $exists) {
                return $orderNumber;
            }

            $orderNumber = $this->buildOrderNumberFromMaxSuffix($companyId, $orderPrefix, $attempt + 1);
            $attempt++;
        }

        return sprintf(
            '%s-%06d',
            $orderPrefix,
            (int) (microtime(true) * 1000000) % 1000000
        );
    }

    private function buildOrderNumberFromMaxSuffix($companyId, $orderPrefix, $offset = 1)
    {
        $maxSuffix = Orders::where('company_id', $companyId)
            ->where('order_number', 'like', $orderPrefix . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED)) as max_suffix")
            ->value('max_suffix');

        $nextSuffix = ((int) $maxSuffix) + max(1, (int) $offset);

        return $orderPrefix . '-' . sprintf('%06d', $nextSuffix);
    }

    public function referenceNumber(Request $request)
    {
        $accountMasterId = is_array($request->id) ? ($request->id['id'] ?? null) : $request->id;
        $companyId = (int) $request->header('company');
        $orderDate = $this->parseOrderDateForReference($request->order_date);

        if (! $accountMasterId) {
            return response()->json([
                'order' => null,
            ]);
        }

        try {
            $firstOrder = Orders::where('order_date', $orderDate)
                ->whereCompany($companyId)
                ->where('account_master_id', $accountMasterId)
                ->orderBy('id', 'asc')->firstOrFail();
            $firstOrder->reference_number = $firstOrder->order_number;
        } catch (\Exception $e) {
            return response()->json([
                'order' => null,
            ]);
        }

        return response()->json([
            'order' => $firstOrder
        ]);
    }

    private function parseOrderDateForReference($orderDate)
    {
        if (! $orderDate) {
            return Carbon::now('Asia/Kolkata')->toDateString();
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $orderDate, 'Asia/Kolkata')->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        return Carbon::now('Asia/Kolkata')->toDateString();
    }

    /**
     * Show single order
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $order = Orders::with([
            'orderItems',
            'user',
        ])->find($id);

        $siteData = [
            'order' => $order,
            'shareable_link' => url('/orders/pdf/' . $order->unique_hash)
        ];

        return response()->json($siteData);
    }

    /**
     * Edit single order
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $order = Orders::with([
            'orderItems',
            'user',
        ])->find($id);
        $customers = User::where('role', 'customer')->get();
        $sundryDebtorsList = AccountMaster::where('id', $order->account_master_id)->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'customers' => $customers,
            'orderNumber' => $order->getOrderNumAttribute(),
            'order' => $order,
            'shareable_link' => url('/orders/pdf/' . $order->unique_hash),
            'order_prefix' => $order->getOrderPrefixAttribute(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Update single order
     *
     * @param OrdersRequest $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function update(OrdersRequest $request, $id)
    {
        $order = Orders::findOrFail($id);
        $order->notes = $request->notes;
        $order->save();

        $oldItems = $order->orderItems->toArray();
        $orderItems = $request->order_items;

        foreach ($oldItems as $oldItem) {
            OrderItems::destroy($oldItem['id']);
        }

        foreach ($orderItems as $orderItem) {
            $orderItem['company_id'] = $request->header('company');
            $orderItem['type'] = 'order';
            $item = $order->orderItems()->create($orderItem);
        }

        $order = Orders::with([
            'orderItems',
            'user',
        ])->find($order->id);

        return response()->json([
            'order' => $order,
            'url' => url('/orders/pdf/' . $order->unique_hash),
        ]);
    }

    /**
     * Delete single order
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        Orders::deleteOrder($id);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            Orders::deleteOrder($id);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
