<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispatch;
use App\Models\Invoice;
use Carbon\Carbon;
use Exception;
use Log;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $dispatch_inprogress = Dispatch::where('status', '!=', 'Completed')->applyFilters($request->only([
            'name',
            'date_time',
            'transport',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        $dispatch_completed = Dispatch::where('status', 'Completed')->applyFilters($request->only([
            'name',
            'date_time',
            'transport',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'dispatch_inprogress' => $dispatch_inprogress,
            'dispatch_completed' => $dispatch_completed,
            'dispatch_total' =>  Dispatch::count(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $dispatch = Dispatch::find($id);

        return response()->json([
            'dispatch' => $dispatch,
        ]);
    }

    /**
     * Create Dispatch.
     *     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $dispatch = new Dispatch();
            $dispatch->name = $request->name;
            $dispatch->invoice_id = $request->invoice_id;
            $dispatch->date_time = Carbon::createFromFormat('Y-m-d H:i:s', $request->date_time, 'Asia/Kolkata');
            $dispatch->transport = $request->transport;
            $dispatch->status = $request->status['name'];
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            return response()->json([
                'dispatch' => $dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving dispatch', [$e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $dispatch = Dispatch::find($id);
            $dispatch->name = $request->name;
            $dispatch->invoice_id = $request->invoice_id;
            $dispatch->date_time = $request->date_time;
            $dispatch->transport = $request->transport;
            $dispatch->status = $request->status['name'];
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            return response()->json([
                'dispatch' => $dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating dispatch', [$e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an existing Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = Dispatch::deleteDispatch($id);

        if (!$data) {
            return response()->json([
                'error' => 'dispatch_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Dispatch.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $dispatch = [];
        foreach ($request->id as $id) {
            $dispatch = Dispatch::deleteDispatch($id);
            if (!$dispatch) {
                array_push($dispatch, $id);
            }
        }

        if (empty($dispatch)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'dispatch' => $dispatch,
        ]);
    }


    /**
     * Retrive a specified user's unpaid invoices from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoices(Request $request)
    {
        $invoices = Invoice::where('paid_status', '<>', Invoice::STATUS_PAID)
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'invoices' => $invoices
        ]);
    }
}
