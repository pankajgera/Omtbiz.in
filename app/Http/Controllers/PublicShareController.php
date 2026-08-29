<?php

namespace App\Http\Controllers;

use App\Models\AccountLedger;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PublicShare;
use App\Models\Receipt;
use App\Support\PublicShareService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicShareController extends Controller
{
    public function __construct(private readonly PublicShareService $shares)
    {
    }

    public function index(Request $request)
    {
        $shares = PublicShare::withoutGlobalScopes()
            ->where('company_id', $request->attributes->get('company_id'))
            ->active()
            ->latest()
            ->get()
            ->map(fn (PublicShare $share) => $this->response($share));

        return response()->json(['shares' => $shares]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(array_merge(PublicShareService::DOCUMENT_TYPES, PublicShareService::REPORT_TYPES))],
            'resource_id' => ['required_if:type,invoice,estimate,receipt,expense', 'nullable', 'integer', 'min:1'],
            'parameters' => ['nullable', 'array'],
            'parameters.from_date' => ['required_if:type,sales-customers,sales-items,expenses,profit-loss,customers,banks', 'date_format:d/m/Y'],
            'parameters.to_date' => ['required_if:type,sales-customers,sales-items,expenses,profit-loss,customers,banks', 'date_format:d/m/Y'],
            'parameters.ledger_id' => ['required_if:type,customers', 'integer', 'min:1'],
        ]);

        $companyId = (int) $request->attributes->get('company_id');
        $userId = (int) $request->user('api')->id;

        if (in_array($request->type, PublicShareService::DOCUMENT_TYPES, true)) {
            $model = match ($request->type) {
                'invoice' => Invoice::class,
                'estimate' => Estimate::class,
                'receipt' => Receipt::class,
                'expense' => Expense::class,
            };
            $resource = $model::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->findOrFail($request->resource_id);
            $share = $this->shares->document($resource, $request->type, $userId);
        } else {
            $parameters = $request->parameters;

            if ($request->type === 'customers') {
                AccountLedger::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->findOrFail($parameters['ledger_id']);
            }

            $share = $this->shares->report($companyId, $userId, $request->type, $parameters);
        }

        return response()->json($this->response($share), 201);
    }

    public function destroy(Request $request, int $share)
    {
        $record = PublicShare::withoutGlobalScopes()
            ->where('company_id', $request->attributes->get('company_id'))
            ->findOrFail($share);
        $record->update(['revoked_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function response(PublicShare $share): array
    {
        return [
            'id' => $share->id,
            'type' => $share->resource_type,
            'resource_id' => $share->resource_id,
            'url' => $this->shares->url($share),
            'created_at' => $share->created_at,
        ];
    }
}
