<?php

namespace Crater\Http\Controllers;

use Crater\Bank;
use Exception;
use Illuminate\Http\Request;
use Log;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 20;

        $banks = Bank::applyFilters($request->only([
                'name',
                'amount',
                'date',
                'orderByField',
                'orderBy',
            ]))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'banks' => $banks,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $bank = Bank::find($id);

        return response()->json([
            'bank' => $bank,
        ]);
    }

    /**
     * Create Bank.
     *     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $bank = new Bank();
            $bank->name = $request->name;
            $bank->amount = $request->amount;
            $bank->date = $request->date;
            $bank->save();

            $bank = Bank::find($bank->id);

            return response()->json([
                'bank' => $bank,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving bank', [$e->getMessage()]);
        }
    }

    /**
     * Update an existing Bank.
     *
     * @param int                               $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $bank = Bank::find($id);
            $bank->name = $request->name;
            $bank->amount = $request->amount;
            $bank->date = $request->date;
            $bank->save();

            $bank = Bank::find($bank->id);

            return response()->json([
                'bank' => $bank,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating bank', [$e->getMessage()]);
        }
    }

    /**
     * Delete an existing Bank.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = Bank::deleteBank($id);

        if (!$data) {
            return response()->json([
                'error' => 'bank_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Bank.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $banks = [];
        foreach ($request->id as $id) {
            $bank = Bank::deleteBank($id);
            if (!$bank) {
                array_push($banks, $id);
            }
        }

        if (empty($banks)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'banks' => $banks,
        ]);
    }
}
