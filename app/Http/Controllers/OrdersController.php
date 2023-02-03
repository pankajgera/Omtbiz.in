<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\OrdersRequest;
use App\Models\Invoice;
use App\Models\User;
use Validator;
use App\Models\CompanySetting;
use App\Models\Company;
use App\Mail\OrderPdf;
use App\Models\AccountMaster;
use App\Models\Inventory;
use App\Models\OrderItems;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;

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
        $nextOrderNumber = Orders::getNextOrderNumber($order_prefix);

        if ($order_num_auto_generate == "YES") {
            $nextOrderNumberAttribute = $nextOrderNumber;
        }

        $customers = User::where('role', 'customer')->get();

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'order_today_date' => Carbon::now()->toDateString(),
            'customers' => $customers,
            'inventories' => Inventory::query()->get(),
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
        $order_number = $request->order_number;
        $number_attributes['order_number'] = $request->order_number;

        Validator::make($number_attributes, [
            'order_number' => 'required'
        ])->validate();

        $order_date = Carbon::createFromFormat('d/m/Y', $request->order_date);
        $status = Orders::DRAFT;

        $order = Orders::create([
            'order_date' => $order_date,
            'expiry_date' => $order_date,
            'order_number' => $number_attributes['order_number'],
            'user_id' => $request->user_id,
            'company_id' => $request->header('company'),
            'status' => $status,
            'notes' => $request->notes,
            'unique_hash' => str_random(60),
            'account_master_id' => $request->debtors['id'],
        ]);

        foreach ($request->order_items as $orderItem) {
            $orderItem['company_id'] = $request->header('company');
            $orderItem['type'] = 'order';
            $item = $order->orderItems()->create($orderItem);
        }

        if ($request->has('orderSend')) {
            $data['order'] = $order->toArray();
            $userId = $data['order']['user_id'];
            $data['user'] = User::find($userId)->toArray();
            $data['company'] = Company::find($order->company_id);
            $email = $data['user']['email'];
            $notificationEmail = CompanySetting::getSetting(
                'notification_email',
                $request->header('company')
            );

            if (!$email) {
                return response()->json([
                    'error' => 'user_email_does_not_exist'
                ]);
            }

            if (!$notificationEmail) {
                return response()->json([
                    'error' => 'notification_email_does_not_exist'
                ]);
            }

            \Mail::to($email)->send(new OrderPdf($data, $notificationEmail));
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
            'inventories' => Inventory::query()->get(),
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
     * Send order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOrder(Request $request)
    {
        $order = Orders::findOrFail($request->id);

        $data['order'] = $order->toArray();
        $userId = $data['order']['user_id'];
        $data['user'] = User::find($userId)->toArray();
        $data['company'] = Company::find($order->company_id);

        $email = $data['user']['email'];
        $notificationEmail = CompanySetting::getSetting(
            'notification_email',
            $request->header('company')
        );

        if (!$email) {
            return response()->json([
                'error' => 'user_email_does_not_exist'
            ]);
        }

        if (!$notificationEmail) {
            return response()->json([
                'error' => 'notification_email_does_not_exist'
            ]);
        }

        \Mail::to($email)->send(new OrderPdf($data, $notificationEmail));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Order to invoice
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function orderToInvoice(Request $request, $id)
    {
        $order = Orders::with(['orderItems', 'user'])->find($id);
        $invoice_date = Carbon::parse($order->order_date);
        $due_date = Carbon::parse($order->order_date)->addDays(7);

        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));

        $invoice = Invoice::create([
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'invoice_number' => "INV-" . Invoice::getNextInvoiceNumber($invoice_prefix, $request->header('company')),
            //'reference_number' => $order->reference_number,
            'user_id' => $order->user_id,
            'company_id' => $request->header('company'),
            'status' => Invoice::TO_BE_DISPATCH,
            'paid_status' => Invoice::STATUS_PAID,
            'notes' => $order->notes,
            'unique_hash' => str_random(60)
        ]);

        $invoiceItems = $order->orderItems->toArray();

        foreach ($invoiceItems as $invoiceItem) {
            $invoiceItem['company_id'] = $request->header('company');
            $invoiceItem['name'] = $invoiceItem['name'];
            $item = $invoice->orderItems()->create($invoiceItem);
        }

        $invoice = Invoice::with([
            'orderItems',
            'user',
        ])->find($invoice->id);

        return response()->json([
            'invoice' => $invoice
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
