<?php

namespace Crater\Http\Controllers;

use Crater\AccountGroup;
use Exception;
use Illuminate\Http\Request;
use Log;

class AccountGroupsController extends Controller
{
    public function index(Request $request)
    {
        $groups = AccountGroup::get(['id', 'name']);

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $group = AccountGroup::find($id);

        return response()->json([
            'group' => $group,
        ]);
    }

    /**
     * Create Account Group.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $group = new AccountGroup();
            $group->name = $request->name;
            $group->save();

            $group = AccountGroup::find($group->id);

            return response()->json([
                'group' => $group,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving account group', [$e->getMessage()]);
        }
    }

    /**
     * Update an existing Account Group.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $group = AccountGroup::find($id);
            $group->name = $request->name;
            $group->save();

            $group = AccountGroup::find($group->id);

            return response()->json([
                'group' => $group,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating account group', [$e->getMessage()]);
        }
    }

    /**
     * Delete an existing Account Group.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = AccountGroup::deleteAccountGroup($id);

        if (!$data) {
            return response()->json([
                'error' => 'group_attached',
            ]);
        }

        return response()->json([
            'success' => $data,
        ]);
    }

    /**
     * Delete a list of existing Account Group.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $groups = [];
        foreach ($request->id as $id) {
            $group = AccountGroup::deleteAccountGroup($id);
            if (!$group) {
                array_push($groups, $id);
            }
        }

        if (empty($groups)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'groups' => $groups,
        ]);
    }
}
