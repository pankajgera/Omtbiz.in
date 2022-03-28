<?php

namespace App\Http\Controllers;

use App\Models\AccountMaster;
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

        $dispatch_inprogress = Dispatch::where('status', 'Draft')->applyFilters($request->only([
            'name',
            'date_time',
            'transport',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        foreach ($dispatch_inprogress as $inprogress) {
            $inprogress['invoices'] =  Invoice::whereIn('id', explode(',', $inprogress['invoice_id']))->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($inprogress['invoices'] as $each) {
                $inprogress['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }

        $dispatch_completed = Dispatch::where('status', 'Sent')->applyFilters($request->only([
            'name',
            'date_time',
            'transport',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'))
            ->latest()
            ->paginate($limit);

        foreach ($dispatch_completed as $processed) {
            $processed['invoices'] =  Invoice::whereIn('id', explode(',', $processed['invoice_id']))->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($processed['invoices'] as $each) {
                $processed['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }
        return response()->json([
            'dispatch_inprogress' => $dispatch_inprogress,
            'dispatch_completed' => $dispatch_completed,
            'dispatch_total' =>  Dispatch::count(),
        ]);
    }

    /**
     * Edit Dispatch
     *
     * @param Request $request
     * @return void
     */
    public function edit(Request $request, $id)
    {
        $dispatch = Dispatch::find($id);
        $dispatch['invoice_id'] = explode(', ', $dispatch->invoice_id);

        return response()->json([
            'dispatch' => $dispatch,
        ]);
    }

    /**
     * Edit To Be Dispatch
     *
     * @param Request $request
     * @return void
     */
    public function tobeEdit(Request $request)
    {
        $to_be_dispatch_ids = array_unique(explode(',', $request->getContent()));
        $tobeDispatch = [];
        foreach ($to_be_dispatch_ids as $id) {
            $dispatch = Dispatch::find($id);
            $dispatch['invoice_id'] = explode(', ', $dispatch->invoice_id);
            array_push($tobeDispatch, $dispatch);
        }

        return response()->json([
            'dispatch' => $tobeDispatch,
        ]);
    }

    /**
     * Create Dispatch.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $date_format = 'Y-m-d\TH:i:s.v\Z';
            if (strpos($request->date_time, ' ') !== false) {
                $date_format = 'Y-m-d H:i:s';
            }
            $date = Carbon::createFromFormat($date_format, $request->date_time);
            $date->setTimeZone('Asia/Kolkata');
            $dispatch = new Dispatch();
            //$dispatch->name = $request->name;
            $dispatch->invoice_id = implode(', ', $request->invoice_id);
            $dispatch->date_time = $date;
            $dispatch->transport = $request->transport;
            $dispatch->person = $request->person;
            $dispatch->time = $request->time;
            $dispatch->status = $request->status['name'];
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            $invoices = Invoice::whereIn('id', $request->invoice_id)->get();
            foreach ($invoices as $each) {
                $each->update([
                    'paid_status' => 'DISPATCHED',
                ]);
            }
            $dispatch->addDispatchBillTy($dispatch->id, $invoices->sum('total'), $request->header('company'));

            return response()->json([
                'dispatch' => $dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving dispatch', [$e]);
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
    public function updateDispatch(Request $request, $id)
    {
        try {
            $date_format = 'Y-m-d\TH:i:s.v\Z';
            if (strpos($request->date_time, ' ') !== false) {
                $date_format = 'Y-m-d H:i:s';
            }
            $date = Carbon::createFromFormat($date_format, $request->date_time);
            $date->setTimeZone('Asia/Kolkata');
            $dispatch = Dispatch::find($id);
            //$dispatch->name = $request->name;
            $dispatch->invoice_id = implode(', ', $request->invoice_id);
            $dispatch->date_time = $date;
            $dispatch->transport = $request->transport;
            $dispatch->person = $request->person;
            $dispatch->time = $request->time;
            $dispatch->status = $request->status['name'];
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            $invoices = Invoice::whereIn('id', $request->invoice_id)->get();
            $dispatch->addDispatchBillTy($dispatch->id, $invoices->sum('total'), $request->header('company'));

            return response()->json([
                'dispatch' => $dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating dispatch', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing To Be Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateToBeDispatch(Request $request)
    {
        $all_selected_dispatch = Dispatch::whereIn('id', $request->all_selected_dispatch)->get();
        try {
            foreach ($all_selected_dispatch as $each) {
                $date_format = 'Y-m-d\TH:i:s.v\Z';
                if (strpos($request->date_time, ' ') !== false) {
                    $date_format = 'Y-m-d H:i:s';
                }
                $date = Carbon::createFromFormat($date_format, $request->date_time);
                $date->setTimeZone('Asia/Kolkata');
                $each->update([
                    //'name' => $request->name,
                    'invoice_id' => implode(', ', $request->invoice_id),
                    'date_time' => $date,
                    'transport' => $request->transport,
                    'person' => $request->person,
                    'time' => $request->time,
                    'status' => $request->status['name'],
                ]);
            }

            $invoices = Invoice::whereIn('id', $request->invoice_id)->get();
            $ids = $all_selected_dispatch->pluck('id')->toArray();
            $each->addDispatchBillTy(implode(', ', $ids), $invoices->sum('total'), $request->header('company'));

            return response()->json([
                'dispatch' => $all_selected_dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating dispatch', [$e]);
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
     * Move a list of existing Dispatch.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function multiple(Request $request)
    {
        $dispatch = [];
        foreach ($request->id as $id) {
            $dispatch = Dispatch::moveDispatch($id);
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
        $invoices = Invoice::with('master')->where('status', '!=', 'COMPLETED')
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'invoices' => $invoices
        ]);
    }
}
