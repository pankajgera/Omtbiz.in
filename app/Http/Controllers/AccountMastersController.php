<?php

namespace Crater\Http\Controllers;

use Crater\AccountGroup;
use Crater\AccountMaster;
use Exception;
use Illuminate\Http\Request;
use Log;

class AccountMastersController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 50;

        $masters = AccountMaster::applyFilters($request->only([
            'name',
            'groups',
            'orderByField',
            'orderBy',
        ]))
            ->latest()
            ->paginate($limit);

        return response()->json([
            'masters' => $masters,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $master = AccountMaster::find($id);

        return response()->json([
            'master' => $master,
        ]);
    }

    /**
     * Create Account Master.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $master = new AccountMaster();
            $master->name = $request->name;
            $master->groups = $request->groups;
            $master->address = $request->address;
            $master->country = $request->country;
            $master->state = $request->state;
            $master->opening_balance = $request->opening_balance;
            $master->type = $request->type;
            $master->save();

            $master = AccountMaster::find($master->id);

            return response()->json([
                'master' => $master,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account master', [$e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing Account Master.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $master = AccountMaster::find($id);
            $master->name = $request->name;
            $master->groups = $request->groups;
            $master->address = $request->address;
            $master->country = $request->country;
            $master->state = $request->state;
            $master->opening_balance = $request->opening_balance;
            $master->type = $request->type;
            $master->save();

            $master = AccountMaster::find($master->id);

            return response()->json([
                'master' => $master,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating account master', [$e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an existing Account Master.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = AccountMaster::deleteAccountMaster($id);

        if (!$data) {
            return response()->json([
                'error' => 'master_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Account Master.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $masters = [];
        foreach ($request->id as $id) {
            $master = AccountMaster::deleteAccountMaster($id);
            if (!$master) {
                array_push($masters, $id);
            }
        }

        if (empty($masters)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'masters' => $masters,
        ]);
    }
}
