<?php

namespace App\Http\Controllers;

use App\Models\RecycleBinEntry;
use App\Services\RecycleBin;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Admin view of deleted invoices, vouchers, receipts and payments (admin-only via RouteRoles).
 */
class RecycleBinController extends Controller
{
    public function index(Request $request)
    {
        $query = RecycleBinEntry::with('deletedBy:id,name')
            ->where('company_id', $request->header('company'))
            ->latest('deleted_at');

        if (in_array($request->type, RecycleBin::TYPES, true)) {
            $query->where('resource_type', $request->type);
        }

        if ($search = trim((string) $request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")->orWhere('party', 'like', "%{$search}%");
            });
        }

        $entries = $query->paginate($request->input('limit', 20));
        $entries->getCollection()->transform(function (RecycleBinEntry $entry) {
            return [
                'id' => $entry->id,
                'resource_type' => $entry->resource_type,
                'label' => $entry->label,
                'party' => $entry->party,
                'amount' => $entry->amount,
                'deleted_by' => $entry->deletedBy?->name,
                'deleted_at' => $entry->deleted_at->toIso8601String(),
                'purge_at' => $entry->purgeAt()->toIso8601String(),
            ];
        });

        return response()->json([
            'entries' => $entries,
            'retention_days' => RecycleBinEntry::RETENTION_DAYS,
        ]);
    }

    public function restore(Request $request, $id)
    {
        $entry = RecycleBinEntry::where('company_id', $request->header('company'))->findOrFail($id);

        try {
            RecycleBin::restore($entry);
        } catch (RuntimeException $e) {
            return response()->json(['error' => 'restore_failed', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true]);
    }
}
