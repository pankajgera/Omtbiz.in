<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Exception;
use Illuminate\Http\Request;

class AuditLogsController extends Controller
{
    /**
     * List audit logs for admins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function index(Request $request)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        try {
            $limit = $request->has('limit') ? (int) $request->limit : 15;

            $logs = AuditLog::query()
                ->applyFilters($request->only([
                    'user',
                    'action',
                    'module',
                    'from_date',
                    'to_date',
                ]))
                ->whereCompany($request->header('company'))
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($limit);

            return response()->json([
                'audit_logs' => $logs,
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /**
     * Show a single audit log entry.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function show($id)
    {
        if ($response = $this->adminOnlyResponse()) {
            return $response;
        }

        try {
            $log = AuditLog::findOrFail($id);

            return response()->json([
                'audit_log' => $log,
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }
}
